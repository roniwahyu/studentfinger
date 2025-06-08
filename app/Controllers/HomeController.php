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
            'totalStudents' => $studentModel->where('status', 'active')->countAllResults(),
            'totalClasses' => $classModel->where('status', 'active')->countAllResults(),
            'presentToday' => $attendanceModel->where('date', date('Y-m-d'))
                                            ->where('status', 'present')
                                            ->countAllResults(),
            'absentToday' => $attendanceModel->where('date', date('Y-m-d'))
                                           ->where('status', 'absent')
                                           ->countAllResults(),
        ];
        
        // Calculate percentages
        $totalStudentsToday = $data['presentToday'] + $data['absentToday'];
        if ($totalStudentsToday > 0) {
            $data['presentPercentage'] = round(($data['presentToday'] / $totalStudentsToday) * 100, 1);
            $data['absentPercentage'] = round(($data['absentToday'] / $totalStudentsToday) * 100, 1);
        } else {
            $data['presentPercentage'] = 0;
            $data['absentPercentage'] = 0;
        }
        
        // Get recent attendance records
        $data['recentAttendance'] = $attendanceModel->select('attendance.*, students.name as student_name, classes.name as class_name')
                                                   ->join('students', 'students.id = attendance.student_id')
                                                   ->join('classes', 'classes.id = students.class_id')
                                                   ->where('attendance.date', date('Y-m-d'))
                                                   ->orderBy('attendance.time_in', 'DESC')
                                                   ->limit(10)
                                                   ->findAll();
        
        return view('welcome_message', $data);
    }
}