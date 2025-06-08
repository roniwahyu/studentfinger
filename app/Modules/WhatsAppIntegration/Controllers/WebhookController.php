<?php

namespace App\Modules\WhatsAppIntegration\Controllers;

use App\Controllers\BaseController;
use App\Modules\WhatsAppIntegration\Models\WaDeviceModel;
use App\Modules\WhatsAppIntegration\Models\WaMessageModel;
use App\Modules\WhatsAppIntegration\Models\WaLogModel;
use App\Modules\WhatsAppIntegration\Services\WhatsAppGatewayService;

class WebhookController extends BaseController
{
    protected $deviceModel;
    protected $messageModel;
    protected $logModel;
    protected $gatewayService;

    public function __construct()
    {
        $this->deviceModel = new WaDeviceModel();
        $this->messageModel = new WaMessageModel();
        $this->logModel = new WaLogModel();
        $this->gatewayService = new WhatsAppGatewayService();
    }

    /**
     * Handle incoming webhook
     */
    public function handle($webhookId = null)
    {
        try {
            // Get webhook data
            $input = $this->request->getBody();
            $webhookData = json_decode($input, true);

            if (!$webhookData) {
                throw new \Exception('Invalid webhook data');
            }

            // Find device by webhook URL
            $device = $this->deviceModel->getByWebhookUrl($this->request->getUri());
            if (!$device) {
                throw new \Exception('Device not found for webhook');
            }

            // Log webhook received
            $this->logModel->logWebhookReceived($device['id'], $webhookData);

            // Process webhook based on device type
            $result = $this->processWebhook($device, $webhookData);

            // Update device last activity
            $this->deviceModel->updateLastActivity($device['id']);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Webhook processed successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Webhook error: ' . $e->getMessage());
            
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Process webhook based on device type
     */
    private function processWebhook($device, $webhookData)
    {
        switch ($device['device_type']) {
            case 'wablas':
                return $this->processWablasWebhook($device, $webhookData);
            case 'whatsapp-web':
                return $this->processWhatsAppWebWebhook($device, $webhookData);
            case 'baileys':
                return $this->processBaileysWebhook($device, $webhookData);
            default:
                return $this->processGenericWebhook($device, $webhookData);
        }
    }

    /**
     * Process Wablas webhook
     */
    private function processWablasWebhook($device, $webhookData)
    {
        $processedEvents = [];

        // Handle different webhook events
        if (isset($webhookData['event'])) {
            switch ($webhookData['event']) {
                case 'message_status':
                    $processedEvents[] = $this->handleMessageStatus($device, $webhookData);
                    break;
                case 'incoming_message':
                    $processedEvents[] = $this->handleIncomingMessage($device, $webhookData);
                    break;
                case 'device_status':
                    $processedEvents[] = $this->handleDeviceStatus($device, $webhookData);
                    break;
                default:
                    $processedEvents[] = $this->handleGenericEvent($device, $webhookData);
            }
        }

        return $processedEvents;
    }

    /**
     * Handle message status update
     */
    private function handleMessageStatus($device, $webhookData)
    {
        if (!isset($webhookData['message_id']) || !isset($webhookData['status'])) {
            return ['error' => 'Missing message_id or status'];
        }

        $messageId = $webhookData['message_id'];
        $status = $webhookData['status'];

        // Update message status
        $updated = $this->messageModel->updateFromWebhook($messageId, $status, $webhookData);

        if ($updated) {
            $this->logModel->logActivity($device['id'], 'message_status_updated', [
                'message_id' => $messageId,
                'status' => $status
            ]);
        }

        return [
            'event' => 'message_status',
            'message_id' => $messageId,
            'status' => $status,
            'updated' => $updated
        ];
    }

    /**
     * Handle incoming message
     */
    private function handleIncomingMessage($device, $webhookData)
    {
        if (!isset($webhookData['from']) || !isset($webhookData['message'])) {
            return ['error' => 'Missing from or message data'];
        }

        $from = $webhookData['from'];
        $message = $webhookData['message'];

        // Store incoming message
        $incomingData = [
            'device_id' => $device['id'],
            'phone_number' => $from,
            'message' => $message,
            'status' => 3, // Received
            'api_response' => json_encode($webhookData),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $messageId = $this->messageModel->insert($incomingData);

        // Process auto-reply if enabled
        $autoReply = $this->processAutoReply($device, $from, $message);

        $this->logModel->logActivity($device['id'], 'incoming_message', [
            'from' => $from,
            'message_id' => $messageId,
            'auto_reply' => $autoReply
        ]);

        return [
            'event' => 'incoming_message',
            'from' => $from,
            'message_id' => $messageId,
            'auto_reply' => $autoReply
        ];
    }

    /**
     * Handle device status update
     */
    private function handleDeviceStatus($device, $webhookData)
    {
        $status = $webhookData['status'] ?? 'unknown';
        $isOnline = in_array(strtolower($status), ['connected', 'online', 'ready']);

        // Update device status
        $this->deviceModel->update($device['id'], [
            'device_status' => $isOnline ? 1 : 0,
            'last_activity' => date('Y-m-d H:i:s')
        ]);

        $this->logModel->logActivity($device['id'], 'device_status_changed', [
            'status' => $status,
            'is_online' => $isOnline
        ]);

        return [
            'event' => 'device_status',
            'status' => $status,
            'is_online' => $isOnline
        ];
    }

    /**
     * Process auto-reply
     */
    private function processAutoReply($device, $phoneNumber, $incomingMessage)
    {
        // Get device settings
        $settings = $this->deviceModel->getDeviceSettings($device['id']);
        
        if (!isset($settings['auto_reply_enabled']) || !$settings['auto_reply_enabled']) {
            return null;
        }

        // Check for keywords
        $autoReplyRules = $settings['auto_reply_rules'] ?? [];
        $replyMessage = null;

        foreach ($autoReplyRules as $rule) {
            $keywords = $rule['keywords'] ?? [];
            $response = $rule['response'] ?? '';

            foreach ($keywords as $keyword) {
                if (stripos($incomingMessage, $keyword) !== false) {
                    $replyMessage = $response;
                    break 2;
                }
            }
        }

        // Default auto-reply if no specific rule matched
        if (!$replyMessage && isset($settings['default_auto_reply'])) {
            $replyMessage = $settings['default_auto_reply'];
        }

        if ($replyMessage) {
            try {
                // Send auto-reply
                $result = $this->gatewayService->sendMessage(
                    $device['id'],
                    $phoneNumber,
                    $replyMessage
                );

                return [
                    'sent' => true,
                    'message' => $replyMessage,
                    'result' => $result
                ];
            } catch (\Exception $e) {
                return [
                    'sent' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        return null;
    }

    /**
     * Handle generic event
     */
    private function handleGenericEvent($device, $webhookData)
    {
        $this->logModel->logActivity($device['id'], 'generic_webhook_event', $webhookData);

        return [
            'event' => 'generic',
            'data' => $webhookData
        ];
    }

    /**
     * Process WhatsApp Web webhook
     */
    private function processWhatsAppWebWebhook($device, $webhookData)
    {
        // Similar processing for WhatsApp Web
        return $this->processGenericWebhook($device, $webhookData);
    }

    /**
     * Process Baileys webhook
     */
    private function processBaileysWebhook($device, $webhookData)
    {
        // Similar processing for Baileys
        return $this->processGenericWebhook($device, $webhookData);
    }

    /**
     * Process generic webhook
     */
    private function processGenericWebhook($device, $webhookData)
    {
        $this->logModel->logActivity($device['id'], 'webhook_received', $webhookData);

        return [
            'event' => 'generic_webhook',
            'processed' => true
        ];
    }

    /**
     * Webhook verification (for some providers)
     */
    public function verify($webhookId = null)
    {
        $verifyToken = $this->request->getGet('hub_verify_token');
        $challenge = $this->request->getGet('hub_challenge');
        $mode = $this->request->getGet('hub_mode');

        // Verify webhook token (implement your verification logic)
        if ($mode === 'subscribe' && $this->verifyWebhookToken($verifyToken)) {
            return $this->response->setBody($challenge);
        }

        return $this->response->setStatusCode(403)->setBody('Forbidden');
    }

    /**
     * Verify webhook token
     */
    private function verifyWebhookToken($token)
    {
        // Implement your webhook token verification logic
        $expectedToken = env('WHATSAPP_WEBHOOK_VERIFY_TOKEN', 'your_verify_token');
        return $token === $expectedToken;
    }

    /**
     * Get webhook logs
     */
    public function logs($deviceId = null)
    {
        $logs = $deviceId 
            ? $this->logModel->getLogsByDevice($deviceId, 100)
            : $this->logModel->getLogsByAction('webhook_received', 100);

        return $this->response->setJSON([
            'success' => true,
            'data' => $logs
        ]);
    }

    /**
     * Test webhook endpoint
     */
    public function test()
    {
        $testData = [
            'event' => 'test',
            'timestamp' => time(),
            'message' => 'Webhook test successful'
        ];

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Webhook endpoint is working',
            'test_data' => $testData
        ]);
    }
}
