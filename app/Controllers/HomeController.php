<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\StudentModel;
use App\Models\AttendanceModel;
use App\Models\ClassModel;

class HomeController extends BaseController
{
    public function index(): string
    {
        $studentModel = new StudentModel();
        $attendanceModel = new AttendanceModel();
        $classModel = new ClassModel();

        // Get dashboard statistics
        $data = [
            'totalStudents' => $studentModel->where('status', 'Active')->countAllResults(),
            'totalClasses' => $classModel->countAllResults(), // Remove status filter as classes table doesn't have status column
            'presentToday' => $attendanceModel->where('DATE(scan_date)', date('Y-m-d'))
                                            ->whereIn('inoutmode', [0, 1]) // Check-in modes
                                            ->groupBy('pin')
                                            ->countAllResults(),
            'totalAttendanceToday' => $attendanceModel->where('DATE(scan_date)', date('Y-m-d'))
                                                    ->countAllResults(),
        ];

        // Calculate attendance statistics
        $data['absentToday'] = max(0, $data['totalStudents'] - $data['presentToday']);

        // Calculate percentages
        if ($data['totalStudents'] > 0) {
            $data['presentPercentage'] = round(($data['presentToday'] / $data['totalStudents']) * 100, 1);
            $data['absentPercentage'] = round(($data['absentToday'] / $data['totalStudents']) * 100, 1);
        } else {
            $data['presentPercentage'] = 0;
            $data['absentPercentage'] = 0;
        }

        // Get recent attendance records using the correct table structure
        $data['recentAttendance'] = $attendanceModel->select('att_log.*, CONCAT(students.firstname, " ", students.lastname) as student_name, students.student_id as student_code')
                                                   ->join('students', 'students.student_id = att_log.student_id', 'left')
                                                   ->where('DATE(att_log.scan_date)', date('Y-m-d'))
                                                   ->orderBy('att_log.scan_date', 'DESC')
                                                   ->limit(10)
                                                   ->findAll();

        return view('welcome_message', $data);
    }
}