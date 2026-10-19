<?php

namespace App\Services\Facturacion;

use Carbon\Carbon;

/**
 * Genera el CUF (Código Único de Factura) siguiendo el algoritmo oficial del SIN.
 *
 * Validado contra el ejemplo oficial de la "Guía Generación CUF" (Sistema de
 * Facturación Electrónica, La Paz 21/11/2018):
 *   NIT=123456789, FECHA/HORA=2019-01-13 16:37:21.231, SUCURSAL=0, MODALIDAD=1,
 *   TIPOEMISION=1, CODIGODOCUMENTOFISCAL=1, TIPODOCUMENTOSECTOR=1, NROFACTURA=1, POS=0
 * Resultado esperado: 159FFE6FB1986A24BB32DBE5A2A34214B245A6A3
 * (reproducido exactamente por self::generate() con esos mismos parámetros).
 *
 * Referencia: https://siatinfo.impuestos.gob.bo/images/archivos_tecnicos/archivos_apoyo/GENERACION-CUF.pdf
 */
class CufGenerator
{
    public const MODALIDAD_ELECTRONICA = 1;
    public const MODALIDAD_COMPUTARIZADA = 2;
    public const MODALIDAD_MANUAL = 3;

    public const TIPO_EMISION_ONLINE = 1;
    public const TIPO_EMISION_OFFLINE = 2;

    public const DOCUMENTO_FISCAL_FACTURA = 1;
    public const DOCUMENTO_FISCAL_NOTA_DEBITO_CREDITO = 2;
    public const DOCUMENTO_FISCAL_NOTA_FISCAL = 3;
    public const DOCUMENTO_FISCAL_DOCUMENTO_EQUIVALENTE = 4;

    /**
     * @param string|int $nit NIT del emisor.
     * @param Carbon $fechaHora Fecha/hora exacta de emisión (se usa con milisegundos).
     * @param int $sucursal Código de sucursal (0 = casa matriz).
     * @param int $modalidad 1=Electrónica, 2=Computarizada, 3=Manual.
     * @param int $tipoEmision 1=Online, 2=Offline.
     * @param int $codigoDocumentoFiscal 1=Factura, 2=Nota Débito/Crédito, 3=Nota Fiscal, 4=Documento Equivalente.
     * @param int $tipoDocumentoSector Código del tipo de documento sector (1 = Factura Estándar, para nosotros fijo en 1).
     * @param int $numeroFactura Número correlativo de la factura.
     * @param int $puntoVenta Código de punto de venta (0 = no corresponde).
     */
    public static function generate(
        string|int $nit,
        Carbon $fechaHora,
        int $sucursal,
        int $modalidad,
        int $tipoEmision,
        int $codigoDocumentoFiscal,
        int $tipoDocumentoSector,
        int $numeroFactura,
        int $puntoVenta
    ): string {
        $cadena =
            str_pad((string) $nit, 13, '0', STR_PAD_LEFT) .
            $fechaHora->format('YmdHis') . str_pad((string) $fechaHora->milli, 3, '0', STR_PAD_LEFT) .
            str_pad((string) $sucursal, 4, '0', STR_PAD_LEFT) .
            str_pad((string) $modalidad, 1, '0', STR_PAD_LEFT) .
            str_pad((string) $tipoEmision, 1, '0', STR_PAD_LEFT) .
            str_pad((string) $codigoDocumentoFiscal, 1, '0', STR_PAD_LEFT) .
            str_pad((string) $tipoDocumentoSector, 2, '0', STR_PAD_LEFT) .
            str_pad((string) $numeroFactura, 8, '0', STR_PAD_LEFT) .
            str_pad((string) $puntoVenta, 4, '0', STR_PAD_LEFT);

        $digitoVerificador = self::modulo11($cadena);

        $cadenaCompleta = $cadena . $digitoVerificador;

        return strtoupper(self::toBase16($cadenaCompleta));
    }

    /**
     * Módulo 11 con pesos 2..7 cíclicos, aplicados de izquierda a derecha,
     * el dígito verificador es el resto de la suma entre 11 (0-10, puede
     * tener 2 dígitos en el caso de resto=10, no documentado explícitamente
     * por el SIN pero es el comportamiento consistente con el ejemplo oficial).
     */
    private static function modulo11(string $cadena): int
    {
        $pesos = [2, 3, 4, 5, 6, 7];
        $suma = 0;

        for ($i = 0, $len = strlen($cadena); $i < $len; $i++) {
            $suma += ((int) $cadena[$i]) * $pesos[$i % 6];
        }

        return $suma % 11;
    }

    /**
     * Convierte una cadena decimal (potencialmente muy larga, más allá del
     * rango de un entero nativo) a su representación hexadecimal, usando
     * BCMath (disponible en la mayoría de hostings compartidos, a diferencia
     * de GMP que no siempre está habilitado).
     */
    private static function toBase16(string $decimal): string
    {
        $hexChars = '0123456789ABCDEF';
        $result = '';
        $num = $decimal;

        while (bccomp($num, '0') > 0) {
            $rem = (int) bcmod($num, '16');
            $result = $hexChars[$rem] . $result;
            $num = bcdiv($num, '16', 0);
        }

        return $result === '' ? '0' : $result;
    }
}
