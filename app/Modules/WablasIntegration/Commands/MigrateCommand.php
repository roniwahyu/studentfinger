<?php

namespace App\Modules\WablasIntegration\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Migration Command for Wablas Integration Module
 */
class MigrateCommand extends BaseCommand
{
    protected $group = 'Wablas';
    protected $name = 'wablas:migrate';
    protected $description = 'Run Wablas Integration module migrations';
    
    protected $usage = 'wablas:migrate [options]';
    protected $arguments = [];
    protected $options = [
        '--rollback' => 'Rollback migrations',
        '--refresh' => 'Rollback and re-run migrations',
        '--seed' => 'Run seeders after migration'
    ];
    
    public function run(array $params)
    {
        $rollback = CLI::getOption('rollback');
        $refresh = CLI::getOption('refresh');
        $seed = CLI::getOption('seed');
        
        CLI::write('Wablas Integration Module Migration', 'yellow');
        CLI::write('=====================================', 'yellow');
        
        try {
            $migrate = \Config\Services::migrations();
            $migrate->setNamespace('App\Modules\WablasIntegration');
            
            if ($rollback) {
                CLI::write('Rolling back migrations...', 'cyan');
                $migrate->regress(0);
                CLI::write('Migrations rolled back successfully!', 'green');
            } elseif ($refresh) {
                CLI::write('Refreshing migrations...', 'cyan');
                $migrate->regress(0);
                $migrate->latest();
                CLI::write('Migrations refreshed successfully!', 'green');
            } else {
                CLI::write('Running migrations...', 'cyan');
                $migrate->latest();
                CLI::write('Migrations completed successfully!', 'green');
            }
            
            if ($seed) {
                CLI::write('Running seeders...', 'cyan');
                $seeder = \Config\Database::seeder();
                $seeder->call('WablasIntegrationSeeder');
                CLI::write('Seeders completed successfully!', 'green');
            }
            
            // Show migration status
            $this->showMigrationStatus();
            
        } catch (\Exception $e) {
            CLI::error('Migration failed: ' . $e->getMessage());
            return EXIT_ERROR;
        }
        
        return EXIT_SUCCESS;
    }
    
    /**
     * Show migration status
     */
    protected function showMigrationStatus()
    {
        CLI::write('', 'white');
        CLI::write('Migration Status:', 'yellow');
        CLI::write('================', 'yellow');
        
        $db = \Config\Database::connect();
        $tables = [
            'wablas_devices' => 'Device Management',
            'wablas_messages' => 'Message History',
            'wablas_contacts' => 'Contact Management',
            'wablas_schedules' => 'Scheduled Messages',
            'wablas_auto_replies' => 'Auto Reply System',
            'wablas_webhooks' => 'Webhook Configuration',
            'wablas_logs' => 'Activity Logs',
            'wablas_templates' => 'Message Templates',
            'wablas_groups' => 'Contact Groups',
            'wablas_campaigns' => 'Messaging Campaigns'
        ];
        
        foreach ($tables as $table => $description) {
            $exists = $db->tableExists($table);
            $status = $exists ? CLI::color('✓ EXISTS', 'green') : CLI::color('✗ MISSING', 'red');
            CLI::write(sprintf('%-20s %-15s %s', $table, $status, $description));
        }
        
        CLI::write('', 'white');
    }
}
