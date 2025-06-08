<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceLogModel extends Model
{
    protected $table = 'att_log';
    protected $primaryKey = ['sn', 'scan_date', 'pin']; // Composite primary key
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
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

    // Dates
    protected $useTimestamps = false;

    // Validation
    protected $validationRules = [
        'sn' => 'required|max_length[30]',
        'scan_date' => 'required|valid_date[Y-m-d H:i:s]',
        'pin' => 'required|max_length[32]',
        'verifymode' => 'required|integer',
        'inoutmode' => 'permit_empty|integer',
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
            'valid_date' => 'Please provide a valid date and time'
        ],
        'pin' => [
            'required' => 'PIN is required',
            'max_length' => 'PIN cannot exceed 32 characters'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Get attendance logs with student information
     */
    public function getLogsWithStudents($filters = [])
    {
        $builder = $this->db->table($this->table)
            ->select('att_log.*, students.firstname, students.lastname, students.student_id as student_code')
            ->join('students', 'students.pin = att_log.pin', 'left');

        // Apply filters
        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('students.firstname', $filters['search'])
                ->orLike('students.lastname', $filters['search'])
                ->orLike('students.student_id', $filters['search'])
                ->orLike('att_log.pin', $filters['search'])
                ->orLike('att_log.sn', $filters['search'])
                ->groupEnd();
        }

        if (!empty($filters['date_from'])) {
            $builder->where('DATE(att_log.scan_date) >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('DATE(att_log.scan_date) <=', $filters['date_to']);
        }

        if (!empty($filters['verifymode'])) {
            $builder->where('att_log.verifymode', $filters['verifymode']);
        }

        if (!empty($filters['inoutmode'])) {
            $builder->where('att_log.inoutmode', $filters['inoutmode']);
        }

        if (!empty($filters['sn'])) {
            $builder->where('att_log.sn', $filters['sn']);
        }

        return $builder->orderBy('att_log.scan_date', 'DESC');
    }

    /**
     * Get verify mode labels for display
     */
    public function getVerifyModeLabel($verifymode)
    {
        $labels = [
            1 => 'Fingerprint',
            3 => 'RFID Card',
            20 => 'Face Recognition'
        ];

        return $labels[$verifymode] ?? 'Unknown';
    }

    /**
     * Get inoutmode labels for display
     */
    public function getInOutModeLabel($inoutmode)
    {
        $labels = [
            0 => 'Check In',
            1 => 'Check In',
            2 => 'Check Out',
            3 => 'Break Out',
            4 => 'Break In',
            5 => 'Overtime In',
            6 => 'Overtime Out'
        ];

        return $labels[$inoutmode] ?? 'Unknown';
    }
}