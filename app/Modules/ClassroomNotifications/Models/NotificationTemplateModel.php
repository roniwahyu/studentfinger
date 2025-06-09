<?php

namespace App\Modules\ClassroomNotifications\Models;

use CodeIgniter\Model;

/**
 * Notification Template Model
 * 
 * Manages WhatsApp notification templates for different classroom events
 */
class NotificationTemplateModel extends Model
{
    protected $table = 'notification_templates';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'template_name',
        'event_type',
        'message_template',
        'is_active',
        'language',
        'variables',
        'description'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    protected $validationRules = [
        'template_name' => 'required|min_length[3]|max_length[100]',
        'event_type' => 'required|in_list[session_start,session_break,session_resume,session_finish,attendance_marked]',
        'message_template' => 'required|min_length[10]',
        'language' => 'required|in_list[id,en]'
    ];
    
    // Event type constants
    const EVENT_SESSION_START = 'session_start';
    const EVENT_SESSION_BREAK = 'session_break';
    const EVENT_SESSION_RESUME = 'session_resume';
    const EVENT_SESSION_FINISH = 'session_finish';
    const EVENT_ATTENDANCE_MARKED = 'attendance_marked';
    
    /**
     * Get active templates by event type
     */
    public function getActiveTemplatesByEvent(string $eventType): array
    {
        return $this->where('event_type', $eventType)
                    ->where('is_active', 1)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
    
    /**
     * Get default template for event type
     */
    public function getDefaultTemplate(string $eventType, string $language = 'id'): ?array
    {
        $template = $this->where('event_type', $eventType)
                         ->where('language', $language)
                         ->where('is_active', 1)
                         ->orderBy('created_at', 'ASC')
                         ->first();
        
        return $template ?: $this->createDefaultTemplate($eventType, $language);
    }
    
    /**
     * Create default templates
     */
    public function createDefaultTemplate(string $eventType, string $language = 'id'): ?array
    {
        $templates = $this->getDefaultTemplates();
        
        if (isset($templates[$eventType][$language])) {
            $templateData = $templates[$eventType][$language];
            $templateData['event_type'] = $eventType;
            $templateData['language'] = $language;
            $templateData['is_active'] = 1;
            
            $id = $this->insert($templateData);
            if ($id) {
                return $this->find($id);
            }
        }
        
        return null;
    }
    
    /**
     * Get default template definitions
     */
    protected function getDefaultTemplates(): array
    {
        return [
            self::EVENT_SESSION_START => [
                'id' => [
                    'template_name' => 'Kelas Dimulai - Default',
                    'message_template' => "🎓 *KELAS DIMULAI*\n\nYth. Orang Tua/Wali {parent_name},\n\nKami informasikan bahwa {student_name} telah hadir di kelas:\n\n📚 *Mata Pelajaran:* {subject}\n🏫 *Kelas:* {class_name}\n👨‍🏫 *Guru:* {teacher_name}\n⏰ *Waktu Mulai:* {start_time}\n📅 *Tanggal:* {session_date}\n\nTerima kasih atas perhatiannya.\n\n*{school_name}*",
                    'description' => 'Template notifikasi saat kelas dimulai',
                    'variables' => json_encode(['parent_name', 'student_name', 'subject', 'class_name', 'teacher_name', 'start_time', 'session_date', 'school_name'])
                ],
                'en' => [
                    'template_name' => 'Class Started - Default',
                    'message_template' => "🎓 *CLASS STARTED*\n\nDear Parent/Guardian {parent_name},\n\nWe inform you that {student_name} is attending class:\n\n📚 *Subject:* {subject}\n🏫 *Class:* {class_name}\n👨‍🏫 *Teacher:* {teacher_name}\n⏰ *Start Time:* {start_time}\n📅 *Date:* {session_date}\n\nThank you for your attention.\n\n*{school_name}*",
                    'description' => 'Notification template when class starts',
                    'variables' => json_encode(['parent_name', 'student_name', 'subject', 'class_name', 'teacher_name', 'start_time', 'session_date', 'school_name'])
                ]
            ],
            self::EVENT_SESSION_BREAK => [
                'id' => [
                    'template_name' => 'Istirahat Kelas - Default',
                    'message_template' => "☕ *ISTIRAHAT KELAS*\n\nYth. Orang Tua/Wali {parent_name},\n\nKelas {subject} sedang istirahat:\n\n👤 *Siswa:* {student_name}\n🏫 *Kelas:* {class_name}\n⏰ *Waktu Istirahat:* {break_time}\n⏱️ *Durasi:* {break_duration} menit\n\nKelas akan dilanjutkan setelah istirahat.\n\n*{school_name}*",
                    'description' => 'Template notifikasi saat kelas istirahat',
                    'variables' => json_encode(['parent_name', 'student_name', 'subject', 'class_name', 'break_time', 'break_duration', 'school_name'])
                ],
                'en' => [
                    'template_name' => 'Class Break - Default',
                    'message_template' => "☕ *CLASS BREAK*\n\nDear Parent/Guardian {parent_name},\n\n{subject} class is on break:\n\n👤 *Student:* {student_name}\n🏫 *Class:* {class_name}\n⏰ *Break Time:* {break_time}\n⏱️ *Duration:* {break_duration} minutes\n\nClass will resume after the break.\n\n*{school_name}*",
                    'description' => 'Notification template when class is on break',
                    'variables' => json_encode(['parent_name', 'student_name', 'subject', 'class_name', 'break_time', 'break_duration', 'school_name'])
                ]
            ],
            self::EVENT_SESSION_RESUME => [
                'id' => [
                    'template_name' => 'Kelas Dilanjutkan - Default',
                    'message_template' => "📚 *KELAS DILANJUTKAN*\n\nYth. Orang Tua/Wali {parent_name},\n\nKelas {subject} telah dilanjutkan setelah istirahat:\n\n👤 *Siswa:* {student_name}\n🏫 *Kelas:* {class_name}\n⏰ *Waktu Lanjut:* {resume_time}\n\nTerima kasih atas perhatiannya.\n\n*{school_name}*",
                    'description' => 'Template notifikasi saat kelas dilanjutkan',
                    'variables' => json_encode(['parent_name', 'student_name', 'subject', 'class_name', 'resume_time', 'school_name'])
                ],
                'en' => [
                    'template_name' => 'Class Resumed - Default',
                    'message_template' => "📚 *CLASS RESUMED*\n\nDear Parent/Guardian {parent_name},\n\n{subject} class has resumed after break:\n\n👤 *Student:* {student_name}\n🏫 *Class:* {class_name}\n⏰ *Resume Time:* {resume_time}\n\nThank you for your attention.\n\n*{school_name}*",
                    'description' => 'Notification template when class resumes',
                    'variables' => json_encode(['parent_name', 'student_name', 'subject', 'class_name', 'resume_time', 'school_name'])
                ]
            ],
            self::EVENT_SESSION_FINISH => [
                'id' => [
                    'template_name' => 'Kelas Selesai - Default',
                    'message_template' => "✅ *KELAS SELESAI*\n\nYth. Orang Tua/Wali {parent_name},\n\nKelas {subject} telah selesai:\n\n👤 *Siswa:* {student_name}\n🏫 *Kelas:* {class_name}\n👨‍🏫 *Guru:* {teacher_name}\n⏰ *Waktu Selesai:* {end_time}\n⏱️ *Durasi Total:* {total_duration}\n\n{student_name} dapat dijemput atau pulang sesuai jadwal.\n\n*{school_name}*",
                    'description' => 'Template notifikasi saat kelas selesai',
                    'variables' => json_encode(['parent_name', 'student_name', 'subject', 'class_name', 'teacher_name', 'end_time', 'total_duration', 'school_name'])
                ],
                'en' => [
                    'template_name' => 'Class Finished - Default',
                    'message_template' => "✅ *CLASS FINISHED*\n\nDear Parent/Guardian {parent_name},\n\n{subject} class has finished:\n\n👤 *Student:* {student_name}\n🏫 *Class:* {class_name}\n👨‍🏫 *Teacher:* {teacher_name}\n⏰ *End Time:* {end_time}\n⏱️ *Total Duration:* {total_duration}\n\n{student_name} can be picked up or go home as scheduled.\n\n*{school_name}*",
                    'description' => 'Notification template when class finishes',
                    'variables' => json_encode(['parent_name', 'student_name', 'subject', 'class_name', 'teacher_name', 'end_time', 'total_duration', 'school_name'])
                ]
            ]
        ];
    }
    
    /**
     * Process template variables
     */
    public function processTemplate(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        
        return $template;
    }
    
    /**
     * Get available variables for event type
     */
    public function getAvailableVariables(string $eventType): array
    {
        $commonVars = ['student_name', 'parent_name', 'class_name', 'school_name', 'session_date'];
        
        $eventSpecificVars = [
            self::EVENT_SESSION_START => ['subject', 'teacher_name', 'start_time'],
            self::EVENT_SESSION_BREAK => ['subject', 'break_time', 'break_duration'],
            self::EVENT_SESSION_RESUME => ['subject', 'resume_time'],
            self::EVENT_SESSION_FINISH => ['subject', 'teacher_name', 'end_time', 'total_duration']
        ];
        
        return array_merge($commonVars, $eventSpecificVars[$eventType] ?? []);
    }
    
    /**
     * Validate template variables
     */
    public function validateTemplate(string $template, string $eventType): array
    {
        $availableVars = $this->getAvailableVariables($eventType);
        $usedVars = [];
        $invalidVars = [];
        
        // Extract variables from template
        preg_match_all('/\{([^}]+)\}/', $template, $matches);
        
        if (!empty($matches[1])) {
            foreach ($matches[1] as $var) {
                if (in_array($var, $availableVars)) {
                    $usedVars[] = $var;
                } else {
                    $invalidVars[] = $var;
                }
            }
        }
        
        return [
            'valid' => empty($invalidVars),
            'used_variables' => array_unique($usedVars),
            'invalid_variables' => array_unique($invalidVars),
            'available_variables' => $availableVars
        ];
    }
}
