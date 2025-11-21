# Script para build y deploy de Inbolsa
# Asegura que todos los procesos que bloquean archivos se cierren

Write-Host "Limpiando carpeta dist..." -ForegroundColor Yellow

# Intentar eliminar dist varias veces si falla
$maxAttempts = 3
$attempt = 0
$success = $false

while (-not $success -and $attempt -lt $maxAttempts) {
    try {
        if (Test-Path ".\dist") {
            Remove-Item -Path ".\dist" -Recurse -Force -ErrorAction Stop
        }
        $success = $true
        Write-Host "Carpeta dist eliminada exitosamente" -ForegroundColor Green
    }
    catch {
        $attempt++
        Write-Host "Intento $attempt de $maxAttempts fallido. Esperando 2 segundos..." -ForegroundColor Red
        Start-Sleep -Seconds 2

        # Intentar cerrar procesos que puedan estar bloqueando
        Get-Process | Where-Object {$_.MainWindowTitle -like "*dist*" -or $_.MainWindowTitle -like "*webp*"} | Stop-Process -Force -ErrorAction SilentlyContinue
    }
}

if (-not $success) {
    Write-Host "No se pudo eliminar la carpeta dist. Intenta cerrar manualmente el explorador de archivos." -ForegroundColor Red
    exit 1
}

Write-Host "`nEjecutando npm run build..." -ForegroundColor Yellow
npm run build

if ($LASTEXITCODE -eq 0) {
    Write-Host "`nBuild exitoso! Copiando archivos a XAMPP..." -ForegroundColor Green

    # Asegurar que el directorio de destino existe
    $destPath = "C:\xampp\htdocs\inbolsaNeo\"
    if (-not (Test-Path $destPath)) {
        New-Item -ItemType Directory -Path $destPath -Force | Out-Null
    }

    # Copiar archivos
    Copy-Item -Path ".\dist\*" -Destination $destPath -Recurse -Force

    Write-Host "`nDeploy completado exitosamente!" -ForegroundColor Green
    Write-Host "Los archivos están en: $destPath" -ForegroundColor Cyan
} else {
    Write-Host "`nError en el build. Por favor revisa los errores arriba." -ForegroundColor Red
    exit 1
}
