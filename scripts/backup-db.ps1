# ============================================================================
#  Respaldo de la base de datos de FarmaFamilia (solo la base de datos)
#
#  - Lee la conexión (DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD)
#    del archivo .env del proyecto, así no hay contraseñas duplicadas aquí.
#  - Genera un .sql con mysqldump de WAMP, verifica que terminó completo,
#    lo comprime en .zip y borra los respaldos con más de $DiasRetencion días.
#  - Deja un registro en respaldo.log dentro de la carpeta de respaldos.
#
#  Se ejecuta cada hora con la tarea programada "FarmaFamilia - Respaldo BD"
#  (ver docs/respaldos-base-de-datos.md). Se puede correr a mano:
#     powershell -ExecutionPolicy Bypass -File scripts\backup-db.ps1
# ============================================================================

param(
    [string]$CarpetaRespaldos = 'D:\respaldos_farmafamilia',
    [int]$DiasRetencion = 30
)

$ErrorActionPreference = 'Stop'
$proyecto = Split-Path -Parent $PSScriptRoot
$log = Join-Path $CarpetaRespaldos 'respaldo.log'

function Escribir-Log([string]$mensaje) {
    $linea = '{0}  {1}' -f (Get-Date -Format 'yyyy-MM-dd HH:mm:ss'), $mensaje
    Add-Content -Path $log -Value $linea -Encoding UTF8
}

New-Item -ItemType Directory -Force -Path $CarpetaRespaldos | Out-Null

try {
    # --- Leer conexión desde .env -------------------------------------------
    $envVars = @{}
    Get-Content (Join-Path $proyecto '.env') | ForEach-Object {
        if ($_ -match '^\s*(DB_[A-Z_]+)\s*=\s*(.*)\s*$') {
            $envVars[$matches[1]] = $matches[2].Trim().Trim('"').Trim("'")
        }
    }
    $dbHost = if ($envVars['DB_HOST']) { $envVars['DB_HOST'] } else { 'localhost' }
    $dbPort = if ($envVars['DB_PORT']) { $envVars['DB_PORT'] } else { '3306' }
    $dbName = $envVars['DB_DATABASE']
    $dbUser = $envVars['DB_USERNAME']
    if (-not $dbName -or -not $dbUser) { throw 'Faltan DB_DATABASE o DB_USERNAME en .env' }

    # --- Ubicar mysqldump de WAMP (MySQL, la versión más nueva) -------------
    $mysqldump = Get-ChildItem 'D:\wampp\bin\mysql\*\bin\mysqldump.exe' -ErrorAction SilentlyContinue |
        Sort-Object FullName -Descending | Select-Object -First 1 -ExpandProperty FullName
    if (-not $mysqldump) { throw 'No se encontró mysqldump.exe en D:\wampp\bin\mysql' }

    # --- Generar el respaldo -------------------------------------------------
    $marca = Get-Date -Format 'yyyy-MM-dd_HH-mm'
    $sql = Join-Path $CarpetaRespaldos "$dbName`_$marca.sql"
    $zip = "$sql.zip"

    # La contraseña va por variable de entorno (no queda visible en la línea de comandos).
    $env:MYSQL_PWD = $envVars['DB_PASSWORD']

    $argumentos = @(
        "--host=$dbHost", "--port=$dbPort", "--user=$dbUser",
        '--single-transaction', '--quick', '--routines', '--triggers', '--events',
        '--default-character-set=utf8mb4', '--no-tablespaces',
        "--result-file=$sql", $dbName
    )
    $proc = Start-Process -FilePath $mysqldump -ArgumentList $argumentos -NoNewWindow -Wait -PassThru `
        -RedirectStandardError (Join-Path $env:TEMP 'farmafamilia_mysqldump_err.txt')
    Remove-Item Env:\MYSQL_PWD -ErrorAction SilentlyContinue

    # --- Verificar que el respaldo esté completo ----------------------------
    $ultimaLinea = if (Test-Path $sql) { Get-Content $sql -Tail 1 } else { '' }
    if ($proc.ExitCode -ne 0 -or $ultimaLinea -notmatch 'Dump completed') {
        $detalle = Get-Content (Join-Path $env:TEMP 'farmafamilia_mysqldump_err.txt') -Raw -ErrorAction SilentlyContinue
        Remove-Item $sql -ErrorAction SilentlyContinue
        throw "mysqldump falló (código $($proc.ExitCode)). $detalle"
    }

    # --- Comprimir -----------------------------------------------------------
    Compress-Archive -Path $sql -DestinationPath $zip -Force
    Remove-Item $sql
    $kb = [math]::Round((Get-Item $zip).Length / 1KB, 1)

    # --- Borrar respaldos viejos --------------------------------------------
    $limite = (Get-Date).AddDays(-$DiasRetencion)
    $viejos = Get-ChildItem $CarpetaRespaldos -Filter "$dbName`_*.sql.zip" | Where-Object { $_.LastWriteTime -lt $limite }
    $viejos | Remove-Item -Force

    Escribir-Log "OK    $(Split-Path $zip -Leaf) ($kb KB). Borrados por antigüedad: $(@($viejos).Count)"
}
catch {
    Escribir-Log "ERROR $($_.Exception.Message)"
    exit 1
}
