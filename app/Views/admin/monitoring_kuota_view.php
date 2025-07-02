<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>
    <?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Monitoring Kuota'; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-chart-pie mr-2"></i><?= htmlspecialchars($subtitle ?? 'Monitoring Kuota Perusahaan'); ?></h1>
    </div>

    <?php
    // Menampilkan flash message jika ada
    if (session()->getFlashdata('message')) {
        echo session()->getFlashdata('message');
    }
    ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-database mr-2"></i>Data Agregat Kuota per Perusahaan</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableMonitoringKuota" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center">#</th>
                            <th>Nama Perusahaan</th>
                            <th><i class="fas fa-envelope mr-1"></i>Email Kontak</th>
                            <th class="text-right">Total Kuota Awal</th>
                            <th class="text-center" style="width: 20%;">Penggunaan Kuota</th>
                            <th class="text-right">Total Sisa Kuota</th>
                            <th>List No. SKEP Aktif</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($monitoring_data) && is_array($monitoring_data)): $no = 1; ?>
                            <?php foreach ($monitoring_data as $item): ?>
                            <?php
                                $total_initial = floatval($item['total_initial_kuota_barang'] ?? 0);
                                $total_remaining = floatval($item['total_remaining_kuota_barang'] ?? 0);
                                $total_used = $total_initial - $total_remaining;
                                $percentage_used = ($total_initial > 0) ? (($total_used / $total_initial) * 100) : 0;

                                $progress_bar_class = 'bg-success'; // Default green
                                if ($total_initial == 0) {
                                    $progress_bar_class = 'bg-secondary'; // Grey if initial quota is 0
                                    $percentage_used = 0; // Ensure percentage is 0 if no initial quota
                                } elseif ($total_remaining <= 0) {
                                    $progress_bar_class = 'bg-danger'; // Red if remaining quota is exhausted
                                    $percentage_used = 100; // Ensure percentage is 100 if exhausted
                                } elseif ($percentage_used > 75) {
                                    $progress_bar_class = 'bg-warning'; // Yellow if usage > 75%
                                }
                            ?>
                            <tr>
                                <td class="text-center align-middle"><?= $no++; ?></td>
                                <td class="align-middle">
                                    <?php
                                    if (isset($item['id_pers']) && !empty($item['id_pers'])) {
                                        $link_histori = base_url('admin/histori_kuota_perusahaan/' . $item['id_pers']);
                                        echo '<a href="' . $link_histori . '" title="Lihat Histori Kuota ' . htmlspecialchars($item['NamaPers'] ?? '') . '"><i class="fas fa-eye fa-fw mr-1"></i>' . htmlspecialchars($item['NamaPers'] ?? 'N/A') . '</a>';
                                    } else {
                                        echo htmlspecialchars($item['NamaPers'] ?? 'N/A');
                                    }
                                    ?>
                                </td>
                                <td class="align-middle"><?= htmlspecialchars($item['user_email'] ?? '-'); ?></td>
                                <td class="text-right align-middle">
                                    <?= htmlspecialchars(number_format($total_initial, 0, ',', '.')); ?> Unit
                                </td>
                                <td class="align-middle">
                                    <div class="progress" style="height: 22px;" title="Terpakai: <?= htmlspecialchars(number_format($total_used, 0, ',', '.')); ?> Unit (<?= number_format($percentage_used, 1) ?>%)">
                                        <div class="progress-bar progress-bar-striped <?= $progress_bar_class ?>" role="progressbar" style="width: <?= $percentage_used ?>%;" aria-valuenow="<?= $percentage_used ?>" aria-valuemin="0" aria-valuemax="100">
                                            <small><?= number_format($percentage_used, 0) ?>%</small>
                                        </div>
                                    </div>
                                    <?php if ($total_initial == 0): ?>
                                        <small class="text-muted d-block text-center">(Kuota awal 0)</small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right align-middle font-weight-bold <?= (($total_remaining <= 0) && ($total_initial > 0)) ? 'text-danger' : (($total_initial > 0) ? 'text-success' : 'text-muted'); ?>">
                                    <?= htmlspecialchars(number_format($total_remaining, 0, ',', '.')); ?> Unit
                                </td>
                                <td class="align-middle" style="min-width: 150px;">
                                    <?php
                                        if (!empty($item['list_skep_aktif'])) {
                                            $skep_list = explode(",", $item['list_skep_aktif']);
                                            foreach($skep_list as $skep_item) {
                                                if(!empty(trim($skep_item))){
                                                    echo '<span class="badge badge-info mr-1 mb-1">' . htmlspecialchars(trim($skep_item)) . '</span>';
                                                }
                                            }
                                        } else {
                                            echo '<span class="text-muted"><em>N/A</em></span>';
                                        }
                                    ?>
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
                <strong><i class="fas fa-info-circle"></i> Catatan:</strong> Klik ikon <i class="fas fa-eye fa-fw"></i> pada nama perusahaan untuk rincian histori. Persentase penggunaan dihitung dari total kuota awal.
            </small>
        </div>
    </div>

</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    console.log('Initializing DataTable for monitoring kuota...');
    
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#dataTableMonitoringKuota').DataTable({
            "order": [[1, "asc"]], // Urutkan berdasarkan Nama Perusahaan A-Z
            "language": {
                "emptyTable": "Belum ada data perusahaan untuk dimonitor.",
                "zeroRecords": "Tidak ada data yang cocok ditemukan",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                "infoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
                "infoFiltered": "(disaring dari _MAX_ total entri)",
                "lengthMenu": "Tampilkan _MENU_ entri",
                "search": "Cari:",
                "paginate": {
                    "first":    "Awal",
                    "last":     "Akhir",
                    "next":     "Lanjut",
                    "previous": "Kembali"
                }
            },
            "columnDefs": [
                { "orderable": false, "targets": [0] }, // Kolom # tidak bisa di-sort
                { "className": "align-middle", "targets": "_all" }
            ]
        });
        console.log('DataTable initialized successfully');
    } else {
        console.error("Plugin DataTables belum dimuat untuk 'dataTableMonitoringKuota'.");
    }
});
</script>
<?= $this->endSection() ?>
