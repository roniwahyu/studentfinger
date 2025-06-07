<?php

namespace App\Modules\Attendance\Controllers;

use App\Modules\Attendance\Models\AttendanceModel;
use App\Modules\Attendance\Models\DeviceModel;
use App\Modules\StudentManagement\Models\StudentModel;
use App\Modules\StudentManagement\Models\ClassModel;
use App\Modules\StudentManagement\Models\SectionModel;
use App\Modules\StudentManagement\Models\SessionModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

class Reports extends Controller
{
    protected $attendanceModel;
    protected $studentModel;
    protected $classModel;
    protected $sectionModel;
    protected $sessionModel;
    protected $deviceModel;
    
    public function __construct()
    {
        $this->attendanceModel = new AttendanceModel();
        $this->studentModel = new StudentModel();
        $this->classModel = new ClassModel();
        $this->sectionModel = new SectionModel();
        $this->sessionModel = new SessionModel();
        $this->deviceModel = new DeviceModel();
    }
    
    /**
     * Reports dashboard
     */
    public function index()
    {
        $data = [
            'title' => 'Attendance Reports',
            'classes' => $this->classModel->getActiveClasses(),
            'sections' => $this->sectionModel->getActiveSections(),
            'sessions' => $this->sessionModel->getActiveSessions(),
            'devices' => $this->deviceModel->getActiveDevices()
        ];
        
        return view('App\Modules\Attendance\Views\reports\index', $data);
    }
    
    /**
     * Daily attendance report
     */
    public function daily()
    {
        $request = service('request');
        
        $filters = [
            'date' => $request->getGet('date') ?? date('Y-m-d'),
            'class_id' => $request->getGet('class_id'),
            'section_id' => $request->getGet('section_id'),
            'session_id' => $request->getGet('session_id')
        ];
        
        // Get attendance data
        $builder = $this->attendanceModel->select('attendance.*, students.name as student_name, 
                                                  students.student_id, students.photo,
                                                  classes.name as class_name, sections.name as section_name')
                                        ->join('students', 'attendance.student_id = students.id')
                                        ->join('student_sessions', 'students.id = student_sessions.student_id')
                                        ->join('class_sections', 'student_sessions.class_section_id = class_sections.id')
                                        ->join('classes', 'class_sections.class_id = classes.id')
                                        ->join('sections', 'class_sections.section_id = sections.id')
                                        ->where('attendance.attendance_date', $filters['date']);
        
        if (!empty($filters['class_id'])) {
            $builder->where('classes.id', $filters['class_id']);
        }
        
        if (!empty($filters['section_id'])) {
            $builder->where('sections.id', $filters['section_id']);
        }
        
        if (!empty($filters['session_id'])) {
            $builder->where('student_sessions.session_id', $filters['session_id']);
        }
        
        $attendanceData = $builder->orderBy('classes.name', 'ASC')
                                ->orderBy('sections.name', 'ASC')
                                ->orderBy('students.name', 'ASC')
                                ->findAll();
        
        // Get summary statistics
        $summary = $this->attendanceModel->getDailyAttendanceSummary($filters['date'], $filters);
        
        $data = [
            'title' => 'Daily Attendance Report',
            'filters' => $filters,
            'attendanceData' => $attendanceData,
            'summary' => $summary,
            'classes' => $this->classModel->getActiveClasses(),
            'sections' => $this->sectionModel->getActiveSections(),
            'sessions' => $this->sessionModel->getActiveSessions()
        ];
        
        return view('App\Modules\Attendance\Views\reports\daily', $data);
    }
    
    /**
     * Weekly attendance report
     */
    public function weekly()
    {
        $request = service('request');
        
        $filters = [
            'week_start' => $request->getGet('week_start') ?? date('Y-m-d', strtotime('monday this week')),
            'class_id' => $request->getGet('class_id'),
            'section_id' => $request->getGet('section_id'),
            'session_id' => $request->getGet('session_id')
        ];
        
        $weekEnd = date('Y-m-d', strtotime($filters['week_start'] . ' +6 days'));
        
        // Get attendance data for the week
        $attendanceData = $this->attendanceModel->getAttendanceReport(
            $filters['week_start'],
            $weekEnd,
            $filters
        );
        
        // Get weekly summary
        $summary = $this->attendanceModel->getWeeklyAttendanceSummary(
            $filters['week_start'],
            $weekEnd,
            $filters
        );
        
        $data = [
            'title' => 'Weekly Attendance Report',
            'filters' => $filters,
            'weekEnd' => $weekEnd,
            'attendanceData' => $attendanceData,
            'summary' => $summary,
            'classes' => $this->classModel->getActiveClasses(),
            'sections' => $this->sectionModel->getActiveSections(),
            'sessions' => $this->sessionModel->getActiveSessions()
        ];
        
        return view('App\Modules\Attendance\Views\reports\weekly', $data);
    }
    
    /**
     * Monthly attendance report
     */
    public function monthly()
    {
        $request = service('request');
        
        $filters = [
            'month' => $request->getGet('month') ?? date('Y-m'),
            'class_id' => $request->getGet('class_id'),
            'section_id' => $request->getGet('section_id'),
            'session_id' => $request->getGet('session_id')
        ];
        
        $monthStart = $filters['month'] . '-01';
        $monthEnd = date('Y-m-t', strtotime($monthStart));
        
        // Get attendance data for the month
        $attendanceData = $this->attendanceModel->getMonthlyAttendanceReport(
            $monthStart,
            $monthEnd,
            $filters
        );
        
        // Get monthly summary
        $summary = $this->attendanceModel->getMonthlyAttendanceSummary(
            $monthStart,
            $monthEnd,
            $filters
        );
        
        $data = [
            'title' => 'Monthly Attendance Report',
            'filters' => $filters,
            'monthStart' => $monthStart,
            'monthEnd' => $monthEnd,
            'attendanceData' => $attendanceData,
            'summary' => $summary,
            'classes' => $this->classModel->getActiveClasses(),
            'sections' => $this->sectionModel->getActiveSections(),
            'sessions' => $this->sessionModel->getActiveSessions()
        ];
        
        return view('App\Modules\Attendance\Views\reports\monthly', $data);
    }
    
    /**
     * Student attendance report
     */
    public function student()
    {
        $request = service('request');
        
        $filters = [
            'student_id' => $request->getGet('student_id'),
            'date_from' => $request->getGet('date_from') ?? date('Y-m-01'),
            'date_to' => $request->getGet('date_to') ?? date('Y-m-d'),
            'session_id' => $request->getGet('session_id')
        ];
        
        $student = null;
        $attendanceData = [];
        $summary = [];
        
        if (!empty($filters['student_id'])) {
            // Get student details
            $student = $this->studentModel->select('students.*, classes.name as class_name, 
                                                   sections.name as section_name')
                                         ->join('student_sessions', 'students.id = student_sessions.student_id')
                                         ->join('class_sections', 'student_sessions.class_section_id = class_sections.id')
                                         ->join('classes', 'class_sections.class_id = classes.id')
                                         ->join('sections', 'class_sections.section_id = sections.id')
                                         ->where('students.id', $filters['student_id'])
                                         ->first();
            
            if ($student) {
                // Get attendance data
                $attendanceData = $this->attendanceModel->getStudentAttendanceReport(
                    $filters['student_id'],
                    $filters['date_from'],
                    $filters['date_to']
                );
                
                // Get attendance summary
                $summary = $this->attendanceModel->getStudentAttendanceSummary(
                    $filters['student_id'],
                    $filters['date_from'],
                    $filters['date_to']
                );
            }
        }
        
        $data = [
            'title' => 'Student Attendance Report',
            'filters' => $filters,
            'student' => $student,
            'attendanceData' => $attendanceData,
            'summary' => $summary,
            'students' => $this->studentModel->getStudentsForDropdown(),
            'sessions' => $this->sessionModel->getActiveSessions()
        ];
        
        return view('App\Modules\Attendance\Views\reports\student', $data);
    }
    
    /**
     * Class attendance report
     */
    public function class()
    {
        $request = service('request');
        
        $filters = [
            'class_id' => $request->getGet('class_id'),
            'section_id' => $request->getGet('section_id'),
            'date_from' => $request->getGet('date_from') ?? date('Y-m-01'),
            'date_to' => $request->getGet('date_to') ?? date('Y-m-d'),
            'session_id' => $request->getGet('session_id')
        ];
        
        $attendanceData = [];
        $summary = [];
        
        if (!empty($filters['class_id'])) {
            // Get attendance data
            $attendanceData = $this->attendanceModel->getClassAttendanceReport(
                $filters['class_id'],
                $filters['section_id'],
                $filters['date_from'],
                $filters['date_to'],
                $filters['session_id']
            );
            
            // Get class summary
            $summary = $this->attendanceModel->getClassAttendanceSummary(
                $filters['class_id'],
                $filters['section_id'],
                $filters['date_from'],
                $filters['date_to'],
                $filters['session_id']
            );
        }
        
        $data = [
            'title' => 'Class Attendance Report',
            'filters' => $filters,
            'attendanceData' => $attendanceData,
            'summary' => $summary,
            'classes' => $this->classModel->getActiveClasses(),
            'sections' => $this->sectionModel->getActiveSections(),
            'sessions' => $this->sessionModel->getActiveSessions()
        ];
        
        return view('App\Modules\Attendance\Views\reports\class', $data);
    }
    
    /**
     * Summary report
     */
    public function summary()
    {
        $request = service('request');
        
        $filters = [
            'date_from' => $request->getGet('date_from') ?? date('Y-m-01'),
            'date_to' => $request->getGet('date_to') ?? date('Y-m-d'),
            'session_id' => $request->getGet('session_id')
        ];
        
        // Get overall summary
        $overallSummary = $this->attendanceModel->getOverallAttendanceSummary(
            $filters['date_from'],
            $filters['date_to'],
            $filters['session_id']
        );
        
        // Get class-wise summary
        $classSummary = $this->attendanceModel->getClassWiseAttendanceSummary(
            $filters['date_from'],
            $filters['date_to'],
            $filters['session_id']
        );
        
        // Get daily trends
        $dailyTrends = $this->attendanceModel->getDailyAttendanceTrends(
            $filters['date_from'],
            $filters['date_to'],
            $filters['session_id']
        );
        
        // Get device usage statistics
        $deviceStats = $this->attendanceModel->getDeviceUsageStats(
            $filters['date_from'],
            $filters['date_to']
        );
        
        $data = [
            'title' => 'Attendance Summary Report',
            'filters' => $filters,
            'overallSummary' => $overallSummary,
            'classSummary' => $classSummary,
            'dailyTrends' => $dailyTrends,
            'deviceStats' => $deviceStats,
            'sessions' => $this->sessionModel->getActiveSessions()
        ];
        
        return view('App\Modules\Attendance\Views\reports\summary', $data);
    }
    
    /**
     * Generate custom report
     */
    public function generate()
    {
        $request = service('request');
        
        if ($request->getMethod() === 'post') {
            $reportType = $request->getPost('report_type');
            $filters = $request->getPost();
            
            try {
                $reportService = new \App\Modules\Attendance\Services\ReportGeneratorService();
                $result = $reportService->generateReport($reportType, $filters);
                
                if ($result['success']) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Report generated successfully',
                        'download_url' => $result['download_url']
                    ]);
                } else {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => $result['message']
                    ]);
                }
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error generating report: ' . $e->getMessage()
                ]);
            }
        }
        
        $data = [
            'title' => 'Generate Custom Report',
            'classes' => $this->classModel->getActiveClasses(),
            'sections' => $this->sectionModel->getActiveSections(),
            'sessions' => $this->sessionModel->getActiveSessions(),
            'devices' => $this->deviceModel->getActiveDevices(),
            'reportTypes' => [
                'daily' => 'Daily Attendance',
                'weekly' => 'Weekly Attendance',
                'monthly' => 'Monthly Attendance',
                'student' => 'Student Report',
                'class' => 'Class Report',
                'summary' => 'Summary Report',
                'absent_students' => 'Absent Students',
                'late_arrivals' => 'Late Arrivals',
                'device_usage' => 'Device Usage',
                'attendance_trends' => 'Attendance Trends'
            ],
            'formats' => [
                'pdf' => 'PDF',
                'excel' => 'Excel',
                'csv' => 'CSV'
            ]
        ];
        
        return view('App\Modules\Attendance\Views\reports\generate', $data);
    }
    
    /**
     * Download report
     */
    public function download($reportId)
    {
        try {
            $reportService = new \App\Modules\Attendance\Services\ReportGeneratorService();
            $result = $reportService->downloadReport($reportId);
            
            if ($result['success']) {
                return $this->response->download($result['file_path'], null)
                                     ->setFileName($result['filename']);
            } else {
                return redirect()->back()->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Download failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Absent students report
     */
    public function absentStudents()
    {
        $request = service('request');
        
        $filters = [
            'date' => $request->getGet('date') ?? date('Y-m-d'),
            'class_id' => $request->getGet('class_id'),
            'section_id' => $request->getGet('section_id'),
            'session_id' => $request->getGet('session_id')
        ];
        
        // Get absent students
        $absentStudents = $this->attendanceModel->getAbsentStudents(
            $filters['date'],
            $filters['class_id'],
            $filters['section_id'],
            $filters['session_id']
        );
        
        $data = [
            'title' => 'Absent Students Report',
            'filters' => $filters,
            'absentStudents' => $absentStudents,
            'classes' => $this->classModel->getActiveClasses(),
            'sections' => $this->sectionModel->getActiveSections(),
            'sessions' => $this->sessionModel->getActiveSessions()
        ];
        
        return view('App\Modules\Attendance\Views\reports\absent_students', $data);
    }
    
    /**
     * Late arrivals report
     */
    public function lateArrivals()
    {
        $request = service('request');
        
        $filters = [
            'date' => $request->getGet('date') ?? date('Y-m-d'),
            'class_id' => $request->getGet('class_id'),
            'section_id' => $request->getGet('section_id'),
            'session_id' => $request->getGet('session_id')
        ];
        
        // Get late arrivals
        $lateArrivals = $this->attendanceModel->getLateArrivals(
            $filters['date'],
            $filters['class_id'],
            $filters['section_id'],
            $filters['session_id']
        );
        
        $data = [
            'title' => 'Late Arrivals Report',
            'filters' => $filters,
            'lateArrivals' => $lateArrivals,
            'classes' => $this->classModel->getActiveClasses(),
            'sections' => $this->sectionModel->getActiveSections(),
            'sessions' => $this->sessionModel->getActiveSessions()
        ];
        
        return view('App\Modules\Attendance\Views\reports\late_arrivals', $data);
    }
    
    /**
     * Export report data
     */
    public function export()
    {
        $request = service('request');
        
        $reportType = $request->getGet('type');
        $format = $request->getGet('format') ?? 'csv';
        $filters = $request->getGet();
        
        try {
            $exportService = new \App\Modules\Attendance\Services\ReportExportService();
            $result = $exportService->exportReport($reportType, $format, $filters);
            
            if ($result['success']) {
                return $this->response->download($result['file_path'], null)
                                     ->setFileName($result['filename']);
            } else {
                return redirect()->back()->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Print report
     */
    public function print($reportType)
    {
        $request = service('request');
        $filters = $request->getGet();
        
        // Set print-friendly layout
        $data = [
            'title' => ucfirst($reportType) . ' Attendance Report',
            'filters' => $filters,
            'printMode' => true
        ];
        
        // Get report data based on type
        switch ($reportType) {
            case 'daily':
                $data['attendanceData'] = $this->attendanceModel->getDailyAttendanceReport(
                    $filters['date'] ?? date('Y-m-d'),
                    $filters
                );
                break;
            case 'weekly':
                $weekStart = $filters['week_start'] ?? date('Y-m-d', strtotime('monday this week'));
                $weekEnd = date('Y-m-d', strtotime($weekStart . ' +6 days'));
                $data['attendanceData'] = $this->attendanceModel->getAttendanceReport(
                    $weekStart,
                    $weekEnd,
                    $filters
                );
                break;
            case 'monthly':
                $monthStart = ($filters['month'] ?? date('Y-m')) . '-01';
                $monthEnd = date('Y-m-t', strtotime($monthStart));
                $data['attendanceData'] = $this->attendanceModel->getMonthlyAttendanceReport(
                    $monthStart,
                    $monthEnd,
                    $filters
                );
                break;
            default:
                throw new \InvalidArgumentException('Invalid report type');
        }
        
        return view('App\Modules\Attendance\Views\reports\print', $data);
    }
}