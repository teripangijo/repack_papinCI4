<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($subtitle ?? 'Pantauan Kuota Perusahaan'); ?></h1>
    </div>

    <?php if (session()->getFlashdata('message')) : ?>
        <?= session()->getFlashdata('message') ?>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Agregat Kuota per Perusahaan</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTablePantauKuotaPerusahaan" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Perusahaan (Klik untuk Detail)</th>
                            <th>Email Kontak</th>
                            <th>List SKEP Barang Aktif</th>
                            <th class="text-right">Total Kuota Awal (Unit)</th>
                            <th class="text-right">Total Sisa Kuota (Unit)</th>
                            <th class="text-right">Total Terpakai (Unit)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($perusahaan_kuota_list)): $no = 1; foreach ($perusahaan_kuota_list as $item): ?>
                        <?php
                            $total_awal = (float)($item['total_initial_kuota_all_items'] ?? 0);
                            $total_sisa = (float)($item['total_remaining_kuota_all_items'] ?? 0);
                            $total_terpakai = $total_awal - $total_sisa;
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td>
                                <a href="<?= base_url('monitoring/detail_kuota_perusahaan/' . $item['id_pers']); ?>" title="Lihat Detail Kuota untuk <?= htmlspecialchars($item['NamaPers'] ?? 'Perusahaan Ini'); ?>">
                                    <?= htmlspecialchars($item['NamaPers'] ?? 'N/A'); ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($item['user_email_kontak'] ?? '-'); ?></td>
                            <td><?= !empty($item['list_skep_aktif_barang']) ? nl2br(htmlspecialchars($item['list_skep_aktif_barang'])) : '<span class="text-muted"><em>Tidak ada SKEP aktif</em></span>'; ?></td>
                            <td class="text-right"><?= number_format($total_awal, 0, ',', '.'); ?></td>
                            <td class="text-right <?= $total_sisa <= 0 && $total_awal > 0 ? 'text-danger font-weight-bold' : ''; ?>"><?= number_format($total_sisa, 0, ',', '.'); ?></td>
                            <td class="text-right"><?= number_format($total_terpakai, 0, ',', '.'); ?></td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="7" class="text-center">Tidak ada data perusahaan dengan kuota untuk ditampilkan.</td></tr>
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
        $('#dataTablePantauKuotaPerusahaan').DataTable({
            "order": [[1, "asc"]], // Urut berdasarkan Nama Perusahaan
            "language": {
                "emptyTable": "Tidak ada data perusahaan.",
                "zeroRecords": "Tidak ada data yang cocok ditemukan",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                "infoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
                "infoFiltered": "(disaring dari _MAX_ total entri)",
                "lengthMenu": "Tampilkan _MENU_ entri",
                "search": "Cari:",
                "paginate": {
                    "first":    "Awal",
                    "last":     "Akhir",
                    "next":     "Berikutnya",
                    "previous": "Sebelumnya"
                }
            },
             "columnDefs": [
                { "orderable": false, "searchable": false, "targets": [0] } 
            ]
        });
    } else {
        console.error("jQuery atau DataTables plugin tidak termuat dengan benar untuk 'dataTablePantauKuotaPerusahaan'.");
    }
});
</script>
<?= $this->endSection() ?>
