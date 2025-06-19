<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= esc($subtitle ?? 'Proses Finalisasi Permohonan') ?></h1>
        <a href="<?= site_url('petugas_administrasi/permohonanMasuk') ?>" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Daftar Permohonan
        </a>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <?= session()->getFlashdata('message') ?>
    <?php endif; ?>

    <?php if (isset($validation) && $validation->getErrors()): ?>
        <div class="alert alert-danger" role="alert">
            <?= $validation->listErrors() ?>
        </div>
    <?php endif; ?>

    <?php if (empty($permohonan)) : ?>
        <div class="alert alert-danger">Data permohonan tidak ditemukan.</div>
    <?php else : ?>
        <div class="row">
            <div class="col-lg-7">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Formulir Penyelesaian Permohonan ID: <?= esc($permohonan['id']) ?></h6>
                    </div>
                    <div class="card-body">
                        <form action="<?= site_url('petugas_administrasi/prosesSurat/' . $permohonan['id']) ?>" method="post" enctype="multipart/form-data">
                            <?= csrf_field() ?>

                            <p><strong>Nama Perusahaan:</strong> <?= esc($permohonan['NamaPers'] ?? 'N/A') ?></p>
                            <p><strong>No Surat Pengajuan:</strong> <?= esc($permohonan['nomorSurat'] ?? 'N/A') ?></p>

                            <?php if (isset($lhp) && !empty($lhp) && !empty($lhp['NoLHP']) && !empty($lhp['TglLHP'])) : ?>
                                <div class="alert alert-success">
                                    <strong>LHP sudah direkam.</strong><br>
                                    Jumlah Barang Disetujui (dari LHP): <strong><?= esc(number_format($lhp['JumlahBenar'] ?? 0)) ?> Unit</strong>
                                </div>
                                <div class="form-group">
                                    <label for="nomorLhpDariPetugas">Nomor LHP (dari Petugas)</label>
                                    <input type="text" class="form-control" id="nomorLhpDariPetugas" value="<?= esc($lhp['NoLHP']) ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="tanggalLhpDariPetugas">Tanggal LHP (dari Petugas)</label>
                                    <input type="text" class="form-control" id="tanggalLhpDariPetugas" value="<?= esc(date('d M Y', strtotime($lhp['TglLHP']))) ?>" readonly>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-danger">
                                    <strong>LHP belum direkam atau data No/Tgl LHP tidak lengkap. Tidak dapat melanjutkan penyelesaian.</strong>
                                </div>
                            <?php endif; ?>
                            <hr>

                            <div class="form-group">
                                <label for="status_final">Status Final Permohonan <span class="text-danger">*</span></label>
                                <select class="form-control" id="status_final" name="status_final" required>
                                    <option value="">-- Pilih Status --</option>
                                    <option value="3" <?= set_select('status_final', '3') ?>>Disetujui (Kuota Akan Dipotong Sesuai LHP)</option>
                                    <option value="4" <?= set_select('status_final', '4') ?>>Ditolak</option>
                                </select>
                            </div>

                            <div id="rejection_reason_field" style="display:none;">
                                <div class="form-group">
                                    <label for="catatan_penolakan">Catatan Penolakan <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="catatan_penolakan" id="catatan_penolakan" rows="3"><?= old('catatan_penolakan') ?></textarea>
                                </div>
                            </div>
                            
                            <div id="approval_fields" style="display:none;">
                                <div class="form-group">
                                    <label for="nomorSetuju">Nomor Surat Persetujuan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nomorSetuju" name="nomorSetuju" value="<?= old('nomorSetuju') ?>">
                                </div>
                                <div class="form-group">
                                    <label for="tgl_S">Tanggal Surat Persetujuan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control gj-datepicker" id="tgl_S" name="tgl_S" placeholder="YYYY-MM-DD" value="<?= old('tgl_S') ?>">
                                </div>
                                <div class="form-group">
                                    <label for="file_surat_keputusan">Upload File Surat Persetujuan (Opsional)</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="file_surat_keputusan" name="file_surat_keputusan">
                                        <label class="custom-file-label" for="file_surat_keputusan">Pilih file...</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="link">Link Surat Keputusan (Opsional)</label>
                                    <input type="url" class="form-control" id="link" name="link" placeholder="https://" value="<?= old('link') ?>">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-user btn-block" <?= !(isset($lhp) && !empty($lhp) && !empty($lhp['NoLHP']) && !empty($lhp['TglLHP'])) ? 'disabled title="Data LHP tidak lengkap"' : '' ?>>
                                Simpan & Selesaikan Permohonan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                 <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Panduan</h6>
                    </div>
                    <div class="card-body small">
                        <p>Pastikan data LHP sudah direkam dengan lengkap (Nomor & Tanggal LHP) oleh Petugas sebelum Anda dapat melanjutkan proses penyelesaian ini.</p>
                        <p>Jika permohonan <strong>Disetujui</strong>, sisa kuota perusahaan akan dikurangi berdasarkan "Jumlah Barang Disetujui" pada LHP.</p>
                        <p>Jika <strong>Ditolak</strong>, mohon isi alasan penolakan pada kolom yang muncul.</p>
                        <p>Tombol simpan hanya akan aktif jika data LHP sudah lengkap.</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function () {
    const statusSelect = $('#status_final');
    const approvalFields = $('#approval_fields');
    const rejectionField = $('#rejection_reason_field');
    const nomorSetujuInput = $('#nomorSetuju');
    const tglSInput = $('#tgl_S');
    const catatanPenolakanInput = $('#catatan_penolakan');

    function toggleFields() {
        const selectedStatus = statusSelect.val();
        if (selectedStatus === '3') { // Disetujui
            approvalFields.show();
            rejectionField.hide();
            nomorSetujuInput.prop('required', true);
            tglSInput.prop('required', true);
            catatanPenolakanInput.prop('required', false);
        } else if (selectedStatus === '4') { // Ditolak
            approvalFields.hide();
            rejectionField.show();
            nomorSetujuInput.prop('required', false);
            tglSInput.prop('required', false);
            catatanPenolakanInput.prop('required', true);
        } else {
            approvalFields.hide();
            rejectionField.hide();
            nomorSetujuInput.prop('required', false);
            tglSInput.prop('required', false);
            catatanPenolakanInput.prop('required', false);
        }
    }

    statusSelect.on('change', toggleFields);
    toggleFields(); // Run on page load

    if (typeof $.fn.datepicker !== 'undefined') {
        $('#tgl_S').datepicker({
            uiLibrary: 'bootstrap4',
            format: 'yyyy-mm-dd',
            showOnFocus: true,
            showRightIcon: true
        });
    }
    
    // For custom file input label
    $('.custom-file-input').on('change', function(event) {
        var fileName = event.target.files[0] ? event.target.files[0].name : "Pilih file...";
        $(this).next('.custom-file-label').html(fileName);
    });
});
</script>
<?= $this->endSection() ?>
