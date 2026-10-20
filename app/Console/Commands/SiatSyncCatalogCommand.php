<?php

namespace App\Console\Commands;

use App\Models\Empresa;
use Illuminate\Console\Command;

/**
 * Sincroniza cualquier catálogo del servicio FacturacionSincronizacion del SIN
 * (Etapa II de la Fase 1 de pruebas). Todas las operaciones de este servicio
 * comparten la misma estructura de solicitud (SolicitudSincronizacion), por
 * lo que un solo comando genérico cubre los ~19 catálogos disponibles.
 *
 * Ejemplos:
 *   php artisan pharmacy:siat-sync sincronizarParametricaUnidadMedida
 *   php artisan pharmacy:siat-sync sincronizarListaProductosServicios
 *   php artisan pharmacy:siat-sync sincronizarFechaHora
 *
 * Referencia: https://siatinfo.impuestos.gob.bo/index.php/facturacion-en-linea/implementacion-servicios-facturacion/sincronizacion-codigos-catalogos
 */
class SiatSyncCatalogCommand extends Command
{
    protected $signature = 'pharmacy:siat-sync
        {operacion : Nombre de la operación SOAP (ej. sincronizarParametricaUnidadMedida)}
        {--dry : Solo muestra los datos que se enviarían, sin llamar al servicio}
        {--sucursal=0 : Código de sucursal (0 = casa matriz)}
        {--punto-venta=0 : Código de punto de venta}';

    protected $description = 'Sincroniza un catálogo del SIN (ambiente Piloto) usando el servicio FacturacionSincronizacion.';

    public function handle(): int
    {
        $company = Empresa::current();

        if (!$company || !$company->codigo_sistema || !$company->cuis) {
            $this->error('Falta Empresa, "codigo_sistema" o "cuis". Corra primero: php artisan pharmacy:siat-cuis');
            return self::FAILURE;
        }

        if (!$company->wsdl_sincronizacion) {
            $this->error('La Empresa no tiene "wsdl_sincronizacion" guardado.');
            return self::FAILURE;
        }

        $token = config('facturacion.token_delegado');

        if (!$token) {
            $this->error('No hay FACTURACION_TOKEN_DELEGADO configurado en el .env.');
            return self::FAILURE;
        }

        $operacion = $this->argument('operacion');
        $ambienteCodigo = $company->ambiente === 'PRODUCCION' ? 1 : 2;

        $solicitud = [
            'codigoAmbiente' => $ambienteCodigo,
            'codigoPuntoVenta' => (int) $this->option('punto-venta'),
            'codigoSistema' => $company->codigo_sistema,
            'codigoSucursal' => (int) $this->option('sucursal'),
            'cuis' => $company->cuis,
            'nit' => (int) $company->nit,
        ];

        $this->info("Operación: {$operacion}");
        $this->table(array_keys($solicitud), [array_values($solicitud)]);

        if ($this->option('dry')) {
            $this->warn('Modo --dry: no se llamó al servicio.');
            return self::SUCCESS;
        }

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

            $response = $client->__soapCall($operacion, [['SolicitudSincronizacion' => $solicitud]]);

            $this->info('--- Respuesta cruda del servicio ---');
            $this->line(print_r($response, true));

            return self::SUCCESS;
        } catch (\SoapFault $e) {
            $this->error('SoapFault: ' . $e->getMessage());

            if (isset($client)) {
                $this->line('--- Operaciones disponibles en este WSDL ---');
                foreach ($client->__getFunctions() ?? [] as $f) {
                    $this->line($f);
                }
                $this->line('--- Request XML enviado ---');
                $this->line($client->__getLastRequest() ?: '(vacío)');
                $this->line('--- Response XML recibido ---');
                $this->line($client->__getLastResponse() ?: '(vacío)');
            }

            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->error('Error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
