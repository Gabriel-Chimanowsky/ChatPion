@echo off
REM =====================================================
REM Broadcast Pro - Gerenciador de Workers
REM =====================================================
REM Este script inicia múltiplos workers para processamento paralelo
REM Uso: start_workers.bat [numero_de_workers]
REM =====================================================

SET WORKERS=%1
IF "%WORKERS%"=="" SET WORKERS=4

SET PHP_PATH=C:\xampp\php\php.exe
SET WORKER_PATH=%~dp0worker_broadcast.php

echo =====================================================
echo Broadcast Pro - Iniciando %WORKERS% workers
echo =====================================================
echo.

FOR /L %%i IN (1,1,%WORKERS%) DO (
    echo Iniciando Worker %%i...
    start "Worker %%i" /min %PHP_PATH% %WORKER_PATH% %%i
    timeout /t 1 /nobreak > nul
)

echo.
echo =====================================================
echo %WORKERS% workers iniciados!
echo Para parar, feche as janelas ou use o Gerenciador de Tarefas
echo =====================================================
