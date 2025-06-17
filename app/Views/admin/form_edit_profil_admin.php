<?= $this->extend('layouts/main') ?> // Menggunakan layout 'main.php'
<?= $this->section('title') ?>
    <?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Edit Profil'; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= $subtitle; ?></h1>

    <div class="row">
        <div class="col-lg-8">
            <form action="<?= base_url('admin/edit_profil'); ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field(); // CSRF token for CI4 ?>
            <div class="form-group row">
                <label for="login_identifier" class="col-sm-3 col-form-label">Email (Login)</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="login_identifier" name="login_identifier" value="<?= old('login_identifier', $user['email']); ?>">
                    <?php if (\Config\Services::validation()->hasError('login_identifier')) : ?>
                        <small class="text-danger pl-3"><?= \Config\Services::validation()->getError('login_identifier'); ?></small>
                    <?php endif; ?>
                </div>
            </div>
            <div class="form-group row">
                <label for="name" class="col-sm-3 col-form-label">Nama Lengkap</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="name" name="name" value="<?= old('name', $user['name']); ?>">
                    <?php if (\Config\Services::validation()->hasError('name')) : ?>
                        <small class="text-danger pl-3"><?= \Config\Services::validation()->getError('name'); ?></small>
                    <?php endif; ?>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-3">Foto Profil</div>
                <div class="col-sm-9">
                    <div class="row">
                        <div class="col-sm-3">
                            <img src="<?= base_url('uploads/profile_images/') . $user['image']; ?>" class="img-thumbnail" alt="Profile Image">
                        </div>
                        <div class="col-sm-9">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="profile_image" name="profile_image" accept="image/jpeg,image/png,image/gif">
                                <label class="custom-file-label" for="profile_image">Pilih file...</label>
                                <small class="form-text text-muted">Format: JPG, PNG, JPEG, GIF. Max: 2MB, 1024x1024px.</small>
                            </div>
                            <?php if (\Config\Services::validation()->hasError('profile_image')) : ?>
                                <small class="text-danger pl-3"><?= \Config\Services::validation()->getError('profile_image'); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group row justify-content-end">
                <div class="col-sm-9">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </div>
            </form>

            <hr>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label">Keamanan Akun</label>
                <div class="col-sm-9">
                     <p class="form-text text-muted">Amankan akun Anda dengan lapisan verifikasi tambahan.</p>
                     <a href="<?= base_url('admin/reset_mfa'); ?>" class="btn btn-info">
                        <i class="fas fa-shield-alt fa-fw"></i> Atur Multi-Factor Authentication (MFA)
                    </a>
                </div>
            </div>

            </div>
    </div>
</div>

<script>
// For displaying file name in custom Bootstrap file input
$('.custom-file-input').on('change', function() {
    let fileName = $(this).val().split('\\').pop();
    $(this).next('.custom-file-label').addClass("selected").html(fileName);
});
</script>
<?= $this->endSection() ?>
