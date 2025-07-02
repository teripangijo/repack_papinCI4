<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <h1 class="h3 mb-4 text-gray-800"> <?= esc($subtitle ?? 'Upload Dokumen Perusahaan') ?></h1>

    <div class="row">
        <div class="col-lg">

            <?php if (isset($validation) && $validation->hasError('menu')): ?>
                <div class="alert alert-danger" role="alert">
                    <?= $validation->getError('menu') ?>
                </div>
            <?php endif; ?>
            
            <?php if (session()->getFlashdata('message')): ?>
                <?= session()->getFlashdata('message') ?>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Daftar Perusahaan</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="dataTablePerusahaan">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Nama Perusahaan</th>
                                    <th scope="col">Alamat</th>
                                    <th scope="col">NPWP</th>
                                    <th scope="col">Nomor Skep</th>
                                    <th scope="col">Tgl Skep</th>
                                    <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; ?>
                                <?php foreach (($perusahaan ?? []) as $p) : ?>
                                    <tr>
                                        <th scope="row"><?= $i++ ?></th>
                                        <td><?= esc($p['NamaPers'] ?? '') ?></td>
                                        <td><?= esc($p['alamat'] ?? '') ?></td>
                                        <td><?= esc($p['npwp'] ?? '') ?></td>
                                        <td><?= esc($p['NoSkep'] ?? '') ?></td>
                                        <td><?= esc($p['tgl_skep'] ?? '') ?></td>
                                        <td>
                                            <a class="btn btn-success btn-sm" href="<?= base_url('petugas_administrasi/uploadproses/' . esc($p['id'], 'url')) ?>">
                                                <i class="fas fa-upload"></i> Upload
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    $('#dataTablePerusahaan').DataTable();
});
</script>
<?= $this->endSection() ?>
