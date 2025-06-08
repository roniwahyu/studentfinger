<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceModel extends Model
{
    protected $table = 'attendance';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    protected $allowedFields = [
        'student_id',
        'attendance_date',
        'check_in_time',
        'check_out_time',
        'status',
        'attendance_type',
        'device_id',
        'notes',
        'marked_by',
        'late_minutes',
        'early_departure_minutes'
    ];
    
    protected $validationRules = [
        'student_id' => 'required|integer|is_not_unique[students.id]',
        'attendance_date' => 'required|valid_date[Y-m-d]',
        'check_in_time' => 'permit_empty|valid_date[H:i:s]',
        'check_out_time' => 'permit_empty|valid_date[H:i:s]',
        'status' => 'required|in_list[Present,Absent,Late,Excused,Sick]',
        'attendance_type' => 'required|in_list[Fingerprint,RFID,Manual,Facial]',
        'device_id' => 'permit_empty|integer',
        'notes' => 'permit_empty|max_length[500]',
        'marked_by' => 'permit_empty|integer',
        'late_minutes' => 'permit_empty|integer|greater_than_equal_to[0]',
        'early_departure_minutes' => 'permit_empty|integer|greater_than_equal_to[0]'
    ];
    
    protected $validationMessages = [
        'student_id' => [
            'required' => 'Student ID is required',
            'integer' => 'Student ID must be a number',
            'is_not_unique' => 'Student does not exist'
        ],
        'attendance_date' => [
            'required' => 'Attendance date is required',
            'valid_date' => 'Please provide a valid date'
        ],
        'status' => [
            'required' => 'Attendance status is required',
            'in_list' => 'Please select a valid status'
        ],
        'attendance_type' => [
            'required' => 'Attendance type is required',
            'in_list' => 'Please select a valid attendance type'
        ]
    ];
    
    /**
     * Get attendance records with student information
     */
    public function getAttendanceWithStudents($filters = [])
    {
        $builder = $this->db->table($this->table)
            ->select('attendance.*, students.name as student_name, students.student_id as student_code, classes.name as class_name, sections.name as section_name')
            ->join('students', 'students.id = attendance.student_id')
            ->join('classes', 'classes.id = students.class_id', 'left')
            ->join('sections', 'sections.id = students.section_id', 'left')
            ->where('attendance.deleted_at', null);
            
        // Apply filters
        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('students.name', $filters['search'])
                ->orLike('students.student_id', $filters['search'])
                ->groupEnd();
        }
        
        if (!empty($filters['class_id'])) {
            $builder->where('students.class_id', $filters['class_id']);
        }
        
        if (!empty($filters['section_id'])) {
            $builder->where('students.section_id', $filters['section_id']);
        }
        
        if (!empty($filters['date_from'])) {
            $builder->where('attendance.attendance_date >=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $builder->where('attendance.attendance_date <=', $filters['date_to']);
        }
        
        if (!empty($filters['status'])) {
            $builder->where('attendance.status', $filters['status']);
        }
        
        return $builder->orderBy('attendance.attendance_date', 'DESC')
                      ->orderBy('attendance.created_at', 'DESC');
    }
    
    /**
     * Get attendance statistics
     */
    public function getAttendanceStats($dateFrom = null, $dateTo = null)
    {
        $builder = $this->db->table($this->table)
            ->select('status, COUNT(*) as count')
            ->where('deleted_at', null);
            
        if ($dateFrom) {
            $builder->where('attendance_date >=', $dateFrom);
        }
        
        if ($dateTo) {
            $builder->where('attendance_date <=', $dateTo);
        }
        
        return $builder->groupBy('status')->get()->getResultArray();
    }
    
    /**
     * Get daily attendance summary
     */
    public function getDailyAttendanceSummary($date)
    {
        return $this->db->table($this->table)
            ->select('status, COUNT(*) as count')
            ->where('attendance_date', $date)
            ->where('deleted_at', null)
            ->groupBy('status')
            ->get()
            ->getResultArray();
    }
    
    /**
     * Get student attendance history
     */
    public function getStudentAttendanceHistory($studentId, $limit = 30)
    {
        return $this->where('student_id', $studentId)
                   ->orderBy('attendance_date', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }
    
    /**
     * Check if attendance exists for student on date
     */
    public function attendanceExists($studentId, $date)
    {
        return $this->where('student_id', $studentId)
                   ->where('attendance_date', $date)
                   ->countAllResults() > 0;
    }
    
    /**
     * Get monthly attendance report
     */
    public function getMonthlyReport($year, $month, $classId = null, $sectionId = null)
    {
        $builder = $this->db->table($this->table)
            ->select('attendance.*, students.name as student_name, students.student_id as student_code')
            ->join('students', 'students.id = attendance.student_id')
            ->where('YEAR(attendance.attendance_date)', $year)
            ->where('MONTH(attendance.attendance_date)', $month)
            ->where('attendance.deleted_at', null);
            
        if ($classId) {
            $builder->where('students.class_id', $classId);
        }
        
        if ($sectionId) {
            $builder->where('students.section_id', $sectionId);
        }
        
        return $builder->orderBy('attendance.attendance_date', 'ASC')
                      ->get()
                      ->getResultArray();
    }
    
    /**
     * Bulk mark attendance
     */
    public function bulkMarkAttendance($attendanceData)
    {
        return $this->insertBatch($attendanceData);
    }
    
    /**
     * Get attendance by device
     */
    public function getAttendanceByDevice($deviceId, $dateFrom = null, $dateTo = null)
    {
        $builder = $this->where('device_id', $deviceId)
                       ->where('deleted_at', null);
                       
        if ($dateFrom) {
            $builder->where('attendance_date >=', $dateFrom);
        }
        
        if ($dateTo) {
            $builder->where('attendance_date <=', $dateTo);
        }
        
        return $builder->orderBy('attendance_date', 'DESC')
                      ->findAll();
    }
}