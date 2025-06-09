<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Device Authentication Filter
 * 
 * Handles authentication for device-specific API endpoints
 */
class DeviceAuthFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Get device authentication credentials
        $deviceId = $request->getHeaderLine('X-Device-ID');
        $deviceKey = $request->getHeaderLine('X-Device-Key');
        
        // Alternative: check in POST data for device credentials
        if (empty($deviceId) || empty($deviceKey)) {
            $postData = $request->getJSON(true) ?? $request->getPost();
            $deviceId = $postData['device_id'] ?? $request->getGet('device_id');
            $deviceKey = $postData['device_key'] ?? $request->getGet('device_key');
        }
        
        if (empty($deviceId) || empty($deviceKey)) {
            return $this->unauthorizedResponse('Missing device credentials');
        }
        
        // Validate device credentials
        if (!$this->validateDeviceCredentials($deviceId, $deviceKey)) {
            return $this->unauthorizedResponse('Invalid device credentials');
        }
        
        // Store device info in request for later use
        $request->deviceId = $deviceId;
        
        return $request;
    }
    
    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing to do after the request
    }
    
    /**
     * Validate device credentials
     * 
     * @param string $deviceId
     * @param string $deviceKey
     * @return bool
     */
    protected function validateDeviceCredentials(string $deviceId, string $deviceKey): bool
    {
        // For demo purposes, accept specific device credentials
        $validDevices = [
            'DEVICE_001' => 'device_key_123456789',
            'DEVICE_002' => 'device_key_987654321',
            'DEMO_DEVICE' => 'demo_device_key_12345'
        ];
        
        if (isset($validDevices[$deviceId]) && $validDevices[$deviceId] === $deviceKey) {
            return true;
        }
        
        // For development, accept any device with 'dev_' prefix
        if (ENVIRONMENT === 'development' && strpos($deviceId, 'dev_') === 0) {
            return true;
        }
        
        // In production, you would typically:
        // 1. Query the database for the device
        // 2. Check if the device is active and not suspended
        // 3. Validate the device key/secret
        // 4. Update last seen timestamp
        
        // Example database validation (commented out):
        /*
        $db = \Config\Database::connect();
        $device = $db->table('devices')
                    ->where('device_id', $deviceId)
                    ->where('device_key', $deviceKey)
                    ->where('is_active', 1)
                    ->where('is_suspended', 0)
                    ->get()
                    ->getRow();
        
        if ($device) {
            // Update last seen
            $db->table('devices')
               ->where('id', $device->id)
               ->update(['last_seen' => date('Y-m-d H:i:s')]);
            
            return true;
        }
        */
        
        return false;
    }
    
    /**
     * Return unauthorized response
     * 
     * @param string $message
     * @return ResponseInterface
     */
    protected function unauthorizedResponse(string $message): ResponseInterface
    {
        $response = service('response');
        
        return $response->setJSON([
            'success' => false,
            'error' => $message,
            'code' => 401
        ])->setStatusCode(401);
    }
}
