<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'id_categoria',
        'id_laboratorio',
        'id_presentacion',
        'id_codigo_producto_sin',
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
        return $this->belongsTo(Categoria::class, 'id_categoria');
    }

    public function laboratory()
    {
        return $this->belongsTo(Laboratorio::class, 'id_laboratorio');
    }

    public function presentation()
    {
        return $this->belongsTo(Presentacion::class, 'id_presentacion');
    }

    public function sinProductCode()
    {
        return $this->belongsTo(CodigoProductoSin::class, 'id_codigo_producto_sin');
    }

    public function lots()
    {
        return $this->hasMany(Lote::class, 'id_producto');
    }
    public function purchaseDetails()
    {
        return $this->hasMany(DetalleCompra::class, 'id_producto');
    }
    public function saleDetails()
    {
        return $this->hasMany(DetalleVenta::class, 'id_producto');
    }
    public function inventoryMovements()
    {
        return $this->hasMany(MovimientoInventario::class, 'id_producto');
    }
}
