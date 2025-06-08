<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>User Logs<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">User Logs</h4>
        <a href="<?= base_url('user-logs/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Log
        </a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= base_url('user-logs') ?>">
                <div class="row">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search"
                               value="<?= esc($filters['search']) ?>" placeholder="Login ID, Data Name, Note...">
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
                        <label for="module" class="form-label">Module</label>
                        <select class="form-control" id="module" name="module">
                            <option value="">All</option>
                            <option value="0" <?= $filters['module'] == '0' ? 'selected' : '' ?>>Settings</option>
                            <option value="1" <?= $filters['module'] == '1' ? 'selected' : '' ?>>Employee</option>
                            <option value="2" <?= $filters['module'] == '2' ? 'selected' : '' ?>>Machine</option>
                            <option value="3" <?= $filters['module'] == '3' ? 'selected' : '' ?>>Exception</option>
                            <option value="4" <?= $filters['module'] == '4' ? 'selected' : '' ?>>Report</option>
                            <option value="5" <?= $filters['module'] == '5' ? 'selected' : '' ?>>Process</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="tipe_log" class="form-label">Log Type</label>
                        <select class="form-control" id="tipe_log" name="tipe_log">
                            <option value="">All</option>
                            <option value="0" <?= $filters['tipe_log'] == '0' ? 'selected' : '' ?>>Add</option>
                            <option value="1" <?= $filters['tipe_log'] == '1' ? 'selected' : '' ?>>Edit</option>
                            <option value="2" <?= $filters['tipe_log'] == '2' ? 'selected' : '' ?>>Delete</option>
                            <option value="3" <?= $filters['tipe_log'] == '3' ? 'selected' : '' ?>>Open Door</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="<?= base_url('user-logs') ?>" class="btn btn-secondary">
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
                            <th>Login ID</th>
                            <th>Module</th>
                            <th>Log Type</th>
                            <th>Data Name</th>
                            <th>Note</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($userlogs)): ?>
                            <?php foreach ($userlogs as $item): ?>
                            <?php
                                $encodedKey = base64_encode($item['login_id'] . '|' . $item['log_date']);

                                // Module labels
                                $moduleLabels = [0 => 'Settings', 1 => 'Employee', 2 => 'Machine', 3 => 'Exception', 4 => 'Report', 5 => 'Process'];
                                $moduleLabel = $moduleLabels[$item['module']] ?? 'Unknown';
                                $moduleClass = ['0' => 'secondary', '1' => 'primary', '2' => 'info', '3' => 'warning', '4' => 'success', '5' => 'dark'][$item['module']] ?? 'secondary';

                                // Log type labels
                                $logTypeLabels = [0 => 'Add', 1 => 'Edit', 2 => 'Delete', 3 => 'Open Door'];
                                $logTypeLabel = $logTypeLabels[$item['tipe_log']] ?? 'Unknown';
                                $logTypeClass = ['0' => 'success', '1' => 'warning', '2' => 'danger', '3' => 'info'][$item['tipe_log']] ?? 'secondary';
                            ?>
                            <tr>
                                <td><?= date('Y-m-d H:i:s', strtotime($item['log_date'])) ?></td>
                                <td><?= esc($item['login_id']) ?></td>
                                <td><span class="badge bg-<?= $moduleClass ?>"><?= $moduleLabel ?></span></td>
                                <td><span class="badge bg-<?= $logTypeClass ?>"><?= $logTypeLabel ?></span></td>
                                <td><?= esc($item['nama_data']) ?></td>
                                <td>
                                    <span title="<?= esc($item['log_note']) ?>">
                                        <?= esc(strlen($item['log_note']) > 50 ? substr($item['log_note'], 0, 50) . '...' : $item['log_note']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= base_url('user-logs/view/' . $encodedKey) ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= base_url('user-logs/edit/' . $encodedKey) ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger" onclick="confirmDelete('<?= $encodedKey ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">No user logs found</td>
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
function confirmDelete(encodedKey) {
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
            window.location.href = `<?= base_url('user-logs/delete/') ?>/${encodedKey}`;
        }
    });
}
</script>
<?= $this->endSection() ?>