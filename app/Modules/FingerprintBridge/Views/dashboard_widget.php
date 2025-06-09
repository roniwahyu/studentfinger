<!-- FingerprintBridge Dashboard Widget -->
<div class="col-xl-6 col-lg-6">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-fingerprint"></i> Fingerprint Import Status
            </h6>
            <div class="dropdown no-arrow">
                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" 
                   data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                    <div class="dropdown-header">Actions:</div>
                    <a class="dropdown-item" href="<?= base_url('fingerprint-bridge') ?>">
                        <i class="fas fa-tachometer-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                        Dashboard
                    </a>
                    <a class="dropdown-item" href="<?= base_url('fingerprint-bridge/manual-import') ?>">
                        <i class="fas fa-upload fa-sm fa-fw mr-2 text-gray-400"></i>
                        Manual Import
                    </a>
                    <a class="dropdown-item" href="<?= base_url('fingerprint-bridge/logs') ?>">
                        <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                        Import Logs
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="<?= base_url('fingerprint-bridge/settings') ?>">
                        <i class="fas fa-cog fa-sm fa-fw mr-2 text-gray-400"></i>
                        Settings
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php if (isset($fingerprint_stats)): ?>
                <!-- Connection Status -->
                <div class="row mb-3">
                    <div class="col-12">
                        <?php if ($fingerprint_stats['connection']['success']): ?>
                            <div class="alert alert-success py-2 mb-2">
                                <i class="fas fa-check-circle"></i>
                                <strong>Connected</strong> - FinPro database accessible
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger py-2 mb-2">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Disconnected</strong> - Check FinPro database settings
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                FinPro Records
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($fingerprint_stats['fin_pro']['total_records'] ?? 0) ?>
                            </div>
                            <small class="text-muted">
                                <?= number_format($fingerprint_stats['fin_pro']['unique_pins'] ?? 0) ?> PINs
                            </small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Imported Records
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($fingerprint_stats['student_finger']['total_records'] ?? 0) ?>
                            </div>
                            <small class="text-muted">
                                <?= number_format($fingerprint_stats['pin_mapping']['active_mappings'] ?? 0) ?> mapped
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <?php if (!empty($fingerprint_stats['recent_imports'])): ?>
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="text-xs font-weight-bold text-gray-600 text-uppercase mb-2">
                                Recent Imports
                            </h6>
                            <?php foreach (array_slice($fingerprint_stats['recent_imports'], 0, 3) as $import): ?>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div>
                                        <small class="text-muted">
                                            <?= date('M j, H:i', strtotime($import['created_at'])) ?>
                                        </small>
                                        <span class="badge badge-<?= $import['status'] === 'completed' ? 'success' : ($import['status'] === 'failed' ? 'danger' : 'warning') ?> badge-sm ml-1">
                                            <?= ucfirst($import['status']) ?>
                                        </span>
                                    </div>
                                    <small class="text-muted">
                                        <?= number_format($import['processed_records']) ?> records
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Running Imports -->
                <?php if (!empty($fingerprint_stats['running_imports'])): ?>
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="alert alert-warning py-2">
                                <i class="fas fa-spinner fa-spin"></i>
                                <strong><?= count($fingerprint_stats['running_imports']) ?> import(s) running</strong>
                                <div class="progress mt-2" style="height: 5px;">
                                    <?php 
                                    $runningImport = $fingerprint_stats['running_imports'][0];
                                    $progress = $runningImport['total_records'] > 0 
                                        ? ($runningImport['processed_records'] / $runningImport['total_records']) * 100 
                                        : 0;
                                    ?>
                                    <div class="progress-bar bg-warning" style="width: <?= $progress ?>%"></div>
                                </div>
                                <small class="text-muted">
                                    <?= number_format($runningImport['processed_records']) ?> / 
                                    <?= number_format($runningImport['total_records']) ?> 
                                    (<?= number_format($progress, 1) ?>%)
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Quick Actions -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <a href="<?= base_url('fingerprint-bridge/manual-import') ?>" 
                               class="btn btn-primary btn-sm">
                                <i class="fas fa-upload"></i> Import
                            </a>
                            <a href="<?= base_url('fingerprint-bridge/pin-mapping') ?>" 
                               class="btn btn-info btn-sm">
                                <i class="fas fa-link"></i> PIN Map
                            </a>
                            <a href="<?= base_url('fingerprint-bridge') ?>" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye"></i> View All
                            </a>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- Module Not Available -->
                <div class="text-center py-4">
                    <i class="fas fa-fingerprint fa-3x text-gray-300 mb-3"></i>
                    <p class="text-muted">FingerprintBridge module not available</p>
                    <a href="<?= base_url('fingerprint-bridge') ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-cog"></i> Configure
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Auto-refresh for running imports -->
<?php if (!empty($fingerprint_stats['running_imports'])): ?>
<script>
// Auto-refresh widget every 30 seconds if there are running imports
setTimeout(function() {
    location.reload();
}, 30000);
</script>
<?php endif; ?>
