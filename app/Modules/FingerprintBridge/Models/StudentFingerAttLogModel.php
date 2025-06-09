<?php

namespace App\Modules\FingerprintBridge\Models;

use CodeIgniter\Model;

/**
 * StudentFinger AttLog Model
 * 
 * Handles database operations for studentfinger.att_log table (destination database)
 */
class StudentFingerAttLogModel extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'att_log';
    protected $primaryKey = 'att_id';
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'att_id',
        'pin',
        'scan_date',
        'verifymode',
        'status',
        'serialnumber',
        'student_id',
        'sn',
        'inoutmode',
        'reserved',
        'work_code'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    protected $validationRules = [
        'att_id' => 'required|max_length[50]',
        'pin' => 'required|max_length[32]',
        'scan_date' => 'required|valid_date',
        'verifymode' => 'required|integer',
        'status' => 'permit_empty|integer',
        'serialnumber' => 'permit_empty|max_length[50]',
        'student_id' => 'permit_empty|integer',
        'sn' => 'required|max_length[30]',
        'inoutmode' => 'required|integer',
        'reserved' => 'permit_empty|integer',
        'work_code' => 'permit_empty|integer'
    ];
    
    protected $validationMessages = [
        'att_id' => [
            'required' => 'Attendance ID is required',
            'max_length' => 'Attendance ID cannot exceed 50 characters'
        ],
        'pin' => [
            'required' => 'PIN is required',
            'max_length' => 'PIN cannot exceed 32 characters'
        ],
        'scan_date' => [
            'required' => 'Scan date is required',
            'valid_date' => 'Scan date must be a valid date'
        ]
    ];
    
    /**
     * Check if attendance record exists
     */
    public function recordExists(string $sn, string $scanDate, string $pin): bool
    {
        return $this->builder()
                   ->where('sn', $sn)
                   ->where('scan_date', $scanDate)
                   ->where('pin', $pin)
                   ->countAllResults() > 0;
    }
    
    /**
     * Check if attendance ID exists
     */
    public function attIdExists(string $attId): bool
    {
        return $this->builder()
                   ->where('att_id', $attId)
                   ->countAllResults() > 0;
    }
    
    /**
     * Insert attendance record with duplicate check
     */
    public function insertWithDuplicateCheck(array $data, string $duplicateHandling = 'skip'): array
    {
        $result = [
            'success' => false,
            'action' => 'none',
            'message' => '',
            'data' => $data
        ];
        
        // Check for duplicate by composite key (sn, scan_date, pin)
        $exists = $this->recordExists($data['sn'], $data['scan_date'], $data['pin']);
        
        if ($exists) {
            switch ($duplicateHandling) {
                case 'skip':
                    $result['action'] = 'skipped';
                    $result['message'] = 'Record already exists, skipped';
                    $result['success'] = true;
                    break;
                    
                case 'update':
                    $updateResult = $this->builder()
                                        ->where('sn', $data['sn'])
                                        ->where('scan_date', $data['scan_date'])
                                        ->where('pin', $data['pin'])
                                        ->update($data);
                    
                    if ($updateResult) {
                        $result['success'] = true;
                        $result['action'] = 'updated';
                        $result['message'] = 'Record updated successfully';
                    } else {
                        $result['message'] = 'Failed to update existing record';
                    }
                    break;
                    
                case 'error':
                    $result['message'] = 'Duplicate record found';
                    break;
            }
        } else {
            // Generate unique att_id if not provided
            if (empty($data['att_id']) || $this->attIdExists($data['att_id'])) {
                $data['att_id'] = $this->generateUniqueAttId($data);
            }
            
            $insertResult = $this->insert($data);
            
            if ($insertResult) {
                $result['success'] = true;
                $result['action'] = 'inserted';
                $result['message'] = 'Record inserted successfully';
                $result['data'] = $data;
            } else {
                $result['message'] = 'Failed to insert record: ' . implode(', ', $this->errors());
            }
        }
        
        return $result;
    }
    
    /**
     * Generate unique attendance ID
     */
    protected function generateUniqueAttId(array $data): string
    {
        // Generate att_id based on sn + scan_date + pin
        $baseId = $data['sn'] . '_' . date('YmdHis', strtotime($data['scan_date'])) . '_' . $data['pin'];
        
        // If this ID already exists, append a counter
        $counter = 1;
        $attId = $baseId;
        
        while ($this->attIdExists($attId)) {
            $attId = $baseId . '_' . $counter;
            $counter++;
        }
        
        return $attId;
    }
    
    /**
     * Get attendance records by date range
     */
    public function getByDateRange(string $startDate, string $endDate, int $limit = null, int $offset = 0): array
    {
        $builder = $this->builder();
        $builder->where('scan_date >=', $startDate)
                ->where('scan_date <=', $endDate)
                ->orderBy('scan_date', 'DESC');
        
        if ($limit !== null) {
            $builder->limit($limit, $offset);
        }
        
        return $builder->get()->getResultArray();
    }
    
    /**
     * Get attendance records with student information
     */
    public function getWithStudentInfo(string $startDate = null, string $endDate = null, int $limit = null, int $offset = 0): array
    {
        $builder = $this->builder();
        $builder->select('att_log.*, students.firstname, students.lastname, students.student_id as student_number')
                ->join('students', 'students.id = att_log.student_id', 'left');
        
        if ($startDate) {
            $builder->where('att_log.scan_date >=', $startDate);
        }
        
        if ($endDate) {
            $builder->where('att_log.scan_date <=', $endDate);
        }
        
        $builder->orderBy('att_log.scan_date', 'DESC');
        
        if ($limit !== null) {
            $builder->limit($limit, $offset);
        }
        
        return $builder->get()->getResultArray();
    }
    
    /**
     * Count records by date range
     */
    public function countByDateRange(string $startDate, string $endDate): int
    {
        return $this->builder()
                   ->where('scan_date >=', $startDate)
                   ->where('scan_date <=', $endDate)
                   ->countAllResults();
    }
    
    /**
     * Get latest imported record
     */
    public function getLatestImported(): ?array
    {
        $result = $this->builder()
                      ->orderBy('created_at', 'DESC')
                      ->limit(1)
                      ->get()
                      ->getRowArray();
        
        return $result ?: null;
    }
    
    /**
     * Delete records by date range
     */
    public function deleteByDateRange(string $startDate, string $endDate): bool
    {
        return $this->builder()
                   ->where('scan_date >=', $startDate)
                   ->where('scan_date <=', $endDate)
                   ->delete();
    }
    
    /**
     * Get import statistics
     */
    public function getImportStats(): array
    {
        try {
            $query = $this->db->query("SELECT 
                COUNT(*) as total_records,
                COUNT(DISTINCT pin) as unique_pins,
                COUNT(DISTINCT sn) as unique_devices,
                MIN(scan_date) as earliest_scan,
                MAX(scan_date) as latest_scan,
                COUNT(CASE WHEN student_id IS NOT NULL THEN 1 END) as mapped_students,
                COUNT(CASE WHEN student_id IS NULL THEN 1 END) as unmapped_pins
                FROM att_log 
                WHERE deleted_at IS NULL");
            
            return $query->getRowArray();
        } catch (\Exception $e) {
            return [
                'total_records' => 0,
                'unique_pins' => 0,
                'unique_devices' => 0,
                'earliest_scan' => null,
                'latest_scan' => null,
                'mapped_students' => 0,
                'unmapped_pins' => 0,
                'error' => $e->getMessage()
            ];
        }
    }
}
