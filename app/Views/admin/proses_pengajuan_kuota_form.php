<?= $this->extend('layouts/main') ?> // Menggunakan layout 'main.php'
<?= $this->section('title') ?>
    <?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Daftar Permohonan Impor'; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$nama_barang_diajukan = isset($pengajuan['nama_barang_kuota']) ? htmlspecialchars($pengajuan['nama_barang_kuota']) : 'Tidak Diketahui';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Proses Pengajuan Kuota'; ?></h1>
        <a href="<?= site_url('admin/daftar_pengajuan_kuota'); ?>" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Daftar Pengajuan
        </a>
    </div>

    <?php
    $validation = \Config\Services::validation();
    if ($validation->getErrors()) : ?>
        <div class="alert alert-danger" role="alert">
            <?php foreach ($validation->getErrors() as $error) : ?>
                <p><?= $error ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('message')) : ?>
        <?= session()->getFlashdata('message'); ?>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Proses Pengajuan Kuota ID: <?= htmlspecialchars($pengajuan['id'] ?? 'N/A'); ?>
                dari: <?= htmlspecialchars($pengajuan['NamaPers'] ?? 'Perusahaan tidak ditemukan'); ?>
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Informasi Perusahaan</h5>
                    <p><strong>Nama Perusahaan:</strong> <?= htmlspecialchars($pengajuan['NamaPers'] ?? 'N/A'); ?></p>
                    <p><strong>Email User:</strong> <?= htmlspecialchars($pengajuan['user_email'] ?? 'N/A'); ?></p>
                    <p class="small text-muted"><em>(Kuota Umum Perusahaan Saat Ini)</em></p>
                    <p><strong>Total Kuota Awal Terdaftar:</strong> <?= htmlspecialchars(number_format($pengajuan['initial_quota_sebelum'] ?? 0, 0, ',', '.')); ?> Unit</p>
                    <p><strong>Total Sisa Kuota Terdaftar:</strong> <?= htmlspecialchars(number_format($pengajuan['remaining_quota_sebelum'] ?? 0, 0, ',', '.')); ?> Unit</p>
                </div>
                <div class="col-md-6">
                    <h5>Detail Pengajuan dari User</h5>
                    <p><strong>No. Surat User:</strong> <?= htmlspecialchars($pengajuan['nomor_surat_pengajuan'] ?? '-'); ?></p>
                    <p><strong>Tgl. Surat User:</strong> <?= isset($pengajuan['tanggal_surat_pengajuan']) ? date('d M Y', strtotime($pengajuan['tanggal_surat_pengajuan'])) : '-'; ?></p>
                    <p><strong>Perihal User:</strong> <?= htmlspecialchars($pengajuan['perihal_pengajuan'] ?? '-'); ?></p>
                    <p><strong>Nama Barang Diajukan:</strong> <span class="font-weight-bold text-info"><?= $nama_barang_diajukan; ?></span></p>
                    <p><strong>Jumlah Kuota Diajukan:</strong> <span class="font-weight-bold text-info"><?= htmlspecialchars(number_format($pengajuan['requested_quota'] ?? 0, 0, ',', '.')); ?> Unit</span></p>
                    <p><strong>Alasan Pengajuan:</strong> <?= nl2br(htmlspecialchars($pengajuan['reason'] ?? 'N/A')); ?></p>
                    <p><strong>Tanggal Pengajuan Sistem:</strong> <?= isset($pengajuan['submission_date']) ? date('d M Y H:i:s', strtotime($pengajuan['submission_date'])) : 'N/A'; ?></p>
                     <?php if (!empty($pengajuan['file_lampiran_user'])): ?>
                        <p><strong>File Lampiran User:</strong>
                            <a href="<?= base_url('uploads/lampiran_kuota/' . $pengajuan['file_lampiran_user']); ?>" target="_blank">
                                <?= htmlspecialchars($pengajuan['file_lampiran_user']); ?>
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            <hr>
            <h5>Form Tindakan Admin</h5>
            <form action="<?= site_url('admin/proses_pengajuan_kuota/' . $pengajuan['id']); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); // CSRF token for CI4 ?>
                <div class="form-group">
                    <label for="status_pengajuan">Status Pengajuan <span class="text-danger">*</span></label>
                    <select class="form-control <?= $validation->hasError('status_pengajuan') ? 'is-invalid' : ''; ?>" id="status_pengajuan" name="status_pengajuan" required>
                        <option value="pending" <?= old('status_pengajuan', ($pengajuan['status'] ?? 'pending')) == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="diproses" <?= old('status_pengajuan', ($pengajuan['status'] ?? '')) == 'diproses' ? 'selected' : ''; ?>>Diproses</option>
                        <option value="approved" <?= old('status_pengajuan', ($pengajuan['status'] ?? '')) == 'approved' ? 'selected' : ''; ?>>Approved (Disetujui)</option>
                        <option value="rejected" <?= old('status_pengajuan', ($pengajuan['status'] ?? '')) == 'rejected' ? 'selected' : ''; ?>>Rejected (Ditolak)</option>
                    </select>
                    <?php if ($validation->hasError('status_pengajuan')) : ?>
                        <small class="text-danger pl-1"><?= $validation->getError('status_pengajuan'); ?></small>
                    <?php endif; ?>
                </div>

                <div id="approved_fields" style="<?= old('status_pengajuan', ($pengajuan['status'] ?? '')) == 'approved' ? 'display:block;' : 'display:none;'; ?>">
                    <div class="form-group">
                        <label for="approved_quota">Jumlah Kuota Disetujui untuk <span class="text-info font-weight-bold"><?= $nama_barang_diajukan; ?></span> <span class="text-danger">*</span></label>
                        <input type="number" class="form-control <?= $validation->hasError('approved_quota') ? 'is-invalid' : ''; ?>" id="approved_quota" name="approved_quota" value="<?= old('approved_quota', $pengajuan['approved_quota'] ?? ($pengajuan['requested_quota'] ?? '0')); ?>" min="0">
                        <?php if ($validation->hasError('approved_quota')) : ?>
                            <small class="text-danger pl-1"><?= $validation->getError('approved_quota'); ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="nomor_sk_petugas">Nomor Surat Keputusan (KEP) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= $validation->hasError('nomor_sk_petugas') ? 'is-invalid' : ''; ?>" id="nomor_sk_petugas" name="nomor_sk_petugas" value="<?= old('nomor_sk_petugas', $pengajuan['nomor_sk_petugas'] ?? ''); ?>">
                            <?php if ($validation->hasError('nomor_sk_petugas')) : ?>
                                <small class="text-danger pl-1"><?= $validation->getError('nomor_sk_petugas'); ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="tanggal_sk_petugas">Tanggal Surat Keputusan (KEP) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control gj-datepicker <?= $validation->hasError('tanggal_sk_petugas') ? 'is-invalid' : ''; ?>" id="tanggal_sk_petugas" name="tanggal_sk_petugas" placeholder="YYYY-MM-DD" value="<?= old('tanggal_sk_petugas', (isset($pengajuan['tanggal_sk_petugas']) && $pengajuan['tanggal_sk_petugas'] != '0000-00-00') ? $pengajuan['tanggal_sk_petugas'] : ''); ?>">
                            <?php if ($validation->hasError('tanggal_sk_petugas')) : ?>
                                <small class="text-danger pl-1"><?= $validation->getError('tanggal_sk_petugas'); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="file_sk_petugas">Upload File SK Petugas (.pdf, .jpg, .png, .jpeg maks 2MB) <span id="file_sk_petugas_label_required" class="text-danger" style="display:none;">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input <?= $validation->hasError('file_sk_petugas') ? 'is-invalid' : ''; ?>" id="file_sk_petugas" name="file_sk_petugas" accept=".pdf,.jpg,.png,.jpeg">
                            <label class="custom-file-label" for="file_sk_petugas"><?= !empty($pengajuan['file_sk_petugas']) ? htmlspecialchars($pengajuan['file_sk_petugas']) : 'Pilih file SK...'; ?></label>
                        </div>
                        <?php if (!empty($pengajuan['file_sk_petugas'])): ?>
                            <small class="form-text text-info">File SK saat ini:
                                <a href="<?= base_url('admin/download_sk_kuota_admin/' . $pengajuan['id']); ?>" target="_blank">
                                    <?= htmlspecialchars($pengajuan['file_sk_petugas']); ?>
                                </a>. Upload file baru akan menggantikannya.
                            </small>
                        <?php endif; ?>
                        <?php if ($validation->hasError('file_sk_petugas')) : ?>
                            <div class="invalid-feedback"><?= $validation->getError('file_sk_petugas'); ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="admin_notes">Catatan Admin (Jika ditolak, alasan penolakan wajib diisi)</label>
                    <textarea class="form-control <?= $validation->hasError('admin_notes') ? 'is-invalid' : ''; ?>" id="admin_notes" name="admin_notes" rows="3"><?= old('admin_notes', $pengajuan['admin_notes'] ?? ''); ?></textarea>
                     <?php if ($validation->hasError('admin_notes')) : ?>
                        <small class="text-danger pl-1"><?= $validation->getError('admin_notes'); ?></small>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Proses Pengajuan</button>
                <a href="<?= site_url('admin/daftar_pengajuan_kuota'); ?>" class="btn btn-secondary ml-2">Batal</a>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusPengajuanDropdown = document.getElementById('status_pengajuan');
    const approvedFieldsDiv = document.getElementById('approved_fields');
    const fileSkPetugasLabelRequired = document.getElementById('file_sk_petugas_label_required');
    const approvedQuotaInput = document.getElementById('approved_quota');
    const nomorSkInput = document.getElementById('nomor_sk_petugas');
    const tanggalSkInput = document.getElementById('tanggal_sk_petugas'); // Input Tanggal SK BARU

    function toggleApprovedFields() {
        if (statusPengajuanDropdown.value === 'approved') {
            approvedFieldsDiv.style.display = 'block';
            approvedQuotaInput.setAttribute('required', 'required');
            nomorSkInput.setAttribute('required', 'required');
            tanggalSkInput.setAttribute('required', 'required'); // Tanggal SK juga wajib jika approved

            <?php if (empty($pengajuan['file_sk_petugas'])): ?>
                fileSkPetugasLabelRequired.style.display = 'inline';
                // document.getElementById('file_sk_petugas').setAttribute('required', 'required'); // Can be added if SK file is mandatory for new approval
            <?php else: ?>
                fileSkPetugasLabelRequired.style.display = 'none';
                // document.getElementById('file_sk_petugas').removeAttribute('required');
            <?php endif; ?>
        } else {
            approvedFieldsDiv.style.display = 'none';
            fileSkPetugasLabelRequired.style.display = 'none';
            approvedQuotaInput.removeAttribute('required');
            nomorSkInput.removeAttribute('required');
            tanggalSkInput.removeAttribute('required'); // Remove required if not approved
            // document.getElementById('file_sk_petugas').removeAttribute('required');
        }
    }
    toggleApprovedFields(); // Call on load
    statusPengajuanDropdown.addEventListener('change', toggleApprovedFields);

    // Script for Bootstrap custom file input
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

// Initialize Gijgo Datepicker for Officer's SK Date
$(document).ready(function () {
    if (typeof $ !== 'undefined' && typeof $.fn.datepicker !== 'undefined') {
        $('#tanggal_sk_petugas.gj-datepicker').datepicker({ // Target with gj-datepicker class
            uiLibrary: 'bootstrap4',
            format: 'yyyy-mm-dd',
            showOnFocus: true,
            showRightIcon: true,
            autoClose: true
        });
    }
});
</script>
<?= $this->endSection() ?>