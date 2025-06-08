<?php

namespace App\Modules\WablasIntegration\Models;

use CodeIgniter\Model;

class WablasLogModel extends Model
{
    protected $table = 'wablas_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'device_id',
        'message_id',
        'log_type',
        'action',
        'description',
        'request_data',
        'response_data',
        'http_status_code',
        'execution_time',
        'ip_address',
        'user_agent',
        'error_code',
        'error_message',
        'stack_trace',
        'context',
        'created_by'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = false;
    protected $deletedField = false;
    
    protected $validationRules = [
        'log_type' => 'in_list[api_call,webhook,error,info,warning,debug]',
        'action' => 'required|max_length[100]',
        'http_status_code' => 'integer',
        'execution_time' => 'decimal'
    ];
    
    protected $validationMessages = [
        'action' => [
            'required' => 'Action is required'
        ]
    ];
    
    protected $skipValidation = false;
    protected $cleanValidationRules = true;
    
    protected $allowCallbacks = true;
    protected $beforeInsert = ['beforeInsert'];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = ['afterFind'];
    protected $beforeDelete = [];
    protected $afterDelete = [];
    
    /**
     * Get logs by device
     */
    public function getByDevice(int $deviceId, int $limit = 100): array
    {
        return $this->where('device_id', $deviceId)
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }
    
    /**
     * Get logs by message
     */
    public function getByMessage(int $messageId): array
    {
        return $this->where('message_id', $messageId)
                   ->orderBy('created_at', 'ASC')
                   ->findAll();
    }
    
    /**
     * Get logs by type
     */
    public function getByType(string $logType, int $limit = 100): array
    {
        return $this->where('log_type', $logType)
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }
    
    /**
     * Get error logs
     */
    public function getErrorLogs(int $limit = 100): array
    {
        return $this->where('log_type', 'error')
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }
    
    /**
     * Get API call logs
     */
    public function getApiCallLogs(int $deviceId = null, int $limit = 100): array
    {
        $builder = $this->where('log_type', 'api_call');
        
        if ($deviceId) {
            $builder->where('device_id', $deviceId);
        }
        
        return $builder->orderBy('created_at', 'DESC')
                      ->limit($limit)
                      ->findAll();
    }
    
    /**
     * Get webhook logs
     */
    public function getWebhookLogs(int $limit = 100): array
    {
        return $this->where('log_type', 'webhook')
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }
    
    /**
     * Get recent logs
     */
    public function getRecentLogs(int $limit = 50): array
    {
        return $this->select('wablas_logs.*, wablas_devices.device_name')
                   ->join('wablas_devices', 'wablas_devices.id = wablas_logs.device_id', 'left')
                   ->orderBy('wablas_logs.created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }
    
    /**
     * Get logs by date range
     */
    public function getByDateRange(string $startDate, string $endDate, string $logType = null): array
    {
        $builder = $this->where('created_at >=', $startDate)
                       ->where('created_at <=', $endDate);
        
        if ($logType) {
            $builder->where('log_type', $logType);
        }
        
        return $builder->orderBy('created_at', 'DESC')->findAll();
    }
    
    /**
     * Get log statistics
     */
    public function getStatistics(string $period = 'today'): array
    {
        $builder = $this;
        
        // Apply date filter
        switch ($period) {
            case 'today':
                $builder->where('DATE(created_at)', date('Y-m-d'));
                break;
            case 'yesterday':
                $builder->where('DATE(created_at)', date('Y-m-d', strtotime('-1 day')));
                break;
            case 'this_week':
                $builder->where('WEEK(created_at)', date('W'));
                break;
            case 'this_month':
                $builder->where('MONTH(created_at)', date('m'))
                       ->where('YEAR(created_at)', date('Y'));
                break;
        }
        
        $total = $builder->countAllResults(false);
        $errors = $builder->where('log_type', 'error')->countAllResults(false);
        $apiCalls = $builder->where('log_type', 'api_call')->countAllResults(false);
        $webhooks = $builder->where('log_type', 'webhook')->countAllResults(false);
        $warnings = $builder->where('log_type', 'warning')->countAllResults();
        
        return [
            'total' => $total,
            'errors' => $errors,
            'api_calls' => $apiCalls,
            'webhooks' => $webhooks,
            'warnings' => $warnings,
            'info' => $total - $errors - $apiCalls - $webhooks - $warnings
        ];
    }
    
    /**
     * Log API call
     */
    public function logApiCall(int $deviceId, string $action, array $requestData, array $responseData, float $executionTime = null, int $httpStatusCode = null): int
    {
        $logData = [
            'device_id' => $deviceId,
            'log_type' => 'api_call',
            'action' => $action,
            'description' => "API call: {$action}",
            'request_data' => json_encode($requestData),
            'response_data' => json_encode($responseData),
            'execution_time' => $executionTime,
            'http_status_code' => $httpStatusCode,
            'ip_address' => $this->getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->insert($logData);
    }
    
    /**
     * Log webhook
     */
    public function logWebhook(string $action, array $requestData, array $responseData = [], int $httpStatusCode = null): int
    {
        $logData = [
            'log_type' => 'webhook',
            'action' => $action,
            'description' => "Webhook: {$action}",
            'request_data' => json_encode($requestData),
            'response_data' => json_encode($responseData),
            'http_status_code' => $httpStatusCode,
            'ip_address' => $this->getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->insert($logData);
    }
    
    /**
     * Log error
     */
    public function logError(string $action, string $errorMessage, array $context = [], int $deviceId = null, int $messageId = null): int
    {
        $logData = [
            'device_id' => $deviceId,
            'message_id' => $messageId,
            'log_type' => 'error',
            'action' => $action,
            'description' => "Error: {$action}",
            'error_message' => $errorMessage,
            'context' => json_encode($context),
            'ip_address' => $this->getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->insert($logData);
    }
    
    /**
     * Log info
     */
    public function logInfo(string $action, string $description, array $context = [], int $deviceId = null, int $messageId = null): int
    {
        $logData = [
            'device_id' => $deviceId,
            'message_id' => $messageId,
            'log_type' => 'info',
            'action' => $action,
            'description' => $description,
            'context' => json_encode($context),
            'ip_address' => $this->getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->insert($logData);
    }
    
    /**
     * Clean old logs
     */
    public function cleanOldLogs(int $daysOld = 30): int
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$daysOld} days"));
        
        return $this->where('created_at <', $cutoffDate)->delete();
    }
    
    /**
     * Get client IP address
     */
    protected function getClientIp(): string
    {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Before insert callback
     */
    protected function beforeInsert(array $data): array
    {
        if (isset($data['data']['request_data']) && is_array($data['data']['request_data'])) {
            $data['data']['request_data'] = json_encode($data['data']['request_data']);
        }
        
        if (isset($data['data']['response_data']) && is_array($data['data']['response_data'])) {
            $data['data']['response_data'] = json_encode($data['data']['response_data']);
        }
        
        if (isset($data['data']['context']) && is_array($data['data']['context'])) {
            $data['data']['context'] = json_encode($data['data']['context']);
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
        $jsonFields = ['request_data', 'response_data', 'context'];
        
        foreach ($jsonFields as $field) {
            if (isset($record[$field]) && is_string($record[$field])) {
                $record[$field] = json_decode($record[$field], true);
            }
        }
    }
}
