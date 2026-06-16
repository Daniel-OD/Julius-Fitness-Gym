@echo off
cd /d "%~dp0"

if exist "public\hot" (
    netstat -ano | findstr /R /C:":5173 .*LISTENING" >nul 2>&1
    if errorlevel 1 del /F /Q "public\hot"
)

start http://127.0.0.1:8000/admin/login
