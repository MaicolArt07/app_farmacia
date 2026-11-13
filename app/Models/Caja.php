<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

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
        'id_usuario',
        'id_sucursal',
        'fecha_apertura',
        'monto_apertura',
        'periodo_validez',
        'expires_at',
        'last_extended_at',
        'monto_cierre',
        'monto_esperado',
        'diferencia',
        'monto_qr_esperado',
        'monto_qr_verificado',
        'diferencia_qr',
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
        'monto_qr_esperado' => 'decimal:2',
        'monto_qr_verificado' => 'decimal:2',
        'diferencia_qr' => 'decimal:2',
        'estado' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }

    public function movements()
    {
        return $this->hasMany(MovimientoCaja::class, 'id_caja');
    }

    public function sales()
    {
        return $this->hasMany(Venta::class, 'id_caja');
    }

    public function incomes()
    {
        return $this->hasMany(MovimientoCaja::class, 'id_caja')
            ->where('type', 'INCOME');
    }

    public function expenses()
    {
        return $this->hasMany(MovimientoCaja::class, 'id_caja')
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

    /**
     * Desglose del dinero de la caja. El "esperado" es SOLO el EFECTIVO que
     * debe haber físicamente en el cajón: los pagos por QR van a la cuenta
     * bancaria y no se cuentan en los cortes.
     *
     *   esperado = monto inicial + ventas en efectivo + ingresos manuales
     *              - egresos manuales
     *
     * Se calcula desde las ventas activas (las canceladas no suman), así que
     * los movimientos automáticos de venta/anulación no se cuentan aparte.
     */
    public function cashBreakdown(): array
    {
        $sales = Venta::where('id_caja', $this->id)->where('status', 'ACTIVE');

        // Mixto: su parte en efectivo suma al cajón y su parte QR a la cuenta.
        $cash = (float) (clone $sales)->sum(DB::raw(Venta::SQL_CASH));
        $qr = (float) (clone $sales)->sum(DB::raw(Venta::SQL_QR));
        $other = max(0, round((float) (clone $sales)->sum('total') - $cash - $qr, 2));

        // Movimientos manuales: excluye los automáticos de ventas y anulaciones.
        $manual = MovimientoCaja::where('id_caja', $this->id)
            ->where(fn ($q) => $q->whereNull('reference_type')->orWhereNotIn('reference_type', ['SALE', 'SALE_CANCEL']))
            ->where('concepto', 'not like', 'Anulación venta #%');

        $incomes = (float) (clone $manual)->where('type', 'INCOME')->sum('monto');
        $expenses = (float) (clone $manual)->where('type', 'EXPENSE')->sum('monto');

        $opening = (float) $this->monto_apertura;

        return [
            'opening' => $opening,
            'cash_sales' => $cash,
            'qr_sales' => $qr,
            'other_sales' => $other,
            'total_sales' => $cash + $qr + $other,
            'incomes' => $incomes,
            'expenses' => $expenses,
            'expected_cash' => round($opening + $cash + $incomes - $expenses, 2),
        ];
    }

    /** Recalcula y guarda monto_esperado (efectivo esperado en el cajón). */
    public function recalculateExpected(): self
    {
        $this->monto_esperado = $this->cashBreakdown()['expected_cash'];
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
