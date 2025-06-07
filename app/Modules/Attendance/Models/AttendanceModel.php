<?php

namespace App\Modules\Attendance\Models;

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
            'valid_date' => 'Invalid attendance date format'
        ],
        'status' => [
            'required' => 'Attendance status is required',
            'in_list' => 'Invalid attendance status'
        ],
        'attendance_type' => [
            'required' => 'Attendance type is required',
            'in_list' => 'Invalid attendance type'
        ]
    ];
    
    /**
     * Get today's attendance summary
     */
    public function getTodayAttendanceSummary()
    {
        $today = date('Y-m-d');
        
        $summary = [
            'total_students' => 0,
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'excused' => 0,
            'sick' => 0,
            'attendance_percentage' => 0
        ];
        
        // Get total active students
        $studentModel = new \App\Modules\StudentManagement\Models\StudentModel();
        $summary['total_students'] = $studentModel->where('status', 'Active')->countAllResults();
        
        // Get attendance counts by status
        $attendanceCounts = $this->select('status, COUNT(*) as count')
                                ->where('attendance_date', $today)
                                ->groupBy('status')
                                ->findAll();
        
        foreach ($attendanceCounts as $count) {
            $status = strtolower($count['status']);
            if (isset($summary[$status])) {
                $summary[$status] = $count['count'];
            }
        }
        
        // Calculate attendance percentage
        $totalMarked = array_sum(array_slice($summary, 1, 5)); // Exclude total_students and attendance_percentage
        if ($summary['total_students'] > 0) {
            $summary['attendance_percentage'] = round(($summary['present'] / $summary['total_students']) * 100, 2);
        }
        
        return $summary;
    }
    
    /**
     * Get attendance report for date range
     */
    public function getAttendanceReport($startDate, $endDate, $filters = [])
    {
        $builder = $this->select('attendance.*, students.name as student_name, students.student_id, 
                                 classes.name as class_name, sections.name as section_name')
                       ->join('students', 'attendance.student_id = students.id')
                       ->join('student_sessions', 'students.id = student_sessions.student_id')
                       ->join('class_sections', 'student_sessions.class_section_id = class_sections.id')
                       ->join('classes', 'class_sections.class_id = classes.id')
                       ->join('sections', 'class_sections.section_id = sections.id')
                       ->where('attendance.attendance_date >=', $startDate)
                       ->where('attendance.attendance_date <=', $endDate);
        
        // Apply filters
        if (!empty($filters['class_id'])) {
            $builder->where('classes.id', $filters['class_id']);
        }
        
        if (!empty($filters['section_id'])) {
            $builder->where('sections.id', $filters['section_id']);
        }
        
        if (!empty($filters['status'])) {
            $builder->where('attendance.status', $filters['status']);
        }
        
        if (!empty($filters['student_id'])) {
            $builder->where('attendance.student_id', $filters['student_id']);
        }
        
        return $builder->orderBy('attendance.attendance_date', 'DESC')
                      ->orderBy('students.name', 'ASC')
                      ->findAll();
    }
    
    /**
     * Get absent students for a specific date
     */
    public function getAbsentStudents($date)
    {
        // Get all active students
        $studentModel = new \App\Modules\StudentManagement\Models\StudentModel();
        $allStudents = $studentModel->select('students.id, students.name, students.student_id, 
                                            classes.name as class_name, sections.name as section_name')
                                   ->join('student_sessions', 'students.id = student_sessions.student_id')
                                   ->join('class_sections', 'student_sessions.class_section_id = class_sections.id')
                                   ->join('classes', 'class_sections.class_id = classes.id')
                                   ->join('sections', 'class_sections.section_id = sections.id')
                                   ->where('students.status', 'Active')
                                   ->findAll();
        
        // Get students who attended
        $presentStudents = $this->select('student_id')
                               ->where('attendance_date', $date)
                               ->whereIn('status', ['Present', 'Late'])
                               ->findColumn('student_id');
        
        // Filter absent students
        $absentStudents = [];
        foreach ($allStudents as $student) {
            if (!in_array($student['id'], $presentStudents)) {
                $absentStudents[] = $student;
            }
        }
        
        return $absentStudents;
    }
    
    /**
     * Get late arrivals for a specific date
     */
    public function getLateArrivals($date)
    {
        return $this->select('attendance.*, students.name as student_name, students.student_id, 
                            classes.name as class_name, sections.name as section_name')
                   ->join('students', 'attendance.student_id = students.id')
                   ->join('student_sessions', 'students.id = student_sessions.student_id')
                   ->join('class_sections', 'student_sessions.class_section_id = class_sections.id')
                   ->join('classes', 'class_sections.class_id = classes.id')
                   ->join('sections', 'class_sections.section_id = sections.id')
                   ->where('attendance.attendance_date', $date)
                   ->where('attendance.status', 'Late')
                   ->orderBy('attendance.late_minutes', 'DESC')
                   ->findAll();
    }
    
    /**
     * Get attendance statistics
     */
    public function getStatistics($filters = [])
    {
        $stats = [];
        
        // Date range
        $startDate = $filters['start_date'] ?? date('Y-m-01'); // First day of current month
        $endDate = $filters['end_date'] ?? date('Y-m-d'); // Today
        
        $builder = $this->builder();
        $builder->where('attendance_date >=', $startDate)
               ->where('attendance_date <=', $endDate);
        
        // Apply additional filters
        if (!empty($filters['class_id'])) {
            $builder->join('students', 'attendance.student_id = students.id')
                   ->join('student_sessions', 'students.id = student_sessions.student_id')
                   ->join('class_sections', 'student_sessions.class_section_id = class_sections.id')
                   ->where('class_sections.class_id', $filters['class_id']);
        }
        
        // Total attendance records
        $stats['total_records'] = $builder->countAllResults(false);
        
        // Attendance by status
        $statusStats = $builder->select('status, COUNT(*) as count')
                              ->groupBy('status')
                              ->get()
                              ->getResultArray();
        
        $stats['by_status'] = [];
        foreach ($statusStats as $stat) {
            $stats['by_status'][$stat['status']] = $stat['count'];
        }
        
        // Attendance by type
        $typeStats = $this->select('attendance_type, COUNT(*) as count')
                         ->where('attendance_date >=', $startDate)
                         ->where('attendance_date <=', $endDate)
                         ->groupBy('attendance_type')
                         ->findAll();
        
        $stats['by_type'] = [];
        foreach ($typeStats as $stat) {
            $stats['by_type'][$stat['attendance_type']] = $stat['count'];
        }
        
        // Daily attendance trend
        $dailyStats = $this->select('attendance_date, COUNT(*) as total, 
                                   SUM(CASE WHEN status = "Present" THEN 1 ELSE 0 END) as present,
                                   SUM(CASE WHEN status = "Absent" THEN 1 ELSE 0 END) as absent,
                                   SUM(CASE WHEN status = "Late" THEN 1 ELSE 0 END) as late')
                          ->where('attendance_date >=', $startDate)
                          ->where('attendance_date <=', $endDate)
                          ->groupBy('attendance_date')
                          ->orderBy('attendance_date', 'ASC')
                          ->findAll();
        
        $stats['daily_trend'] = $dailyStats;
        
        // Average attendance percentage
        $totalDays = count($dailyStats);
        if ($totalDays > 0) {
            $totalPresent = array_sum(array_column($dailyStats, 'present'));
            $totalRecords = array_sum(array_column($dailyStats, 'total'));
            $stats['average_attendance_percentage'] = $totalRecords > 0 ? round(($totalPresent / $totalRecords) * 100, 2) : 0;
        } else {
            $stats['average_attendance_percentage'] = 0;
        }
        
        return $stats;
    }
    
    /**
     * Get student attendance percentage
     */
    public function getStudentAttendancePercentage($studentId, $startDate = null, $endDate = null)
    {
        $startDate = $startDate ?: date('Y-m-01'); // First day of current month
        $endDate = $endDate ?: date('Y-m-d'); // Today
        
        $totalDays = $this->where('student_id', $studentId)
                         ->where('attendance_date >=', $startDate)
                         ->where('attendance_date <=', $endDate)
                         ->countAllResults();
        
        $presentDays = $this->where('student_id', $studentId)
                           ->where('attendance_date >=', $startDate)
                           ->where('attendance_date <=', $endDate)
                           ->whereIn('status', ['Present', 'Late'])
                           ->countAllResults();
        
        return $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 2) : 0;
    }
    
    /**
     * Check if attendance already marked for student on date
     */
    public function isAttendanceMarked($studentId, $date)
    {
        return $this->where('student_id', $studentId)
                   ->where('attendance_date', $date)
                   ->first() !== null;
    }
    
    /**
     * Get attendance by device
     */
    public function getAttendanceByDevice($deviceId, $startDate = null, $endDate = null)
    {
        $builder = $this->where('device_id', $deviceId);
        
        if ($startDate) {
            $builder->where('attendance_date >=', $startDate);
        }
        
        if ($endDate) {
            $builder->where('attendance_date <=', $endDate);
        }
        
        return $builder->orderBy('attendance_date', 'DESC')
                      ->orderBy('check_in_time', 'DESC')
                      ->findAll();
    }
    
    /**
     * Get monthly attendance summary for a student
     */
    public function getMonthlyAttendanceSummary($studentId, $year, $month)
    {
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));
        
        $attendance = $this->where('student_id', $studentId)
                          ->where('attendance_date >=', $startDate)
                          ->where('attendance_date <=', $endDate)
                          ->orderBy('attendance_date', 'ASC')
                          ->findAll();
        
        $summary = [
            'total_days' => count($attendance),
            'present_days' => 0,
            'absent_days' => 0,
            'late_days' => 0,
            'excused_days' => 0,
            'sick_days' => 0,
            'attendance_percentage' => 0,
            'daily_records' => []
        ];
        
        foreach ($attendance as $record) {
            $status = strtolower($record['status']);
            if ($status === 'present') {
                $summary['present_days']++;
            } elseif ($status === 'absent') {
                $summary['absent_days']++;
            } elseif ($status === 'late') {
                $summary['late_days']++;
                $summary['present_days']++; // Late is still considered present
            } elseif ($status === 'excused') {
                $summary['excused_days']++;
            } elseif ($status === 'sick') {
                $summary['sick_days']++;
            }
            
            $summary['daily_records'][$record['attendance_date']] = $record;
        }
        
        if ($summary['total_days'] > 0) {
            $summary['attendance_percentage'] = round(($summary['present_days'] / $summary['total_days']) * 100, 2);
        }
        
        return $summary;
    }
    
    /**
     * Get students with low attendance
     */
    public function getStudentsWithLowAttendance($threshold = 75, $startDate = null, $endDate = null)
    {
        $startDate = $startDate ?: date('Y-m-01');
        $endDate = $endDate ?: date('Y-m-d');
        
        $sql = "
            SELECT 
                s.id,
                s.name,
                s.student_id,
                c.name as class_name,
                sec.name as section_name,
                COUNT(a.id) as total_days,
                SUM(CASE WHEN a.status IN ('Present', 'Late') THEN 1 ELSE 0 END) as present_days,
                ROUND((SUM(CASE WHEN a.status IN ('Present', 'Late') THEN 1 ELSE 0 END) / COUNT(a.id)) * 100, 2) as attendance_percentage
            FROM students s
            LEFT JOIN attendance a ON s.id = a.student_id AND a.attendance_date BETWEEN ? AND ?
            LEFT JOIN student_sessions ss ON s.id = ss.student_id
            LEFT JOIN class_sections cs ON ss.class_section_id = cs.id
            LEFT JOIN classes c ON cs.class_id = c.id
            LEFT JOIN sections sec ON cs.section_id = sec.id
            WHERE s.status = 'Active'
            GROUP BY s.id
            HAVING attendance_percentage < ? OR attendance_percentage IS NULL
            ORDER BY attendance_percentage ASC
        ";
        
        return $this->db->query($sql, [$startDate, $endDate, $threshold])->getResultArray();
    }
}