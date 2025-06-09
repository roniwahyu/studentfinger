<?php

/**
 * Test script for FingerprintBridge module
 */

// Set up CodeIgniter environment
require_once 'vendor/autoload.php';

// Bootstrap CodeIgniter
$app = \Config\Services::codeigniter();
$app->initialize();

echo "=== FingerprintBridge Module Test ===\n\n";

try {
    // Test 1: Database connections
    echo "1. Testing database connections...\n";
    
    // Test default database (studentfinger)
    $defaultDb = \Config\Database::connect();
    $defaultDb->query('SELECT 1');
    echo "   ✓ StudentFinger database connection: OK\n";
    
    // Test fin_pro database
    try {
        $finProDb = \Config\Database::connect('fin_pro');
        $finProDb->query('SELECT 1');
        echo "   ✓ FinPro database connection: OK\n";
    } catch (Exception $e) {
        echo "   ✗ FinPro database connection: FAILED - " . $e->getMessage() . "\n";
        echo "   Creating fin_pro database and test data...\n";
        
        // Create fin_pro database and test data
        $defaultDb->query('CREATE DATABASE IF NOT EXISTS fin_pro');
        exec('mysql -u root < setup_fin_pro_test.sql');
        exec('mysql -u root < setup_test_students.sql');
        
        echo "   ✓ Test data created\n";
    }
    
    // Test 2: Create FingerprintBridge tables
    echo "\n2. Creating FingerprintBridge tables...\n";
    exec('mysql -u root < create_fingerprint_bridge_tables.sql');
    echo "   ✓ FingerprintBridge tables created\n";
    
    // Test 3: Test FingerprintImportService
    echo "\n3. Testing FingerprintImportService...\n";
    
    $importService = new \App\Modules\FingerprintBridge\Services\FingerprintImportService();
    
    // Test connection
    $connectionTest = $importService->testFinProConnection();
    if ($connectionTest['success']) {
        echo "   ✓ FinPro connection test: PASSED\n";
        echo "     - Total records: " . number_format($connectionTest['data']['total_records'] ?? 0) . "\n";
        echo "     - Unique PINs: " . number_format($connectionTest['data']['unique_pins'] ?? 0) . "\n";
        echo "     - Unique devices: " . number_format($connectionTest['data']['unique_devices'] ?? 0) . "\n";
    } else {
        echo "   ✗ FinPro connection test: FAILED - " . $connectionTest['message'] . "\n";
    }
    
    // Test preview import
    $startDate = date('Y-m-d 00:00:00');
    $endDate = date('Y-m-d 23:59:59');
    
    $previewResult = $importService->previewImport($startDate, $endDate, 5);
    if ($previewResult['success']) {
        echo "   ✓ Preview import: PASSED\n";
        echo "     - Total count: " . number_format($previewResult['data']['total_count']) . "\n";
        echo "     - New count: " . number_format($previewResult['data']['new_count']) . "\n";
        echo "     - Existing count: " . number_format($previewResult['data']['existing_count']) . "\n";
    } else {
        echo "   ✗ Preview import: FAILED - " . $previewResult['message'] . "\n";
    }
    
    // Test 4: Test models
    echo "\n4. Testing models...\n";
    
    // Test FinProAttLogModel
    $finProModel = new \App\Modules\FingerprintBridge\Models\FinProAttLogModel();
    $finProRecords = $finProModel->findAll();
    echo "   ✓ FinProAttLogModel: " . count($finProRecords) . " records found\n";
    
    // Test StudentFingerAttLogModel
    $studentFingerModel = new \App\Modules\FingerprintBridge\Models\StudentFingerAttLogModel();
    $studentFingerRecords = $studentFingerModel->findAll();
    echo "   ✓ StudentFingerAttLogModel: " . count($studentFingerRecords) . " records found\n";
    
    // Test ImportLogModel
    $importLogModel = new \App\Modules\FingerprintBridge\Models\ImportLogModel();
    $importLogs = $importLogModel->findAll();
    echo "   ✓ ImportLogModel: " . count($importLogs) . " logs found\n";
    
    // Test StudentPinMappingModel
    $pinMappingModel = new \App\Modules\FingerprintBridge\Models\StudentPinMappingModel();
    $pinMappings = $pinMappingModel->findAll();
    echo "   ✓ StudentPinMappingModel: " . count($pinMappings) . " mappings found\n";
    
    // Test 5: Auto-create PIN mappings
    echo "\n5. Testing auto-create PIN mappings...\n";
    $mappingResult = $importService->autoCreatePinMappings();
    if ($mappingResult['success']) {
        echo "   ✓ Auto-create PIN mappings: PASSED\n";
        echo "     - " . $mappingResult['message'] . "\n";
    } else {
        echo "   ✗ Auto-create PIN mappings: FAILED - " . $mappingResult['message'] . "\n";
    }
    
    // Test 6: Test import (small batch)
    echo "\n6. Testing actual import...\n";
    
    $importOptions = [
        'start_date' => $startDate,
        'end_date' => $endDate,
        'batch_size' => 100,
        'duplicate_handling' => 'skip',
        'import_type' => 'manual',
        'user_id' => 1
    ];
    
    $importResult = $importService->importData($importOptions);
    if ($importResult['success']) {
        echo "   ✓ Import test: PASSED\n";
        echo "     - Log ID: " . $importResult['log_id'] . "\n";
        echo "     - Total records: " . number_format($importResult['data']['total_records']) . "\n";
        echo "     - Processed: " . number_format($importResult['data']['processed_records']) . "\n";
        echo "     - Inserted: " . number_format($importResult['data']['inserted_records']) . "\n";
        echo "     - Updated: " . number_format($importResult['data']['updated_records']) . "\n";
        echo "     - Skipped: " . number_format($importResult['data']['skipped_records']) . "\n";
        echo "     - Errors: " . number_format($importResult['data']['error_records']) . "\n";
    } else {
        echo "   ✗ Import test: FAILED - " . $importResult['message'] . "\n";
    }
    
    // Test 7: Get statistics
    echo "\n7. Getting import statistics...\n";
    $stats = $importService->getImportStats();
    echo "   ✓ Statistics retrieved:\n";
    echo "     - FinPro total records: " . number_format($stats['fin_pro']['total_records'] ?? 0) . "\n";
    echo "     - StudentFinger total records: " . number_format($stats['student_finger']['total_records'] ?? 0) . "\n";
    echo "     - Active PIN mappings: " . number_format($stats['pin_mapping']['active_mappings'] ?? 0) . "\n";
    echo "     - Unmapped PINs: " . number_format($stats['pin_mapping']['unmapped_pins'] ?? 0) . "\n";
    
    echo "\n=== All tests completed successfully! ===\n";
    echo "\nYou can now access the FingerprintBridge module at:\n";
    echo "- Dashboard: http://studentfinger.me/fingerprint-bridge\n";
    echo "- Manual Import: http://studentfinger.me/fingerprint-bridge/manual-import\n";
    echo "- Import Logs: http://studentfinger.me/fingerprint-bridge/logs\n";
    echo "- PIN Mapping: http://studentfinger.me/fingerprint-bridge/pin-mapping\n";
    echo "- Settings: http://studentfinger.me/fingerprint-bridge/settings\n";
    
} catch (Exception $e) {
    echo "\n✗ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
