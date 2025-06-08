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
        'is_active',
        'description',
        'status'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[100]|is_unique[sessions.name,id,{id}]',
        'start_date' => 'required|valid_date[Y-m-d]',
        'end_date' => 'required|valid_date[Y-m-d]',
        'status' => 'required|in_list[Active,Inactive,Completed]'
    ];
    
    protected $validationMessages = [
        'name' => [
            'required' => 'Session name is required',
            'is_unique' => 'Session name already exists'
        ],
        'start_date' => [
            'required' => 'Start date is required',
            'valid_date' => 'Please provide a valid start date'
        ],
        'end_date' => [
            'required' => 'End date is required',
            'valid_date' => 'Please provide a valid end date'
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Please select a valid status'
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