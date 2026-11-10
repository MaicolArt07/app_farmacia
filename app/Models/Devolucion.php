<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Devolucion extends Model
{
    protected $table = 'devoluciones';

    protected $fillable = ['id_venta', 'id_caja', 'id_usuario', 'monto', 'monto_efectivo', 'monto_qr', 'motivo'];

    protected $casts = [
        'monto' => 'decimal:2',
        'monto_efectivo' => 'decimal:2',
        'monto_qr' => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Venta::class, 'id_venta');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function details()
    {
        return $this->hasMany(DevolucionDetalle::class, 'id_devolucion');
    }
}
