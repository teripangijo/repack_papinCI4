<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
    <?= esc($subtitle ?? 'Pengajuan Penambahan Kuota') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$validation = \Config\Services::validation();
?>
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= esc($subtitle ?? 'Pengajuan Penambahan Kuota') ?></h1>
        <a href="<?= base_url('user/daftar_pengajuan_kuota') ?>" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-list fa-sm text-white-50"></i> Lihat Daftar Pengajuan Kuota
        </a>
    </div>

    <?= $validation->listErrors('list') ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Formulir Pengajuan Kuota Returnable Package</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($user_perusahaan)) : ?>
                         <div class="alert alert-danger" role="alert">
                            Data perusahaan Anda belum lengkap. Tidak dapat mengajukan kuota. Silakan <a href="<?= base_url('user/edit') ?>" class="alert-link">lengkapi profil perusahaan Anda</a> terlebih dahulu.
                        </div>
                    <?php elseif (isset($user['is_active']) && $user['is_active'] == 0) : ?>
                        <div class="alert alert-warning" role="alert">
                            Akun Anda belum aktif. Tidak dapat mengajukan kuota. Mohon <a href="<?= base_url('user/edit') ?>" class="alert-link">lengkapi profil perusahaan Anda</a> jika belum, atau hubungi Administrator.
                        </div>
                    <?php else: ?>
                        <form action="<?= base_url('user/pengajuan_kuota') ?>" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <?= csrf_field() ?>

                            <div class="alert alert-secondary small">
                                <strong>Informasi Perusahaan & Kuota Saat Ini:</strong><br>
                                Nama: <?= esc($user_perusahaan['NamaPers'] ?? 'N/A') ?><br>
                                NPWP: <?= esc($user_perusahaan['npwp'] ?? 'N/A') ?><br>
                                <hr class="my-1">
                                Total Kuota Awal (Semua Barang): <?= esc(number_format($total_kuota_awal_semua_barang ?? 0, 0, ',', '.')) ?> Unit<br>
                                Total Sisa Kuota (Semua Barang): <?= esc(number_format($total_sisa_kuota_semua_barang ?? 0, 0, ',', '.')) ?> Unit
                                <p class="mt-2 mb-0"><em>Catatan: Informasi kuota di atas adalah total gabungan dari semua jenis barang yang telah disetujui. Pengajuan ini akan diproses untuk kuota barang spesifik.</em></p>
                            </div>
                            <hr>

                            <h5 class="text-gray-800 my-3">Detail Pengajuan Penetapan/Penambahan Kuota</h5>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="nomor_surat_pengajuan">Nomor Surat Pengajuan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?= $validation->hasError('nomor_surat_pengajuan') ? 'is-invalid' : '' ?>" id="nomor_surat_pengajuan" name="nomor_surat_pengajuan" value="<?= old('nomor_surat_pengajuan') ?>" placeholder="No. Surat dari Perusahaan" required>
                                    <div class="invalid-feedback"><?= $validation->getError('nomor_surat_pengajuan') ?></div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="tanggal_surat_pengajuan">Tanggal Surat Pengajuan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control gj-datepicker <?= $validation->hasError('tanggal_surat_pengajuan') ? 'is-invalid' : '' ?>" id="tanggal_surat_pengajuan" name="tanggal_surat_pengajuan" placeholder="YYYY-MM-DD" value="<?= old('tanggal_surat_pengajuan', date('Y-m-d')) ?>" required>
                                    <div class="invalid-feedback"><?= $validation->getError('tanggal_surat_pengajuan') ?></div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="perihal_pengajuan">Perihal Surat Pengajuan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= $validation->hasError('perihal_pengajuan') ? 'is-invalid' : '' ?>" id="perihal_pengajuan" name="perihal_pengajuan" value="<?= old('perihal_pengajuan') ?>" placeholder="Contoh: Permohonan Penambahan Kuota Returnable Package" required>
                                <div class="invalid-feedback"><?= $validation->getError('perihal_pengajuan') ?></div>
                            </div>

                            <div class="form-group">
                                <label for="nama_barang_kuota">Nama/Jenis Barang (Untuk Kuota Ini) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= $validation->hasError('nama_barang_kuota') ? 'is-invalid' : '' ?>" id="nama_barang_kuota" name="nama_barang_kuota" value="<?= old('nama_barang_kuota') ?>" placeholder="Contoh: Fiber Box, Pallet Kayu, Plastic Bin" required>
                                <div class="invalid-feedback"><?= $validation->getError('nama_barang_kuota') ?></div>
                                <small class="form-text text-muted">Masukkan nama barang spesifik yang Anda ajukan kuotanya.</small>
                            </div>

                            <div class="form-group">
                                <label for="requested_quota">Jumlah Kuota yang Diajukan (Unit) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control <?= $validation->hasError('requested_quota') ? 'is-invalid' : '' ?>" id="requested_quota" name="requested_quota" value="<?= old('requested_quota') ?>" placeholder="Masukkan jumlah kuota" min="1" required>
                                <div class="invalid-feedback"><?= $validation->getError('requested_quota') ?></div>
                            </div>

                            <div class="form-group">
                                <label for="reason">Alasan Pengajuan <span class="text-danger">*</span></label>
                                <textarea class="form-control <?= $validation->hasError('reason') ? 'is-invalid' : '' ?>" id="reason" name="reason" rows="4" placeholder="Jelaskan alasan pengajuan penambahan kuota Anda untuk jenis barang ini" required><?= old('reason') ?></textarea>
                                <div class="invalid-feedback"><?= $validation->getError('reason') ?></div>
                            </div>
                            
                            <div class="form-group">
                                <label for="file_lampiran_pengajuan">Upload Dokumen Pendukung (Opsional, PDF/DOC/Gambar maks 2MB)</label>
                                <div class="custom-file">
                                     <input type="file" class="custom-file-input <?= $validation->hasError('file_lampiran_pengajuan') ? 'is-invalid' : '' ?>" id="file_lampiran_pengajuan" name="file_lampiran_pengajuan" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                     <label class="custom-file-label" for="file_lampiran_pengajuan">Pilih file...</label>
                                </div>
                                <div class="invalid-feedback d-block mt-1"><?= $validation->getError('file_lampiran_pengajuan') ?></div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-user btn-block mt-4">
                                <i class="fas fa-paper-plane fa-fw"></i> Kirim Pengajuan Kuota
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Pengajuan Kuota</h6>
                </div>
                <div class="card-body small">
                    <p>Gunakan formulir ini untuk mengajukan penambahan kuota returnable package untuk <strong>jenis barang tertentu</strong>.</p>
                    <p>Pastikan semua data yang Anda masukkan sudah benar dan sesuai dengan dokumen pendukung (jika ada).</p>
                    <p>Pengajuan Anda akan ditinjau oleh Administrator. Anda dapat melihat status pengajuan Anda di menu "Daftar Pengajuan Kuota".</p>
                    <p>Jika disetujui, kuota untuk jenis barang tersebut akan ditambahkan ke profil perusahaan Anda.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof $ !== 'undefined' && typeof $.fn.datepicker !== 'undefined') {
            const datepickerConfig = {
                uiLibrary: 'bootstrap4',
                format: 'yyyy-mm-dd',
                showOnFocus: true,
                showRightIcon: true,
                autoClose: true
            };
            $('#tanggal_surat_pengajuan').datepicker(datepickerConfig);
        }

        document.querySelectorAll('.custom-file-input').forEach(function(input) {
            const label = input.nextElementSibling;
            const originalText = label.innerHTML;
            input.addEventListener('change', function(e) {
                const fileName = e.target.files.length > 0 ? e.target.files[0].name : originalText;
                label.innerHTML = fileName;
            });
        });
    });
</script>
<?= $this->endSection() ?>
