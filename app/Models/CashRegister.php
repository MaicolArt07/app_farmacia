<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CashRegister extends Model
{
    use HasFactory;

    protected $table = 'cash_registers';

    protected $fillable = [
        'id_user',
        'opening_date',
        'closing_date',
        'opening_amount',
        'expected_amount',
        'counted_amount',
        'difference',
        'status',
        'observation',
        'state',
    ];

    protected $casts = [
        'opening_date' => 'datetime',
        'closing_date' => 'datetime',
        'opening_amount' => 'decimal:2',
        'expected_amount' => 'decimal:2',
        'counted_amount' => 'decimal:2',
        'difference' => 'decimal:2',
        'state' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function movements()
    {
        return $this->hasMany(CashMovement::class, 'id_cash_register');
    }

    public function incomes()
    {
        return $this->hasMany(CashMovement::class, 'id_cash_register')
            ->where('type', 'INCOME');
    }

    public function expenses()
    {
        return $this->hasMany(CashMovement::class, 'id_cash_register')
            ->where('type', 'EXPENSE');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'OPEN');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'CLOSED');
    }

    public function isOpen()
    {
        return $this->status === 'OPEN';
    }

    public function isClosed()
    {
        return $this->status === 'CLOSED';
    }
}