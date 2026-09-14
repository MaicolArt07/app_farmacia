<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory;

    protected $table = 'sales';

    protected $fillable = [
        'id_client',
        'id_user',
        'sale_date',
        'payment_method',
        'subtotal',
        'discount',
        'total',
        'observation',
        'state',

        // 🔹 nuevos campos
        'status',
        'cancelled_at',
        'cancelled_by',
        'cancel_reason',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'state' => 'integer',

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
        return $this->belongsTo(Client::class, 'id_client');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function details()
    {
        return $this->hasMany(SaleDetail::class, 'id_sale');
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