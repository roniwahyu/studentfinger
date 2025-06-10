<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceModel extends Model
{
    protected $table = 'att_log';
    protected $primaryKey = ['sn', 'scan_date', 'pin']; // Composite primary key
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps = false;

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
    
    /**
     * Get attendance records with student information
     */
    public function getAttendanceWithStudents($filters = [])
    {
        $builder = $this->db->table($this->table)
            ->select('att_log.*, CONCAT(students.firstname, " ", students.lastname) as name, students.student_id as student_code')
            ->join('students', 'students.student_id = att_log.student_id', 'left');

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
     * Get attendance statistics by inoutmode
     */
    public function getAttendanceStats($dateFrom = null, $dateTo = null)
    {
        $builder = $this->db->table($this->table)
            ->select('inoutmode, COUNT(*) as count');

        if ($dateFrom) {
            $builder->where('DATE(scan_date) >=', $dateFrom);
        }

        if ($dateTo) {
            $builder->where('DATE(scan_date) <=', $dateTo);
        }

        return $builder->groupBy('inoutmode')->get()->getResultArray();
    }

    /**
     * Get daily attendance summary
     */
    public function getDailyAttendanceSummary($date)
    {
        return $this->db->table($this->table)
            ->select('inoutmode, COUNT(*) as count')
            ->where('DATE(scan_date)', $date)
            ->groupBy('inoutmode')
            ->get()
            ->getResultArray();
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
     * Get student attendance history by PIN
     */
    public function getStudentAttendanceHistory($pin, $limit = 30)
    {
        return $this->where('pin', $pin)
                   ->orderBy('scan_date', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Check if attendance exists for PIN on date
     */
    public function attendanceExists($pin, $date)
    {
        return $this->where('pin', $pin)
                   ->where('DATE(scan_date)', $date)
                   ->countAllResults() > 0;
    }

    /**
     * Get recent attendance logs
     */
    public function getRecentAttendance($limit = 10)
    {
        return $this->select('att_log.*, CONCAT(students.firstname, " ", students.lastname) as name, students.student_id as student_code')
                   ->join('students', 'students.student_id = att_log.student_id', 'left')
                   ->orderBy('scan_date', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }
    
    /**
     * Get monthly attendance report
     */
    public function getMonthlyReport($year, $month)
    {
        $builder = $this->db->table($this->table)
            ->select('att_log.*, students.firstname, students.lastname, students.student_id as student_code')
            ->join('students', 'students.student_id = att_log.student_id', 'left')
            ->where('YEAR(att_log.scan_date)', $year)
            ->where('MONTH(att_log.scan_date)', $month);

        return $builder->orderBy('att_log.scan_date', 'ASC')
                      ->get()
                      ->getResultArray();
    }

    /**
     * Bulk insert attendance logs
     */
    public function bulkInsertLogs($attendanceData)
    {
        return $this->insertBatch($attendanceData);
    }

    /**
     * Get attendance by device serial number
     */
    public function getAttendanceByDevice($serialNumber, $dateFrom = null, $dateTo = null)
    {
        $builder = $this->where('sn', $serialNumber);

        if ($dateFrom) {
            $builder->where('DATE(scan_date) >=', $dateFrom);
        }

        if ($dateTo) {
            $builder->where('DATE(scan_date) <=', $dateTo);
        }

        return $builder->orderBy('scan_date', 'DESC')
                      ->findAll();
    }

    /**
     * Get today's attendance summary for dashboard
     */
    public function getTodaysSummary()
    {
        $today = date('Y-m-d');
        $stats = $this->getDailyAttendanceSummary($today);

        $summary = [
            'check_in' => 0,
            'check_out' => 0,
            'break_out' => 0,
            'break_in' => 0,
            'total' => 0,
            'unique_users' => 0
        ];

        foreach ($stats as $stat) {
            $summary['total'] += $stat['count'];
            switch ($stat['inoutmode']) {
                case 0:
                case 1:
                    $summary['check_in'] += $stat['count'];
                    break;
                case 2:
                    $summary['check_out'] += $stat['count'];
                    break;
                case 3:
                    $summary['break_out'] += $stat['count'];
                    break;
                case 4:
                    $summary['break_in'] += $stat['count'];
                    break;
            }
        }

        // Get unique users for today
        $summary['unique_users'] = $this->db->table($this->table)
            ->select('pin')
            ->where('DATE(scan_date)', $today)
            ->groupBy('pin')
            ->countAllResults();

        return $summary;
    }

    /**
     * Get weekly attendance summary
     */
    public function getWeeklySummary()
    {
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $weekEnd = date('Y-m-d', strtotime('sunday this week'));

        $stats = $this->db->table($this->table)
            ->select('inoutmode, COUNT(*) as count')
            ->where('DATE(scan_date) >=', $weekStart)
            ->where('DATE(scan_date) <=', $weekEnd)
            ->groupBy('inoutmode')
            ->get()
            ->getResultArray();

        $summary = [
            'check_in' => 0,
            'check_out' => 0,
            'break_out' => 0,
            'break_in' => 0,
            'total' => 0,
            'unique_users' => 0,
            'week_start' => $weekStart,
            'week_end' => $weekEnd
        ];

        foreach ($stats as $stat) {
            $summary['total'] += $stat['count'];
            switch ($stat['inoutmode']) {
                case 0:
                case 1:
                    $summary['check_in'] += $stat['count'];
                    break;
                case 2:
                    $summary['check_out'] += $stat['count'];
                    break;
                case 3:
                    $summary['break_out'] += $stat['count'];
                    break;
                case 4:
                    $summary['break_in'] += $stat['count'];
                    break;
            }
        }

        // Get unique users for this week
        $summary['unique_users'] = $this->db->table($this->table)
            ->select('pin')
            ->where('DATE(scan_date) >=', $weekStart)
            ->where('DATE(scan_date) <=', $weekEnd)
            ->groupBy('pin')
            ->countAllResults();

        return $summary;
    }

    /**
     * Get monthly attendance summary
     */
    public function getMonthlySummary()
    {
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');

        $stats = $this->db->table($this->table)
            ->select('inoutmode, COUNT(*) as count')
            ->where('DATE(scan_date) >=', $monthStart)
            ->where('DATE(scan_date) <=', $monthEnd)
            ->groupBy('inoutmode')
            ->get()
            ->getResultArray();

        $summary = [
            'check_in' => 0,
            'check_out' => 0,
            'break_out' => 0,
            'break_in' => 0,
            'total' => 0,
            'unique_users' => 0,
            'month_start' => $monthStart,
            'month_end' => $monthEnd
        ];

        foreach ($stats as $stat) {
            $summary['total'] += $stat['count'];
            switch ($stat['inoutmode']) {
                case 0:
                case 1:
                    $summary['check_in'] += $stat['count'];
                    break;
                case 2:
                    $summary['check_out'] += $stat['count'];
                    break;
                case 3:
                    $summary['break_out'] += $stat['count'];
                    break;
                case 4:
                    $summary['break_in'] += $stat['count'];
                    break;
            }
        }

        // Get unique users for this month
        $summary['unique_users'] = $this->db->table($this->table)
            ->select('pin')
            ->where('DATE(scan_date) >=', $monthStart)
            ->where('DATE(scan_date) <=', $monthEnd)
            ->groupBy('pin')
            ->countAllResults();

        return $summary;
    }

    /**
     * Get attendance trends for analytics
     */
    public function getAttendanceTrends($days = 30)
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));

        return $this->db->table($this->table)
            ->select('DATE(scan_date) as date, COUNT(*) as total_records, COUNT(DISTINCT pin) as unique_users')
            ->where('DATE(scan_date) >=', $startDate)
            ->groupBy('DATE(scan_date)')
            ->orderBy('date', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get present students today using FingerSpot data
     */
    public function getPresentStudentsToday()
    {
        return $this->db->table($this->table)
            ->select('att_log.pin, students.student_id, students.firstname, students.lastname')
            ->join('students', 'students.rfid = att_log.pin', 'left')
            ->where('DATE(scan_date)', date('Y-m-d'))
            ->whereIn('inoutmode', [0, 1]) // Check in modes
            ->groupBy('att_log.pin')
            ->get()
            ->getResultArray();
    }

    /**
     * Get late students today
     */
    public function getLateStudentsToday($lateThreshold = '08:00:00')
    {
        return $this->db->table($this->table)
            ->select('att_log.pin, students.student_id, students.firstname, students.lastname, MIN(scan_date) as arrival_time')
            ->join('students', 'students.rfid = att_log.pin', 'left')
            ->where('DATE(scan_date)', date('Y-m-d'))
            ->whereIn('inoutmode', [0, 1]) // Check in modes
            ->groupBy('att_log.pin')
            ->having('TIME(arrival_time) >', $lateThreshold)
            ->get()
            ->getResultArray();
    }

    /**
     * Import FingerSpot data
     */
    public function importFingerspotData($data)
    {
        try {
            // Validate required fields
            $requiredFields = ['sn', 'scan_date', 'pin', 'verifymode'];

            foreach ($data as $record) {
                foreach ($requiredFields as $field) {
                    if (!isset($record[$field])) {
                        throw new \Exception("Missing required field: {$field}");
                    }
                }

                // Set default values for optional fields
                $record['inoutmode'] = $record['inoutmode'] ?? 1;
                $record['reserved'] = $record['reserved'] ?? 0;
                $record['work_code'] = $record['work_code'] ?? 0;
                $record['att_id'] = $record['att_id'] ?? '0';
            }

            return $this->insertBatch($data);
        } catch (\Exception $e) {
            log_message('error', 'FingerSpot import failed: ' . $e->getMessage());
            return false;
        }
    }
}