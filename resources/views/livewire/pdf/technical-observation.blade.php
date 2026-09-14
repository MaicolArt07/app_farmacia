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
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
            overflow: hidden;
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

        .grid-table td,
        .grid-table th {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }

        .grid-table th {
            background-color: #f0f0f0;
            text-align: left;
        }

        .photo-cell {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 8px;
            page-break-inside: avoid;
        }

        .photo-caption {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 6px;
            text-align: center;
        }

        .photo-img {
            display: block;
            margin: 0 auto;
            max-width: 150px;
            max-height: 110px;
            width: auto;
            height: auto;
            border: 1px solid #bbb;
            padding: 3px;
        }

        .page-break {
            page-break-before: always;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }

        .footer {
            font-size: 9px;
            text-align: center;
            margin-top: 20rem;
        }

        .footer-note {
            margin-bottom: 5px;
        }
    </style>
</head>

<body>

    <!-- ENCABEZADO -->
    <div class="header">
        <div style="float:left;">
            <img src="{{ public_path('img/logo_pbt.png') }}" alt="Logo PBT" style="height:50px; vertical-align:middle;">
            <span style="font-weight:bold; font-size:14px; vertical-align:middle; margin-left:5px;">PBTECHNOLOGIES SRL</span>
        </div>

        <div style="clear:both; text-align:center; font-size:16px; font-weight:bold; margin-top:5px;">
            INFORME TÉCNICO
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

    <!-- DATOS DEL EQUIPO -->
    <table class="grid-table">
        <tr>
            <th colspan="2">DATOS DEL EQUIPO</th>
        </tr>
        <tr>
            <td width="30%">Nombre/Modelo:<br>{{ $note->equipment->name ?? '' }} / {{ $note->equipment->model ?? '' }}</td>
            <td>Descripción:<br>{{ $note->equipment->description ?? '' }}</td>
        </tr>
        <tr>
            <td>Marca:<br>{{ $note->equipment->brand ?? '' }}</td>
            <td>N° Serie:<br>{{ $note->equipment->serial_number ?? '' }}</td>
        </tr>
    </table>

    <!-- TRABAJO REALIZADO -->
    <table class="grid-table">
        <tr>
            <th>TRABAJO REALIZADOS</th>
        </tr>
        <tr>
            <td>
                {!! nl2br(e($note->technical_observation ?? '')) !!}
            </td>
        </tr>
    </table>

    <!-- REGISTRO FOTOGRÁFICO -->
    @if($note->technicalImages && $note->technicalImages->count())
        <table class="grid-table" style="margin-top:10px;">
            <tr>
                <th colspan="3">REGISTRO FOTOGRÁFICO</th>
            </tr>

            @php
                $images = $note->technicalImages;
                $chunks = $images->chunk(3);
            @endphp

            @foreach($chunks as $group)
                <tr>
                    @if($group->count() == 1)
                        <td class="photo-cell">&nbsp;</td>
                        <td class="photo-cell">
                            <div class="photo-caption">{{ $group[0]->caption }}</div>
                            <img src="{{ public_path('storage/' . $group[0]->image_path) }}" class="photo-img">
                        </td>
                        <td class="photo-cell">&nbsp;</td>

                    @elseif($group->count() == 2)
                        <td class="photo-cell">
                            <div class="photo-caption">{{ $group[0]->caption }}</div>
                            <img src="{{ public_path('storage/' . $group[0]->image_path) }}" class="photo-img">
                        </td>
                        <td class="photo-cell">
                            <div class="photo-caption">{{ $group[1]->caption }}</div>
                            <img src="{{ public_path('storage/' . $group[1]->image_path) }}" class="photo-img">
                        </td>
                        <td class="photo-cell">&nbsp;</td>

                    @else
                        @foreach($group as $img)
                            <td class="photo-cell">
                                <div class="photo-caption">{{ $img->caption }}</div>
                                <img src="{{ public_path('storage/' . $img->image_path) }}" class="photo-img">
                            </td>
                        @endforeach
                    @endif
                </tr>
            @endforeach
        </table>
    @endif

    <!-- NUEVA HOJA -->
    <div class="page-break"></div>

    <div class="section-title">
        INFORME TÉCNICO DETALLADO
    </div>

    <!-- REPORTE DE FALLA/DAÑO -->
    @if(!empty($note->detail_technical))
        <table class="grid-table" style="margin-top:10px;">
            <tr>
                <th>REPORTE DE FALLA/DAÑO</th>
            </tr>
            <tr>
                <td>
                    {!! nl2br(e($note->detail_technical)) !!}
                </td>
            </tr>
        </table>
    @endif

    <!-- RECOMENDACIONES -->
    @if(!empty($note->recomendation_technical))
        <table class="grid-table" style="margin-top:10px;">
            <tr>
                <th>RECOMENDACIONES</th>
            </tr>
            <tr>
                <td>
                    {!! nl2br(e($note->recomendation_technical)) !!}
                </td>
            </tr>
        </table>
    @endif

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