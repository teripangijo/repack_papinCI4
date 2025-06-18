<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="container-fluid">

    <h1 class="h3 mb-2 text-gray-800"><?= htmlspecialchars($subtitle ?? 'Detail Permohonan'); ?></h1>
    <p class="mb-4">Rincian lengkap dari permohonan impor kembali Anda dengan ID Aju: <strong><?= htmlspecialchars($permohonan_detail['id'] ?? 'N/A'); ?></strong></p>

    <?php if ($this->session->flashdata('message')) : ?>
        <?= $this->session->flashdata('message'); ?>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Informasi Permohonan</h6>
            <div>
                <a href="<?= site_url('user/daftarPermohonan'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left fa-sm"></i> Kembali ke Daftar</a>
                <?php ?>
                <?php if (isset($permohonan_detail['id'])): ?>
                <a href="<?= site_url('user/printPdf/' . $permohonan_detail['id']); ?>" target="_blank" class="btn btn-info btn-sm"><i class="fas fa-print fa-sm"></i> Cetak PDF Permohonan Awal</a>
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
                                    default: $status_text_user = 'Status Tidak Dikenal (' . htmlspecialchars($permohonan_detail['status']) . ')'; $status_badge_user = 'dark';
                                }
                            }
                            echo '<span class="badge badge-' . $status_badge_user . ' p-2">' . htmlspecialchars($status_text_user) . '</span>';
                            ?>
                        </h5>
                    </div>
                </div>
                <hr>

                <h5 class="mt-4 mb-3 font-weight-bold text-primary">Data Perusahaan</h5>
                <div class="row">
                    <div class="col-md-6"><strong>Nama Perusahaan:</strong> <?= htmlspecialchars($permohonan_detail['NamaPers'] ?? '-'); ?></div>
                    <div class="col-md-6"><strong>NPWP:</strong> <?= htmlspecialchars($permohonan_detail['npwp_perusahaan'] ?? '-'); ?></div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12"><strong>Alamat:</strong> <?= htmlspecialchars($permohonan_detail['alamat_perusahaan'] ?? '-'); ?></div>
                </div>
                 <div class="row mt-2">
                    <div class="col-md-6"><strong>No. SKEP Perusahaan:</strong> <?= htmlspecialchars($permohonan_detail['NoSkep_perusahaan'] ?? '-'); ?></div>
                </div>
                <hr>

                <h5 class="mt-4 mb-3 font-weight-bold text-primary">Detail Permohonan Diajukan</h5>
                <div class="row">
                    <div class="col-md-4"><strong>Nomor Surat Anda:</strong> <?= htmlspecialchars($permohonan_detail['nomorSurat'] ?? '-'); ?></div>
                    <div class="col-md-4"><strong>Tanggal Surat Anda:</strong> <?= isset($permohonan_detail['TglSurat']) && $permohonan_detail['TglSurat'] != '0000-00-00' ? date('d F Y', strtotime($permohonan_detail['TglSurat'])) : '-'; ?></div>
                    <div class="col-md-4"><strong>Perihal:</strong> <?= htmlspecialchars($permohonan_detail['Perihal'] ?? '-'); ?></div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-4"><strong>Jenis Barang:</strong> <?= htmlspecialchars($permohonan_detail['NamaBarang'] ?? '-'); ?></div>
                    <div class="col-md-4"><strong>Jumlah Diajukan:</strong> <?= htmlspecialchars(number_format($permohonan_detail['JumlahBarang'] ?? 0)); ?> <?= htmlspecialchars($permohonan_detail['SatuanBarang'] ?? 'Unit'); ?></div>
                    <?php ?>
                    <div class="col-md-4"><strong>File BC 1.1/Manifest Awal:</strong>
                        <?php if (isset($permohonan_detail['file_bc_manifest']) && !empty($permohonan_detail['file_bc_manifest'])): ?>
                            <a href="<?= base_url('uploads/bc_manifest/' . htmlspecialchars($permohonan_detail['file_bc_manifest'])); ?>" target="_blank" title="Lihat File BC 1.1 / Manifest Awal">
                                <i class="fas fa-file-alt"></i> <?= htmlspecialchars($permohonan_detail['file_bc_manifest']); ?>
                            </a>
                        <?php else: ?>
                            <span class="text-muted"><em>Tidak ada</em></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-4"><strong>Negara Asal:</strong> <?= htmlspecialchars($permohonan_detail['NegaraAsal'] ?? '-'); ?></div>
                    <div class="col-md-4"><strong>Nama Kapal/Voyage:</strong> <?= htmlspecialchars($permohonan_detail['NamaKapal'] ?? '-'); ?> / <?= htmlspecialchars($permohonan_detail['noVoyage'] ?? '-'); ?></div>
                    <div class="col-md-4"><strong>Lokasi Bongkar:</strong> <?= htmlspecialchars($permohonan_detail['lokasi'] ?? '-'); ?></div>
                </div>
                 <div class="row mt-2">
                    <div class="col-md-6"><strong>Tanggal Perkiraan Kedatangan:</strong> <?= isset($permohonan_detail['TglKedatangan']) && $permohonan_detail['TglKedatangan'] != '0000-00-00' ? date('d F Y', strtotime($permohonan_detail['TglKedatangan'])) : '-'; ?></div>
                    <div class="col-md-6"><strong>Tanggal Perkiraan Bongkar:</strong> <?= isset($permohonan_detail['TglBongkar']) && $permohonan_detail['TglBongkar'] != '0000-00-00' ? date('d F Y', strtotime($permohonan_detail['TglBongkar'])) : '-'; ?></div>
                </div>
                <hr>

                <?php if (isset($permohonan_detail['status']) && $permohonan_detail['status'] >= '1' && $permohonan_detail['status'] != '5') : ?>
                    <h5 class="mt-4 mb-3 font-weight-bold text-primary">Informasi Penugasan & Pemeriksaan</h5>
                    <div class="row">
                        <div class="col-md-6"><strong>Petugas Pemeriksa:</strong> <?= htmlspecialchars($permohonan_detail['nama_petugas_pemeriksa'] ?? 'Belum Ditunjuk/Data Tidak Tersedia'); ?></div>
                        <div class="col-md-6"><strong>No. Surat Tugas:</strong> <?= htmlspecialchars($permohonan_detail['NoSuratTugas'] ?? '-'); ?></div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6"><strong>Tgl. Surat Tugas:</strong> <?= isset($permohonan_detail['TglSuratTugas']) && $permohonan_detail['TglSuratTugas'] != '0000-00-00' ? date('d F Y', strtotime($permohonan_detail['TglSuratTugas'])) : '-'; ?></div>
                        <?php if (!empty($permohonan_detail['FileSuratTugas'])) : ?>
                            <div class="col-md-6"><strong>File Surat Tugas:</strong> <a href="<?= base_url('uploads/surat_tugas/' . htmlspecialchars($permohonan_detail['FileSuratTugas'])) ?>" target="_blank">Lihat File</a></div>
                        <?php endif; ?>
                    </div>

                    <?php if ($lhp_detail) : ?>
                        <h6 class="mt-4 mb-3 font-weight-bold" style="color: #17a2b8;">Laporan Hasil Pemeriksaan (LHP)</h6>
                        <div class="row">
                            <div class="col-md-4"><strong>No. LHP:</strong> <?= htmlspecialchars($lhp_detail['NoLHP'] ?? '-'); ?></div>
                            <div class="col-md-4"><strong>Tgl. LHP:</strong> <?= isset($lhp_detail['TglLHP']) && $lhp_detail['TglLHP'] != '0000-00-00' ? date('d F Y', strtotime($lhp_detail['TglLHP'])) : '-'; ?></div>
                            <div class="col-md-4"><strong>Hasil Pemeriksaan:</strong>
                                <?php
                                if (isset($lhp_detail['hasil'])) { echo $lhp_detail['hasil'] == 1 ? '<span class="badge badge-success">Sesuai</span>' : '<span class="badge badge-danger">Tidak Sesuai</span>'; }
                                else { echo '-'; }
                                ?>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-4"><strong>Jumlah Diajukan (Sistem):</strong> <?= htmlspecialchars(number_format($permohonan_detail['JumlahBarang'] ?? 0)); ?></div>
                            <div class="col-md-4"><strong>Jumlah Ditemukan (LHP):</strong> <?= htmlspecialchars(number_format($lhp_detail['JumlahBenar'] ?? 0)); ?></div>
                            <div class="col-md-4"><strong>Jumlah Selisih (LHP):</strong> <?= htmlspecialchars(number_format($lhp_detail['JumlahSalah'] ?? 0)); ?></div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12"><strong>Kesimpulan/Catatan LHP:</strong> <?= !empty($lhp_detail['Kesimpulan']) ? nl2br(htmlspecialchars($lhp_detail['Kesimpulan'])) : '<span class="text-muted"><em>Tidak ada</em></span>'; ?></div>
                        </div>
                         <?php if (!empty($lhp_detail['FileLHP'])) : ?>
                        <div class="row mt-2">
                            <div class="col-md-12"><strong>File LHP:</strong> <a href="<?= base_url('uploads/lhp/' . htmlspecialchars($lhp_detail['FileLHP'])) ?>" target="_blank"><i class="fas fa-file-pdf"></i> Lihat File LHP</a></div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($lhp_detail['file_dokumentasi_foto'])) : ?>
                        <div class="row mt-2">
                            <div class="col-md-12"><strong>File Dokumentasi Foto:</strong> <a href="<?= base_url('uploads/dokumentasi_lhp/' . htmlspecialchars($lhp_detail['file_dokumentasi_foto'])) ?>" target="_blank"><i class="fas fa-camera"></i> Lihat File Dokumentasi</a></div>
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="mt-3 text-muted"><em>Laporan Hasil Pemeriksaan (LHP) belum tersedia.</em></p>
                    <?php endif; ?>
                    <hr>
                <?php endif; ?>


                <?php ?>
                <?php if (isset($permohonan_detail['status']) && ($permohonan_detail['status'] == '3' || $permohonan_detail['status'] == '4')) : ?>
                    <h5 class="mt-4 mb-3 font-weight-bold text-<?= $permohonan_detail['status'] == '3' ? 'success' : 'danger'; ?>">
                        Keputusan Akhir: <?= $permohonan_detail['status'] == '3' ? 'Permohonan Disetujui' : 'Permohonan Ditolak'; ?>
                    </h5>
                    <div class="row">
                        <div class="col-md-6"><strong>No. Surat Persetujuan/Penolakan:</strong> <?= htmlspecialchars($permohonan_detail['nomorSetuju'] ?? '-'); ?></div>
                        <div class="col-md-6"><strong>Tgl. Surat Persetujuan/Penolakan:</strong> <?= isset($permohonan_detail['tgl_S']) && $permohonan_detail['tgl_S'] != '0000-00-00' ? date('d F Y', strtotime($permohonan_detail['tgl_S'])) : '-'; ?></div>
                    </div>

                    <?php ?>
                    <?php if ($permohonan_detail['status'] == '3' && isset($permohonan_detail['file_surat_keputusan']) && !empty($permohonan_detail['file_surat_keputusan'])): ?>
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <strong>File Surat Persetujuan Pengeluaran:</strong>
                            <a href="<?= base_url('uploads/sk_penyelesaian/' . htmlspecialchars($permohonan_detail['file_surat_keputusan'])); ?>" target="_blank" class="btn btn-sm btn-success ml-2">
                                <i class="fas fa-file-download"></i> Unduh <?= htmlspecialchars($permohonan_detail['file_surat_keputusan']); ?>
                            </a>
                        </div>
                    </div>
                    <?php elseif ($permohonan_detail['status'] == '3'): ?>
                    <div class="row mt-2">
                        <div class="col-md-12"><strong>File Surat Persetujuan Pengeluaran:</strong> <span class="text-muted"><em>Tidak ada file diupload oleh Admin.</em></span></div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($permohonan_detail['link'])) : ?>
                    <div class="row mt-2">
                        <div class="col-md-12"><strong>Link Dokumen Keputusan (Eksternal):</strong> <a href="<?= htmlspecialchars($permohonan_detail['link']) ?>" target="_blank"><?= htmlspecialchars($permohonan_detail['link']) ?> <i class="fas fa-external-link-alt fa-xs"></i></a></div>
                    </div>
                    <?php endif; ?>

                    <?php ?>
                    <?php if ($permohonan_detail['status'] == '4' && isset($permohonan_detail['catatan_penolakan']) && !empty($permohonan_detail['catatan_penolakan'])): ?>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <strong class="d-block text-danger">Catatan Penolakan:</strong>
                            <div class="p-2 border bg-light rounded" style="white-space: pre-wrap;"><?= htmlspecialchars($permohonan_detail['catatan_penolakan']); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($permohonan_detail['nama_admin_pemroses']) || (isset($permohonan_detail['time_selesai']) && $permohonan_detail['time_selesai'] != '0000-00-00 00:00:00')) : ?>
                    <div class="row mt-2">
                        <div class="col-md-12"><small class="text-muted"><em>Keputusan diproses oleh: <?= htmlspecialchars($permohonan_detail['nama_admin_pemroses'] ?? 'Sistem'); ?> pada <?= isset($permohonan_detail['time_selesai']) ? date('d F Y H:i', strtotime($permohonan_detail['time_selesai'])) : '-'; ?></em></small></div>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>

            <?php else: ?>
                <div class="alert alert-warning" role="alert">
                    Data detail permohonan tidak dapat dimuat atau tidak ditemukan.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>