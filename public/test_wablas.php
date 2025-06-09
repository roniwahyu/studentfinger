<?php
/**
 * WABLAS API Test Script
 * 
 * This script tests the WABLAS WhatsApp API integration
 */

// Load environment variables
$envFile = '../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// WABLAS Configuration
$wablasBaseUrl = $_ENV['WABLAS_BASE_URL'] ?? 'https://texas.wablas.com';
$wablasToken = $_ENV['WABLAS_TOKEN'] ?? '';
$wablasSecretKey = $_ENV['WABLAS_SECRET_KEY'] ?? '';

// Test phone number (you can change this)
$testPhone = '6281331711385'; // Change to your WhatsApp number for testing

// Test message
$testMessage = "🎓 *TEST NOTIFICATION*\n\nHalo! Ini adalah test notifikasi dari sistem Student Finger.\n\n📚 *Mata Pelajaran:* Matematika\n🏫 *Kelas:* X\n👨‍🏫 *Guru:* Mrs. Sari\n⏰ *Waktu:* " . date('H:i') . "\n📅 *Tanggal:* " . date('d/m/Y') . "\n\nSistem notifikasi WhatsApp berfungsi dengan baik!\n\n*Student Finger School*";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WABLAS API Test - Student Finger</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">
                            <i class="fab fa-whatsapp"></i> WABLAS API Test
                        </h4>
                    </div>
                    <div class="card-body">
                        <!-- Configuration Status -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6>Configuration Status:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-server text-<?= !empty($wablasBaseUrl) ? 'success' : 'danger' ?>"></i> Base URL: <?= htmlspecialchars($wablasBaseUrl) ?></li>
                                    <li><i class="fas fa-key text-<?= !empty($wablasToken) ? 'success' : 'danger' ?>"></i> Token: <?= !empty($wablasToken) ? 'Configured' : 'Not Set' ?></li>
                                    <li><i class="fas fa-lock text-<?= !empty($wablasSecretKey) ? 'success' : 'danger' ?>"></i> Secret Key: <?= !empty($wablasSecretKey) ? 'Configured' : 'Not Set' ?></li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Test Configuration:</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-phone text-info"></i> Test Phone: <?= htmlspecialchars($testPhone) ?></li>
                                    <li><i class="fas fa-clock text-info"></i> Test Time: <?= date('Y-m-d H:i:s') ?></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Test Form -->
                        <form id="testForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone Number *</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($testPhone) ?>" placeholder="628123456789">
                                        <small class="form-text text-muted">Indonesian format (628xxxxxxxxx)</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="messageType" class="form-label">Message Type</label>
                                        <select class="form-control" id="messageType" name="messageType">
                                            <option value="custom">Custom Message</option>
                                            <option value="session_start">Class Started Template</option>
                                            <option value="session_break">Class Break Template</option>
                                            <option value="session_resume">Class Resumed Template</option>
                                            <option value="session_finish">Class Finished Template</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="message" class="form-label">Test Message</label>
                                <textarea class="form-control" id="message" name="message" rows="8"><?= htmlspecialchars($testMessage) ?></textarea>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="button" class="btn btn-info me-md-2" onclick="testConnection()">
                                    <i class="fas fa-wifi"></i> Test Connection
                                </button>
                                <button type="button" class="btn btn-success" onclick="sendTestMessage()">
                                    <i class="fab fa-whatsapp"></i> Send Test Message
                                </button>
                            </div>
                        </form>

                        <!-- Results -->
                        <div id="results" class="mt-4" style="display: none;">
                            <h6>Test Results:</h6>
                            <div id="resultContent" class="alert"></div>
                        </div>
                    </div>
                </div>

                <!-- Message Preview -->
                <div class="card shadow mt-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-eye"></i> Message Preview
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="bg-light p-3 rounded" style="font-family: monospace; white-space: pre-line;" id="messagePreview">
                            <?= htmlspecialchars($testMessage) ?>
                        </div>
                    </div>
                </div>

                <!-- Back to Dashboard -->
                <div class="text-center mt-4">
                    <a href="/classroom-notifications" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left"></i> Back to Classroom Notifications
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update message preview when message changes
        document.getElementById('message').addEventListener('input', function() {
            document.getElementById('messagePreview').textContent = this.value;
        });

        // Update message when type changes
        document.getElementById('messageType').addEventListener('change', function() {
            const messageType = this.value;
            let message = '';
            
            if (messageType === 'custom') {
                message = `🎓 *TEST NOTIFICATION*\n\nHalo! Ini adalah test notifikasi dari sistem Student Finger.\n\n📚 *Mata Pelajaran:* Matematika\n🏫 *Kelas:* X\n👨‍🏫 *Guru:* Mrs. Sari\n⏰ *Waktu:* ${new Date().toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'})}\n📅 *Tanggal:* ${new Date().toLocaleDateString('id-ID')}\n\nSistem notifikasi WhatsApp berfungsi dengan baik!\n\n*Student Finger School*`;
            } else if (messageType === 'session_start') {
                message = `🎓 *KELAS DIMULAI*\n\nYth. Orang Tua/Wali Test Parent,\n\nKami informasikan bahwa Test Student telah hadir di kelas:\n\n📚 *Mata Pelajaran:* Matematika\n🏫 *Kelas:* X\n👨‍🏫 *Guru:* Mrs. Sari\n⏰ *Waktu Mulai:* ${new Date().toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'})}\n📅 *Tanggal:* ${new Date().toLocaleDateString('id-ID')}\n\nTerima kasih atas perhatiannya.\n\n*Student Finger School*`;
            } else if (messageType === 'session_break') {
                message = `☕ *ISTIRAHAT KELAS*\n\nYth. Orang Tua/Wali Test Parent,\n\nKelas Matematika sedang istirahat:\n\n👤 *Siswa:* Test Student\n🏫 *Kelas:* X\n⏰ *Waktu Istirahat:* ${new Date().toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'})}\n⏱️ *Durasi:* 15 menit\n\nKelas akan dilanjutkan setelah istirahat.\n\n*Student Finger School*`;
            } else if (messageType === 'session_resume') {
                message = `📚 *KELAS DILANJUTKAN*\n\nYth. Orang Tua/Wali Test Parent,\n\nKelas Matematika telah dilanjutkan setelah istirahat:\n\n👤 *Siswa:* Test Student\n🏫 *Kelas:* X\n⏰ *Waktu Lanjut:* ${new Date().toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'})}\n\nTerima kasih atas perhatiannya.\n\n*Student Finger School*`;
            } else if (messageType === 'session_finish') {
                message = `✅ *KELAS SELESAI*\n\nYth. Orang Tua/Wali Test Parent,\n\nKelas Matematika telah selesai:\n\n👤 *Siswa:* Test Student\n🏫 *Kelas:* X\n👨‍🏫 *Guru:* Mrs. Sari\n⏰ *Waktu Selesai:* ${new Date().toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'})}\n⏱️ *Durasi Total:* 2 jam\n\nTest Student dapat dijemput atau pulang sesuai jadwal.\n\n*Student Finger School*`;
            }
            
            document.getElementById('message').value = message;
            document.getElementById('messagePreview').textContent = message;
        });

        function showResult(message, type = 'info') {
            const resultDiv = document.getElementById('results');
            const contentDiv = document.getElementById('resultContent');
            
            contentDiv.className = `alert alert-${type}`;
            contentDiv.innerHTML = message;
            resultDiv.style.display = 'block';
            
            // Scroll to results
            resultDiv.scrollIntoView({ behavior: 'smooth' });
        }

        function testConnection() {
            showResult('<i class="fas fa-spinner fa-spin"></i> Testing WABLAS connection...', 'info');
            
            fetch('test_wablas_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'test_connection'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showResult(`<i class="fas fa-check-circle"></i> <strong>Connection Successful!</strong><br>${data.message}`, 'success');
                } else {
                    showResult(`<i class="fas fa-exclamation-triangle"></i> <strong>Connection Failed!</strong><br>${data.message}`, 'danger');
                }
            })
            .catch(error => {
                showResult(`<i class="fas fa-times-circle"></i> <strong>Error:</strong> ${error.message}`, 'danger');
            });
        }

        function sendTestMessage() {
            const phone = document.getElementById('phone').value;
            const message = document.getElementById('message').value;
            
            if (!phone || !message) {
                showResult('<i class="fas fa-exclamation-triangle"></i> Please fill in phone number and message.', 'warning');
                return;
            }
            
            showResult('<i class="fas fa-spinner fa-spin"></i> Sending test message...', 'info');
            
            fetch('test_wablas_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'send_message',
                    phone: phone,
                    message: message
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showResult(`<i class="fas fa-check-circle"></i> <strong>Message Sent Successfully!</strong><br>Phone: ${phone}<br>Status: ${data.status || 'Sent'}<br>Response: ${JSON.stringify(data.response, null, 2)}`, 'success');
                } else {
                    showResult(`<i class="fas fa-exclamation-triangle"></i> <strong>Failed to Send Message!</strong><br>${data.message}<br>Details: ${data.details || 'No additional details'}`, 'danger');
                }
            })
            .catch(error => {
                showResult(`<i class="fas fa-times-circle"></i> <strong>Error:</strong> ${error.message}`, 'danger');
            });
        }
    </script>
</body>
</html>
