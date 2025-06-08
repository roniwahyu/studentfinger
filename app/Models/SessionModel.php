<?php

namespace App\Models;

use CodeIgniter\Model;

class SessionModel extends Model
{
    protected $table = 'sessions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'session',
        'status'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    protected $validationRules = [
        'session' => 'required|min_length[1]|max_length[100]',
        'status' => 'permit_empty|in_list[0,1]'
    ];
    
    protected $validationMessages = [
        'session' => [
            'required' => 'Session name is required',
            'min_length' => 'Session name must be at least 1 character'
        ]
    ];
    
    /**
     * Get active session
     */
    public function getActiveSession()
    {
        return $this->where('is_active', 1)
                   ->where('status', 'Active')
                   ->first();
    }
    
    /**
     * Get active sessions
     */
    public function getActiveSessions()
    {
        return $this->where('status', 'Active')
                   ->orderBy('start_date', 'DESC')
                   ->findAll();
    }
    
    /**
     * Activate session
     */
    public function activateSession($sessionId)
    {
        // Deactivate all sessions first
        $this->set('is_active', 0)->update();
        
        // Activate the selected session
        return $this->update($sessionId, ['is_active' => 1]);
    }
}