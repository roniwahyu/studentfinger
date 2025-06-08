<?php

namespace App\Libraries;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use CodeIgniter\Config\Services;

/**
 * Wablas WhatsApp API Library
 * 
 * Comprehensive library for integrating with Wablas.com WhatsApp API
 * Supports both API v1 and v2 endpoints
 */
class WablasApi
{
    /**
     * HTTP Client
     */
    protected Client $client;
    
    /**
     * API Configuration
     */
    protected array $config;
    
    /**
     * Base URL for Wablas API
     */
    protected string $baseUrl = 'https://wablas.com';
    
    /**
     * API Token
     */
    protected string $token;
    
    /**
     * Secret Key
     */
    protected string $secretKey;
    
    /**
     * Logger instance
     */
    protected $logger;
    
    /**
     * Constructor
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->token = $config['token'] ?? '';
        $this->secretKey = $config['secret_key'] ?? '';
        $this->baseUrl = $config['base_url'] ?? 'https://wablas.com';
        
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
            'verify' => false
        ]);
        
        $this->logger = Services::logger();
    }
    
    /**
     * Set API credentials
     */
    public function setCredentials(string $token, string $secretKey = ''): self
    {
        $this->token = $token;
        $this->secretKey = $secretKey;
        return $this;
    }
    
    /**
     * Set base URL
     */
    public function setBaseUrl(string $url): self
    {
        $this->baseUrl = rtrim($url, '/');
        return $this;
    }
    
    // ========================================
    // DEVICE MANAGEMENT METHODS
    // ========================================
    
    /**
     * Get device information
     */
    public function getDeviceInfo(): array
    {
        return $this->makeRequest('GET', '/api/device/info', [
            'query' => ['token' => $this->token]
        ]);
    }
    
    /**
     * Create new device
     */
    public function createDevice(array $data): array
    {
        $requiredFields = ['name', 'phone', 'bank', 'product'];
        $this->validateRequiredFields($data, $requiredFields);
        
        return $this->makeRequest('POST', '/api/device/create', [
            'headers' => $this->getAuthHeaders(true),
            'form_params' => $data
        ]);
    }
    
    /**
     * Get device QR code for scanning
     */
    public function getDeviceQrCode(): string
    {
        $url = $this->baseUrl . '/api/device/scan?token=' . $this->token;
        return $url; // Returns URL to QR code image
    }
    
    /**
     * Change device number
     */
    public function changeDeviceNumber(string $phone): array
    {
        return $this->makeRequest('POST', '/api/device/change-number', [
            'headers' => $this->getAuthHeaders(true),
            'form_params' => ['phone' => $phone]
        ]);
    }
    
    /**
     * Change device webhook URL
     */
    public function changeWebhookUrl(string $webhookUrl): array
    {
        return $this->makeRequest('POST', '/api/device/change-webhook-url', [
            'headers' => $this->getAuthHeaders(true),
            'form_params' => ['webhook_url' => $webhookUrl]
        ]);
    }
    
    /**
     * Change device tracking URL
     */
    public function changeTrackingUrl(string $trackingUrl): array
    {
        return $this->makeRequest('POST', '/api/device/change-tracking-url', [
            'headers' => $this->getAuthHeaders(true),
            'form_params' => ['tracking_url' => $trackingUrl]
        ]);
    }
    
    /**
     * Disconnect device
     */
    public function disconnectDevice(): array
    {
        return $this->makeRequest('GET', '/api/device/disconnect', [
            'headers' => $this->getAuthHeaders(true)
        ]);
    }
    
    /**
     * Restart device
     */
    public function restartDevice(): array
    {
        return $this->makeRequest('GET', '/api/device/restart', [
            'headers' => $this->getAuthHeaders(true)
        ]);
    }
    
    /**
     * Delete device
     */
    public function deleteDevice(): array
    {
        return $this->makeRequest('DELETE', '/api/device/delete', [
            'headers' => $this->getAuthHeaders(true)
        ]);
    }
    
    /**
     * Generate new device token
     */
    public function generateDeviceToken(): array
    {
        return $this->makeRequest('GET', '/api/device/generate-token', [
            'headers' => $this->getAuthHeaders(true)
        ]);
    }
    
    /**
     * Set device speed (delay between messages)
     */
    public function setDeviceSpeed(int $delay): array
    {
        if ($delay < 10 || $delay > 120) {
            throw new \InvalidArgumentException('Delay must be between 10 and 120 seconds');
        }
        
        return $this->makeRequest('POST', '/api/device/speed', [
            'headers' => $this->getAuthHeaders(true),
            'form_params' => ['delay' => $delay]
        ]);
    }
    
    // ========================================
    // PHONE VERIFICATION METHODS
    // ========================================
    
    /**
     * Check if phone numbers are active on WhatsApp
     */
    public function checkPhoneNumbers(array $phones): array
    {
        $phoneString = implode(',', $phones);
        
        return $this->makeRequest('GET', '/check-phone-number', [
            'base_uri' => 'https://phone.wablas.com',
            'headers' => [
                'Authorization' => $this->token,
                'url' => 'https://wablas.com'
            ],
            'query' => ['phones' => $phoneString]
        ]);
    }
    
    // ========================================
    // MESSAGE SENDING METHODS (API V1)
    // ========================================
    
    /**
     * Send simple text message (GET method)
     */
    public function sendSimpleMessage(string $phone, string $message, array $options = []): array
    {
        $params = array_merge([
            'token' => $this->token . ($this->secretKey ? '.' . $this->secretKey : ''),
            'phone' => $phone,
            'message' => $message
        ], $options);
        
        return $this->makeRequest('GET', '/api/send-message', [
            'query' => $params
        ]);
    }
    
    /**
     * Send text message (POST method)
     */
    public function sendMessage(string $phone, string $message, array $options = []): array
    {
        $data = array_merge([
            'phone' => $phone,
            'message' => $message
        ], $options);
        
        return $this->makeRequest('POST', '/api/send-message', [
            'headers' => $this->getAuthHeaders(),
            'form_params' => $data
        ]);
    }
    
    /**
     * Send image message
     */
    public function sendImage(string $phone, string $imageUrl, string $caption = '', array $options = []): array
    {
        $data = array_merge([
            'phone' => $phone,
            'image' => $imageUrl,
            'caption' => $caption
        ], $options);
        
        return $this->makeRequest('POST', '/api/send-image', [
            'headers' => $this->getAuthHeaders(),
            'form_params' => $data
        ]);
    }
    
    /**
     * Send document message
     */
    public function sendDocument(string $phone, string $documentUrl, string $filename = '', array $options = []): array
    {
        $data = array_merge([
            'phone' => $phone,
            'document' => $documentUrl,
            'filename' => $filename
        ], $options);
        
        return $this->makeRequest('POST', '/api/send-document', [
            'headers' => $this->getAuthHeaders(),
            'form_params' => $data
        ]);
    }
    
    /**
     * Send video message
     */
    public function sendVideo(string $phone, string $videoUrl, string $caption = '', array $options = []): array
    {
        $data = array_merge([
            'phone' => $phone,
            'video' => $videoUrl,
            'caption' => $caption
        ], $options);
        
        return $this->makeRequest('POST', '/api/send-video', [
            'headers' => $this->getAuthHeaders(),
            'form_params' => $data
        ]);
    }
    
    /**
     * Send audio message
     */
    public function sendAudio(string $phone, string $audioUrl, array $options = []): array
    {
        $data = array_merge([
            'phone' => $phone,
            'audio' => $audioUrl
        ], $options);
        
        return $this->makeRequest('POST', '/api/send-audio', [
            'headers' => $this->getAuthHeaders(),
            'form_params' => $data
        ]);
    }
    
    // ========================================
    // MESSAGE SENDING METHODS (API V2)
    // ========================================

    /**
     * Send multiple messages (API v2)
     */
    public function sendMultipleMessages(array $messages): array
    {
        $payload = ['data' => $messages];

        return $this->makeRequest('POST', '/api/v2/send-message', [
            'headers' => array_merge($this->getAuthHeaders(), [
                'Content-Type' => 'application/json'
            ]),
            'json' => $payload
        ]);
    }

    /**
     * Send multiple images (API v2)
     */
    public function sendMultipleImages(array $images): array
    {
        $payload = ['data' => $images];

        return $this->makeRequest('POST', '/api/v2/send-image', [
            'headers' => array_merge($this->getAuthHeaders(), [
                'Content-Type' => 'application/json'
            ]),
            'json' => $payload
        ]);
    }

    /**
     * Send multiple documents (API v2)
     */
    public function sendMultipleDocuments(array $documents): array
    {
        $payload = ['data' => $documents];

        return $this->makeRequest('POST', '/api/v2/send-document', [
            'headers' => array_merge($this->getAuthHeaders(), [
                'Content-Type' => 'application/json'
            ]),
            'json' => $payload
        ]);
    }

    /**
     * Send multiple videos (API v2)
     */
    public function sendMultipleVideos(array $videos): array
    {
        $payload = ['data' => $videos];

        return $this->makeRequest('POST', '/api/v2/send-video', [
            'headers' => array_merge($this->getAuthHeaders(), [
                'Content-Type' => 'application/json'
            ]),
            'json' => $payload
        ]);
    }

    /**
     * Send multiple audio messages (API v2)
     */
    public function sendMultipleAudio(array $audios): array
    {
        $payload = ['data' => $audios];

        return $this->makeRequest('POST', '/api/v2/send-audio', [
            'headers' => array_merge($this->getAuthHeaders(), [
                'Content-Type' => 'application/json'
            ]),
            'json' => $payload
        ]);
    }

    /**
     * Send link with preview (API v2)
     */
    public function sendLink(array $links): array
    {
        $payload = ['data' => $links];

        return $this->makeRequest('POST', '/api/v2/send-link', [
            'headers' => array_merge($this->getAuthHeaders(), [
                'Content-Type' => 'application/json'
            ]),
            'json' => $payload
        ]);
    }

    /**
     * Send list message (API v2)
     */
    public function sendList(array $lists): array
    {
        $payload = ['data' => $lists];

        return $this->makeRequest('POST', '/api/v2/send-list', [
            'headers' => array_merge($this->getAuthHeaders(), [
                'Content-Type' => 'application/json'
            ]),
            'json' => $payload
        ]);
    }

    /**
     * Send location (API v2)
     */
    public function sendLocation(array $locations): array
    {
        $payload = ['data' => $locations];

        return $this->makeRequest('POST', '/api/v2/send-location', [
            'headers' => array_merge($this->getAuthHeaders(), [
                'Content-Type' => 'application/json'
            ]),
            'json' => $payload
        ]);
    }

    // ========================================
    // SCHEDULE METHODS
    // ========================================

    /**
     * Send scheduled message (API v2)
     */
    public function sendScheduledMessage(array $schedules): array
    {
        $payload = ['data' => $schedules];

        return $this->makeRequest('POST', '/api/v2/send-schedule', [
            'headers' => array_merge($this->getAuthHeaders(), [
                'Content-Type' => 'application/json'
            ]),
            'json' => $payload
        ]);
    }

    /**
     * Update scheduled message
     */
    public function updateSchedule(string $scheduleId, array $data): array
    {
        return $this->makeRequest('PUT', "/api/v2/schedule/{$scheduleId}", [
            'headers' => array_merge($this->getAuthHeaders(), [
                'Content-Type' => 'application/json'
            ]),
            'json' => $data
        ]);
    }

    /**
     * Delete scheduled message
     */
    public function deleteSchedule(string $scheduleId): array
    {
        return $this->makeRequest('DELETE', "/api/v2/schedule/{$scheduleId}", [
            'headers' => $this->getAuthHeaders()
        ]);
    }

    // ========================================
    // GROUP METHODS
    // ========================================

    /**
     * Send message to group
     */
    public function sendGroupMessage(string $groupId, string $message, array $options = []): array
    {
        $data = array_merge([
            'phone' => $groupId,
            'message' => $message,
            'isGroup' => 'true'
        ], $options);

        return $this->makeRequest('POST', '/api/send-message', [
            'headers' => $this->getAuthHeaders(),
            'form_params' => $data
        ]);
    }

    /**
     * Send image to group
     */
    public function sendGroupImage(string $groupId, string $imageUrl, string $caption = '', array $options = []): array
    {
        $data = array_merge([
            'phone' => $groupId,
            'image' => $imageUrl,
            'caption' => $caption,
            'isGroup' => 'true'
        ], $options);

        return $this->makeRequest('POST', '/api/send-image', [
            'headers' => $this->getAuthHeaders(),
            'form_params' => $data
        ]);
    }

    // ========================================
    // UTILITY METHODS
    // ========================================
    
    /**
     * Get authorization headers
     */
    protected function getAuthHeaders(bool $includeSecret = false): array
    {
        $token = $this->token;
        if ($includeSecret && $this->secretKey) {
            $token .= '.' . $this->secretKey;
        }
        
        return [
            'Authorization' => $token,
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
    }
    
    /**
     * Validate required fields
     */
    protected function validateRequiredFields(array $data, array $requiredFields): void
    {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new \InvalidArgumentException("Required field '{$field}' is missing or empty");
            }
        }
    }
    
    /**
     * Make HTTP request to Wablas API
     */
    protected function makeRequest(string $method, string $endpoint, array $options = []): array
    {
        try {
            $this->logger->info('Wablas API Request', [
                'method' => $method,
                'endpoint' => $endpoint,
                'options' => $options
            ]);
            
            $response = $this->client->request($method, $endpoint, $options);
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);
            
            $this->logger->info('Wablas API Response', [
                'status_code' => $response->getStatusCode(),
                'response' => $data
            ]);
            
            return [
                'success' => true,
                'status_code' => $response->getStatusCode(),
                'data' => $data,
                'raw_response' => $body
            ];
            
        } catch (RequestException $e) {
            $errorMessage = $e->getMessage();
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 0;
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : '';
            
            $this->logger->error('Wablas API Error', [
                'error' => $errorMessage,
                'status_code' => $statusCode,
                'response' => $responseBody
            ]);
            
            return [
                'success' => false,
                'error' => $errorMessage,
                'status_code' => $statusCode,
                'response' => $responseBody
            ];
        }
    }

    // ========================================
    // CONTACT MANAGEMENT METHODS
    // ========================================

    /**
     * Create multiple contacts (API v2)
     */
    public function createContacts(array $contacts): array
    {
        $payload = ['data' => $contacts];

        return $this->makeRequest('POST', '/api/v2/contact', [
            'headers' => array_merge($this->getAuthHeaders(true), [
                'Content-Type' => 'application/json'
            ]),
            'json' => $payload
        ]);
    }

    /**
     * Update contacts (API v2)
     */
    public function updateContacts(array $contacts): array
    {
        $payload = ['data' => $contacts];

        return $this->makeRequest('POST', '/api/v2/contact/update', [
            'headers' => array_merge($this->getAuthHeaders(true), [
                'Content-Type' => 'application/json'
            ]),
            'json' => $payload
        ]);
    }

    /**
     * Delete contact
     */
    public function deleteContact(string $phone): array
    {
        return $this->makeRequest('DELETE', '/api/contact', [
            'headers' => array_merge($this->getAuthHeaders(true), [
                'Content-Type' => 'application/json'
            ]),
            'query' => ['phone' => $phone]
        ]);
    }

    /**
     * List contacts (API v2)
     */
    public function listContacts(array $params = []): array
    {
        return $this->makeRequest('GET', '/api/v2/contact', [
            'headers' => array_merge($this->getAuthHeaders(true), [
                'Content-Type' => 'application/json'
            ]),
            'query' => $params
        ]);
    }

    // ========================================
    // AUTO REPLY METHODS
    // ========================================

    /**
     * Create auto reply
     */
    public function createAutoReply(string $keyword, string $response): array
    {
        $data = [
            'keyword' => $keyword,
            'response' => $response
        ];

        return $this->makeRequest('POST', '/api/v2/autoreply', [
            'headers' => array_merge($this->getAuthHeaders(true), [
                'Content-Type' => 'application/json'
            ]),
            'json' => $data
        ]);
    }

    /**
     * Update auto reply
     */
    public function updateAutoReply(string $id, string $keyword, string $response): array
    {
        $data = [
            'keyword' => $keyword,
            'response' => $response
        ];

        return $this->makeRequest('PUT', "/api/v2/autoreply/{$id}", [
            'headers' => array_merge($this->getAuthHeaders(true), [
                'Content-Type' => 'application/json'
            ]),
            'json' => $data
        ]);
    }

    /**
     * Delete auto reply
     */
    public function deleteAutoReply(string $id): array
    {
        return $this->makeRequest('DELETE', "/api/v2/autoreply/{$id}", [
            'headers' => array_merge($this->getAuthHeaders(true), [
                'Content-Type' => 'application/json'
            ])
        ]);
    }

    /**
     * Get auto reply data
     */
    public function getAutoReplyData(string $keyword = ''): array
    {
        $params = [];
        if (!empty($keyword)) {
            $params['keyword'] = $keyword;
        }

        return $this->makeRequest('GET', '/api/v2/autoreply/getData', [
            'headers' => array_merge($this->getAuthHeaders(true), [
                'Content-Type' => 'application/json'
            ]),
            'query' => $params
        ]);
    }

    // ========================================
    // REPORTING METHODS
    // ========================================

    /**
     * Get message report
     */
    public function getMessageReport(array $params = []): array
    {
        return $this->makeRequest('GET', '/api/report-message', [
            'headers' => $this->getAuthHeaders(),
            'query' => array_merge(['token' => $this->token], $params)
        ]);
    }

    /**
     * Get realtime report
     */
    public function getRealtimeReport(array $params = []): array
    {
        return $this->makeRequest('GET', '/api/report-realtime', [
            'headers' => $this->getAuthHeaders(),
            'query' => array_merge(['token' => $this->token], $params)
        ]);
    }

    // ========================================
    // FILE UPLOAD METHODS
    // ========================================

    /**
     * Upload file to Wablas server
     */
    public function uploadFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("File not found: {$filePath}");
        }

        return $this->makeRequest('POST', '/api/upload-file', [
            'headers' => $this->getAuthHeaders(),
            'multipart' => [
                [
                    'name' => 'file',
                    'contents' => fopen($filePath, 'r'),
                    'filename' => basename($filePath)
                ]
            ]
        ]);
    }

    // ========================================
    // BLACKLIST METHODS
    // ========================================

    /**
     * Add number to blacklist
     */
    public function addToBlacklist(string $phone): array
    {
        return $this->makeRequest('POST', '/api/blacklist', [
            'headers' => $this->getAuthHeaders(),
            'form_params' => ['phone' => $phone]
        ]);
    }

    /**
     * Remove number from blacklist
     */
    public function removeFromBlacklist(string $phone): array
    {
        return $this->makeRequest('DELETE', '/api/blacklist', [
            'headers' => $this->getAuthHeaders(),
            'form_params' => ['phone' => $phone]
        ]);
    }

    // ========================================
    // MESSAGE CONTROL METHODS
    // ========================================

    /**
     * Resend message by ID
     */
    public function resendMessage(string $messageId): array
    {
        return $this->makeRequest('POST', '/api/resend-message', [
            'headers' => $this->getAuthHeaders(),
            'form_params' => ['id' => $messageId]
        ]);
    }

    /**
     * Revoke message by ID
     */
    public function revokeMessage(string $messageId): array
    {
        return $this->makeRequest('POST', '/api/revoke-message', [
            'headers' => $this->getAuthHeaders(),
            'form_params' => ['id' => $messageId]
        ]);
    }

    /**
     * Cancel pending message by ID
     */
    public function cancelMessage(string $messageId): array
    {
        return $this->makeRequest('POST', '/api/cancel-message', [
            'headers' => $this->getAuthHeaders(),
            'form_params' => ['id' => $messageId]
        ]);
    }

    /**
     * Cancel all pending messages
     */
    public function cancelAllMessages(): array
    {
        return $this->makeRequest('POST', '/api/cancel-all-message', [
            'headers' => $this->getAuthHeaders()
        ]);
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Clean and format phone number
     */
    public function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        // Add country code if not present (assuming Indonesia +62)
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
     * Validate phone number format
     */
    public function isValidPhoneNumber(string $phone): bool
    {
        $cleaned = $this->formatPhoneNumber($phone);
        return preg_match('/^62[0-9]{8,13}$/', $cleaned);
    }

    /**
     * Get API status
     */
    public function getApiStatus(): array
    {
        return $this->makeRequest('GET', '/api/status', [
            'headers' => $this->getAuthHeaders()
        ]);
    }
}
