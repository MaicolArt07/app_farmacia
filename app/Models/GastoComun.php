<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GastoComun extends Model
{
    use HasFactory;

    protected $table = 'gastos_comunes';

    protected $fillable = [
        'nombre',
        'descripcion',
        'monto_referencia',
        'estado',
    ];

    protected $casts = [
        'monto_referencia' => 'decimal:2',
        'estado' => 'integer',
    ];

    public function movimientos()
    {
        return $this->hasMany(MovimientoContable::class, 'id_gasto_comun');
    }
}
