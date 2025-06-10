<?php

namespace App\Modules\WhatsAppAttendance\Models;

use CodeIgniter\Model;

/**
 * Attendance Log Model
 * 
 * Handles attendance log data in studentfinger.att_log table
 */
class AttendanceLogModel extends Model
{
    protected $table = 'att_log';
    protected $primaryKey = 'att_id';
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    
    protected $allowedFields = [
        'att_id',
        'pin',
        'scan_date',
        'verifymode',
        'status',
        'serialnumber',
        'student_id',
        'sn',
        'inoutmode',
        'reserved',
        'work_code'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    protected $validationRules = [
        'att_id' => 'required|max_length[50]',
        'pin' => 'required|integer',
        'scan_date' => 'required|valid_date',
        'verifymode' => 'permit_empty|integer',
        'status' => 'permit_empty|integer',
        'serialnumber' => 'permit_empty|max_length[50]',
        'student_id' => 'required|integer',
        'sn' => 'permit_empty|integer',
        'inoutmode' => 'permit_empty|integer',
        'reserved' => 'permit_empty|integer',
        'work_code' => 'permit_empty|integer'
    ];
    
    protected $validationMessages = [
        'att_id' => [
            'required' => 'Attendance ID is required',
            'max_length' => 'Attendance ID cannot exceed 50 characters'
        ],
        'pin' => [
            'required' => 'PIN is required',
            'integer' => 'PIN must be a valid integer'
        ],
        'scan_date' => [
            'required' => 'Scan date is required',
            'valid_date' => 'Scan date must be a valid date'
        ],
        'student_id' => [
            'required' => 'Student ID is required',
            'integer' => 'Student ID must be a valid integer'
        ]
    ];
    
    /**
     * Get attendance records by date range
     */
    public function getByDateRange($startDate, $endDate, $studentId = null)
    {
        $builder = $this->builder()
            ->where('scan_date >=', $startDate)
            ->where('scan_date <=', $endDate)
            ->orderBy('scan_date', 'DESC');
        
        if ($studentId) {
            $builder->where('student_id', $studentId);
        }
        
        return $builder->get()->getResultArray();
    }
    
    /**
     * Get attendance records by student
     */
    public function getByStudent($studentId, $limit = 100)
    {
        return $this->where('student_id', $studentId)
            ->orderBy('scan_date', 'DESC')
            ->limit($limit)
            ->findAll();
    }
    
    /**
     * Get today's attendance
     */
    public function getTodayAttendance($studentId = null)
    {
        $today = date('Y-m-d');
        
        $builder = $this->builder()
            ->where('DATE(scan_date)', $today)
            ->orderBy('scan_date', 'ASC');
        
        if ($studentId) {
            $builder->where('student_id', $studentId);
        }
        
        return $builder->get()->getResultArray();
    }
    
    /**
     * Get latest attendance record for student
     */
    public function getLatestByStudent($studentId)
    {
        return $this->where('student_id', $studentId)
            ->orderBy('scan_date', 'DESC')
            ->first();
    }
    
    /**
     * Check if record exists
     */
    public function recordExists($pin, $scanDate, $serialNumber)
    {
        return $this->where('pin', $pin)
            ->where('scan_date', $scanDate)
            ->where('serialnumber', $serialNumber)
            ->countAllResults() > 0;
    }
    
    /**
     * Get attendance summary by date
     */
    public function getAttendanceSummary($date)
    {
        $result = $this->select('status, COUNT(*) as count')
            ->where('DATE(scan_date)', $date)
            ->groupBy('status')
            ->get()
            ->getResultArray();
        
        $summary = [
            'present' => 0,
            'late' => 0,
            'absent' => 0,
            'total' => 0
        ];
        
        foreach ($result as $row) {
            switch ($row['status']) {
                case 1:
                    $summary['present'] = $row['count'];
                    break;
                case 2:
                    $summary['late'] = $row['count'];
                    break;
                case 0:
                    $summary['absent'] = $row['count'];
                    break;
            }
            $summary['total'] += $row['count'];
        }
        
        return $summary;
    }
    
    /**
     * Get attendance records for notification processing
     */
    public function getNewRecordsForNotification($lastProcessedTime = null)
    {
        $builder = $this->select('att_log.*, students.firstname, students.lastname, students.class_id, students.section_id')
            ->join('students', 'students.student_id = att_log.student_id', 'left')
            ->where('students.status', 'Active')
            ->orderBy('att_log.scan_date', 'ASC');
        
        if ($lastProcessedTime) {
            $builder->where('att_log.created_at >', $lastProcessedTime);
        } else {
            // Default to records from today
            $builder->where('DATE(att_log.scan_date)', date('Y-m-d'));
        }
        
        return $builder->get()->getResultArray();
    }
    
    /**
     * Get duplicate records
     */
    public function getDuplicateRecords($days = 7)
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        return $this->select('pin, scan_date, serialnumber, COUNT(*) as count')
            ->where('scan_date >=', $startDate)
            ->groupBy(['pin', 'scan_date', 'serialnumber'])
            ->having('COUNT(*) > 1')
            ->get()
            ->getResultArray();
    }
    
    /**
     * Clean old records
     */
    public function cleanOldRecords($days = 365)
    {
        $cutoffDate = date('Y-m-d', strtotime("-{$days} days"));
        
        return $this->where('scan_date <', $cutoffDate)
            ->delete();
    }
    
    /**
     * Get attendance statistics
     */
    public function getStatistics($days = 30)
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        $totalRecords = $this->where('scan_date >=', $startDate)
            ->countAllResults();
        
        $uniqueStudents = $this->select('DISTINCT student_id')
            ->where('scan_date >=', $startDate)
            ->countAllResults();
        
        $dailyAverage = $totalRecords / $days;
        
        return [
            'total_records' => $totalRecords,
            'unique_students' => $uniqueStudents,
            'daily_average' => round($dailyAverage, 2),
            'period_days' => $days
        ];
    }
}