<?= $this->extend('Layouts/main') ?>

<?php
// Kalkulasi agregat dapat tetap di sini atau dipindahkan ke controller
$total_initial_agregat = 0;
$total_remaining_agregat = 0;
if (isset($daftar_kuota_barang_perusahaan) && !empty($daftar_kuota_barang_perusahaan)) {
    foreach ($daftar_kuota_barang_perusahaan as $kuota_brg) {
        $total_initial_agregat += (float)($kuota_brg['initial_quota_barang'] ?? 0);
        $total_remaining_agregat += (float)($kuota_brg['remaining_quota_barang'] ?? 0);
    }
}
$total_terpakai_agregat = $total_initial_agregat - $total_remaining_agregat;
?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= esc($subtitle ?? 'Histori & Detail Kuota Perusahaan') ?></h1>
        <a href="<?= base_url('petugas_administrasi/monitoring_kuota') ?>" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Monitoring Kuota
        </a>
    </div>

    <?php if (session()->getFlashdata('message')) { echo session()->getFlashdata('message'); } ?>

    <?php if (isset($perusahaan) && !empty($perusahaan)): ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Detail Kuota untuk: <?= esc($perusahaan['NamaPers']) ?>
                (NPWP: <?= esc($perusahaan['npwp'] ?? 'N/A') ?>)
                <br><small>Kontak User: <?= esc($perusahaan['nama_kontak_user'] ?? ($perusahaan['email_kontak'] ?? 'N/A')) ?></small>
            </h6>
        </div>
        <div class="card-body">
            <h5 class="text-gray-800">Ringkasan Total Kuota (Agregat dari Semua Jenis Barang)</h5>
            <div class="row mb-3">
                <div class="col-md-4">
                    <strong>Total Kuota Awal Diberikan:</strong> <?= esc(number_format($total_initial_agregat, 0, ',', '.')) ?> Unit
                </div>
                <div class="col-md-4">
                    <strong>Total Sisa Kuota Saat Ini:</strong>
                    <span class="font-weight-bold <?= ($total_remaining_agregat <= 0 && $total_initial_agregat > 0) ? 'text-danger' : 'text-success' ?>">
                        <?= esc(number_format($total_remaining_agregat, 0, ',', '.')) ?> Unit
                    </span>
                </div>
                <div class="col-md-4">
                    <strong>Total Kuota Terpakai:</strong> <?= esc(number_format($total_terpakai_agregat, 0, ',', '.')) ?> Unit
                </div>
            </div>
            <hr>

            <h5 class="text-gray-800 mt-4">Rincian Kuota per Jenis Barang</h5>
            <div class="table-responsive mb-4">
                <table class="table table-sm table-bordered table-hover" id="dataTableRincianKuotaBarang" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Barang</th>
                            <th class="text-right">Kuota Awal Barang</th>
                            <th class="text-right">Sisa Kuota Barang</th>
                            <th>No. SKEP Asal</th>
                            <th>Tgl. SKEP Asal</th>
                            <th>Status Kuota Barang</th>
                            <th>Waktu Pencatatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($daftar_kuota_barang_perusahaan) && !empty($daftar_kuota_barang_perusahaan)): ?>
                            <?php $no_rincian = 1; foreach ($daftar_kuota_barang_perusahaan as $kuota_brg): ?>
                            <tr>
                                <td><?= $no_rincian++ ?></td>
                                <td><?= esc($kuota_brg['nama_barang']) ?></td>
                                <td class="text-right"><?= esc(number_format($kuota_brg['initial_quota_barang'] ?? 0, 0, ',', '.')) ?></td>
                                <td class="text-right font-weight-bold <?= (($kuota_brg['remaining_quota_barang'] ?? 0) <= 0) ? 'text-danger' : 'text-success' ?>">
                                    <?= esc(number_format($kuota_brg['remaining_quota_barang'] ?? 0, 0, ',', '.')) ?>
                                </td>
                                <td><?= esc($kuota_brg['nomor_skep_asal'] ?? '-') ?></td>
                                <td><?= (isset($kuota_brg['tanggal_skep_asal']) && $kuota_brg['tanggal_skep_asal'] != '0000-00-00') ? date('d M Y', strtotime($kuota_brg['tanggal_skep_asal'])) : '-' ?></td>
                                <td>
                                    <?php
                                    $status_kb_badge = 'secondary'; $status_kb_text = ucfirst(esc($kuota_brg['status_kuota_barang'] ?? 'N/A'));
                                    if (isset($kuota_brg['status_kuota_barang'])) {
                                        if ($kuota_brg['status_kuota_barang'] == 'active') $status_kb_badge = 'success';
                                        else if ($kuota_brg['status_kuota_barang'] == 'habis') $status_kb_badge = 'danger';
                                        else if ($kuota_brg['status_kuota_barang'] == 'expired') $status_kb_badge = 'warning';
                                        else if ($kuota_brg['status_kuota_barang'] == 'canceled') $status_kb_badge = 'dark';
                                    }
                                    ?>
                                    <span class="badge badge-<?= $status_kb_badge ?>"><?= $status_kb_text ?></span>
                                </td>
                                <td><?= isset($kuota_brg['waktu_pencatatan']) ? date('d/m/Y H:i', strtotime($kuota_brg['waktu_pencatatan'])) : '-' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <hr>

            <h5 class="text-gray-800 mt-4">Log Transaksi Kuota</h5>
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover" id="dataTableHistoriTransaksiKuota" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tgl Transaksi</th>
                            <th>Jenis Transaksi</th>
                            <th>Nama Barang Terkait</th>
                            <th class="text-right">Jumlah Perubahan</th>
                            <th class="text-right">Sisa Kuota Sblm.</th>
                            <th class="text-right">Sisa Kuota Stlh.</th>
                            <th>Keterangan</th>
                            <th>Ref. ID</th>
                            <th>Dicatat Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($histori_kuota_transaksi)): $no_log = 1; ?>
                            <?php foreach ($histori_kuota_transaksi as $log): ?>
                            <tr>
                                <td><?= $no_log++ ?></td>
                                <td><?= isset($log['tanggal_transaksi']) ? date('d/m/Y H:i', strtotime($log['tanggal_transaksi'])) : '-' ?></td>
                                <td>
                                    <?php 
                                    $jenis_badge = 'secondary';
                                    if (isset($log['jenis_transaksi'])) {
                                        if ($log['jenis_transaksi'] == 'penambahan') $jenis_badge = 'success';
                                        elseif ($log['jenis_transaksi'] == 'pengurangan') $jenis_badge = 'danger';
                                        elseif ($log['jenis_transaksi'] == 'koreksi') $jenis_badge = 'warning';
                                    }
                                    echo '<span class="badge badge-'.$jenis_badge.'">'.ucfirst(esc($log['jenis_transaksi'] ?? 'N/A')).'</span>';
                                    ?>
                                </td>
                                <td><?= esc($log['nama_barang_terkait'] ?? '<span class="text-muted"><em>Umum</em></span>') ?></td>
                                <td class="text-right <?= (isset($log['jenis_transaksi']) && $log['jenis_transaksi'] == 'penambahan') ? 'text-success' : ((isset($log['jenis_transaksi']) && $log['jenis_transaksi'] == 'pengurangan') ? 'text-danger' : '') ?>">
                                    <?= (isset($log['jenis_transaksi']) && $log['jenis_transaksi'] == 'penambahan') ? '+' : ((isset($log['jenis_transaksi']) && $log['jenis_transaksi'] == 'pengurangan') ? '-' : '') ?>
                                    <?= esc(number_format(abs($log['jumlah_perubahan'] ?? 0), 0, ',', '.')) ?> Unit
                                </td>
                                <td class="text-right"><?= esc(number_format($log['sisa_kuota_sebelum'] ?? 0, 0, ',', '.')) ?></td>
                                <td class="text-right"><?= esc(number_format($log['sisa_kuota_setelah'] ?? 0, 0, ',', '.')) ?></td>
                                <td style="max-width: 300px; word-wrap: break-word;">
                                    <?= esc($log['keterangan'] ?? '-') ?>
                                    <?php if (!empty($log['id_referensi_transaksi']) && !empty($log['tipe_referensi'])): ?>
                                        <?php
                                        $link_ref = '#'; $id_ref = $log['id_referensi_transaksi'];
                                        $tipe_ref_text = ucfirst(str_replace('_', ' ', $log['tipe_referensi']));
                                        if (in_array($log['tipe_referensi'], ['pengajuan_kuota_disetujui'])) {
                                            $link_ref = base_url('petugas_administrasi/detailPengajuanKuotaAdmin/' . $id_ref);
                                        } elseif (in_array($log['tipe_referensi'], ['permohonan_impor_disetujui'])) {
                                            $link_ref = base_url('petugas_administrasi/detail_permohonan_admin/' . $id_ref);
                                        }
                                        ?>
                                        <br><small><a href="<?= $link_ref ?>" target="_blank" title="Lihat detail referensi">(Ref: <?= esc($tipe_ref_text . ' ID ' . $id_ref) ?>)</a></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($log['id_referensi_transaksi'] ?? '-') ?></td>
                                <td><?= esc($log['nama_pencatat'] ?? 'Sistem') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php else: ?>
        <div class="alert alert-warning" role="alert"> Data perusahaan tidak ditemukan atau tidak dapat diakses. </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    if(typeof $.fn.DataTable !== 'undefined'){
        const dtLang = {
            "emptyTable": "Tidak ada data yang tersedia", "zeroRecords": "Tidak ada data yang cocok ditemukan",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri", "infoEmpty": "Menampilkan 0 entri",
            "infoFiltered": "(disaring dari _MAX_ entri keseluruhan)", "lengthMenu": "Tampilkan _MENU_ entri",
            "search": "Cari:", "paginate": { "first": "Awal", "last": "Akhir", "next": "Berikutnya", "previous": "Sebelumnya" }
        };
        
        $('#dataTableRincianKuotaBarang').DataTable({
            "order": [[ 1, "asc" ]],
            "language": dtLang
        });

        $('#dataTableHistoriTransaksiKuota').DataTable({
            "order": [[ 1, "desc" ]],
            "language": dtLang
        });
    } else {
        console.error("DataTables plugin is not loaded.");
    }
});
</script>
<?= $this->endSection() ?>
