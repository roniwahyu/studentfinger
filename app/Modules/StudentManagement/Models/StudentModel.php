<?php

namespace Modules\StudentManagement\Models;

use CodeIgniter\Model;

/**
 * Student Model
 * 
 * Handles all database operations related to students
 */
class StudentModel extends Model
{
    protected $table = 'students';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'student_id',
        'admission_no',
        'firstname',
        'lastname',
        'mobileno',
        'email',
        'father_phone',
        'rfid',
        'status'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    protected $validationRules = [
        'student_id' => 'required|is_unique[students.student_id,id,{id}]',
        'admission_no' => 'permit_empty|max_length[50]',
        'firstname' => 'required|min_length[2]|max_length[100]',
        'lastname' => 'required|min_length[2]|max_length[100]',
        'email' => 'permit_empty|valid_email|is_unique[students.email,id,{id}]',
        'mobileno' => 'permit_empty|max_length[20]',
        'rfid' => 'permit_empty|max_length[32]|is_unique[students.rfid,id,{id}]',
        'father_phone' => 'permit_empty|max_length[20]',
        'status' => 'permit_empty|in_list[0,1]'
    ];
    
    protected $validationMessages = [
        'student_id' => [
            'required' => 'Student ID is required',
            'is_unique' => 'Student ID already exists'
        ],
        'firstname' => [
            'required' => 'First name is required',
            'min_length' => 'First name must be at least 2 characters'
        ],
        'lastname' => [
            'required' => 'Last name is required',
            'min_length' => 'Last name must be at least 2 characters'
        ],
        'email' => [
            'valid_email' => 'Please enter a valid email address'
        ],
        'rfid' => [
            'is_unique' => 'RFID card already exists'
        ]
    ];
    
    protected $skipValidation = false;
    protected $cleanValidationRules = true;
    
    protected $allowCallbacks = true;
    protected $beforeInsert = ['generateStudentId'];
    protected $beforeUpdate = [];
    protected $afterInsert = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];
    
    /**
     * Generate student ID if not provided
     */
    protected function generateStudentId(array $data)
    {
        if (empty($data['data']['student_id'])) {
            $year = date('Y');
            $month = date('m');
            
            // Get the last student ID for this year and month
            $lastStudent = $this->where('student_id LIKE', "STD{$year}{$month}%")
                               ->orderBy('id', 'DESC')
                               ->first();
            
            $increment = 1;
            if ($lastStudent) {
                $lastId = $lastStudent['student_id'];
                $lastIncrement = (int) substr($lastId, -4);
                $increment = $lastIncrement + 1;
            }
            
            $data['data']['student_id'] = sprintf('STD%s%s%04d', $year, $month, $increment);
        }
        
        return $data;
    }
    
    /**
     * Get students with their current session and class information
     */
    public function getStudentsWithDetails($limit = null, $offset = null)
    {
        $builder = $this->db->table($this->table . ' s')
            ->select('s.*, ss.session_id, sess.name as session_name, 
                     cs.class_id, c.name as class_name, 
                     cs.section_id, sec.name as section_name')
            ->join('student_session ss', 's.id = ss.student_id', 'left')
            ->join('sessions sess', 'ss.session_id = sess.id', 'left')
            ->join('class_sections cs', 's.id = cs.student_id', 'left')
            ->join('classes c', 'cs.class_id = c.id', 'left')
            ->join('sections sec', 'cs.section_id = sec.id', 'left')
            ->where('s.deleted_at', null)
            ->orderBy('s.name', 'ASC');
        
        if ($limit) {
            $builder->limit($limit, $offset);
        }
        
        return $builder->get()->getResultArray();
    }
    
    /**
     * Get student by RFID card
     */
    public function getByRfid(string $rfidCard)
    {
        return $this->where('rfid_card', $rfidCard)
                   ->where('status', 'Active')
                   ->first();
    }
    
    /**
     * Get student by student ID
     */
    public function getByStudentId(string $studentId)
    {
        return $this->where('student_id', $studentId)
                   ->where('status', 'Active')
                   ->first();
    }
    
    /**
     * Get students by class and section
     */
    public function getByClassSection(int $classId, int $sectionId)
    {
        return $this->db->table($this->table . ' s')
            ->select('s.*')
            ->join('class_sections cs', 's.id = cs.student_id')
            ->where('cs.class_id', $classId)
            ->where('cs.section_id', $sectionId)
            ->where('s.deleted_at', null)
            ->where('s.status', 'Active')
            ->orderBy('s.name', 'ASC')
            ->get()
            ->getResultArray();
    }
    
    /**
     * Get students by session
     */
    public function getBySession(int $sessionId)
    {
        return $this->db->table($this->table . ' s')
            ->select('s.*, c.name as class_name, sec.name as section_name')
            ->join('student_session ss', 's.id = ss.student_id')
            ->join('class_sections cs', 's.id = cs.student_id', 'left')
            ->join('classes c', 'cs.class_id = c.id', 'left')
            ->join('sections sec', 'cs.section_id = sec.id', 'left')
            ->where('ss.session_id', $sessionId)
            ->where('s.deleted_at', null)
            ->orderBy('s.name', 'ASC')
            ->get()
            ->getResultArray();
    }
    
    /**
     * Search students by name, student ID, or email
     */
    public function searchStudents(string $query, int $limit = 20)
    {
        return $this->groupStart()
                   ->like('name', $query)
                   ->orLike('student_id', $query)
                   ->orLike('email', $query)
                   ->groupEnd()
                   ->where('status', 'Active')
                   ->limit($limit)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get active students count
     */
    public function getActiveStudentsCount(int $sessionId = null): int
    {
        $builder = $this->where('status', 'Active');
        
        if ($sessionId) {
            $builder->join('student_session ss', 'students.id = ss.student_id')
                   ->where('ss.session_id', $sessionId);
        }
        
        return $builder->countAllResults();
    }
    
    /**
     * Get students statistics
     */
    public function getStudentsStats(): array
    {
        $total = $this->countAll();
        $active = $this->where('status', 'Active')->countAllResults();
        $inactive = $this->where('status', 'Inactive')->countAllResults();
        $graduated = $this->where('status', 'Graduated')->countAllResults();
        
        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'graduated' => $graduated
        ];
    }
    
    /**
     * Get students by class
     */
    public function getStudentsByClass(int $classId)
    {
        return $this->db->table($this->table . ' s')
            ->select('s.*, sec.name as section_name')
            ->join('class_sections cs', 's.id = cs.student_id')
            ->join('sections sec', 'cs.section_id = sec.id')
            ->where('cs.class_id', $classId)
            ->where('s.deleted_at', null)
            ->where('s.status', 'Active')
            ->orderBy('sec.name', 'ASC')
            ->orderBy('s.name', 'ASC')
            ->get()
            ->getResultArray();
    }
    
    /**
     * Get recent students (last 30 days)
     */
    public function getRecentStudents(int $limit = 10)
    {
        return $this->where('created_at >=', date('Y-m-d H:i:s', strtotime('-30 days')))
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }
    
    /**
     * Bulk update student status
     */
    public function bulkUpdateStatus(array $studentIds, string $status): bool
    {
        return $this->whereIn('id', $studentIds)
                   ->set('status', $status)
                   ->update();
    }
    
    /**
     * Check if RFID card is unique
     */
    public function isRfidUnique(string $rfidCard, int $excludeId = null): bool
    {
        $builder = $this->where('rfid_card', $rfidCard);
        
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        
        return $builder->countAllResults() === 0;
    }
}