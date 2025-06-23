<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= esc($subtitle ?? 'Penunjukan Petugas Pemeriksa') ?></h1>
        <a href="<?= site_url('petugas_administrasi/permohonanMasuk') ?>" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Daftar Permohonan
        </a>
    </div>

    <?php if (session()->getFlashdata('message_transient')) : ?>
        <?= session()->getFlashdata('message_transient') ?>
    <?php endif; ?>
    <?php if (session()->getFlashdata('message')) : ?>
        <?= session()->getFlashdata('message') ?>
    <?php endif; ?>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Formulir Penunjukan Petugas untuk Permohonan ID: <?= esc($permohonan['id']) ?> (<?= esc($permohonan['NamaPers'] ?? 'N/A') ?>)</h6>
        </div>
        <div class="card-body">
            <p>Status Permohonan Saat Ini:
                <?php
                $status_text_current = '-'; $status_badge_current = 'secondary';
                if (isset($permohonan['status'])) {
                    switch ($permohonan['status']) {
                        case '0': $status_text_current = 'Baru Masuk'; $status_badge_current = 'info'; break;
                        case '5': $status_text_current = 'Diproses Admin'; $status_badge_current = 'warning'; break;
                        case '1': $status_text_current = 'Penunjukan Pemeriksa'; $status_badge_current = 'primary'; break;
                        default: $status_text_current = 'Status Tidak Dikenal (' . esc($permohonan['status']) . ')';
                    }
                }
                ?>
                <span class="badge badge-pill badge-<?= $status_badge_current ?>"><?= esc($status_text_current) ?></span>
            </p>
            <hr>

            <form action="<?= site_url('petugas_administrasi/penunjukanPetugas/' . $permohonan['id']) ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="petugas_id">Pilih Petugas/Pemeriksa <span class="text-danger">*</span></label>
                    <select class="form-control <?= (isset($validation) && $validation->hasError('petugas_id')) ? 'is-invalid' : '' ?>" id="petugas_id" name="petugas_id" required>
                        <option value="">-- Pilih Petugas --</option>
                        <?php if (!empty($list_petugas)): ?>
                            <?php 
                                $selected_petugas = old('petugas_id', $permohonan['petugas'] ?? '');
                            ?>
                            <?php foreach ($list_petugas as $petugas_item): ?>
                                <option value="<?= esc($petugas_item['id']) ?>" <?= ($selected_petugas == $petugas_item['id']) ? 'selected' : '' ?>>
                                    <?= esc($petugas_item['Nama']) ?> (NIP: <?= esc($petugas_item['NIP'] ?? '-') ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">Tidak ada petugas tersedia</option>
                        <?php endif; ?>
                    </select>
                    <?php if (isset($validation) && $validation->hasError('petugas_id')): ?>
                        <div class="invalid-feedback"><?= $validation->getError('petugas_id') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="nomor_surat_tugas">Nomor Surat Tugas <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?= (isset($validation) && $validation->hasError('nomor_surat_tugas')) ? 'is-invalid' : '' ?>" id="nomor_surat_tugas" name="nomor_surat_tugas" value="<?= old('nomor_surat_tugas', $permohonan['NoSuratTugas'] ?? '') ?>" required>
                    <?php if (isset($validation) && $validation->hasError('nomor_surat_tugas')): ?>
                        <div class="invalid-feedback"><?= $validation->getError('nomor_surat_tugas') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="tanggal_surat_tugas">Tanggal Surat Tugas <span class="text-danger">*</span></label>
                    <input type="text" class="form-control datepicker <?= (isset($validation) && $validation->hasError('tanggal_surat_tugas')) ? 'is-invalid' : '' ?>" id="tanggal_surat_tugas" name="tanggal_surat_tugas" value="<?= old('tanggal_surat_tugas', ($permohonan['TglSuratTugas'] ?? '') != '0000-00-00' ? ($permohonan['TglSuratTugas'] ?? '') : '') ?>" placeholder="YYYY-MM-DD" required readonly>
                    <?php if (isset($validation) && $validation->hasError('tanggal_surat_tugas')): ?>
                        <div class="invalid-feedback"><?= $validation->getError('tanggal_surat_tugas') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="file_surat_tugas">Upload File Surat Tugas (PDF, JPG, PNG, DOC, DOCX maks 2MB)</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input <?= (isset($validation) && $validation->hasError('file_surat_tugas')) ? 'is-invalid' : '' ?>" id="file_surat_tugas" name="file_surat_tugas">
                        <label class="custom-file-label" for="file_surat_tugas">Pilih file...</label>
                    </div>
                    <?php if (!empty($permohonan['FileSuratTugas'])): ?>
                        <small class="form-text text-muted mt-1">File saat ini:
                            <a href="<?= site_url('petugas_administrasi/downloadFile/' . esc($permohonan['FileSuratTugas'])) ?>" target="_blank">
                                Lihat File Saat Ini
                            </a>. Pilih file baru akan menggantikannya.
                        </small>
                    <?php endif; ?>
                    <?php if (isset($validation) && $validation->hasError('file_surat_tugas')): ?>
                        <div class="invalid-feedback d-block"><?= $validation->getError('file_surat_tugas') ?></div>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn btn-primary">Simpan Penunjukan</button>
                <a href="<?= site_url('petugas_administrasi/permohonanMasuk') ?>" class="btn btn-secondary ml-2">Batal</a>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function(){
    // Initialize datepicker
    if (typeof $.fn.datepicker !== 'undefined') {
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true,
            uiLibrary: 'bootstrap4' // Assuming Gijgo datepicker from other files
        });
    } else {
        console.error("Datepicker library is not loaded.");
    }

    $('.custom-file-input').on('change', function(event) {
        var inputFile = event.target;
        if (inputFile.files.length > 0) {
            var fileName = inputFile.files[0].name;
            $(inputFile).next('.custom-file-label').addClass("selected").html(fileName);
        }
    });

    // =================================================================
    // VALIDASI DINAMIS
    // =================================================================
    function initializeDynamicValidation() {
        const invalidInputs = document.querySelectorAll('.is-invalid');
        
        invalidInputs.forEach(function(input) {
            const validationHandler = function(event) {
                const currentInput = event.target;
                
                if ((currentInput.type === 'file' && currentInput.files.length > 0) || (currentInput.type !== 'file' && currentInput.value.trim() !== '')) {
                    currentInput.classList.remove('is-invalid');
                    
                    let feedbackElement;
                    
                    if (currentInput.parentElement.classList.contains('custom-file')) {
                        let sibling = currentInput.parentElement.nextElementSibling;
                        while(sibling) {
                            if (sibling.classList.contains('invalid-feedback')) {
                                feedbackElement = sibling;
                                break;
                            }
                            sibling = sibling.nextElementSibling;
                        }
                    } else {
                        feedbackElement = currentInput.nextElementSibling;
                    }
                    
                    if (feedbackElement && feedbackElement.classList.contains('invalid-feedback')) {
                        feedbackElement.style.display = 'none';
                    }
                    
                    currentInput.removeEventListener('input', validationHandler);
                    currentInput.removeEventListener('change', validationHandler);
                }
            };
            
            input.addEventListener('input', validationHandler);
            input.addEventListener('change', validationHandler);
        });
    }

    initializeDynamicValidation();
});
</script>
<?= $this->endSection() ?>
