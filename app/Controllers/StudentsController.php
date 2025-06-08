<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\StudentModel;
use App\Models\SessionModel;
use App\Models\ClassModel;
use App\Models\SectionModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Students Controller
 * 
 * Handles all student-related operations
 */
class StudentsController extends BaseController
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
            $builder = $builder->where('class_id', $classId);
        }
        
        if ($sectionId) {
            $builder = $builder->where('section_id', $sectionId);
        }
        
        if ($sessionId) {
            $builder = $builder->where('session_id', $sessionId);
        }
        
        $students = $builder->paginate($perPage);
        $pager = $this->studentModel->pager;
        
        $data = [
            'students' => $students,
            'pager' => $pager,
            'classes' => $this->classModel->findAll(),
            'sections' => $this->sectionModel->findAll(),
            'sessions' => $this->sessionModel->findAll(),
            'filters' => [
                'search' => $search,
                'class_id' => $classId,
                'section_id' => $sectionId,
                'session_id' => $sessionId
            ]
        ];
        
        return view('students/index', $data);
    }
    
    /**
     * Show form to create new student
     */
    public function create()
    {
        $data = [
            'classes' => $this->classModel->findAll(),
            'sections' => $this->sectionModel->findAll(),
            'sessions' => $this->sessionModel->findAll()
        ];
        
        return view('students/create', $data);
    }
    
    /**
     * Store new student
     */
    public function store()
    {
        $rules = [
            'student_id' => 'required|max_length[20]|is_unique[students.student_id]',
            'name' => 'required|min_length[3]|max_length[100]',
            'email' => 'permit_empty|valid_email|is_unique[students.email]',
            'phone' => 'permit_empty|max_length[20]',
            'date_of_birth' => 'permit_empty|valid_date[Y-m-d]',
            'gender' => 'required|in_list[Male,Female,Other]',
            'class_id' => 'required|integer',
            'section_id' => 'required|integer',
            'session_id' => 'required|integer'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $data = [
            'student_id' => $this->request->getPost('student_id'),
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'address' => $this->request->getPost('address'),
            'date_of_birth' => $this->request->getPost('date_of_birth'),
            'gender' => $this->request->getPost('gender'),
            'rfid_card' => $this->request->getPost('rfid_card'),
            'parent_name' => $this->request->getPost('parent_name'),
            'parent_phone' => $this->request->getPost('parent_phone'),
            'parent_email' => $this->request->getPost('parent_email'),
            'emergency_contact' => $this->request->getPost('emergency_contact'),
            'blood_group' => $this->request->getPost('blood_group'),
            'medical_info' => $this->request->getPost('medical_info'),
            'admission_date' => $this->request->getPost('admission_date'),
            'class_id' => $this->request->getPost('class_id'),
            'section_id' => $this->request->getPost('section_id'),
            'session_id' => $this->request->getPost('session_id'),
            'status' => 'Active',
            'notes' => $this->request->getPost('notes')
        ];
        
        // Handle photo upload
        $photo = $this->request->getFile('photo');
        if ($photo && $photo->isValid() && !$photo->hasMoved()) {
            $newName = $photo->getRandomName();
            $photo->move(WRITEPATH . 'uploads/students/', $newName);
            $data['photo'] = $newName;
        }
        
        if ($this->studentModel->save($data)) {
            return redirect()->to('/students')->with('success', 'Student created successfully');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to create student');
        }
    }
    
    /**
     * Show student details
     */
    public function show($id)
    {
        $student = $this->studentModel
            ->select('students.*, classes.name as class_name, sections.name as section_name, sessions.name as session_name')
            ->join('classes', 'classes.id = students.class_id', 'left')
            ->join('sections', 'sections.id = students.section_id', 'left')
            ->join('sessions', 'sessions.id = students.session_id', 'left')
            ->find($id);
            
        if (!$student) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Student not found');
        }
        
        return view('students/show', ['student' => $student]);
    }
    
    /**
     * Show edit form
     */
    public function edit($id)
    {
        $student = $this->studentModel->find($id);
        
        if (!$student) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Student not found');
        }
        
        $data = [
            'student' => $student,
            'classes' => $this->classModel->findAll(),
            'sections' => $this->sectionModel->findAll(),
            'sessions' => $this->sessionModel->findAll()
        ];
        
        return view('students/edit', $data);
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
            'student_id' => "required|max_length[20]|is_unique[students.student_id,id,{$id}]",
            'name' => 'required|min_length[3]|max_length[100]',
            'email' => "permit_empty|valid_email|is_unique[students.email,id,{$id}]",
            'phone' => 'permit_empty|max_length[20]',
            'date_of_birth' => 'permit_empty|valid_date[Y-m-d]',
            'gender' => 'required|in_list[Male,Female,Other]',
            'class_id' => 'required|integer',
            'section_id' => 'required|integer',
            'session_id' => 'required|integer'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $data = [
            'student_id' => $this->request->getPost('student_id'),
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'address' => $this->request->getPost('address'),
            'date_of_birth' => $this->request->getPost('date_of_birth'),
            'gender' => $this->request->getPost('gender'),
            'rfid_card' => $this->request->getPost('rfid_card'),
            'parent_name' => $this->request->getPost('parent_name'),
            'parent_phone' => $this->request->getPost('parent_phone'),
            'parent_email' => $this->request->getPost('parent_email'),
            'emergency_contact' => $this->request->getPost('emergency_contact'),
            'blood_group' => $this->request->getPost('blood_group'),
            'medical_info' => $this->request->getPost('medical_info'),
            'admission_date' => $this->request->getPost('admission_date'),
            'class_id' => $this->request->getPost('class_id'),
            'section_id' => $this->request->getPost('section_id'),
            'session_id' => $this->request->getPost('session_id'),
            'status' => $this->request->getPost('status'),
            'notes' => $this->request->getPost('notes')
        ];
        
        // Handle photo upload
        $photo = $this->request->getFile('photo');
        if ($photo && $photo->isValid() && !$photo->hasMoved()) {
            // Delete old photo if exists
            if (!empty($student['photo']) && file_exists(WRITEPATH . 'uploads/students/' . $student['photo'])) {
                unlink(WRITEPATH . 'uploads/students/' . $student['photo']);
            }
            
            $newName = $photo->getRandomName();
            $photo->move(WRITEPATH . 'uploads/students/', $newName);
            $data['photo'] = $newName;
        }
        
        if ($this->studentModel->update($id, $data)) {
            return redirect()->to('/students')->with('success', 'Student updated successfully');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update student');
        }
    }
    
    /**
     * Delete student
     */
    public function delete($id)
    {
        $student = $this->studentModel->find($id);
        
        if (!$student) {
            return $this->response->setJSON(['success' => false, 'message' => 'Student not found']);
        }
        
        if ($this->studentModel->delete($id)) {
            // Delete photo if exists
            if (!empty($student['photo']) && file_exists(WRITEPATH . 'uploads/students/' . $student['photo'])) {
                unlink(WRITEPATH . 'uploads/students/' . $student['photo']);
            }
            
            return $this->response->setJSON(['success' => true, 'message' => 'Student deleted successfully']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete student']);
        }
    }
    
    /**
     * Search students
     */
    public function search()
    {
        $query = $this->request->getGet('q');
        
        if (empty($query)) {
            return $this->response->setJSON([]);
        }
        
        $students = $this->studentModel
            ->like('name', $query)
            ->orLike('student_id', $query)
            ->limit(10)
            ->findAll();
            
        return $this->response->setJSON($students);
    }
    
    /**
     * Get students by class
     */
    public function getByClass($classId)
    {
        $students = $this->studentModel
            ->where('class_id', $classId)
            ->where('status', 'Active')
            ->findAll();
            
        return $this->response->setJSON($students);
    }
    
    /**
     * Get students by session
     */
    public function getBySession($sessionId)
    {
        $students = $this->studentModel
            ->where('session_id', $sessionId)
            ->where('status', 'Active')
            ->findAll();
            
        return $this->response->setJSON($students);
    }
}