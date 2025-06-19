<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= esc($subtitle ?? 'Dashboard Petugas Administrasi') ?></h1>
    </div>

    <div class="row">
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <a href="<?= site_url('petugas_administrasi/permohonanMasuk') ?>" class="text-decoration-none">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Permohonan Impor Pending</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= esc($pending_permohonan ?? '0') ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-file-import fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <a href="<?= site_url('petugas_administrasi/daftar_pengajuan_kuota') ?>" class="text-decoration-none">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Pengajuan Kuota Pending</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= esc($pending_kuota_requests ?? '0') ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Selamat Datang, <?= esc($user['name'] ?? 'Petugas Administrasi') ?>!</h6>
                </div>
                <div class="card-body">
                    <p>Ini adalah halaman dashboard Petugas Administrasi. Anda dapat mengelola permohonan impor dan pengajuan kuota dari perusahaan.</p>
                    <p>Gunakan menu di sidebar atau kartu di atas untuk navigasi ke halaman yang relevan.</p>
                </div>
            </div>
        </div>
    </div>

</div>
<?= $this->endSection() ?>
