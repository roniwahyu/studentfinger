<?php

namespace App\Modules\ClassroomNotifications\Services;

use App\Modules\ClassroomNotifications\Models\NotificationLogModel;
use App\Modules\ClassroomNotifications\Models\NotificationTemplateModel;
use App\Modules\ClassroomNotifications\Models\ParentContactModel;
use App\Modules\ClassroomNotifications\Models\WhatsAppConnectionModel;

/**
 * WhatsApp Service
 * 
 * Handles WhatsApp message sending via WABLAS API
 */
class WhatsAppService
{
    protected $baseUrl;
    protected $token;
    protected $secretKey;
    protected $notificationLogModel;
    protected $templateModel;
    protected $contactModel;
    protected $connectionModel;
    
    public function __construct()
    {
        $this->baseUrl = env('WABLAS_BASE_URL', 'https://texas.wablas.com');
        $this->token = env('WABLAS_TOKEN', '');
        $this->secretKey = env('WABLAS_SECRET_KEY', '');
        $this->notificationLogModel = new NotificationLogModel();
        $this->templateModel = new NotificationTemplateModel();
        $this->contactModel = new ParentContactModel();
        $this->connectionModel = new WhatsAppConnectionModel();
    }
    
    /**
     * Send classroom notification
     */
    public function sendClassroomNotification(array $data): array
    {
        try {
            // Check WhatsApp connection first
            if (!$this->checkConnection()) {
                return [
                    'success' => false,
                    'message' => 'WhatsApp is not connected. Please check your connection.'
                ];
            }

            // Validate required data
            $this->validateNotificationData($data);

            // Get or create template
            $template = $this->getTemplate($data['event_type'], $data['language'] ?? 'id');
            if (!$template) {
                throw new \Exception('Template not found for event: ' . $data['event_type']);
            }
            
            // Process message template
            $message = $this->templateModel->processTemplate(
                $template['message_template'], 
                $data['variables']
            );
            
            // Log notification
            $logId = $this->notificationLogModel->logNotification([
                'session_id' => $data['session_id'],
                'student_id' => $data['student_id'],
                'parent_phone' => $data['parent_phone'],
                'parent_name' => $data['parent_name'] ?? '',
                'event_type' => $data['event_type'],
                'template_id' => $template['id'],
                'message_content' => $message,
                'variables' => $data['variables']
            ]);
            
            // Send via WABLAS
            $response = $this->sendMessageInternal($data['parent_phone'], $message);
            
            if ($response['success']) {
                $this->notificationLogModel->updateStatus($logId, NotificationLogModel::STATUS_SENT, [
                    'wablas_response' => $response['data']
                ]);
                
                return [
                    'success' => true,
                    'log_id' => $logId,
                    'message' => 'Notification sent successfully',
                    'wablas_response' => $response['data']
                ];
            } else {
                $this->notificationLogModel->updateStatus($logId, NotificationLogModel::STATUS_FAILED, [
                    'failed_reason' => $response['message'],
                    'retry_count' => 0
                ]);
                
                return [
                    'success' => false,
                    'log_id' => $logId,
                    'message' => 'Failed to send notification: ' . $response['message']
                ];
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Send bulk notifications
     */
    public function sendBulkNotifications(array $notifications): array
    {
        $results = [
            'total' => count($notifications),
            'sent' => 0,
            'failed' => 0,
            'details' => []
        ];
        
        foreach ($notifications as $notification) {
            $result = $this->sendClassroomNotification($notification);
            $results['details'][] = $result;
            
            if ($result['success']) {
                $results['sent']++;
            } else {
                $results['failed']++;
            }
            
            // Small delay to avoid rate limiting
            usleep(500000); // 0.5 seconds
        }
        
        return $results;
    }
    
    /**
     * Send simple message via WABLAS API (public method for testing)
     */
    public function sendMessage(string $phone, string $message): array
    {
        return $this->sendMessageInternal($phone, $message);
    }

    /**
     * Send message via WABLAS API (internal method)
     */
    protected function sendMessageInternal(string $phone, string $message): array
    {
        try {
            // Clean phone number
            $phone = $this->cleanPhoneNumber($phone);
            
            // Prepare API request
            $url = rtrim($this->baseUrl, '/') . '/api/send-message';
            $authorization = $this->token . '.' . $this->secretKey;
            
            $data = [
                'phone' => $phone,
                'message' => $message,
                'isGroup' => false
            ];
            
            // Send request
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: ' . $authorization
                ],
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                throw new \Exception('CURL Error: ' . $error);
            }
            
            if ($httpCode !== 200) {
                throw new \Exception('HTTP Error: ' . $httpCode);
            }
            
            $responseData = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response');
            }
            
            // Check WABLAS response format
            if (isset($responseData['status']) && $responseData['status'] === true) {
                return [
                    'success' => true,
                    'data' => $responseData
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $responseData['message'] ?? 'Unknown error from WABLAS',
                    'data' => $responseData
                ];
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Test WABLAS connection
     */
    public function testConnection(): array
    {
        try {
            $url = rtrim($this->baseUrl, '/') . '/api/device/status';
            $authorization = $this->token . '.' . $this->secretKey;
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: ' . $authorization
                ],
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                return [
                    'success' => false,
                    'message' => 'Connection failed: ' . $error
                ];
            }
            
            if ($httpCode === 200) {
                $data = json_decode($response, true);
                return [
                    'success' => true,
                    'message' => 'Connection successful',
                    'data' => $data
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'HTTP Error: ' . $httpCode
                ];
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Clean phone number format
     */
    protected function cleanPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Convert to international format
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        } elseif (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }
        
        return $phone;
    }
    
    /**
     * Get template for event type
     */
    protected function getTemplate(string $eventType, string $language = 'id'): ?array
    {
        return $this->templateModel->getDefaultTemplate($eventType, $language);
    }
    
    /**
     * Validate notification data
     */
    protected function validateNotificationData(array $data): void
    {
        $required = ['session_id', 'student_id', 'parent_phone', 'event_type', 'variables'];
        
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new \Exception("Missing required field: {$field}");
            }
        }
        
        if (empty($data['parent_phone'])) {
            throw new \Exception('Parent phone number is required');
        }
        
        $validEvents = [
            NotificationTemplateModel::EVENT_SESSION_START,
            NotificationTemplateModel::EVENT_SESSION_BREAK,
            NotificationTemplateModel::EVENT_SESSION_RESUME,
            NotificationTemplateModel::EVENT_SESSION_FINISH
        ];
        
        if (!in_array($data['event_type'], $validEvents)) {
            throw new \Exception('Invalid event type: ' . $data['event_type']);
        }
    }
    
    /**
     * Retry failed notifications
     */
    public function retryFailedNotifications(): array
    {
        $failedNotifications = $this->notificationLogModel->getFailedNotifications();
        $results = [
            'total' => count($failedNotifications),
            'retried' => 0,
            'success' => 0,
            'failed' => 0
        ];
        
        foreach ($failedNotifications as $notification) {
            // Reconstruct notification data
            $variables = json_decode($notification['variables_used'], true) ?? [];
            
            $notificationData = [
                'session_id' => $notification['session_id'],
                'student_id' => $notification['student_id'],
                'parent_phone' => $notification['parent_phone'],
                'parent_name' => $notification['parent_name'],
                'event_type' => $notification['event_type'],
                'variables' => $variables
            ];
            
            $result = $this->sendClassroomNotification($notificationData);
            $results['retried']++;
            
            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
                // Increment retry count
                $retryCount = ($notification['retry_count'] ?? 0) + 1;
                $this->notificationLogModel->updateStatus($notification['id'], NotificationLogModel::STATUS_FAILED, [
                    'retry_count' => $retryCount,
                    'failed_reason' => $result['message']
                ]);
            }
        }
        
        return $results;
    }

    /**
     * Check WhatsApp connection
     */
    public function checkConnection(bool $forceCheck = false): bool
    {
        // Check if we need to perform connection check
        if (!$forceCheck && !$this->connectionModel->needsConnectionCheck()) {
            return $this->connectionModel->isConnected();
        }

        try {
            $result = $this->testConnection();

            if ($result['success']) {
                $this->connectionModel->updateStatus(
                    WhatsAppConnectionModel::STATUS_CONNECTED,
                    [
                        'device_id' => $result['data']['device']['id'] ?? null,
                        'device_name' => $result['data']['device']['name'] ?? null,
                        'quota_remaining' => $result['data']['quota'] ?? null,
                        'api_response' => $result['data']
                    ]
                );
                return true;
            } else {
                $this->connectionModel->updateStatus(
                    WhatsAppConnectionModel::STATUS_ERROR,
                    [
                        'error_message' => $result['message'],
                        'api_response' => $result['data'] ?? []
                    ]
                );
                return false;
            }

        } catch (\Exception $e) {
            $this->connectionModel->updateStatus(
                WhatsAppConnectionModel::STATUS_ERROR,
                ['error_message' => $e->getMessage()]
            );
            return false;
        }
    }

    /**
     * Send notification to specific contacts
     */
    public function sendToSpecificContacts(array $contactIds, string $message, array $sessionData = []): array
    {
        // Check connection first
        if (!$this->checkConnection()) {
            return [
                'success' => false,
                'message' => 'WhatsApp is not connected'
            ];
        }

        $results = [
            'total' => count($contactIds),
            'sent' => 0,
            'failed' => 0,
            'details' => []
        ];

        foreach ($contactIds as $contactId) {
            $contact = $this->contactModel->find($contactId);

            if (!$contact || !$contact['is_active'] || !$contact['receive_notifications']) {
                $results['failed']++;
                $results['details'][] = [
                    'contact_id' => $contactId,
                    'success' => false,
                    'message' => 'Contact not found or inactive'
                ];
                continue;
            }

            $phone = $contact['whatsapp_number'] ?: $contact['phone_number'];
            $result = $this->sendMessage($phone, $message);

            if ($result['success']) {
                $results['sent']++;

                // Log notification if session data is provided
                if (!empty($sessionData)) {
                    $this->notificationLogModel->logNotification([
                        'session_id' => $sessionData['session_id'] ?? 0,
                        'student_id' => $contact['student_id'],
                        'parent_phone' => $phone,
                        'parent_name' => $contact['contact_name'],
                        'event_type' => $sessionData['event_type'] ?? 'custom',
                        'message_content' => $message,
                        'variables' => []
                    ]);
                }
            } else {
                $results['failed']++;
            }

            $results['details'][] = [
                'contact_id' => $contactId,
                'contact_name' => $contact['contact_name'],
                'phone' => $phone,
                'success' => $result['success'],
                'message' => $result['message']
            ];

            // Small delay to avoid rate limiting
            usleep(500000); // 0.5 seconds
        }

        return $results;
    }

    /**
     * Send notification to class contacts
     */
    public function sendToClassContacts(int $classId, string $message, array $sessionData = []): array
    {
        $contacts = $this->contactModel->getContactsByClass($classId);
        $contactIds = array_column($contacts, 'id');

        return $this->sendToSpecificContacts($contactIds, $message, $sessionData);
    }

    /**
     * Get connection status
     */
    public function getConnectionStatus(): array
    {
        return $this->connectionModel->getConnectionStats();
    }

    /**
     * Get quota information
     */
    public function getQuotaInfo(): array
    {
        return $this->connectionModel->getQuotaInfo();
    }

    /**
     * Validate WhatsApp configuration
     */
    public function validateConfiguration(): array
    {
        $errors = [];

        if (empty($this->baseUrl)) {
            $errors[] = 'WABLAS Base URL is not configured';
        }

        if (empty($this->token)) {
            $errors[] = 'WABLAS Token is not configured';
        }

        if (empty($this->secretKey)) {
            $errors[] = 'WABLAS Secret Key is not configured';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
