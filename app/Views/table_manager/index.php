<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Table Manager<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Table Manager</h4>
        <div>
            <a href="<?= base_url('table-manager/create') ?>" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Create New Table
            </a>
            <button class="btn btn-success" onclick="generateCrudl()">
                <i class="fas fa-code me-2"></i>Generate CRUDL
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover datatable">
                    <thead>
                        <tr>
                            <th>Table Name</th>
                            <th>Columns</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tables as $table): ?>
                        <tr>
                            <td><?= $table['name'] ?></td>
                            <td>
                                <?php foreach ($table['columns'] as $column): ?>
                                    <span class="badge bg-info me-1"><?= $column->name ?></span>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="viewTable('<?= $table['name'] ?>')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <a href="<?= base_url('table-manager/edit/' . $table['name']) ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-danger" onclick="confirmDelete('<?= $table['name'] ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Generate CRUDL Modal -->
<div class="modal fade" id="generateCrudlModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate CRUDL</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="generateCrudlForm">
                    <div class="mb-3">
                        <label class="form-label">Select Table</label>
                        <select class="form-select select2" name="table_name" required>
                            <option value="">Select a table...</option>
                            <?php foreach ($tables as $table): ?>
                                <option value="<?= $table['name'] ?>"><?= $table['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Module Name</label>
                        <input type="text" class="form-control" name="module_name" required 
                               placeholder="Enter module name (e.g., student)">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="submitGenerateCrudl()">Generate</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function generateCrudl() {
    $('#generateCrudlModal').modal('show');
}

function submitGenerateCrudl() {
    const form = $('#generateCrudlForm');
    const formData = new FormData(form[0]);
    
    $.ajax({
        url: '<?= base_url('table-manager/generate-crudl') ?>',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                showToast('CRUDL generated successfully!', 'success');
                $('#generateCrudlModal').modal('hide');
            } else {
                showToast(response.message || 'Failed to generate CRUDL', 'error');
            }
        },
        error: function() {
            showToast('An error occurred while generating CRUDL', 'error');
        }
    });
}

function viewTable(tableName) {
    // Implement table structure view
    showToast('Viewing table: ' + tableName);
}

function confirmDelete(tableName) {
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
            window.location.href = `<?= base_url('table-manager/delete/') ?>/${tableName}`;
        }
    });
}

// Initialize Select2
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#generateCrudlModal')
    });
});
</script>
<?= $this->endSection() ?> 