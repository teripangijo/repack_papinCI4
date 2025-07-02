<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($subtitle ?? 'Pantauan Data Pengajuan Kuota'); ?></h1>
    </div>

    <?php if (session()->getFlashdata('message')) : ?>
        <?= session()->getFlashdata('message') ?>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Seluruh Data Pengajuan Kuota</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableMonitorPengajuanKuota" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ID Aju</th>
                            <th>Perusahaan</th>
                            <th>Email Pengaju</th>
                            <th>Tgl. Pengajuan</th>
                            <th>Barang</th>
                            <th>Jml Diajukan</th>
                            <th>Status</th>
                            <th>Jml Disetujui</th>
                            <th>Tgl. Proses</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($daftar_pengajuan_kuota)): $no = 1; foreach ($daftar_pengajuan_kuota as $pk): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($pk['id']); ?></td>
                            <td><?= htmlspecialchars($pk['NamaPers'] ?? $pk['id_pers']); ?></td>
                            <td><?= htmlspecialchars($pk['email_pengaju_kuota'] ?? '-'); ?></td>
                            <td><?= isset($pk['submission_date']) && $pk['submission_date'] != '0000-00-00 00:00:00' ? date('d/m/Y H:i', strtotime($pk['submission_date'])) : '-'; ?></td>
                            <td><?= htmlspecialchars($pk['nama_barang_kuota'] ?? '-'); ?></td>
                            <td class="text-right"><?= number_format($pk['requested_quota'] ?? 0, 0, ',', '.'); ?></td>
                            <td>
                                <?php
                                // Pastikan helper 'repack_helper' sudah di-load di app/Config/Autoload.php atau di BaseController
                                $status_info = status_pengajuan_kuota_text_badge($pk['status'] ?? '');
                                echo '<span class="badge badge-'.htmlspecialchars($status_info['badge']).' p-2">'.htmlspecialchars($status_info['text']).'</span>';
                                ?>
                            </td>
                            <td class="text-right"><?= ($pk['status'] == 'approved' && isset($pk['approved_quota'])) ? number_format($pk['approved_quota'],0,',','.') : '-'; ?></td>
                            <td><?= isset($pk['processed_date']) && $pk['processed_date'] != '0000-00-00 00:00:00' ? date('d/m/Y H:i', strtotime($pk['processed_date'])) : '-'; ?></td>
                            <td class="text-center">
                                <a href="<?= base_url('monitoring/detail_pengajuan_kuota/' . $pk['id']); ?>" class="btn btn-sm btn-info btn-circle" title="Lihat Detail Pengajuan">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if (!empty($pk['file_sk_petugas'])): ?>
                                    <a href="<?= base_url('admin/download_sk_kuota_admin/' . $pk['id']); ?>" class="btn btn-sm btn-success btn-circle" title="Unduh SK Penetapan Kuota">
                                        <i class="fas fa-download"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="11" class="text-center">Tidak ada data pengajuan kuota.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
 $(document).ready(function() {
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#dataTableMonitorPengajuanKuota').DataTable({
            "order": [[4, "desc"]], // Urut berdasarkan Tgl. Pengajuan terbaru
            "language": { /* ... bahasa DataTables Anda ... */ },
            "columnDefs": [
                { "orderable": false, "searchable": false, "targets": [0, 10] } // Kolom # dan Action
            ]
        });
    }
});
</script>
<?= $this->endSection() ?>
