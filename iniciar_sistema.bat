@echo off
cd C:\xampp

:: Verificar si MySQL ya está corriendo
tasklist | findstr mysqld.exe >nul
if %errorlevel%==0 (
    echo MySQL ya está encendido.
) else (
    echo Iniciando MySQL...
    start "" mysql_start.bat
)

:: Verificar Apache
tasklist | findstr httpd.exe >nul
if %errorlevel%==0 (
    echo Apache ya está encendido.
) else (
    echo Iniciando Apache...
    start "" apache_start.bat
)

timeout /t 4 > nul
start "" http://localhost/sistema_gestion_aldea/
exit
