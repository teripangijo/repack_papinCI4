<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= esc($subtitle ?? 'Monitoring Kuota Perusahaan') ?></h1>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <?= session()->getFlashdata('message') ?>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Agregat Kuota Returnable Package per Perusahaan (Berdasarkan Jenis Barang)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableMonitoringKuota" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Perusahaan</th>
                            <th>Email Kontak</th>
                            <th class="text-right">Total Kuota Awal Diberikan (Per Barang)</th>
                            <th class="text-right">Total Sisa Kuota (Per Barang)</th>
                            <th class="text-right">Total Kuota Terpakai (Per Barang)</th>
                            <th>List No. SKEP Aktif Terkait Kuota Barang</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($monitoring_data) && is_array($monitoring_data)): $no = 1; ?>
                            <?php foreach ($monitoring_data as $item): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <?php if (isset($item['id_pers']) && !empty($item['id_pers'])): ?>
                                        <a href="<?= site_url('petugas_administrasi/histori_kuota_perusahaan/' . $item['id_pers']) ?>" title="Lihat Histori & Detail Kuota Barang <?= esc($item['NamaPers'] ?? '') ?>">
                                            <?= esc($item['NamaPers'] ?? 'Nama Perusahaan Tidak Ada') ?>
                                        </a>
                                    <?php else: ?>
                                        <?= esc($item['NamaPers'] ?? 'Nama Perusahaan Tidak Ada') ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($item['user_email'] ?? '-') ?></td>
                                <td class="text-right">
                                    <?= esc(number_format($item['total_initial_kuota_barang'] ?? 0, 0, ',', '.')) ?> Unit
                                </td>
                                <td class="text-right font-weight-bold <?= (($item['total_remaining_kuota_barang'] ?? 0) <= 0 && ($item['total_initial_kuota_barang'] ?? 0) > 0) ? 'text-danger' : 'text-success' ?>">
                                    <?= esc(number_format($item['total_remaining_kuota_barang'] ?? 0, 0, ',', '.')) ?> Unit
                                </td>
                                <td class="text-right">
                                    <?php
                                        $total_initial = $item['total_initial_kuota_barang'] ?? 0;
                                        $total_remaining = $item['total_remaining_kuota_barang'] ?? 0;
                                        echo esc(number_format($total_initial - $total_remaining, 0, ',', '.'));
                                    ?> Unit
                                </td>
                                <td style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= esc($item['list_skep_aktif'] ?? 'Tidak ada SKEP aktif terkait kuota barang') ?>">
                                    <?= !empty($item['list_skep_aktif']) ? esc($item['list_skep_aktif']) : '<span class="text-muted"><em>N/A</em></span>' ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">Belum ada data perusahaan untuk dimonitor.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <small class="form-text text-muted mt-2">
                <strong>Catatan:</strong> "Total Kuota Awal Diberikan" dan "Total Sisa Kuota" adalah penjumlahan dari semua kuota per jenis barang yang dimiliki perusahaan. Klik nama perusahaan untuk melihat rincian kuota per jenis barang dan histori transaksinya.
            </small>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#dataTableMonitoringKuota').DataTable({
            "order": [[1, "asc"]],
            "language": {
                "emptyTable": "Belum ada data perusahaan untuk dimonitor.",
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
                { "orderable": false, "targets": [0] }
            ]
        });
    } else {
        console.warn("DataTables plugin is not loaded for 'dataTableMonitoringKuota'.");
    }
});
</script>
<?= $this->endSection() ?>
