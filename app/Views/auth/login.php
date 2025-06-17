<?= $this->extend('layouts/auth') ?>

<?= $this->section('title') ?>
    <?= isset($title) ? htmlspecialchars($title) : 'Login REPACK'; ?>
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
                                            <img src="<?= base_url('assets/img/logo_papin.webp'); ?>" alt="Logo Instansi" class="login-logo mb-3">
                                            <h1 class="h4 text-gray-900 mb-2">Selamat Datang</h1>
                                            <p class="text-muted mb-4">Login ke akun REPACK Anda</p>
                                        </div>

                                        <?php if (session()->getFlashdata('message')): ?>
                                            <div class="alert alert-danger" role="alert">
                                                <?= session()->getFlashdata('message'); ?>
                                            </div>
                                        <?php endif; ?>

                                        <form class="user" method="post" action="<?= base_url('index.php/auth'); ?>">
                                            <?= csrf_field() ?>
                                            <div class="form-group">
                                                <input type="text" class="form-control form-control-user modern-form-control"
                                                       id="login_identifier" name="login_identifier"
                                                       placeholder="Email atau NIP Anda"
                                                       value="<?= old('login_identifier'); ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <input type="password" class="form-control form-control-user modern-form-control"
                                                       id="password" name="password" placeholder="Password" required>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-user btn-block modern-btn-login">
                                                Login
                                            </button>
                                        </form>
                                        <hr class="my-4">
                                        <div class="text-center">
                                        </div>
                                        <div class="text-center">
                                            <a class="small modern-login-link" href="<?= base_url('auth/registration'); ?>">Buat Akun Baru (Pengguna Jasa)</a>
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

    <div class="image-attribution">
        Gambar oleh <a href="https://pixabay.com/id/users/ellisedelacruz-2310550/?utm_source=link-attribution&utm_medium=referral&utm_campaign=image&utm_content=1315672" target="_blank" rel="noopener noreferrer">Claire Dela Cruz</a> dari <a href="https://pixabay.com/id//?utm_source=link-attribution&utm_medium=referral&utm_campaign=image&utm_content=1315672" target="_blank" rel="noopener noreferrer">Pixabay</a>
    </div>
<?= $this->endSection() ?>
