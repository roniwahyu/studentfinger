<?php

namespace App\Modules\WhatsAppIntegration\Controllers;

use App\Controllers\BaseController;
use App\Modules\WhatsAppIntegration\Models\WaDeviceModel;
use App\Modules\WhatsAppIntegration\Services\WhatsAppGatewayService;

class DeviceController extends BaseController
{
    protected $deviceModel;
    protected $gatewayService;

    public function __construct()
    {
        $this->deviceModel = new WaDeviceModel();
        $this->gatewayService = new WhatsAppGatewayService();
    }

    /**
     * Device management dashboard
     */
    public function index()
    {
        $data = [
            'title' => 'WhatsApp Devices',
            'devices' => $this->deviceModel->getDevicesWithStats(),
            'deviceTypes' => $this->deviceModel->getDeviceTypes()
        ];

        return view('App\Modules\WhatsAppIntegration\Views\devices\index', $data);
    }

    /**
     * Add new device
     */
    public function create()
    {
        if ($this->request->getMethod() === 'post') {
            $data = $this->request->getPost();
            
            $validation = \Config\Services::validation();
            $validation->setRules([
                'device_name' => 'required|min_length[3]|max_length[100]',
                'device_token' => 'required|min_length[10]',
                'api_url' => 'required|valid_url',
                'device_type' => 'required|in_list[wablas,whatsapp-web,baileys,other]'
            ]);

            if (!$validation->run($data)) {
                return redirect()->back()->withInput()->with('errors', $validation->getErrors());
            }

            try {
                // Test device connection
                $connectionTest = $this->gatewayService->testDeviceConnection($data);
                
                if (!$connectionTest['success']) {
                    return redirect()->back()->withInput()->with('error', 'Device connection failed: ' . $connectionTest['message']);
                }

                // Save device
                $deviceData = [
                    'device_name' => $data['device_name'],
                    'device_token' => $data['device_token'],
                    'api_url' => $data['api_url'],
                    'device_type' => $data['device_type'],
                    'device_status' => 1,
                    'webhook_url' => base_url('whatsappintegration/webhook/' . uniqid()),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $deviceId = $this->deviceModel->insert($deviceData);

                return redirect()->to('whatsappintegration/devices')->with('success', 'Device added successfully');
            } catch (\Exception $e) {
                return redirect()->back()->withInput()->with('error', $e->getMessage());
            }
        }

        $data = [
            'title' => 'Add New Device',
            'deviceTypes' => [
                'wablas' => 'Wablas API',
                'whatsapp-web' => 'WhatsApp Web',
                'baileys' => 'Baileys (Multi-Device)',
                'other' => 'Other API'
            ]
        ];

        return view('App\Modules\WhatsAppIntegration\Views\devices\create', $data);
    }

    /**
     * Edit device
     */
    public function edit($id)
    {
        $device = $this->deviceModel->find($id);
        if (!$device) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Device not found');
        }

        if ($this->request->getMethod() === 'post') {
            $data = $this->request->getPost();
            
            $validation = \Config\Services::validation();
            $validation->setRules([
                'device_name' => 'required|min_length[3]|max_length[100]',
                'device_token' => 'required|min_length[10]',
                'api_url' => 'required|valid_url'
            ]);

            if (!$validation->run($data)) {
                return redirect()->back()->withInput()->with('errors', $validation->getErrors());
            }

            try {
                $updateData = [
                    'device_name' => $data['device_name'],
                    'device_token' => $data['device_token'],
                    'api_url' => $data['api_url'],
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $this->deviceModel->update($id, $updateData);

                return redirect()->to('whatsappintegration/devices')->with('success', 'Device updated successfully');
            } catch (\Exception $e) {
                return redirect()->back()->withInput()->with('error', $e->getMessage());
            }
        }

        $data = [
            'title' => 'Edit Device',
            'device' => $device
        ];

        return view('App\Modules\WhatsAppIntegration\Views\devices\edit', $data);
    }

    /**
     * Delete device
     */
    public function delete($id)
    {
        try {
            $device = $this->deviceModel->find($id);
            if (!$device) {
                throw new \Exception('Device not found');
            }

            $this->deviceModel->delete($id);

            return redirect()->to('whatsappintegration/devices')->with('success', 'Device deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Test device connection
     */
    public function testConnection($id)
    {
        try {
            $device = $this->deviceModel->find($id);
            if (!$device) {
                throw new \Exception('Device not found');
            }

            $result = $this->gatewayService->testDeviceConnection($device);

            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Toggle device status
     */
    public function toggleStatus($id)
    {
        try {
            $device = $this->deviceModel->find($id);
            if (!$device) {
                throw new \Exception('Device not found');
            }

            $newStatus = $device['device_status'] == 1 ? 0 : 1;
            
            $this->deviceModel->update($id, [
                'device_status' => $newStatus,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Device status updated',
                'status' => $newStatus
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get device statistics
     */
    public function getStats($id)
    {
        try {
            $device = $this->deviceModel->find($id);
            if (!$device) {
                throw new \Exception('Device not found');
            }

            $stats = $this->deviceModel->getDeviceStats($id);

            return $this->response->setJSON([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Sync device contacts
     */
    public function syncContacts($id)
    {
        try {
            $device = $this->deviceModel->find($id);
            if (!$device) {
                throw new \Exception('Device not found');
            }

            $result = $this->gatewayService->syncDeviceContacts($id);

            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get QR Code for device connection (for WhatsApp Web type)
     */
    public function getQRCode($id)
    {
        try {
            $device = $this->deviceModel->find($id);
            if (!$device) {
                throw new \Exception('Device not found');
            }

            if ($device['device_type'] !== 'whatsapp-web') {
                throw new \Exception('QR Code only available for WhatsApp Web devices');
            }

            $qrCode = $this->gatewayService->getDeviceQRCode($id);

            return $this->response->setJSON([
                'success' => true,
                'qr_code' => $qrCode
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
