<?php

namespace App\Services\Facturacion;

use App\Models\Invoice;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;

/**
 * Genera facturas de prueba localmente, sin contactar al SIN. Permite probar
 * todo el flujo venta -> factura -> impresión sin credenciales reales.
 */
class SimulatedFacturacionService implements FacturacionServiceInterface
{
    public function generar(Sale $sale, array $datosCliente = []): Invoice
    {
        // Se consulta directo (sin depender de la relación cacheada en $sale)
        // para evitar re-insertar sobre un id_sale único si el caller reutiliza
        // la misma instancia de Sale entre llamadas.
        $existing = Invoice::where('id_sale', $sale->id)->first();

        if ($existing && $existing->estado === 'ENVIADA') {
            return $existing;
        }

        $cliente = $this->resolveClienteData($sale, $datosCliente);

        $invoice = $existing ?: new Invoice(['id_sale' => $sale->id]);

        $invoice->fill([
            'id_user' => Auth::id(),
            'modo' => 'SIMULADO',
            'ambiente' => config('facturacion.ambiente', 'PRUEBA'),
            'tipo_documento_identidad' => $cliente['tipo_documento_identidad'],
            'nit_cliente' => $cliente['nit'],
            'razon_social' => $cliente['razon_social'],
            'numero_factura' => $invoice->numero_factura ?: $this->nextNumeroFactura(),
            'total' => $sale->total,
            'estado' => 'ENVIADA',
            'observaciones' => 'Factura simulada generada para pruebas. No tiene validez fiscal.',
        ]);

        $invoice->cuf = $invoice->cuf ?: $this->fakeCuf($sale);
        $invoice->cuis = config('facturacion.emisor.cuis') ?: 'SIMULADO';
        $invoice->cufd = config('facturacion.emisor.cufd') ?: 'SIMULADO';
        $invoice->codigo_control = $invoice->codigo_control ?: strtoupper(substr(md5($invoice->cuf), 0, 8));

        $invoice->save();

        return $invoice;
    }

    public function anular(Invoice $invoice, string $motivo): Invoice
    {
        $invoice->estado = 'ANULADA';
        $invoice->anulado_at = now();
        $invoice->anulado_reason = $motivo;
        $invoice->save();

        return $invoice;
    }

    private function resolveClienteData(Sale $sale, array $override): array
    {
        $nit = $override['nit'] ?? null;
        $razonSocial = $override['razon_social'] ?? null;
        $tipoDocumento = $override['tipo_documento_identidad'] ?? null;

        if (!$nit && !$razonSocial && $sale->client) {
            $client = $sale->client;
            $person = $client->person;

            $nit = $client->nit ?: ($person->ci ?? null);
            $razonSocial = $person ? trim($person->name . ' ' . $person->lastname) : null;
            $tipoDocumento = $tipoDocumento ?? ($client->nit ? 'NIT' : 'CI');
        }

        if (!$nit || !$razonSocial) {
            $nit = config('facturacion.consumidor_final.nit');
            $razonSocial = config('facturacion.consumidor_final.razon_social');
            $tipoDocumento = 'OTRO';
        }

        return [
            'nit' => $nit,
            'razon_social' => $razonSocial,
            'tipo_documento_identidad' => $tipoDocumento,
        ];
    }

    private function fakeCuf(Sale $sale): string
    {
        return 'SIM-' . now()->format('YmdHis') . '-' . str_pad((string) $sale->id, 6, '0', STR_PAD_LEFT);
    }

    private function nextNumeroFactura(): int
    {
        return (int) (Invoice::max('numero_factura') ?? 0) + 1;
    }
}
