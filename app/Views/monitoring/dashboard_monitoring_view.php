<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($subtitle ?? 'Dashboard Monitoring'); ?></h1>
    </div>

    <!-- Menampilkan pesan flashdata jika ada, sudah dikonversi ke sintaks CI4 -->
    <?php if (session()->getFlashdata('message')) : ?>
        <?= session()->getFlashdata('message') ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-xl-12 col-md-12 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Pengajuan Kuota (Total Sistem)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= htmlspecialchars(number_format($total_pengajuan_kuota_all ?? 0)); ?> Pengajuan</div>
                            <div class="mt-2">
                                <span class="badge badge-warning">Pending: <?= htmlspecialchars(number_format($pengajuan_kuota_pending ?? 0)); ?></span>
                                <span class="badge badge-success">Disetujui: <?= htmlspecialchars(number_format($pengajuan_kuota_approved ?? 0)); ?></span>
                                <span class="badge badge-danger">Ditolak: <?= htmlspecialchars(number_format($pengajuan_kuota_rejected ?? 0)); ?></span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-import fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12 col-md-12 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Permohonan Impor Kembali (Total Sistem)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= htmlspecialchars(number_format($total_permohonan_impor_all ?? 0)); ?> Permohonan</div>
                             <div class="mt-2">
                                <span class="badge badge-dark">Baru/Diproses Admin: <?= htmlspecialchars(number_format($permohonan_impor_baru_atau_diproses_admin ?? 0)); ?></span>
                                <span class="badge badge-primary">Penunjukan Petugas: <?= htmlspecialchars(number_format($permohonan_impor_penunjukan_petugas ?? 0)); ?></span>
                                <span class="badge badge-warning">LHP Direkam: <?= htmlspecialchars(number_format($permohonan_impor_lhp_direkam ?? 0)); ?></span>
                            </div>
                            <div class="mt-1">
                                <span class="badge badge-success">Selesai (Disetujui): <?= htmlspecialchars(number_format($permohonan_impor_selesai_disetujui ?? 0)); ?></span>
                                <span class="badge badge-danger">Selesai (Ditolak): <?= htmlspecialchars(number_format($permohonan_impor_selesai_ditolak ?? 0)); ?></span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ship fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <p class="text-muted">Selamat datang di Dashboard Monitoring, <?= htmlspecialchars($user['name'] ?? 'Pengguna'); ?>. Anda dapat memantau status pengajuan kuota dan permohonan impor kembali dari seluruh pengguna jasa.</p>
        </div>
    </div>

</div>
<?= $this->endSection() ?>
