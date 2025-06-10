<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Create Class<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Create Class</h4>
        <a href="<?= base_url('classes') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (isset($validation)): ?>
                <div class="alert alert-danger">
                    <?= $validation->listErrors() ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('classes/create') ?>" method="post">
                <?= csrf_field() ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="class" class="form-label">Class Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="class" name="class"
                                   value="<?= old('class') ?>" placeholder="Enter class name (e.g., X, XI, XII)" required>
                            <div class="form-text">Enter the class name or grade level</div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Class
                    </button>
                    <a href="<?= base_url('classes') ?>" class="btn btn-secondary ms-2">
                        <i class="fas fa-times me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>