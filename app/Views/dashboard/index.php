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