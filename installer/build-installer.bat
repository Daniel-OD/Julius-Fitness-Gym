@echo off
setlocal
cd /d "%~dp0"

echo Construire pachet Julius Fitness Gym v1.0 (Windows)...
echo.

if not exist "..\public\favicon.ico" (
    echo Generare public\favicon.ico din SVG...
    where magick >nul 2>&1
    if not errorlevel 1 (
        magick convert "..\public\favicon.svg" -define icon:auto-resize=256,128,64,48,32,16 "..\public\favicon.ico"
    ) else (
        echo ATENTIE: Lipseste public\favicon.ico si ImageMagick ^(magick^) nu e instalat.
        pause
        exit /b 1
    )
)

cd /d "%~dp0.."

echo Dependente PHP (production)...
call composer install --no-interaction --prefer-dist --no-dev --optimize-autoloader
if errorlevel 1 goto :fail

echo Compilare assets frontend...
call npm run build
if errorlevel 1 goto :fail

cd /d "%~dp0"

set ISCC=
if exist "C:\Program Files (x86)\Inno Setup 6\ISCC.exe" set "ISCC=C:\Program Files (x86)\Inno Setup 6\ISCC.exe"
if exist "C:\Program Files\Inno Setup 6\ISCC.exe" set "ISCC=C:\Program Files\Inno Setup 6\ISCC.exe"

if "%ISCC%"=="" (
    echo Inno Setup 6 nu a fost gasit.
    pause
    exit /b 1
)

"%ISCC%" julius-fitness-gym.iss
if errorlevel 1 goto :fail

echo.
echo Gata!
echo   Installer: dist\Julius-Fitness-Gym-Setup-v1.0.exe
echo   Clientul necesita doar Laravel Herd (fara Composer/Node).
pause
exit /b 0

:fail
echo Eroare la build.
pause
exit /b 1
