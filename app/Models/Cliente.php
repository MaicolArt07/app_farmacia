<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'id_person',
        'code',
        'tipo',
        'nit',
        'email',
        'estado',
    ];

    public function person()
    {
        return $this->belongsTo(Persona::class, 'id_person');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'id_client');
    }
}
