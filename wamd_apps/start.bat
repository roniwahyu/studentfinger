@echo off
echo ========================================
echo WhatsApp Multi-Device Gateway
echo ========================================
echo.

echo Checking Node.js installation...
node --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: Node.js is not installed or not in PATH
    echo Please install Node.js 16+ from https://nodejs.org/
    pause
    exit /b 1
)

echo Node.js version:
node --version

echo.
echo Checking dependencies...
if not exist "node_modules" (
    echo Installing dependencies...
    npm install
    if %errorlevel% neq 0 (
        echo ERROR: Failed to install dependencies
        pause
        exit /b 1
    )
) else (
    echo Dependencies already installed
)

echo.
echo Checking environment configuration...
if not exist ".env" (
    echo Creating .env file from template...
    copy ".env.example" ".env" >nul
    echo Please edit .env file with your configuration
    echo Press any key to continue after editing .env...
    pause >nul
)

echo.
echo Creating required directories...
if not exist "logs" mkdir logs
if not exist "sessions" mkdir sessions
if not exist "public" mkdir public

echo.
echo Starting WhatsApp Gateway...
echo.
echo ========================================
echo Server will start on http://localhost:3000
echo QR Scanner: http://localhost:3000/qr
echo Health Check: http://localhost:3000/health
echo ========================================
echo.
echo Press Ctrl+C to stop the server
echo.

npm start
