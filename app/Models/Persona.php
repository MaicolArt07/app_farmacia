<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    protected $table = 'personas';
    protected $fillable = [
        'id_pais',
        'nombre',
        'apellido',
        'ci',
        'telefono',
        'direccion',
        'estado'
    ];

    public function country()
    {
        return $this->belongsTo(Pais::class, 'id_pais');
    }
    public function client()
    {
        return $this->hasOne(Cliente::class, 'id_persona');
    }
    public function supplier()
    {
        return $this->hasOne(Proveedor::class, 'id_persona');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id_persona');
    }
}
