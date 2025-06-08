<?php

namespace App\Modules\WhatsAppIntegration\Controllers;

use App\Controllers\BaseController;
use App\Modules\WhatsAppIntegration\Models\WaDeviceModel;
use App\Modules\WhatsAppIntegration\Models\WaMessageModel;
use App\Modules\WhatsAppIntegration\Models\WaContactModel;
use App\Modules\WhatsAppIntegration\Models\WaTemplateModel;
use App\Modules\WhatsAppIntegration\Services\WhatsAppGatewayService;

class ApiController extends BaseController
{
    protected $deviceModel;
    protected $messageModel;
    protected $contactModel;
    protected $templateModel;
    protected $gatewayService;

    public function __construct()
    {
        $this->deviceModel = new WaDeviceModel();
        $this->messageModel = new WaMessageModel();
        $this->contactModel = new WaContactModel();
        $this->templateModel = new WaTemplateModel();
        $this->gatewayService = new WhatsAppGatewayService();
    }

    /**
     * Send single message via API
     */
    public function sendMessage()
    {
        try {
            // Validate API key
            if (!$this->validateApiKey()) {
                return $this->response->setStatusCode(401)->setJSON([
                    'success' => false,
                    'message' => 'Invalid API key'
                ]);
            }

            $data = $this->request->getJSON(true);
            
            // Validate required fields
            $validation = \Config\Services::validation();
            $validation->setRules([
                'device_id' => 'required|integer',
                'phone_number' => 'required|min_length[10]',
                'message' => 'required|min_length[1]'
            ]);

            if (!$validation->run($data)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'errors' => $validation->getErrors()
                ]);
            }

            // Send message
            $result = $this->gatewayService->sendMessage(
                $data['device_id'],
                $data['phone_number'],
                $data['message'],
                $data['schedule_time'] ?? null
            );

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send bulk messages via API
     */
    public function sendBulk()
    {
        try {
            if (!$this->validateApiKey()) {
                return $this->response->setStatusCode(401)->setJSON([
                    'success' => false,
                    'message' => 'Invalid API key'
                ]);
            }

            $data = $this->request->getJSON(true);
            
            $validation = \Config\Services::validation();
            $validation->setRules([
                'device_id' => 'required|integer',
                'contacts' => 'required|is_array',
                'message' => 'required|min_length[1]'
            ]);

            if (!$validation->run($data)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'errors' => $validation->getErrors()
                ]);
            }

            $result = $this->gatewayService->sendBulkMessage(
                $data['device_id'],
                $data['contacts'],
                $data['message'],
                $data['template_id'] ?? null
            );

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Bulk messages processed',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get device status
     */
    public function deviceStatus($deviceId)
    {
        try {
            if (!$this->validateApiKey()) {
                return $this->response->setStatusCode(401)->setJSON([
                    'success' => false,
                    'message' => 'Invalid API key'
                ]);
            }

            $device = $this->deviceModel->find($deviceId);
            if (!$device) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Device not found'
                ]);
            }

            $stats = $this->deviceModel->getDeviceStats($deviceId);
            $status = $this->gatewayService->checkDeviceStatus($device);

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'device' => $device,
                    'stats' => $stats,
                    'status' => $status
                ]
            ]);

        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get message status
     */
    public function messageStatus($messageId)
    {
        try {
            if (!$this->validateApiKey()) {
                return $this->response->setStatusCode(401)->setJSON([
                    'success' => false,
                    'message' => 'Invalid API key'
                ]);
            }

            $message = $this->messageModel->find($messageId);
            if (!$message) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Message not found'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $message
            ]);

        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get contacts
     */
    public function contacts()
    {
        try {
            if (!$this->validateApiKey()) {
                return $this->response->setStatusCode(401)->setJSON([
                    'success' => false,
                    'message' => 'Invalid API key'
                ]);
            }

            $type = $this->request->getGet('type');
            $search = $this->request->getGet('search');

            if ($search) {
                $contacts = $this->contactModel->searchContacts($search);
            } elseif ($type) {
                $contacts = $this->contactModel->getContactsByType($type);
            } else {
                $contacts = $this->contactModel->getAllContacts();
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $contacts
            ]);

        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get templates
     */
    public function templates()
    {
        try {
            if (!$this->validateApiKey()) {
                return $this->response->setStatusCode(401)->setJSON([
                    'success' => false,
                    'message' => 'Invalid API key'
                ]);
            }

            $type = $this->request->getGet('type');
            
            if ($type) {
                $templates = $this->templateModel->getTemplatesByType($type);
            } else {
                $templates = $this->templateModel->getActiveTemplates();
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $templates
            ]);

        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Schedule message
     */
    public function scheduleMessage()
    {
        try {
            if (!$this->validateApiKey()) {
                return $this->response->setStatusCode(401)->setJSON([
                    'success' => false,
                    'message' => 'Invalid API key'
                ]);
            }

            $data = $this->request->getJSON(true);
            
            $validation = \Config\Services::validation();
            $validation->setRules([
                'device_id' => 'required|integer',
                'phone_number' => 'required|min_length[10]',
                'message' => 'required|min_length[1]',
                'schedule_time' => 'required|valid_date[Y-m-d H:i:s]'
            ]);

            if (!$validation->run($data)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'errors' => $validation->getErrors()
                ]);
            }

            $result = $this->gatewayService->sendMessage(
                $data['device_id'],
                $data['phone_number'],
                $data['message'],
                $data['schedule_time']
            );

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Message scheduled successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get queue status
     */
    public function queueStatus()
    {
        try {
            if (!$this->validateApiKey()) {
                return $this->response->setStatusCode(401)->setJSON([
                    'success' => false,
                    'message' => 'Invalid API key'
                ]);
            }

            $stats = [
                'pending_messages' => $this->messageModel->getPendingCount(),
                'failed_messages' => $this->messageModel->getFailedCount(),
                'sent_today' => $this->messageModel->getSentTodayCount(),
                'total_messages' => $this->messageModel->countAllResults()
            ];

            return $this->response->setJSON([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get system status
     */
    public function systemStatus()
    {
        try {
            if (!$this->validateApiKey()) {
                return $this->response->setStatusCode(401)->setJSON([
                    'success' => false,
                    'message' => 'Invalid API key'
                ]);
            }

            $deviceStats = [
                'total_devices' => $this->deviceModel->countAllResults(),
                'active_devices' => $this->deviceModel->where('device_status', 1)->countAllResults(),
                'device_status' => $this->gatewayService->checkAllDevicesStatus()
            ];

            $messageStats = $this->messageModel->getMessageStats();
            $contactStats = $this->contactModel->getContactStats();
            $templateStats = $this->templateModel->getTemplateStats();

            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'devices' => $deviceStats,
                    'messages' => $messageStats,
                    'contacts' => $contactStats,
                    'templates' => $templateStats,
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Validate API key
     */
    private function validateApiKey()
    {
        $apiKey = $this->request->getHeaderLine('X-API-Key') ?: $this->request->getGet('api_key');
        
        if (!$apiKey) {
            return false;
        }

        // Get valid API keys from config or database
        $validApiKeys = [
            env('WHATSAPP_API_KEY', 'your_api_key_here'),
            // Add more API keys as needed
        ];

        return in_array($apiKey, $validApiKeys);
    }
}
