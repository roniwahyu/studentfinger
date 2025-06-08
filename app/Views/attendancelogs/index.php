<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Attendance Logs<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Attendance Logs</h4>
        <a href="<?= base_url('attendance-logs/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Log
        </a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= base_url('attendance-logs') ?>">
                <div class="row">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search"
                               value="<?= esc($filters['search']) ?>" placeholder="PIN, Name, Serial...">
                    </div>
                    <div class="col-md-2">
                        <label for="date_from" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="date_from" name="date_from"
                               value="<?= esc($filters['date_from']) ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="date_to" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="date_to" name="date_to"
                               value="<?= esc($filters['date_to']) ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="verifymode" class="form-label">Verify Mode</label>
                        <select class="form-control" id="verifymode" name="verifymode">
                            <option value="">All</option>
                            <option value="1" <?= $filters['verifymode'] == '1' ? 'selected' : '' ?>>Fingerprint</option>
                            <option value="3" <?= $filters['verifymode'] == '3' ? 'selected' : '' ?>>RFID Card</option>
                            <option value="20" <?= $filters['verifymode'] == '20' ? 'selected' : '' ?>>Face Recognition</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="inoutmode" class="form-label">In/Out Mode</label>
                        <select class="form-control" id="inoutmode" name="inoutmode">
                            <option value="">All</option>
                            <option value="0" <?= $filters['inoutmode'] == '0' ? 'selected' : '' ?>>Check In</option>
                            <option value="1" <?= $filters['inoutmode'] == '1' ? 'selected' : '' ?>>Check In</option>
                            <option value="2" <?= $filters['inoutmode'] == '2' ? 'selected' : '' ?>>Check Out</option>
                            <option value="3" <?= $filters['inoutmode'] == '3' ? 'selected' : '' ?>>Break Out</option>
                            <option value="4" <?= $filters['inoutmode'] == '4' ? 'selected' : '' ?>>Break In</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="<?= base_url('attendance-logs') ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>PIN</th>
                            <th>Student</th>
                            <th>Device</th>
                            <th>Verify Mode</th>
                            <th>In/Out Mode</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($attendancelogs)): ?>
                            <?php foreach ($attendancelogs as $item): ?>
                            <?php
                                $encodedKey = base64_encode($item['sn'] . '|' . $item['scan_date'] . '|' . $item['pin']);
                                $studentName = trim(($item['firstname'] ?? '') . ' ' . ($item['lastname'] ?? '')) ?: 'Unknown';
                            ?>
                            <tr>
                                <td><?= date('Y-m-d H:i:s', strtotime($item['scan_date'])) ?></td>
                                <td><?= esc($item['pin']) ?></td>
                                <td>
                                    <?= esc($studentName) ?>
                                    <?php if (!empty($item['student_code'])): ?>
                                        <br><small class="text-muted"><?= esc($item['student_code']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($item['sn']) ?></td>
                                <td>
                                    <?php
                                    $verifyLabels = [1 => 'Fingerprint', 3 => 'RFID Card', 20 => 'Face Recognition'];
                                    $verifyLabel = $verifyLabels[$item['verifymode']] ?? 'Unknown';
                                    $verifyClass = ['1' => 'primary', '3' => 'info', '20' => 'success'][$item['verifymode']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $verifyClass ?>"><?= $verifyLabel ?></span>
                                </td>
                                <td>
                                    <?php
                                    $inoutLabels = [0 => 'Check In', 1 => 'Check In', 2 => 'Check Out', 3 => 'Break Out', 4 => 'Break In'];
                                    $inoutLabel = $inoutLabels[$item['inoutmode']] ?? 'Unknown';
                                    $inoutClass = ['0' => 'success', '1' => 'success', '2' => 'warning', '3' => 'info', '4' => 'info'][$item['inoutmode']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $inoutClass ?>"><?= $inoutLabel ?></span>
                                </td>
                                <td>
                                    <a href="<?= base_url('attendance-logs/view/' . $encodedKey) ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">No attendance logs found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if (isset($pager)): ?>
                <div class="d-flex justify-content-center">
                    <?= $pager->links() ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `<?= base_url('attendancelogs/delete/') ?>/${id}`;
        }
    });
}
</script>
<?= $this->endSection() ?>