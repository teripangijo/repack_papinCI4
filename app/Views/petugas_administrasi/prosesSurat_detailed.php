<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <h1 class="h3 mb-2 text-gray-800"><?= esc($subtitle ?? 'Proses Finalisasi Permohonan') ?></h1>
    <p class="mb-4">Lakukan finalisasi persetujuan atau penolakan permohonan impor returnable package.</p>

    <?php if (session()->getFlashdata('message')): ?>
        <?= session()->getFlashdata('message') ?>
    <?php endif; ?>
    <?php if (session()->getFlashdata('message_error_quota')): ?>
        <?= session()->getFlashdata('message_error_quota') ?>
    <?php endif; ?>

    <?php if (isset($validation) && $validation->getErrors()): ?>
        <div class="alert alert-danger" role="alert">
            <h4 class="alert-heading">Oops, ada kesalahan!</h4>
            <p>Mohon periksa kembali data yang Anda masukkan:</p>
            <hr>
            <?= $validation->listErrors() ?>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Formulir Finalisasi Permohonan - ID: <?= esc($permohonan['id'] ?? '') ?></h6>
        </div>
        <div class="card-body">
            <form action="<?= site_url('petugas_administrasi/prosesSurat/' . ($permohonan['id'] ?? '')) ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
            
                <fieldset class="border p-3 mb-4">
                    <legend class="w-auto px-2 small font-weight-bold">Data Perusahaan</legend>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="small mb-1">Nama Perusahaan</label><input type="text" class="form-control form-control-sm" value="<?= esc($user_perusahaan['NamaPers'] ?? ($permohonan['NamaPers'] ?? 'N/A')) ?>" readonly></div>
                        <div class="col-md-6 mb-3"><label class="small mb-1">NPWP</label><input type="text" class="form-control form-control-sm" value="<?= esc($user_perusahaan['npwp'] ?? ($permohonan['npwp'] ?? 'N/A')) ?>" readonly></div>
                    </div>
                </fieldset>

                <fieldset class="border p-3 mb-4">
                    <legend class="w-auto px-2 small font-weight-bold">Data Laporan Hasil Pemeriksaan (LHP)</legend>
                    <div class="row">
                        <div class="col-md-3 mb-3"><label class="small mb-1">No. LHP</label><input type="text" class="form-control form-control-sm" value="<?= esc($lhp['NoLHP'] ?? 'N/A') ?>" readonly></div>
                        <div class="col-md-3 mb-3"><label class="small mb-1">Tgl. LHP</label><input type="text" class="form-control form-control-sm" value="<?= esc(isset($lhp['TglLHP']) && $lhp['TglLHP'] != '0000-00-00' ? date('d F Y', strtotime($lhp['TglLHP'])) : 'N/A') ?>" readonly></div>
                        <div class="col-md-3 mb-3"><label class="small mb-1">Jumlah Sebenarnya (LHP)</label><input type="text" class="form-control form-control-sm" value="<?= esc($lhp['JumlahBenar'] ?? 'N/A') ?>" readonly></div>
                        <div class="col-md-3 mb-3"><label class="small mb-1">Hasil LHP</label><input type="text" class="form-control form-control-sm" value="<?= (isset($lhp['hasil']) && $lhp['hasil'] == 1) ? 'Sesuai' : 'Tidak Sesuai' ?>" readonly></div>
                    </div>
                </fieldset>

                <fieldset class="border p-3 mb-4">
                    <legend class="w-auto px-2 small font-weight-bold text-danger">Keputusan Akhir Admin</legend>
                     <div class="form-group mb-3">
                        <label class="small mb-1" for="status_final">Status Final Permohonan <span class="text-danger">*</span></label>
                        <div>
                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="status_final" id="status_disetujui" value="3" <?= set_radio('status_final', '3', true) ?>><label class="form-check-label small" for="status_disetujui">Disetujui</label></div>
                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="status_final" id="status_ditolak" value="4" <?= set_radio('status_final', '4') ?>><label class="form-check-label small" for="status_ditolak">Ditolak</label></div>
                        </div>
                    </div>

                    <div id="approval_fields">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="small mb-1" for="nomorSetuju">Nomor Surat Persetujuan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="nomorSetuju" name="nomorSetuju" value="<?= old('nomorSetuju', $lhp['NoLHP'] ?? '') ?>" placeholder="Contoh: S-123/WBC.02/KPP.MP.01/2025">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small mb-1" for="tgl_S">Tanggal Surat Persetujuan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm datepicker" id="tgl_S" name="tgl_S" value="<?= old('tgl_S', isset($lhp['TglLHP']) && $lhp['TglLHP'] != '0000-00-00' ? date('Y-m-d', strtotime($lhp['TglLHP'])) : date('Y-m-d')) ?>" placeholder="YYYY-MM-DD">
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="small mb-1" for="file_surat_keputusan">Upload File Surat Persetujuan <span id="sk_required_text" class="text-danger">*</span> <span class="text-info small">(Max 2MB)</span></label>
                            <?php if(!empty($permohonan['file_surat_keputusan'])): ?>
                                <p class="small mb-1">File saat ini: <a href="<?= site_url('path/to/download/' . esc($permohonan['file_surat_keputusan'])) ?>" target="_blank"><i class="fas fa-file-alt"></i> <?= esc($permohonan['file_surat_keputusan']) ?></a></p>
                            <?php endif; ?>
                            <div class="custom-file"><input type="file" class="custom-file-input" id="file_surat_keputusan" name="file_surat_keputusan" accept=".pdf,.jpg,.jpeg,.png"><label class="custom-file-label" for="file_surat_keputusan">Pilih file...</label></div>
                        </div>
                    </div>

                    <div class="form-group mb-3" id="catatan_penolakan_box" style="display: none;">
                        <label class="small mb-1" for="catatan_penolakan">Catatan Penolakan <span class="text-danger">*</span></label>
                        <textarea class="form-control form-control-sm" id="catatan_penolakan" name="catatan_penolakan" rows="3" placeholder="Jelaskan alasan penolakan..."><?= old('catatan_penolakan', $permohonan['catatan_penolakan'] ?? '') ?></textarea>
                    </div>
                </fieldset>

                <hr>
                <div class="form-group text-right">
                    <a href="<?= site_url('petugas_administrasi/detail_permohonan_admin/' . $permohonan['id']) ?>" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-success">Simpan Keputusan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    $('.datepicker').datepicker({ format: 'yyyy-mm-dd', autoclose: true, todayHighlight: true, uiLibrary: 'bootstrap4' });

    function toggleFinalFields() {
        if ($('input[name="status_final"]:checked').val() === '4') { // Ditolak
            $('#catatan_penolakan_box').show().find('textarea').prop('required', true);
            $('#approval_fields').hide().find('input, select').prop('required', false);
        } else { // Disetujui
            $('#catatan_penolakan_box').hide().find('textarea').prop('required', false);
            $('#approval_fields').show().find('#nomorSetuju, #tgl_S').prop('required', true);
            // File is only required if it doesn't exist yet
            if ('<?= empty($permohonan['file_surat_keputusan']) ?>') {
                $('#file_surat_keputusan').prop('required', true);
                $('#sk_required_text').show();
            } else {
                 $('#file_surat_keputusan').prop('required', false);
                 $('#sk_required_text').hide();
            }
        }
    }

    $('input[name="status_final"]').on('change', toggleFinalFields);
    toggleFinalFields();

    $('#file_surat_keputusan').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName || "Pilih file...");
    });
});
</script>
<?= $this->endSection() ?>
