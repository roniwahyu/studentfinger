<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .requirement-item {
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
        }
        .requirement-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .requirement-error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .module-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .feature-badge {
            background-color: rgba(255,255,255,0.2);
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            margin: 3px;
            display: inline-block;
            font-size: 0.85em;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <!-- Module Information -->
        <div class="module-info">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fab fa-whatsapp"></i> <?= $module_info['name'] ?></h1>
                    <p class="lead mb-3"><?= $module_info['description'] ?></p>
                    <div class="mb-3">
                        <strong>Version:</strong> <?= $module_info['version'] ?> | 
                        <strong>Author:</strong> <?= $module_info['author'] ?>
                    </div>
                    <div>
                        <?php foreach ($module_info['features'] as $feature): ?>
                            <span class="feature-badge"><?= $feature ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <i class="fab fa-whatsapp fa-5x opacity-50"></i>
                </div>
            </div>
        </div>

        <!-- Installation Status -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-cogs"></i> System Requirements</h5>
                    </div>
                    <div class="card-body">
                        <?php 
                        $allPassed = true;
                        foreach ($requirements as $req): 
                            if (!$req['status']) $allPassed = false;
                        ?>
                            <div class="requirement-item <?= $req['status'] ? 'requirement-success' : 'requirement-error' ?>">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= $req['name'] ?></strong>
                                        <br>
                                        <small><?= $req['description'] ?></small>
                                    </div>
                                    <div class="text-end">
                                        <div>
                                            <?php if ($req['status']): ?>
                                                <i class="fas fa-check-circle text-success"></i>
                                            <?php else: ?>
                                                <i class="fas fa-times-circle text-danger"></i>
                                            <?php endif; ?>
                                        </div>
                                        <small>
                                            Required: <?= $req['required'] ?><br>
                                            Current: <?= $req['current'] ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-download"></i> Installation</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($is_installed): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                Module is already installed!
                            </div>
                            <button class="btn btn-warning btn-block mb-2" onclick="uninstallModule()">
                                <i class="fas fa-trash"></i> Uninstall Module
                            </button>
                        <?php else: ?>
                            <?php if ($allPassed): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    All requirements met. Ready to install!
                                </div>
                                <button class="btn btn-success btn-block mb-2" onclick="installModule()">
                                    <i class="fas fa-download"></i> Install Module
                                </button>
                            <?php else: ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Please fix the requirements above before installing.
                                </div>
                                <button class="btn btn-secondary btn-block mb-2" disabled>
                                    <i class="fas fa-download"></i> Install Module
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <button class="btn btn-outline-primary btn-block mb-2" onclick="runMigrations()">
                            <i class="fas fa-database"></i> Run Migrations
                        </button>
                        
                        <button class="btn btn-outline-info btn-block mb-2" onclick="seedDatabase()">
                            <i class="fas fa-seedling"></i> Seed Sample Data
                        </button>
                        
                        <button class="btn btn-outline-secondary btn-block" onclick="checkRequirements()">
                            <i class="fas fa-sync"></i> Refresh Requirements
                        </button>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6><i class="fas fa-link"></i> Quick Links</h6>
                    </div>
                    <div class="card-body">
                        <a href="<?= base_url('wablas/test/view') ?>" class="btn btn-sm btn-outline-primary btn-block mb-2">
                            <i class="fas fa-vial"></i> Test Module
                        </a>
                        <a href="<?= base_url('wablas') ?>" class="btn btn-sm btn-outline-success btn-block mb-2">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="<?= base_url('wablas/examples') ?>" class="btn btn-sm btn-outline-info btn-block">
                            <i class="fas fa-code"></i> Examples
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- API Coverage -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-api"></i> API Coverage</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($module_info['api_coverage'] as $api => $status): ?>
                                <div class="col-md-3 mb-2">
                                    <div class="text-center p-3 border rounded">
                                        <h6><?= $api ?></h6>
                                        <span class="badge bg-success"><?= $status ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Installation Log -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-terminal"></i> Installation Log</h5>
                    </div>
                    <div class="card-body">
                        <div id="installationLog" class="bg-dark text-light p-3 rounded" style="height: 200px; overflow-y: auto; font-family: monospace;">
                            <div class="text-muted">Ready for installation...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function logMessage(message, type = 'info') {
            const log = document.getElementById('installationLog');
            const timestamp = new Date().toLocaleTimeString();
            const colorClass = type === 'error' ? 'text-danger' : type === 'success' ? 'text-success' : 'text-info';
            log.innerHTML += `<div class="${colorClass}">[${timestamp}] ${message}</div>`;
            log.scrollTop = log.scrollHeight;
        }

        function installModule() {
            logMessage('Starting module installation...', 'info');
            
            fetch('<?= base_url('wablas/install/run') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    logMessage('Module installed successfully!', 'success');
                    logMessage(`Version: ${data.version}`, 'info');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    logMessage(`Installation failed: ${data.error}`, 'error');
                }
            })
            .catch(error => {
                logMessage(`Installation error: ${error.message}`, 'error');
            });
        }

        function uninstallModule() {
            if (!confirm('Are you sure you want to uninstall the Wablas Integration module?')) {
                return;
            }
            
            logMessage('Starting module uninstallation...', 'info');
            
            fetch('<?= base_url('wablas/install/uninstall') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    logMessage('Module uninstalled successfully!', 'success');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    logMessage(`Uninstallation failed: ${data.error}`, 'error');
                }
            })
            .catch(error => {
                logMessage(`Uninstallation error: ${error.message}`, 'error');
            });
        }

        function runMigrations() {
            logMessage('Running database migrations...', 'info');
            
            fetch('<?= base_url('wablas/install/migrate') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    logMessage('Migrations completed successfully!', 'success');
                } else {
                    logMessage(`Migration failed: ${data.error}`, 'error');
                }
            })
            .catch(error => {
                logMessage(`Migration error: ${error.message}`, 'error');
            });
        }

        function seedDatabase() {
            logMessage('Seeding sample data...', 'info');
            
            fetch('<?= base_url('wablas/install/seed') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    logMessage('Sample data seeded successfully!', 'success');
                } else {
                    logMessage(`Seeding failed: ${data.error}`, 'error');
                }
            })
            .catch(error => {
                logMessage(`Seeding error: ${error.message}`, 'error');
            });
        }

        function checkRequirements() {
            logMessage('Checking system requirements...', 'info');
            location.reload();
        }
    </script>
</body>
</html>
