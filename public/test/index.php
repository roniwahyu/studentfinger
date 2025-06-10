<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentFinger Test Suite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .main-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 2rem;
            margin: 2rem auto;
            max-width: 1200px;
        }
        
        .test-card {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .test-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .btn-test {
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            margin: 0.25rem;
        }
        
        .category-header {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            margin: 1.5rem 0 1rem 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-container">
            <div class="text-center mb-4">
                <h1 class="display-4 text-primary">
                    <i class="fas fa-vial me-3"></i>
                    StudentFinger Test Suite
                </h1>
                <p class="lead text-muted">Comprehensive testing tools for the StudentFinger application</p>
            </div>

            <!-- Application Tests -->
            <div class="category-header">
                <h3><i class="fas fa-desktop me-2"></i>Application Tests</h3>
                <p class="mb-0">Test core application functionality and endpoints</p>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="test-card">
                        <h5><i class="fas fa-globe text-primary me-2"></i>Application Endpoints</h5>
                        <p class="text-muted">Test all main application endpoints and pages</p>
                        <a href="test_app.php" class="btn btn-primary btn-test">
                            <i class="fas fa-play me-1"></i>Run Test
                        </a>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="test-card">
                        <h5><i class="fas fa-home text-success me-2"></i>Home Page</h5>
                        <p class="text-muted">Test home page dashboard functionality</p>
                        <a href="test_home_page.php" class="btn btn-success btn-test">
                            <i class="fas fa-play me-1"></i>Run Test
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="test-card">
                        <h5><i class="fas fa-cog text-info me-2"></i>Simple Controller</h5>
                        <p class="text-muted">Test basic controller functionality</p>
                        <a href="test_simple.php" class="btn btn-info btn-test">
                            <i class="fas fa-play me-1"></i>Run Test
                        </a>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="test-card">
                        <h5><i class="fas fa-route text-secondary me-2"></i>Route Testing</h5>
                        <p class="text-muted">Test application routing configuration</p>
                        <a href="simple_route_test.php" class="btn btn-secondary btn-test">
                            <i class="fas fa-play me-1"></i>Run Test
                        </a>
                    </div>
                </div>
            </div>

            <!-- Database Tests -->
            <div class="category-header">
                <h3><i class="fas fa-database me-2"></i>Database Tests</h3>
                <p class="mb-0">Test database connections and data integrity</p>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="test-card">
                        <h5><i class="fas fa-plug text-info me-2"></i>Database Connection</h5>
                        <p class="text-muted">Test MySQL database connectivity and table structure</p>
                        <a href="test_db.php" class="btn btn-info btn-test">
                            <i class="fas fa-play me-1"></i>Run Test
                        </a>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="test-card">
                        <h5><i class="fas fa-link text-warning me-2"></i>DB Connection Extended</h5>
                        <p class="text-muted">Extended database connection testing</p>
                        <a href="test_db_connection.php" class="btn btn-warning btn-test">
                            <i class="fas fa-play me-1"></i>Run Test
                        </a>
                    </div>
                </div>
            </div>

            <!-- Module Tests -->
            <div class="category-header">
                <h3><i class="fas fa-puzzle-piece me-2"></i>Module Tests</h3>
                <p class="mb-0">Test specific modules and integrations</p>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="test-card">
                        <h5><i class="fas fa-fingerprint text-primary me-2"></i>Fingerprint Bridge</h5>
                        <p class="text-muted">Test fingerprint device integration</p>
                        <a href="test_fingerprint_bridge.php" class="btn btn-primary btn-test">
                            <i class="fas fa-play me-1"></i>Run Test
                        </a>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="test-card">
                        <h5><i class="fab fa-whatsapp text-success me-2"></i>WhatsApp Integration</h5>
                        <p class="text-muted">Test WhatsApp messaging integration</p>
                        <a href="test_wablas_integration.php" class="btn btn-success btn-test">
                            <i class="fas fa-play me-1"></i>Run Test
                        </a>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="test-card">
                        <h5><i class="fas fa-calendar-check text-info me-2"></i>Attendance System</h5>
                        <p class="text-muted">Test attendance tracking and notifications</p>
                        <a href="test_attendance_notification.php" class="btn btn-info btn-test">
                            <i class="fas fa-play me-1"></i>Run Test
                        </a>
                    </div>
                </div>
            </div>

            <!-- System Tests -->
            <div class="category-header">
                <h3><i class="fas fa-server me-2"></i>System Tests</h3>
                <p class="mb-0">Test complete system functionality and production readiness</p>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="test-card">
                        <h5><i class="fas fa-rocket text-danger me-2"></i>Production Ready System</h5>
                        <p class="text-muted">Comprehensive production readiness test</p>
                        <a href="test_production_ready_system.php" class="btn btn-danger btn-test">
                            <i class="fas fa-play me-1"></i>Run Test
                        </a>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="test-card">
                        <h5><i class="fas fa-check-circle text-success me-2"></i>Complete System</h5>
                        <p class="text-muted">End-to-end system functionality test</p>
                        <a href="test_complete_system.php" class="btn btn-success btn-test">
                            <i class="fas fa-play me-1"></i>Run Test
                        </a>
                    </div>
                </div>
            </div>

            <!-- Additional Tests -->
            <div class="category-header">
                <h3><i class="fas fa-tools me-2"></i>Additional Tests</h3>
                <p class="mb-0">Specialized testing tools and utilities</p>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="test-card">
                        <h5><i class="fas fa-tools text-secondary me-2"></i>Enhanced Tests</h5>
                        <p class="text-muted">Additional specialized testing tools</p>
                        <a href="test_enhanced_fingerprint_bridge.php" class="btn btn-secondary btn-test">
                            <i class="fas fa-play me-1"></i>Run Test
                        </a>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="test-card">
                        <h5><i class="fas fa-bell text-warning me-2"></i>Classroom Notifications</h5>
                        <p class="text-muted">Test classroom notification system</p>
                        <a href="test_classroom_notification.php" class="btn btn-warning btn-test">
                            <i class="fas fa-play me-1"></i>Run Test
                        </a>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="test-card">
                        <h5><i class="fas fa-cubes text-info me-2"></i>HMVC Architecture</h5>
                        <p class="text-muted">Test modular architecture implementation</p>
                        <a href="test_hmvc.php" class="btn btn-info btn-test">
                            <i class="fas fa-play me-1"></i>Run Test
                        </a>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <hr>
                <p class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    All tests are designed to verify system functionality and identify potential issues.
                </p>
                <a href="../../" class="btn btn-outline-primary">
                    <i class="fas fa-home me-1"></i>Back to Application
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
