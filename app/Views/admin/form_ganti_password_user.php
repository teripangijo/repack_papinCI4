<?= $this->extend('layouts/main') ?> // Menggunakan layout 'main.php'
<?= $this->section('title') ?>
    <?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Ganti Password'; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= htmlspecialchars($subtitle ?? 'Ganti Password User'); ?></h1>

    <?php
    // Display validation errors for CodeIgniter 4
    $validation = \Config\Services::validation();
    if ($validation->getErrors()) : ?>
        <div class="alert alert-danger" role="alert">
            <?php foreach ($validation->getErrors() as $error) : ?>
                <p><?= $error ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('message')) { echo session()->getFlashdata('message'); } ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Ganti Password untuk User: <?= htmlspecialchars($target_user['name'] ?? 'Tidak Ditemukan'); ?> (<?= htmlspecialchars($target_user['email'] ?? ''); ?>)
            </h6>
        </div>
        <div class="card-body">
            <?php if (isset($target_user) && $target_user) : ?>
            <form action="<?= site_url('admin/ganti_password_user/' . $target_user['id']); ?>" method="post">
                <?= csrf_field(); // CSRF token for CI4 ?>
                <div class="form-group">
                    <label for="new_password">Password Baru <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                    <?php if ($validation->hasError('new_password')) : ?>
                        <small class="text-danger pl-3"><?= $validation->getError('new_password'); ?></small>
                    <?php endif; ?>
                    <small class="form-text text-muted">Minimal 6 karakter.</small>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    <?php if ($validation->hasError('confirm_password')) : ?>
                        <small class="text-danger pl-3"><?= $validation->getError('confirm_password'); ?></small>
                    <?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary">Simpan Password Baru</button>
                <a href="<?= site_url('admin/manajemen_user'); ?>" class="btn btn-secondary">Batal</a>
            </form>
            <?php else: ?>
                <p class="text-danger">Data target user tidak ditemukan.</p>
                <a href="<?= site_url('admin/manajemen_user'); ?>" class="btn btn-secondary">Kembali ke Manajemen User</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>