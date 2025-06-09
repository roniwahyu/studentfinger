<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title><?= $this->renderSection('title') ?> - Student Finger</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background: white;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }
        .main-content {
            padding: 20px;
        }
        .navbar {
            background: white;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }
        .card {
            border: none;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            border-radius: 10px;
        }
    </style>
    <?= $this->renderSection('styles') ?>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-3">
                    <h4 class="text-center mb-4">Student Finger</h4>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('dashboard') ?>">
                                <i class="fas fa-home me-2"></i> Dashboard
                            </a>
                        </li>

                        <!-- Student Management -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('students') ?>">
                                <i class="fas fa-users me-2"></i> Students
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('classes') ?>">
                                <i class="fas fa-chalkboard me-2"></i> Classes
                            </a>
                        </li>

                        <!-- Attendance Management -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('attendance') ?>">
                                <i class="fas fa-calendar-check me-2"></i> Attendance
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('attendance-logs') ?>">
                                <i class="fas fa-list-alt me-2"></i> Attendance Logs
                            </a>
                        </li>

                        <!-- Fingerprint Integration -->
                        <?php
                        helper('fingerprint');
                        if (is_fingerprint_module_available()):
                            $fingerprintStats = get_fingerprint_summary_stats();
                            $runningImports = $fingerprintStats['running_imports'] ?? 0;
                            $unmappedPins = $fingerprintStats['unmapped_pins'] ?? 0;
                        ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('fingerprint-bridge') ?>" style="background: linear-gradient(135deg, #6f42c1 0%, #007bff 100%); color: white; border-radius: 8px; margin: 4px 0;">
                                <i class="fas fa-fingerprint me-2"></i> Import Finger
                                <?php if ($runningImports > 0): ?>
                                    <span class="badge bg-warning text-dark ms-2"><?= $runningImports ?></span>
                                <?php elseif ($unmappedPins > 0): ?>
                                    <span class="badge bg-info ms-2"><?= $unmappedPins ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- Classroom Notifications -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('classroom-notifications') ?>" style="background: linear-gradient(135deg, #FF6B6B 0%, #4ECDC4 100%); color: white; border-radius: 8px; margin: 4px 0;">
                                <i class="fas fa-bell me-2"></i> Class Notifications
                                <span class="badge bg-light text-dark ms-2">NEW</span>
                            </a>
                        </li>

                        <!-- WhatsApp Integration -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('wablas-frontend') ?>" style="background: linear-gradient(135deg, #25D366 0%, #128C7E 100%); color: white; border-radius: 8px; margin: 4px 0;">
                                <i class="fab fa-whatsapp me-2"></i> WablasFrontEnd
                                <span class="badge bg-light text-dark ms-2">NEW</span>
                            </a>
                        </li>

                        <!-- System Management -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('user-logs') ?>">
                                <i class="fas fa-history me-2"></i> User Logs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= base_url('table-manager') ?>">
                                <i class="fas fa-database me-2"></i> Table Manager
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <!-- Navbar -->
                <nav class="navbar navbar-expand-lg mb-4">
                    <div class="container-fluid">
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarNav">
                            <ul class="navbar-nav ms-auto">
                                <!-- Quick Actions -->
                                <?php
                                helper('fingerprint');
                                if (is_fingerprint_module_available()):
                                ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= base_url('fingerprint-bridge') ?>" title="Fingerprint Import">
                                        <i class="fas fa-fingerprint text-purple"></i>
                                    </a>
                                </li>
                                <?php endif; ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= base_url('wablas-frontend') ?>" title="WhatsApp Integration">
                                        <i class="fab fa-whatsapp text-success"></i>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?= base_url('attendance') ?>" title="Attendance">
                                        <i class="fas fa-calendar-check text-primary"></i>
                                    </a>
                                </li>

                                <!-- User Dropdown -->
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-user me-2"></i> Admin
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="<?= base_url('wablas-frontend/settings') ?>"><i class="fas fa-cog me-2"></i> WABLAS Settings</a></li>
                                        <li><a class="dropdown-item" href="#"><i class="fas fa-user-cog me-2"></i> Profile Settings</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="#"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>

                <!-- Flash Messages -->
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= session()->getFlashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= session()->getFlashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('warning')): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?= session()->getFlashdata('warning') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('info')): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <?= session()->getFlashdata('info') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Content -->
                <?= $this->renderSection('content') ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Initialize Components -->
    <script>
        $(document).ready(function() {
            // Initialize DataTables
            $('.datatable').DataTable({
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search..."
                }
            });

            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap-5'
            });

            // Initialize SweetAlert2
            window.showToast = function(message, type = 'success') {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });

                Toast.fire({
                    icon: type,
                    title: message
                });
            };
        });
    </script>

    <!-- CSRF token setup for AJAX requests -->
    <script>
        // CSRF token setup for AJAX requests
        $.ajaxSetup({
            beforeSend: function(xhr, settings) {
                if (!/^(GET|HEAD|OPTIONS|TRACE)$/i.test(settings.type) && !this.crossDomain) {
                    xhr.setRequestHeader("X-CSRF-TOKEN", $('meta[name="csrf-token"]').attr('content'));
                }
            }
        });
    </script>

    <?= $this->renderSection('scripts') ?>
</body>
</html>