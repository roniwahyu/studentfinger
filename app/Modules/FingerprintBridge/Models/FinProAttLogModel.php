<?php

namespace App\Modules\FingerprintBridge\Models;

use CodeIgniter\Model;

/**
 * FinPro AttLog Model
 * 
 * Handles database operations for fin_pro.att_log table (fingerspot machine database)
 */
class FinProAttLogModel extends Model
{
    protected $DBGroup = 'fin_pro';
    protected $table = 'att_log';
    protected $primaryKey = ['sn', 'scan_date', 'pin'];
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'sn',
        'scan_date',
        'pin',
        'verifymode',
        'inoutmode',
        'reserved',
        'work_code',
        'att_id'
    ];
    
    protected $useTimestamps = false;
    
    protected $validationRules = [
        'sn' => 'required|max_length[30]',
        'scan_date' => 'required|valid_date',
        'pin' => 'required|max_length[32]',
        'verifymode' => 'required|integer',
        'inoutmode' => 'required|integer',
        'reserved' => 'permit_empty|integer',
        'work_code' => 'permit_empty|integer',
        'att_id' => 'permit_empty|max_length[50]'
    ];
    
    protected $validationMessages = [
        'sn' => [
            'required' => 'Serial number is required',
            'max_length' => 'Serial number cannot exceed 30 characters'
        ],
        'scan_date' => [
            'required' => 'Scan date is required',
            'valid_date' => 'Scan date must be a valid date'
        ],
        'pin' => [
            'required' => 'PIN is required',
            'max_length' => 'PIN cannot exceed 32 characters'
        ]
    ];
    
    /**
     * Get attendance records by date range
     */
    public function getByDateRange(string $startDate, string $endDate, int $limit = null, int $offset = 0): array
    {
        $builder = $this->builder();
        $builder->where('scan_date >=', $startDate)
                ->where('scan_date <=', $endDate)
                ->orderBy('scan_date', 'ASC');
        
        if ($limit !== null) {
            $builder->limit($limit, $offset);
        }
        
        return $builder->get()->getResultArray();
    }
    
    /**
     * Get attendance records by PIN
     */
    public function getByPin(string $pin, string $startDate = null, string $endDate = null): array
    {
        $builder = $this->builder();
        $builder->where('pin', $pin);
        
        if ($startDate) {
            $builder->where('scan_date >=', $startDate);
        }
        
        if ($endDate) {
            $builder->where('scan_date <=', $endDate);
        }
        
        return $builder->orderBy('scan_date', 'ASC')->get()->getResultArray();
    }
    
    /**
     * Get attendance records by serial number
     */
    public function getBySerialNumber(string $sn, string $startDate = null, string $endDate = null): array
    {
        $builder = $this->builder();
        $builder->where('sn', $sn);
        
        if ($startDate) {
            $builder->where('scan_date >=', $startDate);
        }
        
        if ($endDate) {
            $builder->where('scan_date <=', $endDate);
        }
        
        return $builder->orderBy('scan_date', 'ASC')->get()->getResultArray();
    }
    
    /**
     * Get latest attendance record
     */
    public function getLatest(): ?array
    {
        $result = $this->builder()
                      ->orderBy('scan_date', 'DESC')
                      ->limit(1)
                      ->get()
                      ->getRowArray();
        
        return $result ?: null;
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
     * Get unique PINs
     */
    public function getUniquePins(): array
    {
        return $this->builder()
                   ->select('pin')
                   ->distinct()
                   ->orderBy('pin', 'ASC')
                   ->get()
                   ->getResultArray();
    }
    
    /**
     * Get unique serial numbers
     */
    public function getUniqueSerialNumbers(): array
    {
        return $this->builder()
                   ->select('sn')
                   ->distinct()
                   ->orderBy('sn', 'ASC')
                   ->get()
                   ->getResultArray();
    }
    
    /**
     * Test database connection
     */
    public function testConnection(): bool
    {
        try {
            $this->db->query('SELECT 1');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Get database info
     */
    public function getDatabaseInfo(): array
    {
        try {
            $query = $this->db->query("SELECT 
                COUNT(*) as total_records,
                MIN(scan_date) as earliest_date,
                MAX(scan_date) as latest_date,
                COUNT(DISTINCT pin) as unique_pins,
                COUNT(DISTINCT sn) as unique_devices
                FROM att_log");
            
            return $query->getRowArray();
        } catch (\Exception $e) {
            return [
                'total_records' => 0,
                'earliest_date' => null,
                'latest_date' => null,
                'unique_pins' => 0,
                'unique_devices' => 0,
                'error' => $e->getMessage()
            ];
        }
    }
}
