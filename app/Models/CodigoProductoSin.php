<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodigoProductoSin extends Model
{
    protected $table = 'codigos_producto_sin';

    protected $fillable = [
        'codigo_actividad',
        'codigo_producto',
        'descripcion',
        'nandina',
    ];

    protected $casts = [
        'codigo_producto' => 'integer',
        'nandina' => 'array',
    ];

    public function products()
    {
        return $this->hasMany(Producto::class, 'id_sin_product_code');
    }
}
