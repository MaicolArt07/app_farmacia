<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Factura #{{ $invoice->numero_factura ?? $invoice->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        .header { text-align: center; margin-bottom: 10px; }
        .banner { background: #b91c1c; color: #fff; text-align: center; padding: 6px; font-weight: bold; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
        th { background: #f3f4f6; }
        .totales { width: 40%; margin-left: auto; margin-top: 10px; }
        .totales td { border: none; }
        .meta { margin-top: 10px; }
        .meta td { border: none; padding: 2px 4px; }
        .text-right { text-align: right; }
    </style>
</head>
<body>

    @if ($invoice->isSimulado())
        <div class="banner">DOCUMENTO SIMULADO — SIN VALIDEZ FISCAL</div>
    @endif

    <div class="header">
        <h2>{{ config('facturacion.emisor.razon_social') ?: 'Farmacia' }}</h2>
        <p>NIT: {{ config('facturacion.emisor.nit') ?: '-' }}</p>
        <p>Factura N°: {{ $invoice->numero_factura ?? '-' }} &nbsp;|&nbsp; Ambiente: {{ $invoice->ambiente }}</p>
    </div>

    <table class="meta">
        <tr>
            <td><strong>Cliente / Razón social:</strong> {{ $invoice->razon_social }}</td>
            <td><strong>NIT/CI:</strong> {{ $invoice->nit_cliente }}</td>
        </tr>
        <tr>
            <td><strong>Fecha:</strong> {{ $invoice->sale->sale_date->format('d/m/Y') }}</td>
            <td><strong>Estado:</strong> {{ $invoice->estado_label }}</td>
        </tr>
        <tr>
            <td><strong>CUF:</strong> {{ $invoice->cuf ?? '-' }}</td>
            <td><strong>Código de control:</strong> {{ $invoice->codigo_control ?? '-' }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio unitario</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->sale->details as $detail)
                <tr>
                    <td>{{ $detail->product?->name }}</td>
                    <td>{{ $detail->quantity }}</td>
                    <td class="text-right">{{ number_format($detail->sale_price, 2) }}</td>
                    <td class="text-right">{{ number_format($detail->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totales">
        <tr>
            <td>Subtotal</td>
            <td class="text-right">{{ number_format($invoice->sale->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td>Descuento</td>
            <td class="text-right">{{ number_format($invoice->sale->discount, 2) }}</td>
        </tr>
        <tr>
            <td><strong>Total</strong></td>
            <td class="text-right"><strong>{{ number_format($invoice->total, 2) }}</strong></td>
        </tr>
    </table>

    @if ($invoice->estado === 'ANULADA')
        <p style="margin-top: 20px; color: #b91c1c;">
            <strong>Factura anulada</strong> el {{ optional($invoice->anulado_at)->format('d/m/Y H:i') }}.
            Motivo: {{ $invoice->anulado_reason }}
        </p>
    @endif

    @if ($invoice->isSimulado())
        <p style="margin-top: 20px; font-size: 10px; color: #666;">
            Esta es una representación de prueba generada en modo simulado. No constituye un documento fiscal válido ante el SIN.
        </p>
    @endif

</body>
</html>
