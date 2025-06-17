<?= $this->extend('layouts/auth') ?>

<?= $this->section('title') ?>
    <?= isset($title) ? htmlspecialchars($title) : 'Verifikasi Login'; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<body class="modern-auth-page">
    <div class="auth-wrapper">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-5 col-lg-6 col-md-8">
                    <div class="card o-hidden border-0 shadow-lg my-4 modern-login-card">
                        <div class="card-body p-0">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="p-4 p-sm-5">
                                        <div class="text-center">
                                            <h1 class="h4 text-gray-900 mb-2">Verifikasi Dua Faktor</h1>
                                            <p class="text-muted mb-4">Buka aplikasi authenticator Anda dan masukkan kode 6-digit.</p>
                                        </div>

                                        <?php if (session()->getFlashdata('message')): ?>
                                            <div class="alert alert-warning" role="alert">
                                                <?= session()->getFlashdata('message'); ?>
                                            </div>
                                        <?php endif; ?>

                                        <form class="user" method="post" action="<?= base_url('auth/verify_mfa_login'); ?>">
                                            <?= csrf_field() ?>
                                            <div class="form-group">
                                                <input type="text" class="form-control form-control-user text-center <?= (\Config\Services::validation()->hasError('mfa_code')) ? 'is-invalid' : ''; ?>" id="mfa_code" name="mfa_code" placeholder="xxxxxx" autofocus autocomplete="off" maxlength="6" required>
                                                <?php if(\Config\Services::validation()->hasError('mfa_code')): ?>
                                                    <small class="text-danger pl-3"><?= \Config\Services::validation()->getError('mfa_code'); ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-user btn-block">
                                                Verifikasi
                                            </button>
                                        </form>
                                        <hr>
                                        <div class="text-center">
                                            <a class="small modern-login-link" href="<?= base_url('auth/logout'); ?>">Bukan Anda? Kembali ke Login</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>
