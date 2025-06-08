const express = require('express');
const router = express.Router();
const path = require('path');
const fs = require('fs');
const Logger = require('../utils/Logger');

// Get QR code as JSON (for API)
router.get('/json', (req, res) => {
    try {
        const whatsappService = req.app.locals.whatsappService;
        const qrCode = whatsappService?.getQRCode();
        const isConnected = whatsappService?.isConnected();
        
        res.json({
            success: true,
            connected: isConnected,
            qr_code: qrCode,
            user: whatsappService?.getUser() || null,
            timestamp: new Date().toISOString()
        });
        
    } catch (error) {
        Logger.error('QR JSON API error:', error);
        res.status(500).json({
            success: false,
            error: 'Internal Server Error',
            message: error.message
        });
    }
});

// Get QR code as image
router.get('/image', (req, res) => {
    try {
        const qrPath = path.join(__dirname, '../../public/qr.png');
        
        if (fs.existsSync(qrPath)) {
            res.sendFile(qrPath);
        } else {
            res.status(404).json({
                success: false,
                message: 'QR code not available. Please wait for WhatsApp to generate a new QR code.'
            });
        }
        
    } catch (error) {
        Logger.error('QR image error:', error);
        res.status(500).json({
            success: false,
            error: 'Internal Server Error',
            message: error.message
        });
    }
});

// Serve QR code web interface
router.get('/', (req, res) => {
    const htmlContent = `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp QR Scanner - Student Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .qr-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 2rem;
            margin: 2rem auto;
            max-width: 500px;
        }
        
        .qr-code-display {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
        
        .qr-code-display img {
            max-width: 250px;
            max-height: 250px;
            border-radius: 10px;
        }
        
        .status-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        .status-connected {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status-disconnected {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .status-waiting {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .loading-spinner {
            width: 3rem;
            height: 3rem;
            border: 0.3rem solid #f3f3f3;
            border-top: 0.3rem solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .btn-custom {
            border-radius: 50px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="qr-container position-relative">
                    <div id="statusBadge" class="status-badge status-waiting">
                        <i class="fas fa-clock me-1"></i>
                        Waiting for Connection
                    </div>
                    
                    <div class="text-center mb-4">
                        <h2 class="text-primary mb-2">
                            <i class="fab fa-whatsapp me-2"></i>
                            WhatsApp Connection
                        </h2>
                        <p class="text-muted">Scan the QR code with your WhatsApp mobile app</p>
                    </div>
                    
                    <div id="qrCodeDisplay" class="qr-code-display">
                        <div class="loading-spinner"></div>
                        <p class="mt-3 text-muted">Loading QR Code...</p>
                    </div>
                    
                    <div id="connectionInfo" class="mt-4" style="display: none;">
                        <div class="alert alert-success">
                            <h5><i class="fas fa-check-circle me-2"></i>Connected Successfully!</h5>
                            <p class="mb-0">WhatsApp device connected as: <strong id="connectedUser"></strong></p>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button id="refreshBtn" class="btn btn-outline-primary btn-custom me-2">
                            <i class="fas fa-sync-alt me-1"></i>
                            Refresh
                        </button>
                        <button id="disconnectBtn" class="btn btn-outline-danger btn-custom" style="display: none;">
                            <i class="fas fa-sign-out-alt me-1"></i>
                            Disconnect
                        </button>
                    </div>
                    
                    <div class="mt-4 text-center">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Open WhatsApp on your phone → Settings → Linked Devices → Link a Device
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.socket.io/4.7.4/socket.io.min.js"></script>
    <script>
        const socket = io();
        let isConnected = false;
        
        // DOM elements
        const qrCodeDisplay = document.getElementById('qrCodeDisplay');
        const statusBadge = document.getElementById('statusBadge');
        const connectionInfo = document.getElementById('connectionInfo');
        const connectedUser = document.getElementById('connectedUser');
        const refreshBtn = document.getElementById('refreshBtn');
        const disconnectBtn = document.getElementById('disconnectBtn');
        
        // Socket event handlers
        socket.on('connect', () => {
            console.log('Connected to server');
            loadQRCode();
        });
        
        socket.on('qr_code', (data) => {
            console.log('QR code received');
            displayQRCode(data.qr);
            updateStatus('waiting', 'Scan QR Code');
        });
        
        socket.on('whatsapp_status', (data) => {
            console.log('WhatsApp status:', data);
            if (data.connected) {
                showConnectedState(data.user);
            } else {
                showDisconnectedState();
            }
        });
        
        // Functions
        function loadQRCode() {
            fetch('/qr/json')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.connected) {
                            showConnectedState(data.user);
                        } else if (data.qr_code) {
                            displayQRCode(data.qr_code);
                            updateStatus('waiting', 'Scan QR Code');
                        } else {
                            showWaitingForQR();
                        }
                    } else {
                        showError(data.message || 'Failed to load QR code');
                    }
                })
                .catch(error => {
                    console.error('Error loading QR code:', error);
                    showError('Failed to connect to server');
                });
        }
        
        function displayQRCode(qrDataUrl) {
            qrCodeDisplay.innerHTML = \`
                <img src="\${qrDataUrl}" alt="WhatsApp QR Code" class="pulse">
                <p class="mt-3 text-primary fw-bold">Scan this QR code with WhatsApp</p>
            \`;
        }
        
        function showWaitingForQR() {
            qrCodeDisplay.innerHTML = \`
                <div class="loading-spinner"></div>
                <p class="mt-3 text-muted">Waiting for QR code...</p>
            \`;
            updateStatus('waiting', 'Generating QR Code');
        }
        
        function showConnectedState(user) {
            isConnected = true;
            qrCodeDisplay.innerHTML = \`
                <div class="text-success">
                    <i class="fas fa-check-circle fa-5x mb-3"></i>
                    <h4>Connected Successfully!</h4>
                </div>
            \`;
            
            if (user) {
                connectedUser.textContent = user.name || user.id || 'Unknown';
                connectionInfo.style.display = 'block';
            }
            
            updateStatus('connected', 'Connected');
            disconnectBtn.style.display = 'inline-block';
        }
        
        function showDisconnectedState() {
            isConnected = false;
            connectionInfo.style.display = 'none';
            disconnectBtn.style.display = 'none';
            updateStatus('disconnected', 'Disconnected');
            
            // Try to load new QR code
            setTimeout(loadQRCode, 2000);
        }
        
        function showError(message) {
            qrCodeDisplay.innerHTML = \`
                <div class="text-danger">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                    <h5>Error</h5>
                    <p>\${message}</p>
                </div>
            \`;
            updateStatus('disconnected', 'Error');
        }
        
        function updateStatus(type, text) {
            statusBadge.className = \`status-badge status-\${type}\`;
            
            const icons = {
                connected: 'fas fa-check-circle',
                disconnected: 'fas fa-times-circle',
                waiting: 'fas fa-clock'
            };
            
            statusBadge.innerHTML = \`<i class="\${icons[type]} me-1"></i>\${text}\`;
        }
        
        // Event listeners
        refreshBtn.addEventListener('click', () => {
            loadQRCode();
        });
        
        disconnectBtn.addEventListener('click', () => {
            if (confirm('Are you sure you want to disconnect WhatsApp?')) {
                fetch('/api/disconnect', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-API-Key': 'your_secure_api_key_here' // You should get this from config
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showDisconnectedState();
                    } else {
                        alert('Failed to disconnect: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Disconnect error:', error);
                    alert('Failed to disconnect');
                });
            }
        });
        
        // Auto-refresh QR code every 30 seconds if not connected
        setInterval(() => {
            if (!isConnected) {
                loadQRCode();
            }
        }, 30000);
        
        // Initial load
        loadQRCode();
    </script>
</body>
</html>
    `;
    
    res.send(htmlContent);
});

module.exports = router;
