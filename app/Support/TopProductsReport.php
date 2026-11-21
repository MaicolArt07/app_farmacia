<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Informe "Productos más vendidos" entre dos fechas (solo ventas ACTIVAS,
 * las canceladas no cuentan). Lo usan la pantalla, el PDF y el Excel, así
 * los tres muestran exactamente lo mismo.
 */
class TopProductsReport
{
    public const ORDERS = [
        'cantidad' => 'Cantidad vendida',
        'monto' => 'Monto vendido (Bs)',
        'ganancia' => 'Ganancia (Bs)',
    ];

    public static function rows(string $from, string $to, string $order = 'cantidad'): Collection
    {
        $orderColumn = match ($order) {
            'monto' => 'total_vendido',
            'ganancia' => 'ganancia',
            default => 'cantidad',
        };

        $rows = DB::table('detalles_venta as d')
            ->join('ventas as v', 'v.id', '=', 'd.id_venta')
            ->join('productos as p', 'p.id', '=', 'd.id_producto')
            ->leftJoin('lotes as l', 'l.id', '=', 'd.id_lote')
            ->leftJoin('laboratorios as lab', 'lab.id', '=', 'p.id_laboratorio')
            ->whereNotNull('d.id_producto')
            ->where('v.status', 'ACTIVE')
            ->whereBetween('v.fecha_venta', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->when(($user = Auth::user()) && ! $user->can('Super Admin'), fn ($q) => $q->where('v.id_sucursal', $user->id_sucursal))
            ->groupBy('p.id', 'p.code', 'p.nombre', 'lab.nombre', 'lab.tipo')
            ->selectRaw('
                p.id,
                p.code,
                p.nombre,
                lab.nombre as laboratorio,
                lab.tipo as laboratorio_tipo,
                SUM(d.quantity) as cantidad,
                COUNT(DISTINCT d.id_venta) as num_ventas,
                SUM(d.subtotal) as total_vendido,
                SUM(d.quantity * COALESCE(l.precio_compra, 0)) as costo,
                SUM(d.subtotal) - SUM(d.quantity * COALESCE(l.precio_compra, 0)) as ganancia
            ')
            ->orderByDesc($orderColumn)
            ->orderByDesc('total_vendido')
            ->orderBy('p.nombre')
            ->get();

        return $rows->values()->map(function ($r, $i) {
            $r->puesto = $i + 1;
            // Nombre visible: NOMBRE + LAB.xxx / marca (igual que Producto::nombre_completo).
            $r->nombre = \App\Models\Producto::armarNombreCompleto($r->nombre, $r->laboratorio, $r->laboratorio_tipo);
            $r->laboratorio = $r->laboratorio ? \App\Models\Laboratorio::sufijo($r->laboratorio, $r->laboratorio_tipo) : null;
            $r->cantidad = (int) $r->cantidad;
            $r->num_ventas = (int) $r->num_ventas;
            $r->total_vendido = round((float) $r->total_vendido, 2);
            $r->costo = round((float) $r->costo, 2);
            $r->ganancia = round((float) $r->ganancia, 2);
            $r->precio_promedio = $r->cantidad > 0 ? round($r->total_vendido / $r->cantidad, 2) : 0;

            return $r;
        });
    }

    public static function totals(Collection $rows): array
    {
        return [
            'productos' => $rows->count(),
            'cantidad' => (int) $rows->sum('cantidad'),
            'total_vendido' => round((float) $rows->sum('total_vendido'), 2),
            'costo' => round((float) $rows->sum('costo'), 2),
            'ganancia' => round((float) $rows->sum('ganancia'), 2),
        ];
    }
}
