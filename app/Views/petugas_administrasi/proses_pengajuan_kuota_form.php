<?php
$nama_barang_diajukan = esc($pengajuan['nama_barang_kuota'] ?? 'Tidak Diketahui');
?>

<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= esc($subtitle ?? 'Proses Pengajuan Kuota') ?></h1>
        <a href="<?= base_url('petugas_administrasi/daftar_pengajuan_kuota') ?>" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Daftar Pengajuan
        </a>
    </div>

    <?php if (isset($validation) && $validation->getErrors()): ?>
        <div class="alert alert-danger" role="alert">
            <?= $validation->listErrors() ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('message')): ?>
        <?= session()->getFlashdata('message') ?>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Proses Pengajuan Kuota ID: <?= esc($pengajuan['id'] ?? 'N/A') ?>
                dari: <?= esc($pengajuan['NamaPers'] ?? 'Perusahaan tidak ditemukan') ?>
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Informasi Perusahaan</h5>
                    <p><strong>Nama Perusahaan:</strong> <?= esc($pengajuan['NamaPers'] ?? 'N/A') ?></p>
                    <p><strong>Email User:</strong> <?= esc($pengajuan['user_email'] ?? 'N/A') ?></p>
                    <p class="small text-muted"><em>(Data agregat kuota perusahaan saat ini)</em></p>
                    <p><strong>Total Kuota Awal Terdaftar:</strong> <?= esc(number_format($pengajuan['initial_quota_umum_sebelum'] ?? 0, 0, ',', '.')) ?> Unit</p>
                    <p><strong>Total Sisa Kuota Terdaftar:</strong> <?= esc(number_format($pengajuan['remaining_quota_umum_sebelum'] ?? 0, 0, ',', '.')) ?> Unit</p>
                </div>
                <div class="col-md-6">
                    <h5>Detail Pengajuan dari User</h5>
                    <p><strong>No. Surat User:</strong> <?= esc($pengajuan['nomor_surat_pengajuan'] ?? '-') ?></p>
                    <p><strong>Tgl. Surat User:</strong> <?= isset($pengajuan['tanggal_surat_pengajuan']) ? date('d M Y', strtotime($pengajuan['tanggal_surat_pengajuan'])) : '-' ?></p>
                    <p><strong>Perihal User:</strong> <?= esc($pengajuan['perihal_pengajuan'] ?? '-') ?></p>
                    <p><strong>Nama Barang Diajukan:</strong> <span class="font-weight-bold text-info"><?= $nama_barang_diajukan ?></span></p>
                    <p><strong>Jumlah Kuota Diajukan:</strong> <span class="font-weight-bold text-info"><?= esc(number_format($pengajuan['requested_quota'] ?? 0, 0, ',', '.')) ?> Unit</span></p>
                    <p><strong>Alasan Pengajuan:</strong> <?= nl2br(esc($pengajuan['reason'] ?? 'N/A')) ?></p>
                    <p><strong>Tanggal Pengajuan Sistem:</strong> <?= isset($pengajuan['submission_date']) ? date('d M Y H:i:s', strtotime($pengajuan['submission_date'])) : 'N/A' ?></p>
                     <?php if (!empty($pengajuan['file_lampiran_user'])): ?>
                        <p><strong>File Lampiran User:</strong>
                            <!-- [DIREVISI] Mengarahkan link ke method downloadFile -->
                            <a href="<?= base_url('petugas_administrasi/downloadFile/' . $pengajuan['file_lampiran_user']) ?>" target="_blank">
                                Lihat Lampiran
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            <hr>
            <h5>Form Tindakan Admin</h5>
            <form action="<?= base_url('petugas_administrasi/proses_pengajuan_kuota/' . $pengajuan['id']) ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="status_pengajuan">Status Pengajuan <span class="text-danger">*</span></label>
                    <select class="form-control" id="status_pengajuan" name="status_pengajuan" required>
                        <option value="pending" <?= set_select('status_pengajuan', 'pending', ($pengajuan['status'] ?? 'pending') == 'pending') ?>>Pending</option>
                        <option value="diproses" <?= set_select('status_pengajuan', 'diproses', ($pengajuan['status'] ?? '') == 'diproses') ?>>Diproses</option>
                        <option value="approved" <?= set_select('status_pengajuan', 'approved', ($pengajuan['status'] ?? '') == 'approved') ?>>Approved (Disetujui)</option>
                        <option value="rejected" <?= set_select('status_pengajuan', 'rejected', ($pengajuan['status'] ?? '') == 'rejected') ?>>Rejected (Ditolak)</option>
                    </select>
                </div>

                <div id="approved_fields" style="<?= old('status_pengajuan', $pengajuan['status'] ?? '') == 'approved' ? 'display:block;' : 'display:none;'; ?>">
                    <div class="form-group">
                        <label for="approved_quota">Jumlah Kuota Disetujui untuk <span class="text-info font-weight-bold"><?= $nama_barang_diajukan ?></span> <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="approved_quota" name="approved_quota" value="<?= old('approved_quota', $pengajuan['approved_quota'] ?? ($pengajuan['requested_quota'] ?? '0')) ?>" min="0">
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="nomor_sk_petugas">Nomor Surat Keputusan (KEP) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nomor_sk_petugas" name="nomor_sk_petugas" value="<?= old('nomor_sk_petugas', $pengajuan['nomor_sk_petugas'] ?? '') ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="tanggal_sk_petugas">Tanggal Surat Keputusan (KEP) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control gj-datepicker" id="tanggal_sk_petugas" name="tanggal_sk_petugas" placeholder="YYYY-MM-DD" value="<?= old('tanggal_sk_petugas', ($pengajuan['tanggal_sk_petugas'] ?? '') != '0000-00-00' ? ($pengajuan['tanggal_sk_petugas'] ?? '') : '') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="file_sk_petugas">Upload File SK Petugas (.pdf, .jpg, .png, .jpeg maks 2MB) <span id="file_sk_petugas_label_required" class="text-danger" style="display:none;">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="file_sk_petugas" name="file_sk_petugas">
                            <label class="custom-file-label" for="file_sk_petugas">Pilih file SK...</label>
                        </div>
                        <?php if (!empty($pengajuan['file_sk_petugas'])): ?>
                            <small class="form-text text-info">File SK saat ini:
                                <a href="<?= base_url('petugas_administrasi/downloadFile/' . $pengajuan['file_sk_petugas']) ?>" target="_blank">
                                    Lihat File SK Saat Ini
                                </a>. Upload file baru akan menggantikannya.
                            </small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="admin_notes">Catatan Admin (Jika ditolak, alasan penolakan wajib diisi)</label>
                    <textarea class="form-control" id="admin_notes" name="admin_notes" rows="3"><?= old('admin_notes', $pengajuan['admin_notes'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Proses Pengajuan</button>
                <a href="<?= base_url('petugas_administrasi/daftar_pengajuan_kuota') ?>" class="btn btn-secondary ml-2">Batal</a>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusDropdown = document.getElementById('status_pengajuan');
    const approvedFields = document.getElementById('approved_fields');
    const requiredLabel = document.getElementById('file_sk_petugas_label_required');
    
    function toggleApprovedFields() {
        const isApproved = statusDropdown.value === 'approved';
        approvedFields.style.display = isApproved ? 'block' : 'none';
        
        ['approved_quota', 'nomor_sk_petugas', 'tanggal_sk_petugas'].forEach(id => {
            const input = document.getElementById(id);
            if (isApproved) {
                input.setAttribute('required', 'required');
            } else {
                input.removeAttribute('required');
            }
        });

        const fileInput = document.getElementById('file_sk_petugas');
        const hasExistingFile = <?= !empty($pengajuan['file_sk_petugas']) ? 'true' : 'false' ?>;
        
        if (isApproved && !hasExistingFile) {
            requiredLabel.style.display = 'inline';
            fileInput.setAttribute('required', 'required');
        } else {
            requiredLabel.style.display = 'none';
            fileInput.removeAttribute('required');
        }
    }

    statusDropdown.addEventListener('change', toggleApprovedFields);
    toggleApprovedFields(); // Initial check

    // Custom file input label
    document.querySelectorAll('.custom-file-input').forEach(input => {
        input.addEventListener('change', function (e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'Pilih file SK...';
            const nextLabel = e.target.nextElementSibling;
            nextLabel.innerText = fileName;
        });
    });
});

$(document).ready(function () {
    if (typeof $ !== 'undefined' && typeof $.fn.datepicker !== 'undefined') {
        $('#tanggal_sk_petugas').datepicker({
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
