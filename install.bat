@echo off
setlocal
title Julius Fitness Gym - Instalare
color 0A
cd /d "%~dp0"

call "%~dp0installer\check-prerequisites.bat"
if errorlevel 1 exit /b 1

echo.
echo  Instalare Julius Fitness Gym...
echo.

if not exist "database\database.sqlite" (
    echo  Creare baza de date SQLite...
    type nul > "database\database.sqlite"
)

echo  Composer install...
call composer install --no-interaction --prefer-dist
if errorlevel 1 (
    echo  Eroare la composer install.
    pause
    exit /b 1
)

if not exist ".env" (
    echo  Copiere .env.example -^> .env
    copy /Y ".env.example" ".env" >nul
)

echo  Generare cheie aplicatie...
call php artisan key:generate --force
if errorlevel 1 (
    echo  Eroare la key:generate.
    pause
    exit /b 1
)

echo  Migrari baza de date...
call php artisan migrate --force
if errorlevel 1 (
    echo  Eroare la migrate.
    pause
    exit /b 1
)

echo  NPM install...
call npm install --ignore-scripts
if errorlevel 1 (
    echo  Eroare la npm install.
    pause
    exit /b 1
)

echo  Build assets...
call npm run build
if errorlevel 1 (
    echo  Eroare la npm run build.
    pause
    exit /b 1
)

echo.
echo  Instalare finalizata cu succes.
echo  Deschide: http://julius-fitness-gym.test
echo.
exit /b 0
