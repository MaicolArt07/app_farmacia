<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';

    protected $fillable = [
        'id_cliente',
        'id_usuario',
        'id_caja',
        'id_sucursal',
        'id_metodo_pago',
        'fecha_venta',
        'metodo_pago',
        'subtotal',
        'descuento',
        'total',
        'monto_efectivo',
        'monto_qr',
        'monto_devuelto',
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
        'monto_efectivo' => 'decimal:2',
        'monto_qr' => 'decimal:2',
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
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function cashRegister()
    {
        return $this->belongsTo(Caja::class, 'id_caja');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(MetodoPago::class, 'id_metodo_pago');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function details()
    {
        return $this->hasMany(DetalleVenta::class, 'id_venta');
    }

    public function invoice()
    {
        return $this->hasOne(Factura::class, 'id_venta');
    }

    public function returns()
    {
        return $this->hasMany(Devolucion::class, 'id_venta');
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
     * Reparto del cobro. Efectivo => todo al cajón; QR => todo a la cuenta;
     * Mixto => monto_efectivo + monto_qr. Si una venta vieja no tiene el
     * reparto guardado, se deduce del método de pago.
     */

    /** SQL: parte en EFECTIVO de cada venta (para SUM). */
    public const SQL_CASH = "COALESCE(monto_efectivo, CASE WHEN UPPER(metodo_pago) = 'EFECTIVO' THEN total ELSE 0 END)";

    /** SQL: parte por QR de cada venta (para SUM). */
    public const SQL_QR = "COALESCE(monto_qr, CASE WHEN UPPER(metodo_pago) = 'QR' THEN total ELSE 0 END)";

    public function isMixed(): bool
    {
        return strtoupper((string) $this->metodo_pago) === 'MIXTO';
    }

    public function cashPart(): float
    {
        if ($this->monto_efectivo !== null) return (float) $this->monto_efectivo;

        return strtoupper((string) $this->metodo_pago) === 'EFECTIVO' ? (float) $this->total : 0.0;
    }

    public function qrPart(): float
    {
        if ($this->monto_qr !== null) return (float) $this->monto_qr;

        return strtoupper((string) $this->metodo_pago) === 'QR' ? (float) $this->total : 0.0;
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
