<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Dashboard</h4>
        <div>
            <button class="btn btn-primary" onclick="showToast('Welcome to Dashboard!')">
                <i class="fas fa-bell me-2"></i>Test Notification
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Students</h6>
                            <h3 class="mb-0"><?= number_format($totalStudents) ?></h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="fas fa-users text-primary fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Present Today</h6>
                            <h3 class="mb-0"><?= number_format($presentToday) ?></h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="fas fa-user-check text-success fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Absent Today</h6>
                            <h3 class="mb-0"><?= number_format($absentToday) ?></h3>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded">
                            <i class="fas fa-user-times text-danger fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Late Today</h6>
                            <h3 class="mb-0"><?= number_format($lateToday) ?></h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="fas fa-clock text-warning fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FingerprintBridge Widget -->
    <div class="row">
        <?php
        // Load fingerprint helper and display widget
        helper('fingerprint');
        echo get_fingerprint_dashboard_widget();
        ?>

        <!-- Quick Actions Card -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <a href="<?= base_url('students/create') ?>" class="btn btn-primary btn-block">
                                <i class="fas fa-user-plus"></i><br>
                                <small>Add Student</small>
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="<?= base_url('attendance') ?>" class="btn btn-success btn-block">
                                <i class="fas fa-check-circle"></i><br>
                                <small>Take Attendance</small>
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="<?= base_url('classes') ?>" class="btn btn-info btn-block">
                                <i class="fas fa-chalkboard"></i><br>
                                <small>Manage Classes</small>
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="<?= base_url('attendance-logs') ?>" class="btn btn-warning btn-block">
                                <i class="fas fa-list"></i><br>
                                <small>View Reports</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Classroom Notifications Widget -->
    <div class="row">
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bell"></i> Classroom Notifications
                    </h6>
                    <a href="<?= base_url('classroom-notifications') ?>" class="btn btn-sm btn-primary">
                        View Dashboard
                    </a>
                </div>
                <div class="card-body">
                    <?php
                    // Get classroom notifications stats
                    try {
                        $db = \Config\Database::connect();

                        // Get today's sessions
                        $todaySessionsQuery = $db->query("SELECT COUNT(*) as count FROM class_sessions WHERE session_date = CURDATE()");
                        $todaySessions = $todaySessionsQuery->getRowArray()['count'] ?? 0;

                        // Get active sessions
                        $activeSessionsQuery = $db->query("SELECT COUNT(*) as count FROM class_sessions WHERE status IN ('started', 'break', 'resumed')");
                        $activeSessions = $activeSessionsQuery->getRowArray()['count'] ?? 0;

                        // Get today's notifications
                        $todayNotificationsQuery = $db->query("SELECT COUNT(*) as count FROM notification_logs WHERE DATE(created_at) = CURDATE()");
                        $todayNotifications = $todayNotificationsQuery->getRowArray()['count'] ?? 0;

                        // Get recent sessions
                        $recentSessionsQuery = $db->query("
                            SELECT cs.*, c.class as class_name
                            FROM class_sessions cs
                            LEFT JOIN classes c ON c.id = cs.class_id
                            WHERE cs.session_date = CURDATE()
                            ORDER BY cs.start_time ASC
                            LIMIT 3
                        ");
                        $recentSessions = $recentSessionsQuery->getResultArray();

                    } catch (Exception $e) {
                        $todaySessions = 0;
                        $activeSessions = 0;
                        $todayNotifications = 0;
                        $recentSessions = [];
                    }
                    ?>

                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <div class="h5 mb-0 font-weight-bold text-primary"><?= $todaySessions ?></div>
                            <small class="text-muted">Today's Sessions</small>
                        </div>
                        <div class="col-4">
                            <div class="h5 mb-0 font-weight-bold text-warning"><?= $activeSessions ?></div>
                            <small class="text-muted">Active Now</small>
                        </div>
                        <div class="col-4">
                            <div class="h5 mb-0 font-weight-bold text-success"><?= $todayNotifications ?></div>
                            <small class="text-muted">Notifications</small>
                        </div>
                    </div>

                    <?php if (!empty($recentSessions)): ?>
                        <div class="border-top pt-3">
                            <h6 class="text-muted mb-2">Today's Sessions:</h6>
                            <?php foreach ($recentSessions as $session): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <div class="font-weight-bold"><?= htmlspecialchars($session['subject']) ?></div>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($session['class_name'] ?? 'Unknown') ?> •
                                            <?= date('H:i', strtotime($session['start_time'])) ?>
                                        </small>
                                    </div>
                                    <div>
                                        <?php
                                        $statusColors = [
                                            'scheduled' => 'secondary',
                                            'started' => 'success',
                                            'break' => 'warning',
                                            'resumed' => 'info',
                                            'finished' => 'primary'
                                        ];
                                        $statusColor = $statusColors[$session['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge badge-<?= $statusColor ?> badge-sm">
                                            <?= ucfirst($session['status']) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-calendar-plus fa-2x text-gray-300 mb-2"></i>
                            <p class="text-muted mb-2">No sessions scheduled for today</p>
                            <a href="<?= base_url('classroom-notifications/sessions/create') ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus"></i> Create Session
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- WhatsApp Status Widget -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fab fa-whatsapp"></i> WhatsApp Integration
                    </h6>
                    <a href="<?= base_url('classroom-notifications/settings') ?>" class="btn btn-sm btn-info">
                        Settings
                    </a>
                </div>
                <div class="card-body">
                    <?php
                    // Test WABLAS connection
                    try {
                        $wablasBaseUrl = env('WABLAS_BASE_URL', '');
                        $wablasToken = env('WABLAS_TOKEN', '');
                        $wablasSecretKey = env('WABLAS_SECRET_KEY', '');

                        $wablasConfigured = !empty($wablasBaseUrl) && !empty($wablasToken) && !empty($wablasSecretKey);

                        if ($wablasConfigured) {
                            // Quick connection test
                            $ch = curl_init();
                            curl_setopt_array($ch, [
                                CURLOPT_URL => rtrim($wablasBaseUrl, '/') . '/api/device/status',
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_TIMEOUT => 5,
                                CURLOPT_HTTPHEADER => [
                                    'Authorization: ' . $wablasToken . '.' . $wablasSecretKey
                                ],
                                CURLOPT_SSL_VERIFYPEER => false
                            ]);

                            $response = curl_exec($ch);
                            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                            curl_close($ch);

                            $wablasConnected = ($httpCode === 200);
                        } else {
                            $wablasConnected = false;
                        }
                    } catch (Exception $e) {
                        $wablasConfigured = false;
                        $wablasConnected = false;
                    }
                    ?>

                    <div class="text-center mb-3">
                        <?php if ($wablasConnected): ?>
                            <div class="text-success mb-2">
                                <i class="fas fa-check-circle fa-3x"></i>
                            </div>
                            <h6 class="text-success">WhatsApp Connected</h6>
                            <p class="text-muted">WABLAS API is working properly</p>
                        <?php elseif ($wablasConfigured): ?>
                            <div class="text-warning mb-2">
                                <i class="fas fa-exclamation-triangle fa-3x"></i>
                            </div>
                            <h6 class="text-warning">Connection Issue</h6>
                            <p class="text-muted">WABLAS configured but not responding</p>
                        <?php else: ?>
                            <div class="text-secondary mb-2">
                                <i class="fas fa-cog fa-3x"></i>
                            </div>
                            <h6 class="text-secondary">Not Configured</h6>
                            <p class="text-muted">WABLAS API needs configuration</p>
                        <?php endif; ?>
                    </div>

                    <div class="row text-center">
                        <div class="col-6">
                            <a href="<?= base_url('classroom-notifications') ?>" class="btn btn-primary btn-block btn-sm">
                                <i class="fas fa-bell"></i><br>
                                <small>Notifications</small>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="<?= base_url('wablas-frontend') ?>" class="btn btn-success btn-block btn-sm">
                                <i class="fab fa-whatsapp"></i><br>
                                <small>WABLAS</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Attendance Table -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-4">Recent Attendance</h5>
            <div class="table-responsive">
                <table class="table table-hover datatable">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Method</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentAttendance)): ?>
                            <?php foreach ($recentAttendance as $attendance): ?>
                            <tr>
                                <td><?= esc($attendance['student_id']) ?></td>
                                <td><?= esc($attendance['name']) ?></td>
                                <td><?= esc($attendance['time_in']) ?></td>
                                <td>
                                    <?php
                                    $badgeClass = 'bg-secondary';
                                    switch ($attendance['status']) {
                                        case 'Present':
                                            $badgeClass = 'bg-success';
                                            break;
                                        case 'Late':
                                            $badgeClass = 'bg-warning';
                                            break;
                                        case 'Absent':
                                            $badgeClass = 'bg-danger';
                                            break;
                                        case 'Permission':
                                            $badgeClass = 'bg-info';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= esc($attendance['status']) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark"><?= esc($attendance['verify_mode']) ?></span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="showToast('Viewing details...')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">No recent attendance records found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Additional dashboard-specific scripts can be added here
</script>
<?= $this->endSection() ?> 