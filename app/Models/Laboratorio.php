<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Laboratorio extends Model
{
    use HasFactory;

    public const TIPO_LABORATORIO = 'LABORATORIO';
    public const TIPO_MARCA = 'MARCA';

    public const TIPOS = [
        self::TIPO_LABORATORIO => 'Laboratorio',
        self::TIPO_MARCA => 'Marca',
    ];

    protected $table = 'laboratorios';

    protected $fillable = [
        'nombre',
        'tipo',
        'descripcion',
        'estado',
    ];

    public function products()
    {
        return $this->hasMany(Producto::class, 'id_laboratorio');
    }

    /**
     * Sufijo que se agrega al nombre del producto: "LAB.BAGO" para un
     * laboratorio, "MAMI" para una marca. Siempre en mayúsculas.
     */
    public function getSufijoAttribute(): string
    {
        return self::sufijo($this->nombre, $this->tipo);
    }

    public static function sufijo(?string $nombre, ?string $tipo): string
    {
        $nombre = self::limpiarNombre($nombre);

        if ($nombre === '') {
            return '';
        }

        return ($tipo ?: self::TIPO_LABORATORIO) === self::TIPO_MARCA ? $nombre : 'LAB.' . $nombre;
    }

    /**
     * Nombre como se guarda: sin el prefijo "LAB." (lo agrega el sistema),
     * en mayúsculas y sin espacios dobles.
     */
    public static function limpiarNombre(?string $nombre): string
    {
        $nombre = mb_strtoupper(trim((string) $nombre));
        // "LAB.SAE", "LAB. SAE", "LAB,SAE", "LAB SAE" -> "SAE" (pero "LABORATORIOS X" no se toca).
        $nombre = preg_replace('/^LAB(\s*[.,]\s*|\s+)/u', '', $nombre);

        return trim(preg_replace('/\s+/u', ' ', $nombre));
    }

    /**
     * Clave para detectar duplicados: sin puntos, comas, espacios ni el
     * prefijo "LAB." ("LAB. SAE", "LAB,SAE" y "SAE" dan la misma clave).
     */
    public static function claveComparacion(?string $nombre): string
    {
        return preg_replace('/[^\p{L}\p{N}&+]/u', '', self::limpiarNombre($nombre));
    }
}
