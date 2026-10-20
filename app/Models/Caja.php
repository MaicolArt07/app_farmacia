<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Caja extends Model
{
    use HasFactory;

    protected $table = 'cajas';

    public const VALIDITY_PERIODS = [
        'DAILY' => 'Diario',
        'WEEKLY' => 'Semanal',
        'MONTHLY' => 'Mensual',
    ];

    protected $fillable = [
        'id_user',
        'fecha_apertura',
        'monto_apertura',
        'periodo_validez',
        'expires_at',
        'last_extended_at',
        'monto_cierre',
        'monto_esperado',
        'diferencia',
        'status',
        'observacion',
        'estado',
    ];

    protected $casts = [
        'fecha_apertura' => 'datetime',
        'expires_at' => 'datetime',
        'last_extended_at' => 'datetime',
        'monto_apertura' => 'decimal:2',
        'monto_esperado' => 'decimal:2',
        'monto_cierre' => 'decimal:2',
        'diferencia' => 'decimal:2',
        'estado' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function movements()
    {
        return $this->hasMany(MovimientoCaja::class, 'id_cash_register');
    }

    public function sales()
    {
        return $this->hasMany(Venta::class, 'id_cash_register');
    }

    public function incomes()
    {
        return $this->hasMany(MovimientoCaja::class, 'id_cash_register')
            ->where('type', 'INCOME');
    }

    public function expenses()
    {
        return $this->hasMany(MovimientoCaja::class, 'id_cash_register')
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
        $this->periodo_validez = $period ?: ($this->periodo_validez ?: 'DAILY');
        $this->expires_at = self::calculateExpiration($this->periodo_validez);
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
