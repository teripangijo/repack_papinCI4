<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= htmlspecialchars($subtitle); ?></h1>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Aktifkan Autentikasi Dua Faktor (MFA)</h6>
                </div>
                <div class="card-body">
                    <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= $this->session->flashdata('error'); ?>
                    </div>
                    <?php endif; ?>

                    <p>Untuk meningkatkan keamanan akun Anda, silakan pindai (scan) QR code di bawah ini menggunakan aplikasi authenticator seperti Google Authenticator, Authy, atau lainnya.</p>
                    
                    <div class="text-center my-4">
                        <img src="<?= $qr_code_data_uri; ?>" alt="MFA QR Code">
                    </div>

                    <p>Jika Anda tidak dapat memindai QR code, Anda bisa memasukkan kode rahasia ini secara manual ke dalam aplikasi authenticator:</p>
                    <p class="text-center">
                        <code style="font-size: 1.2rem; letter-spacing: 2px; padding: 10px; background-color: #f8f9fa; border-radius: 5px;"><?= htmlspecialchars($secret_key); ?></code>
                    </p>
                    
                    <hr>
                    
                    <form action="<?= base_url('user/verify_mfa'); ?>" method="post">
                        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                        <div class="form-group">
                            <label for="one_time_password">Masukkan Kode Verifikasi 6-Digit</label>
                            <input type="text" class="form-control" id="one_time_password" name="one_time_password" required autocomplete="off" maxlength="6">
                        </div>
                        <button type="submit" class="btn btn-primary">Aktifkan & Verifikasi</button>
                        <a href="<?= base_url('auth/logout'); ?>" class="btn btn-secondary">Batal</a>
                    </form>
                    </div>
            </div>
        </div>
    </div>
</div>