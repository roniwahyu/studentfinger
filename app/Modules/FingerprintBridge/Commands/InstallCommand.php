<?php

namespace App\Modules\FingerprintBridge\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * FingerprintBridge Install Command
 * 
 * Handles installation and uninstallation of the FingerprintBridge module
 */
class InstallCommand extends BaseCommand
{
    protected $group = 'FingerprintBridge';
    protected $name = 'fingerprint:install';
    protected $description = 'Install or uninstall the FingerprintBridge module';
    
    protected $usage = 'fingerprint:install [options]';
    protected $arguments = [];
    protected $options = [
        '--uninstall' => 'Uninstall the module instead of installing',
        '--force' => 'Force installation/uninstallation without confirmation',
        '--with-data' => 'Include sample data during installation',
        '--clean' => 'Remove all data during uninstallation (use with caution)',
    ];
    
    public function run(array $params)
    {
        $isUninstall = CLI::getOption('uninstall');
        $force = CLI::getOption('force');
        $withData = CLI::getOption('with-data');
        $clean = CLI::getOption('clean');
        
        if ($isUninstall) {
            $this->uninstallModule($force, $clean);
        } else {
            $this->installModule($force, $withData);
        }
    }
    
    /**
     * Install the FingerprintBridge module
     */
    protected function installModule(bool $force = false, bool $withData = false)
    {
        CLI::write('FingerprintBridge Module Installation', 'green');
        CLI::write('=====================================', 'green');
        CLI::newLine();
        
        if (!$force) {
            $confirm = CLI::prompt('Do you want to install the FingerprintBridge module?', ['y', 'n']);
            if ($confirm !== 'y') {
                CLI::write('Installation cancelled.', 'yellow');
                return;
            }
        }
        
        try {
            // Step 1: Check prerequisites
            CLI::write('1. Checking prerequisites...', 'yellow');
            $this->checkPrerequisites();
            CLI::write('   ✓ Prerequisites check passed', 'green');
            
            // Step 2: Create database tables
            CLI::write('2. Creating database tables...', 'yellow');
            $this->createTables();
            CLI::write('   ✓ Database tables created', 'green');
            
            // Step 3: Insert default settings
            CLI::write('3. Installing default settings...', 'yellow');
            $this->insertDefaultSettings();
            CLI::write('   ✓ Default settings installed', 'green');
            
            // Step 4: Update .env file
            CLI::write('4. Updating .env configuration...', 'yellow');
            $this->updateEnvFile();
            CLI::write('   ✓ Environment configuration updated', 'green');
            
            // Step 5: Register module routes
            CLI::write('5. Registering module routes...', 'yellow');
            $this->registerRoutes();
            CLI::write('   ✓ Module routes registered', 'green');
            
            // Step 6: Create sample data (if requested)
            if ($withData) {
                CLI::write('6. Creating sample data...', 'yellow');
                $this->createSampleData();
                CLI::write('   ✓ Sample data created', 'green');
            }
            
            // Step 7: Test installation
            CLI::write('7. Testing installation...', 'yellow');
            $this->testInstallation();
            CLI::write('   ✓ Installation test passed', 'green');
            
            CLI::newLine();
            CLI::write('=====================================', 'green');
            CLI::write('Installation completed successfully!', 'green');
            CLI::write('=====================================', 'green');
            CLI::newLine();
            
            $this->showPostInstallInstructions();
            
        } catch (\Exception $e) {
            CLI::error('Installation failed: ' . $e->getMessage());
            CLI::write('Please check the error and try again.', 'red');
        }
    }
    
    /**
     * Uninstall the FingerprintBridge module
     */
    protected function uninstallModule(bool $force = false, bool $clean = false)
    {
        CLI::write('FingerprintBridge Module Uninstallation', 'red');
        CLI::write('=======================================', 'red');
        CLI::newLine();
        
        if (!$force) {
            CLI::write('WARNING: This will remove the FingerprintBridge module.', 'red');
            if ($clean) {
                CLI::write('WARNING: --clean flag will remove ALL data including import logs!', 'red');
            }
            CLI::newLine();
            
            $confirm = CLI::prompt('Are you sure you want to uninstall?', ['y', 'n']);
            if ($confirm !== 'y') {
                CLI::write('Uninstallation cancelled.', 'yellow');
                return;
            }
        }
        
        try {
            // Step 1: Remove routes
            CLI::write('1. Removing module routes...', 'yellow');
            $this->unregisterRoutes();
            CLI::write('   ✓ Module routes removed', 'green');
            
            // Step 2: Remove database tables (if clean)
            if ($clean) {
                CLI::write('2. Removing database tables...', 'yellow');
                $this->dropTables();
                CLI::write('   ✓ Database tables removed', 'green');
            } else {
                CLI::write('2. Keeping database tables (use --clean to remove)', 'yellow');
            }
            
            // Step 3: Remove .env entries
            CLI::write('3. Cleaning .env configuration...', 'yellow');
            $this->cleanEnvFile();
            CLI::write('   ✓ Environment configuration cleaned', 'green');
            
            CLI::newLine();
            CLI::write('=======================================', 'green');
            CLI::write('Uninstallation completed successfully!', 'green');
            CLI::write('=======================================', 'green');
            CLI::newLine();
            
            if (!$clean) {
                CLI::write('Note: Database tables and data were preserved.', 'yellow');
                CLI::write('Use --clean flag to remove all data.', 'yellow');
            }
            
        } catch (\Exception $e) {
            CLI::error('Uninstallation failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Check prerequisites
     */
    protected function checkPrerequisites()
    {
        // Check if MySQLi extension is loaded
        if (!extension_loaded('mysqli')) {
            throw new \Exception('MySQLi extension is required but not loaded');
        }
        
        // Check if database connection works
        $db = \Config\Database::connect();
        if (!$db->connID) {
            throw new \Exception('Cannot connect to default database');
        }
        
        // Check writable directories
        $writableDirs = [
            WRITEPATH . 'cache',
            WRITEPATH . 'logs',
            WRITEPATH . 'session'
        ];
        
        foreach ($writableDirs as $dir) {
            if (!is_writable($dir)) {
                throw new \Exception("Directory {$dir} is not writable");
            }
        }
    }
    
    /**
     * Create database tables
     */
    protected function createTables()
    {
        $db = \Config\Database::connect();
        
        // Create fingerprint_import_logs table
        $db->query("
            CREATE TABLE IF NOT EXISTS `fingerprint_import_logs` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `import_type` ENUM('manual', 'auto', 'scheduled') NOT NULL DEFAULT 'manual',
                `start_date` DATETIME NULL,
                `end_date` DATETIME NULL,
                `status` ENUM('pending', 'running', 'completed', 'failed', 'cancelled') NOT NULL DEFAULT 'pending',
                `total_records` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `processed_records` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `inserted_records` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `updated_records` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `skipped_records` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `error_records` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `start_time` DATETIME NULL,
                `end_time` DATETIME NULL,
                `duration` INT(11) UNSIGNED NULL,
                `error_message` TEXT NULL,
                `settings` JSON NULL,
                `user_id` INT(11) UNSIGNED NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                KEY `idx_status_created` (`status`, `created_at`),
                KEY `idx_import_type` (`import_type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
        
        // Create student_pin_mapping table
        $db->query("
            CREATE TABLE IF NOT EXISTS `student_pin_mapping` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `pin` VARCHAR(32) NOT NULL,
                `student_id` INT(11) UNSIGNED NOT NULL,
                `rfid_card` VARCHAR(50) NULL,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `notes` VARCHAR(255) NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                `deleted_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_pin` (`pin`),
                UNIQUE KEY `uk_student_id` (`student_id`),
                KEY `idx_is_active` (`is_active`),
                KEY `idx_deleted_at` (`deleted_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
        
        // Create fingerprint_import_settings table
        $db->query("
            CREATE TABLE IF NOT EXISTS `fingerprint_import_settings` (
                `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `setting_key` VARCHAR(100) NOT NULL,
                `setting_value` TEXT NULL,
                `setting_type` ENUM('string', 'integer', 'boolean', 'json') NOT NULL DEFAULT 'string',
                `description` VARCHAR(255) NULL,
                `is_system` TINYINT(1) NOT NULL DEFAULT 0,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_setting_key` (`setting_key`),
                KEY `idx_is_system` (`is_system`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
    }
    
    /**
     * Insert default settings
     */
    protected function insertDefaultSettings()
    {
        $db = \Config\Database::connect();
        
        $settings = [
            ['auto_import_enabled', '0', 'boolean', 'Enable automatic import from fingerprint machine', 1],
            ['auto_import_interval', '300', 'integer', 'Auto import interval in seconds', 1],
            ['import_batch_size', '1000', 'integer', 'Number of records to process in each batch', 1],
            ['duplicate_handling', 'skip', 'string', 'How to handle duplicate records', 1],
            ['default_status', '1', 'integer', 'Default status for imported attendance records', 1],
            ['log_retention_days', '30', 'integer', 'Number of days to keep import logs', 1],
            ['verify_student_exists', '1', 'boolean', 'Verify that student exists before importing', 1],
            ['create_missing_students', '0', 'boolean', 'Automatically create missing students', 1]
        ];
        
        foreach ($settings as $setting) {
            $db->query("
                INSERT IGNORE INTO `fingerprint_import_settings` 
                (`setting_key`, `setting_value`, `setting_type`, `description`, `is_system`, `created_at`, `updated_at`) 
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            ", $setting);
        }
    }
    
    /**
     * Update .env file with FinPro database configuration
     */
    protected function updateEnvFile()
    {
        $envPath = ROOTPATH . '.env';
        $envContent = file_get_contents($envPath);

        // Check if FinPro configuration already exists
        if (strpos($envContent, 'FINPRO_DB_HOST') === false) {
            $finproConfig = "\n#--------------------------------------------------------------------\n";
            $finproConfig .= "# FINGERPRINT BRIDGE (FIN_PRO DATABASE) CONFIGURATION\n";
            $finproConfig .= "#--------------------------------------------------------------------\n\n";
            $finproConfig .= "# FinPro Database Configuration for Fingerspot Machine Data\n";
            $finproConfig .= "FINPRO_DB_HOST=localhost\n";
            $finproConfig .= "FINPRO_DB_USERNAME=root\n";
            $finproConfig .= "FINPRO_DB_PASSWORD=\n";
            $finproConfig .= "FINPRO_DB_DATABASE=fin_pro\n";
            $finproConfig .= "FINPRO_DB_PORT=3306\n";
            $finproConfig .= "FINPRO_DB_CHARSET=latin1\n";
            $finproConfig .= "FINPRO_DB_COLLATION=latin1_swedish_ci\n";

            file_put_contents($envPath, $envContent . $finproConfig);
        }
    }

    /**
     * Register module routes
     */
    protected function registerRoutes()
    {
        $routesPath = APPPATH . 'Config/Routes.php';
        $routesContent = file_get_contents($routesPath);

        $moduleRouteCode = "\n// Load FingerprintBridge module routes\n";
        $moduleRouteCode .= "if (file_exists(APPPATH . 'Modules/FingerprintBridge/Config/Routes.php')) {\n";
        $moduleRouteCode .= "    require_once APPPATH . 'Modules/FingerprintBridge/Config/Routes.php';\n";
        $moduleRouteCode .= "}\n";

        if (strpos($routesContent, 'FingerprintBridge/Config/Routes.php') === false) {
            file_put_contents($routesPath, $routesContent . $moduleRouteCode);
        }
    }

    /**
     * Create sample data
     */
    protected function createSampleData()
    {
        // Create fin_pro database and sample data
        $this->createFinProSampleData();

        // Create sample students
        $this->createSampleStudents();

        // Create sample PIN mappings
        $this->createSamplePinMappings();
    }

    /**
     * Test installation
     */
    protected function testInstallation()
    {
        // Test database connections
        $db = \Config\Database::connect();
        $db->query('SELECT 1');

        // Test if tables exist
        $tables = ['fingerprint_import_logs', 'student_pin_mapping', 'fingerprint_import_settings'];
        foreach ($tables as $table) {
            $result = $db->query("SHOW TABLES LIKE '{$table}'");
            if ($result->getNumRows() === 0) {
                throw new \Exception("Table {$table} was not created");
            }
        }

        // Test FinPro connection (if configured)
        try {
            $finProDb = \Config\Database::connect('fin_pro');
            $finProDb->query('SELECT 1');
        } catch (\Exception $e) {
            CLI::write('   Note: FinPro database connection not configured yet', 'yellow');
        }
    }

    /**
     * Drop database tables
     */
    protected function dropTables()
    {
        $db = \Config\Database::connect();

        $tables = ['fingerprint_import_settings', 'student_pin_mapping', 'fingerprint_import_logs'];
        foreach ($tables as $table) {
            $db->query("DROP TABLE IF EXISTS `{$table}`");
        }
    }

    /**
     * Unregister module routes
     */
    protected function unregisterRoutes()
    {
        $routesPath = APPPATH . 'Config/Routes.php';
        $routesContent = file_get_contents($routesPath);

        // Remove FingerprintBridge route registration
        $routesContent = preg_replace(
            '/\/\/ Load FingerprintBridge module routes.*?}\n/s',
            '',
            $routesContent
        );

        file_put_contents($routesPath, $routesContent);
    }

    /**
     * Clean .env file
     */
    protected function cleanEnvFile()
    {
        $envPath = ROOTPATH . '.env';
        $envContent = file_get_contents($envPath);

        // Remove FinPro configuration section
        $envContent = preg_replace(
            '/#--------------------------------------------------------------------\n# FINGERPRINT BRIDGE.*?FINPRO_DB_COLLATION=.*?\n/s',
            '',
            $envContent
        );

        file_put_contents($envPath, $envContent);
    }

    /**
     * Create FinPro sample data
     */
    protected function createFinProSampleData()
    {
        try {
            $db = \Config\Database::connect();
            $db->query('CREATE DATABASE IF NOT EXISTS fin_pro');

            $finProDb = \Config\Database::connect('fin_pro');

            // Create att_log table
            $finProDb->query("
                CREATE TABLE IF NOT EXISTS `att_log` (
                    `sn` VARCHAR(30) NOT NULL,
                    `scan_date` DATETIME NOT NULL,
                    `pin` VARCHAR(32) NOT NULL,
                    `verifymode` INT(11) NOT NULL,
                    `inoutmode` INT(11) NOT NULL DEFAULT 0,
                    `reserved` INT(11) NOT NULL DEFAULT 0,
                    `work_code` INT(11) NOT NULL DEFAULT 0,
                    `att_id` VARCHAR(50) NOT NULL DEFAULT '0',
                    PRIMARY KEY (`sn`, `scan_date`, `pin`),
                    KEY `pin` (`pin`),
                    KEY `sn` (`sn`)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
            ");

            // Insert sample data
            $sampleData = [
                ['FIO66205020150662', '2025-01-09 07:15:00', '1001', 1, 1, 0, 0, '0'],
                ['FIO66205020150662', '2025-01-09 07:16:00', '1002', 20, 1, 0, 0, '0'],
                ['FIO66205020150662', '2025-01-09 07:17:00', '1003', 3, 1, 0, 0, '0'],
                ['FIO66205020150662', '2025-01-09 17:00:00', '1001', 1, 2, 0, 0, '0'],
                ['FIO66205020150662', '2025-01-09 17:01:00', '1002', 20, 2, 0, 0, '0'],
                ['66208023321907', '2025-01-09 08:00:00', '2001', 20, 1, 0, 0, '0'],
                ['66208023321907', '2025-01-09 16:30:00', '2001', 20, 2, 0, 0, '0']
            ];

            foreach ($sampleData as $data) {
                $finProDb->query("
                    INSERT IGNORE INTO `att_log`
                    (`sn`, `scan_date`, `pin`, `verifymode`, `inoutmode`, `reserved`, `work_code`, `att_id`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ", $data);
            }
        } catch (\Exception $e) {
            CLI::write('   Warning: Could not create FinPro sample data: ' . $e->getMessage(), 'yellow');
        }
    }

    /**
     * Create sample students
     */
    protected function createSampleStudents()
    {
        $db = \Config\Database::connect();

        // Check if students table exists
        $result = $db->query("SHOW TABLES LIKE 'students'");
        if ($result->getNumRows() === 0) {
            CLI::write('   Warning: Students table does not exist, skipping sample students', 'yellow');
            return;
        }

        $sampleStudents = [
            [1, 'STD001', 'John', 'Doe', '081234567890', 'john.doe@email.com', '1001'],
            [2, 'STD002', 'Jane', 'Smith', '081234567892', 'jane.smith@email.com', '1002'],
            [3, 'STD003', 'Bob', 'Johnson', '081234567894', 'bob.johnson@email.com', '1003'],
            [4, 'STD004', 'Alice', 'Brown', '081234567896', 'alice.brown@email.com', '2001'],
            [5, 'STD005', 'Charlie', 'Wilson', '081234567898', 'charlie.wilson@email.com', '2002']
        ];

        foreach ($sampleStudents as $student) {
            $db->query("
                INSERT IGNORE INTO `students`
                (`student_id`, `admission_no`, `firstname`, `lastname`, `mobileno`, `email`, `rfid`, `status`, `created_at`, `updated_at`)
                VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())
            ", $student);
        }
    }

    /**
     * Create sample PIN mappings
     */
    protected function createSamplePinMappings()
    {
        $db = \Config\Database::connect();

        $sampleMappings = [
            ['1001', 1, '1001', 1, 'Sample mapping for John Doe'],
            ['1002', 2, '1002', 1, 'Sample mapping for Jane Smith'],
            ['1003', 3, '1003', 1, 'Sample mapping for Bob Johnson'],
            ['2001', 4, '2001', 1, 'Sample mapping for Alice Brown'],
            ['2002', 5, '2002', 1, 'Sample mapping for Charlie Wilson']
        ];

        foreach ($sampleMappings as $mapping) {
            $db->query("
                INSERT IGNORE INTO `student_pin_mapping`
                (`pin`, `student_id`, `rfid_card`, `is_active`, `notes`, `created_at`, `updated_at`)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            ", $mapping);
        }
    }

    /**
     * Show post-installation instructions
     */
    protected function showPostInstallInstructions()
    {
        CLI::write('Next Steps:', 'yellow');
        CLI::write('1. Configure FinPro database settings in .env file', 'white');
        CLI::write('2. Access the module at: http://your-domain/fingerprint-bridge', 'white');
        CLI::write('3. Test database connection in the module settings', 'white');
        CLI::write('4. Configure PIN mappings for your students', 'white');
        CLI::write('5. Start importing fingerprint data', 'white');
        CLI::newLine();

        CLI::write('Available Commands:', 'yellow');
        CLI::write('- php spark fingerprint:import --help', 'white');
        CLI::write('- php spark fingerprint:install --uninstall', 'white');
        CLI::newLine();
    }
}
