<?php

namespace App\Modules\WablasIntegration\Controllers;

use App\Controllers\BaseController;
use App\Libraries\WablasApi;
use App\Modules\WablasIntegration\Services\WablasService;
use App\Modules\WablasIntegration\Models\WablasDeviceModel;
use App\Modules\WablasIntegration\Models\WablasContactModel;

/**
 * Example Controller demonstrating Wablas Integration usage
 */
class ExampleController extends BaseController
{
    protected WablasService $wablasService;
    protected WablasDeviceModel $deviceModel;
    protected WablasContactModel $contactModel;
    
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->wablasService = new WablasService();
        $this->deviceModel = new WablasDeviceModel();
        $this->contactModel = new WablasContactModel();
    }
    
    /**
     * Example index page showing various usage examples
     */
    public function index()
    {
        $data = [
            'title' => 'Wablas Integration Examples',
            'devices' => $this->deviceModel->getActiveDevices(),
            'contacts' => $this->contactModel->getActiveContacts(),
            'examples' => [
                [
                    'name' => 'Send Simple Message',
                    'description' => 'Send a basic text message to a phone number',
                    'endpoint' => 'POST /wablas/examples/send-simple',
                    'method' => 'sendSimpleMessage'
                ],
                [
                    'name' => 'Send Image Message',
                    'description' => 'Send an image with caption',
                    'endpoint' => 'POST /wablas/examples/send-image',
                    'method' => 'sendImageMessage'
                ],
                [
                    'name' => 'Send Bulk Messages',
                    'description' => 'Send personalized messages to multiple contacts',
                    'endpoint' => 'POST /wablas/examples/send-bulk',
                    'method' => 'sendBulkMessages'
                ],
                [
                    'name' => 'Schedule Message',
                    'description' => 'Schedule a message for future delivery',
                    'endpoint' => 'POST /wablas/examples/schedule',
                    'method' => 'scheduleMessage'
                ],
                [
                    'name' => 'Use API Directly',
                    'description' => 'Demonstrate direct usage of WablasApi library',
                    'endpoint' => 'POST /wablas/examples/api-direct',
                    'method' => 'useApiDirectly'
                ],
                [
                    'name' => 'Manage Contacts',
                    'description' => 'Create and manage contacts',
                    'endpoint' => 'POST /wablas/examples/manage-contacts',
                    'method' => 'manageContacts'
                ],
                [
                    'name' => 'Process Scheduled Messages',
                    'description' => 'Manually process pending scheduled messages',
                    'endpoint' => 'POST /wablas/examples/process-schedules',
                    'method' => 'processScheduledMessages'
                ],
                [
                    'name' => 'Get Statistics',
                    'description' => 'Get comprehensive system statistics',
                    'endpoint' => 'GET /wablas/examples/statistics',
                    'method' => 'getStatistics'
                ]
            ]
        ];

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Wablas Integration Examples',
            'data' => $data
        ]);
    }
    
    /**
     * Example 1: Send a simple text message
     */
    public function sendSimpleMessage()
    {
        try {
            // Get the first active device
            $device = $this->deviceModel->getActiveDevices()[0] ?? null;
            
            if (!$device) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'No active device found. Please configure a device first.'
                ]);
            }
            
            // Send a simple message
            $result = $this->wablasService->sendMessage(
                deviceId: $device['id'],
                phoneNumber: '6281234567890', // Replace with actual phone number
                message: 'Hello! This is a test message from Wablas Integration module.'
            );
            
            return $this->response->setJSON($result);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Example 2: Send an image message
     */
    public function sendImageMessage()
    {
        try {
            $device = $this->deviceModel->getActiveDevices()[0] ?? null;
            
            if (!$device) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'No active device found.'
                ]);
            }
            
            $result = $this->wablasService->sendMediaMessage(
                deviceId: $device['id'],
                phoneNumber: '6281234567890',
                mediaUrl: 'https://via.placeholder.com/300x200.png?text=Test+Image',
                type: 'image',
                options: [
                    'caption' => 'This is a test image sent via Wablas Integration!'
                ]
            );
            
            return $this->response->setJSON($result);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Example 3: Send bulk messages to multiple contacts
     */
    public function sendBulkMessages()
    {
        try {
            $device = $this->deviceModel->getActiveDevices()[0] ?? null;
            
            if (!$device) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'No active device found.'
                ]);
            }
            
            // Get some contacts for bulk messaging
            $contacts = $this->contactModel->getActiveContacts();
            
            if (empty($contacts)) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'No contacts found. Please add some contacts first.'
                ]);
            }
            
            // Limit to first 3 contacts for demo
            $recipients = array_slice($contacts, 0, 3);
            
            $result = $this->wablasService->sendBulkMessages(
                deviceId: $device['id'],
                recipients: $recipients,
                message: 'Hello {name}! This is a personalized bulk message. Your phone number is {phone_number}.'
            );
            
            return $this->response->setJSON($result);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Example 4: Schedule a message for future delivery
     */
    public function scheduleMessage()
    {
        try {
            $device = $this->deviceModel->getActiveDevices()[0] ?? null;
            
            if (!$device) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'No active device found.'
                ]);
            }
            
            // Schedule message for 5 minutes from now
            $scheduledTime = date('Y-m-d H:i:s', strtotime('+5 minutes'));
            
            $result = $this->wablasService->scheduleMessage(
                deviceId: $device['id'],
                phoneNumber: '6281234567890',
                message: 'This is a scheduled message sent at ' . $scheduledTime,
                scheduledAt: $scheduledTime
            );
            
            return $this->response->setJSON($result);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Example 5: Using WablasApi library directly
     */
    public function useApiDirectly()
    {
        try {
            $device = $this->deviceModel->getActiveDevices()[0] ?? null;
            
            if (!$device) {
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'No active device found.'
                ]);
            }
            
            // Initialize API with device credentials
            $config = [
                'base_url' => $device['api_url'],
                'token' => $device['token'],
                'secret_key' => $device['secret_key']
            ];
            
            $api = new WablasApi($config);
            
            // Get device information
            $deviceInfo = $api->getDeviceInfo();
            
            // Send a message
            $messageResult = $api->sendMessage(
                '6281234567890',
                'This message was sent using WablasApi directly!'
            );
            
            // Check phone number validity
            $phoneCheck = $api->checkPhoneNumbers(['6281234567890', '6281234567891']);
            
            return $this->response->setJSON([
                'success' => true,
                'device_info' => $deviceInfo,
                'message_result' => $messageResult,
                'phone_check' => $phoneCheck
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Example 6: Create and manage contacts
     */
    public function manageContacts()
    {
        try {
            // Create a new contact
            $contactData = [
                'phone_number' => '6281234567890',
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'address' => '123 Main Street, City',
                'birthday' => '1990-01-15',
                'gender' => 'male',
                'tags' => ['customer', 'vip'],
                'custom_fields' => [
                    'company' => 'Example Corp',
                    'position' => 'Manager'
                ],
                'notes' => 'Important customer',
                'source' => 'manual'
            ];
            
            // Check if contact exists
            $existingContact = $this->contactModel->getByPhoneNumber($contactData['phone_number']);
            
            if ($existingContact) {
                // Update existing contact
                $contactId = $this->contactModel->update($existingContact['id'], $contactData);
                $action = 'updated';
            } else {
                // Create new contact
                $contactId = $this->contactModel->insert($contactData);
                $action = 'created';
            }
            
            // Get the contact
            $contact = $this->contactModel->find($contactId);
            
            // Add a tag
            $this->contactModel->addTag($contactId, 'example');
            
            // Get contact statistics
            $stats = $this->contactModel->getStatistics();
            
            return $this->response->setJSON([
                'success' => true,
                'action' => $action,
                'contact' => $contact,
                'statistics' => $stats
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Example 7: Process scheduled messages manually
     */
    public function processScheduledMessages()
    {
        try {
            $result = $this->wablasService->processScheduledMessages();
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Scheduled messages processed',
                'result' => $result
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Example 8: Get comprehensive statistics
     */
    public function getStatistics()
    {
        try {
            $stats = [
                'devices' => $this->deviceModel->getStatistics(),
                'contacts' => $this->contactModel->getStatistics(),
                'messages_today' => $this->wablasService->messageModel->getStatistics(null, 'today'),
                'messages_week' => $this->wablasService->messageModel->getStatistics(null, 'this_week'),
                'messages_month' => $this->wablasService->messageModel->getStatistics(null, 'this_month')
            ];
            
            return $this->response->setJSON([
                'success' => true,
                'statistics' => $stats
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
