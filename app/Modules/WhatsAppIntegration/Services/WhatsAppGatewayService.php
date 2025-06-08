<?php

namespace App\Modules\WhatsAppIntegration\Services;

use App\Modules\WhatsAppIntegration\Models\WaDeviceModel;
use App\Modules\WhatsAppIntegration\Models\WaMessageModel;
use App\Modules\WhatsAppIntegration\Models\WaScheduleModel;
use App\Modules\WhatsAppIntegration\Models\WaLogModel;

class WhatsAppGatewayService
{
    protected $deviceModel;
    protected $messageModel;
    protected $scheduleModel;
    protected $logModel;

    public function __construct()
    {
        $this->deviceModel = new WaDeviceModel();
        $this->messageModel = new WaMessageModel();
        $this->scheduleModel = new WaScheduleModel();
        $this->logModel = new WaLogModel();
    }

    /**
     * Send message via WhatsApp gateway
     */
    public function sendMessage($deviceId, $phoneNumber, $message, $scheduleTime = null)
    {
        $device = $this->deviceModel->find($deviceId);
        if (!$device || $device['device_status'] != 1) {
            throw new \Exception('Device not found or inactive');
        }

        // Clean phone number
        $phoneNumber = $this->cleanPhoneNumber($phoneNumber);

        // If scheduled, save to schedule table
        if ($scheduleTime && strtotime($scheduleTime) > time()) {
            return $this->scheduleMessage($deviceId, $phoneNumber, $message, $scheduleTime);
        }

        // Send immediately
        return $this->sendImmediateMessage($device, $phoneNumber, $message);
    }

    /**
     * Send immediate message
     */
    private function sendImmediateMessage($device, $phoneNumber, $message)
    {
        try {
            $apiResponse = $this->callWhatsAppAPI($device, $phoneNumber, $message);
            
            // Log the message
            $messageData = [
                'device_id' => $device['id'],
                'phone_number' => $phoneNumber,
                'message' => $message,
                'status' => $apiResponse['success'] ? 1 : 2,
                'api_response' => json_encode($apiResponse),
                'sent_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];

            $messageId = $this->messageModel->insert($messageData);

            // Log activity
            $this->logActivity($device['id'], 'message_sent', [
                'message_id' => $messageId,
                'phone_number' => $phoneNumber,
                'success' => $apiResponse['success']
            ]);

            return [
                'success' => $apiResponse['success'],
                'message_id' => $messageId,
                'api_response' => $apiResponse
            ];
        } catch (\Exception $e) {
            $this->logActivity($device['id'], 'message_failed', [
                'phone_number' => $phoneNumber,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Schedule message for later delivery
     */
    private function scheduleMessage($deviceId, $phoneNumber, $message, $scheduleTime)
    {
        $scheduleData = [
            'device_id' => $deviceId,
            'phone_number' => $phoneNumber,
            'message' => $message,
            'schedule_time' => $scheduleTime,
            'status' => 0, // Pending
            'created_at' => date('Y-m-d H:i:s')
        ];

        $scheduleId = $this->scheduleModel->insert($scheduleData);

        return [
            'success' => true,
            'schedule_id' => $scheduleId,
            'message' => 'Message scheduled successfully'
        ];
    }

    /**
     * Send bulk messages
     */
    public function sendBulkMessage($deviceId, $contacts, $message, $templateId = null)
    {
        $device = $this->deviceModel->find($deviceId);
        if (!$device || $device['device_status'] != 1) {
            throw new \Exception('Device not found or inactive');
        }

        $results = [];
        $successCount = 0;
        $failCount = 0;

        foreach ($contacts as $contact) {
            try {
                $phoneNumber = is_array($contact) ? $contact['phone_number'] : $contact;
                $personalizedMessage = $this->personalizeMessage($message, $contact, $templateId);
                
                $result = $this->sendImmediateMessage($device, $phoneNumber, $personalizedMessage);
                
                if ($result['success']) {
                    $successCount++;
                } else {
                    $failCount++;
                }
                
                $results[] = [
                    'phone_number' => $phoneNumber,
                    'success' => $result['success'],
                    'message_id' => $result['message_id'] ?? null
                ];

                // Add delay between messages to avoid rate limiting
                usleep(500000); // 0.5 second delay
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
     * Process message queue
     */
    public function processMessageQueue()
    {
        // Process scheduled messages
        $scheduledMessages = $this->scheduleModel->getDueMessages();
        $processedCount = 0;

        foreach ($scheduledMessages as $scheduled) {
            try {
                $device = $this->deviceModel->find($scheduled['device_id']);
                if ($device && $device['device_status'] == 1) {
                    $result = $this->sendImmediateMessage(
                        $device,
                        $scheduled['phone_number'],
                        $scheduled['message']
                    );

                    // Update schedule status
                    $this->scheduleModel->update($scheduled['id'], [
                        'status' => $result['success'] ? 1 : 2,
                        'sent_at' => date('Y-m-d H:i:s')
                    ]);

                    $processedCount++;
                }
            } catch (\Exception $e) {
                // Mark as failed
                $this->scheduleModel->update($scheduled['id'], [
                    'status' => 2,
                    'error_message' => $e->getMessage()
                ]);
            }
        }

        return [
            'success' => true,
            'processed_count' => $processedCount
        ];
    }

    /**
     * Test device connection
     */
    public function testDeviceConnection($deviceData)
    {
        try {
            $testMessage = "Test connection from Student Attendance System";
            $testNumber = "6281234567890"; // Test number
            
            $response = $this->callWhatsAppAPI($deviceData, $testNumber, $testMessage, true);
            
            return [
                'success' => true,
                'message' => 'Device connection successful',
                'response' => $response
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Device connection failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check all devices status
     */
    public function checkAllDevicesStatus()
    {
        $devices = $this->deviceModel->where('device_status', 1)->findAll();
        $statusResults = [];

        foreach ($devices as $device) {
            $status = $this->checkDeviceStatus($device);
            $statusResults[$device['id']] = $status;
        }

        return $statusResults;
    }

    /**
     * Check single device status
     */
    private function checkDeviceStatus($device)
    {
        try {
            $response = $this->callWhatsAppAPI($device, null, null, true, 'status');
            return [
                'online' => true,
                'last_check' => date('Y-m-d H:i:s'),
                'response' => $response
            ];
        } catch (\Exception $e) {
            return [
                'online' => false,
                'last_check' => date('Y-m-d H:i:s'),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Call WhatsApp API based on device type
     */
    private function callWhatsAppAPI($device, $phoneNumber, $message, $testMode = false, $action = 'send')
    {
        switch ($device['device_type']) {
            case 'wablas':
                return $this->callWablasAPI($device, $phoneNumber, $message, $testMode, $action);
            case 'whatsapp-web':
                return $this->callWhatsAppWebAPI($device, $phoneNumber, $message, $testMode, $action);
            case 'baileys':
                return $this->callBaileysAPI($device, $phoneNumber, $message, $testMode, $action);
            default:
                throw new \Exception('Unsupported device type: ' . $device['device_type']);
        }
    }

    /**
     * Call Wablas API
     */
    private function callWablasAPI($device, $phoneNumber, $message, $testMode = false, $action = 'send')
    {
        $url = rtrim($device['api_url'], '/') . '/api/send-message';
        
        if ($action === 'status') {
            $url = rtrim($device['api_url'], '/') . '/api/device-status';
        }

        $headers = [
            'Authorization: Bearer ' . $device['device_token'],
            'Content-Type: application/json'
        ];

        $data = [];
        if ($action === 'send') {
            $data = [
                'phone' => $phoneNumber,
                'message' => $message,
                'isGroup' => false
            ];
        }

        return $this->makeHttpRequest($url, $headers, $data);
    }

    /**
     * Make HTTP request
     */
    private function makeHttpRequest($url, $headers, $data = [])
    {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POST => !empty($data),
            CURLOPT_POSTFIELDS => !empty($data) ? json_encode($data) : null,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception('cURL Error: ' . $error);
        }

        if ($httpCode !== 200) {
            throw new \Exception('HTTP Error: ' . $httpCode);
        }

        $decodedResponse = json_decode($response, true);
        
        return [
            'success' => isset($decodedResponse['status']) && $decodedResponse['status'] === 'success',
            'response' => $decodedResponse,
            'raw_response' => $response
        ];
    }

    /**
     * Clean phone number format
     */
    private function cleanPhoneNumber($phoneNumber)
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Add country code if not present
        if (substr($cleaned, 0, 2) !== '62') {
            if (substr($cleaned, 0, 1) === '0') {
                $cleaned = '62' . substr($cleaned, 1);
            } else {
                $cleaned = '62' . $cleaned;
            }
        }
        
        return $cleaned;
    }

    /**
     * Personalize message with template variables
     */
    private function personalizeMessage($message, $contact, $templateId = null)
    {
        // Replace common variables
        $variables = [
            '{name}' => $contact['name'] ?? 'Student',
            '{phone}' => $contact['phone_number'] ?? '',
            '{date}' => date('Y-m-d'),
            '{time}' => date('H:i'),
            '{school_name}' => 'Student Attendance System'
        ];

        foreach ($variables as $key => $value) {
            $message = str_replace($key, $value, $message);
        }

        return $message;
    }

    /**
     * Log activity
     */
    private function logActivity($deviceId, $action, $data = [])
    {
        $logData = [
            'device_id' => $deviceId,
            'action' => $action,
            'data' => json_encode($data),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->logModel->insert($logData);
    }
}
