<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= $title ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-link text-primary"></i>
            <?= $title ?>
        </h1>
        <div>
            <a href="<?= base_url('fingerprint-bridge') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <button type="button" class="btn btn-success" id="autoMapBtn">
                <i class="fas fa-magic"></i> Auto Map
            </button>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addMappingModal">
                <i class="fas fa-plus"></i> Add Mapping
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Mappings
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['total_mappings']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-link fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Active Mappings
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['active_mappings']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Unmapped PINs
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['unmapped_pins']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Students Without PIN
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format($stats['students_without_pin']) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PIN Mappings Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">PIN Mappings</h6>
        </div>
        <div class="card-body">
            <?php if (!empty($mappings)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="mappingsTable">
                        <thead>
                            <tr>
                                <th>PIN</th>
                                <th>Student</th>
                                <th>Student Number</th>
                                <th>RFID Card</th>
                                <th>Status</th>
                                <th>Notes</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mappings as $mapping): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($mapping['pin']) ?></strong>
                                    </td>
                                    <td>
                                        <?php if (!empty($mapping['firstname'])): ?>
                                            <?= htmlspecialchars($mapping['firstname'] . ' ' . $mapping['lastname']) ?>
                                            <br>
                                            <small class="text-muted"><?= htmlspecialchars($mapping['email']) ?></small>
                                        <?php else: ?>
                                            <span class="text-danger">Student not found</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($mapping['student_number']) ?></td>
                                    <td><?= htmlspecialchars($mapping['rfid_card'] ?? '-') ?></td>
                                    <td>
                                        <?php if ($mapping['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small><?= htmlspecialchars($mapping['notes'] ?? '-') ?></small>
                                    </td>
                                    <td>
                                        <small><?= date('M j, Y', strtotime($mapping['created_at'])) ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <?php if ($mapping['is_active']): ?>
                                                <button class="btn btn-sm btn-warning" onclick="toggleMapping(<?= $mapping['id'] ?>, 0)">
                                                    <i class="fas fa-pause"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-success" onclick="toggleMapping(<?= $mapping['id'] ?>, 1)">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-info" onclick="editMapping(<?= $mapping['id'] ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteMapping(<?= $mapping['id'] ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-link fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-muted">No PIN mappings found</h5>
                    <p class="text-muted">Create your first PIN mapping to link fingerprint PINs with students.</p>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addMappingModal">
                        <i class="fas fa-plus"></i> Add First Mapping
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Unmapped PINs -->
    <?php if (!empty($unmapped_pins)): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-warning">Unmapped PINs</h6>
            </div>
            <div class="card-body">
                <p class="text-muted">These PINs exist in attendance logs but are not mapped to any student:</p>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>PIN</th>
                                <th>Usage Count</th>
                                <th>Last Used</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($unmapped_pins as $pin): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($pin['pin']) ?></strong></td>
                                    <td><?= number_format($pin['usage_count']) ?></td>
                                    <td><?= date('M j, Y H:i', strtotime($pin['last_used'])) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick="mapPin('<?= htmlspecialchars($pin['pin']) ?>')">
                                            <i class="fas fa-plus"></i> Map to Student
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Students Without PIN -->
    <?php if (!empty($students_without_pin)): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info">Students Without PIN</h6>
            </div>
            <div class="card-body">
                <p class="text-muted">These students don't have fingerprint PIN mappings:</p>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Student Number</th>
                                <th>RFID</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students_without_pin as $student): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($student['firstname'] . ' ' . $student['lastname']) ?>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($student['email']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($student['student_id']) ?></td>
                                    <td><?= htmlspecialchars($student['rfid'] ?? '-') ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick="assignPin(<?= $student['id'] ?>)">
                                            <i class="fas fa-plus"></i> Assign PIN
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Add Mapping Modal -->
<div class="modal fade" id="addMappingModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add PIN Mapping</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addMappingForm">
                    <div class="form-group">
                        <label for="pin">PIN <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="pin" name="pin" required>
                    </div>
                    <div class="form-group">
                        <label for="student_id">Student <span class="text-danger">*</span></label>
                        <select class="form-control" id="student_id" name="student_id" required>
                            <option value="">Select Student</option>
                            <!-- Students will be loaded via AJAX -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="rfid_card">RFID Card</label>
                        <input type="text" class="form-control" id="rfid_card" name="rfid_card">
                    </div>
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveMappingForm()">Save Mapping</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#mappingsTable').DataTable({
        "pageLength": 25,
        "order": [[6, "desc"]], // Sort by created date
        "columnDefs": [
            { "orderable": false, "targets": [7] } // Disable sorting on actions column
        ]
    });
    
    // Load students for dropdown
    loadStudents();
});

function loadStudents() {
    $.ajax({
        url: '<?= base_url('api/fingerprint-bridge/students') ?>',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const select = $('#student_id');
                select.empty().append('<option value="">Select Student</option>');
                
                response.data.forEach(function(student) {
                    select.append(`<option value="${student.student_id}">${student.firstname} ${student.lastname} (${student.student_id})</option>`);
                });
            }
        }
    });
}

function autoMap() {
    const btn = $('#autoMapBtn');
    const originalText = btn.html();
    
    btn.html('<i class="fas fa-spinner fa-spin"></i> Mapping...').prop('disabled', true);
    
    $.ajax({
        url: '<?= base_url('fingerprint-bridge/ajax/auto-map') ?>',
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert(`Auto-mapping completed! ${response.mapped_count} PINs were mapped.`);
                location.reload();
            } else {
                alert('Auto-mapping failed: ' + response.message);
            }
        },
        error: function() {
            alert('Auto-mapping failed. Please try again.');
        },
        complete: function() {
            btn.html(originalText).prop('disabled', false);
        }
    });
}

function saveMappingForm() {
    const formData = {
        pin: $('#pin').val(),
        student_id: $('#student_id').val(),
        rfid_card: $('#rfid_card').val(),
        notes: $('#notes').val()
    };
    
    if (!formData.pin || !formData.student_id) {
        alert('Please fill in all required fields.');
        return;
    }
    
    $.ajax({
        url: '<?= base_url('fingerprint-bridge/ajax/save-mapping') ?>',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('Mapping saved successfully!');
                $('#addMappingModal').modal('hide');
                location.reload();
            } else {
                alert('Failed to save mapping: ' + response.message);
            }
        },
        error: function() {
            alert('Failed to save mapping. Please try again.');
        }
    });
}

function toggleMapping(id, status) {
    const action = status ? 'activate' : 'deactivate';
    
    if (confirm(`Are you sure you want to ${action} this mapping?`)) {
        $.ajax({
            url: '<?= base_url('fingerprint-bridge/ajax/toggle-mapping') ?>',
            type: 'POST',
            data: { id: id, status: status },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Failed to update mapping: ' + response.message);
                }
            }
        });
    }
}

function deleteMapping(id) {
    if (confirm('Are you sure you want to delete this mapping? This action cannot be undone.')) {
        $.ajax({
            url: '<?= base_url('fingerprint-bridge/ajax/delete-mapping') ?>',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Failed to delete mapping: ' + response.message);
                }
            }
        });
    }
}

function mapPin(pin) {
    $('#pin').val(pin);
    $('#addMappingModal').modal('show');
}

function assignPin(studentId) {
    $('#student_id').val(studentId);
    $('#addMappingModal').modal('show');
}

// Auto map button click handler
$('#autoMapBtn').click(function() {
    autoMap();
});
</script>
<?= $this->endSection() ?>
