<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($subtitle ?? 'Detail Pantauan Permohonan Impor'); ?></h1>
        <a href="<?= base_url('monitoring/permohonan_impor'); ?>" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Daftar Pantauan
        </a>
    </div>

    <?php if (session()->getFlashdata('message')) : ?>
        <?= session()->getFlashdata('message') ?>
    <?php endif; ?>
    <?php if (session()->getFlashdata('message_error_quota')) : ?>
        <?= session()->getFlashdata('message_error_quota') ?>
    <?php endif; ?>


    <?php if (isset($permohonan_detail) && !empty($permohonan_detail)): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Informasi Permohonan (ID Aju: <?= htmlspecialchars($permohonan_detail['id']); ?>)</h6>
                <span>
                    <?php
                        // Pastikan helper 'repack_helper' dimuat
                        $status_info = status_permohonan_text_badge($permohonan_detail['status'] ?? ''); 
                        echo '<span class="badge badge-pill badge-' . htmlspecialchars($status_info['badge']) . ' p-2">' . htmlspecialchars($status_info['text']) . '</span>';
                    ?>
                </span>
            </div>
            <div class="card-body">
                
                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <strong class="d-block text-primary">Data Pengajuan:</strong>
                        <table class="table table-sm table-borderless table-hover">
                            <tr><td width="40%">No. Surat Pemohon</td><td>: <?= htmlspecialchars($permohonan_detail['nomorSurat'] ?? '-'); ?></td></tr>
                            <tr><td>Tgl. Surat Pemohon</td><td>: <?= isset($permohonan_detail['TglSurat']) && $permohonan_detail['TglSurat'] != '0000-00-00' ? date('d M Y', strtotime($permohonan_detail['TglSurat'])) : '-'; ?></td></tr>
                            <tr><td>Waktu Submit Sistem</td><td>: <?= isset($permohonan_detail['time_stamp']) ? date('d M Y H:i:s', strtotime($permohonan_detail['time_stamp'])) : '-'; ?></td></tr>
                            <tr><td>Perihal</td><td>: <?= htmlspecialchars($permohonan_detail['Perihal'] ?? '-'); ?></td></tr>
                            <tr><td>Nama Barang</td><td>: <?= htmlspecialchars($permohonan_detail['NamaBarang'] ?? '-'); ?></td></tr>
                            <tr><td>Jumlah Diajukan</td><td>: <?= htmlspecialchars(number_format($permohonan_detail['JumlahBarang'] ?? 0)); ?> Unit</td></tr>
                            <tr><td>Lokasi Bongkar</td><td>: <?= htmlspecialchars($permohonan_detail['lokasi'] ?? '-'); ?></td></tr>
                            <tr><td>No. SKEP Asal (Kuota)</td><td>: <?= htmlspecialchars($permohonan_detail['NoSkep'] ?? '-'); ?></td></tr>
                            <?php if (isset($permohonan_detail['file_bc_manifest']) && !empty($permohonan_detail['file_bc_manifest'])): ?>
                                <tr>
                                    <td>File BC 1.1 / Manifest</td>
                                    <td>: 
                                        <a href="<?= base_url('uploads/bc_manifest/' . htmlspecialchars($permohonan_detail['file_bc_manifest'])); ?>" target="_blank" class="btn btn-sm btn-outline-info" title="Unduh/Lihat File BC 1.1 / Manifest">
                                            <i class="fas fa-file-download"></i> <?= htmlspecialchars($permohonan_detail['file_bc_manifest']); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td>File BC 1.1 / Manifest</td>
                                    <td>: <span class="text-muted"><em>Tidak ada file diupload</em></span></td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <strong class="d-block text-primary">Data Perusahaan & Kontak:</strong>
                        <table class="table table-sm table-borderless table-hover">
                            <tr><td width="40%">Nama Perusahaan</td><td>: <?= htmlspecialchars($permohonan_detail['NamaPers'] ?? '-'); ?></td></tr>
                            <tr><td>NPWP</td><td>: <?= htmlspecialchars($permohonan_detail['npwp_perusahaan'] ?? '-'); ?></td></tr>
                            <tr><td>Diajukan Oleh</td><td>: <?= htmlspecialchars($permohonan_detail['nama_pengaju_permohonan'] ?? '-'); ?></td></tr>
                            <tr><td>Email Kontak</td><td>: <?= htmlspecialchars($permohonan_detail['email_pengaju_permohonan'] ?? '-'); ?></td></tr>
                        </table>
                        <strong class="d-block text-primary mt-3">Data Angkutan:</strong>
                        <table class="table table-sm table-borderless table-hover">
                            <tr><td width="40%">Negara Asal Barang</td><td>: <?= htmlspecialchars($permohonan_detail['NegaraAsal'] ?? '-'); ?></td></tr>
                            <tr><td>Nama Kapal/Angkutan</td><td>: <?= htmlspecialchars($permohonan_detail['NamaKapal'] ?? '-'); ?></td></tr>
                            <tr><td>No. Voyage/Flight</td><td>: <?= htmlspecialchars($permohonan_detail['noVoyage'] ?? '-'); ?></td></tr>
                            <tr><td>Tgl. Perkiraan Datang</td><td>: <?= isset($permohonan_detail['TglKedatangan']) && $permohonan_detail['TglKedatangan'] != '0000-00-00' ? date('d M Y', strtotime($permohonan_detail['TglKedatangan'])) : '-'; ?></td></tr>
                            <tr><td>Tgl. Perkiraan Bongkar</td><td>: <?= isset($permohonan_detail['TglBongkar']) && $permohonan_detail['TglBongkar'] != '0000-00-00' ? date('d M Y', strtotime($permohonan_detail['TglBongkar'])) : '-'; ?></td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Detail Penugasan Pemeriksa</h6>
            </div>
             <div class="card-body">
                <?php if (!empty($permohonan_detail['nama_petugas_pemeriksa']) || !empty($permohonan_detail['NoSuratTugas'])): ?>
                    <div class="row">
                        <div class="col-lg-6 mb-3">
                            <table class="table table-sm table-borderless table-hover">
                                <tr><td width="40%">Nama Petugas</td><td>: <?= htmlspecialchars($permohonan_detail['nama_petugas_pemeriksa'] ?? 'Belum Ditunjuk'); ?></td></tr>
                                <tr><td>NIP Petugas</td><td>: <?= htmlspecialchars($permohonan_detail['nip_petugas_pemeriksa'] ?? '-'); ?></td></tr>
                                <tr><td>No. Surat Tugas</td><td>: <?= htmlspecialchars($permohonan_detail['NoSuratTugas'] ?? '-'); ?></td></tr>
                                <tr><td>Tgl. Surat Tugas</td><td>: <?= isset($permohonan_detail['TglSuratTugas']) && $permohonan_detail['TglSuratTugas'] != '0000-00-00' ? date('d M Y', strtotime($permohonan_detail['TglSuratTugas'])) : '-'; ?></td></tr>
                                <?php if (isset($permohonan_detail['FileSuratTugas']) && !empty($permohonan_detail['FileSuratTugas'])): ?>
                                    <tr><td>File Surat Tugas</td><td>: <a href="<?= base_url('uploads/surat_tugas/' . htmlspecialchars($permohonan_detail['FileSuratTugas'])); ?>" target="_blank" title="Unduh/Lihat Surat Tugas"><i class="fas fa-file-alt"></i> <?= htmlspecialchars($permohonan_detail['FileSuratTugas']); ?></a></td></tr>
                                <?php else: ?>
                                    <tr><td>File Surat Tugas</td><td>: <span class="text-muted"><em>Tidak ada file</em></span></td></tr>
                                <?php endif; ?>
                            </table>
                        </div>
                        <div class="col-lg-6 mb-3">
                            <p class="font-italic">Waktu Penunjukan Petugas: <?= isset($permohonan_detail['WaktuPenunjukanPetugas']) && $permohonan_detail['WaktuPenunjukanPetugas'] != '0000-00-00 00:00:00' ? date('d M Y H:i:s', strtotime($permohonan_detail['WaktuPenunjukanPetugas'])) : '-'; ?></p>
                            </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted"><em>Petugas pemeriksa belum ditunjuk atau data penugasan belum lengkap.</em></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Detail Laporan Hasil Pemeriksaan (LHP)</h6>
            </div>
            <div class="card-body">
                <?php if (isset($lhp_detail) && !empty($lhp_detail)): ?>
                     <?php // Kode untuk menampilkan detail LHP akan ditempatkan di sini ?>
                <?php else: ?>
                    <p class="text-muted"><em>Data LHP belum direkam atau tidak ditemukan untuk permohonan ini.</em></p>
                <?php endif; ?>
            </div>
        </div>

        <?php if (isset($permohonan_detail['status']) && ($permohonan_detail['status'] == '3' || $permohonan_detail['status'] == '4')): ?>
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Hasil Akhir & Dokumen Penyelesaian</h6>
                </div>
                <div class="card-body">
                     <?php // Kode untuk menampilkan hasil akhir akan ditempatkan di sini ?>
                </div>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="alert alert-warning" role="alert">
            Data detail permohonan tidak dapat dimuat atau tidak ditemukan.
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
