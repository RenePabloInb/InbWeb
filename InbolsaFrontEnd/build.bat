@echo off
echo ========================================
echo   INBOLSA - Build y Deploy Script
echo ========================================
echo.

echo [1/5] Deteniendo servidor de desarrollo...
tasklist /FI "IMAGENAME eq node.exe" 2>NUL | find /I /N "node.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo Cerrando procesos Node.js...
    taskkill /F /IM "node.exe" /T >nul 2>&1
)

echo [2/5] Cerrando procesos que puedan bloquear archivos...
taskkill /F /IM "Microsoft.Photos.exe" >nul 2>&1
taskkill /F /IM "explorer.exe" >nul 2>&1
start explorer.exe

echo [3/5] Esperando 3 segundos para que se liberen archivos...
timeout /t 3 >nul

echo [4/5] Limpiando carpeta dist (con reintentos)...
set /a attempts=0
:retry
if exist "dist" (
    rmdir /s /q "dist" 2>nul
    if exist "dist" (
        set /a attempts+=1
        if %attempts% LSS 5 (
            echo Reintento %attempts%/5...
            timeout /t 2 >nul
            goto retry
        ) else (
            echo ERROR: No se pudo eliminar la carpeta dist despues de 5 intentos
            echo SOLUCION: Cierra TODOS los exploradores de Windows y vuelve a ejecutar
            pause
            exit /b 1
        )
    )
)

echo [5/5] Ejecutando npm run build...
call npm run build

if %errorlevel% neq 0 (
    echo.
    echo ERROR: El build fallo
    pause
    exit /b 1
)

echo.
echo ========================================
echo   Copiando archivos a XAMPP...
echo ========================================

if not exist "C:\xampp\htdocs\inbolsaNeo\" mkdir "C:\xampp\htdocs\inbolsaNeo\"
xcopy /E /I /Y "dist\*" "C:\xampp\htdocs\inbolsaNeo\" >nul

echo.
echo ========================================
echo   BUILD Y DEPLOY COMPLETADO!
echo ========================================
echo.
echo Los archivos estan en: C:\xampp\htdocs\inbolsaNeo\
echo.
pause
