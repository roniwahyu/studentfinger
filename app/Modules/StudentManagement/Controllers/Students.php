<?php

namespace Modules\StudentManagement\Controllers;

use App\Controllers\BaseController;
use Modules\StudentManagement\Models\StudentModel;
use Modules\StudentManagement\Models\SessionModel;
use Modules\StudentManagement\Models\ClassModel;
use Modules\StudentManagement\Models\SectionModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Students Controller
 * 
 * Handles all student-related operations in the Student Management module
 */
class Students extends BaseController
{
    protected StudentModel $studentModel;
    protected SessionModel $sessionModel;
    protected ClassModel $classModel;
    protected SectionModel $sectionModel;
    
    public function __construct()
    {
        $this->studentModel = new StudentModel();
        $this->sessionModel = new SessionModel();
        $this->classModel = new ClassModel();
        $this->sectionModel = new SectionModel();
    }
    
    /**
     * Display list of students
     */
    public function index()
    {
        $perPage = 25;
        $page = $this->request->getVar('page') ?? 1;
        $search = $this->request->getVar('search');
        $classId = $this->request->getVar('class_id');
        $sectionId = $this->request->getVar('section_id');
        $sessionId = $this->request->getVar('session_id');
        
        $builder = $this->studentModel;
        
        // Apply filters
        if ($search) {
            $builder = $builder->groupStart()
                             ->like('name', $search)
                             ->orLike('student_id', $search)
                             ->orLike('email', $search)
                             ->groupEnd();
        }
        
        if ($classId) {
            $builder = $builder->join('class_sections cs', 'students.id = cs.student_id')
                             ->where('cs.class_id', $classId);
        }
        
        if ($sectionId) {
            $builder = $builder->join('class_sections cs2', 'students.id = cs2.student_id')
                             ->where('cs2.section_id', $sectionId);
        }
        
        if ($sessionId) {
            $builder = $builder->join('student_session ss', 'students.id = ss.student_id')
                             ->where('ss.session_id', $sessionId);
        }
        
        $students = $builder->paginate($perPage, 'default', $page);
        $pager = $builder->pager;
        
        // Get filter options
        $classes = $this->classModel->findAll();
        $sections = $this->sectionModel->findAll();
        $sessions = $this->sessionModel->findAll();
        
        $data = [
            'title' => 'Students Management',
            'students' => $students,
            'pager' => $pager,
            'classes' => $classes,
            'sections' => $sections,
            'sessions' => $sessions,
            'filters' => [
                'search' => $search,
                'class_id' => $classId,
                'section_id' => $sectionId,
                'session_id' => $sessionId
            ]
        ];
        
        return view('Modules/StudentManagement/Views/students/index', $data);
    }
    
    /**
     * Show student creation form
     */
    public function create()
    {
        $data = [
            'title' => 'Add New Student',
            'classes' => $this->classModel->findAll(),
            'sections' => $this->sectionModel->findAll(),
            'sessions' => $this->sessionModel->findAll(),
            'activeSession' => $this->sessionModel->getActiveSession()
        ];
        
        return view('Modules/StudentManagement/Views/students/create', $data);
    }
    
    /**
     * Store new student
     */
    public function store()
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[100]',
            'email' => 'permit_empty|valid_email|max_length[100]',
            'phone' => 'permit_empty|min_length[10]|max_length[15]',
            'student_id' => 'permit_empty|max_length[20]|is_unique[students.student_id]',
            'rfid_card' => 'permit_empty|max_length[50]|is_unique[students.rfid_card]',
            'class_id' => 'required|integer',
            'section_id' => 'required|integer',
            'session_id' => 'required|integer',
            'photo' => 'permit_empty|uploaded[photo]|max_size[photo,2048]|is_image[photo]'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $data = $this->request->getPost();
        
        // Handle photo upload
        $photo = $this->request->getFile('photo');
        if ($photo && $photo->isValid() && !$photo->hasMoved()) {
            $newName = $photo->getRandomName();
            $photo->move(WRITEPATH . 'uploads/students/', $newName);
            $data['photo'] = $newName;
        }
        
        // Remove non-student fields
        $classId = $data['class_id'];
        $sectionId = $data['section_id'];
        $sessionId = $data['session_id'];
        unset($data['class_id'], $data['section_id'], $data['session_id']);
        
        $this->studentModel->db->transStart();
        
        try {
            // Insert student
            $studentId = $this->studentModel->insert($data);
            
            if (!$studentId) {
                throw new \Exception('Failed to create student');
            }
            
            // Assign to class and section
            $this->db->table('class_sections')->insert([
                'student_id' => $studentId,
                'class_id' => $classId,
                'section_id' => $sectionId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Assign to session
            $this->db->table('student_session')->insert([
                'student_id' => $studentId,
                'session_id' => $sessionId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            $this->studentModel->db->transComplete();
            
            if ($this->studentModel->db->transStatus()) {
                return redirect()->to('/students')->with('success', 'Student created successfully');
            } else {
                throw new \Exception('Transaction failed');
            }
            
        } catch (\Exception $e) {
            $this->studentModel->db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Failed to create student: ' . $e->getMessage());
        }
    }
    
    /**
     * Show student details
     */
    public function show($id)
    {
        $student = $this->studentModel->find($id);
        
        if (!$student) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Student not found');
        }
        
        // Get student's class and section
        $classSection = $this->db->table('class_sections cs')
            ->select('cs.*, c.name as class_name, s.name as section_name')
            ->join('classes c', 'cs.class_id = c.id')
            ->join('sections s', 'cs.section_id = s.id')
            ->where('cs.student_id', $id)
            ->get()
            ->getRowArray();
        
        // Get student's session
        $studentSession = $this->db->table('student_session ss')
            ->select('ss.*, sess.name as session_name')
            ->join('sessions sess', 'ss.session_id = sess.id')
            ->where('ss.student_id', $id)
            ->get()
            ->getRowArray();
        
        $data = [
            'title' => 'Student Details',
            'student' => $student,
            'classSection' => $classSection,
            'studentSession' => $studentSession
        ];
        
        return view('Modules/StudentManagement/Views/students/show', $data);
    }
    
    /**
     * Show student edit form
     */
    public function edit($id)
    {
        $student = $this->studentModel->find($id);
        
        if (!$student) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Student not found');
        }
        
        // Get current assignments
        $classSection = $this->db->table('class_sections')
            ->where('student_id', $id)
            ->get()
            ->getRowArray();
        
        $studentSession = $this->db->table('student_session')
            ->where('student_id', $id)
            ->get()
            ->getRowArray();
        
        $data = [
            'title' => 'Edit Student',
            'student' => $student,
            'classes' => $this->classModel->findAll(),
            'sections' => $this->sectionModel->findAll(),
            'sessions' => $this->sessionModel->findAll(),
            'currentClass' => $classSection['class_id'] ?? null,
            'currentSection' => $classSection['section_id'] ?? null,
            'currentSession' => $studentSession['session_id'] ?? null
        ];
        
        return view('Modules/StudentManagement/Views/students/edit', $data);
    }
    
    /**
     * Update student
     */
    public function update($id)
    {
        $student = $this->studentModel->find($id);
        
        if (!$student) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Student not found');
        }
        
        $rules = [
            'name' => 'required|min_length[3]|max_length[100]',
            'email' => 'permit_empty|valid_email|max_length[100]',
            'phone' => 'permit_empty|min_length[10]|max_length[15]',
            'student_id' => "permit_empty|max_length[20]|is_unique[students.student_id,id,{$id}]",
            'rfid_card' => "permit_empty|max_length[50]|is_unique[students.rfid_card,id,{$id}]",
            'class_id' => 'required|integer',
            'section_id' => 'required|integer',
            'session_id' => 'required|integer',
            'photo' => 'permit_empty|uploaded[photo]|max_size[photo,2048]|is_image[photo]'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $data = $this->request->getPost();
        
        // Handle photo upload
        $photo = $this->request->getFile('photo');
        if ($photo && $photo->isValid() && !$photo->hasMoved()) {
            // Delete old photo
            if ($student['photo'] && file_exists(WRITEPATH . 'uploads/students/' . $student['photo'])) {
                unlink(WRITEPATH . 'uploads/students/' . $student['photo']);
            }
            
            $newName = $photo->getRandomName();
            $photo->move(WRITEPATH . 'uploads/students/', $newName);
            $data['photo'] = $newName;
        }
        
        // Remove non-student fields
        $classId = $data['class_id'];
        $sectionId = $data['section_id'];
        $sessionId = $data['session_id'];
        unset($data['class_id'], $data['section_id'], $data['session_id']);
        
        $this->studentModel->db->transStart();
        
        try {
            // Update student
            $this->studentModel->update($id, $data);
            
            // Update class and section assignment
            $this->db->table('class_sections')
                ->where('student_id', $id)
                ->update([
                    'class_id' => $classId,
                    'section_id' => $sectionId,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            
            // Update session assignment
            $this->db->table('student_session')
                ->where('student_id', $id)
                ->update([
                    'session_id' => $sessionId,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            
            $this->studentModel->db->transComplete();
            
            if ($this->studentModel->db->transStatus()) {
                return redirect()->to('/students')->with('success', 'Student updated successfully');
            } else {
                throw new \Exception('Transaction failed');
            }
            
        } catch (\Exception $e) {
            $this->studentModel->db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Failed to update student: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete student
     */
    public function delete($id)
    {
        $student = $this->studentModel->find($id);
        
        if (!$student) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Student not found'
            ]);
        }
        
        if ($this->studentModel->delete($id)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Student deleted successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to delete student'
            ]);
        }
    }
    
    /**
     * Search students (AJAX)
     */
    public function search()
    {
        $query = $this->request->getVar('q');
        $limit = $this->request->getVar('limit') ?? 20;
        
        if (empty($query)) {
            return $this->response->setJSON([]);
        }
        
        $students = $this->studentModel->searchStudents($query, $limit);
        
        return $this->response->setJSON($students);
    }
    
    /**
     * Get students by class (AJAX)
     */
    public function getByClass($classId)
    {
        $students = $this->studentModel->getStudentsByClass($classId);
        
        return $this->response->setJSON($students);
    }
    
    /**
     * Get students by session (AJAX)
     */
    public function getBySession($sessionId)
    {
        $students = $this->studentModel->getBySession($sessionId);
        
        return $this->response->setJSON($students);
    }
    
    /**
     * Bulk delete students
     */
    public function bulkDelete()
    {
        $studentIds = $this->request->getPost('student_ids');
        
        if (empty($studentIds)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No students selected'
            ]);
        }
        
        $deleted = $this->studentModel->delete($studentIds);
        
        if ($deleted) {
            return $this->response->setJSON([
                'success' => true,
                'message' => count($studentIds) . ' students deleted successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to delete students'
            ]);
        }
    }
    
    /**
     * Export students to CSV
     */
    public function export()
    {
        $students = $this->studentModel->getStudentsWithDetails();
        
        $filename = 'students_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($output, [
            'Student ID', 'Name', 'Email', 'Phone', 'RFID Card',
            'Class', 'Section', 'Session', 'Status', 'Created At'
        ]);
        
        // CSV data
        foreach ($students as $student) {
            fputcsv($output, [
                $student['student_id'],
                $student['name'],
                $student['email'],
                $student['phone'],
                $student['rfid_card'],
                $student['class_name'],
                $student['section_name'],
                $student['session_name'],
                $student['status'],
                $student['created_at']
            ]);
        }
        
        fclose($output);
        exit;
    }
}