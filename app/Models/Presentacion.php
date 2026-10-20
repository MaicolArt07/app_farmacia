<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Presentacion extends Model
{
    use HasFactory;

    protected $table = 'presentaciones';

    protected $fillable = [
        'nombre',
        'descripcion',
        'codigo_sin',
        'estado',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'id_presentation');
    }
}
