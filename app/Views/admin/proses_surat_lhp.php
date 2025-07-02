<?= $this->extend('layouts/main') ?> // Menggunakan layout 'main.php'
<?= $this->section('title') ?>
    <?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Proses Permohonan & LHP'; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Proses Surat & LHP'; ?></h1>
        <a href="<?= base_url('admin/permohonanMasuk'); ?>" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Daftar Permohonan
        </a>
    </div>

    <?php
    if (session()->getFlashdata('message')) {
        echo session()->getFlashdata('message');
    }
    ?>

    <?php if (empty($permohonan)) : ?>
        <div class="alert alert-danger">Data permohonan tidak ditemukan.</div>
    <?php else : ?>
        <div class="row">
            <div class="col-lg-7">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Formulir Penyelesaian Permohonan ID: <?= htmlspecialchars($permohonan['id']); ?></h6>
                    </div>
                    <div class="card-body">
                        <form action="<?= base_url('admin/prosesSurat/' . $permohonan['id']); ?>" method="post" enctype="multipart/form-data">
                        <?= csrf_field(); // CSRF token for CI4 ?>

                        <p><strong>Nama Perusahaan:</strong> <?= htmlspecialchars($permohonan['NamaPers']); ?></p>
                        <p><strong>No Surat Pengajuan:</strong> <?= htmlspecialchars($permohonan['nomorSurat']); ?></p>
                        <p><strong>Sisa Kuota Saat Ini:</strong> <?= isset($permohonan['sisa_kuota_perusahaan_saat_ini']) ? number_format($permohonan['sisa_kuota_perusahaan_saat_ini'],0,',','.') : (isset($permohonan['remaining_quota']) ? number_format($permohonan['remaining_quota'],0,',','.') : 'N/A'); ?> Unit</p>

                        <?php if (isset($lhp) && !empty($lhp) && isset($lhp['NoLHP']) && isset($lhp['TglLHP'])) : ?>
                            <p class="text-success"><strong>LHP sudah direkam. Jumlah Barang Disetujui (dari LHP): <?= isset($lhp['JumlahBenar']) ? htmlspecialchars(number_format($lhp['JumlahBenar'])) : '0'; ?> Unit</strong></p>
                            <hr>
                            <div class="form-group">
                                <label for="nomorLhpDariPetugas">Nomor LHP (dari Petugas)</label>
                                <input type="text" class="form-control" id="nomorLhpDariPetugas" value="<?= htmlspecialchars($lhp['NoLHP']); ?>" readonly>
                                </div>
                            <div class="form-group">
                                <label for="tanggalLhpDariPetugas">Tanggal LHP (dari Petugas)</label>
                                <input type="text" class="form-control" id="tanggalLhpDariPetugas" value="<?= htmlspecialchars(date('d M Y', strtotime($lhp['TglLHP']))); ?>" readonly>
                                </div>
                        <?php else: ?>
                            <p class="text-danger"><strong>LHP belum direkam atau data No/Tgl LHP tidak lengkap. Tidak dapat melanjutkan penyelesaian.</strong></p>
                            <?php // Disable submit button if LHP data is incomplete ?>
                        <?php endif; ?>
                        <hr>

                        <div class="form-group">
                            <label for="status_final">Status Final Permohonan <span class="text-danger">*</span></label>
                            <select class="form-control <?= (\Config\Services::validation()->hasError('status_final')) ? 'is-invalid' : ''; ?>" id="status_final" name="status_final" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="3" <?= old('status_final') == '3' ? 'selected' : ''; ?>>Disetujui (Kuota Akan Dipotong Sesuai LHP)</option>
                                <option value="4" <?= old('status_final') == '4' ? 'selected' : ''; ?>>Ditolak</option>
                            </select>
                            <?php if (\Config\Services::validation()->hasError('status_final')) : ?>
                                <small class="text-danger pl-1"><?= \Config\Services::validation()->getError('status_final'); ?></small>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="nomorSetuju">Nomor Surat Persetujuan/Penolakan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= (\Config\Services::validation()->hasError('nomorSetuju')) ? 'is-invalid' : ''; ?>" id="nomorSetuju" name="nomorSetuju" value="<?= old('nomorSetuju', $permohonan['nomorSetuju'] ?? ''); ?>" required>
                            <?php if (\Config\Services::validation()->hasError('nomorSetuju')) : ?>
                                <small class="text-danger pl-1"><?= \Config\Services::validation()->getError('nomorSetuju'); ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label for="tgl_S">Tanggal Surat Persetujuan/Penolakan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control gj-datepicker <?= (\Config\Services::validation()->hasError('tgl_S')) ? 'is-invalid' : ''; ?>" id="tgl_S" name="tgl_S" placeholder="YYYY-MM-DD" value="<?= old('tgl_S', (isset($permohonan['tgl_S']) && $permohonan['tgl_S']!='0000-00-00') ? $permohonan['tgl_S'] : ''); ?>" required>
                            <?php if (\Config\Services::validation()->hasError('tgl_S')) : ?>
                                <small class="text-danger pl-1"><?= \Config\Services::validation()->getError('tgl_S'); ?></small>
                            <?php endif; ?>
                        </div>

                         <div class="form-group">
                            <label for="file_surat_keputusan">Upload File Surat Persetujuan Pengeluaran (.pdf, .jpg, .png, .jpeg maks 2MB) <span id="file_sk_required" class="text-danger" style="display:none;">*</span></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input <?= (\Config\Services::validation()->hasError('file_surat_keputusan')) ? 'is-invalid' : ''; ?>" id="file_surat_keputusan" name="file_surat_keputusan" accept=".pdf,.jpg,.png,.jpeg">
                                <label class="custom-file-label" for="file_surat_keputusan"><?= !empty($permohonan['file_surat_keputusan']) ? htmlspecialchars($permohonan['file_surat_keputusan']) : 'Pilih file...'; ?></label>
                            </div>
                            <?php if (!empty($permohonan['file_surat_keputusan'])): ?>
                                <small class="form-text text-info">File SK saat ini:
                                    <a href="<?= base_url('uploads/sk_penyelesaian/' . $permohonan['file_surat_keputusan']); ?>" target="_blank">
                                        <?= htmlspecialchars($permohonan['file_surat_keputusan']); ?>
                                    </a>. Upload file baru akan menggantikannya.
                                </small>
                            <?php endif; ?>
                            <?php if (\Config\Services::validation()->hasError('file_surat_keputusan')) : ?>
                                <div class="invalid-feedback"><?= \Config\Services::validation()->getError('file_surat_keputusan'); ?></div>
                            <?php endif; ?>
                        </div>

                         <div class="form-group">
                            <label for="link">Link Dokumen SK (Opsional)</label>
                            <input type="url" class="form-control <?= (\Config\Services::validation()->hasError('link')) ? 'is-invalid' : ''; ?>" id="link" name="link" placeholder="https://" value="<?= old('link', $permohonan['link'] ?? ''); ?>">
                            <?php if (\Config\Services::validation()->hasError('link')) : ?>
                                <small class="text-danger pl-1"><?= \Config\Services::validation()->getError('link'); ?></small>
                            <?php endif; ?>
                        </div>

                        <div class="form-group" id="catatan_penolakan_group" style="<?= old('status_final', $permohonan['status'] ?? '') == '4' ? 'display:block;' : 'display:none;'; ?>">
                            <label for="catatan_penolakan">Catatan Penolakan (Wajib diisi jika status Ditolak) <span class="text-danger" id="catatan_penolakan_required" style="display:none;">*</span></label>
                            <textarea class="form-control <?= (\Config\Services::validation()->hasError('catatan_penolakan')) ? 'is-invalid' : ''; ?>" id="catatan_penolakan" name="catatan_penolakan" rows="3"><?= old('catatan_penolakan', $permohonan['catatan_penolakan'] ?? ''); ?></textarea>
                            <?php if (\Config\Services::validation()->hasError('catatan_penolakan')) : ?>
                                <small class="text-danger pl-1"><?= \Config\Services::validation()->getError('catatan_penolakan'); ?></small>
                            <?php endif; ?>
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
                        <p>Jika permohonan disetujui, sisa kuota perusahaan akan dikurangi berdasarkan "Jumlah Barang Disetujui" pada LHP.</p>
                        <p>Jika LHP belum ada atau jumlah disetujui 0, kuota tidak akan terpotong meskipun permohonan disetujui (jika statusnya "Disetujui").</p>
                         <p>Nomor LHP dan Tanggal LHP di atas diambil secara otomatis dari data yang direkam oleh Petugas Pemeriksa.</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<script>
$(document).ready(function () {
    // Initialize datepicker for fields requiring manual date input
    if (typeof $.fn.datepicker !== 'undefined') {
        $('#tgl_S').datepicker({
            uiLibrary: 'bootstrap4',
            format: 'yyyy-mm-dd',
            showOnFocus: true,
            showRightIcon: true,
            autoClose: true
        });
        $('#tgl_ND').datepicker({ // For Nota Dinas Date
            uiLibrary: 'bootstrap4',
            format: 'yyyy-mm-dd',
            showOnFocus: true,
            showRightIcon: true,
            autoClose: true
        });
    }

    // Logic to show/hide 'Catatan Penolakan' and 'File SK' required status based on 'Status Final Permohonan'
    const statusFinalDropdown = document.getElementById('status_final');
    const catatanPenolakanGroup = document.getElementById('catatan_penolakan_group');
    const catatanPenolakanInput = document.getElementById('catatan_penolakan');
    const catatanPenolakanRequiredLabel = document.getElementById('catatan_penolakan_required');
    const fileSkRequiredLabel = document.getElementById('file_sk_required');
    const fileSkInput = document.getElementById('file_surat_keputusan');

    function toggleFieldsBasedOnStatus() {
        if (statusFinalDropdown.value === '4') { // Ditolak
            catatanPenolakanGroup.style.display = 'block';
            catatanPenolakanInput.setAttribute('required', 'required');
            catatanPenolakanRequiredLabel.style.display = 'inline';
            // If status is rejected, file SK is not required
            fileSkRequiredLabel.style.display = 'none';
            fileSkInput.removeAttribute('required');
        } else if (statusFinalDropdown.value === '3') { // Disetujui
            catatanPenolakanGroup.style.display = 'none';
            catatanPenolakanInput.removeAttribute('required');
            catatanPenolakanRequiredLabel.style.display = 'none';

            // If status is approved, and there's no existing file, then file SK is required
            <?php if (empty($permohonan['file_surat_keputusan'])): ?>
                fileSkRequiredLabel.style.display = 'inline';
                fileSkInput.setAttribute('required', 'required');
            <?php else: ?>
                fileSkRequiredLabel.style.display = 'none';
                fileSkInput.removeAttribute('required');
            <?php endif; ?>
        } else { // Default or other statuses
            catatanPenolakanGroup.style.display = 'none';
            catatanPenolakanInput.removeAttribute('required');
            catatanPenolakanRequiredLabel.style.display = 'none';
            fileSkRequiredLabel.style.display = 'none';
            fileSkInput.removeAttribute('required');
        }
    }

    // Initialize on page load
    toggleFieldsBasedOnStatus();
    statusFinalDropdown.addEventListener('change', toggleFieldsBasedOnStatus);

    // Script for custom file input Bootstrap
    var fileInputs = document.querySelectorAll('.custom-file-input');
    Array.prototype.forEach.call(fileInputs, function(input) {
        var label = input.nextElementSibling;
        var originalLabelText = label.innerHTML;
        input.addEventListener('change', function (e) {
            if (e.target.files.length > 0) {
                label.innerText = e.target.files[0].name;
            } else {
                label.innerText = originalLabelText;
            }
        });
    });
});
</script>
<?= $this->endSection() ?>