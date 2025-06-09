<?php

namespace App\Modules\ClassroomNotifications\Models;

use CodeIgniter\Model;

/**
 * Class Session Model
 * 
 * Manages classroom sessions and their states
 */
class ClassSessionModel extends Model
{
    protected $table = 'class_sessions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'class_id',
        'session_name',
        'subject',
        'teacher_name',
        'start_time',
        'end_time',
        'break_duration',
        'status',
        'actual_start_time',
        'actual_break_time',
        'actual_resume_time',
        'actual_end_time',
        'total_students',
        'present_students',
        'notifications_sent',
        'session_date',
        'notes'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    protected $validationRules = [
        'class_id' => 'required|integer',
        'session_name' => 'required|min_length[3]|max_length[100]',
        'subject' => 'required|min_length[2]|max_length[50]',
        'teacher_name' => 'required|min_length[2]|max_length[100]',
        'start_time' => 'required',
        'end_time' => 'required',
        'session_date' => 'required|valid_date'
    ];
    
    protected $validationMessages = [
        'class_id' => [
            'required' => 'Class is required',
            'integer' => 'Invalid class selected'
        ],
        'session_name' => [
            'required' => 'Session name is required',
            'min_length' => 'Session name must be at least 3 characters',
            'max_length' => 'Session name cannot exceed 100 characters'
        ]
    ];
    
    // Session status constants
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_STARTED = 'started';
    const STATUS_BREAK = 'break';
    const STATUS_RESUMED = 'resumed';
    const STATUS_FINISHED = 'finished';
    const STATUS_CANCELLED = 'cancelled';
    
    /**
     * Get sessions with class information
     */
    public function getSessionsWithClass(int $limit = 20, int $offset = 0, array $filters = []): array
    {
        $builder = $this->builder();
        $builder->select('class_sessions.*, classes.class as class_name')
                ->join('classes', 'classes.id = class_sessions.class_id', 'left')
                ->orderBy('class_sessions.session_date', 'DESC')
                ->orderBy('class_sessions.start_time', 'DESC');
        
        // Apply filters
        if (!empty($filters['status'])) {
            $builder->where('class_sessions.status', $filters['status']);
        }
        
        if (!empty($filters['class_id'])) {
            $builder->where('class_sessions.class_id', $filters['class_id']);
        }
        
        if (!empty($filters['date_from'])) {
            $builder->where('class_sessions.session_date >=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $builder->where('class_sessions.session_date <=', $filters['date_to']);
        }
        
        if ($limit > 0) {
            $builder->limit($limit, $offset);
        }
        
        return $builder->get()->getResultArray();
    }
    
    /**
     * Get active sessions
     */
    public function getActiveSessions(): array
    {
        return $this->whereIn('status', [
            self::STATUS_STARTED,
            self::STATUS_BREAK,
            self::STATUS_RESUMED
        ])->findAll();
    }
    
    /**
     * Get today's sessions
     */
    public function getTodaySessions(): array
    {
        return $this->where('session_date', date('Y-m-d'))
                    ->orderBy('start_time', 'ASC')
                    ->findAll();
    }
    
    /**
     * Start a session
     */
    public function startSession(int $sessionId): bool
    {
        return $this->update($sessionId, [
            'status' => self::STATUS_STARTED,
            'actual_start_time' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Set session to break
     */
    public function breakSession(int $sessionId): bool
    {
        return $this->update($sessionId, [
            'status' => self::STATUS_BREAK,
            'actual_break_time' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Resume session from break
     */
    public function resumeSession(int $sessionId): bool
    {
        return $this->update($sessionId, [
            'status' => self::STATUS_RESUMED,
            'actual_resume_time' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Finish a session
     */
    public function finishSession(int $sessionId): bool
    {
        return $this->update($sessionId, [
            'status' => self::STATUS_FINISHED,
            'actual_end_time' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Update attendance count
     */
    public function updateAttendanceCount(int $sessionId, int $presentCount): bool
    {
        return $this->update($sessionId, [
            'present_students' => $presentCount
        ]);
    }
    
    /**
     * Increment notification count
     */
    public function incrementNotificationCount(int $sessionId, int $count = 1): bool
    {
        $session = $this->find($sessionId);
        if ($session) {
            return $this->update($sessionId, [
                'notifications_sent' => ($session['notifications_sent'] ?? 0) + $count
            ]);
        }
        return false;
    }
    
    /**
     * Get session statistics
     */
    public function getSessionStats(): array
    {
        $db = $this->db;
        
        $stats = [
            'total_sessions' => $this->countAllResults(),
            'today_sessions' => $this->where('session_date', date('Y-m-d'))->countAllResults(),
            'active_sessions' => $this->whereIn('status', [
                self::STATUS_STARTED,
                self::STATUS_BREAK,
                self::STATUS_RESUMED
            ])->countAllResults(),
            'completed_today' => $this->where('session_date', date('Y-m-d'))
                                     ->where('status', self::STATUS_FINISHED)
                                     ->countAllResults()
        ];
        
        // Reset builder for next queries
        $this->builder()->resetQuery();
        
        return $stats;
    }
    
    /**
     * Get upcoming sessions
     */
    public function getUpcomingSessions(int $limit = 5): array
    {
        return $this->select('class_sessions.*, classes.class as class_name')
                    ->join('classes', 'classes.id = class_sessions.class_id', 'left')
                    ->where('class_sessions.session_date >=', date('Y-m-d'))
                    ->where('class_sessions.status', self::STATUS_SCHEDULED)
                    ->orderBy('class_sessions.session_date', 'ASC')
                    ->orderBy('class_sessions.start_time', 'ASC')
                    ->limit($limit)
                    ->findAll();
    }
    
    /**
     * Check if session can be started
     */
    public function canStartSession(int $sessionId): bool
    {
        $session = $this->find($sessionId);
        return $session && $session['status'] === self::STATUS_SCHEDULED;
    }
    
    /**
     * Check if session can be set to break
     */
    public function canBreakSession(int $sessionId): bool
    {
        $session = $this->find($sessionId);
        return $session && in_array($session['status'], [self::STATUS_STARTED, self::STATUS_RESUMED]);
    }
    
    /**
     * Check if session can be resumed
     */
    public function canResumeSession(int $sessionId): bool
    {
        $session = $this->find($sessionId);
        return $session && $session['status'] === self::STATUS_BREAK;
    }
    
    /**
     * Check if session can be finished
     */
    public function canFinishSession(int $sessionId): bool
    {
        $session = $this->find($sessionId);
        return $session && in_array($session['status'], [
            self::STATUS_STARTED,
            self::STATUS_BREAK,
            self::STATUS_RESUMED
        ]);
    }
}
