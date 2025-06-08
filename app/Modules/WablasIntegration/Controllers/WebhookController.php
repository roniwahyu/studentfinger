<?php

namespace App\Modules\WablasIntegration\Controllers;

use CodeIgniter\Controller;
use App\Modules\WablasIntegration\Models\WablasDeviceModel;
use App\Modules\WablasIntegration\Models\WablasMessageModel;
use App\Modules\WablasIntegration\Models\WablasContactModel;
use App\Modules\WablasIntegration\Models\WablasLogModel;
use App\Modules\WablasIntegration\Models\WablasAutoReplyModel;
use App\Modules\WablasIntegration\Services\WablasService;

/**
 * Webhook Controller for handling Wablas webhooks
 */
class WebhookController extends Controller
{
    protected WablasDeviceModel $deviceModel;
    protected WablasMessageModel $messageModel;
    protected WablasContactModel $contactModel;
    protected WablasLogModel $logModel;
    protected WablasAutoReplyModel $autoReplyModel;
    protected WablasService $wablasService;
    
    public function __construct()
    {
        $this->deviceModel = new WablasDeviceModel();
        $this->messageModel = new WablasMessageModel();
        $this->contactModel = new WablasContactModel();
        $this->logModel = new WablasLogModel();
        $this->autoReplyModel = new WablasAutoReplyModel();
        $this->wablasService = new WablasService();
    }
    
    /**
     * Handle incoming message webhook
     */
    public function incoming()
    {
        try {
            // Get webhook data
            $input = $this->request->getJSON(true);
            
            if (empty($input)) {
                $input = $this->request->getPost();
            }
            
            // Log the webhook
            $this->logModel->logWebhook('incoming_message', $input);
            
            // Validate required fields
            if (!isset($input['phone']) || !isset($input['message'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Missing required fields'
                ])->setStatusCode(400);
            }
            
            // Process the incoming message
            $result = $this->processIncomingMessage($input);
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            $this->logModel->logError('webhook_incoming_error', $e->getMessage(), [
                'input' => $input ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }
    
    /**
     * Handle message status webhook
     */
    public function messageStatus()
    {
        try {
            // Get webhook data
            $input = $this->request->getJSON(true);
            
            if (empty($input)) {
                $input = $this->request->getPost();
            }
            
            // Log the webhook
            $this->logModel->logWebhook('message_status', $input);
            
            // Validate required fields
            if (!isset($input['id']) || !isset($input['status'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Missing required fields'
                ])->setStatusCode(400);
            }
            
            // Process the status update
            $result = $this->processMessageStatus($input);
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            $this->logModel->logError('webhook_status_error', $e->getMessage(), [
                'input' => $input ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }
    
    /**
     * Handle device status webhook
     */
    public function deviceStatus()
    {
        try {
            // Get webhook data
            $input = $this->request->getJSON(true);
            
            if (empty($input)) {
                $input = $this->request->getPost();
            }
            
            // Log the webhook
            $this->logModel->logWebhook('device_status', $input);
            
            // Validate required fields
            if (!isset($input['deviceId']) || !isset($input['status'])) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Missing required fields'
                ])->setStatusCode(400);
            }
            
            // Process the device status update
            $result = $this->processDeviceStatus($input);
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            $this->logModel->logError('webhook_device_error', $e->getMessage(), [
                'input' => $input ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }
    
    /**
     * Test webhook endpoint
     */
    public function test()
    {
        $testData = [
            'test' => true,
            'timestamp' => date('Y-m-d H:i:s'),
            'message' => 'Webhook test successful'
        ];
        
        $this->logModel->logWebhook('webhook_test', $testData);
        
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Webhook test successful',
            'data' => $testData
        ]);
    }
    
    /**
     * Process incoming message
     */
    protected function processIncomingMessage(array $data): array
    {
        // Extract message data
        $phoneNumber = $data['phone'];
        $message = $data['message'];
        $messageType = $data['messageType'] ?? 'text';
        $pushName = $data['pushName'] ?? null;
        $isGroup = $data['isGroup'] ?? false;
        $deviceId = $data['deviceId'] ?? null;
        $messageId = $data['id'] ?? null;
        
        // Find device by serial or phone number
        $device = null;
        if ($deviceId) {
            $device = $this->deviceModel->getBySerial($deviceId);
        }
        
        if (!$device && isset($data['sender'])) {
            $device = $this->deviceModel->getByPhoneNumber($data['sender']);
        }
        
        if (!$device) {
            throw new \Exception('Device not found for incoming message');
        }
        
        // Save incoming message
        $messageData = [
            'message_id' => $messageId,
            'device_id' => $device['id'],
            'phone_number' => $phoneNumber,
            'contact_name' => $pushName,
            'message_type' => $messageType,
            'message_content' => $message,
            'direction' => 'incoming',
            'status' => 'read',
            'is_group' => $isGroup ? 1 : 0,
            'read_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Handle group messages
        if ($isGroup && isset($data['group'])) {
            $messageData['group_id'] = $phoneNumber;
            $messageData['group_name'] = $data['group']['subject'] ?? null;
        }
        
        // Handle media messages
        if ($messageType !== 'text') {
            $messageData['media_url'] = $data['url'] ?? null;
            $messageData['media_filename'] = $data['file'] ?? null;
            $messageData['media_mime_type'] = $data['mimeType'] ?? null;
        }
        
        $savedMessageId = $this->messageModel->insert($messageData);
        
        // Update or create contact
        if (!$isGroup) {
            $this->updateOrCreateContact($phoneNumber, $pushName, $data);
        }
        
        // Process auto-reply if enabled
        $autoReplyResponse = null;
        if ($device['auto_reply_enabled'] && !$isGroup) {
            $autoReplyResponse = $this->processAutoReply($device['id'], $phoneNumber, $message);
        }
        
        return [
            'message_id' => $savedMessageId,
            'contact_updated' => !$isGroup,
            'auto_reply_sent' => $autoReplyResponse !== null,
            'auto_reply_response' => $autoReplyResponse
        ];
    }
    
    /**
     * Process message status update
     */
    protected function processMessageStatus(array $data): array
    {
        $messageId = $data['id'];
        $status = $data['status'];
        $phone = $data['phone'] ?? null;
        $note = $data['note'] ?? null;
        $deviceId = $data['deviceId'] ?? null;
        
        // Find message by external ID
        $message = $this->messageModel->getByExternalId($messageId);
        
        if (!$message) {
            // Log that message was not found
            $this->logModel->logInfo('message_status_not_found', 'Message not found for status update', [
                'external_id' => $messageId,
                'status' => $status,
                'phone' => $phone
            ]);
            
            return ['message_found' => false];
        }
        
        // Update message status
        $updateData = ['status' => $status];
        
        if ($note) {
            $updateData['error_message'] = $note;
        }
        
        $this->messageModel->updateStatus($message['id'], $status, $updateData);
        
        return [
            'message_found' => true,
            'message_id' => $message['id'],
            'status_updated' => true
        ];
    }
    
    /**
     * Process device status update
     */
    protected function processDeviceStatus(array $data): array
    {
        $deviceSerial = $data['deviceId'];
        $status = $data['status'];
        $deviceName = $data['deviceName'] ?? null;
        $sender = $data['sender'] ?? null;
        $note = $data['note'] ?? null;
        
        // Find device by serial
        $device = $this->deviceModel->getBySerial($deviceSerial);
        
        if (!$device) {
            // Log that device was not found
            $this->logModel->logInfo('device_status_not_found', 'Device not found for status update', [
                'device_serial' => $deviceSerial,
                'status' => $status,
                'sender' => $sender
            ]);
            
            return ['device_found' => false];
        }
        
        // Map Wablas status to our status
        $connectionStatus = $this->mapDeviceStatus($status);
        
        // Update device status
        $updateData = [
            'connection_status' => $connectionStatus,
            'last_seen' => date('Y-m-d H:i:s')
        ];
        
        if ($sender && $sender !== $device['phone_number']) {
            $updateData['phone_number'] = $sender;
        }
        
        $this->deviceModel->update($device['id'], $updateData);
        
        // Log device status change
        $this->logModel->logInfo('device_status_updated', "Device status changed to {$status}", [
            'device_id' => $device['id'],
            'old_status' => $device['connection_status'],
            'new_status' => $connectionStatus,
            'note' => $note
        ], $device['id']);
        
        return [
            'device_found' => true,
            'device_id' => $device['id'],
            'status_updated' => true,
            'old_status' => $device['connection_status'],
            'new_status' => $connectionStatus
        ];
    }
    
    /**
     * Update or create contact from incoming message
     */
    protected function updateOrCreateContact(string $phoneNumber, string $name = null, array $data = []): void
    {
        $contact = $this->contactModel->getByPhoneNumber($phoneNumber);
        
        $contactData = [
            'phone_number' => $phoneNumber,
            'last_seen' => date('Y-m-d H:i:s'),
            'is_whatsapp_active' => 1
        ];
        
        if ($name) {
            $contactData['name'] = $name;
        }
        
        if (isset($data['profileImage'])) {
            $contactData['profile_image'] = $data['profileImage'];
        }
        
        if ($contact) {
            // Update existing contact
            $this->contactModel->update($contact['id'], $contactData);
        } else {
            // Create new contact
            $contactData['name'] = $name ?: 'Unknown';
            $contactData['source'] = 'webhook';
            $this->contactModel->insert($contactData);
        }
    }
    
    /**
     * Process auto-reply
     */
    protected function processAutoReply(int $deviceId, string $phoneNumber, string $message): ?string
    {
        // Get active auto-replies for this device
        $autoReplies = $this->autoReplyModel->getActiveByDevice($deviceId);
        
        foreach ($autoReplies as $autoReply) {
            if ($this->matchesAutoReply($message, $autoReply)) {
                // Send auto-reply
                try {
                    $response = $this->wablasService->sendMessage(
                        $deviceId,
                        $phoneNumber,
                        $autoReply['response_content']
                    );
                    
                    // Update auto-reply usage
                    $this->autoReplyModel->incrementUsage($autoReply['id']);
                    
                    return $autoReply['response_content'];
                    
                } catch (\Exception $e) {
                    $this->logModel->logError('auto_reply_failed', $e->getMessage(), [
                        'auto_reply_id' => $autoReply['id'],
                        'phone_number' => $phoneNumber,
                        'message' => $message
                    ], $deviceId);
                }
                
                break; // Only send one auto-reply
            }
        }
        
        return null;
    }
    
    /**
     * Check if message matches auto-reply keyword
     */
    protected function matchesAutoReply(string $message, array $autoReply): bool
    {
        $keyword = $autoReply['keyword'];
        $isExactMatch = $autoReply['is_exact_match'];
        $isCaseSensitive = $autoReply['is_case_sensitive'];
        
        if (!$isCaseSensitive) {
            $message = strtolower($message);
            $keyword = strtolower($keyword);
        }
        
        if ($isExactMatch) {
            return trim($message) === $keyword;
        } else {
            return strpos($message, $keyword) !== false;
        }
    }
    
    /**
     * Map Wablas device status to our status
     */
    protected function mapDeviceStatus(string $wablasStatus): string
    {
        switch (strtolower($wablasStatus)) {
            case 'connected':
            case 'ready to use':
                return 'connected';
            case 'disconnected':
            case 'need scan qr code again':
                return 'disconnected';
            case 'connecting':
                return 'connecting';
            default:
                return 'error';
        }
    }
}
