<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800"> <?= $subtitle; ?></h1>


    <div class="row">
        <div class="col-lg">

            <?php
            $validation = \Config\Services::validation();
            if ($validation->hasError('menu')) : // Assuming 'menu' is a general validation error for the page ?>
                <div class="alert alert-danger" role="alert"><?= $validation->getError('menu'); ?></div>
            <?php endif; ?>
            <?= session()->getFlashdata('message'); ?>

            <table class="table table-hover">
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
                    <?php foreach ($perusahaan as $p) : ?>
                        <tr>
                            <th scope="row"><?= $i; ?></th>
                            <td><?= htmlspecialchars($p['NamaPers']); ?></td>
                            <td><?= htmlspecialchars($p['alamat']); ?></td>
                            <td><?= htmlspecialchars($p['npwp']); ?></td>
                            <td><?= htmlspecialchars($p['NoSkep']); ?></td>
                            <td><?= htmlspecialchars($p['tgl_skep']); ?></td>
                            <td><a class="btn btn-success btn-sm" href="<?= base_url('admin/uploadproses/' . $p['id']); ?>">Upload Dokumen</a></td>
                        </tr>

                        <?php $i++; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>


        </div>
    </div>

</div>
<!-- /.container-fluid -->

</div>
<!-- End of Main Content -->
