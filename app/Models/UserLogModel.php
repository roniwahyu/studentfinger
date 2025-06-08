<?php

namespace App\Models;

use CodeIgniter\Model;

class UserLogModel extends Model
{
    protected $table = 'user_log';
    protected $primaryKey = null; // No primary key in user_log table
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps = false;
    
    protected $allowedFields = [
        'login_id',
        'log_date',
        'module',
        'tipe_log',
        'nama_data',
        'log_note'
    ];
    
    protected $validationRules = [
        'login_id' => 'required|max_length[50]',
        'log_date' => 'required|valid_date[Y-m-d H:i:s]',
        'module' => 'required|integer|in_list[0,1,2,3,4,5]',
        'tipe_log' => 'required|integer|in_list[0,1,2,3]',
        'nama_data' => 'permit_empty|max_length[250]',
        'log_note' => 'required|max_length[300]'
    ];
    
    protected $validationMessages = [
        'login_id' => [
            'required' => 'Login ID is required',
            'max_length' => 'Login ID cannot exceed 50 characters'
        ],
        'log_date' => [
            'required' => 'Log date is required',
            'valid_date' => 'Please provide a valid date and time'
        ],
        'module' => [
            'required' => 'Module is required',
            'in_list' => 'Module must be 0-5 (Settings, Employee, Machine, Exception, Report, Process)'
        ],
        'tipe_log' => [
            'required' => 'Log type is required',
            'in_list' => 'Log type must be 0-3 (Add, Edit, Delete, Open Door)'
        ]
    ];

    /**
     * Get user logs with filters
     */
    public function getUserLogsWithFilters($filters = [])
    {
        $builder = $this->db->table($this->table);
            
        // Apply filters
        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('login_id', $filters['search'])
                ->orLike('nama_data', $filters['search'])
                ->orLike('log_note', $filters['search'])
                ->groupEnd();
        }
        
        if (!empty($filters['date_from'])) {
            $builder->where('DATE(log_date) >=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $builder->where('DATE(log_date) <=', $filters['date_to']);
        }
        
        if (!empty($filters['module'])) {
            $builder->where('module', $filters['module']);
        }
        
        if (!empty($filters['tipe_log'])) {
            $builder->where('tipe_log', $filters['tipe_log']);
        }
        
        if (!empty($filters['login_id'])) {
            $builder->where('login_id', $filters['login_id']);
        }
        
        return $builder->orderBy('log_date', 'DESC');
    }

    /**
     * Get module labels for display
     */
    public function getModuleLabel($module)
    {
        $labels = [
            0 => 'Settings',
            1 => 'Employee',
            2 => 'Machine',
            3 => 'Exception',
            4 => 'Report',
            5 => 'Process'
        ];
        
        return $labels[$module] ?? 'Unknown';
    }
    
    /**
     * Get log type labels for display
     */
    public function getLogTypeLabel($tipe_log)
    {
        $labels = [
            0 => 'Add',
            1 => 'Edit',
            2 => 'Delete',
            3 => 'Open Door'
        ];
        
        return $labels[$tipe_log] ?? 'Unknown';
    }

    /**
     * Get recent user logs
     */
    public function getRecentLogs($limit = 10)
    {
        return $this->orderBy('log_date', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Get daily log summary
     */
    public function getDailyLogSummary($date)
    {
        return $this->db->table($this->table)
            ->select('module, tipe_log, COUNT(*) as count')
            ->where('DATE(log_date)', $date)
            ->groupBy(['module', 'tipe_log'])
            ->get()
            ->getResultArray();
    }

    /**
     * Get user activity summary
     */
    public function getUserActivitySummary($dateFrom = null, $dateTo = null)
    {
        $builder = $this->db->table($this->table)
            ->select('login_id, COUNT(*) as activity_count')
            ->groupBy('login_id');
            
        if ($dateFrom) {
            $builder->where('DATE(log_date) >=', $dateFrom);
        }
        
        if ($dateTo) {
            $builder->where('DATE(log_date) <=', $dateTo);
        }
        
        return $builder->orderBy('activity_count', 'DESC')
                      ->get()
                      ->getResultArray();
    }

    /**
     * Log user activity
     */
    public function logActivity($loginId, $module, $tipeLog, $namaData, $logNote)
    {
        return $this->insert([
            'login_id' => $loginId,
            'log_date' => date('Y-m-d H:i:s'),
            'module' => $module,
            'tipe_log' => $tipeLog,
            'nama_data' => $namaData,
            'log_note' => $logNote
        ]);
    }
}
