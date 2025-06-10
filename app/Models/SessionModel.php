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
        'name',
        'start_date',
        'end_date',
        'status'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    protected $validationRules = [
        'name' => 'required|min_length[1]|max_length[100]',
        'start_date' => 'permit_empty|valid_date[Y-m-d]',
        'end_date' => 'permit_empty|valid_date[Y-m-d]',
        'status' => 'permit_empty|in_list[Active,Inactive]'
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Session name is required',
            'min_length' => 'Session name must be at least 1 character'
        ]
    ];
    
    /**
     * Get active session
     */
    public function getActiveSession()
    {
        return $this->where('status', 'Active')
                   ->orderBy('start_date', 'DESC')
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