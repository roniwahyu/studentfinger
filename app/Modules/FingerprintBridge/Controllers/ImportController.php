<?php

namespace App\Modules\FingerprintBridge\Controllers;

use App\Controllers\BaseController;
use App\Modules\FingerprintBridge\Services\FingerprintImportService;
use App\Modules\FingerprintBridge\Models\ImportLogModel;
use App\Modules\FingerprintBridge\Models\StudentPinMappingModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Import Controller
 * 
 * Handles fingerprint import operations and UI
 */
class ImportController extends BaseController
{
    protected $importService;
    protected $importLogModel;
    protected $pinMappingModel;
    
    public function __construct()
    {
        $this->importService = new FingerprintImportService();
        $this->importLogModel = new ImportLogModel();
        $this->pinMappingModel = new StudentPinMappingModel();
    }
    
    /**
     * Dashboard - Main import interface
     */
    public function index()
    {
        $data = [
            'title' => 'Fingerprint Import Dashboard',
            'stats' => $this->importService->getImportStats(),
            'recent_logs' => $this->importLogModel->getImportLogs(5),
            'running_imports' => $this->importLogModel->getRunningImports()
        ];
        
        return view('App\Modules\FingerprintBridge\Views\dashboard', $data);
    }
    
    /**
     * Manual import interface
     */
    public function manualImport()
    {
        $data = [
            'title' => 'Manual Import',
            'connection_test' => $this->importService->testFinProConnection()
        ];
        
        return view('App\Modules\FingerprintBridge\Views\manual_import', $data);
    }
    
    /**
     * Process manual import
     */
    public function processManualImport()
    {
        $validation = \Config\Services::validation();
        
        $rules = [
            'start_date' => 'required|valid_date',
            'end_date' => 'required|valid_date',
            'duplicate_handling' => 'required|in_list[skip,update,error]',
            'batch_size' => 'permit_empty|integer|greater_than[0]'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
        
        $options = [
            'start_date' => $this->request->getPost('start_date') . ' 00:00:00',
            'end_date' => $this->request->getPost('end_date') . ' 23:59:59',
            'duplicate_handling' => $this->request->getPost('duplicate_handling'),
            'batch_size' => $this->request->getPost('batch_size') ?: 1000,
            'import_type' => 'manual',
            'user_id' => session('user_id') ?? null
        ];
        
        $result = $this->importService->importData($options);
        
        if ($result['success']) {
            return redirect()->to('fingerprint-bridge/logs/' . $result['log_id'])
                           ->with('success', $result['message']);
        } else {
            return redirect()->back()->withInput()
                           ->with('error', $result['message']);
        }
    }
    
    /**
     * Import logs listing
     */
    public function logs()
    {
        $page = $this->request->getGet('page') ?? 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $filters = [
            'import_type' => $this->request->getGet('import_type'),
            'status' => $this->request->getGet('status'),
            'start_date' => $this->request->getGet('start_date'),
            'end_date' => $this->request->getGet('end_date')
        ];
        
        $data = [
            'title' => 'Import Logs',
            'logs' => $this->importLogModel->getImportLogs($limit, $offset, $filters),
            'filters' => $filters,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit
            ]
        ];
        
        return view('App\Modules\FingerprintBridge\Views\logs', $data);
    }
    
    /**
     * Import log detail
     */
    public function logDetail($id)
    {
        $log = $this->importLogModel->find($id);
        
        if (!$log) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Import log not found');
        }
        
        $data = [
            'title' => 'Import Log Detail',
            'log' => $log
        ];
        
        return view('App\Modules\FingerprintBridge\Views\log_detail', $data);
    }
    
    /**
     * Settings page
     */
    public function settings()
    {
        $data = [
            'title' => 'Import Settings',
            'connection_test' => $this->importService->testFinProConnection()
        ];
        
        return view('App\Modules\FingerprintBridge\Views\settings', $data);
    }
    
    /**
     * PIN mapping management
     */
    public function pinMapping()
    {
        $page = $this->request->getGet('page') ?? 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $data = [
            'title' => 'PIN Mapping',
            'mappings' => $this->pinMappingModel->getMappingsWithStudentInfo($limit, $offset),
            'unmapped_pins' => $this->pinMappingModel->getUnmappedPins(10),
            'students_without_pin' => $this->pinMappingModel->getStudentsWithoutPin(10),
            'stats' => $this->pinMappingModel->getMappingStats(),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit
            ]
        ];
        
        return view('App\Modules\FingerprintBridge\Views\pin_mapping', $data);
    }
    
    /**
     * AJAX: Test connection
     */
    public function testConnection()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }
        
        $result = $this->importService->testFinProConnection();
        return $this->response->setJSON($result);
    }
    
    /**
     * AJAX: Preview import
     */
    public function previewImport()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }
        
        $startDate = $this->request->getPost('start_date');
        $endDate = $this->request->getPost('end_date');
        
        if (!$startDate || !$endDate) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Start date and end date are required']);
        }
        
        $result = $this->importService->previewImport($startDate . ' 00:00:00', $endDate . ' 23:59:59');
        return $this->response->setJSON($result);
    }
    
    /**
     * AJAX: Import status
     */
    public function importStatus()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }
        
        $logId = $this->request->getGet('log_id');
        
        if (!$logId) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Log ID is required']);
        }
        
        $log = $this->importLogModel->find($logId);
        
        if (!$log) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Import log not found']);
        }
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $log
        ]);
    }
    
    /**
     * AJAX: Stop import
     */
    public function stopImport()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }
        
        $logId = $this->request->getPost('log_id');
        
        if (!$logId) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Log ID is required']);
        }
        
        $result = $this->importService->cancelImport($logId);
        return $this->response->setJSON($result);
    }
    
    /**
     * Auto-create PIN mappings
     */
    public function autoCreateMappings()
    {
        $result = $this->importService->autoCreatePinMappings();
        
        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }
    
    /**
     * Save PIN mapping
     */
    public function savePinMapping()
    {
        $validation = \Config\Services::validation();
        
        $rules = [
            'pin' => 'required|max_length[32]',
            'student_id' => 'required|integer',
            'notes' => 'permit_empty|max_length[255]'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
        
        $data = [
            'pin' => $this->request->getPost('pin'),
            'student_id' => $this->request->getPost('student_id'),
            'is_active' => 1,
            'notes' => $this->request->getPost('notes')
        ];
        
        if ($this->pinMappingModel->insert($data)) {
            return redirect()->back()->with('success', 'PIN mapping created successfully');
        } else {
            return redirect()->back()->withInput()
                           ->with('errors', $this->pinMappingModel->errors());
        }
    }
    
    /**
     * Delete PIN mapping
     */
    public function deletePinMapping($id)
    {
        if ($this->pinMappingModel->delete($id)) {
            return redirect()->back()->with('success', 'PIN mapping deleted successfully');
        } else {
            return redirect()->back()->with('error', 'Failed to delete PIN mapping');
        }
    }
}
