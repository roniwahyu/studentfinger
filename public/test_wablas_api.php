<?php
/**
 * WABLAS API Test Endpoint
 * 
 * This script handles WABLAS API testing requests
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

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

// Get request data
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

/**
 * Clean phone number to Indonesian format
 */
function cleanPhoneNumber($phone) {
    // Remove all non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Convert to international format
    if (substr($phone, 0, 1) === '0') {
        $phone = '62' . substr($phone, 1);
    } elseif (substr($phone, 0, 2) !== '62') {
        $phone = '62' . $phone;
    }
    
    return $phone;
}

/**
 * Test WABLAS connection
 */
function testWablasConnection($baseUrl, $token, $secretKey) {
    try {
        $url = rtrim($baseUrl, '/') . '/api/device/status';
        $authorization = $token . '.' . $secretKey;
        
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
            CURLOPT_FOLLOWLOCATION => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'message' => 'CURL Error: ' . $error,
                'http_code' => 0
            ];
        }
        
        $responseData = json_decode($response, true);
        
        return [
            'success' => $httpCode === 200,
            'message' => $httpCode === 200 ? 'Connection successful' : 'HTTP Error: ' . $httpCode,
            'http_code' => $httpCode,
            'response' => $responseData,
            'raw_response' => $response
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Exception: ' . $e->getMessage(),
            'http_code' => 0
        ];
    }
}

/**
 * Send WhatsApp message via WABLAS
 */
function sendWablasMessage($baseUrl, $token, $secretKey, $phone, $message) {
    try {
        $url = rtrim($baseUrl, '/') . '/api/send-message';
        $authorization = $token . '.' . $secretKey;
        $cleanPhone = cleanPhoneNumber($phone);
        
        $data = [
            'phone' => $cleanPhone,
            'message' => $message,
            'isGroup' => false
        ];
        
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
        
        if ($error) {
            return [
                'success' => false,
                'message' => 'CURL Error: ' . $error,
                'http_code' => 0
            ];
        }
        
        $responseData = json_decode($response, true);
        
        // Check WABLAS response format
        $success = false;
        $message = 'Unknown response format';
        
        if (is_array($responseData)) {
            if (isset($responseData['status']) && $responseData['status'] === true) {
                $success = true;
                $message = 'Message sent successfully';
            } elseif (isset($responseData['status']) && $responseData['status'] === false) {
                $success = false;
                $message = $responseData['message'] ?? 'Failed to send message';
            } elseif ($httpCode === 200) {
                $success = true;
                $message = 'Message sent (HTTP 200)';
            }
        } elseif ($httpCode === 200) {
            $success = true;
            $message = 'Message sent (HTTP 200)';
        }
        
        return [
            'success' => $success,
            'message' => $message,
            'http_code' => $httpCode,
            'response' => $responseData,
            'raw_response' => $response,
            'sent_to' => $cleanPhone,
            'original_phone' => $phone
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Exception: ' . $e->getMessage(),
            'http_code' => 0
        ];
    }
}

// Handle different actions
switch ($action) {
    case 'test_connection':
        if (empty($wablasBaseUrl) || empty($wablasToken) || empty($wablasSecretKey)) {
            echo json_encode([
                'success' => false,
                'message' => 'WABLAS configuration is incomplete. Please check your .env file.',
                'config' => [
                    'base_url' => !empty($wablasBaseUrl),
                    'token' => !empty($wablasToken),
                    'secret_key' => !empty($wablasSecretKey)
                ]
            ]);
            exit;
        }
        
        $result = testWablasConnection($wablasBaseUrl, $wablasToken, $wablasSecretKey);
        echo json_encode($result);
        break;
        
    case 'send_message':
        $phone = $input['phone'] ?? '';
        $message = $input['message'] ?? '';
        
        if (empty($phone) || empty($message)) {
            echo json_encode([
                'success' => false,
                'message' => 'Phone number and message are required'
            ]);
            exit;
        }
        
        if (empty($wablasBaseUrl) || empty($wablasToken) || empty($wablasSecretKey)) {
            echo json_encode([
                'success' => false,
                'message' => 'WABLAS configuration is incomplete'
            ]);
            exit;
        }
        
        $result = sendWablasMessage($wablasBaseUrl, $wablasToken, $wablasSecretKey, $phone, $message);
        echo json_encode($result);
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action specified'
        ]);
        break;
}
?>
