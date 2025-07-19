@echo off
echo =====================================================================
echo CREAR BACKUP ANTES DEL RESET COMPLETO
echo =====================================================================

set /p username="Ingrese usuario MySQL (default: root): "
if "%username%"=="" set username=root

set timestamp=%date:~-4%-%date:~3,2%-%date:~0,2%_%time:~0,2%-%time:~3,2%-%time:~6,2%
set timestamp=%timestamp: =0%

echo.
echo Creando backup: backup_antes_reset_%timestamp%.sql
echo.

mysqldump -u %username% -p nuevoanvar_vacio > backup_antes_reset_%timestamp%.sql

if %errorlevel% == 0 (
    echo.
    echo ✅ Backup creado exitosamente: backup_antes_reset_%timestamp%.sql
    echo.
    echo Ahora puedes ejecutar el reset:
    echo 1. Ejecuta: reset_database_complete.sql en phpMyAdmin/MySQL
    echo 2. Ejecuta: php spark migrate
) else (
    echo.
    echo ❌ Error al crear el backup
)

pause