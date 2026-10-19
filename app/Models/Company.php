<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory;

    protected $table = 'companies';

    protected $fillable = [
        'nit',
        'razon_social',
        'nombre_comercial',
        'actividad_economica',
        'email',
        'phone',
        'address',
        'logo_path',
        'codigo_sistema',
        'numero_solicitud',
        'cuis',
        'ambiente',
        'soap_version',
        'wsdl_sincronizacion',
        'wsdl_operaciones',
        'wsdl_codigos',
        'wsdl_factura_compra_venta',
        'state',
    ];

    protected $casts = [
        'state' => 'integer',
    ];

    public function branches()
    {
        return $this->hasMany(Branch::class, 'id_company');
    }

    /**
     * Único registro de empresa activo (este sistema maneja una sola empresa).
     */
    public static function current(): ?self
    {
        return static::where('state', 1)->first();
    }

    /**
     * Sucursal "casa matriz" (numero_sucursal = 0) o la primera activa como
     * respaldo, para usar como emisor por defecto en la facturación.
     */
    public function mainBranch(): ?Branch
    {
        return $this->branches()
            ->where('state', 1)
            ->orderByRaw('numero_sucursal = 0 DESC')
            ->first();
    }
}
