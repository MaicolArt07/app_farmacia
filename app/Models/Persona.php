<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    protected $table = 'personas';
    protected $fillable = [
        'id_country',
        'nombre',
        'apellido',
        'ci',
        'telefono',
        'direccion',
        'estado'
    ];

    public function country()
    {
        return $this->belongsTo(Pais::class, 'id_country');
    }
    public function client()
    {
        return $this->hasOne(Cliente::class, 'id_person');
    }
    public function supplier()
    {
        return $this->hasOne(Proveedor::class, 'id_person');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id_person');
    }
}
