<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= esc($subtitle ?? 'Formulir Penolakan Permohonan') ?></h1>
    </div>

    <?php if (isset($validation) && $validation->getErrors()) : ?>
        <div class="alert alert-danger" role="alert">
            <?= $validation->listErrors() ?>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Tolak Permohonan ID: <?= esc($permohonan['id'] ?? '') ?>
            </h6>
        </div>
        <div class="card-body">
            <p>Anda akan menolak permohonan dari: <strong><?= esc($permohonan['NamaPers'] ?? 'N/A') ?></strong></p>
            <p>Nomor Surat Pemohon: <strong><?= esc($permohonan['nomorSurat'] ?? 'N/A') ?></strong></p>
            <hr>
            
            <form action="<?= site_url('petugas_administrasi/tolak_permohonan_awal/' . ($permohonan['id'] ?? '')) ?>" method="post">
                <?= csrf_field() ?>
                
                <div class="form-group">
                    <label for="alasan_penolakan"><strong>Alasan Penolakan <span class="text-danger">*</span></strong></label>
                    <textarea class="form-control <?= (isset($validation) && $validation->hasError('alasan_penolakan')) ? 'is-invalid' : '' ?>" id="alasan_penolakan" name="alasan_penolakan" rows="4" placeholder="Jelaskan alasan mengapa permohonan ini ditolak..." required><?= old('alasan_penolakan') ?></textarea>
                    <?php if (isset($validation) && $validation->hasError('alasan_penolakan')): ?>
                        <div class="invalid-feedback"><?= $validation->getError('alasan_penolakan') ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="text-right">
                    <a href="<?= site_url('petugas_administrasi/permohonanMasuk') ?>" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-times"></i> Tolak Permohonan Ini</button>
                </div>

            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
