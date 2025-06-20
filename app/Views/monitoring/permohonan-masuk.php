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
                            <th scope="col">Id Aju</th>
                            <th scope="col">No Surat</th>
                            <th scope="col">Tanggal Surat</th>
                            <th scope="col">Nama Perusahaan</th>
                            <th scope="col">Waktu Submit</th>
                            <th scope="col">Waktu Selesai</th>
                            <th scope="col">Nama Petugas</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($permohonan) && !empty($permohonan)): ?>
                            <?php $i = 1; ?>
                            <?php foreach ($permohonan as $p) : ?>
                                <?php
                                    $statusText = 'Belum Diproses';
                                    $petugasName = '-';
                                    
                                    if ($p['status'] == 1) {
                                        $statusText = "Perekaman LHP";
                                    } elseif ($p['status'] == 0) {
                                        $statusText = "Penerimaan Data";
                                    } elseif ($p['status'] == 2) {
                                        $statusText = "Penerbitan Surat Persetujuan";
                                    } elseif ($p['status'] == 3) {
                                        $statusText = "Selesai";
                                    }

                                    if ($p['petugas'] == 1) {
                                        $petugasName = "Suci Dwi Anggraieni";
                                    } elseif ($p['petugas'] == 2) {
                                        $petugasName = "Bayu Raharjo Putra";
                                    } elseif ($p['petugas'] == 3) {
                                        $petugasName = "Zulkifli";
                                    }
                                ?>
                                <tr>
                                    <th scope="row"><?= $i++; ?></th>
                                    <td><?= htmlspecialchars($p['id'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($p['nomorSurat'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($p['TglSurat'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($p['NamaPers'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($p['time_stamp'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($p['time_selesai'] ?? ''); ?></td>
                                    <td><?= htmlspecialchars($petugasName); ?></td>
                                    <td>
                                        <span class="badge badge-info"><?= htmlspecialchars($statusText); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">Tidak ada data permohonan.</td>
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
