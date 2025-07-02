<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
    <?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Dashboard'; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Admin Dashboard'; ?></h1>
    </div>

    <div class="row">

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Pengguna Terdaftar</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= isset($total_users) ? $total_users : '0'; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Permohonan Impor Pending</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= isset($pending_permohonan) ? $pending_permohonan : '0'; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-import fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Pengajuan Kuota Pending</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= isset($pending_kuota_requests) ? $pending_kuota_requests : '0'; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Selamat Datang, <?= isset($user['name']) ? htmlspecialchars($user['name']) : 'Admin'; ?>!</h6>
                </div>
                <div class="card-body">
                    <p>Ini adalah halaman dashboard admin. Anda dapat mengelola pengguna, peran, permohonan, dan pengajuan kuota dari sini.</p>
                    <p>Gunakan menu di sidebar untuk navigasi.</p>

                    <a href="<?= base_url('admin/permohonanMasuk'); ?>" class="btn btn-info mr-2">Lihat Permohonan Masuk</a>
                    <a href="<?= base_url('admin/daftar_pengajuan_kuota'); ?>" class="btn btn-success">Lihat Pengajuan Kuota</a>
                </div>
            </div>
        </div>
    </div>

</div>
<?= $this->endSection() ?>
