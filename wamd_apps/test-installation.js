const fs = require('fs');
const path = require('path');

console.log('🔍 WhatsApp Gateway Installation Test\n');

// Test 1: Check Node.js version
console.log('1. Checking Node.js version...');
console.log(`   Node.js: ${process.version}`);
if (parseInt(process.version.slice(1)) < 16) {
    console.log('   ❌ Node.js 16+ required');
    process.exit(1);
} else {
    console.log('   ✅ Node.js version OK');
}

// Test 2: Check package.json
console.log('\n2. Checking package.json...');
try {
    const packageJson = require('./package.json');
    console.log(`   ✅ Package: ${packageJson.name} v${packageJson.version}`);
} catch (error) {
    console.log('   ❌ package.json not found or invalid');
    process.exit(1);
}

// Test 3: Check dependencies
console.log('\n3. Checking dependencies...');
const nodeModulesPath = path.join(__dirname, 'node_modules');
if (fs.existsSync(nodeModulesPath)) {
    console.log('   ✅ node_modules directory exists');
    
    // Check key dependencies
    const keyDeps = [
        '@whiskeysockets/baileys',
        'express',
        'socket.io',
        'mysql2',
        'qrcode',
        'winston'
    ];
    
    for (const dep of keyDeps) {
        const depPath = path.join(nodeModulesPath, dep);
        if (fs.existsSync(depPath)) {
            console.log(`   ✅ ${dep} installed`);
        } else {
            console.log(`   ❌ ${dep} missing`);
        }
    }
} else {
    console.log('   ❌ node_modules not found. Run: npm install');
    process.exit(1);
}

// Test 4: Check environment file
console.log('\n4. Checking environment configuration...');
const envPath = path.join(__dirname, '.env');
if (fs.existsSync(envPath)) {
    console.log('   ✅ .env file exists');
    
    // Load and check key variables
    require('dotenv').config();
    const requiredVars = ['PORT', 'DB_HOST', 'DB_NAME', 'API_KEY'];
    
    for (const varName of requiredVars) {
        if (process.env[varName]) {
            console.log(`   ✅ ${varName} configured`);
        } else {
            console.log(`   ⚠️  ${varName} not set`);
        }
    }
} else {
    console.log('   ⚠️  .env file not found. Copy from .env.example');
}

// Test 5: Check directory structure
console.log('\n5. Checking directory structure...');
const requiredDirs = ['src', 'src/routes', 'src/services', 'src/middleware', 'src/utils'];
const optionalDirs = ['logs', 'sessions', 'public', 'frontend'];

for (const dir of requiredDirs) {
    const dirPath = path.join(__dirname, dir);
    if (fs.existsSync(dirPath)) {
        console.log(`   ✅ ${dir}/ exists`);
    } else {
        console.log(`   ❌ ${dir}/ missing`);
    }
}

for (const dir of optionalDirs) {
    const dirPath = path.join(__dirname, dir);
    if (fs.existsSync(dirPath)) {
        console.log(`   ✅ ${dir}/ exists`);
    } else {
        console.log(`   ⚠️  ${dir}/ will be created on startup`);
    }
}

// Test 6: Check key files
console.log('\n6. Checking key files...');
const requiredFiles = [
    'index.js',
    'src/routes/api.js',
    'src/routes/qr.js',
    'src/routes/webhook.js',
    'src/services/WhatsAppService.js',
    'src/services/DatabaseService.js',
    'src/services/MessageQueue.js',
    'src/utils/Logger.js'
];

for (const file of requiredFiles) {
    const filePath = path.join(__dirname, file);
    if (fs.existsSync(filePath)) {
        console.log(`   ✅ ${file}`);
    } else {
        console.log(`   ❌ ${file} missing`);
    }
}

// Test 7: Test basic imports
console.log('\n7. Testing basic imports...');
try {
    const express = require('express');
    console.log('   ✅ Express.js import OK');
} catch (error) {
    console.log('   ❌ Express.js import failed');
}

try {
    const { default: makeWASocket } = require('@whiskeysockets/baileys');
    console.log('   ✅ Baileys import OK');
} catch (error) {
    console.log('   ❌ Baileys import failed');
}

try {
    const { Server } = require('socket.io');
    console.log('   ✅ Socket.IO import OK');
} catch (error) {
    console.log('   ❌ Socket.IO import failed');
}

console.log('\n🎉 Installation test completed!');
console.log('\nNext steps:');
console.log('1. Configure your .env file with proper database credentials');
console.log('2. Ensure MySQL is running and database exists');
console.log('3. Run: npm start');
console.log('4. Open: http://localhost:3000/qr');
console.log('5. Scan QR code with WhatsApp mobile app');

console.log('\nUseful commands:');
console.log('• npm start          - Start the gateway');
console.log('• npm run dev        - Start with auto-reload');
console.log('• npm test           - Run tests');
console.log('• node test-installation.js - Run this test again');
