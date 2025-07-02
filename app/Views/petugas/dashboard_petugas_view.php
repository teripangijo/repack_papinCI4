<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= esc($subtitle ?? 'Dashboard Petugas') ?></h1>
    <div class="row">
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                 <a href="<?= base_url('petugas/daftar_pemeriksaan') ?>" class="text-decoration-none">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Tugas Rekam LHP Baru</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= esc($jumlah_tugas_lhp ?? 0) ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <a href="<?= base_url('petugas/riwayat_lhp_direkam') ?>" class="text-decoration-none">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    LHP Selesai Direkam</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= esc($jumlah_lhp_selesai ?? 0) ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
    <p>Selamat datang, <?= esc($user['name'] ?? 'Petugas') ?>. Silakan pilih menu "Daftar Pemeriksaan" untuk melihat tugas Anda.</p>
</div>
<?= $this->endSection() ?>
