@echo off
setlocal
cd /d "%~dp0.."

echo.
echo  Incalzire cache aplicatie (pagini mai rapide)...
echo.

call php artisan app:cache --no-interaction
if errorlevel 1 exit /b 1

echo.
echo  Gata! Reporneste serverul daca ruleaza: php artisan serve
echo.
exit /b 0
