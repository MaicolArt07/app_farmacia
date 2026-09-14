<?php

namespace App\Services\Facturacion;

use App\Models\Invoice;
use App\Models\Sale;

interface FacturacionServiceInterface
{
    /**
     * Genera (o reintenta) la factura de una venta.
     *
     * $datosCliente permite indicar NIT/razón social distintos a los del
     * cliente vinculado a la venta — cubre el caso de "consumidor final"
     * (sin cliente registrado) y el caso de una razón social ocasional
     * distinta a la del registro de cliente.
     *
     * Claves soportadas: nit, razon_social, tipo_documento_identidad.
     */
    public function generar(Sale $sale, array $datosCliente = []): Invoice;

    /**
     * Anula una factura ya generada.
     */
    public function anular(Invoice $invoice, string $motivo): Invoice;
}
