<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Purchase extends Model
{
    use HasFactory;

    protected $table = 'purchases';

    protected $fillable = [
        'id_supplier',
        'id_user',
        'invoice_number',
        'purchase_date',
        'subtotal',
        'total',
        'observation',
        'state',
        'status',
        'cancelled_at',
        'cancelled_by',
        'cancel_reason',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'state' => 'integer',
        'cancelled_at' => 'datetime',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'id_supplier');
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
        return $this->hasMany(PurchaseDetail::class, 'id_purchase');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'CANCELLED');
    }

    public function isCancelled()
    {
        return $this->status === 'CANCELLED';
    }

    public function isActive()
    {
        return $this->status === 'ACTIVE';
    }
}