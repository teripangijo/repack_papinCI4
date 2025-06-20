<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800"> <?= $subtitle; ?></h1>


    <div class="row">
        <div class="col-lg">
            <?php if (session()->getFlashdata('message')) : ?>
                <?= session()->getFlashdata('message'); ?>
            <?php endif; ?>

            <?php $validation = \Config\Services::validation(); ?>
            <?php if ($validation->getError('menu')) : ?>
                <div class="alert alert-danger " role="alert"><?= $validation->getError('menu'); ?></div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Nama Perusahaan</th>
                            <th scope="col">Alamat</th>
                            <th scope="col">NPWP</th>
                            <th scope="col">Nomor Skep</th>
                            <th scope="col">Tgl Skep</th>
                            <th scope="col">Sisa Kuota</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(isset($perusahaan) && !empty($perusahaan)): ?>
                            <?php $i = 1; ?>
                            <?php foreach ($perusahaan as $p) : ?>
                                <tr>
                                    <th scope="row"><?= $i++; ?></th>
                                    <td><?= htmlspecialchars($p['NamaPers'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($p['alamat'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($p['npwp'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($p['NoSkep'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($p['tgl_skep'] ?? ''); ?></td>
                                    <td><span class="btn btn-success btn-sm"><?= htmlspecialchars($p['quota'] ?? '0'); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada data perusahaan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>
<!-- /.container-fluid -->
<?= $this->endSection() ?>
