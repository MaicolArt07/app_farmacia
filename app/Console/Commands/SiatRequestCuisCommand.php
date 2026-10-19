<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;

/**
 * Pide el CUIS (Código Único de Inicio de Sistemas) al servicio SOAP del SIN
 * (ambiente Piloto), usando el Token Delegado generado manualmente en el
 * portal SIAT en Línea.
 *
 * Referencia: https://siatinfo.impuestos.gob.bo/index.php/facturacion-en-linea/implementacion-servicios-facturacion/codigos/solicitud-cuis
 */
class SiatRequestCuisCommand extends Command
{
    protected $signature = 'pharmacy:siat-cuis
        {--dry : Solo muestra los datos que se enviarían, sin llamar al servicio}
        {--sucursal=0 : Código de sucursal (0 = casa matriz)}
        {--punto-venta=0 : Código de punto de venta}';

    protected $description = 'Solicita el CUIS al SIN (ambiente Piloto) usando los datos de la Empresa y el Token Delegado del .env.';

    public function handle(): int
    {
        $company = Company::current();

        if (!$company) {
            $this->error('No hay datos de Empresa registrados. Complete el formulario en /company primero.');
            return self::FAILURE;
        }

        if (!$company->codigo_sistema) {
            $this->error('La Empresa no tiene "codigo_sistema" guardado. Péguelo en /company (lo asigna el SIN al registrar el sistema).');
            return self::FAILURE;
        }

        if (!$company->wsdl_codigos) {
            $this->error('La Empresa no tiene "wsdl_codigos" guardado. Péguelo en /company (viene en el reporte de registro del sistema).');
            return self::FAILURE;
        }

        $token = config('facturacion.token_delegado');

        if (!$token) {
            $this->error('No hay FACTURACION_TOKEN_DELEGADO configurado en el .env. Genere el Token Delegado Piloto en el portal SIAT y péguelo ahí.');
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
            'nit' => (int) $company->nit,
        ];

        $this->info('Datos de la solicitud CUIS:');
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

            $response = $client->cuis(['SolicitudCuis' => $solicitud]);

            $this->info('--- Respuesta cruda del servicio ---');
            $this->line(print_r($response, true));

            $respuestaCuis = $response->RespuestaCuis ?? null;

            if ($respuestaCuis && !empty($respuestaCuis->codigo)) {
                $company->cuis = $respuestaCuis->codigo;
                $company->save();

                $this->info('✅ CUIS obtenido y guardado en Empresa: ' . $respuestaCuis->codigo);

                if (!empty($respuestaCuis->fechaVigencia)) {
                    $this->line('Vigencia hasta: ' . $respuestaCuis->fechaVigencia);
                }
            } else {
                $this->warn('El servicio respondió pero no devolvió un código CUIS. Revise los mensajes de arriba.');
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
