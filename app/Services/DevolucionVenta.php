<?php

namespace App\Services;

use App\Models\Caja;
use App\Models\DetalleVenta;
use App\Models\Devolucion;
use App\Models\DevolucionDetalle;
use App\Models\Lote;
use App\Models\MovimientoCaja;
use App\Models\MovimientoInventario;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Devolución PARCIAL de productos de una venta (solo admin).
 *
 * - Las unidades devueltas vuelven a su MISMO lote (Kardex RETURN / SALE_RETURN).
 * - La venta queda con su total NETO: baja la cantidad y el subtotal de cada
 *   línea, el total, y la parte en efectivo/QR (Mixto: primero efectivo).
 * - La caja de la venta se recalcula (esperado, diferencia y QR), aunque ya
 *   esté cerrada, y se agrega una nota automática a su observación.
 */
class DevolucionVenta
{
    /**
     * Vista previa: monto a devolver y cómo queda la caja, sin guardar nada.
     *
     * @param  array<int,int>  $qtyByDetail  [id_detalle_venta => cantidad a devolver]
     */
    public function preview(Venta $sale, array $qtyByDetail): array
    {
        [$lines, $amount] = $this->lines($sale, $qtyByDetail, false);
        [$cashBack, $qrBack] = $this->split($sale, $amount);

        $caja = $sale->id_caja ? Caja::find($sale->id_caja) : null;
        $b = $caja?->cashBreakdown();
        $active = $sale->status === 'ACTIVE';

        $expectedAfter = $b ? round($b['expected_cash'] - ($active ? $cashBack : 0), 2) : null;
        $qrAfter = $b ? round($b['qr_sales'] - ($active ? $qrBack : 0), 2) : null;

        return [
            'lines' => $lines,
            'amount' => $amount,
            'cash' => $cashBack,
            'qr' => $qrBack,
            'new_total' => round((float) $sale->total - $amount, 2),
            'caja' => $caja ? [
                'id' => $caja->id,
                'closed' => $caja->status === 'CLOSED',
                'expected_before' => $b['expected_cash'],
                'expected_after' => $expectedAfter,
                'diff_before' => $caja->monto_cierre !== null ? round((float) $caja->monto_cierre - $b['expected_cash'], 2) : null,
                'diff_after' => $caja->monto_cierre !== null ? round((float) $caja->monto_cierre - $expectedAfter, 2) : null,
                'qr_before' => $b['qr_sales'],
                'qr_after' => $qrAfter,
            ] : null,
        ];
    }

    /**
     * Aplica la devolución.
     *
     * @param  array<int,int>  $qtyByDetail  [id_detalle_venta => cantidad a devolver]
     */
    public function apply(Venta $sale, array $qtyByDetail, string $reason, int $userId): Devolucion
    {
        $reason = trim($reason);
        if ($reason === '') {
            throw new RuntimeException('Debe escribir el motivo de la devolución.');
        }

        return DB::transaction(function () use ($sale, $qtyByDetail, $reason, $userId) {
            $sale = Venta::whereKey($sale->id)->lockForUpdate()->firstOrFail();

            if ($sale->status !== 'ACTIVE') {
                throw new RuntimeException('Solo se pueden hacer devoluciones de ventas activas.');
            }
            if ($sale->invoice && $sale->invoice->estado === 'ENVIADA') {
                throw new RuntimeException('La venta tiene una factura enviada; anúlela antes en Facturación.');
            }

            [$lines, $amount] = $this->lines($sale, $qtyByDetail, true);
            if (empty($lines)) {
                throw new RuntimeException('Indique al menos una unidad a devolver.');
            }
            [$cashBack, $qrBack] = $this->split($sale, $amount);

            $dev = Devolucion::create([
                'id_venta' => $sale->id,
                'id_caja' => $sale->id_caja,
                'id_usuario' => $userId,
                'monto' => $amount,
                'monto_efectivo' => $cashBack,
                'monto_qr' => $qrBack,
                'motivo' => $reason,
            ]);

            foreach ($lines as $l) {
                /** @var DetalleVenta $d */
                $d = DetalleVenta::whereKey($l['id'])->lockForUpdate()->firstOrFail();

                DevolucionDetalle::create([
                    'id_devolucion' => $dev->id,
                    'id_detalle_venta' => $d->id,
                    'id_producto' => $d->id_producto,
                    'id_servicio' => $d->id_servicio,
                    'id_lote' => $d->id_lote,
                    'cantidad' => $l['qty'],
                    'precio_venta' => $d->precio_venta,
                    'subtotal' => $l['subtotal'],
                ]);

                // La línea queda con lo que realmente se llevó.
                $d->quantity = (int) $d->quantity - $l['qty'];
                $d->cantidad_devuelta = (int) $d->cantidad_devuelta + $l['qty'];
                if ($d->id_servicio) {
                    $d->cantidad_servicio = max(0, (float) $d->cantidad_servicio - $l['qty']);
                }
                $d->subtotal = round($d->quantity * (float) $d->precio_venta, 2);
                $d->save();

                // Stock de vuelta a su mismo lote.
                if ($d->id_lote) {
                    $lot = Lote::whereKey($d->id_lote)->lockForUpdate()->firstOrFail();
                    $before = (int) $lot->cantidad_disponible;
                    $lot->cantidad_disponible = $before + $l['qty'];
                    $lot->save();

                    MovimientoInventario::create([
                        'id_producto' => $d->id_producto,
                        'id_lote' => $lot->id,
                        'id_usuario' => $userId,
                        'tipo_movimiento' => 'RETURN',
                        'reference_type' => 'SALE_RETURN',
                        'reference_id' => $dev->id,
                        'quantity' => $l['qty'],
                        'stock_before' => $before,
                        'stock_after' => $lot->cantidad_disponible,
                        'descripcion' => "Devolución de {$l['qty']} u. de la venta #{$sale->id}. Motivo: {$reason}",
                    ]);
                }
            }

            // Venta con total NETO.
            $sale->subtotal = round((float) $sale->subtotal - $amount, 2);
            $sale->total = round((float) $sale->total - $amount, 2);
            $sale->monto_efectivo = round($sale->cashPart() - $cashBack, 2);
            $sale->monto_qr = round($sale->qrPart() - $qrBack, 2);
            $sale->monto_devuelto = round((float) $sale->monto_devuelto + $amount, 2);
            $sale->save();

            // Movimiento automático de la venta (informativo) con el nuevo total.
            MovimientoCaja::where('reference_type', 'SALE')->where('reference_id', $sale->id)
                ->update(['monto' => $sale->total]);

            $this->recalculateCaja($sale, $dev, $lines);

            return $dev;
        });
    }

    /** Líneas válidas a devolver: [[id, name, qty, price, subtotal]], total. */
    private function lines(Venta $sale, array $qtyByDetail, bool $strict): array
    {
        $lines = [];
        $amount = 0.0;

        foreach ($sale->details()->with(['product', 'service'])->get() as $d) {
            $qty = (int) ($qtyByDetail[$d->id] ?? 0);
            if ($qty <= 0) continue;

            if ($qty > (int) $d->quantity) {
                if ($strict) {
                    throw new RuntimeException('No puede devolver más de lo vendido (' . ($d->product?->nombre ?? $d->service?->nombre) . ': máximo ' . $d->quantity . ').');
                }
                $qty = (int) $d->quantity;
            }

            $sub = round($qty * (float) $d->precio_venta, 2);
            $amount += $sub;
            $lines[] = [
                'id' => $d->id,
                'name' => $d->product?->nombre ?? ('SERVICIO: ' . $d->service?->nombre),
                'qty' => $qty,
                'price' => (float) $d->precio_venta,
                'subtotal' => $sub,
            ];
        }

        return [$lines, round($amount, 2)];
    }

    /** Reparto de lo devuelto: Efectivo / QR; Mixto descuenta primero efectivo. */
    private function split(Venta $sale, float $amount): array
    {
        $cash = $sale->cashPart();
        $cashBack = round(min($amount, $cash), 2);

        return [$cashBack, round($amount - $cashBack, 2)];
    }

    /** Recalcula la caja de la venta (abierta o cerrada) y deja nota. */
    private function recalculateCaja(Venta $sale, Devolucion $dev, array $lines): void
    {
        $caja = $sale->id_caja ? Caja::whereKey($sale->id_caja)->lockForUpdate()->first() : null;
        if (!$caja) return;

        $before = (float) $caja->monto_esperado;
        $b = $caja->cashBreakdown();
        $caja->monto_esperado = $b['expected_cash'];

        $extra = '';
        if ($caja->status === 'CLOSED') {
            $oldDiff = (float) $caja->diferencia;
            if ($caja->monto_cierre !== null) {
                $caja->diferencia = round((float) $caja->monto_cierre - $b['expected_cash'], 2);
            }
            if ($caja->monto_qr_verificado !== null) {
                $caja->monto_qr_esperado = $b['qr_sales'];
                $caja->diferencia_qr = round((float) $caja->monto_qr_verificado - $b['qr_sales'], 2);
            }
            $extra = sprintf(' Caja cerrada recalculada: esperado %s -> %s, diferencia %s -> %s.',
                number_format($before, 2), number_format($b['expected_cash'], 2),
                number_format($oldDiff, 2), number_format((float) $caja->diferencia, 2));
        }

        $items = collect($lines)->map(fn ($l) => "{$l['qty']}x {$l['name']}")->implode(', ');
        $pago = $dev->monto_qr > 0 && $dev->monto_efectivo > 0
            ? 'efectivo ' . number_format($dev->monto_efectivo, 2) . ' + QR ' . number_format($dev->monto_qr, 2)
            : ($dev->monto_qr > 0 ? 'QR' : 'efectivo');
        $nota = '[' . now()->format('d/m/Y H:i') . ' ' . ($dev->user?->name ?? 'admin') . '] '
            . "Devolución venta #{$sale->id}: {$items} = " . number_format($dev->monto, 2) . " Bs ({$pago}). Motivo: " . rtrim($dev->motivo, '. ') . ".{$extra}";

        $caja->observacion = trim(($caja->observacion ? $caja->observacion . "\n" : '') . $nota);
        $caja->save();
    }
}
