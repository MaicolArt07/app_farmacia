<?php

namespace App\Console\Commands;

use App\Models\Empresa;
use App\Models\CodigoProductoSin;
use Illuminate\Console\Command;

/**
 * Descarga y guarda en BD el catálogo de "Productos y Servicios" del SIN
 * (específico para la actividad económica registrada), necesario porque el
 * campo `codigoProductoSin` del XML de factura es obligatorio y debe salir
 * de este catálogo oficial (homologado por el SIN), no inventado.
 *
 * Referencia: https://siatinfo.impuestos.gob.bo/index.php/facturacion-en-linea/archivos-xml-xsd-de-facturas-electronicas/factura-de-compra-y-venta
 */
class SiatSyncProductCodesCommand extends Command
{
    protected $signature = 'pharmacy:siat-sync-productos
        {--sucursal=0 : Código de sucursal (0 = casa matriz)}
        {--punto-venta=0 : Código de punto de venta}';

    protected $description = 'Sincroniza y guarda en BD el catálogo oficial de códigos de producto del SIN (sincronizarListaProductosServicios).';

    public function handle(): int
    {
        $company = Empresa::current();

        if (!$company || !$company->codigo_sistema || !$company->cuis || !$company->wsdl_sincronizacion) {
            $this->error('Falta Empresa, "codigo_sistema", "cuis" o "wsdl_sincronizacion". Corra primero: php artisan pharmacy:siat-cuis');
            return self::FAILURE;
        }

        $token = config('facturacion.token_delegado');

        if (!$token) {
            $this->error('No hay FACTURACION_TOKEN_DELEGADO configurado en el .env.');
            return self::FAILURE;
        }

        $ambienteCodigo = $company->ambiente === 'PRODUCCION' ? 1 : 2;

        $solicitud = [
            'codigoAmbiente' => $ambienteCodigo,
            'codigoPuntoVenta' => (int) $this->option('punto-venta'),
            'codigoSistema' => $company->codigo_sistema,
            'codigoSucursal' => (int) $this->option('sucursal'),
            'cuis' => $company->cuis,
            'nit' => (int) $company->nit,
        ];

        try {
            $context = stream_context_create([
                'http' => [
                    'header' => "apikey: TokenApi {$token}\r\n",
                ],
            ]);

            $client = new \SoapClient($company->wsdl_sincronizacion, [
                'trace' => 1,
                'exceptions' => true,
                'stream_context' => $context,
                'connection_timeout' => 30,
                'cache_wsdl' => WSDL_CACHE_NONE,
            ]);

            $response = $client->__soapCall('sincronizarListaProductosServicios', [['SolicitudSincronizacion' => $solicitud]]);

            $lista = $response->RespuestaListaProductos->listaCodigos ?? [];

            // El SOAP devuelve un objeto único (no array) cuando solo hay un resultado.
            if (is_object($lista)) {
                $lista = [$lista];
            }

            $this->info('Productos recibidos: ' . count($lista));

            $bar = $this->output->createProgressBar(count($lista));
            $bar->start();

            foreach ($lista as $item) {
                $nandina = $item->nandina ?? null;

                if ($nandina !== null && !is_array($nandina)) {
                    $nandina = [$nandina];
                }

                CodigoProductoSin::updateOrCreate(
                    [
                        'codigo_actividad' => $item->codigoActividad,
                        'codigo_producto' => $item->codigoProducto,
                    ],
                    [
                        'descripcion' => $item->descripcionProducto,
                        'nandina' => $nandina,
                    ]
                );

                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);
            $this->info('✅ Catálogo de productos del SIN guardado/actualizado en la base de datos.');

            return self::SUCCESS;
        } catch (\SoapFault $e) {
            $this->error('SoapFault: ' . $e->getMessage());
            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->error('Error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
