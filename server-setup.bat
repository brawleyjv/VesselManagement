@echo off
REM Server Setup Script for logicdock.org (Windows)
echo 🚀 Setting up Vessel Data Logger server...

REM Create necessary directories
echo 📁 Creating directories...
if not exist "api" mkdir api
if not exist "dist" mkdir dist
if not exist "logs" mkdir logs
if not exist "data" mkdir data

REM Create index files for protected directories
echo Forbidden > dist\index.html
echo Forbidden > logs\index.html
echo Forbidden > data\index.html

REM Create empty database file
echo. > data\licenses.sqlite

echo ✅ Directory structure created!
echo.
echo 📋 Upload these files to your web server:
echo - All *.html files
echo - All *.css files  
echo - All *.js files
echo - .htaccess file
echo - api\ directory with all PHP files
echo - dist\ directory (for installer)
echo - logs\ directory (empty, for logging)
echo - data\ directory (for database)
echo.
echo 🎯 After upload, test: https://logicdock.org/landing.html
