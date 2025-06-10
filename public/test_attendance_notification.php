<?php

// Bootstrap the application
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

echo "=== ATTENDANCE NOTIFICATION SYSTEM TEST ===\n\n";

try {
    // 1. Test database connections
    echo "1. Testing database connections...\n";
    $defaultDb = \Config\Database::connect('default');
    $finproDb = \Config\Database::connect('fin_pro');
    
    echo "✓ Default database connected\n";
    echo "✓ FinPro database connected\n\n";

    // 2. Test attendance service
    echo "2. Testing attendance notification service...\n";
    $service = new \App\Modules\AttendanceNotification\Services\AttendanceNotificationService();
    
    $result = $service->syncAndNotify();
    echo "Sync result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n\n";

    // 3. Check notification logs
    echo "3. Checking notification logs...\n";
    $logs = $defaultDb->table('notification_logs')
        ->orderBy('created_at', 'DESC')
        ->limit(5)
        ->get()
        ->getResult();

    if (!empty($logs)) {
        foreach ($logs as $log) {
            echo "- Message to: {$log->parent_phone}\n";
            echo "  Status: {$log->status}\n";
            echo "  Sent at: {$log->sent_at}\n";
            echo "  ----------------------\n";
        }
    } else {
        echo "No notification logs found\n";
    }

    // 4. Test WA Gateway
    echo "\n4. Testing WhatsApp gateway...\n";
    $waService = new \App\Modules\WablasIntegration\Services\WhatsAppGatewayService();
    $status = $waService->checkDeviceStatus();
    echo "WhatsApp gateway status: " . ($status ? "Connected" : "Not connected") . "\n\n";

    // 5. Verify module integration
    echo "5. Checking module integration...\n";
    $modules = ['AttendanceNotification', 'WablasIntegration', 'FingerprintBridge'];
    foreach ($modules as $module) {
        if (class_exists("\\App\\Modules\\{$module}\\{$module}Module")) {
            echo "✓ {$module} module loaded\n";
        } else {
            echo "✗ {$module} module not found\n";
        }
    }

} catch (\Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETED ===\n";
