@echo off
setlocal EnableDelayedExpansion
title Verificare cerinte sistem
color 0E

set "ROOT=%~1"
if "%ROOT%"=="" set "ROOT=%~dp0.."
set "ROOT=%ROOT:\=%"

echo.
echo  Verificare cerinte sistem...
echo.

set MISSING=0
set SKIP_COMPOSER=0
set SKIP_NODE=0

if exist "%ROOT%\vendor\autoload.php" set SKIP_COMPOSER=1
if exist "%ROOT%\public\build\manifest.json" set SKIP_NODE=1

php -v >nul 2>&1
if errorlevel 1 (
    echo  [LIPSESTE] Laravel Herd / PHP
    echo  Descarca: https://herd.laravel.com/windows
    set MISSING=1
) else (
    echo  [OK] PHP detectat
)

if "%SKIP_COMPOSER%"=="0" (
    composer -v >nul 2>&1
    if errorlevel 1 (
        echo  [LIPSESTE] Composer
        echo  Descarca: https://getcomposer.org
        set MISSING=1
    ) else (
        echo  [OK] Composer detectat
    )
) else (
    echo  [OK] Vendor inclus in pachet - Composer optional
)

if "%SKIP_NODE%"=="0" (
    node -v >nul 2>&1
    if errorlevel 1 (
        echo  [LIPSESTE] Node.js
        echo  Descarca: https://nodejs.org
        set MISSING=1
    ) else (
        echo  [OK] Node.js detectat
    )
) else (
    echo  [OK] Assets compilate - Node.js optional
)

where herd >nul 2>&1
if errorlevel 1 (
    echo  [ATENTIE] Herd CLI nu e in PATH - site-ul trebuie configurat manual in Herd
) else (
    echo  [OK] Herd CLI detectat
)

if %MISSING%==1 (
    echo.
    echo  Instaleaza componentele lipsa, reporneste si ruleaza din nou.
    echo  Deschid pagina de download Herd...
    start https://herd.laravel.com/windows
    pause
    exit /b 1
)

echo.
echo  Cerintele sunt indeplinite. Continuam...
exit /b 0
