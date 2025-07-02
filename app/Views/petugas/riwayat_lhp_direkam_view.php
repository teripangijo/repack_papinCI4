<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= esc($subtitle ?? 'Riwayat LHP Direkam') ?></h1>
        <a href="<?= base_url('petugas/daftar_pemeriksaan') ?>" class="btn btn-sm btn-info shadow-sm">
            <i class="fas fa-tasks fa-sm text-white-50"></i> Kembali ke Tugas Pemeriksaan
        </a>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <?= session()->getFlashdata('message') ?>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Laporan Hasil Pemeriksaan yang Telah Anda Rekam</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableRiwayatLHP" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ID Permohonan</th>
                            <th>No. LHP</th>
                            <th>Tgl. LHP</th>
                            <th>Nama Barang (Permohonan)</th>
                            <th class="text-right">Jml. Disetujui (LHP)</th>
                            <th>Waktu Rekam LHP</th>
                            <th>Status Permohonan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($riwayat_lhp) && is_array($riwayat_lhp)):
                            $no = 1;
                            foreach ($riwayat_lhp as $lhp_item): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= esc($lhp_item['id_permohonan']) ?></td>
                            <td><?= esc($lhp_item['NoLHP'] ?? '-') ?></td>
                            <td><?= isset($lhp_item['TglLHP']) && $lhp_item['TglLHP'] != '0000-00-00' ? date('d/m/Y', strtotime($lhp_item['TglLHP'])) : '-' ?></td>
                            <td><?= esc($lhp_item['nama_barang_permohonan'] ?? 'N/A') ?></td>
                            <td class="text-right font-weight-bold text-success"><?= esc(number_format($lhp_item['JumlahBenar'] ?? 0, 0, ',', '.')) ?> Unit</td>
                            <td><?= isset($lhp_item['submit_time']) && $lhp_item['submit_time'] != '0000-00-00 00:00:00' ? date('d/m/Y H:i', strtotime($lhp_item['submit_time'])) : '-' ?></td>
                            <td>
                                <?php
                                $status_text = '-'; $status_badge = 'secondary';
                                if (isset($lhp_item['status_permohonan_terkini'])) {
                                    switch ($lhp_item['status_permohonan_terkini']) {
                                        case '0': $status_text = 'Baru Masuk'; $status_badge = 'dark'; break;
                                        case '5': $status_text = 'Diproses Admin'; $status_badge = 'info'; break;
                                        case '1': $status_text = 'Penunjukan Pemeriksa'; $status_badge = 'primary'; break;
                                        case '2': $status_text = 'LHP Direkam'; $status_badge = 'warning'; break;
                                        case '3': $status_text = 'Selesai (Disetujui)'; $status_badge = 'success'; break;
                                        case '4': $status_text = 'Selesai (Ditolak)'; $status_badge = 'danger'; break;
                                        default: $status_text = 'Status Tidak Dikenal (' . esc($lhp_item['status_permohonan_terkini']) . ')';
                                    }
                                }
                                echo '<span class="badge badge-pill badge-' . $status_badge . '">' . esc($status_text) . '</span>';
                                ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= base_url('petugas/detail_lhp_direkam/' . ($lhp_item['id_lhp'] ?? $lhp_item['id'])) ?>" class="btn btn-sm btn-info" title="Lihat Detail LHP">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if (isset($lhp_item['status_permohonan_terkini']) && $lhp_item['status_permohonan_terkini'] == '2'): ?>
                                    <a href="<?= base_url('petugas/rekam_lhp/' . $lhp_item['id_permohonan']) ?>" class="btn btn-sm btn-warning mt-1" title="Edit LHP">
                                        <i class="fas fa-edit"></i>
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
    if (typeof $.fn.DataTable !== 'undefined' && $('#dataTableRiwayatLHP').length) {
        $('#dataTableRiwayatLHP').DataTable({
            "order": [[ 6, "desc" ]],
            "language": {
                "emptyTable": "Anda belum pernah merekam LHP.",
                "zeroRecords": "Tidak ada data LHP yang cocok ditemukan.",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                "infoEmpty": "Menampilkan 0 entri",
                "infoFiltered": "(disaring dari _MAX_ total entri)",
                "lengthMenu": "Tampilkan _MENU_ entri",
                "search": "Cari:",
                "paginate": { "first": "Awal", "last": "Akhir", "next": "Berikutnya", "previous": "Sebelumnya" }
            },
            "columnDefs": [
                { "orderable": false, "searchable": false, "targets": [0, 8] }
            ]
        });
    } else {
        console.error("jQuery atau DataTables plugin tidak termuat untuk 'dataTableRiwayatLHP'.");
    }
});
</script>
<?= $this->endSection() ?>
