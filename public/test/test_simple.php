<?php
// Test simple controller

$url = 'http://studentfinger.me/test';

echo "Testing Simple Controller:\n";
echo "=========================\n";
echo "URL: $url\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HEADER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ CURL Error: $error\n";
} else {
    echo "✅ HTTP Status: $httpCode\n";
    
    if ($httpCode === 200) {
        echo "✅ Response: " . substr($response, 0, 100) . "\n";
    } else {
        echo "❌ HTTP Error: $httpCode\n";
    }
}

echo "\nTest completed.\n";
?>
