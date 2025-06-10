<?php

namespace App\Modules\WhatsAppAttendance\Services;

use CodeIgniter\Config\Services;
use App\Modules\WhatsAppAttendance\Models\AttendanceLogModel;
use App\Modules\WhatsAppAttendance\Models\TransferLogModel;

/**
 * Data Transfer Service
 * 
 * Handles transferring attendance data from fin_pro.att_log to studentfinger.att_log
 */
class DataTransferService
{
    protected $config;
    protected $logger;
    protected $finProDb;
    protected $studentFingerDb;
    protected $attendanceLogModel;
    protected $transferLogModel;
    
    public function __construct()
    {
        $this->config = config('WhatsAppAttendance');
        $this->logger = Services::logger();
        
        // Initialize database connections
        $this->finProDb = \Config\Database::connect('fin_pro');
        $this->studentFingerDb = \Config\Database::connect('default');
        
        // Initialize models
        $this->attendanceLogModel = new AttendanceLogModel();
        $this->transferLogModel = new TransferLogModel();
    }
    
    /**
     * Transfer attendance data from fin_pro to studentfinger
     */
    public function transferAttendanceData($records)
    {
        $transferResult = [
            'success' => false,
            'transferred_count' => 0,
            'skipped_count' => 0,
            'error_count' => 0,
            'transferred_records' => [],
            'errors' => []
        ];
        
        try {
            $this->logger->info('Starting data transfer for ' . count($records) . ' records');
            
            // Start transaction
            $this->studentFingerDb->transStart();
            
            foreach ($records as $record) {
                try {
                    $transferRecord = $this->processTransferRecord($record);
                    
                    if ($transferRecord) {
                        // Check for duplicates
                        if ($this->isDuplicateRecord($transferRecord)) {
                            if ($this->config['data_transfer']['duplicate_handling'] === 'skip') {
                                $transferResult['skipped_count']++;
                                $this->logger->debug('Skipped duplicate record', $transferRecord);
                                continue;
                            } elseif ($this->config['data_transfer']['duplicate_handling'] === 'update') {
                                $this->updateExistingRecord($transferRecord);
                                $transferResult['transferred_count']++;
                                $transferResult['transferred_records'][] = $transferRecord;
                            }
                        } else {
                            // Insert new record
                            $insertResult = $this->insertNewRecord($transferRecord);
                            
                            if ($insertResult) {
                                $transferResult['transferred_count']++;
                                $transferResult['transferred_records'][] = $transferRecord;
                                $this->logger->debug('Inserted new record', $transferRecord);
                            } else {
                                $transferResult['error_count']++;
                                $transferResult['errors'][] = 'Failed to insert record: ' . json_encode($transferRecord);
                            }
                        }
                    } else {
                        $transferResult['error_count']++;
                        $transferResult['errors'][] = 'Failed to process record: ' . json_encode($record);
                    }
                    
                } catch (\Exception $e) {
                    $transferResult['error_count']++;
                    $transferResult['errors'][] = 'Error processing record: ' . $e->getMessage();
                    $this->logger->error('Error processing transfer record: ' . $e->getMessage(), $record);
                }
            }
            
            // Complete transaction
            $this->studentFingerDb->transComplete();
            
            if ($this->studentFingerDb->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }
            
            $transferResult['success'] = true;
            
            // Log transfer activity
            $this->logTransferActivity($transferResult);
            
            $this->logger->info('Data transfer completed', $transferResult);
            
            return $transferResult;
            
        } catch (\Exception $e) {
            $this->studentFingerDb->transRollback();
            $transferResult['error'] = $e->getMessage();
            $this->logger->error('Data transfer failed: ' . $e->getMessage());
            
            return $transferResult;
        }
    }
    
    /**
     * Process and transform record for transfer
     */
    protected function processTransferRecord($record)
    {
        try {
            // Map fin_pro fields to studentfinger fields
            $transferRecord = [
                'att_id' => $this->generateAttendanceId($record),
                'pin' => $record['pin'],
                'scan_date' => $record['scan_date'],
                'verifymode' => $record['verifymode'],
                'status' => $this->determineAttendanceStatus($record),
                'serialnumber' => $record['sn'],
                'student_id' => $record['student_id'],
                'sn' => $record['sn'],
                'inoutmode' => $record['inoutmode'] ?? 1,
                'reserved' => $record['reserved'] ?? 0,
                'work_code' => $record['work_code'] ?? 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Validate student mapping if configured
            if ($this->config['data_transfer']['validate_student_mapping']) {
                if (!$this->validateStudentMapping($transferRecord['student_id'])) {
                    $this->logger->warning('Invalid student mapping for student_id: ' . $transferRecord['student_id']);
                    return null;
                }
            }
            
            return $transferRecord;
            
        } catch (\Exception $e) {
            $this->logger->error('Error processing transfer record: ' . $e->getMessage(), $record);
            return null;
        }
    }
    
    /**
     * Generate unique attendance ID
     */
    protected function generateAttendanceId($record)
    {
        // Create unique ID based on device serial, date, and pin
        $baseId = $record['sn'] . '_' . date('Ymd_His', strtotime($record['scan_date'])) . '_' . $record['pin'];
        return substr(md5($baseId), 0, 20);
    }
    
    /**
     * Determine attendance status based on time and rules
     */
    protected function determineAttendanceStatus($record)
    {
        $scanTime = date('H:i', strtotime($record['scan_date']));
        $entryStart = $this->config['school_hours']['entry_start'];
        $entryEnd = $this->config['school_hours']['entry_end'];
        $exitStart = $this->config['school_hours']['exit_start'];
        $exitEnd = $this->config['school_hours']['exit_end'];
        
        // Determine if it's entry or exit time
        if ($scanTime >= $entryStart && $scanTime <= $entryEnd) {
            // Entry time - check if late
            if ($scanTime <= $entryStart) {
                return 1; // Present (on time)
            } else {
                return 2; // Late
            }
        } elseif ($scanTime >= $exitStart && $scanTime <= $exitEnd) {
            return 1; // Present (exit)
        } else {
            return 1; // Present (default)
        }
    }
    
    /**
     * Check if record is duplicate
     */
    protected function isDuplicateRecord($record)
    {
        try {
            $existing = $this->attendanceLogModel
                ->where('pin', $record['pin'])
                ->where('scan_date', $record['scan_date'])
                ->where('serialnumber', $record['serialnumber'])
                ->first();
            
            return !empty($existing);
            
        } catch (\Exception $e) {
            $this->logger->error('Error checking duplicate record: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Insert new record
     */
    protected function insertNewRecord($record)
    {
        try {
            return $this->attendanceLogModel->insert($record);
            
        } catch (\Exception $e) {
            $this->logger->error('Error inserting new record: ' . $e->getMessage(), $record);
            return false;
        }
    }
    
    /**
     * Update existing record
     */
    protected function updateExistingRecord($record)
    {
        try {
            return $this->attendanceLogModel
                ->where('pin', $record['pin'])
                ->where('scan_date', $record['scan_date'])
                ->where('serialnumber', $record['serialnumber'])
                ->set($record)
                ->update();
                
        } catch (\Exception $e) {
            $this->logger->error('Error updating existing record: ' . $e->getMessage(), $record);
            return false;
        }
    }
    
    /**
     * Validate student mapping
     */
    protected function validateStudentMapping($studentId)
    {
        try {
            $student = $this->studentFingerDb->table('students')
                ->where('student_id', $studentId)
                ->where('status', 'Active')
                ->get()
                ->getRowArray();
            
            return !empty($student);
            
        } catch (\Exception $e) {
            $this->logger->error('Error validating student mapping: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log transfer activity
     */
    protected function logTransferActivity($result)
    {
        try {
            $this->transferLogModel->insert([
                'transfer_type' => 'attendance_data',
                'source_table' => 'fin_pro.att_log',
                'destination_table' => 'studentfinger.att_log',
                'records_processed' => $result['transferred_count'] + $result['skipped_count'] + $result['error_count'],
                'records_transferred' => $result['transferred_count'],
                'records_skipped' => $result['skipped_count'],
                'records_failed' => $result['error_count'],
                'status' => $result['success'] ? 'success' : 'failed',
                'error_details' => !empty($result['errors']) ? json_encode($result['errors']) : null,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('Error logging transfer activity: ' . $e->getMessage());
        }
    }
    
    /**
     * Get total transferred records count
     */
    public function getTotalTransferredRecords()
    {
        try {
            return $this->transferLogModel
                ->selectSum('records_transferred')
                ->where('transfer_type', 'attendance_data')
                ->get()
                ->getRow()
                ->records_transferred ?? 0;
                
        } catch (\Exception $e) {
            $this->logger->error('Error getting total transferred records: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Test data transfer functionality
     */
    public function testDataTransfer()
    {
        try {
            // Test database connections
            $finProTest = $this->finProDb->query('SELECT 1 as test')->getRow();
            $studentFingerTest = $this->studentFingerDb->query('SELECT 1 as test')->getRow();
            
            if (!$finProTest || !$studentFingerTest) {
                throw new \Exception('Database connection test failed');
            }
            
            // Test table access
            $finProTableTest = $this->finProDb->query('SELECT COUNT(*) as count FROM att_log LIMIT 1')->getRow();
            $studentFingerTableTest = $this->studentFingerDb->query('SELECT COUNT(*) as count FROM att_log LIMIT 1')->getRow();
            
            return [
                'success' => true,
                'fin_pro_connection' => true,
                'studentfinger_connection' => true,
                'fin_pro_table_access' => true,
                'studentfinger_table_access' => true,
                'fin_pro_records' => $finProTableTest->count ?? 0,
                'studentfinger_records' => $studentFingerTableTest->count ?? 0
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get transfer statistics
     */
    public function getTransferStatistics($days = 7)
    {
        try {
            $startDate = date('Y-m-d', strtotime("-{$days} days"));
            
            $stats = $this->transferLogModel
                ->select('DATE(created_at) as date, SUM(records_transferred) as total_transferred, SUM(records_failed) as total_failed')
                ->where('created_at >=', $startDate)
                ->where('transfer_type', 'attendance_data')
                ->groupBy('DATE(created_at)')
                ->orderBy('date', 'DESC')
                ->get()
                ->getResultArray();
            
            return $stats;
            
        } catch (\Exception $e) {
            $this->logger->error('Error getting transfer statistics: ' . $e->getMessage());
            return [];
        }
    }
}