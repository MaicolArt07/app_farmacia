<?php

namespace App\Services\Facturacion;

use App\Models\Empresa;
use App\Models\Factura;
use App\Models\Venta;
use Illuminate\Support\Facades\Auth;

/**
 * Genera facturas de prueba localmente, sin contactar al SIN. Permite probar
 * todo el flujo venta -> factura -> impresión sin credenciales reales.
 */
class SimulatedFacturacionService implements FacturacionServiceInterface
{
    public function generar(Venta $sale, array $datosCliente = []): Factura
    {
        // Se consulta directo (sin depender de la relación cacheada en $sale)
        // para evitar re-insertar sobre un id_sale único si el caller reutiliza
        // la misma instancia de Sale entre llamadas.
        $existing = Factura::where('id_sale', $sale->id)->first();

        if ($existing && $existing->estado === 'ENVIADA') {
            return $existing;
        }

        $cliente = $this->resolveClienteData($sale, $datosCliente);

        $invoice = $existing ?: new Factura(['id_sale' => $sale->id]);

        $company = Empresa::current();

        $invoice->fill([
            'id_user' => Auth::id(),
            'modo' => 'SIMULADO',
            'ambiente' => $company->ambiente ?? config('facturacion.ambiente', 'PRUEBA'),
            'tipo_documento_identidad' => $cliente['tipo_documento_identidad'],
            'nit_cliente' => $cliente['nit'],
            'razon_social' => $cliente['razon_social'],
            'numero_factura' => $invoice->numero_factura ?: $this->nextNumeroFactura(),
            'total' => $sale->total,
            'estado' => 'ENVIADA',
            'observaciones' => 'Factura simulada generada para pruebas. No tiene validez fiscal.',
        ]);

        $invoice->cuf = $invoice->cuf ?: $this->fakeCuf($sale);
        $invoice->cuis = ($company->cuis ?? config('facturacion.emisor.cuis')) ?: 'SIMULADO';
        $invoice->cufd = config('facturacion.emisor.cufd') ?: 'SIMULADO';
        $invoice->codigo_control = $invoice->codigo_control ?: strtoupper(substr(md5($invoice->cuf), 0, 8));

        $invoice->save();

        return $invoice;
    }

    public function anular(Factura $invoice, string $motivo): Factura
    {
        $invoice->estado = 'ANULADA';
        $invoice->anulado_at = now();
        $invoice->anulado_reason = $motivo;
        $invoice->save();

        return $invoice;
    }

    private function resolveClienteData(Venta $sale, array $override): array
    {
        $nit = $override['nit'] ?? null;
        $razonSocial = $override['razon_social'] ?? null;
        $tipoDocumento = $override['tipo_documento_identidad'] ?? null;

        if (!$nit && !$razonSocial && $sale->client) {
            $client = $sale->client;
            $person = $client->person;

            $nit = $client->nit ?: ($person->ci ?? null);
            $razonSocial = $person ? trim($person->nombre . ' ' . $person->apellido) : null;
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

    private function fakeCuf(Venta $sale): string
    {
        return 'SIM-' . now()->format('YmdHis') . '-' . str_pad((string) $sale->id, 6, '0', STR_PAD_LEFT);
    }

    private function nextNumeroFactura(): int
    {
        return (int) (Factura::max('numero_factura') ?? 0) + 1;
    }
}
