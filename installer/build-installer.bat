@echo off
setlocal
cd /d "%~dp0"

echo Construire installer Julius Fitness Gym v1.0...
echo.

if not exist "..\public\favicon.ico" (
    echo Generare public\favicon.ico din SVG...
    where magick >nul 2>&1
    if not errorlevel 1 (
        magick convert "..\public\favicon.svg" -define icon:auto-resize=256,128,64,48,32,16 "..\public\favicon.ico"
    ) else (
        echo ATENTIE: Lipseste public\favicon.ico si ImageMagick ^(magick^) nu e instalat.
        echo Instaleaza ImageMagick sau pune manual favicon.ico in public\
        pause
        exit /b 1
    )
)

cd /d "%~dp0.."
echo Compilare assets frontend...
call npm run build
if errorlevel 1 (
    echo Eroare la npm run build.
    pause
    exit /b 1
)

cd /d "%~dp0"

set ISCC=
if exist "C:\Program Files (x86)\Inno Setup 6\ISCC.exe" set "ISCC=C:\Program Files (x86)\Inno Setup 6\ISCC.exe"
if exist "C:\Program Files\Inno Setup 6\ISCC.exe" set "ISCC=C:\Program Files\Inno Setup 6\ISCC.exe"

if "%ISCC%"=="" (
    echo Inno Setup 6 nu a fost gasit.
    echo Instaleaza de la: https://jrsoftware.org/isinfo.php
    pause
    exit /b 1
)

"%ISCC%" julius-fitness-gym.iss
if errorlevel 1 (
    echo Eroare la compilarea installerului.
    pause
    exit /b 1
)

echo.
echo Gata! Installer creat in: dist\Julius-Fitness-Gym-Setup-v1.0.exe
pause
