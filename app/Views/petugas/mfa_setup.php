<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= esc($subtitle ?? 'Setup Multi-Factor Authentication') ?></h1>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Aktifkan Autentikasi Dua Faktor (MFA)</h6>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('message')): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= session()->getFlashdata('message') ?>
                    </div>
                    <?php endif; ?>

                    <p>Untuk meningkatkan keamanan akun Anda, silakan pindai (scan) QR code di bawah ini menggunakan aplikasi authenticator seperti Google Authenticator, Authy, atau lainnya.</p>
                    
                    <div class="text-center my-4">
                        <img src="<?= esc($qr_code_data_uri ?? '', 'attr') ?>" alt="MFA QR Code">
                    </div>

                    <p>Jika Anda tidak dapat memindai QR code, Anda bisa memasukkan kode rahasia ini secara manual ke dalam aplikasi authenticator:</p>
                    <p class="text-center">
                        <code style="font-size: 1.2rem; letter-spacing: 2px; padding: 10px; background-color: #f8f9fa; border-radius: 5px;"><?= esc($secret_key ?? 'KUNCI TIDAK TERSEDIA') ?></code>
                    </p>
                    
                    <hr>
                    
                    <form action="<?= site_url('petugas/verify_mfa') ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <label for="one_time_password">Masukkan Kode Verifikasi 6-Digit</label>
                            <input type="text" class="form-control" id="one_time_password" name="one_time_password" required autocomplete="off" maxlength="6" pattern="\d{6}" inputmode="numeric">
                        </div>
                        <button type="submit" class="btn btn-primary">Aktifkan & Verifikasi</button>
                        <a href="<?= site_url('petugas/edit_profil') ?>" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
