<?php

namespace App\Services\Facturacion;

use App\Models\Empresa;
use App\Models\Venta;
use Illuminate\Support\Carbon;

/**
 * Arma el XML de "Factura Computarizada Compra-Venta" según el XSD oficial
 * del SIN (facturaComputarizadaCompraVenta.xsd — ver resources/facturacion/xsd/).
 *
 * Referencia: https://siatinfo.impuestos.gob.bo/index.php/facturacion-en-linea/archivos-xml-xsd-de-facturas-electronicas/factura-de-compra-y-venta
 *
 * No envía nada al SIN — solo arma el documento. El envío real (Gzip + hash +
 * SOAP) va en SinFacturacionService.
 */
class FacturaXmlBuilder
{
    public const CODIGO_DOCUMENTO_SECTOR = 1; // FCV, fijo para nuestra actividad (4772100)

    private Venta $sale;
    private Empresa $company;
    private string $cufd;
    private int $numeroFactura;
    private Carbon $fechaEmision;

    public function __construct(Venta $sale, Empresa $company, string $cufd, int $numeroFactura, ?Carbon $fechaEmision = null)
    {
        $this->sale = $sale;
        $this->company = $company;
        $this->cufd = $cufd;
        $this->numeroFactura = $numeroFactura;
        $this->fechaEmision = $fechaEmision ?: now();
    }

    /**
     * @return array{xml: string, cuf: string}
     */
    public function build(): array
    {
        $branch = $this->company->mainBranch();

        if (!$branch) {
            throw new \RuntimeException('La Empresa no tiene ninguna Sucursal registrada. Registre al menos una en /branches antes de facturar.');
        }

        $sucursal = $branch->numero_sucursal ?? 0;
        $puntoVenta = $branch->punto_venta ?? 0;

        $cuf = CufGenerator::generate(
            $this->company->nit,
            $this->fechaEmision,
            $sucursal,
            CufGenerator::MODALIDAD_COMPUTARIZADA,
            CufGenerator::TIPO_EMISION_ONLINE,
            CufGenerator::DOCUMENTO_FISCAL_FACTURA,
            self::CODIGO_DOCUMENTO_SECTOR,
            $this->numeroFactura,
            $puntoVenta
        );

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $root = $dom->createElement('facturaComputarizadaCompraVenta');
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $root->setAttribute('xsi:noNamespaceSchemaLocation', 'facturaComputarizadaCompraVenta.xsd');
        $dom->appendChild($root);

        $root->appendChild($this->buildCabecera($dom, $cuf, $sucursal, $puntoVenta, $branch));

        foreach ($this->sale->details as $detail) {
            $root->appendChild($this->buildDetalle($dom, $detail));
        }

        return [
            'xml' => $dom->saveXML(),
            'cuf' => $cuf,
        ];
    }

    private function buildCabecera(\DOMDocument $dom, string $cuf, int $sucursal, int $puntoVenta, $branch): \DOMElement
    {
        $cabecera = $dom->createElement('cabecera');

        $client = $this->sale->client;
        $person = $client?->person;

        $nombreRazonSocial = $person
            ? trim($person->nombre . ' ' . $person->apellido)
            : config('facturacion.consumidor_final.razon_social', 'SIN NOMBRE');

        $codigoTipoDocumentoIdentidad = $client?->nit ? 5 : 1; // 5=NIT, 1=CI (ver catálogo sincronizado)
        $numeroDocumento = $client?->nit ?: ($person?->ci ?: '0');
        $codigoCliente = $client?->code ?: '0';

        $codigoMetodoPago = $this->sale->paymentMethod?->codigo_sin ?: 1;

        $this->appendText($dom, $cabecera, 'nitEmisor', (string) $this->company->nit);
        $this->appendText($dom, $cabecera, 'razonSocialEmisor', $this->company->razon_social);
        $this->appendText($dom, $cabecera, 'municipio', $branch->municipio ?? throw new \RuntimeException('La sucursal no tiene "municipio" configurado. Complételo en /branches antes de facturar.'));
        $this->appendNillableText($dom, $cabecera, 'telefono', $this->company->telefono);
        $this->appendText($dom, $cabecera, 'numeroFactura', (string) $this->numeroFactura);
        $this->appendText($dom, $cabecera, 'cuf', $cuf);
        $this->appendText($dom, $cabecera, 'cufd', $this->cufd);
        $this->appendText($dom, $cabecera, 'codigoSucursal', (string) $sucursal);
        $this->appendText($dom, $cabecera, 'direccion', $branch->direccion ?? $this->company->direccion ?? 'N/A');
        $this->appendNillableInt($dom, $cabecera, 'codigoPuntoVenta', $puntoVenta);
        $this->appendText($dom, $cabecera, 'fechaEmision', $this->fechaEmision->format('Y-m-d\TH:i:s.v'));
        $this->appendNillableText($dom, $cabecera, 'nombreRazonSocial', $nombreRazonSocial);
        $this->appendText($dom, $cabecera, 'codigoTipoDocumentoIdentidad', (string) $codigoTipoDocumentoIdentidad);
        $this->appendText($dom, $cabecera, 'numeroDocumento', (string) $numeroDocumento);
        $this->appendNillableText($dom, $cabecera, 'complemento', null);
        $this->appendText($dom, $cabecera, 'codigoCliente', (string) $codigoCliente);
        $this->appendText($dom, $cabecera, 'codigoMetodoPago', (string) $codigoMetodoPago);
        $this->appendNillableText($dom, $cabecera, 'numeroTarjeta', null);
        $this->appendText($dom, $cabecera, 'montoTotal', number_format((float) $this->sale->total, 2, '.', ''));
        $this->appendText($dom, $cabecera, 'montoTotalSujetoIva', number_format((float) $this->sale->total, 2, '.', ''));
        $this->appendText($dom, $cabecera, 'codigoMoneda', '1'); // 1 = Boliviano
        $this->appendText($dom, $cabecera, 'tipoCambio', '1.00');
        $this->appendText($dom, $cabecera, 'montoTotalMoneda', number_format((float) $this->sale->total, 2, '.', ''));
        $this->appendNillableText($dom, $cabecera, 'montoGiftCard', null);
        $this->appendNillableText($dom, $cabecera, 'descuentoAdicional', $this->sale->descuento > 0 ? number_format((float) $this->sale->descuento, 2, '.', '') : null);
        $this->appendNillableInt($dom, $cabecera, 'codigoExcepcion', null);
        $this->appendNillableText($dom, $cabecera, 'cafc', null);
        $this->appendText($dom, $cabecera, 'leyenda', $this->randomLeyenda());
        $this->appendText($dom, $cabecera, 'usuario', $this->sale->user?->name ?: 'SISTEMA');
        $this->appendText($dom, $cabecera, 'codigoDocumentoSector', (string) self::CODIGO_DOCUMENTO_SECTOR);

        return $cabecera;
    }

    private function buildDetalle(\DOMDocument $dom, $detail): \DOMElement
    {
        $detalle = $dom->createElement('detalle');
        $product = $detail->product;
        $sinCode = $product?->sinProductCode;

        $cantidad = (float) $detail->quantity;
        $precioUnitario = (float) $detail->precio_venta;
        $subTotal = (float) $detail->subtotal;

        $this->appendText($dom, $detalle, 'actividadEconomica', '4772100');
        $this->appendText($dom, $detalle, 'codigoProductoSin', (string) ($sinCode->codigo_producto ?? 0));
        $this->appendText($dom, $detalle, 'codigoProducto', (string) ($product->code ?? 'N/A'));
        $this->appendText($dom, $detalle, 'descripcion', $product->nombre ?? 'N/A');
        $this->appendText($dom, $detalle, 'cantidad', number_format($cantidad, 2, '.', ''));
        $this->appendText($dom, $detalle, 'unidadMedida', (string) ($product?->presentation?->codigo_sin ?? 57)); // 57 = UNIDAD (BIENES), respaldo
        $this->appendText($dom, $detalle, 'precioUnitario', number_format($precioUnitario, 2, '.', ''));
        $this->appendNillableText($dom, $detalle, 'montoDescuento', null);
        $this->appendText($dom, $detalle, 'subTotal', number_format($subTotal, 2, '.', ''));
        $this->appendNillableText($dom, $detalle, 'numeroSerie', null);
        $this->appendNillableText($dom, $detalle, 'numeroImei', null);

        return $detalle;
    }

    private function appendText(\DOMDocument $dom, \DOMElement $parent, string $name, string $value): void
    {
        $parent->appendChild($dom->createElement($name, htmlspecialchars($value, ENT_XML1)));
    }

    private function appendNillableText(\DOMDocument $dom, \DOMElement $parent, string $name, ?string $value): void
    {
        if ($value === null || $value === '') {
            $el = $dom->createElement($name);
            $el->setAttribute('xsi:nil', 'true');
            $parent->appendChild($el);
            return;
        }

        $this->appendText($dom, $parent, $name, $value);
    }

    private function appendNillableInt(\DOMDocument $dom, \DOMElement $parent, string $name, ?int $value): void
    {
        $this->appendNillableText($dom, $parent, $name, $value === null ? null : (string) $value);
    }

    private function randomLeyenda(): string
    {
        $leyendas = [
            'Ley N° 453: Tienes derecho a recibir información sobre las características y contenidos de los servicios que utilices.',
            'Ley N° 453: Tienes derecho a la protección de tus intereses económicos.',
            'Ley N° 453: Tienes derecho a elegir sin presión ni discriminación los bienes y servicios que necesites.',
        ];

        return $leyendas[array_rand($leyendas)];
    }
}
