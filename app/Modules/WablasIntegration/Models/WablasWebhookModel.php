<?php

namespace App\Modules\WablasIntegration\Models;

use CodeIgniter\Model;

class WablasWebhookModel extends Model
{
    protected $table = 'wablas_webhooks';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'name',
        'endpoint',
        'type',
        'method',
        'is_active',
        'secret_token',
        'headers',
        'timeout',
        'retry_attempts',
        'retry_delay',
        'last_called_at',
        'last_response_code',
        'last_response_body',
        'success_count',
        'failure_count',
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
        'endpoint' => 'required|max_length[255]|is_unique[wablas_webhooks.endpoint,id,{id}]',
        'type' => 'required|in_list[incoming,status,device]',
        'method' => 'in_list[GET,POST,PUT,DELETE]',
        'is_active' => 'in_list[0,1]',
        'timeout' => 'integer|greater_than[0]',
        'retry_attempts' => 'integer|greater_than_equal_to[0]',
        'retry_delay' => 'integer|greater_than_equal_to[0]'
    ];
    
    protected $validationMessages = [
        'name' => [
            'required' => 'Webhook name is required'
        ],
        'endpoint' => [
            'required' => 'Webhook endpoint is required',
            'is_unique' => 'Webhook endpoint already exists'
        ],
        'type' => [
            'required' => 'Webhook type is required'
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
     * Get active webhooks
     */
    public function getActiveWebhooks(): array
    {
        return $this->where('is_active', 1)
                   ->where('deleted_at', null)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get webhooks by type
     */
    public function getByType(string $type): array
    {
        return $this->where('type', $type)
                   ->where('deleted_at', null)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get webhook by endpoint
     */
    public function getByEndpoint(string $endpoint): ?array
    {
        return $this->where('endpoint', $endpoint)
                   ->where('deleted_at', null)
                   ->first();
    }
    
    /**
     * Get active webhooks by type
     */
    public function getActiveByType(string $type): array
    {
        return $this->where('type', $type)
                   ->where('is_active', 1)
                   ->where('deleted_at', null)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }
    
    /**
     * Update webhook statistics
     */
    public function updateStats(int $webhookId, bool $success, int $responseCode = null, string $responseBody = null): bool
    {
        $webhook = $this->find($webhookId);
        if (!$webhook) {
            return false;
        }
        
        $updateData = [
            'last_called_at' => date('Y-m-d H:i:s'),
            'last_response_code' => $responseCode,
            'last_response_body' => $responseBody
        ];
        
        if ($success) {
            $updateData['success_count'] = $webhook['success_count'] + 1;
        } else {
            $updateData['failure_count'] = $webhook['failure_count'] + 1;
        }
        
        return $this->update($webhookId, $updateData);
    }
    
    /**
     * Get webhook statistics
     */
    public function getStatistics(): array
    {
        $total = $this->where('deleted_at', null)->countAllResults();
        $active = $this->where('is_active', 1)->where('deleted_at', null)->countAllResults();
        
        $typeStats = $this->select('type, COUNT(*) as count')
                         ->where('deleted_at', null)
                         ->groupBy('type')
                         ->findAll();
        
        $successRate = $this->select('
            SUM(success_count) as total_success,
            SUM(failure_count) as total_failure,
            (SUM(success_count) / (SUM(success_count) + SUM(failure_count)) * 100) as success_rate
        ')
        ->where('deleted_at', null)
        ->first();
        
        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $total - $active,
            'by_type' => $typeStats,
            'success_rate' => $successRate['success_rate'] ?? 0,
            'total_calls' => ($successRate['total_success'] ?? 0) + ($successRate['total_failure'] ?? 0)
        ];
    }
    
    /**
     * Test webhook
     */
    public function testWebhook(int $webhookId, array $testData = []): array
    {
        $webhook = $this->find($webhookId);
        if (!$webhook) {
            return ['success' => false, 'error' => 'Webhook not found'];
        }
        
        if (!$webhook['is_active']) {
            return ['success' => false, 'error' => 'Webhook is not active'];
        }
        
        // Default test data
        if (empty($testData)) {
            $testData = [
                'test' => true,
                'webhook_id' => $webhookId,
                'timestamp' => date('Y-m-d H:i:s'),
                'message' => 'This is a test webhook call'
            ];
        }
        
        try {
            $startTime = microtime(true);
            
            // Prepare headers
            $headers = $webhook['headers'] ?? [];
            $headers['Content-Type'] = 'application/json';
            
            if ($webhook['secret_token']) {
                $headers['Authorization'] = 'Bearer ' . $webhook['secret_token'];
            }
            
            // Make HTTP request
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $webhook['endpoint'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $webhook['timeout'],
                CURLOPT_CUSTOMREQUEST => $webhook['method'],
                CURLOPT_POSTFIELDS => json_encode($testData),
                CURLOPT_HTTPHEADER => $this->formatHeaders($headers),
                CURLOPT_SSL_VERIFYPEER => false
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            $executionTime = microtime(true) - $startTime;
            
            if ($error) {
                $this->updateStats($webhookId, false, 0, $error);
                return [
                    'success' => false,
                    'error' => $error,
                    'execution_time' => $executionTime
                ];
            }
            
            $success = $httpCode >= 200 && $httpCode < 300;
            $this->updateStats($webhookId, $success, $httpCode, $response);
            
            return [
                'success' => $success,
                'http_code' => $httpCode,
                'response' => $response,
                'execution_time' => $executionTime
            ];
            
        } catch (\Exception $e) {
            $this->updateStats($webhookId, false, 0, $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Call webhook
     */
    public function callWebhook(int $webhookId, array $data): array
    {
        $webhook = $this->find($webhookId);
        if (!$webhook || !$webhook['is_active']) {
            return ['success' => false, 'error' => 'Webhook not found or inactive'];
        }
        
        $attempts = 0;
        $maxAttempts = $webhook['retry_attempts'] + 1;
        
        while ($attempts < $maxAttempts) {
            try {
                $startTime = microtime(true);
                
                // Prepare headers
                $headers = $webhook['headers'] ?? [];
                $headers['Content-Type'] = 'application/json';
                
                if ($webhook['secret_token']) {
                    $headers['Authorization'] = 'Bearer ' . $webhook['secret_token'];
                }
                
                // Make HTTP request
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $webhook['endpoint'],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => $webhook['timeout'],
                    CURLOPT_CUSTOMREQUEST => $webhook['method'],
                    CURLOPT_POSTFIELDS => json_encode($data),
                    CURLOPT_HTTPHEADER => $this->formatHeaders($headers),
                    CURLOPT_SSL_VERIFYPEER => false
                ]);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                curl_close($ch);
                
                $executionTime = microtime(true) - $startTime;
                
                if ($error) {
                    throw new \Exception($error);
                }
                
                $success = $httpCode >= 200 && $httpCode < 300;
                
                if ($success) {
                    $this->updateStats($webhookId, true, $httpCode, $response);
                    return [
                        'success' => true,
                        'http_code' => $httpCode,
                        'response' => $response,
                        'execution_time' => $executionTime,
                        'attempts' => $attempts + 1
                    ];
                } else {
                    throw new \Exception("HTTP {$httpCode}: {$response}");
                }
                
            } catch (\Exception $e) {
                $attempts++;
                
                if ($attempts >= $maxAttempts) {
                    $this->updateStats($webhookId, false, $httpCode ?? 0, $e->getMessage());
                    return [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'attempts' => $attempts
                    ];
                }
                
                // Wait before retry
                if ($webhook['retry_delay'] > 0) {
                    sleep($webhook['retry_delay']);
                }
            }
        }
        
        return ['success' => false, 'error' => 'Max attempts reached'];
    }
    
    /**
     * Format headers for cURL
     */
    protected function formatHeaders(array $headers): array
    {
        $formatted = [];
        foreach ($headers as $key => $value) {
            $formatted[] = "{$key}: {$value}";
        }
        return $formatted;
    }
    
    /**
     * Before insert callback
     */
    protected function beforeInsert(array $data): array
    {
        if (isset($data['data']['headers']) && is_array($data['data']['headers'])) {
            $data['data']['headers'] = json_encode($data['data']['headers']);
        }
        
        // Set default values
        if (!isset($data['data']['method'])) {
            $data['data']['method'] = 'POST';
        }
        
        if (!isset($data['data']['timeout'])) {
            $data['data']['timeout'] = 30;
        }
        
        if (!isset($data['data']['retry_attempts'])) {
            $data['data']['retry_attempts'] = 3;
        }
        
        if (!isset($data['data']['retry_delay'])) {
            $data['data']['retry_delay'] = 5;
        }
        
        if (!isset($data['data']['success_count'])) {
            $data['data']['success_count'] = 0;
        }
        
        if (!isset($data['data']['failure_count'])) {
            $data['data']['failure_count'] = 0;
        }
        
        return $data;
    }
    
    /**
     * Before update callback
     */
    protected function beforeUpdate(array $data): array
    {
        if (isset($data['data']['headers']) && is_array($data['data']['headers'])) {
            $data['data']['headers'] = json_encode($data['data']['headers']);
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
            if (isset($data['data']['headers']) && is_string($data['data']['headers'])) {
                $data['data']['headers'] = json_decode($data['data']['headers'], true) ?? [];
            }
        } else {
            // Multiple records
            foreach ($data as &$record) {
                if (isset($record['headers']) && is_string($record['headers'])) {
                    $record['headers'] = json_decode($record['headers'], true) ?? [];
                }
            }
        }
        
        return $data;
    }
}
