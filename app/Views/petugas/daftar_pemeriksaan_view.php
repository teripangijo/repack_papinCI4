<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= esc($subtitle ?? 'Daftar Pemeriksaan Ditugaskan') ?></h1>
    </div>

    <!-- <?php if (session()->getFlashdata('message')): ?>
        <?= session()->getFlashdata('message') ?>
    <?php endif; ?> -->

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Permohonan yang Perlu Direkam Laporan Hasil Pemeriksaan (LHP)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableTugasPetugas" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ID Aju</th>
                            <th>No Surat Pemohon</th>
                            <th>Tgl Surat Tugas</th>
                            <th>Nama Perusahaan</th>
                            <th>Diajukan Oleh</th>
                            <th>Waktu Penunjukan</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($daftar_tugas) && is_array($daftar_tugas)):
                            $no = 1;
                            foreach ($daftar_tugas as $tugas): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= esc($tugas['id']) ?></td>
                            <td><?= esc($tugas['nomorSurat'] ?? '-') ?></td>
                            <td><?= isset($tugas['TglSuratTugas']) && $tugas['TglSuratTugas'] != '0000-00-00' ? date('d/m/Y', strtotime($tugas['TglSuratTugas'])) : '-' ?></td>
                            <td><?= esc($tugas['NamaPers'] ?? 'N/A') ?></td>
                            <td><?= esc($tugas['nama_pemohon'] ?? 'N/A') ?></td>
                            <td><?= isset($tugas['WaktuPenunjukanPetugas']) && $tugas['WaktuPenunjukanPetugas'] != '0000-00-00 00:00:00' ? date('d/m/Y H:i', strtotime($tugas['WaktuPenunjukanPetugas'])) : '-' ?></td>
                            <td class="text-center">
                                <a href="<?= site_url('petugas/rekam_lhp/' . $tugas['id']) ?>" class="btn btn-sm btn-primary" title="Rekam Laporan Hasil Pemeriksaan untuk ID Aju <?= esc($tugas['id']) ?>">
                                    <i class="fas fa-file-alt mr-1"></i> Rekam LHP
                                </a>
                                <?php if (!empty($tugas['FileSuratTugas'])): ?>
                                    <a href="<?= site_url('petugas/download/surat_tugas/' . esc($tugas['FileSuratTugas'])) ?>" target="_blank" class="btn btn-sm btn-info mt-1" title="Lihat File Surat Tugas">
                                        <i class="fas fa-file-pdf mr-1"></i> Lihat ST
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
    if (typeof $ !== 'undefined' && typeof $.fn.DataTable !== 'undefined') {
        $('#dataTableTugasPetugas').DataTable({
            "order": [[ 6, "desc" ]],
            "language": {
                "emptyTable": "Tidak ada tugas pemeriksaan yang perlu direkam LHP saat ini.",
                "zeroRecords": "Tidak ada data yang cocok ditemukan",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                "infoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
                "infoFiltered": "(disaring dari _MAX_ total entri)",
                "lengthMenu": "Tampilkan _MENU_ entri",
                "search": "Cari:",
                "paginate": { "first": "Awal", "last": "Akhir", "next": "Berikutnya", "previous": "Sebelumnya" }
            },
            "columnDefs": [
                { "orderable": false, "searchable": false, "targets": [0, 7] }
            ]
        });
    } else {
        console.error("jQuery atau DataTables plugin tidak termuat dengan benar untuk 'dataTableTugasPetugas'.");
    }
});
</script>
<?= $this->endSection() ?>
