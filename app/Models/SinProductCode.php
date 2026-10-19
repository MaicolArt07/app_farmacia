<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SinProductCode extends Model
{
    protected $table = 'sin_product_codes';

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
        return $this->hasMany(Product::class, 'id_sin_product_code');
    }
}
