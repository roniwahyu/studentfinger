<?php

namespace Modules\StudentManagement\Models;

use CodeIgniter\Model;

/**
 * Session Model
 * 
 * Handles all database operations related to academic sessions
 */
class SessionModel extends Model
{
    protected $table = 'sessions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'name',
        'start_date',
        'end_date',
        'is_active',
        'description',
        'academic_year'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[100]',
        'start_date' => 'required|valid_date',
        'end_date' => 'required|valid_date',
        'is_active' => 'permit_empty|in_list[0,1]',
        'academic_year' => 'permit_empty|max_length[20]'
    ];
    
    protected $validationMessages = [
        'name' => [
            'required' => 'Session name is required',
            'min_length' => 'Session name must be at least 3 characters'
        ],
        'start_date' => [
            'required' => 'Start date is required',
            'valid_date' => 'Please enter a valid start date'
        ],
        'end_date' => [
            'required' => 'End date is required',
            'valid_date' => 'Please enter a valid end date'
        ]
    ];
    
    protected $skipValidation = false;
    protected $cleanValidationRules = true;
    
    protected $allowCallbacks = true;
    protected $beforeInsert = ['validateDateRange', 'generateAcademicYear'];
    protected $beforeUpdate = ['validateDateRange', 'generateAcademicYear'];
    protected $afterInsert = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];
    
    /**
     * Validate that end date is after start date
     */
    protected function validateDateRange(array $data)
    {
        if (isset($data['data']['start_date']) && isset($data['data']['end_date'])) {
            $startDate = strtotime($data['data']['start_date']);
            $endDate = strtotime($data['data']['end_date']);
            
            if ($endDate <= $startDate) {
                throw new \InvalidArgumentException('End date must be after start date');
            }
        }
        
        return $data;
    }
    
    /**
     * Generate academic year from start and end dates
     */
    protected function generateAcademicYear(array $data)
    {
        if (isset($data['data']['start_date']) && isset($data['data']['end_date'])) {
            $startYear = date('Y', strtotime($data['data']['start_date']));
            $endYear = date('Y', strtotime($data['data']['end_date']));
            
            $data['data']['academic_year'] = $startYear . '-' . $endYear;
        }
        
        return $data;
    }
    
    /**
     * Get active session
     */
    public function getActiveSession()
    {
        return $this->where('is_active', 1)
                   ->orderBy('start_date', 'DESC')
                   ->first();
    }
    
    /**
     * Get current session (based on current date)
     */
    public function getCurrentSession()
    {
        $currentDate = date('Y-m-d');
        
        return $this->where('start_date <=', $currentDate)
                   ->where('end_date >=', $currentDate)
                   ->orderBy('start_date', 'DESC')
                   ->first();
    }
    
    /**
     * Get sessions with student count
     */
    public function getSessionsWithStudentCount()
    {
        return $this->db->table($this->table . ' s')
            ->select('s.*, COUNT(ss.student_id) as student_count')
            ->join('student_session ss', 's.id = ss.session_id', 'left')
            ->where('s.deleted_at', null)
            ->groupBy('s.id')
            ->orderBy('s.start_date', 'DESC')
            ->get()
            ->getResultArray();
    }
    
    /**
     * Activate a session (deactivate others)
     */
    public function activateSession(int $sessionId): bool
    {
        $this->db->transStart();
        
        // Deactivate all sessions
        $this->set('is_active', 0)->update();
        
        // Activate the selected session
        $this->update($sessionId, ['is_active' => 1]);
        
        $this->db->transComplete();
        
        return $this->db->transStatus();
    }
    
    /**
     * Get sessions by academic year
     */
    public function getByAcademicYear(string $academicYear)
    {
        return $this->where('academic_year', $academicYear)
                   ->orderBy('start_date', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get upcoming sessions
     */
    public function getUpcomingSessions()
    {
        $currentDate = date('Y-m-d');
        
        return $this->where('start_date >', $currentDate)
                   ->orderBy('start_date', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get past sessions
     */
    public function getPastSessions()
    {
        $currentDate = date('Y-m-d');
        
        return $this->where('end_date <', $currentDate)
                   ->orderBy('end_date', 'DESC')
                   ->findAll();
    }
    
    /**
     * Check if session name is unique
     */
    public function isNameUnique(string $name, int $excludeId = null): bool
    {
        $builder = $this->where('name', $name);
        
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        
        return $builder->countAllResults() === 0;
    }
    
    /**
     * Check if session dates overlap with existing sessions
     */
    public function hasDateOverlap(string $startDate, string $endDate, int $excludeId = null): bool
    {
        $builder = $this->groupStart()
            ->where('start_date <=', $endDate)
            ->where('end_date >=', $startDate)
            ->groupEnd();
        
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        
        return $builder->countAllResults() > 0;
    }
    
    /**
     * Get session statistics
     */
    public function getSessionStats(): array
    {
        $total = $this->countAll();
        $active = $this->where('is_active', 1)->countAllResults();
        $current = $this->getCurrentSession() ? 1 : 0;
        $upcoming = $this->getUpcomingSessions();
        
        return [
            'total' => $total,
            'active' => $active,
            'current' => $current,
            'upcoming' => count($upcoming)
        ];
    }
    
    /**
     * Get sessions for dropdown/select options
     */
    public function getForDropdown(): array
    {
        $sessions = $this->select('id, name')
                        ->orderBy('start_date', 'DESC')
                        ->findAll();
        
        $options = [];
        foreach ($sessions as $session) {
            $options[$session['id']] = $session['name'];
        }
        
        return $options;
    }
    
    /**
     * Get session duration in days
     */
    public function getSessionDuration(int $sessionId): int
    {
        $session = $this->find($sessionId);
        
        if (!$session) {
            return 0;
        }
        
        $startDate = strtotime($session['start_date']);
        $endDate = strtotime($session['end_date']);
        
        return ceil(($endDate - $startDate) / (60 * 60 * 24));
    }
    
    /**
     * Check if session is currently active (within date range)
     */
    public function isSessionCurrent(int $sessionId): bool
    {
        $session = $this->find($sessionId);
        
        if (!$session) {
            return false;
        }
        
        $currentDate = date('Y-m-d');
        
        return $currentDate >= $session['start_date'] && $currentDate <= $session['end_date'];
    }
}