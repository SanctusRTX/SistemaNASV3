@echo off
echo ============================================
echo  Sistema de Almacenamiento - Servidor local
echo ============================================
echo.
echo  En este equipo:  http://127.0.0.1:8000
echo  Desde la red WiFi: http://192.168.0.106:8000
echo.
echo  IPs detectadas en este equipo:
ipconfig | findstr /i "IPv4"
echo.
echo  Presiona Ctrl+C para detener el servidor.
echo ============================================
echo.
C:\xampp\php\php.exe artisan serve --host=0.0.0.0 --port=8000
