<?= $this->extend('layouts/main') ?> // Menggunakan layout 'main.php'
<?= $this->section('title') ?>
    <?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Detail Pengajuan Kuota'; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($subtitle ?? 'Detail Pengajuan Kuota'); ?></h1>
        <div>
            <a href="<?= base_url('admin/daftar_pengajuan_kuota'); ?>" class="btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Daftar
            </a>
            <a href="<?= base_url('admin/print_pengajuan_kuota/' . $pengajuan['id']); ?>" target="_blank" class="btn btn-sm btn-info shadow-sm">
                <i class="fas fa-print fa-sm text-white-50"></i> Cetak Bukti Permohonan User
            </a>
             </div>
    </div>

    <?php if (session()->getFlashdata('message')) : ?>
        <?= session()->getFlashdata('message'); ?>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Pengajuan ID: <?= htmlspecialchars($pengajuan['id']); ?> oleh <?= htmlspecialchars($pengajuan['NamaPers'] ?? 'N/A'); ?></h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Data Permohonan Awal</h5>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th>Nama Perusahaan</th>
                            <td>: <?= htmlspecialchars($pengajuan['NamaPers'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th>Email Pemohon</th>
                            <td>: <?= htmlspecialchars($pengajuan['user_email_pemohon'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <th>Tanggal Pengajuan</th>
                            <td>: <?= isset($pengajuan['submission_date']) ? date('d M Y H:i:s', strtotime($pengajuan['submission_date'])) : 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <th>Jumlah Kuota Diajukan</th>
                            <td>: <?= htmlspecialchars(number_format($pengajuan['requested_quota'] ?? 0, 0, ',', '.')); ?> Unit</td>
                        </tr>
                        <tr>
                            <th style="vertical-align: top;">Alasan Pengajuan</th>
                            <td>: <?= nl2br(htmlspecialchars($pengajuan['reason'] ?? 'N/A')); ?></td>
                        </tr>
                         <?php if (!empty($pengajuan['file_lampiran_user'])): ?>
                        <tr>
                            <th>Lampiran User</th>
                            <td>: <a href="<?= base_url('user/download_lampiran_kuota/' . $pengajuan['id']); ?>" target="_blank"><?= htmlspecialchars($pengajuan['file_lampiran_user']); ?></a></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5>Status & Tindakan Admin</h5>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th>Status Saat Ini</th>
                            <td>:
                                <?php
                                $status_text = 'N/A';
                                $status_badge = 'secondary';
                                switch ($pengajuan['status'] ?? '') {
                                    case 'pending': $status_text = 'Pending'; $status_badge = 'warning'; break;
                                    case 'diproses': $status_text = 'Diproses Petugas'; $status_badge = 'info'; break;
                                    case 'approved': $status_text = 'Approved (Disetujui)'; $status_badge = 'success'; break;
                                    case 'rejected': $status_text = 'Rejected (Ditolak)'; $status_badge = 'danger'; break;
                                }
                                ?>
                                <span class="badge badge-<?= $status_badge; ?>"><?= htmlspecialchars($status_text); ?></span>
                            </td>
                        </tr>
                        <?php if ($pengajuan['status'] == 'approved' || $pengajuan['status'] == 'rejected' || !empty($pengajuan['admin_notes'])): ?>
                            <tr>
                                <th>Tanggal Diproses Admin</th>
                                <td>: <?= isset($pengajuan['processed_date']) ? date('d M Y H:i:s', strtotime($pengajuan['processed_date'])) : 'Belum diproses'; ?></td>
                            </tr>
                            <?php if ($pengajuan['status'] == 'approved'): ?>
                                <tr>
                                    <th>Kuota Disetujui</th>
                                    <td>: <?= htmlspecialchars(number_format($pengajuan['approved_quota'] ?? 0, 0, ',', '.')); ?> Unit</td>
                                </tr>
                                <tr>
                                    <th>Nomor SK Petugas</th>
                                    <td>: <?= htmlspecialchars($pengajuan['nomor_sk_petugas'] ?? '-'); ?></td>
                                </tr>
                            <?php endif; ?>
                            <tr>
                                <th style="vertical-align: top;">Catatan Admin</th>
                                <td>: <?= nl2br(htmlspecialchars($pengajuan['admin_notes'] ?? '-')); ?></td>
                            </tr>
                            <?php if (!empty($pengajuan['file_sk_petugas'])): ?>
                            <tr>
                                <th>File SK Petugas</th>
                                <td>: <a href="<?= base_url('admin/download_sk_kuota_admin/' . $pengajuan['id']); ?>" target="_blank"><?= htmlspecialchars($pengajuan['file_sk_petugas']); ?></a></td>
                            </tr>
                            <?php endif; ?>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
            <hr>
             <?php if ($pengajuan['status'] == 'pending' || $pengajuan['status'] == 'diproses'): ?>
                <a href="<?= base_url('admin/proses_pengajuan_kuota/' . $pengajuan['id']); ?>" class="btn btn-primary">
                    <i class="fas fa-edit fa-sm"></i> Proses/Ubah Tindakan
                </a>
            <?php else: ?>
                 <a href="<?= base_url('admin/proses_pengajuan_kuota/' . $pengajuan['id']); ?>" class="btn btn-warning">
                    <i class="fas fa-edit fa-sm"></i> Lihat/Revisi Tindakan
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>