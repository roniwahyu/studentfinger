<?php

namespace App\Modules\WablasIntegration\Controllers;

use App\Controllers\BaseController;
use App\Modules\WablasIntegration\Services\WablasService;
use App\Modules\WablasIntegration\Models\WablasDeviceModel;
use App\Modules\WablasIntegration\Models\WablasMessageModel;
use App\Modules\WablasIntegration\Models\WablasContactModel;
use App\Modules\WablasIntegration\Models\WablasScheduleModel;
use App\Modules\WablasIntegration\Models\WablasLogModel;

/**
 * Wablas Integration Dashboard Controller
 */
class Dashboard extends BaseController
{
    protected WablasService $wablasService;
    protected WablasDeviceModel $deviceModel;
    protected WablasMessageModel $messageModel;
    protected WablasContactModel $contactModel;
    protected WablasScheduleModel $scheduleModel;
    protected WablasLogModel $logModel;
    
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->wablasService = new WablasService();
        $this->deviceModel = new WablasDeviceModel();
        $this->messageModel = new WablasMessageModel();
        $this->contactModel = new WablasContactModel();
        $this->scheduleModel = new WablasScheduleModel();
        $this->logModel = new WablasLogModel();
    }
    
    /**
     * Dashboard index page
     */
    public function index()
    {
        $data = [
            'title' => 'Wablas Integration Dashboard',
            'stats' => $this->getDashboardStats(),
            'recent_messages' => $this->messageModel->getRecentMessages(10),
            'device_status' => $this->getDeviceStatusSummary(),
            'pending_schedules' => $this->scheduleModel->getPendingSchedules(),
            'recent_logs' => $this->logModel->getRecentLogs(10)
        ];
        
        return view('Modules/WablasIntegration/Views/dashboard/index', $data);
    }
    
    /**
     * Get dashboard statistics (AJAX)
     */
    public function getStats()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }
        
        $stats = $this->getDashboardStats();
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $stats
        ]);
    }
    
    /**
     * Get recent messages (AJAX)
     */
    public function getRecentMessages()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }
        
        $limit = $this->request->getGet('limit') ?? 20;
        $messages = $this->messageModel->getRecentMessages($limit);
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $messages
        ]);
    }
    
    /**
     * Get device status (AJAX)
     */
    public function getDeviceStatus()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }
        
        $devices = $this->deviceModel->getActiveDevices();
        $deviceStatus = [];
        
        foreach ($devices as $device) {
            $deviceStatus[] = [
                'id' => $device['id'],
                'name' => $device['device_name'],
                'phone' => $device['phone_number'],
                'status' => $device['connection_status'],
                'quota_used' => $device['quota_used'],
                'quota_limit' => $device['quota_limit'],
                'quota_percentage' => $device['quota_limit'] > 0 ? round(($device['quota_used'] / $device['quota_limit']) * 100, 2) : 0,
                'last_seen' => $device['last_seen']
            ];
        }
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $deviceStatus
        ]);
    }
    
    /**
     * Get comprehensive dashboard statistics
     */
    protected function getDashboardStats(): array
    {
        // Device statistics
        $deviceStats = $this->deviceModel->getStatistics();
        
        // Message statistics for different periods
        $messageStatsToday = $this->messageModel->getStatistics(null, 'today');
        $messageStatsWeek = $this->messageModel->getStatistics(null, 'this_week');
        $messageStatsMonth = $this->messageModel->getStatistics(null, 'this_month');
        
        // Contact statistics
        $contactStats = $this->contactModel->getStatistics();
        
        // Schedule statistics
        $scheduleStats = $this->scheduleModel->getStatistics();
        
        // Log statistics
        $logStatsToday = $this->logModel->getStatistics('today');
        
        // Quota usage summary
        $quotaUsage = $this->getQuotaUsageSummary();
        
        // Recent activity summary
        $recentActivity = $this->getRecentActivitySummary();
        
        return [
            'devices' => $deviceStats,
            'messages' => [
                'today' => $messageStatsToday,
                'this_week' => $messageStatsWeek,
                'this_month' => $messageStatsMonth
            ],
            'contacts' => $contactStats,
            'schedules' => $scheduleStats,
            'logs' => $logStatsToday,
            'quota_usage' => $quotaUsage,
            'recent_activity' => $recentActivity
        ];
    }
    
    /**
     * Get device status summary
     */
    protected function getDeviceStatusSummary(): array
    {
        $devices = $this->deviceModel->getActiveDevices();
        $summary = [
            'total' => count($devices),
            'connected' => 0,
            'disconnected' => 0,
            'error' => 0,
            'low_quota' => 0,
            'expired' => 0
        ];
        
        foreach ($devices as $device) {
            // Connection status
            switch ($device['connection_status']) {
                case 'connected':
                    $summary['connected']++;
                    break;
                case 'disconnected':
                    $summary['disconnected']++;
                    break;
                case 'error':
                    $summary['error']++;
                    break;
            }
            
            // Quota check (low if > 90%)
            $quotaPercentage = $device['quota_limit'] > 0 ? ($device['quota_used'] / $device['quota_limit']) * 100 : 0;
            if ($quotaPercentage > 90) {
                $summary['low_quota']++;
            }
            
            // Expiry check
            if ($device['expired_date'] && $device['expired_date'] <= date('Y-m-d')) {
                $summary['expired']++;
            }
        }
        
        return $summary;
    }
    
    /**
     * Get quota usage summary
     */
    protected function getQuotaUsageSummary(): array
    {
        $devices = $this->deviceModel->getActiveDevices();
        $totalQuota = 0;
        $totalUsed = 0;
        $deviceQuotas = [];
        
        foreach ($devices as $device) {
            $totalQuota += $device['quota_limit'];
            $totalUsed += $device['quota_used'];
            
            $percentage = $device['quota_limit'] > 0 ? round(($device['quota_used'] / $device['quota_limit']) * 100, 2) : 0;
            
            $deviceQuotas[] = [
                'device_name' => $device['device_name'],
                'used' => $device['quota_used'],
                'limit' => $device['quota_limit'],
                'percentage' => $percentage,
                'status' => $this->getQuotaStatus($percentage)
            ];
        }
        
        // Sort by percentage (highest first)
        usort($deviceQuotas, function($a, $b) {
            return $b['percentage'] <=> $a['percentage'];
        });
        
        return [
            'total_quota' => $totalQuota,
            'total_used' => $totalUsed,
            'total_percentage' => $totalQuota > 0 ? round(($totalUsed / $totalQuota) * 100, 2) : 0,
            'devices' => array_slice($deviceQuotas, 0, 10) // Top 10 devices
        ];
    }
    
    /**
     * Get quota status based on percentage
     */
    protected function getQuotaStatus(float $percentage): string
    {
        if ($percentage >= 95) {
            return 'critical';
        } elseif ($percentage >= 80) {
            return 'warning';
        } elseif ($percentage >= 60) {
            return 'moderate';
        } else {
            return 'good';
        }
    }
    
    /**
     * Get recent activity summary
     */
    protected function getRecentActivitySummary(): array
    {
        $activities = [];
        
        // Recent messages
        $recentMessages = $this->messageModel->select('id, device_id, phone_number, message_type, status, created_at')
                                           ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')))
                                           ->orderBy('created_at', 'DESC')
                                           ->limit(5)
                                           ->findAll();
        
        foreach ($recentMessages as $message) {
            $activities[] = [
                'type' => 'message',
                'action' => 'Message ' . $message['status'],
                'description' => "Message to {$message['phone_number']} - {$message['message_type']}",
                'timestamp' => $message['created_at'],
                'status' => $message['status']
            ];
        }
        
        // Recent device activities
        $recentLogs = $this->logModel->select('action, description, created_at, log_type')
                                   ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')))
                                   ->whereIn('log_type', ['info', 'warning', 'error'])
                                   ->orderBy('created_at', 'DESC')
                                   ->limit(5)
                                   ->findAll();
        
        foreach ($recentLogs as $log) {
            $activities[] = [
                'type' => 'system',
                'action' => $log['action'],
                'description' => $log['description'],
                'timestamp' => $log['created_at'],
                'status' => $log['log_type']
            ];
        }
        
        // Sort by timestamp (newest first)
        usort($activities, function($a, $b) {
            return strtotime($b['timestamp']) <=> strtotime($a['timestamp']);
        });
        
        return array_slice($activities, 0, 10);
    }
    
    /**
     * Get chart data for messages over time
     */
    public function getMessageChartData()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }
        
        $period = $this->request->getGet('period') ?? 'week';
        $deviceId = $this->request->getGet('device_id');
        
        $chartData = $this->generateMessageChartData($period, $deviceId);
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $chartData
        ]);
    }
    
    /**
     * Generate chart data for messages
     */
    protected function generateMessageChartData(string $period, int $deviceId = null): array
    {
        $builder = $this->messageModel->select("DATE(created_at) as date, status, COUNT(*) as count")
                                    ->where('deleted_at', null);
        
        if ($deviceId) {
            $builder->where('device_id', $deviceId);
        }
        
        // Set date range based on period
        switch ($period) {
            case 'week':
                $builder->where('created_at >=', date('Y-m-d', strtotime('-7 days')));
                break;
            case 'month':
                $builder->where('created_at >=', date('Y-m-d', strtotime('-30 days')));
                break;
            case 'year':
                $builder->where('created_at >=', date('Y-m-d', strtotime('-365 days')));
                break;
        }
        
        $results = $builder->groupBy(['date', 'status'])
                          ->orderBy('date', 'ASC')
                          ->findAll();
        
        // Process data for chart
        $chartData = [
            'labels' => [],
            'datasets' => [
                'sent' => [],
                'delivered' => [],
                'read' => [],
                'failed' => []
            ]
        ];
        
        $dateData = [];
        foreach ($results as $result) {
            if (!isset($dateData[$result['date']])) {
                $dateData[$result['date']] = [
                    'sent' => 0,
                    'delivered' => 0,
                    'read' => 0,
                    'failed' => 0
                ];
            }
            $dateData[$result['date']][$result['status']] = $result['count'];
        }
        
        foreach ($dateData as $date => $data) {
            $chartData['labels'][] = $date;
            $chartData['datasets']['sent'][] = $data['sent'];
            $chartData['datasets']['delivered'][] = $data['delivered'];
            $chartData['datasets']['read'][] = $data['read'];
            $chartData['datasets']['failed'][] = $data['failed'];
        }
        
        return $chartData;
    }
}
