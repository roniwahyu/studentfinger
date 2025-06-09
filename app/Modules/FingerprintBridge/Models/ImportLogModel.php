<?php

namespace App\Modules\FingerprintBridge\Models;

use CodeIgniter\Model;

/**
 * Import Log Model
 * 
 * Handles database operations for fingerprint import logs
 */
class ImportLogModel extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'fingerprint_import_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'import_type',
        'start_date',
        'end_date',
        'status',
        'total_records',
        'processed_records',
        'inserted_records',
        'updated_records',
        'skipped_records',
        'error_records',
        'start_time',
        'end_time',
        'duration',
        'error_message',
        'settings',
        'user_id'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'import_type' => 'required|in_list[manual,auto,scheduled]',
        'start_date' => 'permit_empty|valid_date',
        'end_date' => 'permit_empty|valid_date',
        'status' => 'required|in_list[pending,running,completed,failed,cancelled]',
        'total_records' => 'permit_empty|integer',
        'processed_records' => 'permit_empty|integer',
        'inserted_records' => 'permit_empty|integer',
        'updated_records' => 'permit_empty|integer',
        'skipped_records' => 'permit_empty|integer',
        'error_records' => 'permit_empty|integer'
    ];
    
    protected $validationMessages = [
        'import_type' => [
            'required' => 'Import type is required',
            'in_list' => 'Import type must be manual, auto, or scheduled'
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Status must be pending, running, completed, failed, or cancelled'
        ]
    ];
    
    /**
     * Create new import log
     */
    public function createImportLog(array $data): int
    {
        $logData = array_merge([
            'status' => 'pending',
            'total_records' => 0,
            'processed_records' => 0,
            'inserted_records' => 0,
            'updated_records' => 0,
            'skipped_records' => 0,
            'error_records' => 0,
            'start_time' => date('Y-m-d H:i:s'),
            'settings' => json_encode($data['settings'] ?? [])
        ], $data);
        
        return $this->insert($logData);
    }
    
    /**
     * Update import log status
     */
    public function updateStatus(int $logId, string $status, array $data = []): bool
    {
        $updateData = array_merge(['status' => $status], $data);
        
        if ($status === 'completed' || $status === 'failed' || $status === 'cancelled') {
            $updateData['end_time'] = date('Y-m-d H:i:s');
            
            // Calculate duration if start_time exists
            $log = $this->find($logId);
            if ($log && $log['start_time']) {
                $startTime = strtotime($log['start_time']);
                $endTime = strtotime($updateData['end_time']);
                $updateData['duration'] = $endTime - $startTime;
            }
        }
        
        return $this->update($logId, $updateData);
    }
    
    /**
     * Update import progress
     */
    public function updateProgress(int $logId, array $progress): bool
    {
        $allowedFields = [
            'total_records',
            'processed_records',
            'inserted_records',
            'updated_records',
            'skipped_records',
            'error_records'
        ];
        
        $updateData = array_intersect_key($progress, array_flip($allowedFields));
        
        return $this->update($logId, $updateData);
    }
    
    /**
     * Get import logs with pagination
     */
    public function getImportLogs(int $limit = 20, int $offset = 0, array $filters = []): array
    {
        $builder = $this->builder();
        
        // Apply filters
        if (!empty($filters['import_type'])) {
            $builder->where('import_type', $filters['import_type']);
        }
        
        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }
        
        if (!empty($filters['start_date'])) {
            $builder->where('created_at >=', $filters['start_date']);
        }
        
        if (!empty($filters['end_date'])) {
            $builder->where('created_at <=', $filters['end_date']);
        }
        
        return $builder->orderBy('created_at', 'DESC')
                      ->limit($limit, $offset)
                      ->get()
                      ->getResultArray();
    }
    
    /**
     * Get import log statistics
     */
    public function getImportStats(int $days = 30): array
    {
        $startDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        try {
            $query = $this->db->query("SELECT 
                COUNT(*) as total_imports,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as successful_imports,
                COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_imports,
                COUNT(CASE WHEN status = 'running' THEN 1 END) as running_imports,
                SUM(inserted_records) as total_inserted,
                SUM(updated_records) as total_updated,
                SUM(skipped_records) as total_skipped,
                SUM(error_records) as total_errors,
                AVG(duration) as avg_duration
                FROM fingerprint_import_logs 
                WHERE created_at >= ?", [$startDate]);
            
            return $query->getRowArray();
        } catch (\Exception $e) {
            return [
                'total_imports' => 0,
                'successful_imports' => 0,
                'failed_imports' => 0,
                'running_imports' => 0,
                'total_inserted' => 0,
                'total_updated' => 0,
                'total_skipped' => 0,
                'total_errors' => 0,
                'avg_duration' => 0,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get running imports
     */
    public function getRunningImports(): array
    {
        return $this->builder()
                   ->where('status', 'running')
                   ->orderBy('created_at', 'DESC')
                   ->get()
                   ->getResultArray();
    }
    
    /**
     * Clean old logs
     */
    public function cleanOldLogs(int $retentionDays = 30): int
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$retentionDays} days"));
        
        return $this->builder()
                   ->where('created_at <', $cutoffDate)
                   ->where('status !=', 'running')
                   ->delete();
    }
    
    /**
     * Get latest import by type
     */
    public function getLatestImport(string $importType = null): ?array
    {
        $builder = $this->builder();
        
        if ($importType) {
            $builder->where('import_type', $importType);
        }
        
        $result = $builder->orderBy('created_at', 'DESC')
                         ->limit(1)
                         ->get()
                         ->getRowArray();
        
        return $result ?: null;
    }
    
    /**
     * Cancel running imports
     */
    public function cancelRunningImports(): int
    {
        return $this->builder()
                   ->where('status', 'running')
                   ->update(['status' => 'cancelled', 'end_time' => date('Y-m-d H:i:s')]);
    }
}
