<?php

namespace App\Modules\FingerprintBridge\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Modules\FingerprintBridge\Services\FingerprintImportService;
use App\Modules\FingerprintBridge\Models\ImportLogModel;

/**
 * API Controller for Fingerprint Bridge
 * 
 * Provides REST API endpoints for fingerprint import operations
 */
class ApiController extends ResourceController
{
    protected $format = 'json';
    protected $importService;
    protected $importLogModel;
    
    public function __construct()
    {
        $this->importService = new FingerprintImportService();
        $this->importLogModel = new ImportLogModel();
    }
    
    /**
     * Start import process via API
     * POST /api/fingerprint-bridge/import
     */
    public function import()
    {
        $validation = \Config\Services::validation();
        
        $rules = [
            'start_date' => 'required|valid_date',
            'end_date' => 'required|valid_date',
            'duplicate_handling' => 'permit_empty|in_list[skip,update,error]',
            'batch_size' => 'permit_empty|integer|greater_than[0]'
        ];
        
        if (!$this->validate($rules)) {
            return $this->failValidationErrors($validation->getErrors());
        }
        
        $options = [
            'start_date' => $this->request->getPost('start_date') . ' 00:00:00',
            'end_date' => $this->request->getPost('end_date') . ' 23:59:59',
            'duplicate_handling' => $this->request->getPost('duplicate_handling') ?: 'skip',
            'batch_size' => $this->request->getPost('batch_size') ?: 1000,
            'import_type' => 'api',
            'user_id' => $this->request->getPost('user_id')
        ];
        
        $result = $this->importService->importData($options);
        
        if ($result['success']) {
            return $this->respondCreated($result);
        } else {
            return $this->fail($result['message']);
        }
    }
    
    /**
     * Get import status
     * GET /api/fingerprint-bridge/status?log_id=123
     */
    public function status()
    {
        $logId = $this->request->getGet('log_id');
        
        if (!$logId) {
            return $this->failValidationErrors(['log_id' => 'Log ID is required']);
        }
        
        $log = $this->importLogModel->find($logId);
        
        if (!$log) {
            return $this->failNotFound('Import log not found');
        }
        
        return $this->respond([
            'success' => true,
            'data' => $log
        ]);
    }
    
    /**
     * Get import logs
     * GET /api/fingerprint-bridge/logs
     */
    public function logs()
    {
        $page = $this->request->getGet('page') ?? 1;
        $limit = $this->request->getGet('limit') ?? 20;
        $offset = ($page - 1) * $limit;
        
        $filters = [
            'import_type' => $this->request->getGet('import_type'),
            'status' => $this->request->getGet('status'),
            'start_date' => $this->request->getGet('start_date'),
            'end_date' => $this->request->getGet('end_date')
        ];
        
        $logs = $this->importLogModel->getImportLogs($limit, $offset, $filters);
        
        return $this->respond([
            'success' => true,
            'data' => $logs,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total_items' => count($logs)
            ]
        ]);
    }
    
    /**
     * Get import statistics
     * GET /api/fingerprint-bridge/stats
     */
    public function stats()
    {
        $stats = $this->importService->getImportStats();
        
        return $this->respond([
            'success' => true,
            'data' => $stats
        ]);
    }
    
    /**
     * Test database connection
     * POST /api/fingerprint-bridge/test-connection
     */
    public function testConnection()
    {
        $result = $this->importService->testFinProConnection();
        
        if ($result['success']) {
            return $this->respond($result);
        } else {
            return $this->fail($result['message']);
        }
    }
    
    /**
     * Preview import data
     * POST /api/fingerprint-bridge/preview
     */
    public function preview()
    {
        $validation = \Config\Services::validation();
        
        $rules = [
            'start_date' => 'required|valid_date',
            'end_date' => 'required|valid_date',
            'limit' => 'permit_empty|integer|greater_than[0]'
        ];
        
        if (!$this->validate($rules)) {
            return $this->failValidationErrors($validation->getErrors());
        }
        
        $startDate = $this->request->getPost('start_date') . ' 00:00:00';
        $endDate = $this->request->getPost('end_date') . ' 23:59:59';
        $limit = $this->request->getPost('limit') ?: 10;
        
        $result = $this->importService->previewImport($startDate, $endDate, $limit);
        
        if ($result['success']) {
            return $this->respond($result);
        } else {
            return $this->fail($result['message']);
        }
    }
    
    /**
     * Cancel import
     * POST /api/fingerprint-bridge/cancel
     */
    public function cancel()
    {
        $logId = $this->request->getPost('log_id');
        
        if (!$logId) {
            return $this->failValidationErrors(['log_id' => 'Log ID is required']);
        }
        
        $result = $this->importService->cancelImport($logId);
        
        if ($result['success']) {
            return $this->respond($result);
        } else {
            return $this->fail($result['message']);
        }
    }
}
