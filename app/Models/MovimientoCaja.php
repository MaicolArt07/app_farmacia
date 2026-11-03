<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MovimientoCaja extends Model
{
    use HasFactory;

    protected $table = 'movimientos_caja';

    protected $fillable = [
        'id_caja',
        'id_usuario',
        'type',
        'origin',
        'concepto',
        'monto',
        'reference_type',
        'reference_id',
        'observacion',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
    ];

    public function cashRegister()
    {
        return $this->belongsTo(Caja::class, 'id_caja');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_usuario');
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
