<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'id_category',
        'id_laboratory',
        'id_presentation',
        'id_sin_product_code',
        'code',
        'barcode',
        'nombre',
        'nombre_generico',
        'concentracion',
        'descripcion',
        'precio_venta',
        'stock_minimo',
        'requiere_receta',
        'estado',
    ];

    protected $casts = [
        'precio_venta' => 'decimal:2',
        'requiere_receta' => 'boolean',
        'stock_minimo' => 'integer',
        'estado' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Categoria::class, 'id_category');
    }

    public function laboratory()
    {
        return $this->belongsTo(Laboratorio::class, 'id_laboratory');
    }

    public function presentation()
    {
        return $this->belongsTo(Presentacion::class, 'id_presentation');
    }

    public function sinProductCode()
    {
        return $this->belongsTo(SinProductCode::class, 'id_sin_product_code');
    }

    public function lots()
    {
        return $this->hasMany(Lote::class, 'id_product');
    }
    public function purchaseDetails()
    {
        return $this->hasMany(DetalleCompra::class, 'id_product');
    }
    public function saleDetails()
    {
        return $this->hasMany(DetalleVenta::class, 'id_product');
    }
    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class, 'id_product');
    }
}
