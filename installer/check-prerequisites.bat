@echo off
title Verificare cerințe sistem
color 0E
echo.
echo  Verificare cerinte sistem...
echo.

set MISSING=0

REM Verifică Herd / PHP
php -v >nul 2>&1
if errorlevel 1 (
    echo  [LIPSESTE] Laravel Herd / PHP
    echo  Descarca de la: https://herd.laravel.com/windows
    set MISSING=1
) else (
    echo  [OK] PHP detectat
)

REM Verifică Composer
composer -v >nul 2>&1
if errorlevel 1 (
    echo  [LIPSESTE] Composer
    echo  Descarca de la: https://getcomposer.org
    set MISSING=1
) else (
    echo  [OK] Composer detectat
)

REM Verifică Node
node -v >nul 2>&1
if errorlevel 1 (
    echo  [LIPSESTE] Node.js
    echo  Descarca de la: https://nodejs.org
    set MISSING=1
) else (
    echo  [OK] Node.js detectat
)

if %MISSING%==1 (
    echo.
    echo  Instaleaza programele lipsa de mai sus,
    echo  reporneste calculatorul si ruleaza din nou installerul.
    pause
    exit /b 1
)

echo.
echo  Toate cerintele sunt instalate. Continuam...
exit /b 0
