<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DevolucionDetalle extends Model
{
    protected $table = 'devolucion_detalles';

    protected $fillable = [
        'id_devolucion', 'id_detalle_venta', 'id_producto', 'id_servicio', 'id_lote',
        'cantidad', 'precio_venta', 'subtotal',
    ];

    public function product()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }

    public function service()
    {
        return $this->belongsTo(Servicio::class, 'id_servicio');
    }
}
