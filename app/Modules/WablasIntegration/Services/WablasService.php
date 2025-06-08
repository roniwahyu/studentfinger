<?php

namespace App\Modules\WablasIntegration\Services;

use App\Libraries\WablasApi;
use App\Modules\WablasIntegration\Models\WablasDeviceModel;
use App\Modules\WablasIntegration\Models\WablasMessageModel;
use App\Modules\WablasIntegration\Models\WablasContactModel;
use App\Modules\WablasIntegration\Models\WablasScheduleModel;
use App\Modules\WablasIntegration\Models\WablasLogModel;
use App\Modules\WablasIntegration\Models\WablasTemplateModel;
use CodeIgniter\Config\Services;

/**
 * Wablas Service Class
 * 
 * Main service class for handling Wablas WhatsApp integration
 * Provides high-level methods for common operations
 */
class WablasService
{
    protected WablasDeviceModel $deviceModel;
    protected WablasMessageModel $messageModel;
    protected WablasContactModel $contactModel;
    protected WablasScheduleModel $scheduleModel;
    protected WablasLogModel $logModel;
    protected WablasTemplateModel $templateModel;
    protected $logger;
    
    public function __construct()
    {
        $this->deviceModel = new WablasDeviceModel();
        $this->messageModel = new WablasMessageModel();
        $this->contactModel = new WablasContactModel();
        $this->scheduleModel = new WablasScheduleModel();
        $this->logModel = new WablasLogModel();
        $this->templateModel = new WablasTemplateModel();
        $this->logger = Services::logger();
    }
    
    /**
     * Get Wablas API instance for a device
     */
    public function getApiInstance(int $deviceId): WablasApi
    {
        $device = $this->deviceModel->find($deviceId);
        
        if (!$device) {
            throw new \Exception("Device not found: {$deviceId}");
        }
        
        if ($device['device_status'] != 1) {
            throw new \Exception("Device is not active: {$deviceId}");
        }
        
        $config = [
            'base_url' => $device['api_url'],
            'token' => $device['token'],
            'secret_key' => $device['secret_key']
        ];
        
        return new WablasApi($config);
    }
    
    /**
     * Send a single message
     */
    public function sendMessage(int $deviceId, string $phoneNumber, string $message, array $options = []): array
    {
        try {
            $api = $this->getApiInstance($deviceId);
            $device = $this->deviceModel->find($deviceId);
            
            // Clean phone number
            $phoneNumber = $api->formatPhoneNumber($phoneNumber);
            
            // Validate phone number
            if (!$api->isValidPhoneNumber($phoneNumber)) {
                throw new \Exception("Invalid phone number: {$phoneNumber}");
            }
            
            // Check quota
            if ($device['quota_used'] >= $device['quota_limit']) {
                throw new \Exception("Device quota exceeded");
            }
            
            // Send message via API
            $response = $api->sendMessage($phoneNumber, $message, $options);
            
            // Save message to database
            $messageData = [
                'device_id' => $deviceId,
                'phone_number' => $phoneNumber,
                'message_type' => 'text',
                'message_content' => $message,
                'direction' => 'outgoing',
                'status' => $response['success'] ? 'sent' : 'failed',
                'api_response' => json_encode($response),
                'sent_at' => $response['success'] ? date('Y-m-d H:i:s') : null,
                'error_message' => !$response['success'] ? ($response['error'] ?? 'Unknown error') : null,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            if (isset($response['data']['messages'][0]['id'])) {
                $messageData['message_id'] = $response['data']['messages'][0]['id'];
            }
            
            $messageId = $this->messageModel->insert($messageData);
            
            // Update device quota
            if ($response['success']) {
                $this->deviceModel->update($deviceId, [
                    'quota_used' => $device['quota_used'] + 1,
                    'last_seen' => date('Y-m-d H:i:s')
                ]);
            }
            
            // Log the activity
            $this->logActivity($deviceId, 'message_sent', [
                'message_id' => $messageId,
                'phone_number' => $phoneNumber,
                'success' => $response['success']
            ], $messageId);
            
            return [
                'success' => $response['success'],
                'message_id' => $messageId,
                'external_id' => $response['data']['messages'][0]['id'] ?? null,
                'response' => $response
            ];
            
        } catch (\Exception $e) {
            $this->logActivity($deviceId, 'message_failed', [
                'phone_number' => $phoneNumber,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Send bulk messages
     */
    public function sendBulkMessages(int $deviceId, array $recipients, string $message, array $options = []): array
    {
        $results = [];
        $successCount = 0;
        $failCount = 0;
        
        $device = $this->deviceModel->find($deviceId);
        $delay = $device['delay_seconds'] ?? 1;
        
        foreach ($recipients as $recipient) {
            try {
                $phoneNumber = is_array($recipient) ? $recipient['phone_number'] : $recipient;
                $personalizedMessage = $this->personalizeMessage($message, $recipient);
                
                $result = $this->sendMessage($deviceId, $phoneNumber, $personalizedMessage, $options);
                
                if ($result['success']) {
                    $successCount++;
                } else {
                    $failCount++;
                }
                
                $results[] = [
                    'phone_number' => $phoneNumber,
                    'success' => $result['success'],
                    'message_id' => $result['message_id']
                ];
                
                // Add delay between messages
                if ($delay > 0) {
                    sleep($delay);
                }
                
            } catch (\Exception $e) {
                $failCount++;
                $results[] = [
                    'phone_number' => $phoneNumber,
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return [
            'success' => true,
            'total_sent' => $successCount,
            'total_failed' => $failCount,
            'results' => $results
        ];
    }
    
    /**
     * Send media message (image, document, video, audio)
     */
    public function sendMediaMessage(int $deviceId, string $phoneNumber, string $mediaUrl, string $type, array $options = []): array
    {
        try {
            $api = $this->getApiInstance($deviceId);
            $device = $this->deviceModel->find($deviceId);
            
            // Clean phone number
            $phoneNumber = $api->formatPhoneNumber($phoneNumber);
            
            // Check quota
            if ($device['quota_used'] >= $device['quota_limit']) {
                throw new \Exception("Device quota exceeded");
            }
            
            // Send media message based on type
            switch ($type) {
                case 'image':
                    $response = $api->sendImage($phoneNumber, $mediaUrl, $options['caption'] ?? '', $options);
                    break;
                case 'document':
                    $response = $api->sendDocument($phoneNumber, $mediaUrl, $options['filename'] ?? '', $options);
                    break;
                case 'video':
                    $response = $api->sendVideo($phoneNumber, $mediaUrl, $options['caption'] ?? '', $options);
                    break;
                case 'audio':
                    $response = $api->sendAudio($phoneNumber, $mediaUrl, $options);
                    break;
                default:
                    throw new \Exception("Unsupported media type: {$type}");
            }
            
            // Save message to database
            $messageData = [
                'device_id' => $deviceId,
                'phone_number' => $phoneNumber,
                'message_type' => $type,
                'message_content' => $options['caption'] ?? $options['filename'] ?? 'Media message',
                'media_url' => $mediaUrl,
                'media_caption' => $options['caption'] ?? null,
                'media_filename' => $options['filename'] ?? null,
                'direction' => 'outgoing',
                'status' => $response['success'] ? 'sent' : 'failed',
                'api_response' => json_encode($response),
                'sent_at' => $response['success'] ? date('Y-m-d H:i:s') : null,
                'error_message' => !$response['success'] ? ($response['error'] ?? 'Unknown error') : null,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            if (isset($response['data']['messages'][0]['id'])) {
                $messageData['message_id'] = $response['data']['messages'][0]['id'];
            }
            
            $messageId = $this->messageModel->insert($messageData);
            
            // Update device quota
            if ($response['success']) {
                $this->deviceModel->update($deviceId, [
                    'quota_used' => $device['quota_used'] + 1,
                    'last_seen' => date('Y-m-d H:i:s')
                ]);
            }
            
            // Log the activity
            $this->logActivity($deviceId, 'media_message_sent', [
                'message_id' => $messageId,
                'phone_number' => $phoneNumber,
                'media_type' => $type,
                'success' => $response['success']
            ], $messageId);
            
            return [
                'success' => $response['success'],
                'message_id' => $messageId,
                'external_id' => $response['data']['messages'][0]['id'] ?? null,
                'response' => $response
            ];
            
        } catch (\Exception $e) {
            $this->logActivity($deviceId, 'media_message_failed', [
                'phone_number' => $phoneNumber,
                'media_type' => $type,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Schedule a message
     */
    public function scheduleMessage(int $deviceId, string $phoneNumber, string $message, string $scheduledAt, array $options = []): array
    {
        try {
            $device = $this->deviceModel->find($deviceId);
            
            if (!$device) {
                throw new \Exception("Device not found: {$deviceId}");
            }
            
            // Clean phone number
            $api = $this->getApiInstance($deviceId);
            $phoneNumber = $api->formatPhoneNumber($phoneNumber);
            
            // Validate scheduled time
            $scheduledTime = strtotime($scheduledAt);
            if ($scheduledTime <= time()) {
                throw new \Exception("Scheduled time must be in the future");
            }
            
            // Save scheduled message
            $scheduleData = [
                'device_id' => $deviceId,
                'phone_number' => $phoneNumber,
                'message_type' => $options['type'] ?? 'text',
                'message_content' => $message,
                'media_url' => $options['media_url'] ?? null,
                'media_caption' => $options['caption'] ?? null,
                'is_group' => $options['is_group'] ?? 0,
                'scheduled_at' => date('Y-m-d H:i:s', $scheduledTime),
                'status' => 'pending',
                'template_id' => $options['template_id'] ?? null,
                'campaign_id' => $options['campaign_id'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $scheduleId = $this->scheduleModel->insert($scheduleData);
            
            // Log the activity
            $this->logActivity($deviceId, 'message_scheduled', [
                'schedule_id' => $scheduleId,
                'phone_number' => $phoneNumber,
                'scheduled_at' => $scheduledAt
            ]);
            
            return [
                'success' => true,
                'schedule_id' => $scheduleId,
                'message' => 'Message scheduled successfully'
            ];
            
        } catch (\Exception $e) {
            $this->logActivity($deviceId, 'schedule_failed', [
                'phone_number' => $phoneNumber,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Process scheduled messages
     */
    public function processScheduledMessages(): array
    {
        $scheduledMessages = $this->scheduleModel->getDueMessages();
        $processedCount = 0;
        $errors = [];
        
        foreach ($scheduledMessages as $scheduled) {
            try {
                // Mark as processing
                $this->scheduleModel->update($scheduled['id'], ['status' => 'processing']);
                
                // Send the message
                if ($scheduled['message_type'] === 'text') {
                    $result = $this->sendMessage(
                        $scheduled['device_id'],
                        $scheduled['phone_number'],
                        $scheduled['message_content']
                    );
                } else {
                    $result = $this->sendMediaMessage(
                        $scheduled['device_id'],
                        $scheduled['phone_number'],
                        $scheduled['media_url'],
                        $scheduled['message_type'],
                        [
                            'caption' => $scheduled['media_caption'],
                            'filename' => $scheduled['media_filename'] ?? null
                        ]
                    );
                }
                
                // Update schedule status
                $this->scheduleModel->update($scheduled['id'], [
                    'status' => $result['success'] ? 'sent' : 'failed',
                    'sent_at' => date('Y-m-d H:i:s'),
                    'error_message' => !$result['success'] ? 'Failed to send message' : null
                ]);
                
                $processedCount++;
                
            } catch (\Exception $e) {
                // Mark as failed
                $this->scheduleModel->update($scheduled['id'], [
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
                
                $errors[] = [
                    'schedule_id' => $scheduled['id'],
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return [
            'success' => true,
            'processed_count' => $processedCount,
            'errors' => $errors
        ];
    }
    
    /**
     * Personalize message with variables
     */
    protected function personalizeMessage(string $message, $contact): string
    {
        if (is_array($contact)) {
            $variables = [
                '{name}' => $contact['name'] ?? 'Customer',
                '{phone}' => $contact['phone_number'] ?? '',
                '{email}' => $contact['email'] ?? '',
                '{address}' => $contact['address'] ?? '',
                '{nickname}' => $contact['nickname'] ?? '',
                '{date}' => date('Y-m-d'),
                '{time}' => date('H:i'),
                '{datetime}' => date('Y-m-d H:i:s')
            ];
            
            foreach ($variables as $key => $value) {
                $message = str_replace($key, $value, $message);
            }
        }
        
        return $message;
    }
    
    /**
     * Log activity
     */
    protected function logActivity(int $deviceId, string $action, array $data = [], int $messageId = null): void
    {
        $logData = [
            'device_id' => $deviceId,
            'message_id' => $messageId,
            'log_type' => 'info',
            'action' => $action,
            'description' => "Action: {$action}",
            'request_data' => json_encode($data),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->logModel->insert($logData);
    }
}
