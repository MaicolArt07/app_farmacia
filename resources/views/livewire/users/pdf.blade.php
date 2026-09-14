<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nota de Recepción de Equipo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 30px;
        }

        h1 {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .sub-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
        }

        .logo {
            font-weight: bold;
            font-size: 14px;
        }

        .numero {
            text-align: right;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        td, th {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }

        .section-title {
            background-color: #f0f0f0;
            font-weight: bold;
            text-transform: uppercase;
        }

        .checkbox {
            display: inline-block;
            width: 10px;
            height: 10px;
            border: 1px solid #000;
            margin-right: 5px;
        }

        .checked {
            background-color: #000;
        }

        .firma {
            margin-top: 40px;
            text-align: center;
        }

        .firma > div {
            display: inline-block;
            width: 30%;
            vertical-align: top;
            font-size: 11px;
            line-height: 1.4;
        }

        .firma-line {
            margin-top: 40px;
            margin-bottom: 5px;
            border-top: 1px solid #000;
            width: 100%;
        }
    </style>
</head>
<body>

    <!-- TÍTULO -->
    <h1>NOTA DE RECEPCIÓN DE EQUIPO</h1>

    <!-- LOGO Y Nº DE RECEPCIÓN -->
    <div class="sub-header">
        <div class="logo">
            PBTECHNOLOGIES SRL
        </div>
        <div class="numero">
            Fecha: <!-- {{ \Carbon\Carbon::now()->format('d/m/Y') }} --> <br>
            N°: <!-- 00001 -->
        </div>
    </div>

    <!-- DATOS GENERALES -->
    <table>
        <tr><td colspan="2" class="section-title">Empresa / Contacto</td></tr>
        <tr><td style="width: 25%;">Empresa:</td><td><!-- Empresa --></td></tr>
        <tr><td>Contacto:</td><td><!-- Contacto --></td></tr>
        <tr><td>Email:</td><td><!-- Email --></td></tr>
        <tr><td>Teléfono:</td><td><!-- Teléfono --></td></tr>
        <tr><td>Ref.:</td><td><!-- Dirección --></td></tr>
    </table>

    <!-- EQUIPO -->
    <table>
        <tr><td colspan="2" class="section-title">Datos del equipo</td></tr>
        <tr><td style="width: 25%;">Modelo:</td><td><!-- Modelo --></td></tr>
        <tr><td>Marca:</td><td><!-- Marca --></td></tr>
        <tr><td>N° Serie:</td><td><!-- Nº Serie --></td></tr>
        <tr><td>Descripción:</td><td><!-- Descripción --></td></tr>
    </table>

    <!-- TÉCNICOS -->
    <table>
        <tr><td class="section-title">Datos técnicos</td></tr>
        <tr><td style="height: 80px;"><!-- Técnicos --></td></tr>
    </table>

    <!-- ACCESORIOS -->
    <table>
        <tr><td class="section-title">Accesorios</td></tr>
        <tr><td style="height: 50px;"><!-- Accesorios --></td></tr>
        <tr><td>Enciende: <span class="checkbox"></span></td></tr>
    </table>

    <!-- TRABAJO -->
    <table>
        <tr><td class="section-title">Trabajo a realizar</td></tr>
        <tr><td>
            <div><span class="checkbox"></span> Calibración</div><br>
            <div><span class="checkbox"></span> Mantenimiento</div><br>
            <div><span class="checkbox"></span> Cambio de repuestos</div><br>
            <div><span class="checkbox"></span> Servicio por garantía</div><br>
            <div><span class="checkbox"></span> Otros</div>
        </td></tr>
    </table>

    <!-- FIRMAS -->
    <div class="firma">
        <div>
            <div class="firma-line"></div>
            <strong>Recibido por:</strong><br>
            Nombre: ___________<br>
            C.I.: ___________<br>
            Cargo: ___________
        </div>
        <div>
            <div class="firma-line"></div>
            <strong>Entregado por:</strong><br>
            Nombre: ___________<br>
            C.I.: ___________<br>
            Cargo: ___________
        </div>
        <div>
            <div class="firma-line"></div>
            <strong>Recogido por:</strong><br>
            Nombre: ___________<br>
            C.I.: ___________<br>
            Cargo: ___________
        </div>
    </div>

</body>
</html>
        