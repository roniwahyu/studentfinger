<?php

echo "=== SIMPLE WHATSAPP ATTENDANCE TEST ===\n\n";

// Test 1: Database Connections
echo "1. Testing Database Connections...\n";

try {
    // Test fin_pro connection
    $finProDb = new mysqli('localhost', 'root', '', 'fin_pro');
    if ($finProDb->connect_error) {
        echo "   ❌ fin_pro database connection failed: " . $finProDb->connect_error . "\n";
    } else {
        echo "   ✅ fin_pro database connected successfully\n";
        
        // Check att_log table
        $result = $finProDb->query("SHOW TABLES LIKE 'att_log'");
        if ($result && $result->num_rows > 0) {
            echo "   ✅ fin_pro.att_log table exists\n";
            
            // Check sample data
            $result = $finProDb->query("SELECT COUNT(*) as count FROM att_log");
            if ($result) {
                $row = $result->fetch_assoc();
                echo "   📊 fin_pro.att_log has {$row['count']} records\n";
            }
        } else {
            echo "   ❌ fin_pro.att_log table not found\n";
        }
        $finProDb->close();
    }
    
    // Test studentfinger connection
    $studentDb = new mysqli('localhost', 'root', '', 'studentfinger');
    if ($studentDb->connect_error) {
        echo "   ❌ studentfinger database connection failed: " . $studentDb->connect_error . "\n";
    } else {
        echo "   ✅ studentfinger database connected successfully\n";
        
        // Check required tables
        $tables = ['att_log', 'whatsapp_notification_logs', 'whatsapp_transfer_logs', 'whatsapp_student_parents'];
        foreach ($tables as $table) {
            $result = $studentDb->query("SHOW TABLES LIKE '$table'");
            if ($result && $result->num_rows > 0) {
                echo "   ✅ studentfinger.$table table exists\n";
            } else {
                echo "   ❌ studentfinger.$table table not found\n";
            }
        }
        $studentDb->close();
    }
    
} catch (Exception $e) {
    echo "   ❌ Database test error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Insert Sample Data
echo "2. Inserting Sample Data...\n";

try {
    $studentDb = new mysqli('localhost', 'root', '', 'studentfinger');
    
    if ($studentDb->connect_error) {
        echo "   ❌ Database connection failed\n";
    } else {
        // Insert sample parent mapping
        $sampleMappings = [
            ['TEST001', 'Test Student 1', 'Test Parent 1', '6281234567890', 'father'],
            ['TEST002', 'Test Student 2', 'Test Parent 2', '6281234567891', 'mother'],
            ['TEST003', 'Test Student 3', 'Test Parent 3', '6281234567892', 'father']
        ];
        
        foreach ($sampleMappings as $mapping) {
            $stmt = $studentDb->prepare("
                INSERT IGNORE INTO whatsapp_student_parents 
                (student_id, student_name, parent_name, parent_phone, relationship, is_primary, status, created_at) 
                VALUES (?, ?, ?, ?, ?, 1, 1, NOW())
            ");
            
            if ($stmt) {
                $stmt->bind_param('sssss', $mapping[0], $mapping[1], $mapping[2], $mapping[3], $mapping[4]);
                
                if ($stmt->execute()) {
                    echo "   ✅ Inserted mapping for {$mapping[1]} -> {$mapping[3]}\n";
                } else {
                    echo "   ❌ Failed to insert mapping for {$mapping[1]}: " . $stmt->error . "\n";
                }
                
                $stmt->close();
            } else {
                echo "   ❌ Failed to prepare statement: " . $studentDb->error . "\n";
            }
        }
        
        $studentDb->close();
    }
    
} catch (Exception $e) {
    echo "   ❌ Sample data insertion error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Check Wablas Configuration
echo "3. Checking Wablas Configuration...\n";

// Load .env file
if (file_exists('.env')) {
    $envContent = file_get_contents('.env');
    $envLines = explode("\n", $envContent);
    
    $wablasConfig = [];
    foreach ($envLines as $line) {
        if (strpos($line, 'WABLAS_') === 0) {
            $parts = explode('=', $line, 2);
            if (count($parts) == 2) {
                $wablasConfig[trim($parts[0])] = trim($parts[1]);
            }
        }
    }
    
    if (!empty($wablasConfig)) {
        echo "   ✅ Wablas configuration found:\n";
        foreach ($wablasConfig as $key => $value) {
            if (strpos($key, 'TOKEN') !== false || strpos($key, 'SECRET') !== false) {
                $displayValue = substr($value, 0, 10) . '...';
            } else {
                $displayValue = $value;
            }
            echo "     $key = $displayValue\n";
        }
    } else {
        echo "   ❌ No Wablas configuration found in .env\n";
    }
} else {
    echo "   ❌ .env file not found\n";
}

echo "\n";

// Test 4: Module Files Check
echo "4. Checking Module Files...\n";

$moduleFiles = [
    'app/Modules/WhatsAppAttendance/WhatsAppAttendanceModule.php',
    'app/Modules/WhatsAppAttendance/Config/WhatsAppAttendance.php',
    'app/Modules/WhatsAppAttendance/Controllers/WhatsAppAttendanceController.php',
    'app/Modules/WhatsAppAttendance/Services/AttendanceMonitorService.php',
    'app/Modules/WhatsAppAttendance/Services/DataTransferService.php',
    'app/Modules/WhatsAppAttendance/Services/NotificationService.php',
    'app/Modules/WhatsAppAttendance/Models/AttendanceLogModel.php',
    'app/Modules/WhatsAppAttendance/Models/NotificationLogModel.php',
    'app/Modules/WhatsAppAttendance/Models/TransferLogModel.php',
    'app/Modules/WhatsAppAttendance/Models/StudentParentModel.php'
];

foreach ($moduleFiles as $file) {
    if (file_exists($file)) {
        echo "   ✅ $file exists\n";
    } else {
        echo "   ❌ $file not found\n";
    }
}

echo "\n";

// Test 5: Simple Data Transfer Test
echo "5. Testing Simple Data Transfer...\n";

try {
    $finProDb = new mysqli('localhost', 'root', '', 'fin_pro');
    $studentDb = new mysqli('localhost', 'root', '', 'studentfinger');
    
    if ($finProDb->connect_error || $studentDb->connect_error) {
        echo "   ❌ Database connection failed\n";
    } else {
        // Insert sample data in fin_pro
        $sampleAttendance = [
            ['TEST001', date('Y-m-d'), '07:30:00'],
            ['TEST002', date('Y-m-d'), '07:45:00'],
            ['TEST003', date('Y-m-d'), '08:00:00']
        ];
        
        foreach ($sampleAttendance as $attendance) {
            $stmt = $finProDb->prepare("
                INSERT IGNORE INTO att_log 
                (pin, scan_date, scan_time, status, created_at) 
                VALUES (?, ?, ?, 1, NOW())
            ");
            
            if ($stmt) {
                $stmt->bind_param('sss', $attendance[0], $attendance[1], $attendance[2]);
                
                if ($stmt->execute()) {
                    echo "   ✅ Inserted attendance for {$attendance[0]} at {$attendance[2]}\n";
                } else {
                    echo "   ❌ Failed to insert attendance for {$attendance[0]}\n";
                }
                
                $stmt->close();
            }
        }
        
        // Check data in both databases
        $result = $finProDb->query("SELECT COUNT(*) as count FROM att_log WHERE DATE(scan_date) = CURDATE()");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "   📊 fin_pro.att_log today's records: {$row['count']}\n";
        }
        
        $result = $studentDb->query("SELECT COUNT(*) as count FROM att_log WHERE DATE(scan_date) = CURDATE()");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "   📊 studentfinger.att_log today's records: {$row['count']}\n";
        }
        
        $finProDb->close();
        $studentDb->close();
    }
    
} catch (Exception $e) {
    echo "   ❌ Data transfer test error: " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETED ===\n";

?>