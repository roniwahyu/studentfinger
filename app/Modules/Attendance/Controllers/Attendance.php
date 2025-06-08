<?php

namespace Modules\Attendance\Controllers;

use App\Libraries\BaseModule;
use App\Modules\Attendance\Models\AttendanceModel;
use App\Modules\Attendance\Models\DeviceModel;
use App\Modules\Attendance\Models\TimetableModel;
use Modules\StudentManagement\Models\StudentModel;
use App\Modules\StudentManagement\Models\ClassModel;
use App\Modules\StudentManagement\Models\SectionModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

class Attendance extends Controller
{
    protected $attendanceModel;
    protected $studentModel;
    protected $classModel;
    protected $sectionModel;
    protected $deviceModel;
    protected $timetableModel;
    protected $attendanceModule;
    
    public function __construct()
    {
        $this->attendanceModel = new AttendanceModel();
        $this->studentModel = new StudentModel();
        $this->classModel = new ClassModel();
        $this->sectionModel = new SectionModel();
        $this->deviceModel = new DeviceModel();
        $this->timetableModel = new TimetableModel();
        $this->attendanceModule = new \App\Modules\Attendance\AttendanceModule();
        
        // Initialize the module
        $this->attendanceModule->initialize();
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
            'status' => $request->getGet('status'),
            'attendance_type' => $request->getGet('attendance_type'),
            'date_from' => $request->getGet('date_from'),
            'date_to' => $request->getGet('date_to')
        ];
        
        // Build query
        $builder = $this->attendanceModel->select('attendance.*, students.name as student_name, 
                                                  students.student_id, classes.name as class_name, 
                                                  sections.name as section_name')
                                        ->join('students', 'attendance.student_id = students.id')
                                        ->join('student_sessions', 'students.id = student_sessions.student_id')
                                        ->join('class_sections', 'student_sessions.class_section_id = class_sections.id')
                                        ->join('classes', 'class_sections.class_id = classes.id')
                                        ->join('sections', 'class_sections.section_id = sections.id');
        
        // Apply filters
        if (!empty($filters['search'])) {
            $builder->groupStart()
                   ->like('students.name', $filters['search'])
                   ->orLike('students.student_id', $filters['search'])
                   ->groupEnd();
        }
        
        if (!empty($filters['class_id'])) {
            $builder->where('classes.id', $filters['class_id']);
        }
        
        if (!empty($filters['section_id'])) {
            $builder->where('sections.id', $filters['section_id']);
        }
        
        if (!empty($filters['status'])) {
            $builder->where('attendance.status', $filters['status']);
        }
        
        if (!empty($filters['attendance_type'])) {
            $builder->where('attendance.attendance_type', $filters['attendance_type']);
        }
        
        if (!empty($filters['date_from'])) {
            $builder->where('attendance.attendance_date >=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $builder->where('attendance.attendance_date <=', $filters['date_to']);
        }
        
        // Get paginated results
        $attendance = $builder->orderBy('attendance.attendance_date', 'DESC')
                            ->orderBy('attendance.check_in_time', 'DESC')
                            ->paginate(20);
        
        // Get filter options
        $classes = $this->classModel->getActiveClasses();
        $sections = $this->sectionModel->getActiveSections();
        
        // Get today's summary
        $todaySummary = $this->attendanceModule->getTodayAttendanceSummary();
        
        $data = [
            'title' => 'Attendance Records',
            'attendance' => $attendance,
            'pager' => $this->attendanceModel->pager,
            'filters' => $filters,
            'classes' => $classes,
            'sections' => $sections,
            'todaySummary' => $todaySummary,
            'attendanceStatuses' => ['Present', 'Absent', 'Late', 'Excused', 'Sick'],
            'attendanceTypes' => ['Fingerprint', 'RFID', 'Manual', 'Facial']
        ];
        
        return view('App\Modules\Attendance\Views\attendance\index', $data);
    }
    
    /**
     * Show mark attendance form
     */
    public function mark()
    {
        $classes = $this->classModel->getActiveClasses();
        $sections = $this->sectionModel->getActiveSections();
        $devices = $this->deviceModel->getActiveDevices();
        
        $data = [
            'title' => 'Mark Attendance',
            'classes' => $classes,
            'sections' => $sections,
            'devices' => $devices,
            'attendanceStatuses' => ['Present', 'Absent', 'Late', 'Excused', 'Sick'],
            'attendanceTypes' => ['Fingerprint', 'RFID', 'Manual', 'Facial']
        ];
        
        return view('App\Modules\Attendance\Views\attendance\mark', $data);
    }
    
    /**
     * Store attendance record
     */
    public function store()
    {
        $request = service('request');
        $validation = service('validation');
        
        // Validation rules
        $rules = [
            'student_id' => 'required|integer|is_not_unique[students.id]',
            'attendance_date' => 'required|valid_date[Y-m-d]',
            'status' => 'required|in_list[Present,Absent,Late,Excused,Sick]',
            'attendance_type' => 'required|in_list[Fingerprint,RFID,Manual,Facial]',
            'check_in_time' => 'permit_empty|valid_date[H:i:s]',
            'check_out_time' => 'permit_empty|valid_date[H:i:s]',
            'notes' => 'permit_empty|max_length[500]'
        ];
        
        if (!$validation->setRules($rules)->run($request->getPost())) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
        
        $data = [
            'student_id' => $request->getPost('student_id'),
            'attendance_date' => $request->getPost('attendance_date'),
            'status' => $request->getPost('status'),
            'attendance_type' => $request->getPost('attendance_type'),
            'check_in_time' => $request->getPost('check_in_time'),
            'check_out_time' => $request->getPost('check_out_time'),
            'device_id' => $request->getPost('device_id'),
            'notes' => $request->getPost('notes'),
            'marked_by' => session()->get('user_id')
        ];
        
        // Check if attendance already marked
        if ($this->attendanceModel->isAttendanceMarked($data['student_id'], $data['attendance_date'])) {
            return redirect()->back()->withInput()->with('error', 'Attendance already marked for this student on this date.');
        }
        
        // Calculate late minutes if applicable
        if ($data['status'] === 'Late' && !empty($data['check_in_time'])) {
            $schedule = $this->timetableModel->getTodayScheduleForStudent($data['student_id']);
            if ($schedule) {
                $checkInTime = strtotime($data['check_in_time']);
                $scheduleTime = strtotime($schedule['start_time']);
                $data['late_minutes'] = max(0, ($checkInTime - $scheduleTime) / 60);
            }
        }
        
        try {
            $result = $this->attendanceModel->insert($data);
            
            if ($result) {
                // Trigger attendance marked event
                \CodeIgniter\Events\Events::trigger('attendance_marked', $data['student_id'], $data);
                
                return redirect()->to(base_url('attendance'))->with('success', 'Attendance marked successfully.');
            } else {
                return redirect()->back()->withInput()->with('error', 'Failed to mark attendance.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Show attendance record
     */
    public function show($id)
    {
        $attendance = $this->attendanceModel->select('attendance.*, students.name as student_name, 
                                                    students.student_id, students.photo,
                                                    classes.name as class_name, sections.name as section_name,
                                                    devices.name as device_name')
                                           ->join('students', 'attendance.student_id = students.id')
                                           ->join('student_sessions', 'students.id = student_sessions.student_id')
                                           ->join('class_sections', 'student_sessions.class_section_id = class_sections.id')
                                           ->join('classes', 'class_sections.class_id = classes.id')
                                           ->join('sections', 'class_sections.section_id = sections.id')
                                           ->join('devices', 'attendance.device_id = devices.id', 'left')
                                           ->find($id);
        
        if (!$attendance) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Attendance record not found');
        }
        
        $data = [
            'title' => 'Attendance Details',
            'attendance' => $attendance
        ];
        
        return view('App\Modules\Attendance\Views\attendance\show', $data);
    }
    
    /**
     * Show edit attendance form
     */
    public function edit($id)
    {
        $attendance = $this->attendanceModel->find($id);
        
        if (!$attendance) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Attendance record not found');
        }
        
        $student = $this->studentModel->find($attendance['student_id']);
        $classes = $this->classModel->getActiveClasses();
        $sections = $this->sectionModel->getActiveSections();
        $devices = $this->deviceModel->getActiveDevices();
        
        $data = [
            'title' => 'Edit Attendance',
            'attendance' => $attendance,
            'student' => $student,
            'classes' => $classes,
            'sections' => $sections,
            'devices' => $devices,
            'attendanceStatuses' => ['Present', 'Absent', 'Late', 'Excused', 'Sick'],
            'attendanceTypes' => ['Fingerprint', 'RFID', 'Manual', 'Facial']
        ];
        
        return view('App\Modules\Attendance\Views\attendance\edit', $data);
    }
    
    /**
     * Update attendance record
     */
    public function update($id)
    {
        $request = service('request');
        $validation = service('validation');
        
        $attendance = $this->attendanceModel->find($id);
        if (!$attendance) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Attendance record not found');
        }
        
        // Validation rules
        $rules = [
            'status' => 'required|in_list[Present,Absent,Late,Excused,Sick]',
            'attendance_type' => 'required|in_list[Fingerprint,RFID,Manual,Facial]',
            'check_in_time' => 'permit_empty|valid_date[H:i:s]',
            'check_out_time' => 'permit_empty|valid_date[H:i:s]',
            'notes' => 'permit_empty|max_length[500]'
        ];
        
        if (!$validation->setRules($rules)->run($request->getPost())) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
        
        $data = [
            'status' => $request->getPost('status'),
            'attendance_type' => $request->getPost('attendance_type'),
            'check_in_time' => $request->getPost('check_in_time'),
            'check_out_time' => $request->getPost('check_out_time'),
            'device_id' => $request->getPost('device_id'),
            'notes' => $request->getPost('notes')
        ];
        
        // Calculate late minutes if applicable
        if ($data['status'] === 'Late' && !empty($data['check_in_time'])) {
            $schedule = $this->timetableModel->getTodayScheduleForStudent($attendance['student_id']);
            if ($schedule) {
                $checkInTime = strtotime($data['check_in_time']);
                $scheduleTime = strtotime($schedule['start_time']);
                $data['late_minutes'] = max(0, ($checkInTime - $scheduleTime) / 60);
            }
        }
        
        try {
            $result = $this->attendanceModel->update($id, $data);
            
            if ($result) {
                return redirect()->to(base_url('attendance'))->with('success', 'Attendance updated successfully.');
            } else {
                return redirect()->back()->withInput()->with('error', 'Failed to update attendance.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete attendance record
     */
    public function delete($id)
    {
        $request = service('request');
        
        if ($request->getMethod() !== 'delete') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method']);
        }
        
        $attendance = $this->attendanceModel->find($id);
        if (!$attendance) {
            return $this->response->setJSON(['success' => false, 'message' => 'Attendance record not found']);
        }
        
        try {
            $result = $this->attendanceModel->delete($id);
            
            if ($result) {
                return $this->response->setJSON(['success' => true, 'message' => 'Attendance deleted successfully']);
            } else {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete attendance']);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Bulk delete attendance records
     */
    public function bulkDelete()
    {
        $request = service('request');
        $attendanceIds = $request->getPost('attendance_ids');
        
        if (empty($attendanceIds) || !is_array($attendanceIds)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No attendance records selected']);
        }
        
        try {
            $deleted = 0;
            foreach ($attendanceIds as $id) {
                if ($this->attendanceModel->delete($id)) {
                    $deleted++;
                }
            }
            
            return $this->response->setJSON([
                'success' => true, 
                'message' => "Successfully deleted {$deleted} attendance records"
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Bulk mark attendance
     */
    public function bulkMark()
    {
        $request = service('request');
        $studentIds = $request->getPost('student_ids');
        $attendanceData = $request->getPost();
        
        if (empty($studentIds) || !is_array($studentIds)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No students selected']);
        }
        
        $validation = service('validation');
        $rules = [
            'attendance_date' => 'required|valid_date[Y-m-d]',
            'status' => 'required|in_list[Present,Absent,Late,Excused,Sick]',
            'attendance_type' => 'required|in_list[Fingerprint,RFID,Manual,Facial]'
        ];
        
        if (!$validation->setRules($rules)->run($attendanceData)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Validation failed', 'errors' => $validation->getErrors()]);
        }
        
        try {
            $marked = 0;
            $skipped = 0;
            
            foreach ($studentIds as $studentId) {
                // Check if attendance already marked
                if ($this->attendanceModel->isAttendanceMarked($studentId, $attendanceData['attendance_date'])) {
                    $skipped++;
                    continue;
                }
                
                $data = [
                    'student_id' => $studentId,
                    'attendance_date' => $attendanceData['attendance_date'],
                    'status' => $attendanceData['status'],
                    'attendance_type' => $attendanceData['attendance_type'],
                    'check_in_time' => $attendanceData['check_in_time'] ?? null,
                    'check_out_time' => $attendanceData['check_out_time'] ?? null,
                    'device_id' => $attendanceData['device_id'] ?? null,
                    'notes' => $attendanceData['notes'] ?? null,
                    'marked_by' => session()->get('user_id')
                ];
                
                if ($this->attendanceModel->insert($data)) {
                    $marked++;
                    // Trigger attendance marked event
                    \CodeIgniter\Events\Events::trigger('attendance_marked', $studentId, $data);
                }
            }
            
            $message = "Successfully marked attendance for {$marked} students";
            if ($skipped > 0) {
                $message .= ", skipped {$skipped} (already marked)";
            }
            
            return $this->response->setJSON(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Export attendance data
     */
    public function export()
    {
        $request = service('request');
        $format = $request->getGet('format') ?? 'csv';
        
        // Get filters
        $filters = [
            'date_from' => $request->getGet('date_from') ?? date('Y-m-01'),
            'date_to' => $request->getGet('date_to') ?? date('Y-m-d'),
            'class_id' => $request->getGet('class_id'),
            'section_id' => $request->getGet('section_id'),
            'status' => $request->getGet('status')
        ];
        
        try {
            $exportService = new \App\Modules\Attendance\Services\AttendanceExportService();
            $result = $exportService->export($format, $filters);
            
            if ($result['success']) {
                return $this->response->download($result['file_path'], null)->setFileName($result['filename']);
            } else {
                return redirect()->back()->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Import attendance data
     */
    public function import()
    {
        $request = service('request');
        $file = $request->getFile('import_file');
        
        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'Please select a valid file to import.');
        }
        
        try {
            $importService = new \App\Modules\Attendance\Services\AttendanceImportService();
            $result = $importService->import($file);
            
            if ($result['success']) {
                return redirect()->to(base_url('attendance'))->with('success', $result['message']);
            } else {
                return redirect()->back()->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}