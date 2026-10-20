<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedores';

    protected $fillable = [
        'id_person',
        'razon_social',
        'cargo_contacto',
        'nit',
        'email',
        'estado',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class, 'id_person');
    }
    public function purchases()
{
    return $this->hasMany(Purchase::class, 'id_supplier');
}
}
