<?php
// Test all main application pages

echo "=== TESTING ALL MAIN APPLICATION PAGES ===\n\n";

$baseUrl = 'http://studentfinger.me';
$pages = [
    '/' => 'Dashboard/Home',
    '/home' => 'Home Page',
    '/attendance' => 'Attendance Page',
    '/attendance-logs' => 'Attendance Logs',
    '/students' => 'Students List',
    '/classes' => 'Classes List',
    '/user-logs' => 'User Logs'
];

$totalPages = count($pages);
$successCount = 0;
$errorCount = 0;

foreach ($pages as $endpoint => $description) {
    $url = $baseUrl . $endpoint;
    echo "Testing: $description\n";
    echo "URL: $url\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "  ❌ CURL Error: $error\n";
        $errorCount++;
    } else {
        echo "  ✅ HTTP Status: $httpCode\n";
        
        if ($httpCode === 200) {
            $successCount++;
            
            // Check for specific errors in response
            $hasError = false;
            
            if (strpos($response, 'DatabaseException') !== false) {
                echo "  ❌ DatabaseException detected\n";
                $hasError = true;
            }
            
            if (strpos($response, 'Unknown column') !== false) {
                echo "  ❌ Unknown column error detected\n";
                if (preg_match('/Unknown column[^<]*/', $response, $matches)) {
                    echo "     Error: " . trim($matches[0]) . "\n";
                }
                $hasError = true;
            }
            
            if (strpos($response, 'Call to undefined method') !== false) {
                echo "  ❌ Undefined method error detected\n";
                if (preg_match('/Call to undefined method[^<]*/', $response, $matches)) {
                    echo "     Error: " . trim($matches[0]) . "\n";
                }
                $hasError = true;
            }
            
            if (strpos($response, 'ErrorException') !== false) {
                echo "  ⚠️  ErrorException detected\n";
                $hasError = true;
            }
            
            if (strpos($response, 'Fatal error') !== false) {
                echo "  ❌ Fatal error detected\n";
                $hasError = true;
            }
            
            if (!$hasError) {
                echo "  ✅ No obvious errors detected\n";
                
                // Check for expected content
                if (strpos($response, 'StudentFinger') !== false || 
                    strpos($response, 'Dashboard') !== false ||
                    strpos($response, 'Attendance') !== false) {
                    echo "  ✅ Contains expected application content\n";
                }
            } else {
                $errorCount++;
                $successCount--;
            }
        } else {
            echo "  ❌ HTTP Error: $httpCode\n";
            $errorCount++;
        }
    }
    echo "\n";
}

// Summary
echo "=== TEST SUMMARY ===\n";
echo "Total Pages Tested: $totalPages\n";
echo "✅ Successful: $successCount\n";
echo "❌ Errors: $errorCount\n";

if ($errorCount === 0) {
    echo "\n🎉 ALL PAGES WORKING CORRECTLY!\n";
    echo "✅ Database schema fixes successful\n";
    echo "✅ All main application pages are functional\n";
} else {
    echo "\n⚠️  Some pages have issues that need attention\n";
    echo "📋 Review the errors above for specific problems\n";
}

echo "\n=== TEST COMPLETED ===\n";
?>
