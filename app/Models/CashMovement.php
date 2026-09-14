<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CashMovement extends Model
{
    use HasFactory;

    protected $table = 'cash_movements';

    protected $fillable = [
        'id_cash_register',
        'id_user',
        'type',
        'origin',
        'concept',
        'amount',
        'reference_type',
        'reference_id',
        'observation',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class, 'id_cash_register');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function scopeIncome($query)
    {
        return $query->where('type', 'INCOME');
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'EXPENSE');
    }
}