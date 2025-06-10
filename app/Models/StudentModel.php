<?php

namespace App\Models;

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
        'name',
        'email',
        'phone',
        'address',
        'date_of_birth',
        'gender',
        'rfid_card',
        'pin',
        'parent_name',
        'parent_phone',
        'parent_email',
        'emergency_contact',
        'blood_group',
        'medical_info',
        'admission_date',
        'class_id',
        'section_id',
        'session_id',
        'status',
        'notes',
        'photo'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    protected $validationRules = [
        'student_id' => 'required|is_unique[students.student_id,id,{id}]',
        'name' => 'required|min_length[3]|max_length[100]',
        'email' => 'permit_empty|valid_email|is_unique[students.email,id,{id}]',
        'phone' => 'permit_empty|max_length[20]',
        'date_of_birth' => 'permit_empty|valid_date[Y-m-d]',
        'gender' => 'required|in_list[Male,Female,Other]',
        'rfid_card' => 'permit_empty|max_length[50]|is_unique[students.rfid_card,id,{id}]',
        'pin' => 'permit_empty|max_length[32]|is_unique[students.pin,id,{id}]',
        'parent_phone' => 'permit_empty|max_length[20]',
        'class_id' => 'required|integer',
        'section_id' => 'required|integer',
        'session_id' => 'required|integer',
        'status' => 'permit_empty|in_list[Active,Inactive,Graduated,Transferred]'
    ];
    
    protected $validationMessages = [
        'student_id' => [
            'required' => 'Student ID is required',
            'is_unique' => 'Student ID already exists'
        ],
        'name' => [
            'required' => 'Student name is required',
            'min_length' => 'Student name must be at least 3 characters'
        ],
        'email' => [
            'valid_email' => 'Please enter a valid email address',
            'is_unique' => 'Email address already exists'
        ],
        'gender' => [
            'required' => 'Gender is required'
        ],
        'rfid_card' => [
            'is_unique' => 'RFID card already exists'
        ],
        'pin' => [
            'is_unique' => 'PIN already exists'
        ]
    ];
    
    /**
     * Get students with class, section, and session information
     */
    public function getStudentsWithDetails($filters = [])
    {
        $builder = $this->db->table($this->table)
            ->select('students.*, classes.name as class_name, sections.name as section_name, sessions.name as session_name')
            ->join('classes', 'classes.id = students.class_id', 'left')
            ->join('sections', 'sections.id = students.section_id', 'left')
            ->join('sessions', 'sessions.id = students.session_id', 'left')
            ->where('students.deleted_at', null);
            
        // Apply filters
        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('students.name', $filters['search'])
                ->orLike('students.student_id', $filters['search'])
                ->orLike('students.email', $filters['search'])
                ->groupEnd();
        }
        
        if (!empty($filters['class_id'])) {
            $builder->where('students.class_id', $filters['class_id']);
        }
        
        if (!empty($filters['section_id'])) {
            $builder->where('students.section_id', $filters['section_id']);
        }
        
        if (!empty($filters['session_id'])) {
            $builder->where('students.session_id', $filters['session_id']);
        }
        
        if (!empty($filters['status'])) {
            $builder->where('students.status', $filters['status']);
        }
        
        return $builder->orderBy('students.name', 'ASC');
    }
    
    /**
     * Get students by class
     */
    public function getStudentsByClass($classId, $sectionId = null)
    {
        $builder = $this->where('class_id', $classId)
                       ->where('status', 'Active');
                       
        if ($sectionId) {
            $builder->where('section_id', $sectionId);
        }
        
        return $builder->orderBy('name', 'ASC')->findAll();
    }
    
    /**
     * Get students by section
     */
    public function getStudentsBySection($sectionId)
    {
        return $this->where('section_id', $sectionId)
                   ->where('status', 'Active')
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get students by session
     */
    public function getStudentsBySession($sessionId)
    {
        return $this->where('session_id', $sessionId)
                   ->where('status', 'Active')
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }
    
    /**
     * Search students
     */
    public function searchStudents($query, $limit = 10)
    {
        return $this->groupStart()
                   ->like('name', $query)
                   ->orLike('student_id', $query)
                   ->orLike('email', $query)
                   ->groupEnd()
                   ->where('status', 'Active')
                   ->limit($limit)
                   ->findAll();
    }
    
    /**
     * Get student by RFID card
     */
    public function getStudentByRFID($rfidCard)
    {
        return $this->where('rfid_card', $rfidCard)
                   ->where('status', 'Active')
                   ->first();
    }
    
    /**
     * Get student statistics
     */
    public function getStudentStats()
    {
        $stats = [];
        
        // Total students
        $stats['total'] = $this->where('deleted_at', null)->countAllResults();
        
        // Active students
        $stats['active'] = $this->where('status', 'Active')
                               ->where('deleted_at', null)
                               ->countAllResults();
        
        // Students by gender
        $genderStats = $this->db->table($this->table)
            ->select('gender, COUNT(*) as count')
            ->where('deleted_at', null)
            ->where('status', 'Active')
            ->groupBy('gender')
            ->get()
            ->getResultArray();
            
        $stats['by_gender'] = [];
        foreach ($genderStats as $gender) {
            $stats['by_gender'][$gender['gender']] = $gender['count'];
        }
        
        // Students by class
        $classStats = $this->db->table($this->table)
            ->select('classes.name as class_name, COUNT(*) as count')
            ->join('classes', 'classes.id = students.class_id')
            ->where('students.deleted_at', null)
            ->where('students.status', 'Active')
            ->groupBy('students.class_id')
            ->get()
            ->getResultArray();
            
        $stats['by_class'] = $classStats;
        
        return $stats;
    }
    
    /**
     * Bulk import students
     */
    public function bulkImportStudents($studentsData)
    {
        return $this->insertBatch($studentsData);
    }
    
    /**
     * Get students for export
     */
    public function getStudentsForExport($filters = [])
    {
        $builder = $this->getStudentsWithDetails($filters);
        return $builder->get()->getResultArray();
    }
    
    /**
     * Update student status
     */
    public function updateStudentStatus($studentId, $status)
    {
        return $this->update($studentId, ['status' => $status]);
    }
    
    /**
     * Get recently added students
     */
    public function getRecentStudents($limit = 10)
    {
        return $this->select('students.*, classes.name as class_name, sections.name as section_name')
                   ->join('classes', 'classes.id = students.class_id', 'left')
                   ->join('sections', 'sections.id = students.section_id', 'left')
                   ->orderBy('students.created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }
    
    /**
     * Check if student ID exists
     */
    public function studentIdExists($studentId, $excludeId = null)
    {
        $builder = $this->where('student_id', $studentId);
        
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        
        return $builder->countAllResults() > 0;
    }
    
    /**
     * Check if RFID card exists
     */
    public function rfidCardExists($rfidCard, $excludeId = null)
    {
        $builder = $this->where('rfid_card', $rfidCard);
        
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        
        return $builder->countAllResults() > 0;
    }
}