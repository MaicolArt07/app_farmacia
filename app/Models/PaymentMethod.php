<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $table = 'payment_methods';

    protected $fillable = [
        'name',
        'codigo_sin',
        'state',
    ];

    protected $casts = [
        'codigo_sin' => 'integer',
        'state' => 'integer',
    ];

    public function sales()
    {
        return $this->hasMany(Venta::class, 'id_payment_method');
    }
}
