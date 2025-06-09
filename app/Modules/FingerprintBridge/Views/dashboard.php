<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-fingerprint text-primary"></i>
            <?= $title ?>
        </h1>
        <div>
            <a href="<?= base_url('fingerprint-bridge/manual-import') ?>" class="btn btn-primary">
                <i class="fas fa-upload"></i> Manual Import
            </a>
            <a href="<?= base_url('fingerprint-bridge/settings') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-cog"></i> Settings
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <!-- FinPro Database Stats -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                FinPro Records
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['fin_pro']['total_records'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-database fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- StudentFinger Database Stats -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Imported Records
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['student_finger']['total_records'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mapped Students -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Mapped Students
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['pin_mapping']['active_mappings'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Unmapped PINs -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Unmapped PINs
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['pin_mapping']['unmapped_pins'] ?? 0) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Import Logs -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Import Logs</h6>
                    <a href="<?= base_url('fingerprint-bridge/logs') ?>" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_logs)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Date Range</th>
                                        <th>Status</th>
                                        <th>Records</th>
                                        <th>Duration</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_logs as $log): ?>
                                        <tr>
                                            <td>
                                                <span class="badge badge-<?= $log['import_type'] === 'manual' ? 'primary' : 'info' ?>">
                                                    <?= ucfirst($log['import_type']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($log['start_date'] && $log['end_date']): ?>
                                                    <?= date('M j', strtotime($log['start_date'])) ?> - 
                                                    <?= date('M j, Y', strtotime($log['end_date'])) ?>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = [
                                                    'pending' => 'secondary',
                                                    'running' => 'warning',
                                                    'completed' => 'success',
                                                    'failed' => 'danger',
                                                    'cancelled' => 'dark'
                                                ];
                                                ?>
                                                <span class="badge badge-<?= $statusClass[$log['status']] ?? 'secondary' ?>">
                                                    <?= ucfirst($log['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= number_format($log['processed_records']) ?> / 
                                                <?= number_format($log['total_records']) ?>
                                            </td>
                                            <td>
                                                <?php if ($log['duration']): ?>
                                                    <?= gmdate('H:i:s', $log['duration']) ?>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="<?= base_url('fingerprint-bridge/logs/' . $log['id']) ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">No import logs found</p>
                            <a href="<?= base_url('fingerprint-bridge/manual-import') ?>" class="btn btn-primary">
                                Start First Import
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="<?= base_url('fingerprint-bridge/manual-import') ?>" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-upload text-primary"></i>
                            Manual Import
                            <small class="text-muted d-block">Import data for specific date range</small>
                        </a>
                        
                        <a href="<?= base_url('fingerprint-bridge/pin-mapping') ?>" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-link text-info"></i>
                            PIN Mapping
                            <small class="text-muted d-block">Map fingerprint PINs to students</small>
                        </a>
                        
                        <a href="<?= base_url('fingerprint-bridge/logs') ?>" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-list text-success"></i>
                            Import History
                            <small class="text-muted d-block">View all import logs</small>
                        </a>
                        
                        <a href="<?= base_url('fingerprint-bridge/settings') ?>" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-cog text-secondary"></i>
                            Settings
                            <small class="text-muted d-block">Configure import settings</small>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Running Imports -->
            <?php if (!empty($running_imports)): ?>
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-warning">Running Imports</h6>
                    </div>
                    <div class="card-body">
                        <?php foreach ($running_imports as $import): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong><?= ucfirst($import['import_type']) ?> Import</strong>
                                    <br>
                                    <small class="text-muted">
                                        Started: <?= date('M j, Y H:i', strtotime($import['start_time'])) ?>
                                    </small>
                                </div>
                                <div class="text-right">
                                    <div class="progress" style="width: 100px;">
                                        <?php 
                                        $progress = $import['total_records'] > 0 
                                            ? ($import['processed_records'] / $import['total_records']) * 100 
                                            : 0;
                                        ?>
                                        <div class="progress-bar" style="width: <?= $progress ?>%"></div>
                                    </div>
                                    <small><?= number_format($progress, 1) ?>%</small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Auto-refresh running imports every 30 seconds
<?php if (!empty($running_imports)): ?>
setInterval(function() {
    location.reload();
}, 30000);
<?php endif; ?>
</script>
<?= $this->endSection() ?>
