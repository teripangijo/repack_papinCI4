<?= $this->extend('layouts/main') ?> // Menggunakan layout 'main.php'
<?= $this->section('title') ?>
    <?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Detail Permohonan'; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$download_base_url = $download_url_segment ?? 'admin/downloadFile/';
?>
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($subtitle ?? 'Detail Permohonan Impor'); ?></h1>
        <div>
            <?php
            $url_kembali = base_url('admin/permohonanMasuk');
            if (isset($is_monitoring_view) && $is_monitoring_view === TRUE) {
                $url_kembali = base_url('monitoring/permohonan_impor');
            } elseif (session()->get('role_id') == 2) {
                 $url_kembali = base_url('user/daftarPermohonan');
            }
            ?>

            <?php // Dynamic action buttons based on status for Admin role ?>
            <?php if ((!isset($is_monitoring_view) || $is_monitoring_view !== TRUE) && session()->get('role_id') == 1): ?>
                <?php if (isset($permohonan_detail['status'])): ?>
                    <?php if ($permohonan_detail['status'] == '0' || $permohonan_detail['status'] == '5'): ?>
                        <a href="<?= base_url('admin/penunjukanPetugas/' . $permohonan_detail['id']); ?>" class="btn btn-sm btn-success shadow-sm mr-2">
                            <i class="fas fa-user-plus fa-sm text-white-50"></i> Proses & Tunjuk Petugas
                        </a>
                    <?php elseif ($permohonan_detail['status'] == '1'): ?>
                        <a href="<?= base_url('admin/penunjukanPetugas/' . $permohonan_detail['id']); ?>" class="btn btn-sm btn-warning shadow-sm mr-2">
                            <i class="fas fa-edit fa-sm text-white-50"></i> Lihat/Edit Penunjukan
                        </a>
                    <?php elseif ($permohonan_detail['status'] == '2'): ?>
                         <a href="<?= base_url('admin/prosesSurat/' . $permohonan_detail['id']); ?>" class="btn btn-sm btn-primary shadow-sm mr-2">
                            <i class="fas fa-flag-checkered fa-sm text-white-50"></i> Proses Penyelesaian Akhir
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>

            <a href="<?= $url_kembali; ?>" class="btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Daftar
            </a>
            <?php // Print button ?>
            <?php if (isset($permohonan_detail['id'])): ?>
            <a href="<?= base_url('user/printPdf/' . $permohonan_detail['id']); ?>" target="_blank" class="btn btn-info btn-sm ml-2"><i class="fas fa-print fa-sm"></i> Cetak Form Awal</a>
            <?php endif; ?>
        </div>
    </div>

    <?php // Flashdata messages ?>
    <?php if (session()->getFlashdata('message')) { echo session()->getFlashdata('message'); } ?>
    <?php if (session()->getFlashdata('message_error_quota')) { echo session()->getFlashdata('message_error_quota'); } ?>

    <?php if (isset($permohonan_detail) && !empty($permohonan_detail)): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Informasi Permohonan (ID Aju: <?= htmlspecialchars($permohonan_detail['id']); ?>)</h6>
                <span>
                    <?php
                    $status_text = 'Tidak Diketahui'; $status_badge = 'light';
                    if (isset($permohonan_detail['status'])) {
                        switch ($permohonan_detail['status']) {
                            case '0': $status_text = 'Baru Masuk'; $status_badge = 'dark'; break;
                            case '5': $status_text = 'Diproses Admin'; $status_badge = 'info'; break;
                            case '1': $status_text = 'Penunjukan Pemeriksa'; $status_badge = 'primary'; break;
                            case '2': $status_text = 'LHP Direkam'; $status_badge = 'warning'; break;
                            case '3': $status_text = 'Selesai (Disetujui)'; $status_badge = 'success'; break;
                            case '4': $status_text = 'Selesai (Ditolak)'; $status_badge = 'danger'; break;
                            default: $status_text = 'Status Tidak Dikenal (' . htmlspecialchars($permohonan_detail['status']) . ')';
                        }
                    }
                    echo '<span class="badge badge-pill badge-' . $status_badge . ' p-2">' . htmlspecialchars($status_text) . '</span>';
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
                            <tr><td>Jumlah Diajukan</td><td>: <?= htmlspecialchars(number_format($permohonan_detail['JumlahBarang'] ?? 0)); ?> <?= htmlspecialchars($permohonan_detail['SatuanBarang'] ?? 'Unit'); ?></td></tr>
                            <tr><td>Lokasi Bongkar</td><td>: <?= htmlspecialchars($permohonan_detail['lokasi'] ?? '-'); ?></td></tr>
                            <tr><td>No. SKEP Asal (Kuota)</td><td>: <?= htmlspecialchars($permohonan_detail['NoSkep'] ?? '-'); ?></td></tr>
                            <?php if (isset($permohonan_detail['file_bc_manifest']) && !empty($permohonan_detail['file_bc_manifest'])): ?>
                                <tr>
                                    <td>File BC 1.1 / Manifest</td>
                                    <td>:
                                        <a href="<?= base_url($download_base_url . $permohonan_detail['file_bc_manifest']); ?>" target="_blank" class="btn btn-sm btn-outline-info" title="Unduh/Lihat File BC 1.1 / Manifest">
                                            <i class="fas fa-file-download"></i> Lihat File
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
                            <tr><td>NPWP</td><td>: <?= htmlspecialchars($permohonan_detail['npwp'] ?? '-'); ?></td></tr>
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

        <?php // Section for Inspector Assignment Details ?>
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
                                    <tr><td>File Surat Tugas</td><td>: <a href="<?= base_url($download_base_url . $permohonan_detail['FileSuratTugas']); ?>" target="_blank" title="Unduh/Lihat Surat Tugas"><i class="fas fa-file-alt"></i> Lihat File</a></td></tr>
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

        <?php // Section for LHP Details ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Detail Laporan Hasil Pemeriksaan (LHP)</h6>
            </div>
            <div class="card-body">
                <?php if (isset($lhp_detail) && !empty($lhp_detail)): ?>
                     <div class="row">
                        <div class="col-lg-6 mb-3">
                             <table class="table table-sm table-borderless table-hover">
                                <tr><td width="40%">ID LHP</td><td>: <?= htmlspecialchars($lhp_detail['id_lhp'] ?? '-'); ?></td></tr>
                                <tr><td>No. LHP</td><td>: <?= htmlspecialchars($lhp_detail['NoLHP'] ?? '-'); ?></td></tr>
                                <tr><td>Tgl. LHP</td><td>: <?= isset($lhp_detail['TglLHP']) && $lhp_detail['TglLHP'] != '0000-00-00' ? date('d M Y', strtotime($lhp_detail['TglLHP'])) : '-'; ?></td></tr>
                                <tr><td>Jumlah Diajukan (Permohonan)</td><td>: <?= htmlspecialchars(number_format($lhp_detail['JumlahAju'] ?? ($permohonan_detail['JumlahBarang'] ?? 0) )); ?> <?= htmlspecialchars($permohonan_detail['SatuanBarang'] ?? 'Unit'); ?></td></tr>
                                <tr class="font-weight-bold text-success"><td>Jumlah Disetujui (LHP)</td><td>: <?= htmlspecialchars(number_format($lhp_detail['JumlahBenar'] ?? 0)); ?> <?= htmlspecialchars($permohonan_detail['SatuanBarang'] ?? 'Unit'); ?></td></tr>
                                <tr><td>Jumlah Salah (LHP)</td><td>: <?= htmlspecialchars(number_format($lhp_detail['JumlahSalah'] ?? 0)); ?> <?= htmlspecialchars($permohonan_detail['SatuanBarang'] ?? 'Unit'); ?></td></tr>
                            </table>
                        </div>
                        <div class="col-lg-6 mb-3">
                             <table class="table table-sm table-borderless table-hover">
                                <tr><td width="40%">Waktu Rekam LHP</td><td>: <?= isset($lhp_detail['submit_time']) && $lhp_detail['submit_time'] != '0000-00-00 00:00:00' ? date('d M Y H:i:s', strtotime($lhp_detail['submit_time'])) : '-'; ?></td></tr>
                                <tr><td>Catatan LHP</td><td>: <?= !empty($lhp_detail['Catatan']) ? nl2br(htmlspecialchars($lhp_detail['Catatan'])) : '<span class="text-muted"><em>Tidak ada catatan</em></span>'; ?></td></tr>
                                <?php if (isset($lhp_detail['FileLHP']) && !empty($lhp_detail['FileLHP'])): ?>
                                    <tr><td>File LHP</td><td>: <a href="<?= base_url($download_base_url . $lhp_detail['FileLHP']); ?>" target="_blank" title="Unduh/Lihat LHP"><i class="fas fa-file-pdf"></i> Lihat File</a></td></tr>
                                <?php else: ?>
                                    <tr><td>File LHP</td><td>: <span class="text-muted"><em>Tidak ada file</em></span></td></tr>
                                <?php endif; ?>
                                <?php if (isset($lhp_detail['file_dokumentasi_foto']) && !empty($lhp_detail['file_dokumentasi_foto'])): ?>
                                    <tr>
                                        <td>File Dokumentasi Foto</td>
                                        <td>
                                            <a href="<?= base_url($download_base_url . $lhp_detail['file_dokumentasi_foto']); ?>" target="_blank" title="Unduh/Lihat Dokumentasi Foto">
                                                <i class="fas fa-camera"></i> Lihat Foto
                                            </a>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td>File Dokumentasi Foto</td>
                                        <td>: <span class="text-muted"><em>Tidak ada file dokumentasi foto</em></span></td>
                                    </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted"><em>Data LHP belum direkam atau tidak ditemukan untuk permohonan ini.</em></p>
                <?php endif; ?>
            </div>
        </div>

        <?php // Section for Final Result & Settlement Documents ?>
        <?php if (isset($permohonan_detail['status']) && ($permohonan_detail['status'] == '3' || $permohonan_detail['status'] == '4')): ?>
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Hasil Akhir & Dokumen Penyelesaian</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12 mb-3">
                            <strong class="d-block text-primary">Surat Persetujuan / Penolakan:</strong>
                            <table class="table table-sm table-borderless table-hover">
                                <tr><td width="30%">No. Surat</td><td>: <?= htmlspecialchars($permohonan_detail['nomorSetuju'] ?? '-'); ?></td></tr>
                                <tr><td>Tanggal Surat</td><td>: <?= isset($permohonan_detail['tgl_S']) && $permohonan_detail['tgl_S'] != '0000-00-00' ? date('d M Y', strtotime($permohonan_detail['tgl_S'])) : '-'; ?></td></tr>
                                <?php if ($permohonan_detail['status'] == '3' && isset($permohonan_detail['file_surat_keputusan']) && !empty($permohonan_detail['file_surat_keputusan'])): ?>
                                    <tr>
                                        <td>File Surat Persetujuan</td>
                                        <td>:
                                            <a href="<?= base_url($download_base_url . $permohonan_detail['file_surat_keputusan']); ?>" target="_blank" class="btn btn-sm btn-success" title="Unduh/Lihat Surat Persetujuan">
                                                <i class="fas fa-file-download"></i> Lihat Surat Persetujuan
                                            </a>
                                        </td>
                                    </tr>
                                <?php elseif($permohonan_detail['status'] == '3'): ?>
                                    <tr><td>File Surat Persetujuan</td><td>: <span class="text-muted"><em>Tidak ada file diupload oleh Admin.</em></span></td></tr>
                                <?php endif; ?>
                                </table>
                        </div>
                        <?php if ($permohonan_detail['status'] == '4' && isset($permohonan_detail['catatan_penolakan']) && !empty($permohonan_detail['catatan_penolakan'])): ?>
                        <div class="col-lg-12 mb-3">
                            <strong class="d-block text-danger">Catatan Penolakan:</strong>
                            <div class="p-2 border bg-light rounded" style="white-space: pre-wrap;"><?= htmlspecialchars($permohonan_detail['catatan_penolakan']); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                     <p class="font-italic mt-2"><small>Waktu Penyelesaian Permohonan: <?= isset($permohonan_detail['time_selesai']) && $permohonan_detail['time_selesai'] != '0000-00-00 00:00:00' ? date('d M Y H:i:s', strtotime($permohonan_detail['time_selesai'])) : '-'; ?></small></p>
                     <p class="font-italic"><small>Diproses oleh Admin: <?= htmlspecialchars($permohonan_detail['nama_admin_pemroses'] ?? 'N/A'); ?></small></p>
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
