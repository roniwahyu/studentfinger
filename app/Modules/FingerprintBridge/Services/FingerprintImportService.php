<?php

namespace App\Modules\FingerprintBridge\Services;

use App\Modules\FingerprintBridge\Models\FinProAttLogModel;
use App\Modules\FingerprintBridge\Models\StudentFingerAttLogModel;
use App\Modules\FingerprintBridge\Models\ImportLogModel;
use App\Modules\FingerprintBridge\Models\StudentPinMappingModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * Fingerprint Import Service
 * 
 * Handles the business logic for importing attendance data from fin_pro to studentfinger
 */
class FingerprintImportService
{
    protected $finProModel;
    protected $studentFingerModel;
    protected $importLogModel;
    protected $pinMappingModel;
    protected $config;
    
    public function __construct()
    {
        $this->finProModel = new FinProAttLogModel();
        $this->studentFingerModel = new StudentFingerAttLogModel();
        $this->importLogModel = new ImportLogModel();
        $this->pinMappingModel = new StudentPinMappingModel();
        
        // Load module configuration
        $this->config = config('App\Modules\FingerprintBridge\Config\Module');
    }
    
    /**
     * Test connection to fin_pro database
     */
    public function testFinProConnection(): array
    {
        try {
            $isConnected = $this->finProModel->testConnection();
            
            if ($isConnected) {
                $dbInfo = $this->finProModel->getDatabaseInfo();
                return [
                    'success' => true,
                    'message' => 'Connection successful',
                    'data' => $dbInfo
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to connect to fin_pro database'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Preview import data
     */
    public function previewImport(string $startDate, string $endDate, int $limit = 10): array
    {
        try {
            $records = $this->finProModel->getByDateRange($startDate, $endDate, $limit);
            $totalCount = $this->finProModel->countByDateRange($startDate, $endDate);
            
            // Check for existing records
            $existingCount = 0;
            foreach ($records as $record) {
                if ($this->studentFingerModel->recordExists($record['sn'], $record['scan_date'], $record['pin'])) {
                    $existingCount++;
                }
            }
            
            return [
                'success' => true,
                'data' => [
                    'records' => $records,
                    'total_count' => $totalCount,
                    'preview_count' => count($records),
                    'existing_count' => $existingCount,
                    'new_count' => count($records) - $existingCount
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Preview error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Import attendance data
     */
    public function importData(array $options = []): array
    {
        $defaultOptions = [
            'start_date' => null,
            'end_date' => null,
            'batch_size' => $this->config['settings']['import_batch_size'] ?? 1000,
            'duplicate_handling' => $this->config['settings']['duplicate_handling'] ?? 'skip',
            'import_type' => 'manual',
            'user_id' => null
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        // Create import log
        $logId = $this->importLogModel->createImportLog([
            'import_type' => $options['import_type'],
            'start_date' => $options['start_date'],
            'end_date' => $options['end_date'],
            'user_id' => $options['user_id'],
            'settings' => $options
        ]);
        
        try {
            // Update log status to running
            $this->importLogModel->updateStatus($logId, 'running');
            
            // Get total count
            $totalRecords = $this->finProModel->countByDateRange($options['start_date'], $options['end_date']);
            $this->importLogModel->updateProgress($logId, ['total_records' => $totalRecords]);
            
            // Process in batches
            $offset = 0;
            $batchSize = $options['batch_size'];
            $processedCount = 0;
            $insertedCount = 0;
            $updatedCount = 0;
            $skippedCount = 0;
            $errorCount = 0;
            
            while ($offset < $totalRecords) {
                $records = $this->finProModel->getByDateRange(
                    $options['start_date'],
                    $options['end_date'],
                    $batchSize,
                    $offset
                );
                
                if (empty($records)) {
                    break;
                }
                
                foreach ($records as $record) {
                    $result = $this->processRecord($record, $options['duplicate_handling']);
                    
                    $processedCount++;
                    
                    switch ($result['action']) {
                        case 'inserted':
                            $insertedCount++;
                            break;
                        case 'updated':
                            $updatedCount++;
                            break;
                        case 'skipped':
                            $skippedCount++;
                            break;
                        default:
                            $errorCount++;
                            break;
                    }
                    
                    // Update progress every 100 records
                    if ($processedCount % 100 === 0) {
                        $this->importLogModel->updateProgress($logId, [
                            'processed_records' => $processedCount,
                            'inserted_records' => $insertedCount,
                            'updated_records' => $updatedCount,
                            'skipped_records' => $skippedCount,
                            'error_records' => $errorCount
                        ]);
                    }
                }
                
                $offset += $batchSize;
            }
            
            // Final progress update
            $this->importLogModel->updateProgress($logId, [
                'processed_records' => $processedCount,
                'inserted_records' => $insertedCount,
                'updated_records' => $updatedCount,
                'skipped_records' => $skippedCount,
                'error_records' => $errorCount
            ]);
            
            // Update student IDs in attendance logs
            $mappedCount = $this->pinMappingModel->updateAttendanceLogsWithStudentIds();
            
            // Mark as completed
            $this->importLogModel->updateStatus($logId, 'completed');
            
            return [
                'success' => true,
                'log_id' => $logId,
                'message' => 'Import completed successfully',
                'data' => [
                    'total_records' => $totalRecords,
                    'processed_records' => $processedCount,
                    'inserted_records' => $insertedCount,
                    'updated_records' => $updatedCount,
                    'skipped_records' => $skippedCount,
                    'error_records' => $errorCount,
                    'mapped_students' => $mappedCount
                ]
            ];
            
        } catch (\Exception $e) {
            // Mark as failed
            $this->importLogModel->updateStatus($logId, 'failed', [
                'error_message' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'log_id' => $logId,
                'message' => 'Import failed: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Process a single record
     */
    protected function processRecord(array $record, string $duplicateHandling): array
    {
        try {
            // Transform record for studentfinger database
            $transformedRecord = $this->transformRecord($record);
            
            // Get student ID from PIN mapping
            $studentId = $this->pinMappingModel->getStudentIdByPin($record['pin']);
            if ($studentId) {
                $transformedRecord['student_id'] = $studentId;
            }
            
            // Insert with duplicate check
            return $this->studentFingerModel->insertWithDuplicateCheck($transformedRecord, $duplicateHandling);
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'action' => 'error',
                'message' => 'Error processing record: ' . $e->getMessage(),
                'data' => $record
            ];
        }
    }
    
    /**
     * Transform fin_pro record to studentfinger format
     */
    protected function transformRecord(array $record): array
    {
        $fieldMapping = $this->config['field_mapping'];
        $transformed = [];
        
        // Map fields according to configuration
        foreach ($fieldMapping as $sourceField => $targetField) {
            if (isset($record[$sourceField])) {
                $transformed[$targetField] = $record[$sourceField];
            }
        }
        
        // Add additional fields
        $transformed['status'] = $this->config['settings']['default_status'] ?? 1;
        $transformed['serialnumber'] = $record['sn'] ?? null;
        
        // Generate unique att_id if not provided or empty
        if (empty($transformed['att_id']) || $transformed['att_id'] === '0') {
            $transformed['att_id'] = $this->generateAttId($record);
        }
        
        return $transformed;
    }
    
    /**
     * Generate attendance ID
     */
    protected function generateAttId(array $record): string
    {
        return $record['sn'] . '_' . date('YmdHis', strtotime($record['scan_date'])) . '_' . $record['pin'];
    }
    
    /**
     * Get import statistics
     */
    public function getImportStats(): array
    {
        $finProStats = $this->finProModel->getDatabaseInfo();
        $studentFingerStats = $this->studentFingerModel->getImportStats();
        $importLogStats = $this->importLogModel->getImportStats();
        $mappingStats = $this->pinMappingModel->getMappingStats();
        
        return [
            'fin_pro' => $finProStats,
            'student_finger' => $studentFingerStats,
            'import_logs' => $importLogStats,
            'pin_mapping' => $mappingStats
        ];
    }
    
    /**
     * Auto-create PIN mappings from RFID
     */
    public function autoCreatePinMappings(): array
    {
        try {
            $mappedCount = $this->pinMappingModel->createMappingFromRfid();

            return [
                'success' => true,
                'message' => "Successfully created {$mappedCount} PIN mappings from RFID cards",
                'mapped_count' => $mappedCount
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error creating PIN mappings: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Cancel running import
     */
    public function cancelImport(int $logId): array
    {
        try {
            $result = $this->importLogModel->updateStatus($logId, 'cancelled');

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Import cancelled successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to cancel import'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error cancelling import: ' . $e->getMessage()
            ];
        }
    }
}
