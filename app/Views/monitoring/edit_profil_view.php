<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= $subtitle; ?></h1>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Keamanan Akun</h5>
            <p>Amankan akun Anda dengan lapisan verifikasi tambahan.</p>
            <a href="<?= base_url('monitoring/reset_mfa'); ?>" class="btn btn-primary">
                Atur Ulang Multi-Factor Authentication (MFA)
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
