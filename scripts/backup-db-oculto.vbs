' Lanza backup-db.ps1 sin abrir ninguna ventana (lo usa la tarea programada
' "FarmaFamilia - Respaldo BD", para que no aparezca una consola cada hora).
Set fso = CreateObject("Scripting.FileSystemObject")
script = fso.BuildPath(fso.GetParentFolderName(WScript.ScriptFullName), "backup-db.ps1")
CreateObject("WScript.Shell").Run "powershell.exe -NoProfile -ExecutionPolicy Bypass -File """ & script & """", 0, False
