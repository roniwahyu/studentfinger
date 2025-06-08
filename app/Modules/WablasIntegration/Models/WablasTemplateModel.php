<?php

namespace App\Modules\WablasIntegration\Models;

use CodeIgniter\Model;

class WablasTemplateModel extends Model
{
    protected $table = 'wablas_templates';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'name',
        'description',
        'category',
        'message_type',
        'content',
        'media_url',
        'media_caption',
        'variables',
        'is_active',
        'usage_count',
        'last_used_at',
        'tags',
        'created_by',
        'updated_by'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    protected $validationRules = [
        'name' => 'required|max_length[100]',
        'message_type' => 'in_list[text,image,document,video,audio,location,list,button,template]',
        'content' => 'required',
        'is_active' => 'in_list[0,1]'
    ];
    
    protected $validationMessages = [
        'name' => [
            'required' => 'Template name is required'
        ],
        'content' => [
            'required' => 'Template content is required'
        ]
    ];
    
    protected $skipValidation = false;
    protected $cleanValidationRules = true;
    
    protected $allowCallbacks = true;
    protected $beforeInsert = ['beforeInsert'];
    protected $afterInsert = [];
    protected $beforeUpdate = ['beforeUpdate'];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = ['afterFind'];
    protected $beforeDelete = [];
    protected $afterDelete = [];
    
    /**
     * Get active templates
     */
    public function getActiveTemplates(): array
    {
        return $this->where('is_active', 1)
                   ->where('deleted_at', null)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get templates by category
     */
    public function getByCategory(string $category): array
    {
        return $this->where('category', $category)
                   ->where('deleted_at', null)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get templates by type
     */
    public function getByType(string $messageType): array
    {
        return $this->where('message_type', $messageType)
                   ->where('deleted_at', null)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }
    
    /**
     * Search templates
     */
    public function searchTemplates(string $query): array
    {
        return $this->groupStart()
                   ->like('name', $query)
                   ->orLike('description', $query)
                   ->orLike('content', $query)
                   ->orLike('category', $query)
                   ->groupEnd()
                   ->where('deleted_at', null)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get popular templates
     */
    public function getPopularTemplates(int $limit = 10): array
    {
        return $this->where('usage_count >', 0)
                   ->where('deleted_at', null)
                   ->orderBy('usage_count', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }
    
    /**
     * Get recent templates
     */
    public function getRecentTemplates(int $limit = 10): array
    {
        return $this->where('deleted_at', null)
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }
    
    /**
     * Get all categories
     */
    public function getCategories(): array
    {
        return $this->select('category')
                   ->where('category IS NOT NULL')
                   ->where('category !=', '')
                   ->where('deleted_at', null)
                   ->groupBy('category')
                   ->orderBy('category', 'ASC')
                   ->findColumn('category');
    }
    
    /**
     * Increment usage count
     */
    public function incrementUsage(int $templateId): bool
    {
        $template = $this->find($templateId);
        if (!$template) {
            return false;
        }
        
        return $this->update($templateId, [
            'usage_count' => $template['usage_count'] + 1,
            'last_used_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Duplicate template
     */
    public function duplicateTemplate(int $templateId, string $newName = null): ?int
    {
        $template = $this->find($templateId);
        if (!$template) {
            return null;
        }
        
        // Remove fields that shouldn't be duplicated
        unset($template['id'], $template['created_at'], $template['updated_at'], $template['deleted_at']);
        
        // Set new name
        if ($newName) {
            $template['name'] = $newName;
        } else {
            $template['name'] = $template['name'] . ' (Copy)';
        }
        
        // Reset usage stats
        $template['usage_count'] = 0;
        $template['last_used_at'] = null;
        
        return $this->insert($template);
    }
    
    /**
     * Process template variables
     */
    public function processTemplate(int $templateId, array $variables = []): ?array
    {
        $template = $this->find($templateId);
        if (!$template) {
            return null;
        }
        
        $content = $template['content'];
        $mediaCaption = $template['media_caption'];
        
        // Replace variables in content
        foreach ($variables as $key => $value) {
            $placeholder = '{' . $key . '}';
            $content = str_replace($placeholder, $value, $content);
            if ($mediaCaption) {
                $mediaCaption = str_replace($placeholder, $value, $mediaCaption);
            }
        }
        
        // Replace default variables
        $defaultVariables = [
            '{date}' => date('Y-m-d'),
            '{time}' => date('H:i'),
            '{datetime}' => date('Y-m-d H:i:s'),
            '{day}' => date('l'),
            '{month}' => date('F'),
            '{year}' => date('Y')
        ];
        
        foreach ($defaultVariables as $key => $value) {
            $content = str_replace($key, $value, $content);
            if ($mediaCaption) {
                $mediaCaption = str_replace($key, $value, $mediaCaption);
            }
        }
        
        return [
            'id' => $template['id'],
            'name' => $template['name'],
            'message_type' => $template['message_type'],
            'content' => $content,
            'media_url' => $template['media_url'],
            'media_caption' => $mediaCaption,
            'variables' => $template['variables']
        ];
    }
    
    /**
     * Extract variables from template content
     */
    public function extractVariables(string $content): array
    {
        preg_match_all('/\{([^}]+)\}/', $content, $matches);
        return array_unique($matches[1]);
    }
    
    /**
     * Get template statistics
     */
    public function getStatistics(): array
    {
        $total = $this->where('deleted_at', null)->countAllResults();
        $active = $this->where('is_active', 1)->where('deleted_at', null)->countAllResults();
        $used = $this->where('usage_count >', 0)->where('deleted_at', null)->countAllResults();
        
        $typeStats = $this->select('message_type, COUNT(*) as count')
                         ->where('deleted_at', null)
                         ->groupBy('message_type')
                         ->findAll();
        
        $categoryStats = $this->select('category, COUNT(*) as count')
                             ->where('category IS NOT NULL')
                             ->where('category !=', '')
                             ->where('deleted_at', null)
                             ->groupBy('category')
                             ->findAll();
        
        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $total - $active,
            'used' => $used,
            'unused' => $total - $used,
            'by_type' => $typeStats,
            'by_category' => $categoryStats
        ];
    }
    
    /**
     * Before insert callback
     */
    protected function beforeInsert(array $data): array
    {
        if (isset($data['data']['variables']) && is_array($data['data']['variables'])) {
            $data['data']['variables'] = json_encode($data['data']['variables']);
        }
        
        if (isset($data['data']['tags']) && is_array($data['data']['tags'])) {
            $data['data']['tags'] = json_encode($data['data']['tags']);
        }
        
        // Extract variables from content
        if (isset($data['data']['content'])) {
            $extractedVars = $this->extractVariables($data['data']['content']);
            if (!isset($data['data']['variables']) && !empty($extractedVars)) {
                $data['data']['variables'] = json_encode($extractedVars);
            }
        }
        
        // Set default values
        if (!isset($data['data']['message_type'])) {
            $data['data']['message_type'] = 'text';
        }
        
        if (!isset($data['data']['is_active'])) {
            $data['data']['is_active'] = 1;
        }
        
        if (!isset($data['data']['usage_count'])) {
            $data['data']['usage_count'] = 0;
        }
        
        return $data;
    }
    
    /**
     * Before update callback
     */
    protected function beforeUpdate(array $data): array
    {
        if (isset($data['data']['variables']) && is_array($data['data']['variables'])) {
            $data['data']['variables'] = json_encode($data['data']['variables']);
        }
        
        if (isset($data['data']['tags']) && is_array($data['data']['tags'])) {
            $data['data']['tags'] = json_encode($data['data']['tags']);
        }
        
        // Extract variables from content if content is being updated
        if (isset($data['data']['content'])) {
            $extractedVars = $this->extractVariables($data['data']['content']);
            if (!empty($extractedVars)) {
                $data['data']['variables'] = json_encode($extractedVars);
            }
        }
        
        return $data;
    }
    
    /**
     * After find callback
     */
    protected function afterFind(array $data): array
    {
        if (isset($data['data'])) {
            // Single record
            $this->decodeJsonFields($data['data']);
        } else {
            // Multiple records
            foreach ($data as &$record) {
                $this->decodeJsonFields($record);
            }
        }
        
        return $data;
    }
    
    /**
     * Decode JSON fields
     */
    protected function decodeJsonFields(array &$record): void
    {
        if (isset($record['variables']) && is_string($record['variables'])) {
            $record['variables'] = json_decode($record['variables'], true) ?? [];
        }
        
        if (isset($record['tags']) && is_string($record['tags'])) {
            $record['tags'] = json_decode($record['tags'], true) ?? [];
        }
    }
}
