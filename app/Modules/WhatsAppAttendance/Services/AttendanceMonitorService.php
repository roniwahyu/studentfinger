<?php

namespace App\Modules\WhatsAppAttendance\Services;

use CodeIgniter\Config\Services;
use App\Modules\WhatsAppAttendance\Models\AttendanceLogModel;
use App\Modules\WhatsAppAttendance\Models\MonitoringLogModel;

/**
 * Attendance Monitor Service
 * 
 * Monitors new attendance records from fin_pro.att_log table
 */
class AttendanceMonitorService
{
    protected $config;
    protected $logger;
    protected $finProDb;
    protected $studentFingerDb;
    protected $monitoringLogModel;
    
    public function __construct()
    {
        $this->config = config('WhatsAppAttendance');
        $this->logger = Services::logger();
        
        // Initialize database connections
        $this->finProDb = \Config\Database::connect('fin_pro');
        $this->studentFingerDb = \Config\Database::connect('default');
        
        // Initialize models
        $this->monitoringLogModel = new MonitoringLogModel();
    }
    
    /**
     * Check for new attendance records
     */
    public function checkNewAttendance()
    {
        try {
            $this->logger->info('Checking for new attendance records...');
            
            // Get last monitoring timestamp
            $lastMonitoring = $this->getLastMonitoringTime();
            
            // Query new records from fin_pro.att_log
            $query = $this->finProDb->table('att_log')
                ->where('scan_date >', $lastMonitoring)
                ->where('DATE(scan_date)', date('Y-m-d')) // Only today's records
                ->orderBy('scan_date', 'ASC')
                ->limit($this->config['monitoring']['max_records_per_batch']);
            
            // Filter by school hours if configured
            if (!$this->config['monitoring']['monitor_weekends'] && $this->isWeekend()) {
                $this->logger->info('Skipping monitoring - Weekend');
                return [];
            }
            
            if (!$this->config['monitoring']['monitor_holidays'] && $this->isHoliday()) {
                $this->logger->info('Skipping monitoring - Holiday');
                return [];
            }
            
            $newRecords = $query->get()->getResultArray();
            
            if (!empty($newRecords)) {
                $this->logger->info('Found ' . count($newRecords) . ' new attendance records');
                
                // Update last monitoring time
                $this->updateLastMonitoringTime();
                
                // Log monitoring activity
                $this->logMonitoringActivity(count($newRecords));
                
                return $this->processNewRecords($newRecords);
            }
            
            return [];
            
        } catch (\Exception $e) {
            $this->logger->error('Error checking new attendance: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Process new records and validate them
     */
    protected function processNewRecords($records)
    {
        $processedRecords = [];
        
        foreach ($records as $record) {
            try {
                // Validate record
                if ($this->validateRecord($record)) {
                    // Map PIN to student_id
                    $studentId = $this->mapPinToStudentId($record['pin']);
                    
                    if ($studentId) {
                        $record['student_id'] = $studentId;
                        $record['original_pin'] = $record['pin'];
                        $processedRecords[] = $record;
                        
                        $this->logger->debug("Processed record for PIN {$record['pin']} -> Student ID {$studentId}");
                    } else {
                        $this->logger->warning("No student mapping found for PIN: {$record['pin']}");
                    }
                } else {
                    $this->logger->warning('Invalid record skipped', $record);
                }
                
            } catch (\Exception $e) {
                $this->logger->error('Error processing record: ' . $e->getMessage(), $record);
            }
        }
        
        return $processedRecords;
    }
    
    /**
     * Validate attendance record
     */
    protected function validateRecord($record)
    {
        // Check required fields
        if (empty($record['pin']) || empty($record['scan_date']) || empty($record['sn'])) {
            return false;
        }
        
        // Validate scan_date format
        if (!strtotime($record['scan_date'])) {
            return false;
        }
        
        // Check if record is within school hours (if configured)
        if ($this->config['monitoring']['enable_real_time']) {
            $scanTime = date('H:i', strtotime($record['scan_date']));
            $schoolStart = $this->config['school_hours']['entry_start'];
            $schoolEnd = $this->config['school_hours']['exit_end'];
            
            if ($scanTime < $schoolStart || $scanTime > $schoolEnd) {
                $this->logger->debug("Record outside school hours: {$scanTime}");
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Map PIN to student_id
     */
    protected function mapPinToStudentId($pin)
    {
        try {
            // First try direct mapping from students table
            $student = $this->studentFingerDb->table('students')
                ->where('pin', $pin)
                ->orWhere('rfid', $pin)
                ->orWhere('student_id', $pin)
                ->get()
                ->getRowArray();
            
            if ($student) {
                return $student['student_id'];
            }
            
            // Try mapping from student_pin_mapping table if exists
            if ($this->studentFingerDb->tableExists('student_pin_mapping')) {
                $mapping = $this->studentFingerDb->table('student_pin_mapping')
                    ->where('pin', $pin)
                    ->get()
                    ->getRowArray();
                
                if ($mapping) {
                    return $mapping['student_id'];
                }
            }
            
            return null;
            
        } catch (\Exception $e) {
            $this->logger->error('Error mapping PIN to student ID: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get last monitoring timestamp
     */
    public function getLastMonitoringTime()
    {
        try {
            $lastLog = $this->monitoringLogModel
                ->orderBy('created_at', 'DESC')
                ->first();
            
            if ($lastLog) {
                return $lastLog['last_scan_date'] ?? $lastLog['created_at'];
            }
            
            // Default to start of today if no previous monitoring
            return date('Y-m-d 00:00:00');
            
        } catch (\Exception $e) {
            $this->logger->error('Error getting last monitoring time: ' . $e->getMessage());
            return date('Y-m-d 00:00:00');
        }
    }
    
    /**
     * Update last monitoring time
     */
    protected function updateLastMonitoringTime()
    {
        try {
            $this->monitoringLogModel->insert([
                'monitoring_type' => 'attendance_check',
                'last_scan_date' => date('Y-m-d H:i:s'),
                'status' => 'completed',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('Error updating last monitoring time: ' . $e->getMessage());
        }
    }
    
    /**
     * Log monitoring activity
     */
    protected function logMonitoringActivity($recordCount)
    {
        try {
            $this->monitoringLogModel->insert([
                'monitoring_type' => 'new_records_found',
                'records_count' => $recordCount,
                'status' => 'success',
                'details' => json_encode([
                    'timestamp' => date('Y-m-d H:i:s'),
                    'records_found' => $recordCount
                ]),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('Error logging monitoring activity: ' . $e->getMessage());
        }
    }
    
    /**
     * Check if today is weekend
     */
    protected function isWeekend()
    {
        $dayOfWeek = date('N'); // 1 (Monday) to 7 (Sunday)
        return $dayOfWeek >= 6; // Saturday or Sunday
    }
    
    /**
     * Check if today is holiday
     */
    protected function isHoliday()
    {
        try {
            // Check if libur table exists and today is marked as holiday
            if ($this->studentFingerDb->tableExists('libur')) {
                $holiday = $this->studentFingerDb->table('libur')
                    ->where('tanggal', date('Y-m-d'))
                    ->get()
                    ->getRowArray();
                
                return !empty($holiday);
            }
            
            return false;
            
        } catch (\Exception $e) {
            $this->logger->error('Error checking holiday: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get total monitored records count
     */
    public function getTotalMonitoredRecords()
    {
        try {
            return $this->monitoringLogModel
                ->selectSum('records_count')
                ->where('monitoring_type', 'new_records_found')
                ->get()
                ->getRow()
                ->records_count ?? 0;
                
        } catch (\Exception $e) {
            $this->logger->error('Error getting total monitored records: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get monitoring statistics
     */
    public function getMonitoringStatistics($days = 7)
    {
        try {
            $startDate = date('Y-m-d', strtotime("-{$days} days"));
            
            $stats = $this->monitoringLogModel
                ->select('DATE(created_at) as date, SUM(records_count) as total_records')
                ->where('created_at >=', $startDate)
                ->where('monitoring_type', 'new_records_found')
                ->groupBy('DATE(created_at)')
                ->orderBy('date', 'DESC')
                ->get()
                ->getResultArray();
            
            return $stats;
            
        } catch (\Exception $e) {
            $this->logger->error('Error getting monitoring statistics: ' . $e->getMessage());
            return [];
        }
    }
}