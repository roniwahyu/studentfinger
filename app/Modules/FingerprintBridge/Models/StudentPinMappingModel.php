<?php

namespace App\Modules\FingerprintBridge\Models;

use CodeIgniter\Model;

/**
 * Student PIN Mapping Model
 * 
 * Handles mapping between fingerprint machine PINs and student IDs
 */
class StudentPinMappingModel extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'student_pin_mapping';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'pin',
        'student_id',
        'rfid_card',
        'is_active',
        'notes'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    protected $validationRules = [
        'pin' => 'required|max_length[32]|is_unique[student_pin_mapping.pin,id,{id}]',
        'student_id' => 'required|integer|is_unique[student_pin_mapping.student_id,id,{id}]',
        'rfid_card' => 'permit_empty|max_length[50]',
        'is_active' => 'permit_empty|in_list[0,1]',
        'notes' => 'permit_empty|max_length[255]'
    ];
    
    protected $validationMessages = [
        'pin' => [
            'required' => 'PIN is required',
            'max_length' => 'PIN cannot exceed 32 characters',
            'is_unique' => 'This PIN is already mapped to another student'
        ],
        'student_id' => [
            'required' => 'Student ID is required',
            'integer' => 'Student ID must be a valid integer',
            'is_unique' => 'This student is already mapped to another PIN'
        ]
    ];
    
    /**
     * Get student ID by PIN
     */
    public function getStudentIdByPin(string $pin): ?int
    {
        $result = $this->builder()
                      ->select('student_id')
                      ->where('pin', $pin)
                      ->where('is_active', 1)
                      ->get()
                      ->getRowArray();
        
        return $result ? (int)$result['student_id'] : null;
    }
    
    /**
     * Get PIN by student ID
     */
    public function getPinByStudentId(int $studentId): ?string
    {
        $result = $this->builder()
                      ->select('pin')
                      ->where('student_id', $studentId)
                      ->where('is_active', 1)
                      ->get()
                      ->getRowArray();
        
        return $result ? $result['pin'] : null;
    }
    
    /**
     * Get mapping with student information
     */
    public function getMappingsWithStudentInfo(int $limit = null, int $offset = 0): array
    {
        $builder = $this->builder();
        $builder->select('student_pin_mapping.*, students.firstname, students.lastname, students.student_id as student_number, students.email')
                ->join('students', 'students.id = student_pin_mapping.student_id', 'left')
                ->orderBy('student_pin_mapping.created_at', 'DESC');
        
        if ($limit !== null) {
            $builder->limit($limit, $offset);
        }
        
        return $builder->get()->getResultArray();
    }
    
    /**
     * Get unmapped PINs from attendance logs
     */
    public function getUnmappedPins(int $limit = 100): array
    {
        try {
            $query = $this->db->query("
                SELECT DISTINCT al.pin, COUNT(*) as usage_count, MAX(al.scan_date) as last_used
                FROM att_log al
                LEFT JOIN student_pin_mapping spm ON al.pin = spm.pin AND spm.deleted_at IS NULL
                WHERE spm.pin IS NULL
                AND al.deleted_at IS NULL
                GROUP BY al.pin
                ORDER BY usage_count DESC, last_used DESC
                LIMIT ?
            ", [$limit]);
            
            return $query->getResultArray();
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Get students without PIN mapping
     */
    public function getStudentsWithoutPin(int $limit = 100): array
    {
        try {
            $query = $this->db->query("
                SELECT s.id, s.student_id, s.firstname, s.lastname, s.email, s.rfid
                FROM students s
                LEFT JOIN student_pin_mapping spm ON s.id = spm.student_id AND spm.deleted_at IS NULL
                WHERE spm.student_id IS NULL
                AND s.deleted_at IS NULL
                ORDER BY s.firstname, s.lastname
                LIMIT ?
            ", [$limit]);
            
            return $query->getResultArray();
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Create mapping from RFID
     */
    public function createMappingFromRfid(): int
    {
        try {
            // Map students who have RFID cards that match PINs in attendance logs
            $query = $this->db->query("
                INSERT INTO student_pin_mapping (pin, student_id, rfid_card, is_active, notes, created_at, updated_at)
                SELECT DISTINCT al.pin, s.id, s.rfid, 1, 'Auto-mapped from RFID', NOW(), NOW()
                FROM att_log al
                INNER JOIN students s ON al.pin = s.rfid
                LEFT JOIN student_pin_mapping spm ON al.pin = spm.pin AND spm.deleted_at IS NULL
                WHERE spm.pin IS NULL
                AND s.deleted_at IS NULL
                AND al.deleted_at IS NULL
                AND s.rfid IS NOT NULL
                AND s.rfid != ''
            ");
            
            return $this->db->affectedRows();
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * Bulk create mappings
     */
    public function bulkCreateMappings(array $mappings): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        foreach ($mappings as $mapping) {
            try {
                if ($this->insert($mapping)) {
                    $results['success']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = [
                        'mapping' => $mapping,
                        'errors' => $this->errors()
                    ];
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'mapping' => $mapping,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Update attendance logs with student IDs
     */
    public function updateAttendanceLogsWithStudentIds(): int
    {
        try {
            $query = $this->db->query("
                UPDATE att_log al
                INNER JOIN student_pin_mapping spm ON al.pin = spm.pin AND spm.is_active = 1 AND spm.deleted_at IS NULL
                SET al.student_id = spm.student_id
                WHERE al.student_id IS NULL
                AND al.deleted_at IS NULL
            ");
            
            return $this->db->affectedRows();
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get mapping statistics
     */
    public function getMappingStats(): array
    {
        try {
            $query = $this->db->query("
                SELECT 
                    COUNT(*) as total_mappings,
                    COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_mappings,
                    COUNT(CASE WHEN is_active = 0 THEN 1 END) as inactive_mappings,
                    (SELECT COUNT(DISTINCT pin) FROM att_log WHERE deleted_at IS NULL) as total_pins_in_logs,
                    (SELECT COUNT(DISTINCT al.pin) 
                     FROM att_log al 
                     LEFT JOIN student_pin_mapping spm ON al.pin = spm.pin AND spm.deleted_at IS NULL
                     WHERE spm.pin IS NULL AND al.deleted_at IS NULL) as unmapped_pins,
                    (SELECT COUNT(*) 
                     FROM students s 
                     LEFT JOIN student_pin_mapping spm ON s.id = spm.student_id AND spm.deleted_at IS NULL
                     WHERE spm.student_id IS NULL AND s.deleted_at IS NULL) as students_without_pin
                FROM student_pin_mapping 
                WHERE deleted_at IS NULL
            ");
            
            return $query->getRowArray();
        } catch (\Exception $e) {
            return [
                'total_mappings' => 0,
                'active_mappings' => 0,
                'inactive_mappings' => 0,
                'total_pins_in_logs' => 0,
                'unmapped_pins' => 0,
                'students_without_pin' => 0,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Deactivate mapping
     */
    public function deactivateMapping(int $id): bool
    {
        return $this->update($id, ['is_active' => 0]);
    }
    
    /**
     * Activate mapping
     */
    public function activateMapping(int $id): bool
    {
        return $this->update($id, ['is_active' => 1]);
    }
}
