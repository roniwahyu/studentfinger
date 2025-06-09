<?php

namespace App\Modules\ClassroomNotifications\Controllers;

use App\Controllers\BaseController;
use App\Modules\ClassroomNotifications\Models\ClassSessionModel;
use App\Modules\ClassroomNotifications\Models\NotificationTemplateModel;
use App\Modules\ClassroomNotifications\Services\WhatsAppService;

/**
 * Session Controller
 * 
 * Manages classroom sessions and their state changes
 */
class SessionController extends BaseController
{
    protected $sessionModel;
    protected $whatsappService;
    
    public function __construct()
    {
        $this->sessionModel = new ClassSessionModel();
        $this->whatsappService = new WhatsAppService();
    }
    
    /**
     * Sessions list
     */
    public function index()
    {
        $filters = [
            'status' => $this->request->getGet('status'),
            'class_id' => $this->request->getGet('class_id'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to')
        ];
        
        $data = [
            'title' => 'Class Sessions',
            'sessions' => $this->sessionModel->getSessionsWithClass(50, 0, $filters),
            'filters' => $filters,
            'classes' => $this->getClasses(),
            'stats' => $this->sessionModel->getSessionStats()
        ];
        
        return view('App\Modules\ClassroomNotifications\Views\sessions', $data);
    }
    
    /**
     * Create new session
     */
    public function create()
    {
        $data = [
            'title' => 'Create Class Session',
            'classes' => $this->getClasses(),
            'teachers' => $this->getTeachers()
        ];
        
        return view('App\Modules\ClassroomNotifications\Views\session_form', $data);
    }
    
    /**
     * Save new session
     */
    public function save()
    {
        $validation = \Config\Services::validation();
        
        $rules = [
            'class_id' => 'required|integer',
            'session_name' => 'required|min_length[3]|max_length[100]',
            'subject' => 'required|min_length[2]|max_length[50]',
            'teacher_name' => 'required|min_length[2]|max_length[100]',
            'start_time' => 'required',
            'end_time' => 'required',
            'session_date' => 'required|valid_date'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
        
        // Get total students in class
        $totalStudents = $this->getClassStudentCount($this->request->getPost('class_id'));
        
        $data = [
            'class_id' => $this->request->getPost('class_id'),
            'session_name' => $this->request->getPost('session_name'),
            'subject' => $this->request->getPost('subject'),
            'teacher_name' => $this->request->getPost('teacher_name'),
            'start_time' => $this->request->getPost('start_time'),
            'end_time' => $this->request->getPost('end_time'),
            'break_duration' => $this->request->getPost('break_duration') ?: 15,
            'session_date' => $this->request->getPost('session_date'),
            'notes' => $this->request->getPost('notes'),
            'status' => ClassSessionModel::STATUS_SCHEDULED,
            'total_students' => $totalStudents,
            'present_students' => 0,
            'notifications_sent' => 0
        ];
        
        if ($this->sessionModel->insert($data)) {
            return redirect()->to('classroom-notifications/sessions')->with('success', 'Session created successfully');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to create session');
        }
    }
    
    /**
     * Edit session
     */
    public function edit(int $sessionId)
    {
        $session = $this->sessionModel->find($sessionId);
        if (!$session) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Session not found');
        }
        
        $data = [
            'title' => 'Edit Class Session',
            'session' => $session,
            'classes' => $this->getClasses(),
            'teachers' => $this->getTeachers()
        ];
        
        return view('App\Modules\ClassroomNotifications\Views\session_form', $data);
    }
    
    /**
     * Update session
     */
    public function update(int $sessionId)
    {
        $session = $this->sessionModel->find($sessionId);
        if (!$session) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Session not found');
        }
        
        // Only allow editing if session is scheduled
        if ($session['status'] !== ClassSessionModel::STATUS_SCHEDULED) {
            return redirect()->back()->with('error', 'Cannot edit session that has already started');
        }
        
        $validation = \Config\Services::validation();
        
        $rules = [
            'session_name' => 'required|min_length[3]|max_length[100]',
            'subject' => 'required|min_length[2]|max_length[50]',
            'teacher_name' => 'required|min_length[2]|max_length[100]',
            'start_time' => 'required',
            'end_time' => 'required',
            'session_date' => 'required|valid_date'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
        
        $data = [
            'session_name' => $this->request->getPost('session_name'),
            'subject' => $this->request->getPost('subject'),
            'teacher_name' => $this->request->getPost('teacher_name'),
            'start_time' => $this->request->getPost('start_time'),
            'end_time' => $this->request->getPost('end_time'),
            'break_duration' => $this->request->getPost('break_duration') ?: 15,
            'session_date' => $this->request->getPost('session_date'),
            'notes' => $this->request->getPost('notes')
        ];
        
        if ($this->sessionModel->update($sessionId, $data)) {
            return redirect()->to('classroom-notifications/sessions')->with('success', 'Session updated successfully');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update session');
        }
    }
    
    /**
     * Delete session
     */
    public function delete(int $sessionId)
    {
        $session = $this->sessionModel->find($sessionId);
        if (!$session) {
            return redirect()->back()->with('error', 'Session not found');
        }
        
        // Only allow deletion if session is scheduled
        if ($session['status'] !== ClassSessionModel::STATUS_SCHEDULED) {
            return redirect()->back()->with('error', 'Cannot delete session that has already started');
        }
        
        if ($this->sessionModel->delete($sessionId)) {
            return redirect()->back()->with('success', 'Session deleted successfully');
        } else {
            return redirect()->back()->with('error', 'Failed to delete session');
        }
    }
    
    /**
     * Start session
     */
    public function startSession(int $sessionId)
    {
        if (!$this->sessionModel->canStartSession($sessionId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Session cannot be started'
            ]);
        }
        
        if ($this->sessionModel->startSession($sessionId)) {
            // Send start notifications if requested
            $sendNotifications = $this->request->getPost('send_notifications');
            if ($sendNotifications) {
                $this->sendSessionNotifications($sessionId, NotificationTemplateModel::EVENT_SESSION_START);
            }
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Session started successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to start session'
            ]);
        }
    }
    
    /**
     * Set session to break
     */
    public function breakSession(int $sessionId)
    {
        if (!$this->sessionModel->canBreakSession($sessionId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Session cannot be set to break'
            ]);
        }
        
        if ($this->sessionModel->breakSession($sessionId)) {
            // Send break notifications if requested
            $sendNotifications = $this->request->getPost('send_notifications');
            if ($sendNotifications) {
                $this->sendSessionNotifications($sessionId, NotificationTemplateModel::EVENT_SESSION_BREAK);
            }
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Session set to break successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to set session to break'
            ]);
        }
    }
    
    /**
     * Resume session from break
     */
    public function resumeSession(int $sessionId)
    {
        if (!$this->sessionModel->canResumeSession($sessionId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Session cannot be resumed'
            ]);
        }
        
        if ($this->sessionModel->resumeSession($sessionId)) {
            // Send resume notifications if requested
            $sendNotifications = $this->request->getPost('send_notifications');
            if ($sendNotifications) {
                $this->sendSessionNotifications($sessionId, NotificationTemplateModel::EVENT_SESSION_RESUME);
            }
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Session resumed successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to resume session'
            ]);
        }
    }
    
    /**
     * Finish session
     */
    public function finishSession(int $sessionId)
    {
        if (!$this->sessionModel->canFinishSession($sessionId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Session cannot be finished'
            ]);
        }
        
        if ($this->sessionModel->finishSession($sessionId)) {
            // Send finish notifications if requested
            $sendNotifications = $this->request->getPost('send_notifications');
            if ($sendNotifications) {
                $this->sendSessionNotifications($sessionId, NotificationTemplateModel::EVENT_SESSION_FINISH);
            }
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Session finished successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to finish session'
            ]);
        }
    }
    
    /**
     * Get session status
     */
    public function getSessionStatus(int $sessionId)
    {
        $session = $this->sessionModel->find($sessionId);
        if (!$session) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Session not found'
            ]);
        }
        
        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'status' => $session['status'],
                'can_start' => $this->sessionModel->canStartSession($sessionId),
                'can_break' => $this->sessionModel->canBreakSession($sessionId),
                'can_resume' => $this->sessionModel->canResumeSession($sessionId),
                'can_finish' => $this->sessionModel->canFinishSession($sessionId)
            ]
        ]);
    }
    
    /**
     * Get students for AJAX
     */
    public function getStudents()
    {
        $classId = $this->request->getPost('class_id');
        
        if (empty($classId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Class ID is required'
            ]);
        }
        
        $db = \Config\Database::connect();
        $students = $db->table('students')
                      ->select('student_id, firstname, lastname, father_phone')
                      ->where('deleted_at', null)
                      ->get()
                      ->getResultArray();
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $students
        ]);
    }
    
    /**
     * Send session notifications
     */
    protected function sendSessionNotifications(int $sessionId, string $eventType)
    {
        // This would be called from the NotificationController
        // to avoid circular dependencies
        $notificationController = new \App\Modules\ClassroomNotifications\Controllers\NotificationController();
        
        // Simulate the request
        $this->request->setGlobal('post', [
            'session_id' => $sessionId,
            'event_type' => $eventType
        ]);
        
        return $notificationController->sendNotification();
    }
    
    /**
     * Get available classes
     */
    protected function getClasses(): array
    {
        $db = \Config\Database::connect();
        return $db->table('classes')
                 ->select('id, class as class_name')
                 ->where('deleted_at', null)
                 ->orderBy('class', 'ASC')
                 ->get()
                 ->getResultArray();
    }
    
    /**
     * Get available teachers
     */
    protected function getTeachers(): array
    {
        // This could be from a teachers table or just return common names
        return [
            'Mr. Ahmad',
            'Mrs. Sari',
            'Mr. Budi',
            'Mrs. Dewi',
            'Mr. Andi',
            'Mrs. Rina'
        ];
    }
    
    /**
     * Get student count for a class
     */
    protected function getClassStudentCount(int $classId): int
    {
        $db = \Config\Database::connect();
        $result = $db->table('students')
                    ->where('deleted_at', null)
                    ->countAllResults();
        
        return $result;
    }
}
