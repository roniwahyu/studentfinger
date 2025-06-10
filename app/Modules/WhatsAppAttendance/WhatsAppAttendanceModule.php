<?php

namespace App\Modules\WhatsAppAttendance;

use CodeIgniter\Config\Services;
use App\Modules\WhatsAppAttendance\Services\AttendanceMonitorService;
use App\Modules\WhatsAppAttendance\Services\DataTransferService;
use App\Modules\WhatsAppAttendance\Services\NotificationService;

/**
 * WhatsApp Attendance Module
 * 
 * Monitors attendance from fin_pro.att_log, transfers data to studentfinger.att_log,
 * and sends WhatsApp notifications to parents via Wablas API
 */
class WhatsAppAttendanceModule
{
    protected $config;
    protected $logger;
    protected $attendanceMonitor;
    protected $dataTransfer;
    protected $notification;
    
    public function __construct()
    {
        $this->config = config('WhatsAppAttendance');
        $this->logger = Services::logger();
        
        // Initialize services
        $this->attendanceMonitor = new AttendanceMonitorService();
        $this->dataTransfer = new DataTransferService();
        $this->notification = new NotificationService();
        
        $this->logger->info('WhatsApp Attendance Module initialized');
    }
    
    /**
     * Start monitoring attendance
     */
    public function startMonitoring()
    {
        try {
            $this->logger->info('Starting attendance monitoring...');
            
            // Monitor new attendance records
            $newRecords = $this->attendanceMonitor->checkNewAttendance();
            
            if (!empty($newRecords)) {
                $this->logger->info('Found ' . count($newRecords) . ' new attendance records');
                
                // Transfer data to studentfinger database
                $transferResult = $this->dataTransfer->transferAttendanceData($newRecords);
                
                if ($transferResult['success']) {
                    $this->logger->info('Successfully transferred ' . $transferResult['transferred_count'] . ' records');
                    
                    // Send notifications to parents
                    $this->sendAttendanceNotifications($transferResult['transferred_records']);
                } else {
                    $this->logger->error('Data transfer failed: ' . $transferResult['error']);
                }
            } else {
                $this->logger->info('No new attendance records found');
            }
            
        } catch (\Exception $e) {
            $this->logger->error('Attendance monitoring error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Send attendance notifications to parents
     */
    protected function sendAttendanceNotifications($records)
    {
        foreach ($records as $record) {
            try {
                // Determine if it's entry or exit
                $isEntry = $this->isEntryTime($record['scan_date']);
                $isExit = $this->isExitTime($record['scan_date']);
                
                if ($isEntry || $isExit) {
                    $result = $this->notification->sendAttendanceNotification(
                        $record['student_id'],
                        $record['scan_date'],
                        $isEntry ? 'entry' : 'exit',
                        $record
                    );
                    
                    if ($result['success']) {
                        $this->logger->info("Notification sent for student {$record['student_id']}");
                    } else {
                        $this->logger->error("Failed to send notification for student {$record['student_id']}: {$result['error']}");
                    }
                }
                
            } catch (\Exception $e) {
                $this->logger->error("Notification error for student {$record['student_id']}: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Check if time is within entry hours
     */
    protected function isEntryTime($scanDate)
    {
        $time = date('H:i', strtotime($scanDate));
        $entryStart = $this->config['school_hours']['entry_start'] ?? '06:30';
        $entryEnd = $this->config['school_hours']['entry_end'] ?? '08:30';
        
        return $time >= $entryStart && $time <= $entryEnd;
    }
    
    /**
     * Check if time is within exit hours
     */
    protected function isExitTime($scanDate)
    {
        $time = date('H:i', strtotime($scanDate));
        $exitStart = $this->config['school_hours']['exit_start'] ?? '14:00';
        $exitEnd = $this->config['school_hours']['exit_end'] ?? '16:00';
        
        return $time >= $exitStart && $time <= $exitEnd;
    }
    
    /**
     * Get monitoring statistics
     */
    public function getStatistics()
    {
        return [
            'total_monitored' => $this->attendanceMonitor->getTotalMonitoredRecords(),
            'total_transferred' => $this->dataTransfer->getTotalTransferredRecords(),
            'total_notifications' => $this->notification->getTotalNotificationsSent(),
            'last_monitoring' => $this->attendanceMonitor->getLastMonitoringTime(),
            'errors_count' => $this->getErrorsCount()
        ];
    }
    
    /**
     * Get errors count from logs
     */
    protected function getErrorsCount()
    {
        // Implementation to count errors from logs
        return 0;
    }
    
    /**
     * Test the module functionality
     */
    public function runTest()
    {
        $this->logger->info('Running WhatsApp Attendance Module test...');
        
        try {
            // Test database connections
            $dbTest = $this->testDatabaseConnections();
            
            // Test Wablas API connection
            $apiTest = $this->notification->testWablasConnection();
            
            // Test data transfer
            $transferTest = $this->dataTransfer->testDataTransfer();
            
            $results = [
                'database_connections' => $dbTest,
                'wablas_api' => $apiTest,
                'data_transfer' => $transferTest,
                'overall_status' => $dbTest['success'] && $apiTest['success'] && $transferTest['success']
            ];
            
            $this->logger->info('Test completed', $results);
            return $results;
            
        } catch (\Exception $e) {
            $this->logger->error('Test failed: ' . $e->getMessage());
            return [
                'overall_status' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Test database connections
     */
    protected function testDatabaseConnections()
    {
        try {
            $finProDb = \Config\Database::connect('fin_pro');
            $studentFingerDb = \Config\Database::connect('default');
            
            // Test fin_pro connection
            $finProTest = $finProDb->query('SELECT COUNT(*) as count FROM att_log LIMIT 1');
            $finProResult = $finProTest->getRow();
            
            // Test studentfinger connection
            $studentFingerTest = $studentFingerDb->query('SELECT COUNT(*) as count FROM att_log LIMIT 1');
            $studentFingerResult = $studentFingerTest->getRow();
            
            return [
                'success' => true,
                'fin_pro_records' => $finProResult->count ?? 0,
                'studentfinger_records' => $studentFingerResult->count ?? 0
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}