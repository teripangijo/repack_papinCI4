<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
    <?= esc($subtitle ?? 'Dashboard') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= esc($subtitle ?? 'Dashboard') ?></h1>
        <?php if (isset($user_perusahaan) && !empty($user_perusahaan) && ($user['is_active'] ?? 0) == 1) : ?>
            <a href="<?= base_url('user/permohonan_impor_kembali') ?>" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Buat Permohonan Impor Kembali
            </a>
        <?php endif; ?>
    </div>

    <!-- Flash messages akan ditangani oleh layout/main.php -->
    
    <?php if (isset($user['is_active']) && $user['is_active'] == 0 && empty($user_perusahaan)) : ?>
        <div class="alert alert-warning" role="alert">
            <h4 class="alert-heading">Akun Belum Aktif!</h4>
            <p>Untuk mengaktifkan akun Anda dan mulai menggunakan layanan, mohon lengkapi data profil dan perusahaan Anda.</p>
            <hr>
            <p class="mb-0"><a href="<?= base_url('user/edit') ?>" class="btn btn-primary">Lengkapi Profil Sekarang</a></p>
        </div>
    <?php else : ?>
        <div class="row">
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Kuota Awal Disetujui (Semua Barang)</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= esc(number_format($total_kuota_awal_disetujui_barang ?? 0, 0, ',', '.')) ?> Unit
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-cubes fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total Sisa Kuota (Semua Barang)</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= esc(number_format($total_sisa_kuota_barang ?? 0, 0, ',', '.')) ?> Unit
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-cube fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Kuota Terpakai (Semua Barang)
                                </div>
                                <div class="row no-gutters align-items-center">
                                    <div class="col-auto">
                                        <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                            <?= esc(number_format($total_kuota_terpakai_barang ?? 0, 0, ',', '.')) ?> Unit
                                        </div>
                                    </div>
                                    </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-dolly-flatbed fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($daftar_kuota_per_barang) && !empty($daftar_kuota_per_barang)): ?>
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Rincian Sisa Kuota Aktif per Jenis Barang</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover" id="dataTableRincianKuotaDashboard">
                                <thead>
                                    <tr>
                                        <th>Nama Barang</th>
                                        <th class="text-right">Kuota Awal Diberikan</th>
                                        <th class="text-right">Sisa Kuota</th>
                                        <th>No. SKEP Asal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($daftar_kuota_per_barang as $kuota_brg): ?>
                                    <tr>
                                        <td><?= esc($kuota_brg['nama_barang']) ?></td>
                                        <td class="text-right"><?= esc(number_format($kuota_brg['initial_quota_barang'] ?? 0, 0, ',', '.')) ?></td>
                                        <td class="text-right font-weight-bold text-success"><?= esc(number_format($kuota_brg['remaining_quota_barang'] ?? 0, 0, ',', '.')) ?></td>
                                        <td><?= esc($kuota_brg['nomor_skep_asal'] ?? '-') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                         <small class="form-text text-muted">Untuk mengajukan penambahan kuota barang tertentu, silakan ke menu "Pengajuan Kuota".</small>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>


        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Ringkasan Permohonan Impor Kembali Terbaru</h6>
                        <a href="<?= base_url('user/daftarPermohonan') ?>">Lihat Semua Permohonan &rarr;</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_permohonan)) : ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-sm">
                                    <thead>
                                        <tr>
                                            <th>No Surat</th>
                                            <th>Tgl Surat</th>
                                            <th>Nama Barang</th>
                                            <th class="text-right">Jumlah</th>
                                            <th>Status</th>
                                            <th>Waktu Submit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_permohonan as $p) : ?>
                                            <tr>
                                                <td><?= esc($p['nomorSurat'] ?? '-') ?></td>
                                                <td><?= isset($p['TglSurat']) && $p['TglSurat'] != '0000-00-00' ? esc(date('d/m/Y', strtotime($p['TglSurat']))) : '-' ?></td>
                                                <td><?= esc($p['NamaBarang'] ?? '-') ?></td>
                                                <td class="text-right"><?= esc(number_format($p['JumlahBarang'] ?? 0, 0, ',', '.')) ?> Unit</td>
                                                <td>
                                                    <?php
                                                    // This block generates safe HTML (a Bootstrap badge)
                                                    $status_text = '-'; $status_badge = 'secondary';
                                                    if (isset($p['status'])) {
                                                        switch ($p['status']) {
                                                            case '0': $status_text = 'Baru Masuk'; $status_badge = 'dark'; break;
                                                            case '5': $status_text = 'Diproses Admin'; $status_badge = 'info'; break;
                                                            case '1': $status_text = 'Penunjukan Petugas'; $status_badge = 'primary'; break;
                                                            case '2': $status_text = 'LHP Direkam'; $status_badge = 'warning'; break;
                                                            case '3': $status_text = 'Selesai (Disetujui)'; $status_badge = 'success'; break;
                                                            case '4': $status_text = 'Selesai (Ditolak)'; $status_badge = 'danger'; break;
                                                            default: $status_text = 'N/A';
                                                        }
                                                    }
                                                    echo '<span class="badge badge-pill badge-' . $status_badge . '">' . esc($status_text) . '</span>';
                                                    ?>
                                                </td>
                                                <td><?= isset($p['time_stamp']) ? esc(date('d/m/Y H:i', strtotime($p['time_stamp']))) : '-' ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else : ?>
                            <p class="text-center text-muted">Belum ada data permohonan impor kembali terbaru.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<!-- Script dipindahkan ke layout utama atau section script jika diperlukan -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof $ !== 'undefined' && typeof $.fn.DataTable !== 'undefined' && $('#dataTableRincianKuotaDashboard').length) {
        $('#dataTableRincianKuotaDashboard').DataTable({
            "order": [[0, "asc"]], // Urut berdasarkan Nama Barang
            "pageLength": 5,
            "lengthMenu": [ [5, 10, -1], [5, 10, "Semua"] ],
            "searching": false, 
            "info": false, 
            "language": { "emptyTable": "Tidak ada rincian kuota aktif per barang." }
        });
    }
});
</script>
<?= $this->endSection() ?>
