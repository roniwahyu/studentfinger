<?php
// Simple test script to check the application endpoints

$baseUrl = 'http://studentfinger.me';
$endpoints = [
    '/' => 'Dashboard',
    '/students' => 'Students List',
    '/classes/create' => 'Classes Create',
    '/attendance' => 'Attendance',
    '/attendance-logs' => 'Attendance Logs'
];

echo "Testing StudentFinger Application Endpoints:\n";
echo "==========================================\n\n";

foreach ($endpoints as $endpoint => $description) {
    $url = $baseUrl . $endpoint;
    echo "Testing: $description ($url)\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "  ❌ Error: $error\n";
    } else {
        echo "  ✅ HTTP Status: $httpCode\n";
        
        // Check for specific errors in response
        if (strpos($response, 'ErrorException') !== false) {
            echo "  ⚠️  ErrorException detected in response\n";
            // Extract error message
            if (preg_match('/ErrorException[^<]*/', $response, $matches)) {
                echo "     Error: " . trim($matches[0]) . "\n";
            }
        }
        
        if (strpos($response, 'Call to undefined method') !== false) {
            echo "  ⚠️  Undefined method error detected\n";
            if (preg_match('/Call to undefined method[^<]*/', $response, $matches)) {
                echo "     Error: " . trim($matches[0]) . "\n";
            }
        }
        
        if (strpos($response, 'Undefined array key') !== false) {
            echo "  ⚠️  Undefined array key error detected\n";
            if (preg_match('/Undefined array key[^<]*/', $response, $matches)) {
                echo "     Error: " . trim($matches[0]) . "\n";
            }
        }
    }
    echo "\n";
}

echo "Test completed.\n";
?>
