<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow mb-4 mt-5">
                <div class="card-header py-3">
                    <h4 class="m-0 font-weight-bold text-primary text-center"><?= esc($subtitle ?? 'Wajib Ganti Password (Petugas)') ?></h4>
                </div>
                <div class="card-body">
                    <p class="text-center text-warning">Untuk keamanan akun Anda, silakan buat password baru.</p>

                    <?php if (session()->getFlashdata('message')): ?>
                        <?= session()->getFlashdata('message') ?>
                    <?php endif; ?>

                    <?php if (isset($validation) && $validation->getErrors()): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= $validation->listErrors() ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="<?= site_url('petugas/force_change_password_page') ?>">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <label for="new_password">Password Baru <span class="text-danger">*</span></label>
                            <input type="password" class="form-control <?= (isset($validation) && $validation->hasError('new_password')) ? 'is-invalid' : '' ?>" 
                                   id="new_password" name="new_password" 
                                   placeholder="Masukkan password baru Anda" required>
                            <?php if (isset($validation) && $validation->hasError('new_password')): ?>
                                <div class="invalid-feedback"><?= $validation->getError('new_password') ?></div>
                            <?php endif; ?>
                            <small class="form-text text-muted">Minimal 6 karakter.</small>
                        </div>
                        <div class="form-group">
                            <label for="confirm_new_password">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                            <input type="password" class="form-control <?= (isset($validation) && $validation->hasError('confirm_new_password')) ? 'is-invalid' : '' ?>" 
                                   id="confirm_new_password" name="confirm_new_password" 
                                   placeholder="Ulangi password baru Anda" required>
                            <?php if (isset($validation) && $validation->hasError('confirm_new_password')): ?>
                                <div class="invalid-feedback"><?= $validation->getError('confirm_new_password') ?></div>
                            <?php endif; ?>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Simpan Password Baru</button>
                    </form>
                    <hr>
                    <div class="text-center">
                        <a class="small" href="<?= site_url('auth/logout') ?>">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
