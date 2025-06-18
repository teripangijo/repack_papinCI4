<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
    <?= esc($subtitle ?? 'Daftar Permohonan') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= esc($subtitle ?? 'Daftar Permohonan Impor Kembali Saya') ?></h1>
        <a href="<?= site_url('user/permohonan_impor_kembali') ?>" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Buat Permohonan Baru
        </a>
    </div>

    <!-- Flash messages akan ditangani oleh layout/main.php -->

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Riwayat Permohonan Impor Kembali</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTablePermohonanUser" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ID Aju</th>
                            <th>No. Surat</th>
                            <th>Tgl Surat</th>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                            <th>Waktu Submit</th>
                            <th>Status</th>
                            <th style="min-width: 120px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($permohonan) && is_array($permohonan)): ?>
                            <?php $no = 1; foreach ($permohonan as $p): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= esc($p['id']) ?></td>
                                    <td><?= esc($p['nomorSurat'] ?? '-') ?></td>
                                    <td><?= isset($p['TglSurat']) && $p['TglSurat'] != '0000-00-00' ? esc(date('d/m/Y', strtotime($p['TglSurat']))) : '-' ?></td>
                                    <td><?= esc($p['NamaBarang'] ?? '-') ?></td>
                                    <td><?= esc(number_format($p['JumlahBarang'] ?? 0)) ?></td>
                                    <td><?= isset($p['time_stamp']) && $p['time_stamp'] != '0000-00-00 00:00:00' ? esc(date('d/m/Y H:i', strtotime($p['time_stamp']))) : '-' ?></td>
                                    <td>
                                        <?php
                                        // This block generates safe HTML (a Bootstrap badge)
                                        $status_text = '-'; $status_badge = 'secondary';
                                        if (isset($p['status'])) {
                                            switch ($p['status']) {
                                                case '0': $status_text = 'Baru Masuk'; $status_badge = 'dark'; break;
                                                case '5': $status_text = 'Diproses Admin'; $status_badge = 'info'; break;
                                                case '1': $status_text = 'Petugas Ditunjuk'; $status_badge = 'primary'; break;
                                                case '2': $status_text = 'LHP Direkam'; $status_badge = 'warning'; break;
                                                case '3': $status_text = 'Selesai (Disetujui)'; $status_badge = 'success'; break;
                                                case '4': $status_text = 'Selesai (Ditolak)'; $status_badge = 'danger'; break;
                                                default: $status_text = 'Status Tidak Dikenal (' . esc($p['status']) . ')';
                                            }
                                        }
                                        echo '<span class="badge badge-pill badge-' . $status_badge . '">' . esc($status_text) . '</span>';
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= site_url('user/detailPermohonan/' . $p['id']) ?>" class="btn btn-info btn-circle btn-sm my-1" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php
                                        $deletable_import_statuses = ['0', '5'];
                                        if (isset($p['status']) && in_array($p['status'], $deletable_import_statuses)):
                                        ?>
                                            <a href="<?= site_url('user/editpermohonan/' . $p['id']) ?>" class="btn btn-warning btn-circle btn-sm my-1" title="Edit Permohonan">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>
                                            <a href="<?= site_url('user/hapus_permohonan_impor/' . $p['id']) ?>" class="btn btn-danger btn-circle btn-sm my-1" title="Hapus Permohonan" onclick="return confirm('Apakah Anda yakin ingin menghapus permohonan impor dengan ID Aju: <?= esc($p['id']) ?> ini?');">
                                                <i class="fas fa-trash"></i>
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

<!-- Script dipindahkan ke layout utama atau section script jika diperlukan -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof $ !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
        $('#dataTablePermohonanUser').DataTable({
            "order": [[ 6, "desc" ]], // Urutkan berdasarkan Waktu Submit terbaru
            "language": { /* sesuaikan bahasa jika perlu */ },
            "columnDefs": [
                { "orderable": false, "searchable": false, "targets": [0, 8] } // Kolom # dan Action
            ]
        });
    }
});
</script>
<?= $this->endSection() ?>
