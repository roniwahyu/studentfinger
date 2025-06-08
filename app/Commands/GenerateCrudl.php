<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class GenerateCrudl extends BaseCommand
{
    protected $group       = 'Generators';
    protected $name        = 'make:crudl';
    protected $description = 'Generates a complete CRUDL (Create, Read, Update, Delete, List) module for a table';
    protected $usage       = 'make:crudl [table_name] [module_name]';
    protected $arguments   = [
        'table_name' => 'The database table name',
        'module_name' => 'The module name (will be used for controller and model names)'
    ];

    public function run(array $params)
    {
        if (count($params) < 2) {
            CLI::error('Please provide both table name and module name');
            return;
        }

        $tableName = $params[0];
        $moduleName = $params[1];
        $moduleNameSingular = ucfirst($moduleName);
        $moduleNamePlural = $this->pluralize($moduleName);

        // Generate Model
        $this->generateModel($tableName, $moduleNameSingular);

        // Generate Controller
        $this->generateController($moduleNameSingular, $moduleNamePlural);

        // Generate Views
        $this->generateViews($moduleNameSingular, $moduleNamePlural, $tableName);

        CLI::write('CRUDL module generated successfully!', 'green');
    }

    protected function generateModel($tableName, $moduleNameSingular)
    {
        $modelPath = APPPATH . 'Models/' . $moduleNameSingular . 'Model.php';
        
        if (file_exists($modelPath)) {
            CLI::error("Model already exists: {$modelPath}");
            return;
        }

        $modelContent = <<<EOT
<?php

namespace App\Models;

use CodeIgniter\Model;

class {$moduleNameSingular}Model extends Model
{
    protected \$table = '{$tableName}';
    protected \$primaryKey = 'id';
    protected \$useAutoIncrement = true;
    protected \$returnType = 'array';
    protected \$useSoftDeletes = true;
    protected \$allowedFields = [];

    // Dates
    protected \$useTimestamps = true;
    protected \$dateFormat = 'datetime';
    protected \$createdField = 'created_at';
    protected \$updatedField = 'updated_at';
    protected \$deletedField = 'deleted_at';

    // Validation
    protected \$validationRules = [];
    protected \$validationMessages = [];
    protected \$skipValidation = false;
    protected \$cleanValidationRules = true;
}
EOT;

        file_put_contents($modelPath, $modelContent);
        CLI::write("Model created: {$modelPath}", 'green');
    }

    protected function generateController($moduleNameSingular, $moduleNamePlural)
    {
        $controllerPath = APPPATH . 'Controllers/' . $moduleNameSingular . 'Controller.php';
        
        if (file_exists($controllerPath)) {
            CLI::error("Controller already exists: {$controllerPath}");
            return;
        }

        $controllerContent = <<<EOT
<?php

namespace App\Controllers;

use App\Models\\{$moduleNameSingular}Model;

class {$moduleNameSingular}Controller extends BaseController
{
    protected \${$moduleNameSingular}Model;

    public function __construct()
    {
        \$this->{$moduleNameSingular}Model = new {$moduleNameSingular}Model();
    }

    public function index()
    {
        \$data['title'] = '{$moduleNamePlural}';
        \$data['{$moduleNamePlural}'] = \$this->{$moduleNameSingular}Model->findAll();
        
        return view('{$moduleNamePlural}/index', \$data);
    }

    public function create()
    {
        \$data['title'] = 'Create {$moduleNameSingular}';
        
        if (\$this->request->getMethod() === 'post') {
            \$rules = [
                // Add your validation rules here
            ];

            if (!\$this->validate(\$rules)) {
                \$data['validation'] = \$this->validator;
            } else {
                \$this->{$moduleNameSingular}Model->save(\$this->request->getPost());
                return redirect()->to('/{$moduleNamePlural}')->with('message', '{$moduleNameSingular} created successfully');
            }
        }

        return view('{$moduleNamePlural}/create', \$data);
    }

    public function edit(\$id = null)
    {
        \$data['title'] = 'Edit {$moduleNameSingular}';
        \$data['{$moduleNameSingular}'] = \$this->{$moduleNameSingular}Model->find(\$id);

        if (empty(\$data['{$moduleNameSingular}'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the {$moduleNameSingular} with id: ' . \$id);
        }

        if (\$this->request->getMethod() === 'post') {
            \$rules = [
                // Add your validation rules here
            ];

            if (!\$this->validate(\$rules)) {
                \$data['validation'] = \$this->validator;
            } else {
                \$this->{$moduleNameSingular}Model->update(\$id, \$this->request->getPost());
                return redirect()->to('/{$moduleNamePlural}')->with('message', '{$moduleNameSingular} updated successfully');
            }
        }

        return view('{$moduleNamePlural}/edit', \$data);
    }

    public function delete(\$id = null)
    {
        if (\$this->{$moduleNameSingular}Model->delete(\$id)) {
            return redirect()->to('/{$moduleNamePlural}')->with('message', '{$moduleNameSingular} deleted successfully');
        }
        
        return redirect()->to('/{$moduleNamePlural}')->with('error', 'Failed to delete {$moduleNameSingular}');
    }

    public function view(\$id = null)
    {
        \$data['title'] = 'View {$moduleNameSingular}';
        \$data['{$moduleNameSingular}'] = \$this->{$moduleNameSingular}Model->find(\$id);

        if (empty(\$data['{$moduleNameSingular}'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the {$moduleNameSingular} with id: ' . \$id);
        }

        return view('{$moduleNamePlural}/view', \$data);
    }
}
EOT;

        file_put_contents($controllerPath, $controllerContent);
        CLI::write("Controller created: {$controllerPath}", 'green');
    }

    protected function generateViews($moduleNameSingular, $moduleNamePlural, $tableName)
    {
        $viewPath = APPPATH . 'Views/' . $moduleNamePlural;
        
        if (!is_dir($viewPath)) {
            mkdir($viewPath, 0777, true);
        }

        // Generate index view
        $indexContent = <<<EOT
<?= \$this->extend('layouts/main') ?>

<?= \$this->section('title') ?>{$moduleNamePlural}<?= \$this->endSection() ?>

<?= \$this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">{$moduleNamePlural}</h4>
        <a href="<?= base_url('{$moduleNamePlural}/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Create New
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (\${$moduleNamePlural} as \$item): ?>
                        <tr>
                            <td><?= \$item['id'] ?></td>
                            <td>
                                <a href="<?= base_url('{$moduleNamePlural}/view/' . \$item['id']) ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= base_url('{$moduleNamePlural}/edit/' . \$item['id']) ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?= \$item['id'] ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= \$this->endSection() ?>

<?= \$this->section('scripts') ?>
<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `<?= base_url('{$moduleNamePlural}/delete/') ?>/\${id}`;
        }
    });
}
</script>
<?= \$this->endSection() ?>
EOT;

        // Generate create view
        $createContent = <<<EOT
<?= \$this->extend('layouts/main') ?>

<?= \$this->section('title') ?>Create {$moduleNameSingular}<?= \$this->endSection() ?>

<?= \$this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Create {$moduleNameSingular}</h4>
        <a href="<?= base_url('{$moduleNamePlural}') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (isset(\$validation)): ?>
                <div class="alert alert-danger">
                    <?= \$validation->listErrors() ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('{$moduleNamePlural}/create') ?>" method="post">
                <?= csrf_field() ?>
                
                <!-- Add your form fields here -->

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= \$this->endSection() ?>
EOT;

        // Generate edit view
        $editContent = <<<EOT
<?= \$this->extend('layouts/main') ?>

<?= \$this->section('title') ?>Edit {$moduleNameSingular}<?= \$this->endSection() ?>

<?= \$this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Edit {$moduleNameSingular}</h4>
        <a href="<?= base_url('{$moduleNamePlural}') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (isset(\$validation)): ?>
                <div class="alert alert-danger">
                    <?= \$validation->listErrors() ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('{$moduleNamePlural}/edit/' . \${$moduleNameSingular}['id']) ?>" method="post">
                <?= csrf_field() ?>
                
                <!-- Add your form fields here -->

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= \$this->endSection() ?>
EOT;

        // Generate view view
        $viewContent = <<<EOT
<?= \$this->extend('layouts/main') ?>

<?= \$this->section('title') ?>View {$moduleNameSingular}<?= \$this->endSection() ?>

<?= \$this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">View {$moduleNameSingular}</h4>
        <div>
            <a href="<?= base_url('{$moduleNamePlural}/edit/' . \${$moduleNameSingular}['id']) ?>" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
            <a href="<?= base_url('{$moduleNamePlural}') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to List
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Add your view fields here -->
        </div>
    </div>
</div>
<?= \$this->endSection() ?>
EOT;

        file_put_contents($viewPath . '/index.php', $indexContent);
        file_put_contents($viewPath . '/create.php', $createContent);
        file_put_contents($viewPath . '/edit.php', $editContent);
        file_put_contents($viewPath . '/view.php', $viewContent);

        CLI::write("Views created in: {$viewPath}", 'green');

        // Add routes to Routes.php
        $this->addRoutes($tableName, $moduleNameSingular, $moduleNamePlural);

        // Add to sidebar navigation
        $this->addToSidebar($tableName, $moduleNamePlural);
    }

    /**
     * Simple pluralize function
     */
    protected function pluralize($word)
    {
        $word = strtolower($word);

        // Simple pluralization rules
        if (substr($word, -1) === 'y') {
            return substr($word, 0, -1) . 'ies';
        } elseif (in_array(substr($word, -1), ['s', 'x', 'z']) || in_array(substr($word, -2), ['ch', 'sh'])) {
            return $word . 'es';
        } else {
            return $word . 's';
        }
    }

    /**
     * Add routes to Routes.php
     */
    protected function addRoutes($tableName, $moduleNameSingular, $moduleNamePlural)
    {
        $routesPath = APPPATH . 'Config/Routes.php';
        $routesContent = file_get_contents($routesPath);

        $routeGroup = strtolower(str_replace('_', '-', $moduleNamePlural));
        $controllerName = $moduleNameSingular . 'Controller';

        $routeCode = "\n// {$moduleNameSingular} Routes (Generated by CRUDL)\n";
        $routeCode .= "\$routes->group('{$routeGroup}', function(\$routes) {\n";
        $routeCode .= "    \$routes->get('/', '{$controllerName}::index');\n";
        $routeCode .= "    \$routes->get('create', '{$controllerName}::create');\n";
        $routeCode .= "    \$routes->post('create', '{$controllerName}::create');\n";
        $routeCode .= "    \$routes->get('view/(:segment)', '{$controllerName}::view/\$1');\n";
        $routeCode .= "    \$routes->get('edit/(:segment)', '{$controllerName}::edit/\$1');\n";
        $routeCode .= "    \$routes->post('edit/(:segment)', '{$controllerName}::edit/\$1');\n";
        $routeCode .= "    \$routes->get('delete/(:segment)', '{$controllerName}::delete/\$1');\n";
        $routeCode .= "});\n";

        // Check if routes already exist
        if (strpos($routesContent, "// {$moduleNameSingular} Routes") === false) {
            // Add before the closing of the file
            $routesContent = str_replace('<?php', "<?php{$routeCode}", $routesContent);
            file_put_contents($routesPath, $routesContent);
            CLI::write("Routes added to Routes.php", 'green');
        } else {
            CLI::write("Routes already exist in Routes.php", 'yellow');
        }
    }

    /**
     * Add to sidebar navigation
     */
    protected function addToSidebar($tableName, $moduleNamePlural)
    {
        $sidebarPath = APPPATH . 'Views/layouts/main.php';
        $sidebarContent = file_get_contents($sidebarPath);

        $routeGroup = strtolower(str_replace('_', '-', $moduleNamePlural));
        $displayName = ucwords(str_replace('_', ' ', $moduleNamePlural));
        $icon = $this->getIconForTable($tableName);

        $navItem = "                        <li class=\"nav-item\">\n";
        $navItem .= "                            <a class=\"nav-link\" href=\"<?= base_url('{$routeGroup}') ?>\">\n";
        $navItem .= "                                <i class=\"fas fa-{$icon} me-2\"></i> {$displayName}\n";
        $navItem .= "                            </a>\n";
        $navItem .= "                        </li>\n";

        // Check if nav item already exists
        if (strpos($sidebarContent, "href=\"<?= base_url('{$routeGroup}') ?>\"") === false) {
            // Add before the closing </ul> of the navigation
            $sidebarContent = str_replace('                    </ul>', $navItem . '                    </ul>', $sidebarContent);
            file_put_contents($sidebarPath, $sidebarContent);
            CLI::write("Navigation item added to sidebar", 'green');
        } else {
            CLI::write("Navigation item already exists in sidebar", 'yellow');
        }
    }

    /**
     * Get appropriate icon for table
     */
    protected function getIconForTable($tableName)
    {
        $icons = [
            'user' => 'users',
            'student' => 'user-graduate',
            'attendance' => 'calendar-check',
            'log' => 'list-alt',
            'report' => 'chart-bar',
            'setting' => 'cog',
            'class' => 'chalkboard',
            'subject' => 'book',
            'teacher' => 'chalkboard-teacher',
            'grade' => 'star',
            'exam' => 'clipboard-list'
        ];

        foreach ($icons as $keyword => $icon) {
            if (strpos(strtolower($tableName), $keyword) !== false) {
                return $icon;
            }
        }

        return 'table'; // Default icon
    }
}