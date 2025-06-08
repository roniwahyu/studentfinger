<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AttendanceModel;
use App\Models\DeviceModel;
use App\Models\TimetableModel;
use App\Models\StudentModel;
use App\Models\ClassModel;
use App\Models\SectionModel;
use CodeIgniter\HTTP\ResponseInterface;

class AttendanceController extends BaseController
{
    protected $attendanceModel;
    protected $studentModel;
    protected $classModel;
    protected $sectionModel;
    protected $deviceModel;
    protected $timetableModel;
    
    public function __construct()
    {
        $this->attendanceModel = new AttendanceModel();
        $this->studentModel = new StudentModel();
        $this->classModel = new ClassModel();
        $this->sectionModel = new SectionModel();
        $this->deviceModel = new DeviceModel();
        $this->timetableModel = new TimetableModel();
    }
    
    /**
     * Display attendance records
     */
    public function index()
    {
        $request = service('request');
        $pager = service('pager');
        
        // Get filters from request
        $filters = [
            'search' => $request->getGet('search'),
            'class_id' => $request->getGet('class_id'),
            'section_id' => $request->getGet('section_id'),
            'date_from' => $request->getGet('date_from'),
            'date_to' => $request->getGet('date_to'),
            'status' => $request->getGet('status')
        ];
        
        // Get attendance records with pagination
        $attendanceRecords = $this->attendanceModel
            ->select('attendance.*, students.name as student_name, students.student_id as student_code')
            ->join('students', 'students.id = attendance.student_id')
            ->where('attendance.deleted_at', null);
            
        // Apply filters
        if (!empty($filters['search'])) {
            $attendanceRecords->groupStart()
                ->like('students.name', $filters['search'])
                ->orLike('students.student_id', $filters['search'])
                ->groupEnd();
        }
        
        if (!empty($filters['class_id'])) {
            $attendanceRecords->where('students.class_id', $filters['class_id']);
        }
        
        if (!empty($filters['section_id'])) {
            $attendanceRecords->where('students.section_id', $filters['section_id']);
        }
        
        if (!empty($filters['date_from'])) {
            $attendanceRecords->where('attendance.attendance_date >=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $attendanceRecords->where('attendance.attendance_date <=', $filters['date_to']);
        }
        
        if (!empty($filters['status'])) {
            $attendanceRecords->where('attendance.status', $filters['status']);
        }
        
        $data = [
            'attendance_records' => $attendanceRecords->paginate(25),
            'pager' => $this->attendanceModel->pager,
            'filters' => $filters,
            'classes' => $this->classModel->findAll(),
            'sections' => $this->sectionModel->findAll()
        ];
        
        return view('attendance/index', $data);
    }
    
    /**
     * Show form to mark attendance
     */
    public function mark()
    {
        $data = [
            'classes' => $this->classModel->findAll(),
            'sections' => $this->sectionModel->findAll(),
            'devices' => $this->deviceModel->where('status', 'active')->findAll()
        ];
        
        return view('attendance/mark', $data);
    }
    
    /**
     * Store attendance record
     */
    public function store()
    {
        $rules = [
            'student_id' => 'required|integer',
            'attendance_date' => 'required|valid_date[Y-m-d]',
            'status' => 'required|in_list[Present,Absent,Late,Excused,Sick]',
            'attendance_type' => 'required|in_list[Fingerprint,RFID,Manual,Facial]'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $data = [
            'student_id' => $this->request->getPost('student_id'),
            'attendance_date' => $this->request->getPost('attendance_date'),
            'check_in_time' => $this->request->getPost('check_in_time'),
            'check_out_time' => $this->request->getPost('check_out_time'),
            'status' => $this->request->getPost('status'),
            'attendance_type' => $this->request->getPost('attendance_type'),
            'device_id' => $this->request->getPost('device_id'),
            'notes' => $this->request->getPost('notes'),
            'marked_by' => session('user_id') ?? 1
        ];
        
        if ($this->attendanceModel->save($data)) {
            return redirect()->to('/attendance')->with('success', 'Attendance marked successfully');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to mark attendance');
        }
    }
    
    /**
     * Show attendance record
     */
    public function show($id)
    {
        $attendance = $this->attendanceModel
            ->select('attendance.*, students.name as student_name, students.student_id as student_code')
            ->join('students', 'students.id = attendance.student_id')
            ->find($id);
            
        if (!$attendance) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Attendance record not found');
        }
        
        return view('attendance/show', ['attendance' => $attendance]);
    }
    
    /**
     * Show edit form
     */
    public function edit($id)
    {
        $attendance = $this->attendanceModel->find($id);
        
        if (!$attendance) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Attendance record not found');
        }
        
        $data = [
            'attendance' => $attendance,
            'students' => $this->studentModel->findAll(),
            'devices' => $this->deviceModel->findAll()
        ];
        
        return view('attendance/edit', $data);
    }
    
    /**
     * Update attendance record
     */
    public function update($id)
    {
        $attendance = $this->attendanceModel->find($id);
        
        if (!$attendance) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Attendance record not found');
        }
        
        $rules = [
            'student_id' => 'required|integer',
            'attendance_date' => 'required|valid_date[Y-m-d]',
            'status' => 'required|in_list[Present,Absent,Late,Excused,Sick]',
            'attendance_type' => 'required|in_list[Fingerprint,RFID,Manual,Facial]'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $data = [
            'student_id' => $this->request->getPost('student_id'),
            'attendance_date' => $this->request->getPost('attendance_date'),
            'check_in_time' => $this->request->getPost('check_in_time'),
            'check_out_time' => $this->request->getPost('check_out_time'),
            'status' => $this->request->getPost('status'),
            'attendance_type' => $this->request->getPost('attendance_type'),
            'device_id' => $this->request->getPost('device_id'),
            'notes' => $this->request->getPost('notes')
        ];
        
        if ($this->attendanceModel->update($id, $data)) {
            return redirect()->to('/attendance')->with('success', 'Attendance updated successfully');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update attendance');
        }
    }
    
    /**
     * Delete attendance record
     */
    public function delete($id)
    {
        $attendance = $this->attendanceModel->find($id);
        
        if (!$attendance) {
            return $this->response->setJSON(['success' => false, 'message' => 'Attendance record not found']);
        }
        
        if ($this->attendanceModel->delete($id)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Attendance deleted successfully']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete attendance']);
        }
    }
}