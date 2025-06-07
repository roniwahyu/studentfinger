<?php

namespace Modules\StudentManagement;

use App\Libraries\BaseModule;

/**
 * Student Management Module
 * 
 * This module handles all student-related operations including:
 * - Student registration and management
 * - Academic sessions
 * - Classes and sections
 * - Student-session assignments
 */
class StudentManagementModule extends BaseModule
{
    /**
     * Module name
     */
    protected string $moduleName = 'StudentManagement';
    
    /**
     * Module version
     */
    protected string $version = '1.0.0';
    
    /**
     * Module description
     */
    protected string $description = 'Manages students, sessions, classes, and sections';
    
    /**
     * Module dependencies
     */
    protected array $dependencies = [];
    
    /**
     * Initialize module
     */
    protected function initialize(): void
    {
        // Load module-specific configurations
        $this->loadModuleHelpers();
        $this->registerModuleServices();
    }
    
    /**
     * Load module helpers
     */
    private function loadModuleHelpers(): void
    {
        // Load any module-specific helpers
        helper(['form', 'url', 'text']);
    }
    
    /**
     * Register module services
     */
    private function registerModuleServices(): void
    {
        // Register any module-specific services
        // This can be extended as needed
    }
    
    /**
     * Get module information
     * 
     * @return array
     */
    public function getModuleInfo(): array
    {
        return [
            'name' => $this->moduleName,
            'version' => $this->version,
            'description' => $this->description,
            'dependencies' => $this->dependencies,
            'status' => 'active'
        ];
    }
    
    /**
     * Get student by ID
     * 
     * @param int $studentId
     * @return array|null
     */
    public function getStudent(int $studentId): ?array
    {
        $studentModel = $this->loadModel('StudentModel');
        return $studentModel->find($studentId);
    }
    
    /**
     * Get students by session
     * 
     * @param int $sessionId
     * @return array
     */
    public function getStudentsBySession(int $sessionId): array
    {
        $studentSessionModel = $this->loadModel('StudentSessionModel');
        return $studentSessionModel->getStudentsBySession($sessionId);
    }
    
    /**
     * Get active session
     * 
     * @return array|null
     */
    public function getActiveSession(): ?array
    {
        $sessionModel = $this->loadModel('SessionModel');
        return $sessionModel->getActiveSession();
    }
    
    /**
     * Get class sections
     * 
     * @param int $classId
     * @return array
     */
    public function getClassSections(int $classId): array
    {
        $classSectionModel = $this->loadModel('ClassSectionModel');
        return $classSectionModel->getSectionsByClass($classId);
    }
    
    /**
     * Validate student data
     * 
     * @param array $data
     * @return array
     */
    public function validateStudentData(array $data): array
    {
        $validation = \Config\Services::validation();
        
        $validation->setRules([
            'name' => 'required|min_length[3]|max_length[100]',
            'email' => 'permit_empty|valid_email|max_length[100]',
            'phone' => 'permit_empty|min_length[10]|max_length[15]',
            'rfid_card' => 'permit_empty|max_length[50]',
            'student_id' => 'required|max_length[20]',
            'class_id' => 'required|integer',
            'section_id' => 'required|integer'
        ]);
        
        $isValid = $validation->run($data);
        
        return [
            'valid' => $isValid,
            'errors' => $validation->getErrors()
        ];
    }
    
    /**
     * Create student widget for dashboard
     * 
     * @param array $params
     * @return string
     */
    public function getStudentStatsWidget(array $params = []): string
    {
        $studentModel = $this->loadModel('StudentModel');
        $sessionModel = $this->loadModel('SessionModel');
        
        $activeSession = $sessionModel->getActiveSession();
        $totalStudents = $studentModel->countAll();
        $activeStudents = $activeSession ? 
            $studentModel->getActiveStudentsCount($activeSession['id']) : 0;
        
        $data = [
            'total_students' => $totalStudents,
            'active_students' => $activeStudents,
            'session' => $activeSession
        ];
        
        return $this->loadView('widgets/student_stats', $data);
    }
}