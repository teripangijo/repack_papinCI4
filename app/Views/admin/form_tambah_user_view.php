<?= $this->extend('layouts/main') ?> // Menggunakan layout 'main.php'
<?= $this->section('title') ?>
    <?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Tambah User'; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($subtitle ?? 'Tambah User Baru'); ?></h1>
        <a href="<?= site_url('admin/manajemen_user'); ?>" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Manajemen User
        </a>
    </div>

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
            <h6 class="m-0 font-weight-bold text-primary">Formulir Tambah User untuk Role: <?= htmlspecialchars($target_role_info['role'] ?? 'Tidak Diketahui'); ?></h6>
        </div>
        <div class="card-body">
            <form action="<?= site_url('admin/tambah_user/' . $role_id_to_add); ?>" method="post">
                <?= csrf_field(); // CSRF token for CI4 ?>
                <input type="hidden" name="role_id_hidden" value="<?= htmlspecialchars($role_id_to_add); ?>">

                <div class="form-group">
                    <label for="name">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?= $validation->hasError('name') ? 'is-invalid' : ''; ?>" id="name" name="name" value="<?= old('name'); ?>" required>
                    <?php if ($validation->hasError('name')) : ?>
                        <small class="text-danger pl-3"><?= $validation->getError('name'); ?></small>
                    <?php endif; ?>
                </div>

                <?php
                $login_identifier_label_view = 'Login Identifier';
                $login_identifier_placeholder = 'Masukkan Email atau NIP';
                $login_identifier_help_text = 'Digunakan untuk login.';
                if ($role_id_to_add == 2) {
                    $login_identifier_label_view = 'Email';
                    $login_identifier_placeholder = 'Contoh: user@example.com';
                } elseif ($role_id_to_add == 3) {
                    $login_identifier_label_view = 'NIP (Nomor Induk Pegawai)';
                    $login_identifier_placeholder = 'Masukkan NIP Petugas';
                    $login_identifier_help_text = 'NIP akan digunakan untuk login.';
                } elseif ($role_id_to_add == 4) {
                    $login_identifier_label_view = 'NIP (Nomor Induk Pegawai)';
                    $login_identifier_placeholder = 'Masukkan NIP Monitoring';
                    $login_identifier_help_text = 'NIP akan digunakan untuk login.';
                }
                ?>
                <div class="form-group">
                    <label for="login_identifier"><?= htmlspecialchars($login_identifier_label_view); ?> <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?= $validation->hasError('login_identifier') ? 'is-invalid' : ''; ?>" id="login_identifier" name="login_identifier" placeholder="<?= htmlspecialchars($login_identifier_placeholder); ?>" value="<?= old('login_identifier'); ?>" required>
                    <small class="form-text text-muted"><?= htmlspecialchars($login_identifier_help_text); ?></small>
                    <?php if ($validation->hasError('login_identifier')) : ?>
                        <small class="text-danger pl-3"><?= $validation->getError('login_identifier'); ?></small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password">Password Awal <span class="text-danger">*</span></label>
                    <input type="password" class="form-control <?= $validation->hasError('password') ? 'is-invalid' : ''; ?>" id="password" name="password" required>
                    <small class="form-text text-muted">Minimal 6 karakter. User akan diminta mengganti password ini saat login pertama.</small>
                    <?php if ($validation->hasError('password')) : ?>
                        <small class="text-danger pl-3"><?= $validation->getError('password'); ?></small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password Awal <span class="text-danger">*</span></label>
                    <input type="password" class="form-control <?= $validation->hasError('confirm_password') ? 'is-invalid' : ''; ?>" id="confirm_password" name="confirm_password" required>
                    <?php if ($validation->hasError('confirm_password')) : ?>
                        <small class="text-danger pl-3"><?= $validation->getError('confirm_password'); ?></small>
                    <?php endif; ?>
                </div>

                <?php if ($role_id_to_add == 3) : ?>
                <hr>
                <h6 class="text-muted">Data Detail Spesifik untuk Role Petugas</h6>
                <div class="form-group">
                    <label for="jabatan_petugas">Jabatan Petugas <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?= $validation->hasError('jabatan_petugas') ? 'is-invalid' : ''; ?>" id="jabatan_petugas" name="jabatan_petugas" value="<?= old('jabatan_petugas'); ?>" required>
                    <?php if ($validation->hasError('jabatan_petugas')) : ?>
                        <small class="text-danger pl-3"><?= $validation->getError('jabatan_petugas'); ?></small>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-primary">Simpan User</button>
                <a href="<?= site_url('admin/manajemen_user'); ?>" class="btn btn-secondary ml-2">Batal</a>
            </form>
        </div>
    </div>
</div>
