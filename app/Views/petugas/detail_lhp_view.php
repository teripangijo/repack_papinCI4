<?= $this->extend('Layouts/main') ?>

<?php
// NOTE: Logic to fetch related data should ideally be in the controller.
// This is kept here to match the original file's logic.
$status_permohonan_terkait = '';
if(isset($lhp_detail['id_permohonan_ajuan'])){
    $db = \Config\Database::connect();
    $permohonan_status_query = $db->table('user_permohonan')
                                  ->select('status')
                                  ->where('id', $lhp_detail['id_permohonan_ajuan'])
                                  ->get()->getRow();
    if($permohonan_status_query) {
        $status_permohonan_terkait = $permohonan_status_query->status;
    }
}
?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= esc($subtitle ?? 'Detail LHP') ?></h1>
        <a href="<?= site_url('petugas/riwayat_lhp_direkam') ?>" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Riwayat LHP
        </a>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <?= session()->getFlashdata('message') ?>
    <?php endif; ?>

    <?php if (isset($lhp_detail) && !empty($lhp_detail)): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Informasi Permohonan Terkait (ID Aju: <?= esc($lhp_detail['id_permohonan_ajuan'] ?? 'N/A') ?>)</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Nama Perusahaan:</strong> <?= esc($lhp_detail['nama_perusahaan_pemohon'] ?? '-') ?></p>
                        <p><strong>No. Surat Pemohon:</strong> <?= esc($lhp_detail['nomor_surat_permohonan'] ?? '-') ?></p>
                        <p><strong>Tgl. Surat Pemohon:</strong> <?= isset($lhp_detail['tanggal_surat_pemohon']) ? date('d M Y', strtotime($lhp_detail['tanggal_surat_pemohon'])) : '-' ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Nama Barang (Diajukan):</strong> <?= esc($lhp_detail['nama_barang_di_permohonan'] ?? '-') ?></p>
                        <p><strong>Jumlah Diajukan (Permohonan):</strong> <?= esc(number_format($lhp_detail['jumlah_barang_di_permohonan'] ?? 0)) ?> Unit</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Detail Laporan Hasil Pemeriksaan</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>No. LHP:</strong> <?= esc($lhp_detail['NoLHP'] ?? '-') ?></p>
                        <p><strong>Tanggal LHP:</strong> <?= isset($lhp_detail['TglLHP']) ? date('d M Y', strtotime($lhp_detail['TglLHP'])) : '-' ?></p>
                        <p><strong>Jumlah Diajukan (di LHP):</strong> <?= esc(number_format($lhp_detail['JumlahAju'] ?? 0)) ?> Unit</p>
                        <p><strong>Jumlah Disetujui (LHP):</strong> <span class="font-weight-bold text-success"><?= esc(number_format($lhp_detail['JumlahBenar'] ?? 0)) ?> Unit</span></p>
                        <p><strong>Jumlah Ditolak (LHP):</strong> <span class="text-danger"><?= esc(number_format($lhp_detail['JumlahSalah'] ?? 0)) ?> Unit</span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Catatan Pemeriksaan:</strong></p>
                        <p><?= !empty($lhp_detail['Catatan']) ? nl2br(esc($lhp_detail['Catatan'])) : '<span class="text-muted"><em>Tidak ada catatan.</em></span>' ?></p>
                        <p><strong>Waktu Rekam LHP:</strong> <?= isset($lhp_detail['submit_time']) ? date('d M Y H:i:s', strtotime($lhp_detail['submit_time'])) : '-' ?></p>
                        <?php if (!empty($lhp_detail['FileLHP'])): ?>
                            <p><strong>File LHP:</strong> <a href="<?= site_url('petugas/download/lhp/' . esc($lhp_detail['FileLHP'])) ?>" target="_blank"><?= esc($lhp_detail['FileLHP']) ?></a></p>
                        <?php endif; ?>
                        <?php if (!empty($lhp_detail['file_dokumentasi_foto'])): ?>
                            <p><strong>File Dokumentasi Foto:</strong> <a href="<?= site_url('petugas/download/dokumentasi_lhp/' . esc($lhp_detail['file_dokumentasi_foto'])) ?>" target="_blank"><?= esc($lhp_detail['file_dokumentasi_foto']) ?></a></p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($status_permohonan_terkait == '2'): ?>
                    <hr>
                    <a href="<?= site_url('petugas/rekam_lhp/' . $lhp_detail['id_permohonan_ajuan']) ?>" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit LHP Ini
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">Detail LHP tidak dapat dimuat.</div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
