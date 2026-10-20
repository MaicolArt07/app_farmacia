<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetalleVenta extends Model
{
    use HasFactory;

    protected $table = 'detalles_venta';

    protected $fillable = [
        'id_sale',
        'id_product',
        'id_lot',
        'quantity',
        'precio_venta',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'precio_venta' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Venta::class, 'id_sale');
    }

    public function product()
    {
        return $this->belongsTo(Producto::class, 'id_product');
    }

    public function lot()
    {
        return $this->belongsTo(Lote::class, 'id_lot');
    }
}
