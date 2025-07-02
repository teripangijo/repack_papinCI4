<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <h1 class="h3 mb-2 text-gray-800"><?= esc($subtitle ?? 'Detail Permohonan') ?></h1>
    <p class="mb-4">Rincian lengkap dari permohonan impor kembali dengan ID Aju: <strong><?= esc($permohonan_detail['id'] ?? 'N/A') ?></strong></p>

    <?php if (session()->getFlashdata('message')): ?>
        <?= session()->getFlashdata('message') ?>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Informasi Permohonan</h6>
            <div>
                <a href="<?= base_url('petugas/monitoring_permohonan') ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left fa-sm"></i> Kembali ke Daftar Monitoring</a>
                <?php if (isset($permohonan_detail['id'])): ?>
                <a href="<?= base_url('user/printPdf/' . $permohonan_detail['id']) ?>" target="_blank" class="btn btn-info btn-sm"><i class="fas fa-print fa-sm"></i> Cetak PDF Permohonan Awal</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <?php if (isset($permohonan_detail) && !empty($permohonan_detail)): ?>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <h5>Status Saat Ini:
                            <?php
                            $status_text_user = 'Tidak Diketahui';
                            $status_badge_user = 'secondary';
                            if (isset($permohonan_detail['status'])) {
                                switch ($permohonan_detail['status']) {
                                    case '0': $status_text_user = 'Diajukan (Menunggu Verifikasi)'; $status_badge_user = 'info'; break;
                                    case '5': $status_text_user = 'Diproses Kantor'; $status_badge_user = 'info'; break;
                                    case '1': $status_text_user = 'Pemeriksaan Petugas'; $status_badge_user = 'primary'; break;
                                    case '2': $status_text_user = 'Menunggu Keputusan'; $status_badge_user = 'warning'; break;
                                    case '3': $status_text_user = 'Disetujui'; $status_badge_user = 'success'; break;
                                    case '4': $status_text_user = 'Ditolak'; $status_badge_user = 'danger'; break;
                                    default: $status_text_user = 'Status Tidak Dikenal (' . esc($permohonan_detail['status']) . ')'; $status_badge_user = 'dark';
                                }
                            }
                            echo '<span class="badge badge-' . $status_badge_user . ' p-2">' . esc($status_text_user) . '</span>';
                            ?>
                        </h5>
                    </div>
                </div>
                <hr>

                <h5 class="mt-4 mb-3 font-weight-bold text-primary">Data Perusahaan</h5>
                <div class="row">
                    <div class="col-md-6"><strong>Nama Perusahaan:</strong> <?= esc($permohonan_detail['NamaPers'] ?? '-') ?></div>
                    <div class="col-md-6"><strong>NPWP:</strong> <?= esc($permohonan_detail['npwp_perusahaan'] ?? '-') ?></div>
                </div>
                <hr>

                <h5 class="mt-4 mb-3 font-weight-bold text-primary">Detail Permohonan</h5>
                <div class="row">
                    <div class="col-md-4"><strong>Nomor Surat Anda:</strong> <?= esc($permohonan_detail['nomorSurat'] ?? '-') ?></div>
                    <div class="col-md-4"><strong>Tanggal Surat Anda:</strong> <?= isset($permohonan_detail['TglSurat']) && $permohonan_detail['TglSurat'] != '0000-00-00' ? date('d F Y', strtotime($permohonan_detail['TglSurat'])) : '-' ?></div>
                </div>
                <hr>

                <?php if (isset($permohonan_detail['status']) && $permohonan_detail['status'] >= '1' && $permohonan_detail['status'] != '5') : ?>
                    <h5 class="mt-4 mb-3 font-weight-bold text-primary">Informasi Penugasan & Pemeriksaan</h5>
                    <div class="row">
                        <div class="col-md-6"><strong>Petugas Pemeriksa:</strong> <?= esc($permohonan_detail['nama_petugas_pemeriksa'] ?? 'N/A') ?></div>
                        <div class="col-md-6"><strong>No. Surat Tugas:</strong> <?= esc($permohonan_detail['NoSuratTugas'] ?? '-') ?></div>
                    </div>

                    <?php if ($lhp_detail) : ?>
                        <h6 class="mt-4 mb-3 font-weight-bold" style="color: #17a2b8;">Laporan Hasil Pemeriksaan (LHP)</h6>
                        <div class="row">
                            <div class="col-md-4"><strong>No. LHP:</strong> <?= esc($lhp_detail['NoLHP'] ?? '-') ?></div>
                            <div class="col-md-4"><strong>Tgl. LHP:</strong> <?= isset($lhp_detail['TglLHP']) && $lhp_detail['TglLHP'] != '0000-00-00' ? date('d F Y', strtotime($lhp_detail['TglLHP'])) : '-' ?></div>
                        </div>
                    <?php else: ?>
                        <p class="mt-3 text-muted"><em>Laporan Hasil Pemeriksaan (LHP) belum tersedia.</em></p>
                    <?php endif; ?>
                    <hr>
                <?php endif; ?>

                <?php if (isset($permohonan_detail['status']) && in_array($permohonan_detail['status'], ['3', '4'])) : ?>
                    <h5 class="mt-4 mb-3 font-weight-bold text-<?= $permohonan_detail['status'] == '3' ? 'success' : 'danger' ?>">
                        Keputusan Akhir: <?= $permohonan_detail['status'] == '3' ? 'Permohonan Disetujui' : 'Permohonan Ditolak' ?>
                    </h5>
                    <div class="row">
                        <div class="col-md-6"><strong>No. Surat Keputusan:</strong> <?= esc($permohonan_detail['nomorSetuju'] ?? '-') ?></div>
                        <div class="col-md-6"><strong>Tgl. Surat Keputusan:</strong> <?= isset($permohonan_detail['tgl_S']) && $permohonan_detail['tgl_S'] != '0000-00-00' ? date('d F Y', strtotime($permohonan_detail['tgl_S'])) : '-' ?></div>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="alert alert-warning" role="alert">
                    Data detail permohonan tidak dapat dimuat atau tidak ditemukan.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
