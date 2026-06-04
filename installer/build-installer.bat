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
set "ROOT=%CD%"

set "COMPOSER_CMD=composer"
where composer >nul 2>&1
if errorlevel 1 (
    if exist "%ROOT%\composer.phar" (
        set "COMPOSER_CMD=php "%ROOT%\composer.phar""
    ) else (
        echo Composer lipseste. Instaleaza Composer sau pune composer.phar in radacina proiectului.
        goto :fail
    )
)

if exist "%ROOT%\vendor\autoload.php" if exist "%ROOT%\public\build\manifest.json" (
    echo [OK] vendor\ si public\build\ deja prezente - sar composer/npm
    goto :inno
)

echo Dependente PHP (production)...
call %COMPOSER_CMD% install --no-interaction --prefer-dist --no-dev --optimize-autoloader --ignore-platform-reqs
if errorlevel 1 goto :fail

where npm >nul 2>&1
if errorlevel 1 (
    if exist "%ROOT%\public\build\manifest.json" (
        echo [OK] npm lipseste dar public\build exista deja - sar peste npm run build
    ) else (
        echo Node.js / npm lipseste si public\build nu exista.
        echo Instaleaza Node.js LTS: https://nodejs.org  sau: scoop install nodejs-lts
        goto :fail
    )
) else (
    echo Compilare assets frontend...
    call npm run build
    if errorlevel 1 goto :fail
)

cd /d "%~dp0"

:inno
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
