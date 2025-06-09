<?php
/**
 * Direct WABLAS Connection Test
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

echo "<h2>WABLAS Connection Test</h2>";
echo "<p><strong>Base URL:</strong> " . htmlspecialchars($wablasBaseUrl) . "</p>";
echo "<p><strong>Token:</strong> " . (empty($wablasToken) ? 'Not configured' : 'Configured (' . strlen($wablasToken) . ' chars)') . "</p>";
echo "<p><strong>Secret Key:</strong> " . (empty($wablasSecretKey) ? 'Not configured' : 'Configured (' . strlen($wablasSecretKey) . ' chars)') . "</p>";

if (empty($wablasBaseUrl) || empty($wablasToken) || empty($wablasSecretKey)) {
    echo "<div style='color: red;'><strong>Error:</strong> WABLAS configuration is incomplete!</div>";
    exit;
}

echo "<hr>";
echo "<h3>Testing Connection...</h3>";

try {
    $url = rtrim($wablasBaseUrl, '/') . '/api/device/status';
    $authorization = $wablasToken . '.' . $wablasSecretKey;
    
    echo "<p><strong>Testing URL:</strong> " . htmlspecialchars($url) . "</p>";
    echo "<p><strong>Authorization:</strong> " . htmlspecialchars(substr($authorization, 0, 20)) . "...</p>";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => [
            'Authorization: ' . $authorization,
            'Content-Type: application/json'
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_VERBOSE => true
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    
    echo "<h4>Connection Results:</h4>";
    echo "<p><strong>HTTP Code:</strong> " . $httpCode . "</p>";
    
    if ($error) {
        echo "<p style='color: red;'><strong>CURL Error:</strong> " . htmlspecialchars($error) . "</p>";
    }
    
    echo "<p><strong>Response:</strong></p>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>" . htmlspecialchars($response) . "</pre>";
    
    if ($httpCode === 200) {
        echo "<div style='color: green; font-weight: bold;'>✅ Connection Successful!</div>";
        
        $responseData = json_decode($response, true);
        if ($responseData) {
            echo "<h4>Parsed Response:</h4>";
            echo "<pre style='background: #e8f5e8; padding: 10px; border-radius: 5px;'>" . json_encode($responseData, JSON_PRETTY_PRINT) . "</pre>";
        }
    } else {
        echo "<div style='color: red; font-weight: bold;'>❌ Connection Failed!</div>";
        echo "<p>HTTP Code: " . $httpCode . "</p>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red;'><strong>Exception:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "<hr>";
echo "<h3>Testing Message Send...</h3>";

// Test phone number (change this to your WhatsApp number for actual testing)
$testPhone = '6281331711385';
$testMessage = "🎓 *TEST NOTIFICATION*\n\nHalo! Ini adalah test notifikasi dari sistem Student Finger.\n\n📚 *Mata Pelajaran:* Matematika\n🏫 *Kelas:* X\n👨‍🏫 *Guru:* Mrs. Sari\n⏰ *Waktu:* " . date('H:i') . "\n📅 *Tanggal:* " . date('d/m/Y') . "\n\nSistem notifikasi WhatsApp berfungsi dengan baik!\n\n*Student Finger School*";

echo "<p><strong>Test Phone:</strong> " . htmlspecialchars($testPhone) . "</p>";
echo "<p><strong>Test Message:</strong></p>";
echo "<pre style='background: #f0f8ff; padding: 10px; border-radius: 5px;'>" . htmlspecialchars($testMessage) . "</pre>";

try {
    $url = rtrim($wablasBaseUrl, '/') . '/api/send-message';
    $authorization = $wablasToken . '.' . $wablasSecretKey;
    
    // Clean phone number
    $cleanPhone = preg_replace('/[^0-9]/', '', $testPhone);
    if (substr($cleanPhone, 0, 1) === '0') {
        $cleanPhone = '62' . substr($cleanPhone, 1);
    } elseif (substr($cleanPhone, 0, 2) !== '62') {
        $cleanPhone = '62' . $cleanPhone;
    }
    
    $data = [
        'phone' => $cleanPhone,
        'message' => $testMessage,
        'isGroup' => false
    ];
    
    echo "<p><strong>Send URL:</strong> " . htmlspecialchars($url) . "</p>";
    echo "<p><strong>Clean Phone:</strong> " . htmlspecialchars($cleanPhone) . "</p>";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: ' . $authorization
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_FOLLOWLOCATION => true
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "<h4>Send Results:</h4>";
    echo "<p><strong>HTTP Code:</strong> " . $httpCode . "</p>";
    
    if ($error) {
        echo "<p style='color: red;'><strong>CURL Error:</strong> " . htmlspecialchars($error) . "</p>";
    }
    
    echo "<p><strong>Response:</strong></p>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>" . htmlspecialchars($response) . "</pre>";
    
    $responseData = json_decode($response, true);
    
    if ($httpCode === 200 && $responseData) {
        if (isset($responseData['status']) && $responseData['status'] === true) {
            echo "<div style='color: green; font-weight: bold;'>✅ Message Sent Successfully!</div>";
        } else {
            echo "<div style='color: orange; font-weight: bold;'>⚠️ Message Response Received (check status)</div>";
        }
        
        echo "<h4>Parsed Response:</h4>";
        echo "<pre style='background: #e8f5e8; padding: 10px; border-radius: 5px;'>" . json_encode($responseData, JSON_PRETTY_PRINT) . "</pre>";
    } else {
        echo "<div style='color: red; font-weight: bold;'>❌ Failed to Send Message!</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red;'><strong>Exception:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "<hr>";
echo "<p><a href='/classroom-notifications'>← Back to Classroom Notifications</a></p>";
?>
