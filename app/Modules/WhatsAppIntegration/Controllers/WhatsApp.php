<?php

namespace App\Modules\WhatsAppIntegration\Controllers;

use App\Controllers\BaseController;
use App\Modules\WhatsAppIntegration\Models\WaDeviceModel;
use App\Modules\WhatsAppIntegration\Models\WaMessageModel;
use App\Modules\WhatsAppIntegration\Models\WaContactModel;
use App\Modules\WhatsAppIntegration\Models\WaTemplateModel;
use App\Modules\WhatsAppIntegration\Services\WhatsAppGatewayService;

class WhatsApp extends BaseController
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
     * WhatsApp Gateway Dashboard
     */
    public function index()
    {
        $data = [
            'title' => 'WhatsApp Gateway Dashboard',
            'devices' => $this->deviceModel->getActiveDevices(),
            'stats' => $this->getGatewayStats(),
            'recentMessages' => $this->messageModel->getRecentMessages(10),
            'deviceStatus' => $this->gatewayService->checkAllDevicesStatus()
        ];

        return view('App\Modules\WhatsAppIntegration\Views\dashboard\index', $data);
    }

    /**
     * Get gateway statistics
     */
    private function getGatewayStats()
    {
        return [
            'totalDevices' => $this->deviceModel->countAllResults(),
            'activeDevices' => $this->deviceModel->where('device_status', 1)->countAllResults(),
            'totalMessages' => $this->messageModel->countAllResults(),
            'sentToday' => $this->messageModel->getSentTodayCount(),
            'pendingMessages' => $this->messageModel->getPendingCount(),
            'failedMessages' => $this->messageModel->getFailedCount(),
            'totalContacts' => $this->contactModel->countAllResults(),
            'totalTemplates' => $this->templateModel->countAllResults()
        ];
    }

    /**
     * Send message via gateway
     */
    public function sendMessage()
    {
        if ($this->request->getMethod() === 'post') {
            $data = $this->request->getPost();

            $validation = \Config\Services::validation();
            $validation->setRules([
                'device_id' => 'required|integer',
                'phone_number' => 'required|min_length[10]',
                'message' => 'required|min_length[1]'
            ]);

            if (!$validation->run($data)) {
                return $this->response->setJSON([
                    'success' => false,
                    'errors' => $validation->getErrors()
                ]);
            }

            try {
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
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
        }

        $data = [
            'title' => 'Send Message',
            'devices' => $this->deviceModel->getActiveDevices(),
            'templates' => $this->templateModel->findAll(),
            'contacts' => $this->contactModel->getAllContacts()
        ];

        return view('App\Modules\WhatsAppIntegration\Views\messages\send', $data);
    }

    /**
     * Bulk message sending
     */
    public function sendBulkMessage()
    {
        if ($this->request->getMethod() === 'post') {
            $data = $this->request->getPost();

            try {
                $result = $this->gatewayService->sendBulkMessage(
                    $data['device_id'],
                    $data['contacts'],
                    $data['message'],
                    $data['template_id'] ?? null
                );

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Bulk messages queued successfully',
                    'data' => $result
                ]);
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
        }

        $data = [
            'title' => 'Send Bulk Message',
            'devices' => $this->deviceModel->getActiveDevices(),
            'templates' => $this->templateModel->findAll(),
            'contactGroups' => $this->contactModel->getContactGroups()
        ];

        return view('App\Modules\WhatsAppIntegration\Views\messages\bulk', $data);
    }

    /**
     * Message queue management
     */
    public function messageQueue()
    {
        $data = [
            'title' => 'Message Queue',
            'pendingMessages' => $this->messageModel->getPendingMessages(),
            'failedMessages' => $this->messageModel->getFailedMessages(),
            'scheduledMessages' => $this->messageModel->getScheduledMessages()
        ];

        return view('App\Modules\WhatsAppIntegration\Views\messages\queue', $data);
    }

    /**
     * Process message queue
     */
    public function processQueue()
    {
        try {
            $result = $this->gatewayService->processMessageQueue();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Queue processed successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}