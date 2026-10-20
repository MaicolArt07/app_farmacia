<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MetodoPago extends Model
{
    use HasFactory;

    protected $table = 'metodos_pago';

    protected $fillable = [
        'nombre',
        'codigo_sin',
        'estado',
    ];

    protected $casts = [
        'codigo_sin' => 'integer',
        'estado' => 'integer',
    ];

    public function sales()
    {
        return $this->hasMany(Venta::class, 'id_payment_method');
    }
}
