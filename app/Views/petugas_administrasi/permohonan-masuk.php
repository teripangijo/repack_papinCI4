<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= esc($subtitle ?? 'Daftar Permohonan Impor') ?></h1>
    </div>

    <!-- <?php if (session()->getFlashdata('message')): ?>
        <?= session()->getFlashdata('message') ?>
    <?php endif; ?> -->

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Data Permohonan Masuk</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTablePermohonanAdmin" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ID Aju</th>
                            <th>No Surat Pemohon</th>
                            <th>Tgl Surat Pemohon</th>
                            <th>Nama Perusahaan</th>
                            <th>Diajukan Oleh</th>
                            <th>Waktu Submit</th>
                            <th>Petugas Ditugaskan</th>
                            <th>Status</th>
                            <th style="min-width: 160px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($permohonan) && is_array($permohonan)) : ?>
                            <?php $no = 1; ?>
                            <?php foreach ($permohonan as $p) : ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= esc($p['id']) ?></td>
                                    <td><?= esc($p['nomorSurat'] ?? '-') ?></td>
                                    <td><?= isset($p['TglSurat']) && $p['TglSurat'] != '0000-00-00' ? date('d/m/Y', strtotime($p['TglSurat'])) : '-' ?></td>
                                    <td><?= esc($p['NamaPers'] ?? 'N/A') ?></td>
                                    <td><?= esc($p['nama_pengaju'] ?? 'N/A') ?></td>
                                    <td><?= isset($p['time_stamp']) && $p['time_stamp'] != '0000-00-00 00:00:00' ? date('d/m/Y H:i', strtotime($p['time_stamp'])) : '-' ?></td>
                                    <td><?= !empty($p['nama_petugas_assigned']) ? esc($p['nama_petugas_assigned']) : '<span class="text-muted font-italic">Belum Ditunjuk</span>' ?></td>
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
                                                case '6': $status_text = 'Ditolak oleh Admin'; $status_badge = 'danger'; break;
                                                default: $status_text = 'Status Tidak Dikenal (' . esc($p['status']) . ')';
                                            }
                                        }
                                        echo '<span class="badge badge-pill badge-' . $status_badge . ' p-2">' . esc($status_text) . '</span>';
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if(isset($p['id'])): ?>
                                            <a href="<?= base_url('petugas_administrasi/detail_permohonan_admin/' . $p['id']) ?>" class="btn btn-info btn-circle btn-sm my-1" title="Lihat Detail Permohonan Lengkap">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php
                                            if (isset($p['status'])) {
                                                switch ($p['status']) {
                                                    case '0': 
                                                    case '5': 
                                                        echo '<a href="' . base_url('petugas_administrasi/penunjukanPetugas/' . $p['id']) . '" class="btn btn-success btn-circle btn-sm my-1" title="Proses & Tunjuk Petugas Pemeriksa"><i class="fas fa-user-plus"></i></a>';
                                                        echo '<a href="' . base_url('petugas_administrasi/tolak_permohonan_awal/' . $p['id']) . '" class="btn btn-warning btn-circle btn-sm my-1" title="Tolak Langsung Permohonan"><i class="fas fa-ban"></i></a>';
                                                        break;
                                                    case '1':
                                                        echo '<a href="' . base_url('petugas_administrasi/penunjukanPetugas/' . $p['id']) . '" class="btn btn-warning btn-circle btn-sm my-1" title="Lihat/Edit Penunjukan Petugas"><i class="fas fa-user-edit"></i></a>';
                                                        break;
                                                    case '2':
                                                        echo '<a href="' . base_url('petugas_administrasi/prosesSurat/' . $p['id']) . '" class="btn btn-primary btn-circle btn-sm my-1" title="Proses Penyelesaian Akhir Permohonan"><i class="fas fa-flag-checkered"></i></a>';
                                                        break;
                                                }
                                            }
                                            if (isset($p['status']) && in_array($p['status'], ['0', '5', '1'])) {
                                                echo '<a href="' . base_url('petugas_administrasi/edit_permohonan/' . $p['id']) . '" class="btn btn-secondary btn-circle btn-sm my-1" title="Edit Data Pengajuan Permohonan"><i class="fas fa-pencil-alt"></i></a>';
                                            }
                                            if (isset($p['status']) && in_array($p['status'], ['0', '5', '6'])) { 
                                                $confirm_msg = "APAKAH ANDA YAKIN ingin menghapus permohonan dengan ID Aju: " . esc($p['id']) . " atas nama " . esc($p['NamaPers'] ?? 'N/A') . "?\\nTindakan ini tidak dapat diurungkan!";
                                                echo '<a href="' . base_url('petugas_administrasi/hapus_permohonan/' . $p['id']) . '" class="btn btn-danger btn-circle btn-sm my-1" title="Hapus Permohonan" onclick="return confirm(\''.$confirm_msg.'\');"><i class="fas fa-trash"></i></a>';
                                            }
                                            ?>
                                        <?php else: ?>
                                            <span class="text-muted">ID Error</span>
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
        $('#dataTablePermohonanAdmin').DataTable({
            "order": [],
            "language": {
                "emptyTable": "Belum ada data permohonan masuk.",
                "zeroRecords": "Tidak ada data yang cocok ditemukan",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                "infoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
                "infoFiltered": "(disaring dari _MAX_ total entri)",
                "lengthMenu": "Tampilkan _MENU_ entri per halaman",
                "search": "Cari:",
                "paginate": { "first": "Awal", "last": "Akhir", "next": "Berikutnya", "previous": "Sebelumnya" },
                "aria": { "sortAscending": ": aktifkan untuk mengurutkan kolom secara menaik", "sortDescending": ": aktifkan untuk mengurutkan kolom secara menurun" }
            },
            "columnDefs": [
                { "orderable": false, "searchable": false, "targets": [0, 9] }
            ],
        });
    } else {
        console.error("jQuery atau plugin DataTables tidak termuat dengan benar untuk tabel 'dataTablePermohonanAdmin'.");
    }
});
</script>
<?= $this->endSection() ?>
