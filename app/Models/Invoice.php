<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'invoices';

    protected $fillable = [
        'id_sale',
        'id_user',
        'modo',
        'ambiente',
        'tipo_documento_identidad',
        'nit_cliente',
        'razon_social',
        'numero_factura',
        'cuf',
        'cuis',
        'cufd',
        'codigo_control',
        'total',
        'estado',
        'observaciones',
        'raw_request',
        'raw_response',
        'anulado_at',
        'anulado_reason',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'anulado_at' => 'datetime',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'id_sale');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function isSimulado(): bool
    {
        return $this->modo === 'SIMULADO';
    }

    public function isAnulada(): bool
    {
        return $this->estado === 'ANULADA';
    }

    public function getEstadoLabelAttribute(): string
    {
        return match ($this->estado) {
            'ENVIADA' => 'Enviada',
            'OBSERVADA' => 'Observada',
            'ERROR' => 'Error',
            'ANULADA' => 'Anulada',
            default => 'Pendiente',
        };
    }

    public function getEstadoColorAttribute(): string
    {
        return match ($this->estado) {
            'ENVIADA' => 'emerald',
            'OBSERVADA' => 'amber',
            'ERROR' => 'red',
            'ANULADA' => 'zinc',
            default => 'blue',
        };
    }
}
