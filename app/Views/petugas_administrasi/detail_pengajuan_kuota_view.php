<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= esc($subtitle ?? 'Detail Pengajuan Kuota') ?></h1>
        <div>
            <a href="<?= site_url('petugas_administrasi/daftar_pengajuan_kuota') ?>" class="btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Daftar
            </a>
            <a href="<?= site_url('petugas_administrasi/print_pengajuan_kuota/' . $pengajuan['id']) ?>" target="_blank" class="btn btn-sm btn-info shadow-sm">
                <i class="fas fa-print fa-sm text-white-50"></i> Cetak Bukti Permohonan User
            </a>
        </div>
    </div>

    <?php if (session()->getFlashdata('message')) : ?>
        <?= session()->getFlashdata('message') ?>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Pengajuan ID: <?= esc($pengajuan['id']) ?> oleh <?= esc($pengajuan['NamaPers'] ?? 'N/A') ?></h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Data Permohonan Awal</h5>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th>Nama Perusahaan</th>
                            <td>: <?= esc($pengajuan['NamaPers'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <th>Email Pemohon</th>
                            <td>: <?= esc($pengajuan['user_email_pemohon'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <th>Tanggal Pengajuan</th>
                            <td>: <?= isset($pengajuan['submission_date']) ? date('d M Y H:i:s', strtotime($pengajuan['submission_date'])) : 'N/A' ?></td>
                        </tr>
                        <tr>
                            <th>Jumlah Kuota Diajukan</th>
                            <td>: <?= esc(number_format($pengajuan['requested_quota'] ?? 0, 0, ',', '.')) ?> Unit</td>
                        </tr>
                        <tr>
                            <th style="vertical-align: top;">Alasan Pengajuan</th>
                            <td>: <?= nl2br(esc($pengajuan['reason'] ?? 'N/A')) ?></td>
                        </tr>
                         <?php if (!empty($pengajuan['file_lampiran_user'])): ?>
                        <tr>
                            <th>Lampiran User</th>
                            <td>: <a href="<?= site_url('petugas_administrasi/downloadFile/' . $pengajuan['file_lampiran_user']) ?>" target="_blank">Lihat Lampiran</a></td>
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
                                <span class="badge badge-<?= $status_badge ?>"><?= esc($status_text) ?></span>
                            </td>
                        </tr>
                        <?php if ($pengajuan['status'] == 'approved' || $pengajuan['status'] == 'rejected' || !empty($pengajuan['admin_notes'])): ?>
                            <tr>
                                <th>Tanggal Diproses Admin</th>
                                <td>: <?= isset($pengajuan['processed_date']) ? date('d M Y H:i:s', strtotime($pengajuan['processed_date'])) : 'Belum diproses' ?></td>
                            </tr>
                            <?php if ($pengajuan['status'] == 'approved'): ?>
                                <tr>
                                    <th>Kuota Disetujui</th>
                                    <td>: <?= esc(number_format($pengajuan['approved_quota'] ?? 0, 0, ',', '.')) ?> Unit</td>
                                </tr>
                                <tr>
                                    <th>Nomor SK Petugas</th>
                                    <td>: <?= esc($pengajuan['nomor_sk_petugas'] ?? '-') ?></td>
                                </tr>
                            <?php endif; ?>
                            <tr>
                                <th style="vertical-align: top;">Catatan Admin</th>
                                <td>: <?= nl2br(esc($pengajuan['admin_notes'] ?? '-')) ?></td>
                            </tr>
                            <?php if (!empty($pengajuan['file_sk_petugas'])): ?>
                            <tr>
                                <th>File SK Petugas</th>
                                <td>: <a href="<?= site_url('petugas_administrasi/downloadFile/' . $pengajuan['file_sk_petugas']) ?>" target="_blank">Lihat File SK</a></td>
                            </tr>
                            <?php endif; ?>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
            <hr>
             <?php if ($pengajuan['status'] == 'pending' || $pengajuan['status'] == 'diproses'): ?>
                <a href="<?= site_url('petugas_administrasi/proses_pengajuan_kuota/' . $pengajuan['id']) ?>" class="btn btn-primary">
                    <i class="fas fa-edit fa-sm"></i> Proses/Ubah Tindakan
                </a>
            <?php else: ?>
                 <a href="<?= site_url('petugas_administrasi/proses_pengajuan_kuota/' . $pengajuan['id']) ?>" class="btn btn-warning">
                    <i class="fas fa-edit fa-sm"></i> Lihat/Revisi Tindakan
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
