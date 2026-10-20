<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\Venta;
use App\Services\Facturacion\FacturaXmlBuilder;
use Illuminate\Console\Command;

/**
 * Arma (sin enviar al SIN) el XML de una venta existente, lo valida contra el
 * XSD oficial local (resources/facturacion/xsd/), y muestra el hash SHA-256
 * que se enviaría como "Huella Digital". Útil para probar el generador antes
 * de gastar un envío real contra el servicio del SIN.
 */
class SiatPreviewFacturaXmlCommand extends Command
{
    protected $signature = 'pharmacy:siat-preview-xml
        {sale : ID de una venta existente}
        {--save= : Ruta donde guardar el XML generado (opcional)}';

    protected $description = 'Genera y valida localmente el XML de factura para una venta, sin enviarlo al SIN.';

    public function handle(): int
    {
        $sale = Venta::with(['details.product.sinProductCode', 'details.product.presentation', 'client.person', 'paymentMethod', 'user'])
            ->find($this->argument('sale'));

        if (!$sale) {
            $this->error('No existe una venta con ese ID.');
            return self::FAILURE;
        }

        if ($sale->details->isEmpty()) {
            $this->error('Esa venta no tiene detalle de productos.');
            return self::FAILURE;
        }

        $faltantes = $sale->details->filter(fn ($d) => !$d->product?->id_sin_product_code);

        if ($faltantes->isNotEmpty()) {
            $this->warn('⚠️ Los siguientes productos de esta venta NO tienen código SIN asignado (se enviaría codigoProductoSin=0, inválido):');
            foreach ($faltantes as $d) {
                $this->line(' - ' . ($d->product?->nombre ?? 'producto #' . $d->id_product));
            }
        }

        $company = Company::current();

        if (!$company) {
            $this->error('No hay datos de Empresa registrados.');
            return self::FAILURE;
        }

        $cufdCache = cache()->get('facturacion.cufd');

        if (!$cufdCache) {
            $this->error('No hay un CUFD vigente en caché. Corra primero: php artisan pharmacy:siat-cufd');
            return self::FAILURE;
        }

        $numeroFactura = (int) (Invoice::max('numero_factura') ?? 0) + 1;

        try {
            $builder = new FacturaXmlBuilder($sale, $company, $cufdCache['codigo'], $numeroFactura);
            $resultado = $builder->build();
        } catch (\Throwable $e) {
            $this->error('Error al armar el XML: ' . $e->getMessage());
            return self::FAILURE;
        }

        $xml = $resultado['xml'];
        $cuf = $resultado['cuf'];

        $this->info('CUF generado: ' . $cuf);
        $this->newLine();
        $this->line($xml);

        // Validación local contra el XSD oficial
        $xsdPath = resource_path('facturacion/xsd/facturaComputarizadaCompraVenta.xsd');

        if (file_exists($xsdPath)) {
            libxml_use_internal_errors(true);
            $dom = new \DOMDocument();
            $dom->loadXML($xml);

            if ($dom->schemaValidate($xsdPath)) {
                $this->info('✅ XML válido contra el XSD oficial del SIN.');
            } else {
                $this->error('❌ El XML NO pasa la validación contra el XSD oficial:');
                foreach (libxml_get_errors() as $error) {
                    $this->line('  - ' . trim($error->message));
                }
            }
            libxml_clear_errors();
        } else {
            $this->warn('No se encontró el XSD local en ' . $xsdPath . ' — no se pudo validar.');
        }

        // Cálculo de lo que se enviaría al SIN (Gzip + SHA-256), solo informativo.
        $comprimido = gzencode($xml, 9);
        $hash = hash('sha256', $comprimido);

        $this->newLine();
        $this->line('Tamaño XML: ' . strlen($xml) . ' bytes | Comprimido (Gzip): ' . strlen($comprimido) . ' bytes');
        $this->line('hashArchivo (SHA-256, "Huella Digital"): ' . $hash);

        if ($path = $this->option('save')) {
            file_put_contents($path, $xml);
            $this->info('Guardado en: ' . $path);
        }

        return self::SUCCESS;
    }
}
