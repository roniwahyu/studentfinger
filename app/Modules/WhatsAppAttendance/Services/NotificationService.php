<?php

namespace App\Modules\WhatsAppAttendance\Services;

use CodeIgniter\Config\Services;
use App\Modules\WablasIntegration\Services\WablasService;
use App\Modules\WhatsAppAttendance\Models\NotificationLogModel;
use App\Modules\WhatsAppAttendance\Models\StudentParentModel;

/**
 * Notification Service
 * 
 * Handles sending WhatsApp notifications to parents via Wablas
 */
class NotificationService
{
    protected $config;
    protected $logger;
    protected $wablasService;
    protected $notificationLogModel;
    protected $studentParentModel;
    protected $db;
    
    public function __construct()
    {
        $this->config = config('WhatsAppAttendance');
        $this->logger = Services::logger();
        $this->wablasService = new WablasService();
        $this->notificationLogModel = new NotificationLogModel();
        $this->studentParentModel = new StudentParentModel();
        $this->db = \Config\Database::connect();
    }
    
    /**
     * Send attendance notification to parents
     */
    public function sendAttendanceNotification($attendanceRecord)
    {
        $result = [
            'success' => false,
            'notifications_sent' => 0,
            'notifications_failed' => 0,
            'notifications' => [],
            'errors' => []
        ];
        
        try {
            // Get student information
            $student = $this->getStudentInfo($attendanceRecord['student_id']);
            
            if (!$student) {
                throw new \Exception('Student not found: ' . $attendanceRecord['student_id']);
            }
            
            // Get parent phone numbers
            $parentPhones = $this->getParentPhones($student['student_id']);
            
            if (empty($parentPhones)) {
                throw new \Exception('No parent phone numbers found for student: ' . $student['student_id']);
            }
            
            // Determine notification type
            $notificationType = $this->determineNotificationType($attendanceRecord);
            
            // Generate message
            $message = $this->generateMessage($student, $attendanceRecord, $notificationType);
            
            // Send notifications to each parent
            foreach ($parentPhones as $phone) {
                try {
                    $notificationResult = $this->sendSingleNotification($phone, $message, $student, $attendanceRecord, $notificationType);
                    
                    if ($notificationResult['success']) {
                        $result['notifications_sent']++;
                        $result['notifications'][] = $notificationResult;
                    } else {
                        $result['notifications_failed']++;
                        $result['errors'][] = $notificationResult['error'];
                    }
                    
                } catch (\Exception $e) {
                    $result['notifications_failed']++;
                    $result['errors'][] = 'Failed to send to ' . $phone . ': ' . $e->getMessage();
                    $this->logger->error('Notification send error: ' . $e->getMessage(), ['phone' => $phone, 'student' => $student]);
                }
            }
            
            $result['success'] = $result['notifications_sent'] > 0;
            
            $this->logger->info('Attendance notification completed', [
                'student_id' => $student['student_id'],
                'sent' => $result['notifications_sent'],
                'failed' => $result['notifications_failed']
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
            $this->logger->error('Attendance notification failed: ' . $e->getMessage(), $attendanceRecord);
            
            return $result;
        }
    }
    
    /**
     * Send single notification
     */
    protected function sendSingleNotification($phone, $message, $student, $attendanceRecord, $notificationType)
    {
        try {
            // Format phone number
            $formattedPhone = $this->formatPhoneNumber($phone);
            
            // Check if notification already sent (duplicate prevention)
            if ($this->isDuplicateNotification($student['student_id'], $attendanceRecord['scan_date'], $formattedPhone, $notificationType)) {
                if ($this->config['notifications']['duplicate_prevention']) {
                    return [
                        'success' => false,
                        'error' => 'Duplicate notification prevented',
                        'phone' => $formattedPhone
                    ];
                }
            }
            
            // Send via Wablas
            $wablasResult = $this->wablasService->sendMessage($formattedPhone, $message);
            
            // Log notification
            $logData = [
                'student_id' => $student['student_id'],
                'parent_phone' => $formattedPhone,
                'notification_type' => $notificationType,
                'message' => $message,
                'scan_date' => $attendanceRecord['scan_date'],
                'status' => $wablasResult['success'] ? 'sent' : 'failed',
                'wablas_response' => json_encode($wablasResult),
                'error_message' => $wablasResult['success'] ? null : ($wablasResult['error'] ?? 'Unknown error'),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $this->notificationLogModel->insert($logData);
            
            return [
                'success' => $wablasResult['success'],
                'phone' => $formattedPhone,
                'message' => $message,
                'wablas_response' => $wablasResult,
                'error' => $wablasResult['success'] ? null : ($wablasResult['error'] ?? 'Unknown error')
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Single notification send error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'phone' => $phone,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get student information
     */
    protected function getStudentInfo($studentId)
    {
        try {
            $student = $this->db->table('students')
                ->select('student_id, firstname, lastname, class_id, section_id, rfid')
                ->where('student_id', $studentId)
                ->where('status', 'Active')
                ->get()
                ->getRowArray();
            
            if ($student) {
                // Get class and section names
                $class = $this->db->table('classes')
                    ->select('class')
                    ->where('class_id', $student['class_id'])
                    ->get()
                    ->getRowArray();
                
                $section = $this->db->table('sections')
                    ->select('section')
                    ->where('section_id', $student['section_id'])
                    ->get()
                    ->getRowArray();
                
                $student['class_name'] = $class['class'] ?? 'Unknown';
                $student['section_name'] = $section['section'] ?? 'Unknown';
                $student['full_name'] = trim($student['firstname'] . ' ' . $student['lastname']);
            }
            
            return $student;
            
        } catch (\Exception $e) {
            $this->logger->error('Error getting student info: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get parent phone numbers
     */
    protected function getParentPhones($studentId)
    {
        try {
            $phones = [];
            
            // Get from student table
            $student = $this->db->table('students')
                ->select('father_phone, mother_phone, mobileno')
                ->where('student_id', $studentId)
                ->get()
                ->getRowArray();
            
            if ($student) {
                if (!empty($student['father_phone'])) {
                    $phones[] = $student['father_phone'];
                }
                if (!empty($student['mother_phone'])) {
                    $phones[] = $student['mother_phone'];
                }
                if (!empty($student['mobileno'])) {
                    $phones[] = $student['mobileno'];
                }
            }
            
            // Get from custom mapping if configured
            if ($this->config['student_parent_mapping']['use_custom_mapping']) {
                $customPhones = $this->studentParentModel
                    ->where('student_id', $studentId)
                    ->where('status', 'active')
                    ->findColumn('phone_number');
                
                if ($customPhones) {
                    $phones = array_merge($phones, $customPhones);
                }
            }
            
            // Remove duplicates and empty values
            $phones = array_unique(array_filter($phones));
            
            return $phones;
            
        } catch (\Exception $e) {
            $this->logger->error('Error getting parent phones: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Determine notification type
     */
    protected function determineNotificationType($attendanceRecord)
    {
        $scanTime = date('H:i', strtotime($attendanceRecord['scan_date']));
        $entryStart = $this->config['school_hours']['entry_start'];
        $entryEnd = $this->config['school_hours']['entry_end'];
        $exitStart = $this->config['school_hours']['exit_start'];
        $exitEnd = $this->config['school_hours']['exit_end'];
        $lateThreshold = $this->config['school_hours']['late_threshold'];
        
        // Determine if it's entry or exit time
        if ($scanTime >= $entryStart && $scanTime <= $entryEnd) {
            // Entry time - check if late
            if ($scanTime <= $lateThreshold) {
                return 'entry';
            } else {
                return 'late';
            }
        } elseif ($scanTime >= $exitStart && $scanTime <= $exitEnd) {
            return 'exit';
        } else {
            return 'entry'; // Default to entry
        }
    }
    
    /**
     * Generate message based on type
     */
    protected function generateMessage($student, $attendanceRecord, $notificationType)
    {
        $templates = $this->config['notifications']['message_templates'];
        $template = $templates[$notificationType] ?? $templates['entry'];
        
        $scanDate = date('d/m/Y', strtotime($attendanceRecord['scan_date']));
        $scanTime = date('H:i', strtotime($attendanceRecord['scan_date']));
        
        $placeholders = [
            '{student_name}' => $student['full_name'],
            '{class}' => $student['class_name'],
            '{section}' => $student['section_name'],
            '{date}' => $scanDate,
            '{time}' => $scanTime,
            '{school_name}' => $this->config['school_info']['name'],
            '{school_phone}' => $this->config['school_info']['phone']
        ];
        
        return str_replace(array_keys($placeholders), array_values($placeholders), $template);
    }
    
    /**
     * Format phone number
     */
    protected function formatPhoneNumber($phone)
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Add country code if not present
        if (substr($phone, 0, 2) !== '62') {
            if (substr($phone, 0, 1) === '0') {
                $phone = '62' . substr($phone, 1);
            } else {
                $phone = '62' . $phone;
            }
        }
        
        return $phone;
    }
    
    /**
     * Check if notification is duplicate
     */
    protected function isDuplicateNotification($studentId, $scanDate, $phone, $notificationType)
    {
        try {
            $existing = $this->notificationLogModel
                ->where('student_id', $studentId)
                ->where('parent_phone', $phone)
                ->where('notification_type', $notificationType)
                ->where('DATE(scan_date)', date('Y-m-d', strtotime($scanDate)))
                ->where('status', 'sent')
                ->first();
            
            return !empty($existing);
            
        } catch (\Exception $e) {
            $this->logger->error('Error checking duplicate notification: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send daily absent notifications
     */
    public function sendDailyAbsentNotifications($date = null)
    {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        try {
            // Get students who are absent today
            $absentStudents = $this->getAbsentStudents($date);
            
            $result = [
                'success' => true,
                'date' => $date,
                'absent_students' => count($absentStudents),
                'notifications_sent' => 0,
                'notifications_failed' => 0,
                'errors' => []
            ];
            
            foreach ($absentStudents as $student) {
                try {
                    $attendanceRecord = [
                        'student_id' => $student['student_id'],
                        'scan_date' => $date . ' ' . $this->config['school_hours']['entry_end']
                    ];
                    
                    $notificationResult = $this->sendAbsentNotification($student, $attendanceRecord);
                    
                    if ($notificationResult['success']) {
                        $result['notifications_sent'] += $notificationResult['notifications_sent'];
                    } else {
                        $result['notifications_failed']++;
                        $result['errors'][] = 'Failed for student ' . $student['student_id'] . ': ' . ($notificationResult['error'] ?? 'Unknown error');
                    }
                    
                } catch (\Exception $e) {
                    $result['notifications_failed']++;
                    $result['errors'][] = 'Error for student ' . $student['student_id'] . ': ' . $e->getMessage();
                }
            }
            
            $this->logger->info('Daily absent notifications completed', $result);
            
            return $result;
            
        } catch (\Exception $e) {
            $this->logger->error('Daily absent notifications failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Send absent notification
     */
    protected function sendAbsentNotification($student, $attendanceRecord)
    {
        $parentPhones = $this->getParentPhones($student['student_id']);
        
        if (empty($parentPhones)) {
            return [
                'success' => false,
                'error' => 'No parent phone numbers found'
            ];
        }
        
        $message = $this->generateMessage($student, $attendanceRecord, 'absent');
        
        $result = [
            'success' => false,
            'notifications_sent' => 0,
            'notifications_failed' => 0
        ];
        
        foreach ($parentPhones as $phone) {
            $notificationResult = $this->sendSingleNotification($phone, $message, $student, $attendanceRecord, 'absent');
            
            if ($notificationResult['success']) {
                $result['notifications_sent']++;
            } else {
                $result['notifications_failed']++;
            }
        }
        
        $result['success'] = $result['notifications_sent'] > 0;
        
        return $result;
    }
    
    /**
     * Get absent students for a date
     */
    protected function getAbsentStudents($date)
    {
        try {
            // Get all active students
            $allStudents = $this->db->table('students')
                ->select('student_id, firstname, lastname, class_id, section_id')
                ->where('status', 'Active')
                ->get()
                ->getResultArray();
            
            // Get students who have attendance records for the date
            $presentStudents = $this->db->table('att_log')
                ->select('DISTINCT student_id')
                ->where('DATE(scan_date)', $date)
                ->get()
                ->getResultArray();
            
            $presentStudentIds = array_column($presentStudents, 'student_id');
            
            // Filter absent students
            $absentStudents = array_filter($allStudents, function($student) use ($presentStudentIds) {
                return !in_array($student['student_id'], $presentStudentIds);
            });
            
            return array_values($absentStudents);
            
        } catch (\Exception $e) {
            $this->logger->error('Error getting absent students: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Test notification functionality
     */
    public function testNotification($studentId, $phone = null)
    {
        try {
            $student = $this->getStudentInfo($studentId);
            
            if (!$student) {
                throw new \Exception('Student not found: ' . $studentId);
            }
            
            $testRecord = [
                'student_id' => $studentId,
                'scan_date' => date('Y-m-d H:i:s')
            ];
            
            $testPhone = $phone ?: $this->getParentPhones($studentId)[0] ?? null;
            
            if (!$testPhone) {
                throw new \Exception('No phone number available for testing');
            }
            
            $message = $this->generateMessage($student, $testRecord, 'entry') . ' [TEST MESSAGE]';
            
            $result = $this->sendSingleNotification($testPhone, $message, $student, $testRecord, 'test');
            
            return $result;
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get notification statistics
     */
    public function getNotificationStatistics($days = 7)
    {
        try {
            $startDate = date('Y-m-d', strtotime("-{$days} days"));
            
            $stats = $this->notificationLogModel
                ->select('DATE(created_at) as date, notification_type, status, COUNT(*) as count')
                ->where('created_at >=', $startDate)
                ->groupBy(['DATE(created_at)', 'notification_type', 'status'])
                ->orderBy('date', 'DESC')
                ->get()
                ->getResultArray();
            
            return $stats;
            
        } catch (\Exception $e) {
            $this->logger->error('Error getting notification statistics: ' . $e->getMessage());
            return [];
        }
    }
}