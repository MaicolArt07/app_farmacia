<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sucursal extends Model
{
    use HasFactory;

    protected $table = 'sucursales';

    protected $fillable = [
        'id_empresa',
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
        return $this->belongsTo(Empresa::class, 'id_empresa');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'id_sucursal');
    }

    public function lots()
    {
        return $this->hasMany(Lote::class, 'id_sucursal');
    }

    public function cashRegisters()
    {
        return $this->hasMany(Caja::class, 'id_sucursal');
    }

    public function sales()
    {
        return $this->hasMany(Venta::class, 'id_sucursal');
    }

    public function purchases()
    {
        return $this->hasMany(Compra::class, 'id_sucursal');
    }
}
