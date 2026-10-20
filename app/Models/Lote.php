<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lote extends Model
{
    use HasFactory;

    protected $table = 'lotes';

    protected $fillable = [
        'id_product',
        'codigo_lote',
        'precio_compra',
        'precio_venta',
        'cantidad_ingresada',
        'cantidad_disponible',
        'fecha_vencimiento',
        'ubicacion',
        'estado',
    ];

    protected $casts = [
        'precio_compra' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'cantidad_ingresada' => 'integer',
        'cantidad_disponible' => 'integer',
        'fecha_vencimiento' => 'date',
        'estado' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Producto::class, 'id_product');
    }
    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class, 'id_lot');
    }
    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class, 'id_lot');
    }
}
