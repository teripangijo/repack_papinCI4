<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= esc($subtitle ?? 'Monitoring Permohonan Impor') ?></h1>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <?= session()->getFlashdata('message') ?>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Seluruh Permohonan</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableMonitoringPetugas" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ID Aju</th>
                            <th>No Surat Pemohon</th>
                            <th>Tgl Surat</th>
                            <th>Nama Perusahaan</th>
                            <th>Diajukan Oleh</th>
                            <th>Waktu Submit</th>
                            <th>Petugas Ditugaskan</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($permohonan_list) && is_array($permohonan_list)) : ?>
                            <?php $no = 1; ?>
                            <?php foreach ($permohonan_list as $p) : ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= esc($p['id']) ?></td>
                                    <td><?= esc($p['nomorSurat'] ?? '-') ?></td>
                                    <td><?= isset($p['TglSurat']) && $p['TglSurat'] != '0000-00-00' ? date('d/m/Y', strtotime($p['TglSurat'])) : '-' ?></td>
                                    <td><?= esc($p['NamaPers'] ?? 'N/A') ?></td>
                                    <td><?= esc($p['nama_pengaju_permohonan'] ?? 'N/A') ?></td>
                                    <td><?= isset($p['time_stamp']) && $p['time_stamp'] != '0000-00-00 00:00:00' ? date('d/m/Y H:i', strtotime($p['time_stamp'])) : '-' ?></td>
                                    <td><?= !empty($p['nama_petugas_pemeriksa']) ? esc($p['nama_petugas_pemeriksa']) : '<span class="text-muted font-italic">Belum Ditunjuk</span>' ?></td>
                                    <td>
                                        <?php
                                        $status_text = '-'; $status_badge = 'secondary';
                                        if (isset($p['status'])) {
                                            switch ($p['status']) {
                                                case '0': $status_text = 'Baru Masuk'; $status_badge = 'dark'; break;
                                                case '5': $status_text = 'Diproses Admin'; $status_badge = 'info'; break;
                                                case '1': $status_text = 'Penunjukan Pemeriksa'; $status_badge = 'primary'; break;
                                                case '2': $status_text = 'LHP Direkam'; $status_badge = 'warning'; break;
                                                case '3': $status_text = 'Selesai (Disetujui)'; $status_badge = 'success'; break;
                                                case '4': $status_text = 'Selesai (Ditolak)'; $status_badge = 'danger'; break;
                                                default: $status_text = 'Status Tidak Dikenal (' . esc($p['status']) . ')';
                                            }
                                        }
                                        echo '<span class="badge badge-pill badge-' . $status_badge . ' p-2">' . esc($status_text) . '</span>';
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if(isset($p['id'])): ?>
                                            <a href="<?= base_url('petugas/detail_monitoring_permohonan/' . $p['id']) ?>" class="btn btn-info btn-circle btn-sm my-1" title="Lihat Detail Permohonan">
                                                <i class="fas fa-eye"></i>
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
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#dataTableMonitoringPetugas').DataTable({
            "order": [], // Let the server-side logic dictate the initial order
            "language": {
                "emptyTable": "Tidak ada data permohonan untuk dimonitor.",
                "zeroRecords": "Tidak ada data yang cocok ditemukan",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                "infoEmpty": "Menampilkan 0 entri",
                "infoFiltered": "(disaring dari _MAX_ total entri)",
                "lengthMenu": "Tampilkan _MENU_ entri",
                "search": "Cari:",
                "paginate": { "first": "Awal", "last": "Akhir", "next": "Berikutnya", "previous": "Sebelumnya" }
            },
             "columnDefs": [
                 { "orderable": false, "searchable": false, "targets": [0, 9] }
             ]
        });
    }
});
</script>
<?= $this->endSection() ?>
