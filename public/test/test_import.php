<?php

/**
 * Simple test import script
 */

echo "FingerprintBridge Import Test\n";
echo "============================\n\n";

// Test via API
$apiUrl = 'http://studentfinger.me/api/fingerprint-bridge/stats';

echo "1. Testing API connection...\n";
$response = file_get_contents($apiUrl);
$data = json_decode($response, true);

if ($data && $data['success']) {
    echo "   ✓ API connection successful\n";
    echo "   - FinPro records: " . $data['data']['fin_pro']['total_records'] . "\n";
    echo "   - Unique PINs: " . $data['data']['fin_pro']['unique_pins'] . "\n";
    echo "   - Date range: " . $data['data']['fin_pro']['earliest_date'] . " to " . $data['data']['fin_pro']['latest_date'] . "\n";
} else {
    echo "   ✗ API connection failed\n";
    exit(1);
}

echo "\n2. Testing preview import...\n";
$previewUrl = 'http://studentfinger.me/api/fingerprint-bridge/preview';
$previewData = [
    'start_date' => '2025-01-09',
    'end_date' => '2025-01-09',
    'limit' => 5
];

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => http_build_query($previewData)
    ]
]);

$previewResponse = file_get_contents($previewUrl, false, $context);
$previewResult = json_decode($previewResponse, true);

if ($previewResult && $previewResult['success']) {
    echo "   ✓ Preview successful\n";
    echo "   - Total count: " . $previewResult['data']['total_count'] . "\n";
    echo "   - New count: " . $previewResult['data']['new_count'] . "\n";
    echo "   - Existing count: " . $previewResult['data']['existing_count'] . "\n";
    
    if (!empty($previewResult['data']['records'])) {
        echo "   - Sample records:\n";
        foreach (array_slice($previewResult['data']['records'], 0, 3) as $record) {
            echo "     PIN: {$record['pin']}, Date: {$record['scan_date']}, Device: {$record['sn']}\n";
        }
    }
} else {
    echo "   ✗ Preview failed: " . ($previewResult['message'] ?? 'Unknown error') . "\n";
}

echo "\n3. Testing actual import (small batch)...\n";
$importUrl = 'http://studentfinger.me/api/fingerprint-bridge/import';
$importData = [
    'start_date' => '2025-01-09',
    'end_date' => '2025-01-09',
    'duplicate_handling' => 'skip',
    'batch_size' => 10
];

$importContext = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => http_build_query($importData)
    ]
]);

$importResponse = file_get_contents($importUrl, false, $importContext);
$importResult = json_decode($importResponse, true);

if ($importResult && $importResult['success']) {
    echo "   ✓ Import successful\n";
    echo "   - Log ID: " . $importResult['log_id'] . "\n";
    echo "   - Total records: " . $importResult['data']['total_records'] . "\n";
    echo "   - Processed: " . $importResult['data']['processed_records'] . "\n";
    echo "   - Inserted: " . $importResult['data']['inserted_records'] . "\n";
    echo "   - Updated: " . $importResult['data']['updated_records'] . "\n";
    echo "   - Skipped: " . $importResult['data']['skipped_records'] . "\n";
    echo "   - Errors: " . $importResult['data']['error_records'] . "\n";
} else {
    echo "   ✗ Import failed: " . ($importResult['message'] ?? 'Unknown error') . "\n";
    if (isset($importResult['log_id'])) {
        echo "   - Log ID: " . $importResult['log_id'] . "\n";
    }
}

echo "\n4. Getting updated statistics...\n";
$finalResponse = file_get_contents($apiUrl);
$finalData = json_decode($finalResponse, true);

if ($finalData && $finalData['success']) {
    echo "   ✓ Final statistics:\n";
    echo "   - FinPro records: " . $finalData['data']['fin_pro']['total_records'] . "\n";
    echo "   - StudentFinger records: " . $finalData['data']['student_finger']['total_records'] . "\n";
    echo "   - Import logs: " . $finalData['data']['import_logs']['total_imports'] . "\n";
}

echo "\n============================\n";
echo "Test completed!\n";
echo "\nYou can now access:\n";
echo "- Dashboard: http://studentfinger.me/fingerprint-bridge\n";
echo "- Manual Import: http://studentfinger.me/fingerprint-bridge/manual-import\n";
echo "- Import Logs: http://studentfinger.me/fingerprint-bridge/logs\n";
echo "- PIN Mapping: http://studentfinger.me/fingerprint-bridge/pin-mapping\n";
echo "- Test Page: http://studentfinger.me/test_fingerprint_bridge.php\n";
