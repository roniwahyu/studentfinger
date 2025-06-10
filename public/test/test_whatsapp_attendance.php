<?php

require_once 'vendor/autoload.php';
require_once 'app/Config/Database.php';
require_once 'app/Modules/WhatsAppAttendance/WhatsAppAttendanceModule.php';
require_once 'app/Modules/WhatsAppAttendance/Services/AttendanceMonitorService.php';
require_once 'app/Modules/WhatsAppAttendance/Services/DataTransferService.php';
require_once 'app/Modules/WhatsAppAttendance/Services/NotificationService.php';

use App\Modules\WhatsAppAttendance\WhatsAppAttendanceModule;
use App\Modules\WhatsAppAttendance\Services\AttendanceMonitorService;
use App\Modules\WhatsAppAttendance\Services\DataTransferService;
use App\Modules\WhatsAppAttendance\Services\NotificationService;

class WhatsAppAttendanceTest
{
    private $module;
    private $attendanceService;
    private $transferService;
    private $notificationService;
    
    public function __construct()
    {
        $this->module = new WhatsAppAttendanceModule();
        $this->attendanceService = new AttendanceMonitorService();
        $this->transferService = new DataTransferService();
        $this->notificationService = new NotificationService();
    }
    
    public function runAllTests()
    {
        echo "=== TESTING WHATSAPP ATTENDANCE MODULE ===\n\n";
        
        // Test 1: Database Connections
        $this->testDatabaseConnections();
        
        // Test 2: Data Transfer
        $this->testDataTransfer();
        
        // Test 3: Notification Service
        $this->testNotificationService();
        
        // Test 4: Full Module Test
        $this->testFullModule();
        
        // Test 5: History and Logs
        $this->testHistoryLogs();
        
        echo "\n=== ALL TESTS COMPLETED ===\n";
    }
    
    private function testDatabaseConnections()
    {
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
                    $row = $result->fetch_assoc();
                    echo "   📊 fin_pro.att_log has {$row['count']} records\n";
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
    }
    
    private function testDataTransfer()
    {
        echo "2. Testing Data Transfer Service...\n";
        
        try {
            // Test transfer service
            $result = $this->transferService->testTransfer();
            
            if ($result['success']) {
                echo "   ✅ Data transfer test passed\n";
                echo "   📊 Source records: {$result['source_count']}\n";
                echo "   📊 Destination records: {$result['destination_count']}\n";
                
                // Perform actual transfer
                echo "   🔄 Performing data transfer...\n";
                $transferResult = $this->transferService->transferNewRecords();
                
                if ($transferResult['success']) {
                    echo "   ✅ Data transfer completed successfully\n";
                    echo "   📊 Records transferred: {$transferResult['transferred']}\n";
                    echo "   📊 Records skipped: {$transferResult['skipped']}\n";
                } else {
                    echo "   ❌ Data transfer failed: {$transferResult['error']}\n";
                }
            } else {
                echo "   ❌ Data transfer test failed: {$result['error']}\n";
            }
            
        } catch (Exception $e) {
            echo "   ❌ Transfer test error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    private function testNotificationService()
    {
        echo "3. Testing Notification Service...\n";
        
        try {
            // Test notification service
            $result = $this->notificationService->testNotification();
            
            if ($result['success']) {
                echo "   ✅ Notification service test passed\n";
                echo "   📊 Wablas API: {$result['wablas_status']}\n";
                
                // Test sending a sample notification
                echo "   📱 Testing sample notification...\n";
                
                // Create sample data for testing
                $sampleData = [
                    'student_id' => 'TEST001',
                    'student_name' => 'Test Student',
                    'parent_phone' => '6281234567890', // Test phone number
                    'scan_date' => date('Y-m-d'),
                    'scan_time' => date('H:i:s'),
                    'type' => 'entry'
                ];
                
                $notifResult = $this->notificationService->sendNotification($sampleData);
                
                if ($notifResult['success']) {
                    echo "   ✅ Test notification sent successfully\n";
                    echo "   📱 Message ID: {$notifResult['message_id']}\n";
                } else {
                    echo "   ❌ Test notification failed: {$notifResult['error']}\n";
                }
                
            } else {
                echo "   ❌ Notification service test failed: {$result['error']}\n";
            }
            
        } catch (Exception $e) {
            echo "   ❌ Notification test error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    private function testFullModule()
    {
        echo "4. Testing Full Module Integration...\n";
        
        try {
            // Test module initialization
            $result = $this->module->test();
            
            if ($result['success']) {
                echo "   ✅ Module initialization successful\n";
                
                // Test monitoring
                echo "   🔍 Testing attendance monitoring...\n";
                $monitorResult = $this->module->startMonitoring();
                
                if ($monitorResult['success']) {
                    echo "   ✅ Attendance monitoring started\n";
                    
                    // Get statistics
                    $stats = $this->module->getStatistics();
                    echo "   📊 Today's processed records: {$stats['today_processed']}\n";
                    echo "   📊 Today's notifications sent: {$stats['today_notifications']}\n";
                    echo "   📊 Total transferred records: {$stats['total_transferred']}\n";
                    
                } else {
                    echo "   ❌ Attendance monitoring failed: {$monitorResult['error']}\n";
                }
                
            } else {
                echo "   ❌ Module test failed: {$result['error']}\n";
            }
            
        } catch (Exception $e) {
            echo "   ❌ Full module test error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    private function testHistoryLogs()
    {
        echo "5. Testing History and Logs...\n";
        
        try {
            $studentDb = new mysqli('localhost', 'root', '', 'studentfinger');
            
            if ($studentDb->connect_error) {
                echo "   ❌ Database connection failed\n";
                return;
            }
            
            // Check notification logs
            $result = $studentDb->query("SELECT COUNT(*) as count FROM whatsapp_notification_logs WHERE DATE(created_at) = CURDATE()");
            if ($result) {
                $row = $result->fetch_assoc();
                echo "   📊 Today's notification logs: {$row['count']}\n";
            }
            
            // Check transfer logs
            $result = $studentDb->query("SELECT COUNT(*) as count FROM whatsapp_transfer_logs WHERE DATE(started_at) = CURDATE()");
            if ($result) {
                $row = $result->fetch_assoc();
                echo "   📊 Today's transfer logs: {$row['count']}\n";
            }
            
            // Show recent notifications
            echo "   📱 Recent notifications:\n";
            $result = $studentDb->query("
                SELECT student_id, parent_phone, notification_type, status, sent_at 
                FROM whatsapp_notification_logs 
                WHERE DATE(created_at) = CURDATE() 
                ORDER BY created_at DESC 
                LIMIT 5
            ");
            
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $status = $row['status'] == 1 ? '✅' : '❌';
                    echo "     $status {$row['student_id']} -> {$row['parent_phone']} ({$row['notification_type']}) at {$row['sent_at']}\n";
                }
            } else {
                echo "     No notifications found for today\n";
            }
            
            // Show recent transfers
            echo "   🔄 Recent transfers:\n";
            $result = $studentDb->query("
                SELECT transfer_type, records_transferred, records_skipped, status, completed_at 
                FROM whatsapp_transfer_logs 
                WHERE DATE(started_at) = CURDATE() 
                ORDER BY started_at DESC 
                LIMIT 5
            ");
            
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $status = $row['status'] == 'completed' ? '✅' : '❌';
                    echo "     $status {$row['transfer_type']}: {$row['records_transferred']} transferred, {$row['records_skipped']} skipped at {$row['completed_at']}\n";
                }
            } else {
                echo "     No transfers found for today\n";
            }
            
            $studentDb->close();
            
        } catch (Exception $e) {
            echo "   ❌ History logs test error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    public function insertSampleData()
    {
        echo "=== INSERTING SAMPLE DATA FOR TESTING ===\n\n";
        
        try {
            // Insert sample student-parent mapping
            $studentDb = new mysqli('localhost', 'root', '', 'studentfinger');
            
            if ($studentDb->connect_error) {
                echo "❌ Database connection failed\n";
                return;
            }
            
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
                
                $stmt->bind_param('sssss', $mapping[0], $mapping[1], $mapping[2], $mapping[3], $mapping[4]);
                
                if ($stmt->execute()) {
                    echo "✅ Inserted mapping for {$mapping[1]} -> {$mapping[3]}\n";
                } else {
                    echo "❌ Failed to insert mapping for {$mapping[1]}\n";
                }
                
                $stmt->close();
            }
            
            // Insert sample attendance data in fin_pro
            $finProDb = new mysqli('localhost', 'root', '', 'fin_pro');
            
            if (!$finProDb->connect_error) {
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
                    
                    $stmt->bind_param('sss', $attendance[0], $attendance[1], $attendance[2]);
                    
                    if ($stmt->execute()) {
                        echo "✅ Inserted attendance for {$attendance[0]} at {$attendance[2]}\n";
                    } else {
                        echo "❌ Failed to insert attendance for {$attendance[0]}\n";
                    }
                    
                    $stmt->close();
                }
                
                $finProDb->close();
            }
            
            $studentDb->close();
            
        } catch (Exception $e) {
            echo "❌ Sample data insertion error: " . $e->getMessage() . "\n";
        }
        
        echo "\n=== SAMPLE DATA INSERTION COMPLETED ===\n\n";
    }
}

// Run the tests
$test = new WhatsAppAttendanceTest();

// Insert sample data first
$test->insertSampleData();

// Run all tests
$test->runAllTests();

?>