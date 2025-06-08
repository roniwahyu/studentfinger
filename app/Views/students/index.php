<?= $this->extend('layouts/main') ?>

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
                    <form id="filterForm" method="GET">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       placeholder="Search by name, student ID, or RFID" 
                                       value="<?= esc($search ?? '') ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="class_id" class="form-label">Class</label>
                                <select class="form-select" id="class_id" name="class_id">
                                    <option value="">All Classes</option>
                                    <?php if (isset($classes)): ?>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?= $class['id'] ?>" 
                                                    <?= ($class_id ?? '') == $class['id'] ? 'selected' : '' ?>>
                                                <?= esc($class['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="section_id" class="form-label">Section</label>
                                <select class="form-select" id="section_id" name="section_id">
                                    <option value="">All Sections</option>
                                    <?php if (isset($sections)): ?>
                                        <?php foreach ($sections as $section): ?>
                                            <option value="<?= $section['id'] ?>" 
                                                    <?= ($section_id ?? '') == $section['id'] ? 'selected' : '' ?>>
                                                <?= esc($section['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="session_id" class="form-label">Session</label>
                                <select class="form-select" id="session_id" name="session_id">
                                    <option value="">All Sessions</option>
                                    <?php if (isset($sessions)): ?>
                                        <?php foreach ($sessions as $session): ?>
                                            <option value="<?= $session['id'] ?>" 
                                                    <?= ($session_id ?? '') == $session['id'] ? 'selected' : '' ?>>
                                                <?= esc($session['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Status</option>
                                    <option value="active" <?= ($status ?? '') == 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= ($status ?? '') == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
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
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="studentsTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                    <th>Photo</th>
                                    <th>Student ID</th>
                                    <th>Name</th>
                                    <th>Class</th>
                                    <th>Section</th>
                                    <th>Session</th>
                                    <th>RFID</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($students) && !empty($students)): ?>
                                    <?php foreach ($students as $student): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="form-check-input student-checkbox" 
                                                       value="<?= $student['id'] ?>">
                                            </td>
                                            <td>
                                                <?php if (!empty($student['photo'])): ?>
                                                    <img src="<?= base_url('uploads/students/' . $student['photo']) ?>" 
                                                         alt="Student Photo" class="rounded-circle" width="40" height="40">
                                                <?php else: ?>
                                                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" 
                                                         style="width: 40px; height: 40px;">
                                                        <i class="fas fa-user text-white"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= esc($student['student_id']) ?></td>
                                            <td><?= esc($student['first_name'] . ' ' . $student['last_name']) ?></td>
                                            <td><?= esc($student['class_name'] ?? 'N/A') ?></td>
                                            <td><?= esc($student['section_name'] ?? 'N/A') ?></td>
                                            <td><?= esc($student['session_name'] ?? 'N/A') ?></td>
                                            <td>
                                                <?php if (!empty($student['rfid_card'])): ?>
                                                    <span class="badge bg-success"><?= esc($student['rfid_card']) ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Not Assigned</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($student['status'] == 'active'): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?= base_url('students/show/' . $student['id']) ?>" 
                                                       class="btn btn-sm btn-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="<?= base_url('students/edit/' . $student['id']) ?>" 
                                                       class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger delete-student" 
                                                            data-id="<?= $student['id'] ?>" 
                                                            data-name="<?= esc($student['first_name'] . ' ' . $student['last_name']) ?>" 
                                                            title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-users fa-3x mb-3"></i>
                                                <p class="mb-0">No students found</p>
                                            </div>
                                        </td>
                                    </tr>
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

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Students</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="importForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="csvFile" class="form-label">Select CSV File</label>
                        <input type="file" class="form-control" id="csvFile" name="csv_file" accept=".csv" required>
                        <div class="form-text">
                            Please ensure your CSV file has the following columns: 
                            student_id, first_name, last_name, email, phone, class_id, section_id, session_id
                        </div>
                    </div>
                    <div class="mb-3">
                        <a href="<?= base_url('students/download-template') ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-download"></i> Download Template
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="studentName"></strong>?</p>
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
    // Select all checkbox functionality
    $('#selectAll').change(function() {
        $('.student-checkbox').prop('checked', this.checked);
        toggleBulkDeleteButton();
    });

    // Individual checkbox change
    $('.student-checkbox').change(function() {
        toggleBulkDeleteButton();
        
        // Update select all checkbox
        const totalCheckboxes = $('.student-checkbox').length;
        const checkedCheckboxes = $('.student-checkbox:checked').length;
        $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
    });

    // Toggle bulk delete button
    function toggleBulkDeleteButton() {
        const checkedCount = $('.student-checkbox:checked').length;
        if (checkedCount > 0) {
            $('#bulkDeleteBtn').show();
        } else {
            $('#bulkDeleteBtn').hide();
        }
    }

    // Delete single student
    $('.delete-student').click(function() {
        const studentId = $(this).data('id');
        const studentName = $(this).data('name');
        
        $('#studentName').text(studentName);
        $('#confirmDelete').data('id', studentId);
        $('#deleteModal').modal('show');
    });

    // Confirm delete
    $('#confirmDelete').click(function() {
        const studentId = $(this).data('id');
        
        $.ajax({
            url: '<?= base_url('students/delete') ?>/' + studentId,
            type: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
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

    // Bulk delete
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
                data: {
                    ids: selectedIds
                },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('An error occurred while deleting the students.');
                }
            });
        }
    });

    // Import form submission
    $('#importForm').submit(function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        $.ajax({
            url: '<?= base_url('students/import') ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    alert('Students imported successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while importing students.');
            }
        });
        
        $('#importModal').modal('hide');
    });

    // Class change event to load sections
    $('#class_id').change(function() {
        const classId = $(this).val();
        
        if (classId) {
            $.ajax({
                url: '<?= base_url('api/sections-by-class') ?>/' + classId,
                type: 'GET',
                success: function(response) {
                    $('#section_id').empty().append('<option value="">All Sections</option>');
                    
                    if (response.success && response.data) {
                        response.data.forEach(function(section) {
                            $('#section_id').append(
                                '<option value="' + section.id + '">' + section.name + '</option>'
                            );
                        });
                    }
                }
            });
        } else {
            $('#section_id').empty().append('<option value="">All Sections</option>');
        }
    });
});
</script>
<?php $this->endSection(); ?>