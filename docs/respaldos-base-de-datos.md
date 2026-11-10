# Respaldos de la base de datos

Se respalda **solo la base de datos** (`bd_farmacia`) **cada hora**.

| Qué | Dónde / valor |
|---|---|
| Carpeta de respaldos | `D:\respaldos_farmafamilia` |
| Nombre de cada respaldo | `bd_farmacia_AAAA-MM-DD_HH-mm.sql.zip` |
| Cuánto tiempo se guardan | 30 días (los más viejos se borran solos) |
| Registro de cada respaldo | `D:\respaldos_farmafamilia\respaldo.log` (`OK` o `ERROR`) |
| Script | `scripts/backup-db.ps1` (lee la conexión del `.env`) |
| Tarea programada de Windows | **FarmaFamilia - Respaldo BD** (cada hora, sin ventanas) |

> La tarea corre mientras la sesión de Windows del usuario esté iniciada
> (en la compu de mostrador la sesión queda abierta) y WAMP/MySQL encendido.

## Revisar que se están haciendo

Abrir `D:\respaldos_farmafamilia\respaldo.log`: cada hora debe aparecer una
línea `OK`. Si aparece `ERROR`, normalmente es porque MySQL (WAMP) estaba
apagado en ese momento.

## Hacer un respaldo a mano (en cualquier momento)

```powershell
powershell -ExecutionPolicy Bypass -File D:\wampp\www\app_farmacia\scripts\backup-db.ps1
```

## Restaurar un respaldo

**Esto reemplaza todos los datos actuales por los del respaldo.** Antes,
hacer un respaldo a mano del estado actual (paso anterior), por si acaso.

1. Elegir el `.zip` de la fecha/hora deseada en `D:\respaldos_farmafamilia`
   y descomprimirlo (clic derecho → Extraer todo). Queda un `.sql`.
2. Abrir **cmd** y ejecutar (cambiando el nombre del archivo):

```bat
"D:\wampp\bin\mysql\mysql8.4.7\bin\mysql.exe" --user=root --default-character-set=utf8mb4 -e "DROP DATABASE IF EXISTS bd_farmacia; CREATE DATABASE bd_farmacia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
"D:\wampp\bin\mysql\mysql8.4.7\bin\mysql.exe" --user=root --default-character-set=utf8mb4 bd_farmacia < "D:\respaldos_farmafamilia\bd_farmacia_2026-11-03_23-05.sql"
```

3. Entrar al sistema y verificar.

## Recomendación

Los respaldos quedan en el mismo disco de la computadora. Si el disco falla,
se pierden junto con todo. De vez en cuando (por ejemplo una vez por semana)
copiar la carpeta `D:\respaldos_farmafamilia` a una memoria USB o a Google
Drive.

## Volver a crear la tarea programada (máquina nueva)

En PowerShell (como el usuario que deja la sesión abierta):

```powershell
$vbs = 'D:\wampp\www\app_farmacia\scripts\backup-db-oculto.vbs'
$accion = New-ScheduledTaskAction -Execute 'wscript.exe' -Argument "`"$vbs`""
$disparador = New-ScheduledTaskTrigger -Once -At (Get-Date).Date.AddHours((Get-Date).Hour + 1) -RepetitionInterval (New-TimeSpan -Hours 1) -RepetitionDuration (New-TimeSpan -Days 3650)
$ajustes = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -StartWhenAvailable -ExecutionTimeLimit (New-TimeSpan -Minutes 15) -MultipleInstances IgnoreNew
Register-ScheduledTask -TaskName 'FarmaFamilia - Respaldo BD' -Action $accion -Trigger $disparador -Settings $ajustes -Force
```
