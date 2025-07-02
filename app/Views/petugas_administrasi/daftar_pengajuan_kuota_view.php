<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= esc($subtitle ?? 'Daftar Pengajuan Kuota (Petugas Administrasi)') ?></h1>
    </div>

    <?php if (session()->getFlashdata('message')) : ?>
        <?= session()->getFlashdata('message') ?>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Pengajuan Kuota dari Perusahaan</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTablePetugasAdminPengajuanKuota" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ID Pengajuan</th>
                            <th>Nama Perusahaan</th>
                            <th>Email User</th>
                            <th>No. Surat Pengajuan</th>
                            <th>Tgl. Surat Pengajuan</th>
                            <th class="text-right">Kuota Diajukan</th>
                            <th>Alasan</th>
                            <th>Tgl. Submit Sistem</th>
                            <th>Status</th>
                            <th class="text-right">Kuota Disetujui</th>
                            <th>Catatan Petugas</th>
                            <th>Tgl. Proses</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pengajuan_kuota) && is_array($pengajuan_kuota)) : ?>
                            <?php $no = 1; ?>
                            <?php foreach ($pengajuan_kuota as $pk) : ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= esc($pk['id'] ?? '-') ?></td>
                                    <td><?= esc($pk['NamaPers'] ?? 'N/A') ?></td>
                                    <td><?= esc($pk['user_email'] ?? 'N/A') ?></td>
                                    <td><?= esc($pk['nomor_surat_pengajuan'] ?? '-') ?></td>
                                    <td><?= isset($pk['tanggal_surat_pengajuan']) && $pk['tanggal_surat_pengajuan'] != '0000-00-00' ? date('d/m/Y', strtotime($pk['tanggal_surat_pengajuan'])) : '-' ?></td>
                                    <td class="text-right"><?= isset($pk['requested_quota']) ? number_format($pk['requested_quota'],0,',','.') : '-' ?> <?= esc($pk['satuan_barang'] ?? 'Unit'); ?></td>
                                    <td><?= isset($pk['reason']) ? nl2br(esc($pk['reason'])) : '-' ?></td>
                                    <td><?= isset($pk['submission_date']) && $pk['submission_date'] != '0000-00-00 00:00:00' ? date('d/m/Y H:i', strtotime($pk['submission_date'])) : '-' ?></td>
                                    <td>
                                        <?php
                                        $status_text = ucfirst(esc($pk['status'] ?? 'N/A'));
                                        $status_badge = 'secondary';
                                        if (isset($pk['status'])) {
                                            switch (strtolower($pk['status'])) {
                                                case 'pending': $status_badge = 'warning'; $status_text = 'Pending'; break;
                                                case 'diproses': $status_badge = 'info'; $status_text = 'Diproses'; break;
                                                case 'approved': $status_badge = 'success'; $status_text = 'Disetujui'; break;
                                                case 'rejected': $status_badge = 'danger'; $status_text = 'Ditolak'; break;
                                            }
                                        }
                                        echo '<span class="badge badge-pill badge-' . $status_badge . '">' . $status_text . '</span>';
                                        ?>
                                    </td>
                                    <td class="text-right"><?= (isset($pk['status']) && strtolower($pk['status']) == 'approved' && isset($pk['approved_quota'])) ? number_format($pk['approved_quota'],0,',','.') . ' ' . esc($pk['satuan_barang'] ?? 'Unit') : '-' ?></td>
                                    <td><?= isset($pk['admin_notes']) && !empty($pk['admin_notes']) ? nl2br(esc($pk['admin_notes'])) : '-' ?></td>
                                    <td><?= isset($pk['processed_date']) && $pk['processed_date'] != '0000-00-00 00:00:00' ? date('d/m/Y H:i', strtotime($pk['processed_date'])) : '-' ?></td>
                                    <td>
                                        <a href="<?= base_url('petugas_administrasi/detailPengajuanKuotaAdmin/' . ($pk['id'] ?? '')) ?>" class="btn btn-info btn-circle btn-sm my-1" title="Lihat Detail Proses">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if (isset($pk['status']) && (strtolower($pk['status']) == 'pending' || strtolower($pk['status']) == 'diproses')) : ?>
                                            <a href="<?= base_url('petugas_administrasi/proses_pengajuan_kuota/' . ($pk['id'] ?? '')) ?>" class="btn btn-success btn-circle btn-sm my-1" title="Proses Pengajuan">
                                                <i class="fas fa-cogs"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($pk['file_sk_petugas']) && (isset($pk['status']) && (strtolower($pk['status']) == 'approved' || strtolower($pk['status']) == 'rejected'))): ?>
                                            <a href="<?= base_url('petugas_administrasi/download_sk_kuota_admin/' . ($pk['id'] ?? '')) ?>" class="btn btn-primary btn-circle btn-sm my-1" title="Unduh SK Petugas">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
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
    if (typeof $ !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
        $('#dataTablePetugasAdminPengajuanKuota').DataTable({
            "order": [[ 8, "desc" ]], 
            "language": {
                "emptyTable": "Belum ada data pengajuan kuota.", 
                "zeroRecords": "Tidak ada data yang cocok ditemukan",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                "infoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
                "infoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
                "lengthMenu": "Tampilkan _MENU_ entri",
                "search": "Cari:",
                "paginate": {
                    "first":    "Pertama",
                    "last":     "Terakhir",
                    "next":     "Selanjutnya",
                    "previous": "Sebelumnya"
                }
            },
            "columnDefs": [
                { "orderable": false, "targets": [0, 13] } 
            ]
        });
    } else {
        console.error("DataTables plugin is not loaded for 'dataTablePetugasAdminPengajuanKuota'.");
    }
});
</script>
<?= $this->endSection() ?>
