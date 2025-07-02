<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($subtitle ?? 'Detail Kuota Perusahaan'); ?></h1>
        <a href="<?= base_url('monitoring/pantau_kuota_perusahaan'); ?>" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Pantauan Kuota
        </a>
    </div>

    <?php if (session()->getFlashdata('message')) : ?>
        <?= session()->getFlashdata('message') ?>
    <?php endif; ?>

    <?php if (isset($perusahaan_info) && !empty($perusahaan_info)): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Informasi Perusahaan: <?= htmlspecialchars($perusahaan_info['NamaPers']); ?></h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6"><strong>NPWP:</strong> <?= htmlspecialchars($perusahaan_info['npwp'] ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Email Kontak:</strong> <?= htmlspecialchars($perusahaan_info['user_email_kontak'] ?? '-'); ?></div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6"><strong>Nama Kontak User:</strong> <?= htmlspecialchars($perusahaan_info['nama_kontak_user'] ?? '-'); ?></div>
                    <div class="col-md-6"><strong>Alamat:</strong> <?= htmlspecialchars($perusahaan_info['alamat'] ?? '-'); ?></div>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Rincian Kuota per Jenis Barang Saat Ini</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTableDetailKuotaItemsMonitor" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Barang</th>
                                <th>No. SKEP Asal</th>
                                <th>Tgl. SKEP Asal</th>
                                <th class="text-right">Kuota Awal</th>
                                <th class="text-right">Sisa Kuota</th>
                                <th class="text-right">Terpakai</th>
                                <th>Status</th>
                                <th>Admin Pencatat Kuota</th>
                                <th>Waktu Catat/Update Kuota</th>
                                <th>Catatan Admin (Kuota)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($detail_kuota_items)): $no_item = 1; foreach ($detail_kuota_items as $item_kuota): ?>
                            <?php
                                $awal_item = (float)($item_kuota['initial_quota_barang'] ?? 0);
                                $sisa_item = (float)($item_kuota['remaining_quota_barang'] ?? 0);
                                $terpakai_item = $awal_item - $sisa_item;
                                $status_kuota_info = function_exists('status_kuota_barang_text_badge') ? 
                                                        status_kuota_barang_text_badge($item_kuota['status_kuota_barang'] ?? '') : 
                                                        ['text' => ucfirst($item_kuota['status_kuota_barang'] ?? 'N/A'), 'badge' => 'secondary'];
                            ?>
                            <tr>
                                <td><?= $no_item++; ?></td>
                                <td><?= htmlspecialchars($item_kuota['nama_barang']); ?></td>
                                <td><?= htmlspecialchars($item_kuota['nomor_skep_asal'] ?? '-'); ?></td>
                                <td><?= (isset($item_kuota['tanggal_skep_asal']) && $item_kuota['tanggal_skep_asal'] != '0000-00-00') ? date('d/m/Y', strtotime($item_kuota['tanggal_skep_asal'])) : '-'; ?></td>
                                <td class="text-right"><?= number_format($awal_item, 0, ',', '.'); ?></td>
                                <td class="text-right <?= $sisa_item <= 0 && $awal_item > 0 ? 'text-danger font-weight-bold' : ''; ?>"><?= number_format($sisa_item, 0, ',', '.'); ?></td>
                                <td class="text-right"><?= number_format($terpakai_item, 0, ',', '.'); ?></td>
                                <td><span class="badge badge-<?= htmlspecialchars($status_kuota_info['badge']); ?> p-1"><?= htmlspecialchars($status_kuota_info['text']); ?></span></td>
                                <td><?= htmlspecialchars($item_kuota['nama_admin_pencatat_kuota'] ?? '-'); ?></td>
                                <td><?= (isset($item_kuota['waktu_pencatatan']) && $item_kuota['waktu_pencatatan'] != '0000-00-00 00:00:00') ? date('d/m/Y H:i', strtotime($item_kuota['waktu_pencatatan'])) : '-'; ?></td>
                                <td><?= !empty($item_kuota['catatan_admin_kuota']) ? nl2br(htmlspecialchars($item_kuota['catatan_admin_kuota'])) : '<span class="text-muted"><em>-</em></span>'; ?></td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="11" class="text-center">Tidak ada data rincian item kuota untuk perusahaan ini.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <?php if (isset($histori_transaksi_kuota)): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Histori Perubahan Kuota</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($histori_transaksi_kuota)): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped" id="dataTableHistoriKuotaMonitor" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tanggal Transaksi</th>
                                <th>Nama Barang Terkait</th>
                                <th>Jenis Transaksi</th>
                                <th class="text-right">Jumlah Perubahan</th>
                                <th class="text-right">Sisa Sebelum</th>
                                <th class="text-right">Sisa Sesudah</th>
                                <th>Keterangan</th>
                                <th>Pencatat</th>
                                <th>Ref. ID Transaksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no_log = 1; foreach($histori_transaksi_kuota as $log): ?>
                            <tr>
                                <td><?= $no_log++; ?></td>
                                <td><?= (isset($log['tanggal_transaksi']) && $log['tanggal_transaksi'] != '0000-00-00 00:00:00') ? date('d/m/Y H:i:s', strtotime($log['tanggal_transaksi'])) : '-'; ?></td>
                                <td><?= htmlspecialchars($log['nama_barang_terkait'] ?? '-'); ?></td>
                                <td>
                                    <?php 
                                    $jenis_trans_text = str_replace('_', ' ', $log['jenis_transaksi'] ?? '');
                                    echo htmlspecialchars(ucwords($jenis_trans_text)); 
                                    ?>
                                </td>
                                <td class="text-right <?= (strtolower($log['jenis_transaksi'] ?? '') == 'pengurangan' || strtolower($log['jenis_transaksi'] ?? '') == 'pemakaian') ? 'text-danger' : 'text-success'; ?>">
                                    <?= ((strtolower($log['jenis_transaksi'] ?? '') == 'pengurangan' || strtolower($log['jenis_transaksi'] ?? '') == 'pemakaian') ? '-' : '+') . number_format((float)($log['jumlah_perubahan'] ?? 0),0,',','.'); ?>
                                </td>
                                <td class="text-right"><?= number_format((float)($log['sisa_kuota_sebelum'] ?? 0),0,',','.'); ?></td>
                                <td class="text-right"><?= number_format((float)($log['sisa_kuota_setelah'] ?? 0),0,',','.'); ?></td>
                                <td><?= htmlspecialchars($log['keterangan'] ?? '-'); ?></td>
                                <td><?= htmlspecialchars($log['nama_pencatat_log'] ?? 'Sistem'); ?></td>
                                <td>
                                    <?php if(!empty($log['id_referensi_transaksi']) && !empty($log['tipe_referensi'])): ?>
                                        <?php 
                                        $link_ref = '#'; 
                                        if ($log['tipe_referensi'] == 'permohonan_impor_selesai' && !empty($log['id_referensi_transaksi'])) {
                                            $link_ref = base_url('monitoring/detail_permohonan_impor/' . $log['id_referensi_transaksi']);
                                        } elseif (($log['tipe_referensi'] == 'pengajuan_kuota_disetujui' || $log['tipe_referensi'] == 'penambahan_kuota_manual') && !empty($log['id_referensi_transaksi'])) {
                                            $link_ref = base_url('monitoring/detail_pengajuan_kuota/' . $log['id_referensi_transaksi']);
                                        }
                                        ?>
                                        <?php if ($link_ref != '#'): ?>
                                            <a href="<?= $link_ref; ?>" title="Lihat detail <?= str_replace('_',' ',$log['tipe_referensi']); ?>">
                                                <?= htmlspecialchars(ucfirst(str_replace('_',' ',$log['tipe_referensi']))); ?> #<?= htmlspecialchars($log['id_referensi_transaksi']); ?>
                                            </a>
                                        <?php else: ?>
                                            <?= htmlspecialchars(ucfirst(str_replace('_',' ',$log['tipe_referensi'] ?? '-'))); ?> <?= !empty($log['id_referensi_transaksi']) ? '#' . htmlspecialchars($log['id_referensi_transaksi']) : ''; ?>
                                        <?php endif; ?>
                                    <?php else: echo '-'; endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p class="text-muted"><em>Tidak ada histori perubahan kuota untuk perusahaan ini.</em></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="alert alert-warning" role="alert">
            Informasi detail kuota perusahaan tidak dapat dimuat atau tidak ditemukan.
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#dataTableDetailKuotaItemsMonitor').DataTable({
            "order": [[1, "asc"]], 
            "language": { id: "Bahasa Indonesia" }, // Ganti dengan bahasa yang sesuai
            "columnDefs": [
                { "orderable": false, "searchable": false, "targets": [0] }
            ]
        });
        
        $('#dataTableHistoriKuotaMonitor').DataTable({
            "order": [[1, "desc"]], // Urut berdasarkan Tanggal transaksi terbaru
             "language": { id: "Bahasa Indonesia" }, // Ganti dengan bahasa yang sesuai
             "columnDefs": [
                { "orderable": false, "searchable": false, "targets": [0] }
            ]
        });
    } else {
         console.error("jQuery atau DataTables plugin tidak termuat dengan benar.");
    }
});
</script>
<?= $this->endSection() ?>
