<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <h1 class="h3 mb-4 text-gray-800"><?= esc($subtitle ?? 'Upload Berhasil') ?></h1>

    <div class="card shadow">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">File Anda Berhasil Di-upload!</h6>
        </div>
        <div class="card-body">
            <ul>
                <?php foreach (($upload_data ?? []) as $item => $value) : ?>
                    <li><strong><?= esc($item) ?>:</strong> <?= esc($value) ?></li>
                <?php endforeach; ?>
            </ul>
        
            <p><a href="<?= site_url('petugas_administrasi/upload') ?>" class="btn btn-primary">Upload File Lain</a></p>
        </div>
    </div>

</div>
<?= $this->endSection() ?>
