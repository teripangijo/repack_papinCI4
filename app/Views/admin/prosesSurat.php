<?= $this->extend('layouts/main') ?> // Menggunakan layout 'main.php'
<?= $this->section('title') ?>
    <?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Proses Permohonan & LHP'; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <h1 class="h3 mb-2 text-gray-800"><?= htmlspecialchars($subtitle ?? 'Proses Finalisasi Permohonan'); ?></h1>
    <p class="mb-4">Lakukan finalisasi persetujuan atau penolakan permohonan impor returnable package.</p>

    <?php if (session()->getFlashdata('message')) : ?>
        <?= session()->getFlashdata('message'); ?>
    <?php endif; ?>
    <?php if (session()->getFlashdata('message_error_quota')) : ?>
        <?= session()->getFlashdata('message_error_quota'); ?>
    <?php endif; ?>

    <?php
    $validation = \Config\Services::validation();
    if ($validation->getErrors()) : ?>
        <div class="alert alert-danger" role="alert">
            <h4 class="alert-heading">Oops, ada kesalahan!</h4>
            <p>Mohon periksa kembali data yang Anda masukkan:</p>
            <hr>
            <?php foreach ($validation->getErrors() as $error) : ?>
                <div><?= $error ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Formulir Finalisasi Permohonan - ID: <?= htmlspecialchars($permohonan['id']); ?></h6>
        </div>
        <div class="card-body">
            <form action="<?= base_url('admin/prosesSurat/' . $permohonan['id']); ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field(); // CSRF token for CI4 ?>

                <fieldset class="border p-3 mb-4">
                    <legend class="w-auto px-2 small font-weight-bold">Data Perusahaan</legend>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="small mb-1">Nama Perusahaan</label>
                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($user_perusahaan['NamaPers'] ?? ($permohonan['NamaPers'] ?? 'N/A')) ?>" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small mb-1">NPWP</label>
                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($user_perusahaan['npwp'] ?? ($permohonan['npwp'] ?? 'N/A')) ?>" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="small mb-1">Alamat</label>
                            <textarea class="form-control form-control-sm" rows="2" readonly><?= htmlspecialchars($user_perusahaan['alamat'] ?? ($permohonan['alamat'] ?? 'N/A')) ?></textarea>
                        </div>
                    </div>
                     <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="small mb-1">No. SKEP Perusahaan (Jika Ada)</label>
                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($user_perusahaan['NoSkep'] ?? ($permohonan['NoSkep'] ?? 'N/A')) ?>" readonly>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="border p-3 mb-4">
                    <legend class="w-auto px-2 small font-weight-bold">Data Permohonan Awal</legend>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="small mb-1">Nomor Surat Pemohon</label>
                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($permohonan['nomorSurat']); ?>" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small mb-1">Tanggal Surat Pemohon</label>
                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars(date('d F Y', strtotime($permohonan['TglSurat']))); ?>" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small mb-1">Perihal</label>
                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($permohonan['Perihal']); ?>" readonly />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="small mb-1">Nama / Jenis Barang</label>
                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($permohonan['NamaBarang']); ?>" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small mb-1">Jumlah Barang Diajukan</label>
                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($permohonan['JumlahBarang'] . ' ' . ($permohonan['SatuanBarang'] ?? '')); ?>" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small mb-1">Negara Asal Barang</label>
                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($permohonan['NegaraAsal']); ?>" readonly>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="border p-3 mb-4">
                    <legend class="w-auto px-2 small font-weight-bold">Data Laporan Hasil Pemeriksaan (LHP)</legend>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="small mb-1">No. LHP</label>
                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($lhp['NoLHP'] ?? 'N/A'); ?>" readonly>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small mb-1">Tgl. LHP</label>
                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars(isset($lhp['TglLHP']) && $lhp['TglLHP'] != '0000-00-00' ? date('d F Y', strtotime($lhp['TglLHP'])) : 'N/A'); ?>" readonly>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small mb-1">Jumlah Barang Sebenarnya (LHP)</label>
                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($lhp['JumlahBenar'] ?? 'N/A'); ?>" readonly>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small mb-1">Hasil Pemeriksaan LHP</label>
                            <?php
                            $hasil_lhp_text = 'N/A';
                            if (isset($lhp['hasil'])) {
                                if ($lhp['hasil'] == 1) $hasil_lhp_text = 'Sesuai';
                                else if ($lhp['hasil'] == 0) $hasil_lhp_text = 'Tidak Sesuai';
                            }
                            ?>
                            <input type="text" class="form-control form-control-sm" value="<?= htmlspecialchars($hasil_lhp_text); ?>" readonly>
                        </div>
                    </div>
                    <div class="row">
                         <div class="col-md-12 mb-3">
                            <label class="small mb-1">Keterangan / Kesimpulan LHP</label>
                            <textarea class="form-control form-control-sm" rows="3" readonly><?= htmlspecialchars($lhp['Kesimpulan'] ?? 'N/A'); ?></textarea>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="border p-3 mb-4">
                    <legend class="w-auto px-2 small font-weight-bold text-danger">Keputusan Akhir Admin</legend>
                     <div class="form-group mb-3">
                        <label class="small mb-1" for="status_final">Status Final Permohonan <span class="text-danger">*</span></label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status_final" id="status_disetujui" value="3" <?= old('status_final') == '3' ? 'checked' : ''; ?>>
                                <label class="form-check-label small" for="status_disetujui">Disetujui</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status_final" id="status_ditolak" value="4" <?= old('status_final') == '4' ? 'checked' : ''; ?>>
                                <label class="form-check-label small" for="status_ditolak">Ditolak</label>
                            </div>
                        </div>
                        <?php if (\Config\Services::validation()->hasError('status_final')) : ?>
                            <small class="text-danger d-block mt-1"><?= \Config\Services::validation()->getError('status_final'); ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="small mb-1" for="nomorSetuju">Nomor Surat Persetujuan/Penolakan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm <?= (\Config\Services::validation()->hasError('nomorSetuju')) ? 'is-invalid' : ''; ?>" id="nomorSetuju" name="nomorSetuju" value="<?= old('nomorSetuju', $permohonan['nomorSetuju'] ?? '') ?>" placeholder="Contoh: S-123/WBC.02/KPP.MP.01/2025">
                            <?php if (\Config\Services::validation()->hasError('nomorSetuju')) : ?>
                                <small class="text-danger"><?= \Config\Services::validation()->getError('nomorSetuju'); ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small mb-1" for="tgl_S">Tanggal Surat Persetujuan/Penolakan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm datepicker <?= (\Config\Services::validation()->hasError('tgl_S')) ? 'is-invalid' : ''; ?>" id="tgl_S" name="tgl_S" value="<?= old('tgl_S', isset($lhp['TglLHP']) && $lhp['TglLHP'] != '0000-00-00' ? date('Y-m-d', strtotime($lhp['TglLHP'])) : date('Y-m-d')) ?>" placeholder="YYYY-MM-DD">
                            <?php if (\Config\Services::validation()->hasError('tgl_S')) : ?>
                                <small class="text-danger"><?= \Config\Services::validation()->getError('tgl_S'); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group mb-3" id="upload_surat_persetujuan_box">
                        <label class="small mb-1" for="file_surat_keputusan">Upload File Surat Persetujuan Pengeluaran <span id="surat_keputusan_wajib_text" class="text-danger">*</span> <span class="text-info small">(Max 2MB: PDF, JPG, PNG)</span></label>
                        <?php if(!empty($permohonan['file_surat_keputusan'])): ?>
                            <p class="small mb-1">File saat ini:
                                <!-- [DIREVISI] -->
                                <a href="<?= site_url('admin/downloadFile/' . $permohonan['file_surat_keputusan']); ?>" target="_blank">
                                    <i class="fas fa-file-alt"></i> Lihat File Saat Ini
                                </a>
                                (Upload file baru di bawah akan menggantikan file ini)
                            </p>
                        <?php endif; ?>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input <?= (\Config\Services::validation()->hasError('file_surat_keputusan')) ? 'is-invalid' : ''; ?>" id="file_surat_keputusan" name="file_surat_keputusan" accept=".pdf,.jpg,.jpeg,.png">
                            <label class="custom-file-label" for="file_surat_keputusan">Pilih file...</label>
                        </div>
                        <?php if (\Config\Services::validation()->hasError('file_surat_keputusan')) : ?>
                            <small class="text-danger d-block mt-1"><?= \Config\Services::validation()->getError('file_surat_keputusan'); ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3" id="catatan_penolakan_box" style="display: none;">
                            <label class="small mb-1" for="catatan_penolakan">Catatan Penolakan <span class="text-danger">*</span></label>
                            <textarea class="form-control form-control-sm <?= (\Config\Services::validation()->hasError('catatan_penolakan')) ? 'is-invalid' : ''; ?>" id="catatan_penolakan" name="catatan_penolakan" rows="3" placeholder="Jelaskan alasan penolakan..."><?= old('catatan_penolakan', $permohonan['catatan_penolakan'] ?? ''); ?></textarea>
                            <?php if (\Config\Services::validation()->hasError('catatan_penolakan')) : ?>
                                <small class="text-danger"><?= \Config\Services::validation()->getError('catatan_penolakan'); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                </fieldset>

                <hr>
                <div class="form-group text-right">
                    <a href="<?= base_url('admin/detail_permohonan_admin/' . $permohonan['id']) ?>" class="btn btn-secondary btn-icon-split mr-2">
                        <span class="icon text-white-50"><i class="fas fa-arrow-left"></i></span>
                        <span class="text">Kembali ke Detail</span>
                    </a>
                    <button type="submit" class="btn btn-success btn-icon-split" <?= !(isset($lhp) && !empty($lhp) && !empty($lhp['NoLHP']) && !empty($lhp['TglLHP'])) ? 'disabled title="Data LHP tidak lengkap"' : '' ?>>
                        <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                        <span class="text">Simpan Keputusan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    // Inisialisasi datepicker
    $('.datepicker').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true,
        uiLibrary: 'bootstrap4'
    });

    function toggleFinalFields() {
        var statusFinal = $('input[name="status_final"]:checked').val();
        if (statusFinal === '4') { // Ditolak
            $('#catatan_penolakan_box').show();
            $('#upload_surat_persetujuan_box').hide();
            $('#surat_keputusan_wajib_text').hide();
            $('#file_surat_keputusan').removeAttr('required');
        } else if (statusFinal === '3') {
            $('#catatan_penolakan_box').hide();
            $('#upload_surat_persetujuan_box').show();
            $('#surat_keputusan_wajib_text').show();
            <?php if(empty($permohonan['file_surat_keputusan'])): ?>
                $('#file_surat_keputusan').attr('required', 'required');
            <?php else: ?>
                $('#file_surat_keputusan').removeAttr('required');
            <?php endif; ?>
        } else { 
            $('#catatan_penolakan_box').hide();
            $('#upload_surat_persetujuan_box').show(); 
            $('#surat_keputusan_wajib_text').show();
             <?php if(empty($permohonan['file_surat_keputusan'])): ?>
                $('#file_surat_keputusan').attr('required', 'required');
            <?php else: ?>
                $('#file_surat_keputusan').removeAttr('required');
            <?php endif; ?>
        }
    }

    toggleFinalFields();

    $('input[name="status_final"]').on('change', function() {
        toggleFinalFields();
    });

    $('#file_surat_keputusan').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName || "Pilih file...");
    });
});
</script>
<?= $this->endSection() ?>
