<?php

namespace App\Modules\ClassroomNotifications\Models;

use CodeIgniter\Model;

/**
 * WhatsApp Connection Model
 * 
 * Manages WhatsApp connection status and monitoring
 */
class WhatsAppConnectionModel extends Model
{
    protected $table = 'whatsapp_connection_status';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'device_id',
        'device_name',
        'connection_status',
        'last_check',
        'last_connected',
        'error_message',
        'api_response',
        'quota_remaining'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    // Connection status constants
    const STATUS_CONNECTED = 'connected';
    const STATUS_DISCONNECTED = 'disconnected';
    const STATUS_CONNECTING = 'connecting';
    const STATUS_ERROR = 'error';
    
    /**
     * Get current connection status
     */
    public function getCurrentStatus(): array
    {
        $status = $this->orderBy('updated_at', 'DESC')->first();
        
        if (!$status) {
            return [
                'connection_status' => self::STATUS_DISCONNECTED,
                'last_check' => null,
                'error_message' => 'No connection data available',
                'quota_remaining' => 0
            ];
        }
        
        return $status;
    }
    
    /**
     * Update connection status
     */
    public function updateStatus(string $status, array $data = []): bool
    {
        $updateData = [
            'connection_status' => $status,
            'last_check' => date('Y-m-d H:i:s')
        ];
        
        if ($status === self::STATUS_CONNECTED) {
            $updateData['last_connected'] = date('Y-m-d H:i:s');
            $updateData['error_message'] = null;
        }
        
        if (isset($data['device_id'])) {
            $updateData['device_id'] = $data['device_id'];
        }
        
        if (isset($data['device_name'])) {
            $updateData['device_name'] = $data['device_name'];
        }
        
        if (isset($data['error_message'])) {
            $updateData['error_message'] = $data['error_message'];
        }
        
        if (isset($data['api_response'])) {
            $updateData['api_response'] = json_encode($data['api_response']);
        }
        
        if (isset($data['quota_remaining'])) {
            $updateData['quota_remaining'] = $data['quota_remaining'];
        }
        
        // Get current record or create new one
        $current = $this->first();
        
        if ($current) {
            return $this->update($current['id'], $updateData);
        } else {
            return $this->insert($updateData) !== false;
        }
    }
    
    /**
     * Check if WhatsApp is connected
     */
    public function isConnected(): bool
    {
        $status = $this->getCurrentStatus();
        return $status['connection_status'] === self::STATUS_CONNECTED;
    }
    
    /**
     * Get connection uptime
     */
    public function getConnectionUptime(): ?int
    {
        $status = $this->getCurrentStatus();
        
        if ($status['connection_status'] !== self::STATUS_CONNECTED || !$status['last_connected']) {
            return null;
        }
        
        return time() - strtotime($status['last_connected']);
    }
    
    /**
     * Get connection history
     */
    public function getConnectionHistory(int $limit = 50): array
    {
        return $this->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
    
    /**
     * Get connection statistics
     */
    public function getConnectionStats(): array
    {
        $db = \Config\Database::connect();
        
        // Get total checks in last 24 hours
        $last24hQuery = $db->query("
            SELECT COUNT(*) as total_checks,
                   SUM(CASE WHEN connection_status = 'connected' THEN 1 ELSE 0 END) as successful_checks
            FROM whatsapp_connection_status 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $last24h = $last24hQuery->getRowArray();
        
        // Get current status
        $currentStatus = $this->getCurrentStatus();
        
        // Calculate uptime percentage
        $uptimePercentage = 0;
        if ($last24h['total_checks'] > 0) {
            $uptimePercentage = ($last24h['successful_checks'] / $last24h['total_checks']) * 100;
        }
        
        return [
            'current_status' => $currentStatus['connection_status'],
            'last_check' => $currentStatus['last_check'],
            'last_connected' => $currentStatus['last_connected'],
            'quota_remaining' => $currentStatus['quota_remaining'] ?? 0,
            'uptime_percentage' => round($uptimePercentage, 2),
            'total_checks_24h' => $last24h['total_checks'],
            'successful_checks_24h' => $last24h['successful_checks'],
            'device_id' => $currentStatus['device_id'],
            'device_name' => $currentStatus['device_name']
        ];
    }
    
    /**
     * Log connection attempt
     */
    public function logConnectionAttempt(bool $success, array $response = [], string $errorMessage = null): bool
    {
        $status = $success ? self::STATUS_CONNECTED : self::STATUS_ERROR;
        
        $data = [
            'api_response' => $response
        ];
        
        if (!$success && $errorMessage) {
            $data['error_message'] = $errorMessage;
        }
        
        if (isset($response['data']['device']['id'])) {
            $data['device_id'] = $response['data']['device']['id'];
        }
        
        if (isset($response['data']['device']['name'])) {
            $data['device_name'] = $response['data']['device']['name'];
        }
        
        if (isset($response['data']['quota'])) {
            $data['quota_remaining'] = $response['data']['quota'];
        }
        
        return $this->updateStatus($status, $data);
    }
    
    /**
     * Check if connection check is needed
     */
    public function needsConnectionCheck(int $intervalMinutes = 5): bool
    {
        $status = $this->getCurrentStatus();
        
        if (!$status['last_check']) {
            return true;
        }
        
        $lastCheck = strtotime($status['last_check']);
        $now = time();
        
        return ($now - $lastCheck) >= ($intervalMinutes * 60);
    }
    
    /**
     * Get last error message
     */
    public function getLastError(): ?string
    {
        $status = $this->getCurrentStatus();
        return $status['error_message'] ?? null;
    }
    
    /**
     * Clear error status
     */
    public function clearError(): bool
    {
        return $this->updateStatus(self::STATUS_DISCONNECTED, [
            'error_message' => null
        ]);
    }
    
    /**
     * Get quota information
     */
    public function getQuotaInfo(): array
    {
        $status = $this->getCurrentStatus();
        
        return [
            'remaining' => $status['quota_remaining'] ?? 0,
            'last_updated' => $status['last_check'],
            'status' => $status['connection_status']
        ];
    }
    
    /**
     * Update quota
     */
    public function updateQuota(int $remaining): bool
    {
        $current = $this->first();
        
        if ($current) {
            return $this->update($current['id'], [
                'quota_remaining' => $remaining,
                'last_check' => date('Y-m-d H:i:s')
            ]);
        }
        
        return false;
    }
}
