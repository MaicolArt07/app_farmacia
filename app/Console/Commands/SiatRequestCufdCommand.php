<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;

/**
 * Pide el CUFD (Código Único de Facturación Diaria) al servicio SOAP del SIN
 * (ambiente Piloto). Requiere que la Empresa ya tenga un CUIS vigente
 * (ver pharmacy:siat-cuis) y el Token Delegado configurado en el .env.
 *
 * El CUFD tiene vigencia de 24 horas: este comando debe correr a diario
 * (vía `php artisan schedule`) una vez se integre al flujo real de facturación.
 *
 * Referencia: https://siatinfo.impuestos.gob.bo/index.php/facturacion-en-linea/implementacion-servicios-facturacion/codigos/solicitud-cufd
 */
class SiatRequestCufdCommand extends Command
{
    protected $signature = 'pharmacy:siat-cufd
        {--dry : Solo muestra los datos que se enviarían, sin llamar al servicio}
        {--sucursal=0 : Código de sucursal (0 = casa matriz)}
        {--punto-venta=0 : Código de punto de venta}';

    protected $description = 'Solicita el CUFD al SIN (ambiente Piloto) usando el CUIS vigente de la Empresa y el Token Delegado del .env.';

    public function handle(): int
    {
        $company = Company::current();

        if (!$company) {
            $this->error('No hay datos de Empresa registrados. Complete el formulario en /company primero.');
            return self::FAILURE;
        }

        if (!$company->codigo_sistema || !$company->cuis) {
            $this->error('Falta "codigo_sistema" o "cuis" en la Empresa. Corra primero: php artisan pharmacy:siat-cuis');
            return self::FAILURE;
        }

        if (!$company->wsdl_codigos) {
            $this->error('La Empresa no tiene "wsdl_codigos" guardado.');
            return self::FAILURE;
        }

        $token = config('facturacion.token_delegado');

        if (!$token) {
            $this->error('No hay FACTURACION_TOKEN_DELEGADO configurado en el .env.');
            return self::FAILURE;
        }

        $ambienteCodigo = $company->ambiente === 'PRODUCCION' ? 1 : 2; // 1=Producción, 2=Pruebas y Piloto
        $modalidadCodigo = 2; // Computarizada en Línea

        $solicitud = [
            'codigoAmbiente' => $ambienteCodigo,
            'codigoModalidad' => $modalidadCodigo,
            'codigoPuntoVenta' => (int) $this->option('punto-venta'),
            'codigoSistema' => $company->codigo_sistema,
            'codigoSucursal' => (int) $this->option('sucursal'),
            'cuis' => $company->cuis,
            'nit' => (int) $company->nit,
        ];

        $this->info('Datos de la solicitud CUFD:');
        $this->table(array_keys($solicitud), [array_values($solicitud)]);
        $this->line('WSDL: ' . $company->wsdl_codigos);

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

            $client = new \SoapClient($company->wsdl_codigos, [
                'trace' => 1,
                'exceptions' => true,
                'stream_context' => $context,
                'connection_timeout' => 30,
                'cache_wsdl' => WSDL_CACHE_NONE,
            ]);

            $response = $client->cufd(['SolicitudCufd' => $solicitud]);

            $this->info('--- Respuesta cruda del servicio ---');
            $this->line(print_r($response, true));

            $respuestaCufd = $response->RespuestaCufd ?? null;

            if ($respuestaCufd && !empty($respuestaCufd->codigo)) {
                // El CUFD vence cada 24h: se cachea, no se guarda como dato fijo de Empresa.
                cache()->put('facturacion.cufd', [
                    'codigo' => $respuestaCufd->codigo,
                    'codigo_control' => $respuestaCufd->codigoControl ?? null,
                    'direccion' => $respuestaCufd->direccion ?? null,
                    'fecha_vigencia' => $respuestaCufd->fechaVigencia ?? null,
                ], now()->addHours(23));

                $this->info('✅ CUFD obtenido y guardado en caché (23h): ' . $respuestaCufd->codigo);
                $this->line('Código de control: ' . ($respuestaCufd->codigoControl ?? '-'));
                $this->line('Vigencia hasta: ' . ($respuestaCufd->fechaVigencia ?? '-'));
            } else {
                $this->warn('El servicio respondió pero no devolvió un código CUFD. Revise los mensajes de arriba.');
            }

            return self::SUCCESS;
        } catch (\SoapFault $e) {
            $this->error('SoapFault: ' . $e->getMessage());

            if (isset($client)) {
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
