<?php

/**
 * Web-based test for FingerprintBridge module
 */

// Simple test without CodeIgniter bootstrap
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FingerprintBridge Module Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4><i class="fas fa-fingerprint"></i> FingerprintBridge Module Test</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle"></i> Module Status</h5>
                            <p>Testing the FingerprintBridge module installation and functionality.</p>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h6>Database Tests</h6>
                                <div class="list-group mb-3">
                                    <?php
                                    // Test database connections
                                    $tests = [];
                                    
                                    try {
                                        $pdo = new PDO('mysql:host=localhost;dbname=studentfinger', 'root', '');
                                        $tests[] = ['name' => 'StudentFinger DB', 'status' => 'success', 'message' => 'Connected'];
                                    } catch (Exception $e) {
                                        $tests[] = ['name' => 'StudentFinger DB', 'status' => 'danger', 'message' => 'Failed: ' . $e->getMessage()];
                                    }
                                    
                                    try {
                                        $pdo = new PDO('mysql:host=localhost;dbname=fin_pro', 'root', '');
                                        $tests[] = ['name' => 'FinPro DB', 'status' => 'success', 'message' => 'Connected'];
                                    } catch (Exception $e) {
                                        $tests[] = ['name' => 'FinPro DB', 'status' => 'warning', 'message' => 'Not found (will be created)'];
                                    }
                                    
                                    // Check if FingerprintBridge tables exist
                                    try {
                                        $pdo = new PDO('mysql:host=localhost;dbname=studentfinger', 'root', '');
                                        $stmt = $pdo->query("SHOW TABLES LIKE 'fingerprint%'");
                                        $tables = $stmt->fetchAll();
                                        if (count($tables) >= 3) {
                                            $tests[] = ['name' => 'FingerprintBridge Tables', 'status' => 'success', 'message' => count($tables) . ' tables found'];
                                        } else {
                                            $tests[] = ['name' => 'FingerprintBridge Tables', 'status' => 'warning', 'message' => 'Need to be created'];
                                        }
                                    } catch (Exception $e) {
                                        $tests[] = ['name' => 'FingerprintBridge Tables', 'status' => 'danger', 'message' => 'Error: ' . $e->getMessage()];
                                    }
                                    
                                    foreach ($tests as $test) {
                                        echo '<div class="list-group-item d-flex justify-content-between align-items-center">';
                                        echo '<span>' . $test['name'] . '</span>';
                                        echo '<span class="badge bg-' . $test['status'] . '">' . $test['message'] . '</span>';
                                        echo '</div>';
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h6>Module Files</h6>
                                <div class="list-group mb-3">
                                    <?php
                                    $moduleFiles = [
                                        'FingerprintBridgeModule.php' => '../app/Modules/FingerprintBridge/FingerprintBridgeModule.php',
                                        'ImportController.php' => '../app/Modules/FingerprintBridge/Controllers/ImportController.php',
                                        'FingerprintImportService.php' => '../app/Modules/FingerprintBridge/Services/FingerprintImportService.php',
                                        'FinProAttLogModel.php' => '../app/Modules/FingerprintBridge/Models/FinProAttLogModel.php',
                                        'Routes.php' => '../app/Modules/FingerprintBridge/Config/Routes.php'
                                    ];
                                    
                                    foreach ($moduleFiles as $name => $path) {
                                        $exists = file_exists($path);
                                        echo '<div class="list-group-item d-flex justify-content-between align-items-center">';
                                        echo '<span>' . $name . '</span>';
                                        if ($exists) {
                                            echo '<span class="badge bg-success"><i class="fas fa-check"></i> Found</span>';
                                        } else {
                                            echo '<span class="badge bg-danger"><i class="fas fa-times"></i> Missing</span>';
                                        }
                                        echo '</div>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <h6>Quick Actions</h6>
                                <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                                    <a href="http://studentfinger.me/fingerprint-bridge" class="btn btn-primary" target="_blank">
                                        <i class="fas fa-tachometer-alt"></i> Open Dashboard
                                    </a>
                                    <a href="http://studentfinger.me/fingerprint-bridge/manual-import" class="btn btn-success" target="_blank">
                                        <i class="fas fa-upload"></i> Manual Import
                                    </a>
                                    <a href="http://studentfinger.me/fingerprint-bridge/logs" class="btn btn-info" target="_blank">
                                        <i class="fas fa-list"></i> Import Logs
                                    </a>
                                    <a href="http://studentfinger.me/fingerprint-bridge/pin-mapping" class="btn btn-warning" target="_blank">
                                        <i class="fas fa-link"></i> PIN Mapping
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <h6>Setup Commands</h6>
                                <div class="alert alert-light">
                                    <p><strong>To complete the setup, run these commands:</strong></p>
                                    <code>
                                        # Create test databases and data<br>
                                        mysql -u root &lt; setup_fin_pro_test.sql<br>
                                        mysql -u root &lt; setup_test_students.sql<br>
                                        mysql -u root &lt; create_fingerprint_bridge_tables.sql<br><br>
                                        
                                        # Test CLI command<br>
                                        php spark fingerprint:import --test --start-date=2025-01-09 --end-date=2025-01-09
                                    </code>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <h6>API Test</h6>
                                <button class="btn btn-outline-primary" onclick="testAPI()">
                                    <i class="fas fa-flask"></i> Test API Connection
                                </button>
                                <div id="apiResult" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function testAPI() {
            const resultDiv = document.getElementById('apiResult');
            resultDiv.innerHTML = '<div class="alert alert-info">Testing API...</div>';
            
            fetch('http://studentfinger.me/api/fingerprint-bridge/stats')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        resultDiv.innerHTML = `
                            <div class="alert alert-success">
                                <strong>API Test Successful!</strong><br>
                                FinPro Records: ${data.data.fin_pro.total_records || 0}<br>
                                StudentFinger Records: ${data.data.student_finger.total_records || 0}<br>
                                PIN Mappings: ${data.data.pin_mapping.active_mappings || 0}
                            </div>
                        `;
                    } else {
                        resultDiv.innerHTML = '<div class="alert alert-warning">API responded but with errors</div>';
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <strong>API Test Failed:</strong><br>
                            ${error.message}<br>
                            <small>Make sure the server is running and the module is properly installed.</small>
                        </div>
                    `;
                });
        }
    </script>
</body>
</html>
