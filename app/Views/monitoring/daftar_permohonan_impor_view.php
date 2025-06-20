<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Pantauan Data Permohonan Impor'; ?></h1>
    </div>

    <?php if (session()->getFlashdata('message')) : ?>
        <?= session()->getFlashdata('message') ?>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Seluruh Permohonan Impor Kembali</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableMonitorPermohonanImpor" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ID Aju</th>
                            <th>No Surat Pemohon</th>
                            <th>Tgl Surat</th>
                            <th>Nama Perusahaan</th>
                            <th>Email Pemohon</th>
                            <th>Waktu Submit</th>
                            <th>Petugas Ditugaskan</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($daftar_permohonan_impor) && is_array($daftar_permohonan_impor)) : ?>
                            <?php $no = 1; ?>
                            <?php foreach ($daftar_permohonan_impor as $p) : ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($p['id']); ?></td>
                                    <td><?= htmlspecialchars($p['nomorSurat'] ?? '-'); ?></td>
                                    <td><?= isset($p['TglSurat']) && $p['TglSurat'] != '0000-00-00' ? date('d/m/Y', strtotime($p['TglSurat'])) : '-'; ?></td>
                                    <td><?= htmlspecialchars($p['NamaPers'] ?? 'N/A'); ?></td>
                                    <td><?= htmlspecialchars($p['email_pemohon_impor'] ?? 'N/A'); ?></td>
                                    <td><?= isset($p['time_stamp']) && $p['time_stamp'] != '0000-00-00 00:00:00' ? date('d/m/Y H:i', strtotime($p['time_stamp'])) : '-'; ?></td>
                                    <td><?= !empty($p['nama_petugas_pemeriksa']) ? htmlspecialchars($p['nama_petugas_pemeriksa']) : '<span class="text-muted font-italic">Belum Ditunjuk</span>'; ?></td>
                                    <td>
                                        <?php
                                        // Pastikan helper 'repack_helper' sudah di-load di app/Config/Autoload.php atau di BaseController
                                        $status_info = status_permohonan_text_badge($p['status'] ?? '');
                                        echo '<span class="badge badge-pill badge-' . htmlspecialchars($status_info['badge']) . ' p-2">' . htmlspecialchars($status_info['text']) . '</span>';
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if(isset($p['id'])): ?>
                                            <a href="<?= site_url('monitoring/detail_permohonan_impor/' . $p['id']); ?>" class="btn btn-info btn-circle btn-sm my-1" title="Lihat Detail Permohonan">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr><td colspan="10" class="text-center">Belum ada data permohonan impor.</td></tr>
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
        $('#dataTableMonitorPermohonanImpor').DataTable({
            "order": [], // Gunakan urutan dari server
            "language": { /* ... bahasa DataTables Anda ... */ },
            "columnDefs": [
                { "orderable": false, "searchable": false, "targets": [0, 9] }
            ]
        });
    }
});
</script>
<?= $this->endSection() ?>
