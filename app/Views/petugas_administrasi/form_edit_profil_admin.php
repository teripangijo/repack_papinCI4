<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= esc($subtitle ?? 'Edit Profil') ?></h1>

    <div class="row">
        <div class="col-lg-8">
            
            <?php if (session()->getFlashdata('message')) : ?>
                <?= session()->getFlashdata('message') ?>
            <?php endif; ?>
            
            <?php if (isset($validation) && $validation->getErrors()) : ?>
                <div class="alert alert-danger" role="alert">
                    <?= $validation->listErrors() ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('petugas_administrasi/edit_profil') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="form-group row">
                    <label for="login_identifier" class="col-sm-3 col-form-label">Email (Login)</label>
                    <div class="col-sm-9">
                        <input type="email" class="form-control <?= (isset($validation) && $validation->hasError('login_identifier')) ? 'is-invalid' : '' ?>" id="login_identifier" name="login_identifier" value="<?= old('login_identifier', $user['email'] ?? '') ?>">
                        <?php if(isset($validation) && $validation->hasError('login_identifier')): ?>
                            <div class="invalid-feedback"><?= $validation->getError('login_identifier') ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="name" class="col-sm-3 col-form-label">Nama Lengkap</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control <?= (isset($validation) && $validation->hasError('name')) ? 'is-invalid' : '' ?>" id="name" name="name" value="<?= old('name', $user['name'] ?? '') ?>">
                        <?php if(isset($validation) && $validation->hasError('name')): ?>
                            <div class="invalid-feedback"><?= $validation->getError('name') ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-sm-3">Foto Profil</div>
                    <div class="col-sm-9">
                        <div class="row">
                            <div class="col-sm-3">
                                <img src="<?= base_url('petugas_administrasi/downloadFile/' . esc($user['image'] ?? 'default.jpg')) ?>" class="img-thumbnail" alt="Profile Image">
                            </div>
                            <div class="col-sm-9">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input <?= (isset($validation) && $validation->hasError('profile_image')) ? 'is-invalid' : '' ?>" id="profile_image" name="profile_image">
                                    <label class="custom-file-label" for="profile_image">Pilih file...</label>
                                    <small class="form-text text-muted">Format: JPG, PNG, JPEG, GIF. Max: 2MB, 1024x1024px.</small>
                                    <?php if(isset($validation) && $validation->hasError('profile_image')): ?>
                                        <div class="invalid-feedback d-block"><?= $validation->getError('profile_image') ?></div>
                                    <?php endif; ?>
                                </div>
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
                     <a href="<?= base_url('petugas_administrasi/setup_mfa') ?>" class="btn btn-info">
                        <i class="fas fa-shield-alt fa-fw"></i> Atur Multi-Factor Authentication (MFA)
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Untuk menampilkan nama file di input custom file bootstrap
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });
});
</script>
<?= $this->endSection() ?>
