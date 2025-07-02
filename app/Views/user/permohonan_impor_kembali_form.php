<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
    <?= esc($subtitle ?? 'Form Permohonan Impor Kembali') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$validation = \Config\Services::validation();

$selected_id_kuota_barang_js = old('id_kuota_barang_selected', '');
$selected_nama_barang_js = old('NamaBarang', '');
$prefill_skep = '';

// Prefill logic based on old input or database values
if (!empty($selected_id_kuota_barang_js) && !empty($list_barang_berkuota)) {
    foreach ($list_barang_berkuota as $barang_opt) {
        if ($barang_opt['id_kuota_barang'] == $selected_id_kuota_barang_js) {
            $prefill_skep = $barang_opt['nomor_skep_asal'] ?? 'SKEP Tidak Ada';
            $selected_nama_barang_js = $barang_opt['nama_barang'];
            break;
        }
    }
}
?>
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= esc($subtitle ?? 'Form Permohonan Impor Kembali') ?></h1>
        <a href="<?= base_url('user/daftarPermohonan') ?>" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Daftar Permohonan
        </a>
    </div>

    <!-- <?= $validation->listErrors('list') ?> -->
    <!-- Flash Messages are handled by the main layout -->

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Formulir Pengajuan Permohonan Impor Kembali</h6>
        </div>
        <div class="card-body">
            <?php if (isset($user['is_active']) && $user['is_active'] == 1 && !empty($user_perusahaan)) : ?>
                <form action="<?= base_url('user/permohonan_impor_kembali') ?>" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <?= csrf_field() ?>

                    <div class="alert alert-secondary small">
                        <strong>Data Perusahaan:</strong><br>
                        Nama: <?= esc($user_perusahaan['NamaPers'] ?? 'N/A') ?><br>
                        NPWP: <?= esc($user_perusahaan['npwp'] ?? 'N/A') ?>
                    </div>
                    <hr>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="nomorSurat">Nomor Surat Pengajuan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= $validation->hasError('nomorSurat') ? 'is-invalid' : '' ?>" id="nomorSurat" name="nomorSurat" value="<?= old('nomorSurat') ?>" required>
                            <div class="invalid-feedback"><?= $validation->getError('nomorSurat') ?></div>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="TglSurat">Tanggal Surat <span class="text-danger">*</span></label>
                            <input type="text" class="form-control gj-datepicker <?= $validation->hasError('TglSurat') ? 'is-invalid' : '' ?>" id="TglSurat" name="TglSurat" placeholder="YYYY-MM-DD" value="<?= old('TglSurat', date('Y-m-d')) ?>" required>
                            <div class="invalid-feedback"><?= $validation->getError('TglSurat') ?></div>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="Perihal">Perihal Surat <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= $validation->hasError('Perihal') ? 'is-invalid' : '' ?>" id="Perihal" name="Perihal" value="<?= old('Perihal') ?>" required>
                            <div class="invalid-feedback"><?= $validation->getError('Perihal') ?></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-5">
                            <label for="id_kuota_barang_selected">Pilih Barang Berdasarkan Kuota <span class="text-danger">*</span></label>
                            <select class="form-control <?= ($validation->hasError('id_kuota_barang_selected') || $validation->hasError('NamaBarang')) ? 'is-invalid' : '' ?>" id="id_kuota_barang_selected" name="id_kuota_barang_selected" required>
                                <option value="">-- Pilih Barang & Kuota SKEP --</option>
                                <?php if (!empty($list_barang_berkuota)): ?>
                                    <?php foreach($list_barang_berkuota as $barang): ?>
                                        <option value="<?= esc($barang['id_kuota_barang'], 'attr') ?>"
                                                data-nama_barang="<?= esc($barang['nama_barang'], 'attr') ?>"
                                                data-sisa_kuota="<?= esc($barang['remaining_quota_barang'] ?? 0, 'attr') ?>"
                                                data-skep="<?= esc($barang['nomor_skep_asal'] ?? '', 'attr') ?>"
                                                <?= set_select('id_kuota_barang_selected', $barang['id_kuota_barang'], old('id_kuota_barang_selected') == $barang['id_kuota_barang']) ?>>
                                            <?= esc($barang['nama_barang']) ?> (Sisa: <?= number_format($barang['remaining_quota_barang'] ?? 0, 0, ',', '.') ?> Unit - SKEP: <?= esc($barang['nomor_skep_asal'] ?? 'N/A') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="invalid-feedback"><?= $validation->getError('id_kuota_barang_selected') ?: $validation->getError('NamaBarang') ?></div>
                            <small id="sisaKuotaInfo" class="form-text text-info"></small>
                            <input type="hidden" name="NamaBarang" id="NamaBarangHidden" value="<?= esc($selected_nama_barang_js) ?>">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="JumlahBarang">Jumlah Barang Diajukan <span class="text-danger">*</span></label>
                            <input type="number" class="form-control <?= $validation->hasError('JumlahBarang') ? 'is-invalid' : '' ?>" id="JumlahBarang" name="JumlahBarang" value="<?= old('JumlahBarang') ?>" required min="1" max="9999999999">
                            <div class="invalid-feedback"><?= $validation->getError('JumlahBarang') ?></div>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="NoSkepOtomatis">No. SKEP (Dasar Permohonan)</label>
                            <input type="text" class="form-control" id="NoSkepOtomatis" value="<?= old('NoSkepOtomatis', $prefill_skep) ?>" readonly title="No. SKEP otomatis terisi berdasarkan barang yang dipilih.">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="NegaraAsal">Negara Asal Barang <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= $validation->hasError('NegaraAsal') ? 'is-invalid' : '' ?>" id="NegaraAsal" name="NegaraAsal" value="<?= old('NegaraAsal') ?>" required>
                            <div class="invalid-feedback"><?= $validation->getError('NegaraAsal') ?></div>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="NamaKapal">Nama Kapal / Sarana Pengangkut <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= $validation->hasError('NamaKapal') ? 'is-invalid' : '' ?>" id="NamaKapal" name="NamaKapal" value="<?= old('NamaKapal') ?>" required>
                            <div class="invalid-feedback"><?= $validation->getError('NamaKapal') ?></div>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="noVoyage">No. Voyage / Flight <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= $validation->hasError('noVoyage') ? 'is-invalid' : '' ?>" id="noVoyage" name="noVoyage" value="<?= old('noVoyage') ?>" required>
                            <div class="invalid-feedback"><?= $validation->getError('noVoyage') ?></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="TglKedatangan">Tanggal Perkiraan Kedatangan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control gj-datepicker <?= $validation->hasError('TglKedatangan') ? 'is-invalid' : '' ?>" id="TglKedatangan" name="TglKedatangan" placeholder="YYYY-MM-DD" value="<?= old('TglKedatangan') ?>" required>
                            <div class="invalid-feedback"><?= $validation->getError('TglKedatangan') ?></div>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="TglBongkar">Tanggal Perkiraan Bongkar <span class="text-danger">*</span></label>
                            <input type="text" class="form-control gj-datepicker <?= $validation->hasError('TglBongkar') ? 'is-invalid' : '' ?>" id="TglBongkar" name="TglBongkar" placeholder="YYYY-MM-DD" value="<?= old('TglBongkar') ?>" required>
                            <div class="invalid-feedback"><?= $validation->getError('TglBongkar') ?></div>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="lokasi">Lokasi Bongkar <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= $validation->hasError('lokasi') ? 'is-invalid' : '' ?>" id="lokasi" name="lokasi" value="<?= old('lokasi') ?>" required>
                            <div class="invalid-feedback"><?= $validation->getError('lokasi') ?></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="file_bc_manifest">Upload File BC 1.1 / Manifest <span class="text-danger">*</span> <span class="text-info small">(Wajib, max 2MB: Hanya PDF)</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input <?= $validation->hasError('file_bc_manifest') ? 'is-invalid' : '' ?>" id="file_bc_manifest" name="file_bc_manifest" required accept=".pdf">
                            <label class="custom-file-label" for="file_bc_manifest">Pilih file (PDF)...</label>
                        </div>
                        <div class="invalid-feedback d-block mt-1"><?= $validation->getError('file_bc_manifest') ?></div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-user btn-block mt-4" id="submitPermohonanBtn" <?= empty($list_barang_berkuota) ? 'disabled title="Tidak ada barang dengan kuota aktif untuk diajukan."' : '' ?>>
                        <i class="fas fa-paper-plane fa-fw"></i> Ajukan Permohonan
                    </button>
                </form>

            <?php else : ?>
                <div class="alert alert-warning" role="alert">
                    Akun Anda belum aktif atau data perusahaan belum lengkap. Silakan lengkapi <a href="<?= base_url('user/edit') ?>" class="alert-link">profil perusahaan Anda</a> terlebih dahulu.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Inisialisasi Datepicker
    if (typeof $ !== 'undefined' && typeof $.fn.datepicker !== 'undefined') {
        const datepickerConfig = { uiLibrary: 'bootstrap4', format: 'yyyy-mm-dd', showOnFocus: true, showRightIcon: true, autoClose: true };
        $('#TglSurat').datepicker(datepickerConfig);
        $('#TglKedatangan').datepicker(datepickerConfig);
        $('#TglBongkar').datepicker(datepickerConfig);
    }

    // Logika untuk Kuota Barang
    const kuotaSelect = document.getElementById('id_kuota_barang_selected');
    const submitBtn = document.getElementById('submitPermohonanBtn');
    const jumlahInput = document.getElementById('JumlahBarang');

    function updateFormState() {
        const selectedOption = kuotaSelect.options[kuotaSelect.selectedIndex];
        const sisaKuota = parseInt(selectedOption.dataset.sisa_kuota) || 0;
        const skep = selectedOption.dataset.skep || 'SKEP Tidak Tersedia';
        const namaBarang = selectedOption.dataset.nama_barang || '';

        document.getElementById('sisaKuotaInfo').textContent = 'Sisa kuota untuk barang (' + namaBarang + ') ini: ' + sisaKuota.toLocaleString() + ' Unit';
        document.getElementById('NoSkepOtomatis').value = skep;
        jumlahInput.setAttribute('max', sisaKuota);
        document.getElementById('NamaBarangHidden').value = namaBarang;
        
        if (sisaKuota <= 0 || kuotaSelect.value === "") {
            submitBtn.disabled = true;
            submitBtn.title = 'Tidak ada kuota tersedia atau barang belum dipilih.';
            jumlahInput.value = 0;
            jumlahInput.readOnly = true;
        } else {
            submitBtn.disabled = false;
            submitBtn.title = '';
            jumlahInput.readOnly = false;
        }
    }

    if(kuotaSelect){
        kuotaSelect.addEventListener('change', function() {
            jumlahInput.value = ''; // Reset jumlah on change
            updateFormState();
        });
        updateFormState(); // Initial check
    }
    
    if(jumlahInput){
        jumlahInput.addEventListener('input', function() {
            let jumlahDimohon = parseInt(this.value) || 0;
            const sisaKuotaMax = parseInt(this.getAttribute('max')) || 0;
            
            document.querySelector('#sisaKuotaInfo .text-danger')?.remove();

            if (jumlahDimohon > sisaKuotaMax && kuotaSelect.value !== "") {
                this.value = sisaKuotaMax;
                document.getElementById('sisaKuotaInfo').insertAdjacentHTML('beforeend', ' <strong class="text-danger">(Jumlah melebihi sisa!)</strong>');
                jumlahDimohon = sisaKuotaMax;
            }
            
            if(kuotaSelect.value === "") {
                submitBtn.disabled = true;
                submitBtn.title = 'Pilih barang berkuota terlebih dahulu.';
            } else if (jumlahDimohon <= 0) {
                submitBtn.disabled = true;
                submitBtn.title = 'Jumlah barang harus lebih dari 0.';
            } else {
                submitBtn.disabled = false;
                submitBtn.title = '';
            }
        });
    }
    
    // Logika untuk Label Custom File Input
    document.querySelectorAll('.custom-file-input').forEach(function(input) {
        const label = input.nextElementSibling;
        const originalText = label.innerHTML;
        input.addEventListener('change', function(e) {
            const fileName = e.target.files.length > 0 ? e.target.files[0].name : originalText;
            label.innerHTML = fileName;
        });
    });

    // Logika untuk Validasi Dinamis Input Standar
    function initializeDynamicValidation() {
        const invalidInputs = document.querySelectorAll('.is-invalid');
        invalidInputs.forEach(function(input) {
            const validationHandler = function(event) {
                const currentInput = event.target;
                if ((currentInput.type === 'file' && currentInput.files.length > 0) || (currentInput.type !== 'file' && currentInput.value.trim() !== '')) {
                    currentInput.classList.remove('is-invalid');
                    let feedbackElement;
                    if (currentInput.parentElement.classList.contains('custom-file')) {
                        feedbackElement = currentInput.parentElement.nextElementSibling;
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

    // =================================================================
    // KODE BARU: PERBAIKAN UNTUK VALIDASI GIJGO DATEPICKER
    // =================================================================
    if (typeof $ !== 'undefined' && typeof $.fn.datepicker !== 'undefined') {
        // Target semua input yang menggunakan gj-datepicker
        $('.gj-datepicker').each(function() {
            var $datepickerInput = $(this);
            
            // Dengarkan event 'change' dari plugin Gijgo Datepicker
            $datepickerInput.on('change', function(e) {
                var inputElement = e.target;
                // Jika input sudah memiliki nilai (setelah dipilih)
                if (inputElement.value) {
                    // Hapus class 'is-invalid' untuk menghilangkan border merah
                    $datepickerInput.removeClass('is-invalid');
                    // Cari elemen .invalid-feedback berikutnya dan sembunyikan
                    var $feedbackElement = $datepickerInput.next('.invalid-feedback');
                    if ($feedbackElement.length) {
                        $feedbackElement.hide();
                    }
                }
            });
        });
    }
});
</script>
<?= $this->endSection() ?>
