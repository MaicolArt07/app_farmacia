<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sucursal extends Model
{
    use HasFactory;

    protected $table = 'sucursales';

    protected $fillable = [
        'id_company',
        'numero_sucursal',
        'punto_venta',
        'nombre',
        'municipio',
        'direccion',
        'telefono',
        'estado',
    ];

    protected $casts = [
        'numero_sucursal' => 'integer',
        'punto_venta' => 'integer',
        'estado' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Empresa::class, 'id_company');
    }
}
