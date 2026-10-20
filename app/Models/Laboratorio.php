<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Laboratorio extends Model
{
    use HasFactory;

    protected $table = 'laboratorios';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'id_laboratory');
    }
}
