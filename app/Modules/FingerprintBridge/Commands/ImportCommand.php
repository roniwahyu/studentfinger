<?php

namespace App\Modules\FingerprintBridge\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Modules\FingerprintBridge\Services\FingerprintImportService;

/**
 * Import Command
 * 
 * CLI command for running fingerprint imports
 */
class ImportCommand extends BaseCommand
{
    protected $group = 'FingerprintBridge';
    protected $name = 'fingerprint:import';
    protected $description = 'Import attendance data from FinPro fingerprint machine database';
    
    protected $usage = 'fingerprint:import [options]';
    protected $arguments = [];
    protected $options = [
        '--start-date' => 'Start date for import (YYYY-MM-DD)',
        '--end-date' => 'End date for import (YYYY-MM-DD)',
        '--batch-size' => 'Number of records to process in each batch (default: 1000)',
        '--duplicate-handling' => 'How to handle duplicates: skip, update, error (default: skip)',
        '--auto' => 'Run automatic import (imports data from last successful import)',
        '--test' => 'Test mode - preview import without actually importing',
    ];
    
    protected $importService;
    
    public function __construct()
    {
        parent::__construct();
        $this->importService = new FingerprintImportService();
    }
    
    public function run(array $params)
    {
        CLI::write('FingerprintBridge Import Tool', 'green');
        CLI::write('================================', 'green');
        CLI::newLine();
        
        // Test database connection first
        CLI::write('Testing database connection...', 'yellow');
        $connectionTest = $this->importService->testFinProConnection();
        
        if (!$connectionTest['success']) {
            CLI::error('Database connection failed: ' . $connectionTest['message']);
            return;
        }
        
        CLI::write('✓ Database connection successful', 'green');
        CLI::write('  Total records in FinPro: ' . number_format($connectionTest['data']['total_records'] ?? 0));
        CLI::write('  Unique PINs: ' . number_format($connectionTest['data']['unique_pins'] ?? 0));
        CLI::write('  Unique devices: ' . number_format($connectionTest['data']['unique_devices'] ?? 0));
        CLI::newLine();
        
        // Determine import mode
        $isAuto = CLI::getOption('auto');
        $isTest = CLI::getOption('test');
        
        if ($isAuto) {
            $this->runAutoImport($isTest);
        } else {
            $this->runManualImport($isTest);
        }
    }
    
    /**
     * Run automatic import
     */
    protected function runAutoImport(bool $testMode = false)
    {
        CLI::write('Running automatic import...', 'yellow');
        
        // Get last successful import date
        $importLogModel = new \App\Modules\FingerprintBridge\Models\ImportLogModel();
        $lastImport = $importLogModel->getLatestImport('auto');
        
        if ($lastImport && $lastImport['status'] === 'completed' && $lastImport['end_date']) {
            $startDate = $lastImport['end_date'];
        } else {
            // Default to yesterday if no previous import
            $startDate = date('Y-m-d 00:00:00', strtotime('-1 day'));
        }
        
        $endDate = date('Y-m-d 23:59:59');
        
        CLI::write("Import date range: {$startDate} to {$endDate}");
        
        $this->executeImport($startDate, $endDate, 'auto', $testMode);
    }
    
    /**
     * Run manual import
     */
    protected function runManualImport(bool $testMode = false)
    {
        $startDate = CLI::getOption('start-date');
        $endDate = CLI::getOption('end-date');
        
        // Validate dates
        if (!$startDate || !$endDate) {
            CLI::error('Start date and end date are required for manual import');
            CLI::write('Usage: php spark fingerprint:import --start-date=2025-01-01 --end-date=2025-01-31');
            return;
        }
        
        if (!$this->validateDate($startDate) || !$this->validateDate($endDate)) {
            CLI::error('Invalid date format. Use YYYY-MM-DD format');
            return;
        }
        
        if (strtotime($startDate) > strtotime($endDate)) {
            CLI::error('Start date cannot be later than end date');
            return;
        }
        
        $startDate .= ' 00:00:00';
        $endDate .= ' 23:59:59';
        
        CLI::write("Import date range: {$startDate} to {$endDate}");
        
        $this->executeImport($startDate, $endDate, 'manual', $testMode);
    }
    
    /**
     * Execute the import
     */
    protected function executeImport(string $startDate, string $endDate, string $importType, bool $testMode = false)
    {
        $batchSize = CLI::getOption('batch-size') ?: 1000;
        $duplicateHandling = CLI::getOption('duplicate-handling') ?: 'skip';
        
        // Validate options
        if (!in_array($duplicateHandling, ['skip', 'update', 'error'])) {
            CLI::error('Invalid duplicate handling option. Use: skip, update, or error');
            return;
        }
        
        if ($batchSize < 1 || $batchSize > 10000) {
            CLI::error('Batch size must be between 1 and 10000');
            return;
        }
        
        CLI::newLine();
        CLI::write('Import Configuration:', 'yellow');
        CLI::write("  Type: {$importType}");
        CLI::write("  Batch size: {$batchSize}");
        CLI::write("  Duplicate handling: {$duplicateHandling}");
        CLI::write("  Test mode: " . ($testMode ? 'Yes' : 'No'));
        CLI::newLine();
        
        if ($testMode) {
            // Preview mode
            CLI::write('Running in test mode - previewing import...', 'yellow');
            $result = $this->importService->previewImport($startDate, $endDate, 10);
            
            if ($result['success']) {
                $data = $result['data'];
                CLI::write('✓ Preview completed', 'green');
                CLI::write("  Total records to import: " . number_format($data['total_count']));
                CLI::write("  New records: " . number_format($data['new_count']));
                CLI::write("  Existing records: " . number_format($data['existing_count']));
                
                if (!empty($data['records'])) {
                    CLI::newLine();
                    CLI::write('Sample records:', 'yellow');
                    foreach (array_slice($data['records'], 0, 5) as $record) {
                        CLI::write("  PIN: {$record['pin']}, Date: {$record['scan_date']}, Device: {$record['sn']}");
                    }
                }
            } else {
                CLI::error('Preview failed: ' . $result['message']);
            }
            return;
        }
        
        // Confirm before proceeding
        if (!CLI::prompt('Do you want to proceed with the import?', ['y', 'n']) === 'y') {
            CLI::write('Import cancelled', 'yellow');
            return;
        }
        
        // Start import
        CLI::write('Starting import...', 'yellow');
        
        $options = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'batch_size' => $batchSize,
            'duplicate_handling' => $duplicateHandling,
            'import_type' => $importType,
            'user_id' => null
        ];
        
        $result = $this->importService->importData($options);
        
        CLI::newLine();
        if ($result['success']) {
            CLI::write('✓ Import completed successfully!', 'green');
            CLI::write('Import Results:', 'yellow');
            CLI::write("  Total records: " . number_format($result['data']['total_records']));
            CLI::write("  Processed: " . number_format($result['data']['processed_records']));
            CLI::write("  Inserted: " . number_format($result['data']['inserted_records']));
            CLI::write("  Updated: " . number_format($result['data']['updated_records']));
            CLI::write("  Skipped: " . number_format($result['data']['skipped_records']));
            CLI::write("  Errors: " . number_format($result['data']['error_records']));
            CLI::write("  Mapped students: " . number_format($result['data']['mapped_students']));
            CLI::write("  Log ID: " . $result['log_id']);
        } else {
            CLI::error('Import failed: ' . $result['message']);
            if (isset($result['log_id'])) {
                CLI::write("Log ID: " . $result['log_id']);
            }
        }
    }
    
    /**
     * Validate date format
     */
    protected function validateDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
