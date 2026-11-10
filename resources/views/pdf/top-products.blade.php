<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Productos más vendidos</title>
    <style>
        @page { margin: 24px 28px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        h1 { font-size: 16px; margin: 0 0 2px; }
        .sub { color: #555; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d4d4d8; padding: 4px 5px; }
        th { background: #e4e4e7; text-align: left; font-size: 9px; text-transform: uppercase; }
        .r { text-align: right; }
        .c { text-align: center; }
        tfoot td { font-weight: bold; background: #f4f4f5; }
        .top td { background: #eff6ff; }
        .resumen { margin-bottom: 10px; }
        .resumen td { border: none; padding: 2px 10px 2px 0; }
        .footer { margin-top: 10px; color: #777; font-size: 8px; }
    </style>
</head>
<body>
    @php($company = \App\Models\Empresa::current())

    <h1>Productos más vendidos</h1>
    <div class="sub">
        {{ $company->razon_social ?? 'FarmaFamilia' }} —
        del <strong>{{ \Carbon\Carbon::parse($from)->format('d/m/Y') }}</strong>
        al <strong>{{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}</strong>
        · ordenado por {{ mb_strtolower($orderLabel) }}
    </div>

    <table class="resumen">
        <tr>
            <td>Productos vendidos: <strong>{{ $totals['productos'] }}</strong></td>
            <td>Unidades: <strong>{{ $totals['cantidad'] }}</strong></td>
            <td>Total vendido: <strong>Bs {{ number_format($totals['total_vendido'], 2) }}</strong></td>
            <td>Ganancia: <strong>Bs {{ number_format($totals['ganancia'], 2) }}</strong></td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th class="c">N°</th>
                <th>Producto</th>
                <th>Laboratorio</th>
                <th class="r">Cant.</th>
                <th class="r">Ventas</th>
                <th class="r">P. prom.</th>
                <th class="r">Total (Bs)</th>
                <th class="r">Costo (Bs)</th>
                <th class="r">Ganancia (Bs)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $r)
                <tr class="{{ $r->puesto <= 3 ? 'top' : '' }}">
                    <td class="c">{{ $r->puesto }}</td>
                    <td>{{ $r->nombre }}<br><span style="color:#888;font-size:8px">{{ $r->code }}</span></td>
                    <td>{{ $r->laboratorio ?? '-' }}</td>
                    <td class="r">{{ $r->cantidad }}</td>
                    <td class="r">{{ $r->num_ventas }}</td>
                    <td class="r">{{ number_format($r->precio_promedio, 2) }}</td>
                    <td class="r">{{ number_format($r->total_vendido, 2) }}</td>
                    <td class="r">{{ number_format($r->costo, 2) }}</td>
                    <td class="r">{{ number_format($r->ganancia, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="c">No hubo ventas de productos en este período.</td></tr>
            @endforelse
        </tbody>
        @if($rows->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="3">TOTAL</td>
                    <td class="r">{{ $totals['cantidad'] }}</td>
                    <td></td>
                    <td></td>
                    <td class="r">{{ number_format($totals['total_vendido'], 2) }}</td>
                    <td class="r">{{ number_format($totals['costo'], 2) }}</td>
                    <td class="r">{{ number_format($totals['ganancia'], 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="footer">
        Solo ventas activas (las canceladas no cuentan). No incluye servicios.
        Generado el {{ now()->format('d/m/Y H:i') }} por {{ auth()->user()?->name }}.
    </div>
</body>
</html>
