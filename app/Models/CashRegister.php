<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CashRegister extends Model
{
    use HasFactory;

    protected $table = 'cash_registers';

    public const VALIDITY_PERIODS = [
        'DAILY' => 'Diario',
        'WEEKLY' => 'Semanal',
        'MONTHLY' => 'Mensual',
    ];

    protected $fillable = [
        'id_user',
        'opening_date',
        'opening_amount',
        'validity_period',
        'expires_at',
        'last_extended_at',
        'closing_amount',
        'expected_amount',
        'difference',
        'status',
        'observation',
        'state',
    ];

    protected $casts = [
        'opening_date' => 'datetime',
        'expires_at' => 'datetime',
        'last_extended_at' => 'datetime',
        'opening_amount' => 'decimal:2',
        'expected_amount' => 'decimal:2',
        'closing_amount' => 'decimal:2',
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

    public function sales()
    {
        return $this->hasMany(Sale::class, 'id_cash_register');
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

    public function isExpired()
    {
        return $this->expires_at !== null && now()->greaterThan($this->expires_at);
    }

    public function extend(?string $period = null)
    {
        $this->validity_period = $period ?: ($this->validity_period ?: 'DAILY');
        $this->expires_at = self::calculateExpiration($this->validity_period);
        $this->last_extended_at = now();
        $this->save();

        return $this;
    }

    public function extendUntil($datetime)
    {
        $this->expires_at = \Illuminate\Support\Carbon::parse($datetime);
        $this->last_extended_at = now();
        $this->save();

        return $this;
    }

    public static function calculateExpiration(string $period, $from = null)
    {
        $from = $from ? \Illuminate\Support\Carbon::parse($from) : now();

        return match ($period) {
            'WEEKLY' => $from->copy()->addWeek(),
            'MONTHLY' => $from->copy()->addMonth(),
            default => $from->copy()->addDay(),
        };
    }
}