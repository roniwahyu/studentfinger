<?php

namespace App\Controllers;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Exceptions\DatabaseException;

class TableManagerController extends BaseController
{
    protected $db;
    protected $tables = [];

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        $data['title'] = 'Table Manager';
        $data['tables'] = $this->getTables();
        
        return view('table_manager/index', $data);
    }

    public function create()
    {
        $data['title'] = 'Create New Table';
        
        if ($this->request->getMethod() === 'post') {
            $tableName = $this->request->getPost('table_name');
            $fields = $this->request->getPost('fields');
            
            try {
                $this->createTable($tableName, $fields);
                return redirect()->to('/table-manager')->with('message', 'Table created successfully');
            } catch (\Exception $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        }

        return view('table_manager/create', $data);
    }

    public function edit($tableName)
    {
        $data['title'] = 'Edit Table: ' . $tableName;
        $data['table'] = $this->getTableStructure($tableName);
        
        if ($this->request->getMethod() === 'post') {
            $fields = $this->request->getPost('fields');
            
            try {
                $this->updateTable($tableName, $fields);
                return redirect()->to('/table-manager')->with('message', 'Table updated successfully');
            } catch (\Exception $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        }

        return view('table_manager/edit', $data);
    }

    public function delete($tableName)
    {
        try {
            $this->db->query("DROP TABLE IF EXISTS `$tableName`");
            return redirect()->to('/table-manager')->with('message', 'Table deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function generateCrudl()
    {
        if ($this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method']);
        }

        $tableName = $this->request->getPost('table_name');
        $moduleName = $this->request->getPost('module_name');

        try {
            // Execute the CRUDL generator command
            $command = "php spark make:crudl {$tableName} {$moduleName}";
            exec($command, $output, $returnVar);

            if ($returnVar === 0) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'CRUDL generated successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to generate CRUDL: ' . implode("\n", $output)
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    protected function getTables()
    {
        $tables = [];
        $result = $this->db->query("SHOW TABLES")->getResultArray();
        
        foreach ($result as $row) {
            $tableName = reset($row);
            $tables[] = [
                'name' => $tableName,
                'columns' => $this->getTableStructure($tableName)
            ];
        }
        
        return $tables;
    }

    protected function getTableStructure($tableName)
    {
        return $this->db->getFieldData($tableName);
    }

    protected function createTable($tableName, $fields)
    {
        $sql = "CREATE TABLE `$tableName` (";
        $sql .= "`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,";
        
        foreach ($fields as $field) {
            $sql .= "`{$field['name']}` {$field['type']}";
            if (isset($field['length'])) {
                $sql .= "({$field['length']})";
            }
            if (isset($field['null']) && $field['null'] === 'NO') {
                $sql .= " NOT NULL";
            }
            if (isset($field['default'])) {
                $sql .= " DEFAULT '{$field['default']}'";
            }
            $sql .= ",";
        }
        
        $sql .= "`created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,";
        $sql .= "`updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,";
        $sql .= "`deleted_at` DATETIME DEFAULT NULL";
        $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $this->db->query($sql);
    }

    protected function updateTable($tableName, $fields)
    {
        // Implementation for updating table structure
        // This is a complex operation that requires careful handling
        // You might want to use a migration approach instead
    }
} 