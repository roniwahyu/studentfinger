<?= $this->extend('layouts/main') ?>

<?php $this->section('title'); ?>
<?= $title ?? 'Attendance Management' ?>
<?php $this->endSection(); ?>

<?php $this->section('content'); ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0"><?= $title ?? 'Attendance Management' ?></h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Attendance</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="<?= base_url('attendance/mark') ?>" class="btn btn-primary">
                        <i class="fas fa-check"></i> Mark Attendance
                    </a>
                    <a href="<?= base_url('attendance/report') ?>" class="btn btn-info">
                        <i class="fas fa-chart-bar"></i> View Reports
                    </a>
                </div>
                <div>
                    <a href="<?= base_url('attendance/export') ?>" class="btn btn-success">
                        <i class="fas fa-download"></i> Export Data
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-1">
                            <p class="text-truncate font-size-14 mb-2">Today's Present</p>
                            <h4 class="mb-2"><?= $todayPresent ?? 0 ?></h4>
                            <p class="text-muted mb-0">
                                <span class="text-success fw-bold font-size-12 me-2">
                                    <i class="ri-arrow-right-up-line me-1 align-middle"></i>
                                    <?= $presentPercentage ?? 0 ?>%
                                </span>
                            </p>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-primary rounded-3">
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
                            <p class="text-truncate font-size-14 mb-2">Today's Absent</p>
                            <h4 class="mb-2"><?= $todayAbsent ?? 0 ?></h4>
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
                            <p class="text-truncate font-size-14 mb-2">Total Students</p>
                            <h4 class="mb-2"><?= $totalStudents ?? 0 ?></h4>
                            <p class="text-muted mb-0">
                                <span class="text-info fw-bold font-size-12 me-2">
                                    Active Students
                                </span>
                            </p>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-info rounded-3">
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
                            <p class="text-truncate font-size-14 mb-2">Attendance Rate</p>
                            <h4 class="mb-2"><?= $attendanceRate ?? 0 ?>%</h4>
                            <p class="text-muted mb-0">
                                <span class="text-success fw-bold font-size-12 me-2">
                                    This Month
                                </span>
                            </p>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-success rounded-3">
                                <i class="fas fa-chart-line font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Attendance -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Recent Attendance Records</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Student ID</th>
                                    <th>Student Name</th>
                                    <th>Class</th>
                                    <th>Section</th>
                                    <th>Date</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($recentAttendance) && !empty($recentAttendance)): ?>
                                    <?php foreach ($recentAttendance as $record): ?>
                                        <tr>
                                            <td><?= esc($record['student_id']) ?></td>
                                            <td><?= esc($record['student_name']) ?></td>
                                            <td><?= esc($record['class_name']) ?></td>
                                            <td><?= esc($record['section_name']) ?></td>
                                            <td><?= date('M d, Y', strtotime($record['date'])) ?></td>
                                            <td>
                                                <?= $record['time_in'] ? date('h:i A', strtotime($record['time_in'])) : '-' ?>
                                            </td>
                                            <td>
                                                <?= $record['time_out'] ? date('h:i A', strtotime($record['time_out'])) : '-' ?>
                                            </td>
                                            <td>
                                                <?php if ($record['status'] == 'present'): ?>
                                                    <span class="badge bg-success">Present</span>
                                                <?php elseif ($record['status'] == 'absent'): ?>
                                                    <span class="badge bg-danger">Absent</span>
                                                <?php elseif ($record['status'] == 'late'): ?>
                                                    <span class="badge bg-warning">Late</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><?= ucfirst($record['status']) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?= base_url('attendance/student/' . $record['student_id']) ?>" 
                                                       class="btn btn-sm btn-info" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-warning edit-attendance" 
                                                            data-id="<?= $record['id'] ?>" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-calendar-check fa-3x mb-3"></i>
                                                <p class="mb-0">No attendance records found</p>
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
    </div>
</div>
<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
<script>
$(document).ready(function() {
    // Edit attendance functionality
    $('.edit-attendance').click(function() {
        const attendanceId = $(this).data('id');
        // Add edit attendance modal or redirect to edit page
        window.location.href = '<?= base_url('attendance/edit') ?>/' + attendanceId;
    });

    // Auto-refresh attendance data every 30 seconds
    setInterval(function() {
        // You can implement auto-refresh functionality here
        // location.reload();
    }, 30000);
});
</script>
<?php $this->endSection(); ?>