<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Servicio extends Model
{
    use HasFactory;

    protected $table = 'servicios';

    protected $fillable = [
        'id_sucursal',
        'nombre',
        'descripcion',
        'precio',
        'estado',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'estado' => 'integer',
    ];

    public function details()
    {
        return $this->hasMany(DetalleVenta::class, 'id_servicio');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }
}
