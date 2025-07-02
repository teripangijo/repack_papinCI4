<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($subtitle ?? 'Detail Pantauan Pengajuan Kuota'); ?></h1>
        <a href="<?= base_url('monitoring/pengajuan_kuota'); ?>" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Daftar Pantauan
        </a>
    </div>

    <?php if (session()->getFlashdata('message')) : ?>
        <?= session()->getFlashdata('message') ?>
    <?php endif; ?>

    <?php if (isset($pengajuan) && !empty($pengajuan)): ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Pengajuan Kuota ID: <?= htmlspecialchars($pengajuan['id']); ?> oleh <?= htmlspecialchars($pengajuan['NamaPers'] ?? 'N/A'); ?></h6>
                    <?php
                        // Pastikan helper 'repack_helper' dimuat
                        $status_info = status_pengajuan_kuota_text_badge($pengajuan['status'] ?? ''); 
                        echo '<span class="badge badge-'.htmlspecialchars($status_info['badge']).' p-2">'.htmlspecialchars($status_info['text']).'</span>';
                    ?>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="font-weight-bold text-secondary">Informasi Perusahaan</h6>
                            <table class="table table-sm table-borderless">
                                <tr><td width="40%">Nama Perusahaan</td><td>: <?= htmlspecialchars($pengajuan['NamaPers'] ?? 'N/A'); ?></td></tr>
                                <tr><td>NPWP</td><td>: <?= htmlspecialchars($pengajuan['npwp_perusahaan'] ?? 'N/A'); ?></td></tr>
                                <tr><td>Alamat</td><td>: <?= htmlspecialchars($pengajuan['alamat_perusahaan'] ?? 'N/A'); ?></td></tr>
                                <tr><td>Diajukan Oleh (User)</td><td>: <?= htmlspecialchars($pengajuan['nama_pengaju_kuota'] ?? 'N/A'); ?></td></tr>
                                <tr><td>Email User</td><td>: <?= htmlspecialchars($pengajuan['email_pengaju_kuota'] ?? 'N/A'); ?></td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="font-weight-bold text-secondary">Detail Pengajuan</h6>
                            <table class="table table-sm table-borderless">
                                <tr><td width="40%">Nomor Surat Pengajuan</td><td>: <?= htmlspecialchars($pengajuan['nomor_surat_pengajuan'] ?? '-'); ?></td></tr>
                                <tr><td>Tanggal Surat</td><td>: <?= isset($pengajuan['tanggal_surat_pengajuan']) && $pengajuan['tanggal_surat_pengajuan'] != '0000-00-00' ? date('d F Y', strtotime($pengajuan['tanggal_surat_pengajuan'])) : '-'; ?></td></tr>
                                <tr><td>Perihal</td><td>: <?= htmlspecialchars($pengajuan['perihal_pengajuan'] ?? '-'); ?></td></tr>
                                <tr><td>Nama Barang Diajukan</td><td>: <?= htmlspecialchars($pengajuan['nama_barang_kuota'] ?? '-'); ?></td></tr>
                                <tr><td>Jumlah Kuota Diajukan</td><td>: <?= htmlspecialchars(number_format($pengajuan['requested_quota'] ?? 0)); ?> Unit</td></tr>
                                <tr><td>Alasan Pengajuan</td><td>: <?= nl2br(htmlspecialchars($pengajuan['reason'] ?? '-')); ?></td></tr>
                                <tr><td>Tanggal Submit Sistem</td><td>: <?= isset($pengajuan['submission_date']) && $pengajuan['submission_date'] != '0000-00-00 00:00:00' ? date('d F Y H:i:s', strtotime($pengajuan['submission_date'])) : '-'; ?></td></tr>
                                <?php if (!empty($pengajuan['file_lampiran_user'])): ?>
                                <tr><td>File Lampiran Pengguna</td><td>: <a href="<?= base_url('uploads/lampiran_kuota/' . htmlspecialchars($pengajuan['file_lampiran_user'])); ?>" target="_blank"><i class="fas fa-paperclip"></i> <?= htmlspecialchars($pengajuan['file_lampiran_user']); ?></a></td></tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                    <hr>
                    <h6 class="font-weight-bold text-secondary">Informasi Pemrosesan oleh Admin</h6>
                    <?php if ($pengajuan['status'] != 'pending'): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr><td width="40%">Diproses oleh Admin</td><td>: <?= htmlspecialchars($pengajuan['nama_admin_pemroses_kuota'] ?? 'N/A'); ?></td></tr>
                                    <tr><td>Tanggal Diproses</td><td>: <?= isset($pengajuan['processed_date']) && $pengajuan['processed_date'] != '0000-00-00 00:00:00' ? date('d F Y H:i:s', strtotime($pengajuan['processed_date'])) : 'Belum diproses'; ?></td></tr>
                                    <tr><td>Status Akhir</td><td><span class="badge badge-<?= htmlspecialchars($status_info['badge']); ?> p-1"><?= htmlspecialchars($status_info['text']); ?></span></td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <?php if ($pengajuan['status'] == 'approved'): ?>
                                    <table class="table table-sm table-borderless">
                                        <tr><td width="40%">Kuota Disetujui</td><td>: <?= htmlspecialchars(number_format($pengajuan['approved_quota'] ?? 0)); ?> Unit</td></tr>
                                        <tr><td>No. SK Penetapan</td><td>: <?= htmlspecialchars($pengajuan['nomor_sk_petugas'] ?? '-'); ?></td></tr>
                                        <tr><td>Tanggal SK Penetapan</td><td>: <?= isset($pengajuan['tanggal_sk_petugas']) && $pengajuan['tanggal_sk_petugas'] != '0000-00-00' ? date('d F Y', strtotime($pengajuan['tanggal_sk_petugas'])) : '-'; ?></td></tr>
                                        <?php if (!empty($pengajuan['file_sk_petugas'])): ?>
                                        <tr><td>File SK Penetapan</td><td>: <a href="<?= base_url('uploads/sk_kuota/' . htmlspecialchars($pengajuan['file_sk_petugas'])); ?>" target="_blank"><i class="fas fa-download"></i> <?= htmlspecialchars($pengajuan['file_sk_petugas']); ?></a></td></tr>
                                        <?php endif; ?>
                                    </table>
                                <?php endif; ?>
                                <p><strong>Catatan Admin:</strong> <?= !empty($pengajuan['admin_notes']) ? nl2br(htmlspecialchars($pengajuan['admin_notes'])) : '<span class="text-muted"><em>Tidak ada catatan</em></span>'; ?></p>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-muted"><em>Pengajuan ini belum diproses oleh Administrator.</em></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
        <div class="alert alert-warning" role="alert">
            Detail pengajuan kuota tidak ditemukan.
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
