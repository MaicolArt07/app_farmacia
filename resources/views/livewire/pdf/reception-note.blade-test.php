<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nota de Recepción de Equipo</title>
    <style>
        @page {
            margin: 40px 40px 80px 40px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
        }

        .logo {
            font-weight: bold;
            font-size: 14px;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            flex-grow: 1;
            margin-top: 0;
        }

        .doc-number {
            text-align: right;
            font-size: 11px;
        }

        .doc-number span {
            color: red;
            border: 1px solid red;
            padding: 1px 4px;
            font-weight: bold;
        }

        .section {
            margin-top: 10px;
        }

        .field-row {
            margin-bottom: 4px;
        }

        .field-label {
            font-weight: bold;
            display: inline-block;
            width: 80px;
        }

        .grid-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .grid-table td,
        .grid-table th {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: top;
        }

        .grid-table th {
            text-align: left;
            background-color: #f0f0f0;
        }

        .checkbox {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1px solid #000;
            margin-right: 5px;
            vertical-align: middle;
        }

        .checked {
            background-color: #000;
        }

        .signatures {
            margin-top: 30px;
            text-align: center;
        }

        .signature-block {
            display: inline-block;
            width: 30%;
            margin: 0 1%;
            font-size: 10px;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 40px;
            margin-bottom: 5px;
        }

        .footer {
            position: fixed;
            bottom: 10px;
            left: 40px;
            right: 40px;
            font-size: 9px;
            text-align: center;
        }

        .footer-note {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>

    <!-- ENCABEZADO -->
    <div class="header">
        <div class="logo" style="text-align: left;">
            <img src="{{ public_path('img/logo_pbt.png') }}" alt="PBTECHNOLOGIES SRL" style="height: 40px;">
        </div>
        <div class="title">NOTA DE RECEPCIÓN DE EQUIPO</div>
        <div class="doc-number">
            N° <span>________</span><br>
            Fecha: __/__/____
        </div>
    </div>

    <!-- DATOS GENERALES -->
    <div class="section">
        <div class="field-row"><span class="field-label">Empresa:</span> ________________________</div>
        <div class="field-row"><span class="field-label">Contacto:</span> ________________________</div>
        <div class="field-row"><span class="field-label">Email:</span> ________________________</div>
        <div class="field-row"><span class="field-label">Teléfono:</span> ________________________</div>
        <div class="field-row"><span class="field-label">Ref.:</span> ________________________</div>
    </div>

    <!-- EQUIPO -->
    <table class="grid-table">
        <tr><td colspan="2"><strong>DATOS DEL EQUIPO</strong></td></tr>
        <tr>
            <td width="30%">Modelo:<br>________________________</td>
            <td>Descripción:<br>________________________</td>
        </tr>
        <tr>
            <td>Marca:<br>________________________</td>
            <td>N° Serie:<br>________________________</td>
        </tr>
    </table>

    <!-- DATOS TÉCNICOS -->
    <table class="grid-table">
        <tr><td><strong>DATOS TÉCNICOS</strong><br><br>__________________________________________</td></tr>
    </table>

    <div class="section">
        Enciende: <span class="checkbox"></span>
        No Enciende: <span class="checkbox"></span>
    </div>

    <!-- TRABAJO -->
    <table class="grid-table">
        <tr><td><strong>TRABAJO A REALIZAR</strong></td></tr>
        <tr>
            <td>
                <span class="checkbox"></span> Calibración<br>
                <span class="checkbox"></span> Mantenimiento<br>
                <span class="checkbox"></span> Cambio de repuestos<br>
                <span class="checkbox"></span> Servicio por garantía<br>
                <span class="checkbox"></span> Otros
            </td>
        </tr>
    </table>

    <!-- FIRMAS -->
    <div class="signatures">
        <div class="signature-block">
            <div class="signature-line"></div>
            <strong>Recibido por:</strong><br>
            Nombre: ___________________<br>
            CI: ___________________<br>
            Cargo: ___________________
        </div>
        <div class="signature-block">
            <div class="signature-line"></div>
            <strong>Entregado por:</strong><br>
            Nombre: ___________________<br>
            CI: ___________________<br>
            Cargo: ___________________
        </div>
        <div class="signature-block">
            <div class="signature-line"></div>
            <strong>Recogido por:</strong><br>
            Nombre: ___________________<br>
            CI: ___________________<br>
            Cargo: ___________________
        </div>
    </div>

    <!-- FOOTER FIJO -->
    <div class="footer">
        <div class="footer-note">
            * Una vez concluido el servicio será notificado por email y tiene 90 días para recoger el equipo de nuestras oficinas.<br>
            * Para el recojo del equipo debe presentarse esta nota.
        </div>
        Av. Cristo Redentor C/Osorio N° 2015, Santa Cruz - Bolivia | Telf.: (591) 3-3454600 | Cel.: 71033004 | info@pbt.com.bo | www.pbt.com.bo
    </div>

</body>
</html>
