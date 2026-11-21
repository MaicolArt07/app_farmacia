<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'id_categoria',
        'id_laboratorio',
        'id_presentacion',
        'id_codigo_producto_sin',
        'id_producto_unidad',
        'unidades_por_paquete',
        'code',
        'barcode',
        'nombre',
        'nombre_generico',
        'concentracion',
        'via',
        'descripcion',
        'precio_venta',
        'stock_minimo',
        'requiere_receta',
        'estado',
    ];

    /**
     * Vía / forma de administración. Mismo principio activo (nombre_generico)
     * + misma vía = equivalente intercambiable directo en Ventas.
     */
    public const VIAS = [
        'ORAL SOLIDO' => 'Oral sólido (comprimido, cápsula, sobre)',
        'ORAL LIQUIDO' => 'Oral líquido (jarabe, suspensión, gotas)',
        'SUBLINGUAL' => 'Sublingual',
        'INYECTABLE' => 'Inyectable',
        'TOPICO' => 'Tópico (crema, pomada, gel, spray)',
        'OFTALMICO' => 'Oftálmico (ojos)',
        'OTICO' => 'Ótico (oídos)',
        'NASAL' => 'Nasal',
        'INHALADO' => 'Inhalado',
        'VAGINAL' => 'Vaginal',
        'RECTAL' => 'Rectal',
    ];

    /**
     * Nombre visible + " | concentración", solo si la concentración no está ya
     * en el nombre (ej. "PARACETAMOL 500 MG" no repite "| 500 MG").
     */
    public function getNombreConConcentracionAttribute(): string
    {
        $nombre = $this->nombre_completo;
        $conc = trim((string) $this->concentracion);
        if ($conc === '') return $nombre;

        $clave = fn ($s) => preg_replace('/[^0-9A-Z]/', '', mb_strtoupper(str_replace(',', '.', $s)));
        return str_contains($clave($nombre), $clave($conc)) ? $nombre : $nombre . ' | ' . $conc;
    }

    /** Principio activo normalizado (MAYÚSCULAS, sin tildes, " + " entre componentes). */
    public static function normalizarGenerico(?string $texto): ?string
    {
        $t = trim((string) $texto);
        if ($t === '') return null;

        $t = mb_strtoupper(strtr($t, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U']));
        $t = preg_replace('/\s*\+\s*/u', ' + ', $t);

        return preg_replace('/\s+/u', ' ', $t);
    }

    protected $casts = [
        'precio_venta' => 'decimal:2',
        'requiere_receta' => 'boolean',
        'stock_minimo' => 'integer',
        'estado' => 'integer',
        'unidades_por_paquete' => 'integer',
    ];

    // El nombre visible (nombre_completo) necesita el laboratorio/marca:
    // se carga siempre para no hacer una consulta por producto.
    protected $with = ['laboratory'];

    /**
     * Nombre visible del producto, en mayúsculas:
     *   laboratorio -> "REFRIANEX COMPRIMIDO LAB.BAGO"
     *   marca       -> "PAÑAL TALLA G 9A12KG MAMI"
     *   sin ninguno -> "JERINGA 50ML"
     * En la tabla `productos.nombre` se guarda solo el nombre limpio.
     */
    public function getNombreCompletoAttribute(): string
    {
        $lab = $this->id_laboratorio ? $this->laboratory : null;

        return self::armarNombreCompleto($this->nombre, $lab?->nombre, $lab?->tipo);
    }

    /** Misma regla que nombre_completo, para consultas que no traen el modelo (reportes con SQL). */
    public static function armarNombreCompleto(?string $nombre, ?string $labNombre, ?string $labTipo): string
    {
        $nombre = trim(preg_replace('/\s+/u', ' ', mb_strtoupper((string) $nombre)));
        $sufijo = Laboratorio::sufijo($labNombre, $labTipo);

        if ($sufijo === '') {
            return $nombre;
        }

        // Datos todavía sin limpiar (el nombre ya trae "LAB.xxx" o la marca al final):
        // se muestra tal cual para no duplicar ("... LAB.BAGO LAB.BAGO").
        if (preg_match('/(^|\s)LAB\s*[.,]/u', $nombre) || str_ends_with($nombre, ' ' . $sufijo) || $nombre === $sufijo) {
            return $nombre;
        }

        return trim($nombre . ' ' . $sufijo);
    }

    /**
     * Búsqueda por nombre del producto O laboratorio/marca, palabra por palabra:
     * "refrianex bago" encuentra "REFRIANEX COMPRIMIDO" de LAB.BAGO.
     * $extra agrega otras columnas del producto que también deben coincidir
     * con el texto completo (código, código de barras, genérico...).
     */
    public function scopeBuscarNombre($query, ?string $term, array $extra = [])
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $words = array_values(array_filter(preg_split('/\s+/u', $term)));

        return $query->where(function ($q) use ($words, $extra, $term) {
            $q->where(function ($byWords) use ($words) {
                foreach ($words as $w) {
                    // "LAB.BAGO" escrito por el usuario se busca como "BAGO".
                    $w = Laboratorio::limpiarNombre($w) ?: $w;

                    $byWords->where(fn ($one) => $one->where('productos.nombre', 'like', "%{$w}%")
                        ->orWhereHas('laboratory', fn ($l) => $l->where('nombre', 'like', "%{$w}%")));
                }
            });

            foreach ($extra as $column) {
                $q->orWhere($column, 'like', "%{$term}%");
            }
        });
    }

    /** Producto "unidad" en el que se abre este paquete (si es un paquete). */
    public function unitProduct()
    {
        return $this->belongsTo(Producto::class, 'id_producto_unidad');
    }

    /** Paquetes que se abren en este producto (si es un producto "unidad"). */
    public function packProducts()
    {
        return $this->hasMany(Producto::class, 'id_producto_unidad');
    }

    public function isPack(): bool
    {
        return $this->id_producto_unidad !== null && (int) $this->unidades_por_paquete > 0;
    }

    /** Stock disponible en lotes activos y no vencidos. */
    public function availableStock(): int
    {
        return (int) Lote::where('id_producto', $this->id)
            ->where('estado', 1)
            ->where('cantidad_disponible', '>', 0)
            ->where(fn ($q) => $q->whereNull('fecha_vencimiento')->orWhereDate('fecha_vencimiento', '>=', now()->toDateString()))
            ->sum('cantidad_disponible');
    }

    public function category()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria');
    }

    public function laboratory()
    {
        return $this->belongsTo(Laboratorio::class, 'id_laboratorio');
    }

    public function presentation()
    {
        return $this->belongsTo(Presentacion::class, 'id_presentacion');
    }

    public function sinProductCode()
    {
        return $this->belongsTo(CodigoProductoSin::class, 'id_codigo_producto_sin');
    }

    public function lots()
    {
        return $this->hasMany(Lote::class, 'id_producto');
    }
    public function purchaseDetails()
    {
        return $this->hasMany(DetalleCompra::class, 'id_producto');
    }
    public function saleDetails()
    {
        return $this->hasMany(DetalleVenta::class, 'id_producto');
    }
    public function inventoryMovements()
    {
        return $this->hasMany(MovimientoInventario::class, 'id_producto');
    }
}
