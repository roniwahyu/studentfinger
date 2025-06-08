<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Edit UserLog<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Edit UserLog</h4>
        <a href="<?= base_url('userlogs') ?>" class="btn btn-secondary">
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

            <form action="<?= base_url('userlogs/edit/' . $UserLog['id']) ?>" method="post">
                <?= csrf_field() ?>
                
                <!-- Add your form fields here -->

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>