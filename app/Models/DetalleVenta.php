<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetalleVenta extends Model
{
    use HasFactory;

    protected $table = 'detalles_venta';

    protected $fillable = [
        'id_venta',
        'id_producto',
        'id_lote',
        'id_servicio',
        'cantidad_servicio',
        'quantity',
        'precio_venta',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'cantidad_servicio' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Venta::class, 'id_venta');
    }

    public function product()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function lot()
    {
        return $this->belongsTo(Lote::class, 'id_lote');
    }

    public function service()
    {
        return $this->belongsTo(Servicio::class, 'id_servicio');
    }
}
