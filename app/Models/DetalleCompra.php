<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetalleCompra extends Model
{
    use HasFactory;

    protected $table = 'detalles_compra';

    protected $fillable = [
        'id_compra',
        'id_producto',
        'codigo_lote',
        'fecha_vencimiento',
        'quantity',
        'precio_compra',
        'precio_venta',
        'subtotal',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'quantity' => 'integer',
        'precio_compra' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function purchase()
    {
        return $this->belongsTo(Compra::class, 'id_compra');
    }

    public function product()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }
}
