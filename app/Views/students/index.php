<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Students Management<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Students Management</h4>
        <a href="<?= base_url('students/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Create New
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Gender</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($students)): ?>
                            <?php foreach ($students as $item): ?>
                            <tr>
                                <td><?= $item['id'] ?? 'N/A' ?></td>
                                <td><?= esc($item['student_id'] ?? 'N/A') ?></td>
                                <td><?= esc($item['name'] ?? 'N/A') ?></td>
                                <td><?= esc($item['email'] ?? 'N/A') ?></td>
                                <td><?= esc($item['phone'] ?? 'N/A') ?></td>
                                <td><?= esc($item['gender'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge <?= ($item['status'] ?? '') === 'Active' ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= esc($item['status'] ?? 'Unknown') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (isset($item['id'])): ?>
                                        <a href="<?= base_url('students/show/' . $item['id']) ?>" class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= base_url('students/edit/' . $item['id']) ?>" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?= $item['id'] ?>)" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">No actions available</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-users fa-3x mb-3 text-gray-300"></i>
                                    <p class="mb-2">No students found</p>
                                    <a href="<?= base_url('students/create') ?>" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Add First Student
                                    </a>
                                </td>
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
            window.location.href = `<?= base_url('students/delete/') ?>/${id}`;
        }
    });
}
</script>
<?= $this->endSection() ?>