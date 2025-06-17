<?= $this->extend('layouts/auth') ?>

<?= $this->section('title') ?>
    Registrasi Akun REPACK
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<body class="modern-auth-page">
    <div class="auth-wrapper">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-7 col-lg-8 col-md-10">
                    <div class="card o-hidden border-0 shadow-lg my-4 modern-login-card modern-register-card">
                        <div class="card-body p-0">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="p-4 p-sm-5">
                                        <div class="text-center">
                                            <img src="<?= base_url('assets/img/logo_papin.png'); ?>" alt="Logo Instansi" class="login-logo mb-3">
                                            <h1 class="h4 text-gray-900 mb-3">Buat Akun Baru</h1>
                                            <p class="text-muted mb-4 small">Daftarkan diri Anda sebagai Pengguna Jasa.</p>
                                        </div>

                                        <?php if (session()->getFlashdata('message')): ?>
                                            <div class="alert alert-success" role="alert">
                                                <?= session()->getFlashdata('message'); ?>
                                            </div>
                                        <?php endif; ?>

                                        <form class="user" method="post" action="<?= base_url('auth/registration'); ?>">
                                            <?= csrf_field() ?>
                                            
                                            <div class="form-group">
                                                <input type="text" class="form-control form-control-user modern-form-control <?= (\Config\Services::validation()->hasError('name')) ? 'is-invalid' : ''; ?>" id="name" name="name" placeholder="Nama Lengkap Anda" value="<?= old('name'); ?>" required>
                                                <?php if(\Config\Services::validation()->hasError('name')): ?>
                                                    <small class="text-danger pl-3"><?= \Config\Services::validation()->getError('name'); ?></small>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="form-group">
                                                <input type="email" class="form-control form-control-user modern-form-control <?= (\Config\Services::validation()->hasError('email')) ? 'is-invalid' : ''; ?>" id="email" name="email" placeholder="Alamat Email Aktif" value="<?= old('email'); ?>" required>
                                                <?php if(\Config\Services::validation()->hasError('email')): ?>
                                                    <small class="text-danger pl-3"><?= \Config\Services::validation()->getError('email'); ?></small>
                                                <?php endif; ?>
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-sm-6 mb-3 mb-sm-0">
                                                    <input type="password" class="form-control form-control-user modern-form-control <?= (\Config\Services::validation()->hasError('password')) ? 'is-invalid' : ''; ?>" id="password" name="password" placeholder="Password (min. 6 karakter)" required>
                                                    <?php if(\Config\Services::validation()->hasError('password')): ?>
                                                        <small class="text-danger pl-3"><?= \Config\Services::validation()->getError('password'); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-sm-6">
                                                    <input type="password" class="form-control form-control-user modern-form-control <?= (\Config\Services::validation()->hasError('password2')) ? 'is-invalid' : ''; ?>" id="password2" name="password2" placeholder="Ulangi Password" required>
                                                    <?php if(\Config\Services::validation()->hasError('password2')): ?>
                                                        <small class="text-danger pl-3"><?= \Config\Services::validation()->getError('password2'); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <button type="submit" class="btn btn-primary btn-user btn-block modern-btn-login">
                                                Daftarkan Akun
                                            </button>
                                        </form>
                                        <hr class="my-4">
                                        <div class="text-center">
                                            <a class="small modern-login-link" href="<?= base_url('auth'); ?>">Sudah Punya Akun? Login!</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="auth-page-footer">
                        Copyright © Bea Cukai Pangkalpinang 2025
                    </div>

                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>
