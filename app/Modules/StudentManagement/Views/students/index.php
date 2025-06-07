<?php $this->extend('layouts/main'); ?>

<?php $this->section('title'); ?>
<?= $title ?? 'Students Management' ?>
<?php $this->endSection(); ?>

<?php $this->section('content'); ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0"><?= $title ?? 'Students Management' ?></h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Students</li>
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
                    <a href="<?= base_url('students/create') ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Student
                    </a>
                    <button type="button" class="btn btn-danger" id="bulkDeleteBtn" style="display: none;">
                        <i class="fas fa-trash"></i> Delete Selected
                    </button>
                </div>
                <div>
                    <a href="<?= base_url('students/export') ?>" class="btn btn-success">
                        <i class="fas fa-download"></i> Export CSV
                    </a>
                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="fas fa-upload"></i> Import CSV
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="<?= base_url('students') ?>">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?= $filters['search'] ?? '' ?>" 
                                       placeholder="Name, Student ID, or Email">
                            </div>
                            <div class="col-md-2">
                                <label for="class_id" class="form-label">Class</label>
                                <select class="form-select" id="class_id" name="class_id">
                                    <option value="">All Classes</option>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?= $class['id'] ?>" 
                                                <?= ($filters['class_id'] ?? '') == $class['id'] ? 'selected' : '' ?>>
                                            <?= esc($class['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="section_id" class="form-label">Section</label>
                                <select class="form-select" id="section_id" name="section_id">
                                    <option value="">All Sections</option>
                                    <?php foreach ($sections as $section): ?>
                                        <option value="<?= $section['id'] ?>" 
                                                <?= ($filters['section_id'] ?? '') == $section['id'] ? 'selected' : '' ?>>
                                            <?= esc($section['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="session_id" class="form-label">Session</label>
                                <select class="form-select" id="session_id" name="session_id">
                                    <option value="">All Sessions</option>
                                    <?php foreach ($sessions as $session): ?>
                                        <option value="<?= $session['id'] ?>" 
                                                <?= ($filters['session_id'] ?? '') == $session['id'] ? 'selected' : '' ?>>
                                            <?= esc($session['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Students Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= session()->getFlashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= session()->getFlashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                    <th>Photo</th>
                                    <th>Student ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Class</th>
                                    <th>Section</th>
                                    <th>RFID</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($students)): ?>
                                    <tr>
                                        <td colspan="11" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-users fa-3x mb-3"></i>
                                                <p>No students found</p>
                                                <a href="<?= base_url('students/create') ?>" class="btn btn-primary">
                                                    Add First Student
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($students as $student): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="form-check-input student-checkbox" 
                                                       value="<?= $student['id'] ?>">
                                            </td>
                                            <td>
                                                <?php if (!empty($student['photo'])): ?>
                                                    <img src="<?= base_url('uploads/students/' . $student['photo']) ?>" 
                                                         alt="<?= esc($student['name']) ?>" 
                                                         class="rounded-circle" width="40" height="40">
                                                <?php else: ?>
                                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                                         style="width: 40px; height: 40px; color: white;">
                                                        <?= strtoupper(substr($student['name'], 0, 1)) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= esc($student['student_id']) ?></td>
                                            <td><?= esc($student['name']) ?></td>
                                            <td><?= esc($student['email']) ?></td>
                                            <td><?= esc($student['phone']) ?></td>
                                            <td><?= esc($student['class_name'] ?? '-') ?></td>
                                            <td><?= esc($student['section_name'] ?? '-') ?></td>
                                            <td>
                                                <?php if (!empty($student['rfid_card'])): ?>
                                                    <span class="badge bg-success"><?= esc($student['rfid_card']) ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Not Set</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $statusClass = match($student['status']) {
                                                    'Active' => 'bg-success',
                                                    'Inactive' => 'bg-warning',
                                                    'Graduated' => 'bg-info',
                                                    'Transferred' => 'bg-secondary',
                                                    default => 'bg-secondary'
                                                };
                                                ?>
                                                <span class="badge <?= $statusClass ?>">
                                                    <?= esc($student['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?= base_url('students/show/' . $student['id']) ?>" 
                                                       class="btn btn-sm btn-outline-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="<?= base_url('students/edit/' . $student['id']) ?>" 
                                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-btn" 
                                                            data-id="<?= $student['id'] ?>" 
                                                            data-name="<?= esc($student['name']) ?>" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if (isset($pager)): ?>
                        <div class="d-flex justify-content-center mt-3">
                            <?= $pager->links() ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete student <strong id="studentName"></strong>?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
<script>
$(document).ready(function() {
    // Handle select all checkbox
    $('#selectAll').change(function() {
        $('.student-checkbox').prop('checked', this.checked);
        toggleBulkDeleteButton();
    });
    
    // Handle individual checkboxes
    $('.student-checkbox').change(function() {
        toggleBulkDeleteButton();
        
        // Update select all checkbox
        const totalCheckboxes = $('.student-checkbox').length;
        const checkedCheckboxes = $('.student-checkbox:checked').length;
        $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
    });
    
    // Toggle bulk delete button visibility
    function toggleBulkDeleteButton() {
        const checkedCount = $('.student-checkbox:checked').length;
        if (checkedCount > 0) {
            $('#bulkDeleteBtn').show();
        } else {
            $('#bulkDeleteBtn').hide();
        }
    }
    
    // Handle delete button click
    $('.delete-btn').click(function() {
        const studentId = $(this).data('id');
        const studentName = $(this).data('name');
        
        $('#studentName').text(studentName);
        $('#confirmDelete').data('id', studentId);
        $('#deleteModal').modal('show');
    });
    
    // Handle delete confirmation
    $('#confirmDelete').click(function() {
        const studentId = $(this).data('id');
        
        $.ajax({
            url: '<?= base_url('students/delete') ?>/' + studentId,
            type: 'DELETE',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while deleting the student.');
            }
        });
        
        $('#deleteModal').modal('hide');
    });
    
    // Handle bulk delete
    $('#bulkDeleteBtn').click(function() {
        const selectedIds = $('.student-checkbox:checked').map(function() {
            return this.value;
        }).get();
        
        if (selectedIds.length === 0) {
            alert('Please select students to delete.');
            return;
        }
        
        if (confirm('Are you sure you want to delete ' + selectedIds.length + ' selected students?')) {
            $.ajax({
                url: '<?= base_url('students/bulk-delete') ?>',
                type: 'POST',
                data: { student_ids: selectedIds },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('An error occurred while deleting students.');
                }
            });
        }
    });
});
</script>
<?php $this->endSection(); ?>