@echo off
setlocal EnableDelayedExpansion
title Julius Fitness Gym - Setup test sală
cd /d "%~dp0.."

echo.
echo  Setup pentru test in sala de fitness...
echo.

if not exist "database\database.sqlite" (
    if not exist "database" mkdir "database"
    type nul > "database\database.sqlite"
)

call composer install --no-interaction --prefer-dist
if errorlevel 1 exit /b 1

if not exist ".env" copy /Y ".env.example" ".env"

if not exist "storage\data\settingsData.json" (
    if not exist "storage\data" mkdir "storage\data"
    copy /Y "storage\data\settingsData.json.example" "storage\data\settingsData.json"
)

call php artisan key:generate --force
call php artisan migrate --force
call php artisan storage:link 2>nul

where node >nul 2>&1
if errorlevel 1 (
    echo  [ATENTIE] Node.js lipseste - ruleaza npm run build pe alt PC sau instaleaza Node.js
) else (
    call npm install --ignore-scripts
    call npm run build
)

echo.
echo  Creare admin de test (parola temporara)...
call php artisan app:install --no-interaction --force --email=admin@julius.test --password=GymTest2026! --url=http://127.0.0.1:8000
if errorlevel 1 exit /b 1

echo.
echo  Incarcare date demo (planuri, membri, abonamente)...
call php artisan db:seed --force

echo.
echo  ========================================
echo   Setup finalizat!
echo  ========================================
echo   Pornire:  php artisan serve
echo   Admin:    http://127.0.0.1:8000/admin/login
echo   Office:   http://127.0.0.1:8000/office/login
echo   Email:    admin@julius.test
echo   Parola:   GymTest2026!  (TEMPORARA - schimba dupa login)
echo.
exit /b 0
