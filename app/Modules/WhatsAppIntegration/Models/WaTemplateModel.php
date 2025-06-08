<?php

namespace App\Modules\WhatsAppIntegration\Models;

use CodeIgniter\Model;

class WaTemplateModel extends Model
{
    protected $table = 'wa_templates';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'template_name',
        'template_content',
        'template_type',
        'variables',
        'is_active',
        'created_at',
        'updated_at'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $validationRules = [
        'template_name' => 'required|min_length[3]|max_length[100]',
        'template_content' => 'required|min_length[10]',
        'template_type' => 'required|in_list[attendance,notification,reminder,general]'
    ];

    /**
     * Get active templates
     */
    public function getActiveTemplates()
    {
        return $this->where('is_active', 1)
                   ->orderBy('template_name', 'ASC')
                   ->findAll();
    }

    /**
     * Get templates by type
     */
    public function getTemplatesByType($type)
    {
        return $this->where('template_type', $type)
                   ->where('is_active', 1)
                   ->orderBy('template_name', 'ASC')
                   ->findAll();
    }

    /**
     * Get attendance templates
     */
    public function getAttendanceTemplates()
    {
        return $this->getTemplatesByType('attendance');
    }

    /**
     * Process template with variables
     */
    public function processTemplate($templateId, $variables = [])
    {
        $template = $this->find($templateId);
        if (!$template) {
            throw new \Exception('Template not found');
        }

        $content = $template['template_content'];
        $templateVariables = json_decode($template['variables'] ?? '[]', true);

        // Default variables
        $defaultVariables = [
            '{school_name}' => 'Student Attendance System',
            '{date}' => date('Y-m-d'),
            '{time}' => date('H:i'),
            '{day}' => date('l'),
            '{month}' => date('F'),
            '{year}' => date('Y')
        ];

        // Merge with provided variables
        $allVariables = array_merge($defaultVariables, $variables);

        // Replace variables in content
        foreach ($allVariables as $key => $value) {
            $content = str_replace($key, $value, $content);
        }

        return $content;
    }

    /**
     * Create default templates
     */
    public function createDefaultTemplates()
    {
        $defaultTemplates = [
            [
                'template_name' => 'Student Check-in Notification',
                'template_content' => 'Hello {parent_name}, your child {student_name} has arrived at school at {time} on {date}. Have a great day!',
                'template_type' => 'attendance',
                'variables' => json_encode(['{parent_name}', '{student_name}', '{time}', '{date}']),
                'is_active' => 1
            ],
            [
                'template_name' => 'Student Check-out Notification',
                'template_content' => 'Hello {parent_name}, your child {student_name} has left school at {time} on {date}. Please ensure safe arrival home.',
                'template_type' => 'attendance',
                'variables' => json_encode(['{parent_name}', '{student_name}', '{time}', '{date}']),
                'is_active' => 1
            ],
            [
                'template_name' => 'Absence Notification',
                'template_content' => 'Dear {parent_name}, we noticed that {student_name} is absent today ({date}). Please contact the school if this is an emergency.',
                'template_type' => 'attendance',
                'variables' => json_encode(['{parent_name}', '{student_name}', '{date}']),
                'is_active' => 1
            ],
            [
                'template_name' => 'Late Arrival Warning',
                'template_content' => 'Hello {parent_name}, {student_name} arrived late at {time} on {date}. Please ensure punctual arrival to avoid missing important lessons.',
                'template_type' => 'attendance',
                'variables' => json_encode(['{parent_name}', '{student_name}', '{time}', '{date}']),
                'is_active' => 1
            ],
            [
                'template_name' => 'General Announcement',
                'template_content' => 'Dear Parents/Students, {announcement_text}. Thank you for your attention. - {school_name}',
                'template_type' => 'notification',
                'variables' => json_encode(['{announcement_text}', '{school_name}']),
                'is_active' => 1
            ],
            [
                'template_name' => 'Event Reminder',
                'template_content' => 'Reminder: {event_name} is scheduled for {event_date} at {event_time}. {event_details}. Please mark your calendar!',
                'template_type' => 'reminder',
                'variables' => json_encode(['{event_name}', '{event_date}', '{event_time}', '{event_details}']),
                'is_active' => 1
            ],
            [
                'template_name' => 'Fee Payment Reminder',
                'template_content' => 'Dear {parent_name}, this is a reminder that the school fee payment for {student_name} is due on {due_date}. Amount: {amount}. Please make payment to avoid late fees.',
                'template_type' => 'reminder',
                'variables' => json_encode(['{parent_name}', '{student_name}', '{due_date}', '{amount}']),
                'is_active' => 1
            ]
        ];

        $createdCount = 0;
        foreach ($defaultTemplates as $template) {
            // Check if template already exists
            $existing = $this->where('template_name', $template['template_name'])->first();
            if (!$existing) {
                $template['created_at'] = date('Y-m-d H:i:s');
                $template['updated_at'] = date('Y-m-d H:i:s');
                $this->insert($template);
                $createdCount++;
            }
        }

        return $createdCount;
    }

    /**
     * Get template variables
     */
    public function getTemplateVariables($templateId)
    {
        $template = $this->find($templateId);
        if (!$template) {
            return [];
        }

        return json_decode($template['variables'] ?? '[]', true);
    }

    /**
     * Extract variables from template content
     */
    public function extractVariables($content)
    {
        preg_match_all('/\{([^}]+)\}/', $content, $matches);
        return array_unique($matches[0]);
    }

    /**
     * Validate template content
     */
    public function validateTemplate($content, $variables = [])
    {
        $extractedVars = $this->extractVariables($content);
        $errors = [];

        // Check for undefined variables
        foreach ($extractedVars as $var) {
            if (!in_array($var, $variables) && !$this->isDefaultVariable($var)) {
                $errors[] = "Undefined variable: {$var}";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'variables' => $extractedVars
        ];
    }

    /**
     * Check if variable is a default system variable
     */
    private function isDefaultVariable($variable)
    {
        $defaultVars = [
            '{school_name}', '{date}', '{time}', '{day}', '{month}', '{year}'
        ];

        return in_array($variable, $defaultVars);
    }

    /**
     * Clone template
     */
    public function cloneTemplate($templateId, $newName)
    {
        $template = $this->find($templateId);
        if (!$template) {
            throw new \Exception('Template not found');
        }

        unset($template['id']);
        $template['template_name'] = $newName;
        $template['created_at'] = date('Y-m-d H:i:s');
        $template['updated_at'] = date('Y-m-d H:i:s');

        return $this->insert($template);
    }

    /**
     * Get template usage statistics
     */
    public function getTemplateUsage($templateId = null, $days = 30)
    {
        $db = \Config\Database::connect();
        
        $builder = $db->table('wa_messages m')
                     ->select('COUNT(*) as usage_count')
                     ->where('DATE(m.created_at) >=', date('Y-m-d', strtotime("-{$days} days")));

        if ($templateId) {
            $builder->where('JSON_EXTRACT(m.api_response, "$.template_id")', $templateId);
        }

        $result = $builder->get()->getRowArray();
        return $result['usage_count'] ?? 0;
    }

    /**
     * Get most used templates
     */
    public function getMostUsedTemplates($limit = 5, $days = 30)
    {
        $db = \Config\Database::connect();
        
        return $db->table($this->table . ' t')
                 ->select('t.*, COUNT(m.id) as usage_count')
                 ->join('wa_messages m', 'JSON_EXTRACT(m.api_response, "$.template_id") = t.id', 'left')
                 ->where('DATE(m.created_at) >=', date('Y-m-d', strtotime("-{$days} days")))
                 ->groupBy('t.id')
                 ->orderBy('usage_count', 'DESC')
                 ->limit($limit)
                 ->get()
                 ->getResultArray();
    }

    /**
     * Search templates
     */
    public function searchTemplates($query)
    {
        return $this->groupStart()
                   ->like('template_name', $query)
                   ->orLike('template_content', $query)
                   ->groupEnd()
                   ->where('is_active', 1)
                   ->orderBy('template_name', 'ASC')
                   ->findAll();
    }

    /**
     * Toggle template status
     */
    public function toggleStatus($templateId)
    {
        $template = $this->find($templateId);
        if (!$template) {
            return false;
        }

        $newStatus = $template['is_active'] == 1 ? 0 : 1;
        
        return $this->update($templateId, [
            'is_active' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get template statistics
     */
    public function getTemplateStats()
    {
        $total = $this->countAllResults();
        $active = $this->where('is_active', 1)->countAllResults();
        $inactive = $total - $active;

        $byType = $this->select('template_type, COUNT(*) as count')
                      ->where('is_active', 1)
                      ->groupBy('template_type')
                      ->findAll();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'by_type' => $byType
        ];
    }
}
