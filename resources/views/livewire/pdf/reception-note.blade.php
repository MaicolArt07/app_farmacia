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
            color: #000;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
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
            margin-top: 15px;
        }

        .field-row {
            margin-bottom: 6px;
        }

        .field-label {
            font-weight: bold;
            display: inline-block;
            width: 100px;
        }

        .grid-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 11px;
        }

        .grid-table td, .grid-table th {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }

        .grid-table th {
            background-color: #f0f0f0;
            text-align: left;
        }

        .checkbox {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 1px solid #000;
            margin-right: 5px;
            vertical-align: middle;
        }

        .checked {
            background-color: #000;
        }

        .signatures {
            margin-top: 180px;
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
            font-size: 9px;
            text-align: center;
            margin-top: 50px;
        }

        .footer-note {
            margin-bottom: 5px;
        }

    </style>
</head>
<body>

    <!-- ENCABEZADO -->
    <div class="header" style="border-bottom:2px solid #000; padding-bottom:10px; margin-bottom:10px; overflow:hidden;">

        <!-- Primera fila: Logo a la izquierda y número de documento a la derecha -->
        <div style="float:left;">
            <img src="{{ public_path('img/logo_pbt.png') }}" alt="Logo PBT" style="height:50px; vertical-align:middle;">
            <span style="font-weight:bold; font-size:14px; vertical-align:middle; margin-left:5px;">PBTECHNOLOGIES SRL</span>
        </div>

        <div style="float:right; text-align:right; font-size:11px;">
            N° <span style="color:red; border:1px solid red; padding:1px 4px; font-weight:bold;">{{ str_pad($note->reception_note_number, 6, '0', STR_PAD_LEFT) }}</span><br>
            Fecha: {{ $note->reception_date ? \Carbon\Carbon::parse($note->reception_date)->format('d/m/Y') : '' }}
        </div>

        <!-- Segunda fila: Título centrado -->
        <div style="clear:both; text-align:center; font-size:16px; font-weight:bold; margin-top:5px;">
            NOTA DE RECEPCIÓN DE EQUIPO
        </div>

    </div>


    <!-- DATOS GENERALES -->
    <div class="section">
        <div class="field-row"><span class="field-label">Empresa:</span> {{ $note->contactClient->company->name ?? '' }}</div>
        <div class="field-row"><span class="field-label">Contacto:</span> {{ $note->contactClient->name_contact ?? '' }}</div>
        <div class="field-row"><span class="field-label">Email:</span> {{ $note->contactClient->email ?? '' }}</div>
        <div class="field-row"><span class="field-label">Teléfono:</span> {{ $note->contactClient->phone ?? '' }}</div>
        <div class="field-row"><span class="field-label">Ref.:</span> {{ $note->reference ?? '' }}</div>
    </div>

    <!-- EQUIPO -->
    <table class="grid-table">
        <tr><th colspan="2">DATOS DEL EQUIPO</th></tr>
        <tr>
            <td width="30%">Nombre/Modelo:<br>{{ $note->equipment->name ?? '' }} / {{ $note->equipment->model ?? '' }}</td>
            <td>Descripción:<br>{{ $note->equipment->description ?? '' }}</td>
        </tr>
        <tr>
            <td>Marca:<br>{{ $note->equipment->brand ?? '' }}</td>
            <td>N° Serie:<br>{{ $note->equipment->serial_number ?? '' }}</td>
        </tr>
    </table>

    <!-- DATOS TÉCNICOS -->
    <table class="grid-table">
        <tr><th>DATOS TÉCNICOS</th></tr>
        <tr>
            <td>{{ $note->technical_data ?? '' }}</td>
        </tr>
    </table>

    <!-- ENCENDIDO -->
    <div class="section">
        Enciende: <span class="checkbox {{ $note->power_status ? 'checked' : '' }}"></span>
        No Enciende: <span class="checkbox {{ !$note->power_status ? 'checked' : '' }}"></span>
    </div>

    <!-- TRABAJO -->
    <table class="grid-table">
        <tr><th>TRABAJO A REALIZAR</th></tr>
        <tr>
            <td>
                <span class="checkbox {{ $note->id_type_work == 1 ? 'checked' : '' }}"></span> Calibración<br>
                <span class="checkbox {{ $note->id_type_work == 2 ? 'checked' : '' }}"></span> Mantenimiento<br>
                <span class="checkbox {{ $note->id_type_work == 3 ? 'checked' : '' }}"></span> Cambio de repuestos<br>
                <span class="checkbox {{ $note->id_type_work == 4 ? 'checked' : '' }}"></span> Servicio por garantía<br>
                <span class="checkbox {{ $note->id_type_work == 5 ? 'checked' : '' }}"></span> Otros
            </td>
        </tr>
    </table>

    <!-- FIRMAS -->
    <div class="signatures">

        <!-- RECIBIDO POR -->
        <div class="signature-block">
            <div class="signature-line"></div>
            <strong>Recibido por (PBT):</strong><br>
            Nombre: {{ $note->employee->person->name ?? '' }}<br>
            CI: {{ $note->employee->person->ci ?? '' }}<br>
            Cargo: {{ $note->employee->employee_position->name ?? '' }}
        </div>

        <!-- ENTREGADO POR (CLIENTE) -->
        <div class="signature-block">
            <div class="signature-line"></div>
            <strong>Entregado por:</strong><br>
            Nombre: {{ $note->delivered_by ?? '' }}<br>
            CI: {{ $note->delivered_ci ?? '' }}<br>
            Cargo: {{ $note->delivered_position ?? '' }}
        </div>

        <!-- RECOGIDO POR (CLIENTE / RESPONSABLE FINAL) -->
        <div class="signature-block">
            <div class="signature-line"></div>
            <strong>Recogido por:</strong><br>
            Nombre: {{ $note->delivered_to ?? '' }}<br>
            CI: {{ $note->delivered_to_ci ?? '' }}<br>
            Cargo: {{ $note->delivered_to_position ?? '' }}
        </div>

    </div>

    <!-- FOOTER -->
    <div class="footer">
        <div class="footer-note">
            * Una vez concluido el servicio será notificado por email y tiene 90 días para recoger el equipo de nuestras oficinas.<br>
            * Para el recojo del equipo debe presentarse esta nota.
        </div>
        Av. Cristo Redentor C/Osorio N° 2015, Santa Cruz - Bolivia | Telf.: (591) 3-3454600 | Cel.: 71033004 | info@pbt.com.bo | www.pbt.com.bo
    </div>

</body>
</html>
