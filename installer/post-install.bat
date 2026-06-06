@echo off
setlocal EnableDelayedExpansion
title Julius Fitness Gym - Configurare
color 0A

set "APP_ROOT=%~dp0.."
cd /d "%APP_ROOT%"

call "%APP_ROOT%\installer\check-prerequisites.bat" "%APP_ROOT%"
if errorlevel 1 exit /b 1

echo.
echo  Configurare Julius Fitness Gym...
echo.

if exist "%APP_ROOT%\storage\app\.install-complete" (
    echo  [OK] Instalarea a fost deja rulata — nu se reconfigureaza automat.
    echo  Pentru reset parola admin, ruleaza in terminal:
    echo    php artisan app:install --force --email=admin@julius.test --password=PAROLA_TA --url=http://julius-fitness-gym.test
    echo.
    exit /b 0
)

if not exist "%APP_ROOT%\database\database.sqlite" (
    echo  Creare baza de date SQLite...
    if not exist "%APP_ROOT%\database" mkdir "%APP_ROOT%\database"
    type nul > "%APP_ROOT%\database\database.sqlite"
)

if not exist "%APP_ROOT%\vendor\autoload.php" (
    echo  Composer install...
    call composer install --no-interaction --prefer-dist
    if errorlevel 1 (
        echo  Eroare la composer install.
        pause
        exit /b 1
    )
) else (
    echo  [OK] Dependente PHP deja incluse
)

if not exist "%APP_ROOT%\public\build\manifest.json" (
    echo  NPM install si build...
    call npm install --ignore-scripts
    if errorlevel 1 goto :failed
    call npm run build
    if errorlevel 1 goto :failed
) else (
    echo  [OK] Assets frontend deja compilate
)

if not exist "%APP_ROOT%\.env" (
    echo  Copiere .env.example -^> .env
    copy /Y "%APP_ROOT%\.env.example" "%APP_ROOT%\.env" >nul
)

echo  Generare cheie aplicatie...
call php artisan key:generate --force
if errorlevel 1 goto :failed

echo  Migrari baza de date...
call php artisan migrate --force
if errorlevel 1 goto :failed

echo  Legatura storage...
call php artisan storage:link 2>nul

where herd >nul 2>&1
if not errorlevel 1 (
    echo  Configurare site in Laravel Herd...
    cd /d "%APP_ROOT%"
    herd link julius-fitness-gym 2>nul
    if errorlevel 1 herd link 2>nul
    herd init --no-interaction 2>nul
)

echo  Creare utilizator admin...
REM Parola explicita = fara ecran "Change password" la fiecare login (schimba-o din cont dupa prima utilizare)
call php artisan app:install --no-interaction --email=admin@julius.test --password=GymTest2026! --url=http://julius-fitness-gym.test
if errorlevel 1 goto :failed

echo.
echo  ========================================
echo   Instalare finalizata cu succes!
echo  ========================================
echo   Site:  http://julius-fitness-gym.test
echo   Admin: http://julius-fitness-gym.test/admin
echo.
if exist "%APP_ROOT%\storage\app\install-credentials.txt" (
    echo  Credentiale salvate in storage\app\install-credentials.txt
    type "%APP_ROOT%\storage\app\install-credentials.txt"
    echo.
)
exit /b 0

:failed
echo.
echo  Instalarea a esuat. Verifica mesajele de mai sus.
pause
exit /b 1
