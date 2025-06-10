<?php
// Test home page specifically

$url = 'http://studentfinger.me/home';

echo "Testing Home Page:\n";
echo "==================\n";
echo "URL: $url\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
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
        echo "✅ Page loaded successfully\n";
        
        // Check for specific database errors
        if (strpos($response, 'Unknown column') !== false) {
            echo "❌ Still has 'Unknown column' database error\n";
            
            // Extract the specific error
            if (preg_match('/Unknown column[^<]*/', $response, $matches)) {
                echo "   Error: " . trim($matches[0]) . "\n";
            }
        } elseif (strpos($response, 'DatabaseException') !== false) {
            echo "❌ Has DatabaseException\n";
            
            // Extract the specific error
            if (preg_match('/DatabaseException[^<]*/', $response, $matches)) {
                echo "   Error: " . trim($matches[0]) . "\n";
            }
        } elseif (strpos($response, 'Call to undefined method') !== false) {
            echo "❌ Still has 'Call to undefined method' error\n";
            
            // Extract the specific error
            if (preg_match('/Call to undefined method[^<]*/', $response, $matches)) {
                echo "   Error: " . trim($matches[0]) . "\n";
            }
        } elseif (strpos($response, 'ErrorException') !== false) {
            echo "❌ Has ErrorException\n";
        } elseif (strpos($response, 'Fatal error') !== false) {
            echo "❌ Has Fatal error\n";
        } else {
            echo "✅ No obvious errors detected\n";
            
            // Check if it contains expected content
            if (strpos($response, 'Dashboard') !== false || strpos($response, 'Home') !== false) {
                echo "✅ Contains expected dashboard content\n";
            }
            
            if (strpos($response, 'Students') !== false) {
                echo "✅ Contains 'Students' text\n";
            }
            
            if (strpos($response, 'Classes') !== false) {
                echo "✅ Contains 'Classes' text\n";
            }
            
            if (strpos($response, 'Attendance') !== false) {
                echo "✅ Contains 'Attendance' text\n";
            }
        }
    } else {
        echo "❌ HTTP Error: $httpCode\n";
    }
}

echo "\nTest completed.\n";
?>
