<?php
// Simple test to check if endpoints are working

$baseUrl = 'http://studentfinger.me';
$endpoints = [
    '/' => 'Dashboard',
    '/students' => 'Students List',
    '/classes/create' => 'Classes Create',
    '/attendance' => 'Attendance',
    '/attendance-logs' => 'Attendance Logs'
];

echo "Testing StudentFinger Application:\n";
echo "=================================\n\n";

foreach ($endpoints as $endpoint => $description) {
    $url = $baseUrl . $endpoint;
    echo "Testing: $description\n";
    echo "URL: $url\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ CURL Error: $error\n";
    } else {
        echo "✅ HTTP Status: $httpCode\n";
        
        // Check for specific errors in response
        if (strpos($response, 'ErrorException') !== false) {
            echo "⚠️  ErrorException found in response\n";
        } elseif (strpos($response, 'Call to undefined method') !== false) {
            echo "⚠️  Undefined method error found\n";
        } elseif (strpos($response, 'Undefined array key') !== false) {
            echo "⚠️  Undefined array key error found\n";
        } elseif ($httpCode === 200) {
            echo "✅ Page loaded successfully\n";
        }
    }
    echo "\n";
}

echo "Test completed.\n";
?>
