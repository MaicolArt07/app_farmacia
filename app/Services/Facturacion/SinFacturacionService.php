<?php

namespace App\Services\Facturacion;

use App\Models\Invoice;
use App\Models\Sale;

/**
 * Punto de extensión para la integración real con el Servicio de Impuestos
 * Nacionales (Bolivia), modalidad "Facturación Computarizada".
 *
 * Deliberadamente NO implementado: los endpoints WSDL, el formato exacto del
 * paquete/XML y las reglas de CUIS/CUFD/CUF deben salir de la Ficha Técnica
 * que el SIN asigne al NIT del contribuyente, no de una suposición. Complete
 * esta clase con esos datos reales (típicamente vía \SoapClient, incluido en
 * PHP, sin necesidad de una librería adicional) antes de activar
 * FACTURACION_MODO=real en el .env.
 *
 * Flujo general esperado (a confirmar contra la documentación oficial):
 *   1. Obtener CUIS (una vez, por sistema/punto de venta).
 *   2. Obtener CUFD (vigencia diaria).
 *   3. Armar y enviar el paquete de la factura.
 *   4. Recibir CUF y código de control.
 *   5. Generar la representación gráfica (rollo/carta) con esos datos.
 */
class SinFacturacionService implements FacturacionServiceInterface
{
    public function generar(Sale $sale, array $datosCliente = []): Invoice
    {
        throw new \RuntimeException(
            'Integración real con el SIN pendiente. Configure NIT, sucursal, punto de venta, ' .
            'código de sistema, CUIS y CUFD en config/facturacion.php (o el .env), y complete ' .
            'App\\Services\\Facturacion\\SinFacturacionService con el cliente SOAP/REST hacia el ' .
            'WSDL que el SIN asigne a su modalidad "Facturación Computarizada" antes de usar ' .
            'FACTURACION_MODO=real.'
        );
    }

    public function anular(Invoice $invoice, string $motivo): Invoice
    {
        throw new \RuntimeException(
            'Integración real con el SIN pendiente. Ver App\\Services\\Facturacion\\SinFacturacionService::generar().'
        );
    }
}
