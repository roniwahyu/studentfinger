<?php

namespace App\Modules\WhatsAppAttendance\Models;

use CodeIgniter\Model;

/**
 * Transfer Log Model
 * 
 * Handles data transfer logging between fin_pro and studentfinger databases
 */
class TransferLogModel extends Model
{
    protected $table = 'whatsapp_transfer_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'transfer_type',
        'source_table',
        'destination_table',
        'records_processed',
        'records_transferred',
        'records_skipped',
        'records_failed',
        'status',
        'error_details',
        'processing_time',
        'started_at',
        'completed_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'transfer_type' => 'required|max_length[50]',
        'source_table' => 'required|max_length[100]',
        'destination_table' => 'required|max_length[100]',
        'records_processed' => 'required|integer',
        'records_transferred' => 'required|integer',
        'records_skipped' => 'permit_empty|integer',
        'records_failed' => 'permit_empty|integer',
        'status' => 'required|in_list[success,failed,partial,running]',
        'processing_time' => 'permit_empty|decimal'
    ];
    
    protected $validationMessages = [
        'transfer_type' => [
            'required' => 'Transfer type is required',
            'max_length' => 'Transfer type cannot exceed 50 characters'
        ],
        'source_table' => [
            'required' => 'Source table is required',
            'max_length' => 'Source table cannot exceed 100 characters'
        ],
        'destination_table' => [
            'required' => 'Destination table is required',
            'max_length' => 'Destination table cannot exceed 100 characters'
        ],
        'records_processed' => [
            'required' => 'Records processed count is required',
            'integer' => 'Records processed must be a valid integer'
        ],
        'records_transferred' => [
            'required' => 'Records transferred count is required',
            'integer' => 'Records transferred must be a valid integer'
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Status must be one of: success, failed, partial, running'
        ]
    ];
    
    /**
     * Get transfer logs by date range
     */
    public function getByDateRange($startDate, $endDate, $transferType = null)
    {
        $builder = $this->builder()
            ->where('created_at >=', $startDate)
            ->where('created_at <=', $endDate)
            ->orderBy('created_at', 'DESC');
        
        if ($transferType) {
            $builder->where('transfer_type', $transferType);
        }
        
        return $builder->get()->getResultArray();
    }
    
    /**
     * Get today's transfer logs
     */
    public function getTodayTransfers($transferType = null)
    {
        $today = date('Y-m-d');
        
        $builder = $this->builder()
            ->where('DATE(created_at)', $today)
            ->orderBy('created_at', 'DESC');
        
        if ($transferType) {
            $builder->where('transfer_type', $transferType);
        }
        
        return $builder->get()->getResultArray();
    }
    
    /**
     * Get transfer statistics
     */
    public function getStatistics($days = 7)
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        // Total transfers
        $total = $this->where('created_at >=', $startDate)
            ->countAllResults();
        
        // By status
        $byStatus = $this->select('status, COUNT(*) as count')
            ->where('created_at >=', $startDate)
            ->groupBy('status')
            ->get()
            ->getResultArray();
        
        // By transfer type
        $byType = $this->select('transfer_type, COUNT(*) as count')
            ->where('created_at >=', $startDate)
            ->groupBy('transfer_type')
            ->get()
            ->getResultArray();
        
        // Total records processed
        $totalRecords = $this->selectSum('records_processed')
            ->where('created_at >=', $startDate)
            ->get()
            ->getRow()
            ->records_processed ?? 0;
        
        // Total records transferred
        $totalTransferred = $this->selectSum('records_transferred')
            ->where('created_at >=', $startDate)
            ->get()
            ->getRow()
            ->records_transferred ?? 0;
        
        // Total records failed
        $totalFailed = $this->selectSum('records_failed')
            ->where('created_at >=', $startDate)
            ->get()
            ->getRow()
            ->records_failed ?? 0;
        
        // Success rate
        $successRate = $totalRecords > 0 ? round(($totalTransferred / $totalRecords) * 100, 2) : 0;
        
        // Average processing time
        $avgProcessingTime = $this->selectAvg('processing_time')
            ->where('created_at >=', $startDate)
            ->where('processing_time IS NOT NULL')
            ->get()
            ->getRow()
            ->processing_time ?? 0;
        
        return [
            'total_transfers' => $total,
            'total_records_processed' => $totalRecords,
            'total_records_transferred' => $totalTransferred,
            'total_records_failed' => $totalFailed,
            'success_rate' => $successRate,
            'average_processing_time' => round($avgProcessingTime, 2),
            'by_status' => $byStatus,
            'by_type' => $byType,
            'period_days' => $days
        ];
    }
    
    /**
     * Get daily transfer summary
     */
    public function getDailySummary($date)
    {
        $result = $this->select('transfer_type, status, SUM(records_processed) as processed, SUM(records_transferred) as transferred, SUM(records_failed) as failed')
            ->where('DATE(created_at)', $date)
            ->groupBy(['transfer_type', 'status'])
            ->get()
            ->getResultArray();
        
        $summary = [];
        
        foreach ($result as $row) {
            $type = $row['transfer_type'];
            $status = $row['status'];
            
            if (!isset($summary[$type])) {
                $summary[$type] = [
                    'processed' => 0,
                    'transferred' => 0,
                    'failed' => 0,
                    'success_count' => 0,
                    'failed_count' => 0
                ];
            }
            
            $summary[$type]['processed'] += $row['processed'];
            $summary[$type]['transferred'] += $row['transferred'];
            $summary[$type]['failed'] += $row['failed'];
            
            if ($status === 'success') {
                $summary[$type]['success_count']++;
            } elseif ($status === 'failed') {
                $summary[$type]['failed_count']++;
            }
        }
        
        return $summary;
    }
    
    /**
     * Get latest transfer by type
     */
    public function getLatestByType($transferType)
    {
        return $this->where('transfer_type', $transferType)
            ->orderBy('created_at', 'DESC')
            ->first();
    }
    
    /**
     * Get failed transfers for retry
     */
    public function getFailedTransfers($hours = 24)
    {
        $cutoffTime = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));
        
        return $this->where('status', 'failed')
            ->where('created_at >=', $cutoffTime)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
    
    /**
     * Get running transfers (potentially stuck)
     */
    public function getRunningTransfers($hours = 2)
    {
        $cutoffTime = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));
        
        return $this->where('status', 'running')
            ->where('started_at <', $cutoffTime)
            ->findAll();
    }
    
    /**
     * Start transfer log
     */
    public function startTransfer($transferType, $sourceTable, $destinationTable)
    {
        $data = [
            'transfer_type' => $transferType,
            'source_table' => $sourceTable,
            'destination_table' => $destinationTable,
            'records_processed' => 0,
            'records_transferred' => 0,
            'records_skipped' => 0,
            'records_failed' => 0,
            'status' => 'running',
            'started_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->insert($data);
    }
    
    /**
     * Complete transfer log
     */
    public function completeTransfer($id, $result)
    {
        $startTime = $this->find($id)['started_at'] ?? null;
        $processingTime = null;
        
        if ($startTime) {
            $processingTime = (strtotime(date('Y-m-d H:i:s')) - strtotime($startTime)) / 60; // in minutes
        }
        
        $data = [
            'records_processed' => $result['transferred_count'] + $result['skipped_count'] + $result['error_count'],
            'records_transferred' => $result['transferred_count'],
            'records_skipped' => $result['skipped_count'],
            'records_failed' => $result['error_count'],
            'status' => $result['success'] ? 'success' : 'failed',
            'error_details' => !empty($result['errors']) ? json_encode($result['errors']) : null,
            'processing_time' => $processingTime,
            'completed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->update($id, $data);
    }
    
    /**
     * Get transfer performance metrics
     */
    public function getPerformanceMetrics($days = 30)
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        // Average records per transfer
        $avgRecordsPerTransfer = $this->selectAvg('records_processed')
            ->where('created_at >=', $startDate)
            ->where('status !=', 'running')
            ->get()
            ->getRow()
            ->records_processed ?? 0;
        
        // Peak transfer times
        $peakHours = $this->select('HOUR(started_at) as hour, COUNT(*) as count')
            ->where('created_at >=', $startDate)
            ->groupBy('HOUR(started_at)')
            ->orderBy('count', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();
        
        // Transfer frequency
        $dailyFrequency = $this->select('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at >=', $startDate)
            ->groupBy('DATE(created_at)')
            ->orderBy('date', 'DESC')
            ->get()
            ->getResultArray();
        
        return [
            'average_records_per_transfer' => round($avgRecordsPerTransfer, 2),
            'peak_hours' => $peakHours,
            'daily_frequency' => $dailyFrequency
        ];
    }
    
    /**
     * Clean old transfer logs
     */
    public function cleanOldLogs($days = 90)
    {
        $cutoffDate = date('Y-m-d', strtotime("-{$days} days"));
        
        return $this->where('created_at <', $cutoffDate)
            ->delete();
    }
    
    /**
     * Get error analysis
     */
    public function getErrorAnalysis($days = 7)
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        $errors = $this->select('error_details')
            ->where('created_at >=', $startDate)
            ->where('status', 'failed')
            ->where('error_details IS NOT NULL')
            ->get()
            ->getResultArray();
        
        $errorCounts = [];
        
        foreach ($errors as $error) {
            $details = json_decode($error['error_details'], true);
            
            if (is_array($details)) {
                foreach ($details as $detail) {
                    $key = substr($detail, 0, 100); // Truncate for grouping
                    $errorCounts[$key] = ($errorCounts[$key] ?? 0) + 1;
                }
            }
        }
        
        arsort($errorCounts);
        
        return array_slice($errorCounts, 0, 10, true); // Top 10 errors
    }
    
    /**
     * Get transfer trends
     */
    public function getTransferTrends($days = 30)
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        return $this->select('DATE(created_at) as date, transfer_type, SUM(records_transferred) as total_transferred')
            ->where('created_at >=', $startDate)
            ->groupBy(['DATE(created_at)', 'transfer_type'])
            ->orderBy('date', 'ASC')
            ->get()
            ->getResultArray();
    }
}