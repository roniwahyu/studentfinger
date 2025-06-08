<?= $this->extend('layouts/main') ?>

<?php $this->section('title'); ?>
Dashboard
<?php $this->endSection(); ?>

<?php $this->section('content'); ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Dashboard</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-1">
                            <h2 class="text-white mb-2">Welcome to Student Management System</h2>
                            <p class="text-white-50 mb-0">Manage students, track attendance, and generate reports efficiently.</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-graduation-cap fa-4x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1">
                            <p class="text-truncate font-size-14 mb-2">Total Students</p>
                            <h4 class="mb-2"><?= $totalStudents ?? 0 ?></h4>
                            <p class="text-muted mb-0">
                                <span class="text-success fw-bold font-size-12 me-2">
                                    <i class="ri-arrow-right-up-line me-1 align-middle"></i>
                                    Active
                                </span>
                            </p>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-primary rounded-3">
                                <i class="fas fa-users font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1">
                            <p class="text-truncate font-size-14 mb-2">Present Today</p>
                            <h4 class="mb-2"><?= $presentToday ?? 0 ?></h4>
                            <p class="text-muted mb-0">
                                <span class="text-success fw-bold font-size-12 me-2">
                                    <i class="ri-arrow-right-up-line me-1 align-middle"></i>
                                    <?= $presentPercentage ?? 0 ?>%
                                </span>
                            </p>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-success rounded-3">
                                <i class="fas fa-user-check font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1">
                            <p class="text-truncate font-size-14 mb-2">Absent Today</p>
                            <h4 class="mb-2"><?= $absentToday ?? 0 ?></h4>
                            <p class="text-muted mb-0">
                                <span class="text-danger fw-bold font-size-12 me-2">
                                    <i class="ri-arrow-right-down-line me-1 align-middle"></i>
                                    <?= $absentPercentage ?? 0 ?>%
                                </span>
                            </p>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-danger rounded-3">
                                <i class="fas fa-user-times font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1">
                            <p class="text-truncate font-size-14 mb-2">Total Classes</p>
                            <h4 class="mb-2"><?= $totalClasses ?? 0 ?></h4>
                            <p class="text-muted mb-0">
                                <span class="text-info fw-bold font-size-12 me-2">
                                    Active Classes
                                </span>
                            </p>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-info rounded-3">
                                <i class="fas fa-chalkboard-teacher font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Quick Actions</h4>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="<?= base_url('students/create') ?>" class="btn btn-primary w-100 p-3">
                                <i class="fas fa-user-plus fa-2x mb-2 d-block"></i>
                                Add New Student
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= base_url('attendance/mark') ?>" class="btn btn-success w-100 p-3">
                                <i class="fas fa-check-circle fa-2x mb-2 d-block"></i>
                                Mark Attendance
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= base_url('attendance/report') ?>" class="btn btn-info w-100 p-3">
                                <i class="fas fa-chart-bar fa-2x mb-2 d-block"></i>
                                View Reports
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="<?= base_url('students') ?>" class="btn btn-warning w-100 p-3">
                                <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                Manage Students
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Recent Attendance</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Class</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($recentAttendance) && !empty($recentAttendance)): ?>
                                    <?php foreach ($recentAttendance as $record): ?>
                                        <tr>
                                            <td><?= esc($record['student_name']) ?></td>
                                            <td><?= esc($record['class_name']) ?></td>
                                            <td><?= date('h:i A', strtotime($record['time_in'])) ?></td>
                                            <td>
                                                <?php if ($record['status'] == 'present'): ?>
                                                    <span class="badge bg-success">Present</span>
                                                <?php elseif ($record['status'] == 'late'): ?>
                                                    <span class="badge bg-warning">Late</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Absent</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-calendar-check fa-3x mb-3"></i>
                                                <p class="mb-0">No recent attendance records</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">System Information</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>CodeIgniter Version:</span>
                            <span class="fw-bold"><?= \CodeIgniter\CodeIgniter::CI_VERSION ?></span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>PHP Version:</span>
                            <span class="fw-bold"><?= PHP_VERSION ?></span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Environment:</span>
                            <span class="badge bg-<?= ENVIRONMENT === 'production' ? 'success' : 'warning' ?>">
                                <?= ucfirst(ENVIRONMENT) ?>
                            </span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Server Time:</span>
                            <span class="fw-bold"><?= date('Y-m-d H:i:s') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection(); ?>
