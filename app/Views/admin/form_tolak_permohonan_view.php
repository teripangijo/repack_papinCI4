<?= $this->extend('layouts/main') ?> // Menggunakan layout 'main.php'
<?= $this->section('title') ?>
    <?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Tolak Permohonan'; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($subtitle); ?></h1>
    </div>

    <?php
    // Display validation errors for CodeIgniter 4
    $validation = \Config\Services::validation();
    if ($validation->getErrors()) : ?>
        <div class="alert alert-danger" role="alert">
            <?php foreach ($validation->getErrors() as $error) : ?>
                <p><?= $error ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Tolak Permohonan ID: <?= htmlspecialchars($permohonan['id']); ?>
            </h6>
        </div>
        <div class="card-body">
            <p>Anda akan menolak permohonan dari: <strong><?= htmlspecialchars($permohonan['NamaPers']); ?></strong></p>
            <p>Nomor Surat Pemohon: <strong><?= htmlspecialchars($permohonan['nomorSurat']); ?></strong></p>
            <hr>

            <form action="<?= base_url('admin/tolak_permohonan_awal/' . $permohonan['id']); ?>" method="post">
                <?= csrf_field(); // CSRF token for CI4 ?>

                <div class="form-group">
                    <label for="alasan_penolakan"><strong>Alasan Penolakan <span class="text-danger">*</span></strong></label>
                    <textarea class="form-control <?= $validation->hasError('alasan_penolakan') ? 'is-invalid' : ''; ?>" id="alasan_penolakan" name="alasan_penolakan" rows="4" placeholder="Jelaskan alasan mengapa permohonan ini ditolak..." required><?= old('alasan_penolakan'); ?></textarea>
                    <?php if ($validation->hasError('alasan_penolakan')) : ?>
                        <div class="invalid-feedback"><?= $validation->getError('alasan_penolakan'); ?></div>
                    <?php endif; ?>
                </div>

                <div class="text-right">
                    <a href="<?= base_url('admin/permohonanMasuk'); ?>" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-times"></i> Tolak Permohonan Ini</button>
                </div>

            </form>
        </div>
    </div>
</div>
