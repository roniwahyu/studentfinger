<?php

namespace App\Controllers;

use App\Models\AttendanceModel;
use App\Models\StudentModel;

class DashboardController extends BaseController
{
    protected $attendanceModel;
    protected $studentModel;

    public function __construct()
    {
        $this->attendanceModel = new AttendanceModel();
        $this->studentModel = new StudentModel();
    }

    public function index()
    {
        $data['title'] = 'Dashboard';

        try {
            // Get real statistics with error handling
            $todaysSummary = $this->attendanceModel->getTodaysSummary();

            // Get total students count safely
            $data['totalStudents'] = $this->studentModel->countAll();

            // Calculate attendance statistics
            $data['presentToday'] = $todaysSummary['unique_users'] ?? 0;
            $data['absentToday'] = max(0, $data['totalStudents'] - $data['presentToday']);
            $data['lateToday'] = $todaysSummary['check_in'] ?? 0;

            // Get recent attendance from att_log
            $recentLogs = $this->attendanceModel->getRecentAttendance(10);
            $data['recentAttendance'] = [];

            foreach ($recentLogs as $log) {
                $data['recentAttendance'][] = [
                    'student_id' => $log['student_code'] ?? $log['pin'],
                    'name' => trim(($log['firstname'] ?? '') . ' ' . ($log['lastname'] ?? '')) ?: 'Student #' . ($log['pin'] ?? 'Unknown'),
                    'time_in' => date('H:i A', strtotime($log['scan_date'])),
                    'status' => $this->attendanceModel->getInOutModeLabel($log['inoutmode'] ?? 0),
                    'verify_mode' => $this->attendanceModel->getVerifyModeLabel($log['verifymode'] ?? 0)
                ];
            }
        } catch (\Exception $e) {
            // Handle database errors gracefully
            log_message('error', 'Dashboard error: ' . $e->getMessage());

            // Set default values
            $data['totalStudents'] = 0;
            $data['presentToday'] = 0;
            $data['absentToday'] = 0;
            $data['lateToday'] = 0;
            $data['recentAttendance'] = [];
        }

        return view('dashboard/index', $data);
    }
}