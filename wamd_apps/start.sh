#!/bin/bash

echo "========================================"
echo "WhatsApp Multi-Device Gateway"
echo "========================================"
echo

echo "Checking Node.js installation..."
if ! command -v node &> /dev/null; then
    echo "ERROR: Node.js is not installed or not in PATH"
    echo "Please install Node.js 16+ from https://nodejs.org/"
    exit 1
fi

echo "Node.js version:"
node --version

echo
echo "Checking dependencies..."
if [ ! -d "node_modules" ]; then
    echo "Installing dependencies..."
    npm install
    if [ $? -ne 0 ]; then
        echo "ERROR: Failed to install dependencies"
        exit 1
    fi
else
    echo "Dependencies already installed"
fi

echo
echo "Checking environment configuration..."
if [ ! -f ".env" ]; then
    echo "Creating .env file from template..."
    cp ".env.example" ".env"
    echo "Please edit .env file with your configuration"
    echo "Press any key to continue after editing .env..."
    read -n 1 -s
fi

echo
echo "Creating required directories..."
mkdir -p logs sessions public

echo
echo "Starting WhatsApp Gateway..."
echo
echo "========================================"
echo "Server will start on http://localhost:3000"
echo "QR Scanner: http://localhost:3000/qr"
echo "Health Check: http://localhost:3000/health"
echo "========================================"
echo
echo "Press Ctrl+C to stop the server"
echo

npm start
