@echo off
chcp 65001 > nul
echo ========================================
echo    SISTEMA DE BACKUP AUTOMATIZADO
echo ========================================
echo.

:menu
echo Selecciona una opcion:
echo 1. Backup de base de datos
echo 2. Backup completo
echo 3. Listar backups
echo 4. Limpiar backups antiguos
echo 5. Programar backup automatico
echo 6. Salir
echo.
set /p choice="Opcion [1-6]: "

if "%choice%"=="1" goto backup_db
if "%choice%"=="2" goto backup_full
if "%choice%"=="3" goto list_backups
if "%choice%"=="4" goto clean_backups
if "%choice%"=="5" goto schedule_backup
if "%choice%"=="6" goto exit
echo Opcion invalida
echo.
goto menu

:backup_db
echo Realizando backup de base de datos...
php artisan system:backup --db-only
echo.
goto menu

:backup_full
echo Realizando backup completo...
php artisan backup:run
echo.
goto menu

:list_backups
echo Listando backups disponibles...
php artisan system:backup --list
echo.
goto menu

:clean_backups
echo Limpiando backups antiguos...
php artisan backup:clean
echo.
goto menu

:schedule_backup
echo Programando backup automatico en Windows Task Scheduler...
schtasks /create /tn "LaravelBackup" /tr "%~dp0backup.bat" /sc daily /st 02:00 /ru System
echo Backup programado exitosamente!
echo.
goto menu

:exit
echo Saliendo del sistema de backup...
pause