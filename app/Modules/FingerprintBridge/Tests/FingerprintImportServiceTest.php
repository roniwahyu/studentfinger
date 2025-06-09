<?php

namespace App\Modules\FingerprintBridge\Tests;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Modules\FingerprintBridge\Services\FingerprintImportService;

/**
 * Test class for FingerprintImportService
 */
class FingerprintImportServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    
    protected $importService;
    protected $migrate = true;
    protected $migrateOnce = false;
    protected $refresh = true;
    protected $namespace = 'App\Modules\FingerprintBridge';
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->importService = new FingerprintImportService();
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
    }
    
    /**
     * Test service instantiation
     */
    public function testServiceInstantiation()
    {
        $this->assertInstanceOf(FingerprintImportService::class, $this->importService);
    }
    
    /**
     * Test database connection test method
     */
    public function testFinProConnectionTest()
    {
        $result = $this->importService->testFinProConnection();
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertIsBool($result['success']);
        $this->assertIsString($result['message']);
    }
    
    /**
     * Test import statistics method
     */
    public function testGetImportStats()
    {
        $stats = $this->importService->getImportStats();
        
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('fin_pro', $stats);
        $this->assertArrayHasKey('student_finger', $stats);
        $this->assertArrayHasKey('import_logs', $stats);
        $this->assertArrayHasKey('pin_mapping', $stats);
    }
    
    /**
     * Test auto-create PIN mappings method
     */
    public function testAutoCreatePinMappings()
    {
        $result = $this->importService->autoCreatePinMappings();
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertIsBool($result['success']);
        $this->assertIsString($result['message']);
    }
    
    /**
     * Test cancel import method
     */
    public function testCancelImport()
    {
        // Create a dummy import log first
        $importLogModel = new \App\Modules\FingerprintBridge\Models\ImportLogModel();
        $logId = $importLogModel->createImportLog([
            'import_type' => 'manual',
            'status' => 'running'
        ]);
        
        $result = $this->importService->cancelImport($logId);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertIsBool($result['success']);
        $this->assertIsString($result['message']);
    }
    
    /**
     * Test preview import with invalid dates
     */
    public function testPreviewImportWithInvalidDates()
    {
        $result = $this->importService->previewImport('invalid-date', 'invalid-date');
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('message', $result);
    }
    
    /**
     * Test preview import with valid dates
     */
    public function testPreviewImportWithValidDates()
    {
        $startDate = date('Y-m-d H:i:s', strtotime('-7 days'));
        $endDate = date('Y-m-d H:i:s');
        
        $result = $this->importService->previewImport($startDate, $endDate, 5);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertIsBool($result['success']);
        
        if ($result['success']) {
            $this->assertArrayHasKey('data', $result);
            $this->assertIsArray($result['data']);
            $this->assertArrayHasKey('records', $result['data']);
            $this->assertArrayHasKey('total_count', $result['data']);
        }
    }
}
