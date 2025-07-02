<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= esc($subtitle ?? 'Edit Profil Saya') ?></h1>
        <a href="<?= base_url('petugas/index') ?>" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Dashboard Petugas
        </a>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <?= session()->getFlashdata('message') ?>
    <?php endif; ?>
    <?php if (isset($validation) && $validation->getErrors()): ?>
        <div class="alert alert-danger" role="alert">
            <?= $validation->listErrors() ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Profil Petugas</h6>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('petugas/edit_profil') ?>" method="post" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <img src="<?= base_url('uploads/profile_images/' . esc($user['image'] ?? 'default.jpg')) ?>"
                                     alt="Foto Profil <?= esc($user['name'] ?? '') ?>"
                                     class="img-thumbnail rounded-circle mb-3"
                                     style="width: 150px; height: 150px; object-fit: cover;"
                                     onerror="this.onerror=null; this.src='<?= base_url('assets/img/default-avatar.png') ?>';">
                                
                                <div class="form-group">
                                    <label for="profile_image">Ganti Foto Profil</label>
                                    <input type="file" class="form-control-file" id="profile_image" name="profile_image" accept="image/jpeg,image/png,image/gif">
                                    <small class="form-text text-muted">Kosongkan jika tidak ingin mengganti. Maks 2MB (JPG, PNG, GIF).</small>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="nama_petugas">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="nama_petugas" value="<?= esc($user['name'] ?? 'N/A') ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="nip_petugas">NIP (Nomor Induk Pegawai)</label>
                                    <input type="text" class="form-control" id="nip_petugas" value="<?= esc($user['email'] ?? 'N/A') ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="jabatan_petugas_display">Jabatan</label>
                                    <input type="text" class="form-control" id="jabatan_petugas_display" value="<?= esc($petugas_detail['Jabatan'] ?? 'N/A') ?>" readonly>
                                </div>
                                
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan Foto</button>
                            </div>
                        </div>
                    </form>

                    <hr>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Keamanan Akun</label>
                        <div class="col-sm-9">
                            <p class="form-text text-muted">Amankan akun Anda dengan lapisan verifikasi tambahan.</p>
                            <a href="<?= base_url('petugas/setup_mfa') ?>" class="btn btn-info">
                                <i class="fas fa-shield-alt fa-fw"></i> Atur Multi-Factor Authentication (MFA)
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                 <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Akun</h6>
                </div>
                <div class="card-body">
                    <p>Di halaman ini Anda hanya dapat mengubah foto profil Anda.</p>
                    <p>Untuk perubahan data lain seperti Nama, NIP, atau Jabatan, silakan hubungi Administrator.</p>
                    <p>Anda juga dapat <a href="<?= base_url('auth/changepass') ?>">mengganti password Anda</a> jika diperlukan.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
