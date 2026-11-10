<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MovimientoContable extends Model
{
    use HasFactory;

    protected $table = 'movimientos_contables';

    const DEBE = 'DEBE';
    const HABER = 'HABER';

    protected $fillable = [
        'fecha',
        'tipo',
        'concepto',
        'monto',
        'origen',
        'reference_id',
        'id_gasto_comun',
        'id_usuario',
        'observacion',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
    ];

    public function gastoComun()
    {
        return $this->belongsTo(GastoComun::class, 'id_gasto_comun');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function scopeDebe($query)
    {
        return $query->where('tipo', self::DEBE);
    }

    public function scopeHaber($query)
    {
        return $query->where('tipo', self::HABER);
    }

    public function scopeEnPeriodo($query, $desde, $hasta)
    {
        return $query->whereBetween('fecha', [$desde, $hasta]);
    }

    /** Crea/actualiza una entrada automática del Debe o Haber ligada a un origen (compra, cierre de caja, etc). */
    public static function registrarAutomatico(string $origen, int $referenceId, array $data): self
    {
        return self::updateOrCreate(
            ['origen' => $origen, 'reference_id' => $referenceId],
            $data
        );
    }

    /** Elimina la entrada automática ligada a un origen (ej. al anular una compra). */
    public static function eliminarAutomatico(string $origen, int $referenceId): void
    {
        self::where('origen', $origen)->where('reference_id', $referenceId)->delete();
    }
}
