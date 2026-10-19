<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Modo de facturación
    |--------------------------------------------------------------------------
    |
    | "simulado": genera facturas de prueba localmente, sin contactar al SIN.
    | Funciona sin configurar nada más — es el modo por defecto para poder
    | probar todo el flujo de venta -> factura -> impresión hoy mismo.
    |
    | "real": usa la integración real con el Servicio de Impuestos Nacionales
    | (Bolivia). Requiere completar App\Services\Facturacion\SinFacturacionService
    | con las credenciales y el WSDL que el SIN asigne al NIT del contribuyente
    | (Ficha Técnica de su modalidad "Facturación Computarizada").
    |
    */
    'modo' => env('FACTURACION_MODO', 'simulado'),

    'ambiente' => env('FACTURACION_AMBIENTE', 'PRUEBA'), // PRUEBA | PRODUCCION

    'emisor' => [
        'nit' => env('FACTURACION_NIT'),
        'razon_social' => env('FACTURACION_RAZON_SOCIAL'),
        'sucursal' => env('FACTURACION_SUCURSAL', 0),
        'punto_venta' => env('FACTURACION_PUNTO_VENTA', 0),
        'codigo_sistema' => env('FACTURACION_CODIGO_SISTEMA'),
        'cuis' => env('FACTURACION_CUIS'),
        'cufd' => env('FACTURACION_CUFD'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Token Delegado (SIAT)
    |--------------------------------------------------------------------------
    |
    | Requerido por los servicios SOAP de facturación (CUIS, CUFD, etc.) para
    | validar la autenticidad del contribuyente. Se genera manualmente en el
    | portal SIAT en Línea ("Gestor Token Delegado Piloto" / "en Producción")
    | y se envía en cada llamada SOAP como header HTTP:
    |   apikey: TokenApi <token>
    |
    */
    'token_delegado' => env('FACTURACION_TOKEN_DELEGADO'),

    'consumidor_final' => [
        'nit' => '0',
        'razon_social' => env('FACTURACION_CF_RAZON_SOCIAL', 'SIN NOMBRE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Integración real (pendiente)
    |--------------------------------------------------------------------------
    |
    | Intencionalmente vacío de endpoints/WSDL. Esos valores deben salir de la
    | Ficha Técnica real que el SIN entregue para el NIT del contribuyente,
    | no de una suposición. Complete estos valores recién antes de activar
    | modo=real.
    |
    */
    'real' => [
        'wsdl_endpoint' => env('FACTURACION_SIN_WSDL_ENDPOINT'),
        'timeout' => env('FACTURACION_SIN_TIMEOUT', 30),
    ],
];
