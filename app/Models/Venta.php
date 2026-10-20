<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';

    protected $fillable = [
        'id_client',
        'id_user',
        'id_cash_register',
        'id_payment_method',
        'fecha_venta',
        'metodo_pago',
        'subtotal',
        'descuento',
        'total',
        'monto_pagado',
        'monto_cambio',
        'observacion',
        'estado',

        // 🔹 nuevos campos
        'status',
        'cancelled_at',
        'cancelled_by',
        'cancel_reason',
    ];

    protected $casts = [
        'fecha_venta' => 'date',
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'total' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'monto_cambio' => 'decimal:2',
        'estado' => 'integer',

        // 🔹 nuevos casts
        'cancelled_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    public function client()
    {
        return $this->belongsTo(Cliente::class, 'id_client');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class, 'id_cash_register');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'id_payment_method');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function details()
    {
        return $this->hasMany(DetalleVenta::class, 'id_sale');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'id_sale');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'CANCELLED');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function isCancelled()
    {
        return $this->status === 'CANCELLED';
    }

    public function isActive()
    {
        return $this->status === 'ACTIVE';
    }

    /*
    |--------------------------------------------------------------------------
    | ATRIBUTOS ACCESORIOS
    |--------------------------------------------------------------------------
    */

    public function getStatusLabelAttribute()
    {
        return $this->status === 'CANCELLED' ? 'Anulada' : 'Activa';
    }

    public function getStatusColorAttribute()
    {
        return $this->status === 'CANCELLED'
            ? 'red'
            : 'emerald';
    }
}
