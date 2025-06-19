<?= $this->extend('Layouts/main') ?>

<?php
// Blok logika ini bisa tetap di sini atau dipindahkan ke controller untuk view yang lebih bersih
$id_kuota_barang_saat_ini = old('id_kuota_barang_selected', $permohonan_edit['id_kuota_barang_digunakan'] ?? '');
$nama_barang_saat_ini = old('NamaBarang', $permohonan_edit['NamaBarang'] ?? '');
$jumlah_barang_saat_ini_di_permohonan = (float)($permohonan_edit['JumlahBarang'] ?? 0);
$no_skep_saat_ini = $permohonan_edit['NoSkep'] ?? '';

$sisa_kuota_efektif_awal_display = 'N/A';
if (!empty($id_kuota_barang_saat_ini) && isset($list_barang_berkuota) && is_array($list_barang_berkuota)) {
    foreach ($list_barang_berkuota as $barang_opt_init) {
        if ($barang_opt_init['id_kuota_barang'] == $id_kuota_barang_saat_ini) {
            $sisa_kuota_asli_db = (float)($barang_opt_init['remaining_quota_barang'] ?? 0);
            $sisa_kuota_efektif_awal_display = number_format($sisa_kuota_asli_db + $jumlah_barang_saat_ini_di_permohonan, 0, ',', '.');
            if(empty($no_skep_saat_ini) && isset($barang_opt_init['nomor_skep_asal'])) {
                $no_skep_saat_ini = $barang_opt_init['nomor_skep_asal'];
            }
            break;
        }
    }
}
?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= esc($subtitle ?? 'Edit Permohonan Impor Kembali') ?></h1>
        <a href="<?= site_url('petugas_administrasi/permohonanMasuk') ?>" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Daftar Permohonan
        </a>
    </div>

    <?php if (session()->getFlashdata('message')) : ?>
        <?= session()->getFlashdata('message') ?>
    <?php endif; ?>
    <?php if (isset($validation) && $validation->getErrors()) : ?>
        <div class="alert alert-danger mt-3" role="alert">
            <?= $validation->listErrors() ?>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Formulir Edit Permohonan Impor Kembali (ID Aju: <?= esc($permohonan_edit['id']) ?>)
            </h6>
        </div>
        <div class="card-body">
            <div class="alert alert-secondary small">
                <strong>Data Perusahaan:</strong><br>
                Nama: <?= esc($user_perusahaan['NamaPers'] ?? 'N/A') ?><br>
                NPWP: <?= esc($user_perusahaan['npwp'] ?? 'N/A') ?>
            </div>
            <hr>

            <form action="<?= site_url('petugas_administrasi/edit_permohonan/' . $permohonan_edit['id']) ?>" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                <?= csrf_field() ?>
            
                <input type="hidden" name="id_kuota_barang_selected" id="id_kuota_barang_selected" value="<?= old('id_kuota_barang_selected', $id_kuota_barang_saat_ini) ?>">
                <input type="hidden" name="NamaBarang" id="NamaBarangHidden" value="<?= old('NamaBarang', $nama_barang_saat_ini) ?>">

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="nomorSurat">Nomor Surat Pengajuan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= (isset($validation) && $validation->hasError('nomorSurat')) ? 'is-invalid' : '' ?>" id="nomorSurat" name="nomorSurat" value="<?= old('nomorSurat', $permohonan_edit['nomorSurat'] ?? '') ?>" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="TglSurat">Tanggal Surat <span class="text-danger">*</span></label>
                        <input type="text" class="form-control gj-datepicker <?= (isset($validation) && $validation->hasError('TglSurat')) ? 'is-invalid' : '' ?>" id="TglSurat" name="TglSurat" placeholder="YYYY-MM-DD" value="<?= old('TglSurat', ($permohonan_edit['TglSurat'] ?? '') != '0000-00-00' ? ($permohonan_edit['TglSurat'] ?? '') : '') ?>" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="Perihal">Perihal Surat <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= (isset($validation) && $validation->hasError('Perihal')) ? 'is-invalid' : '' ?>" id="Perihal" name="Perihal" value="<?= old('Perihal', $permohonan_edit['Perihal'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-5">
                        <label for="id_kuota_barang_selected_dropdown">Nama / Jenis Barang (sesuai kuota) <span class="text-danger">*</span></label>
                        <select class="form-control <?= (isset($validation) && ($validation->hasError('id_kuota_barang_selected') || $validation->hasError('NamaBarang'))) ? 'is-invalid' : '' ?>" id="id_kuota_barang_selected_dropdown" name="id_kuota_barang_selected_dropdown_display" required>
                            <option value="">-- Pilih Barang & Kuota SKEP --</option>
                            <?php if (!empty($list_barang_berkuota)): ?>
                                <?php foreach($list_barang_berkuota as $barang): ?>
                                    <?php $isSelected = ($id_kuota_barang_saat_ini == $barang['id_kuota_barang']); ?>
                                    <option value="<?= esc($barang['id_kuota_barang']) ?>"
                                            data-nama_barang="<?= esc($barang['nama_barang']) ?>"
                                            data-sisa_kuota_asli="<?= esc($barang['remaining_quota_barang'] ?? 0) ?>"
                                            data-skep="<?= esc($barang['nomor_skep_asal'] ?? '') ?>"
                                            <?= $isSelected ? 'selected' : '' ?>>
                                        <?= esc($barang['nama_barang']) ?> (Sisa Asli: <?= number_format($barang['remaining_quota_barang'] ?? 0, 0, ',', '.') ?> Unit - SKEP: <?= esc($barang['nomor_skep_asal'] ?? 'N/A') ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <small id="sisaKuotaInfoEdit" class="form-text text-info">Sisa kuota efektif: <?= esc($sisa_kuota_efektif_awal_display) ?> Unit</small>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="JumlahBarang">Jumlah Barang Diajukan <span class="text-danger">*</span></label>
                        <input type="number" class="form-control <?= (isset($validation) && $validation->hasError('JumlahBarang')) ? 'is-invalid' : '' ?>" id="JumlahBarang" name="JumlahBarang" value="<?= old('JumlahBarang', $permohonan_edit['JumlahBarang'] ?? '') ?>" required min="1" step="any">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="NoSkepOtomatis">No. SKEP (Dasar Permohonan)</label>
                        <input type="text" class="form-control" id="NoSkepOtomatis" name="NoSkepOtomatis" value="<?= old('NoSkepOtomatis', $no_skep_saat_ini) ?>" readonly title="No. SKEP otomatis terisi berdasarkan barang yang dipilih.">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4"><label for="NegaraAsal">Negara Asal Barang <span class="text-danger">*</span></label><input type="text" class="form-control" id="NegaraAsal" name="NegaraAsal" value="<?= old('NegaraAsal', $permohonan_edit['NegaraAsal'] ?? '') ?>" required></div>
                    <div class="form-group col-md-4"><label for="NamaKapal">Nama Kapal / Sarana Pengangkut <span class="text-danger">*</span></label><input type="text" class="form-control" id="NamaKapal" name="NamaKapal" value="<?= old('NamaKapal', $permohonan_edit['NamaKapal'] ?? '') ?>" required></div>
                    <div class="form-group col-md-4"><label for="noVoyage">No. Voyage / Flight <span class="text-danger">*</span></label><input type="text" class="form-control" id="noVoyage" name="noVoyage" value="<?= old('noVoyage', $permohonan_edit['noVoyage'] ?? '') ?>" required></div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4"><label for="TglKedatangan">Tanggal Perkiraan Kedatangan <span class="text-danger">*</span></label><input type="text" class="form-control gj-datepicker" id="TglKedatangan" name="TglKedatangan" placeholder="YYYY-MM-DD" value="<?= old('TglKedatangan', ($permohonan_edit['TglKedatangan'] ?? '') != '0000-00-00' ? ($permohonan_edit['TglKedatangan'] ?? '') : '') ?>" required></div>
                    <div class="form-group col-md-4"><label for="TglBongkar">Tanggal Perkiraan Bongkar <span class="text-danger">*</span></label><input type="text" class="form-control gj-datepicker" id="TglBongkar" name="TglBongkar" placeholder="YYYY-MM-DD" value="<?= old('TglBongkar', ($permohonan_edit['TglBongkar'] ?? '') != '0000-00-00' ? ($permohonan_edit['TglBongkar'] ?? '') : '') ?>" required></div>
                    <div class="form-group col-md-4"><label for="lokasi">Lokasi Bongkar <span class="text-danger">*</span></label><input type="text" class="form-control" id="lokasi" name="lokasi" value="<?= old('lokasi', $permohonan_edit['lokasi'] ?? '') ?>" required></div>
                </div>

                <div class="form-group mt-3">
                    <label>File BC 1.1 / Manifest Saat Ini:</label>
                    <?php if (!empty($permohonan_edit['file_bc_manifest'])): ?>
                        <p>
                            <!-- Pastikan ada route untuk download aman -->
                            <a href="<?= site_url('petugas_administrasi/download/bc_manifest/' . esc($permohonan_edit['file_bc_manifest'])) ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                <i class="fas fa-file-pdf"></i> <?= esc($permohonan_edit['file_bc_manifest']) ?>
                            </a>
                        </p>
                    <?php else: ?>
                        <p class="text-muted"><em>Tidak ada file BC 1.1 / Manifest yang terlampir sebelumnya.</em></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="file_bc_manifest_pa_edit">Upload File BC 1.1 / Manifest Baru <span class="text-info small">(Hanya PDF, max 2MB)</span></label>
                    <span class="text-info small d-block mb-1">Kosongkan jika tidak ingin mengganti. Jika belum ada file sebelumnya, ini wajib diisi.</span>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="file_bc_manifest_pa_edit" name="file_bc_manifest_pa_edit" accept=".pdf" <?= (empty($permohonan_edit['file_bc_manifest'])) ? 'required' : '' ?>>
                        <label class="custom-file-label" for="file_bc_manifest_pa_edit">Pilih file baru (PDF)...</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-user btn-block mt-4" id="submitEditPermohonanBtn">
                    <i class="fas fa-save fa-fw"></i> Simpan Perubahan Permohonan
                </button>
                <a href="<?= site_url('petugas_administrasi/permohonanMasuk') ?>" class="btn btn-secondary btn-user btn-block mt-2">
                    <i class="fas fa-times fa-fw"></i> Batal
                </a>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function () {
    var datepickerConfig = { uiLibrary: 'bootstrap4', format: 'yyyy-mm-dd', showOnFocus: true, showRightIcon: true, autoClose: true };
    $('#TglSurat').datepicker(datepickerConfig);
    $('#TglKedatangan').datepicker(datepickerConfig);
    $('#TglBongkar').datepicker(datepickerConfig);

    var jumlahBarangLamaDiPermohonan = parseFloat(<?= json_encode($permohonan_edit['JumlahBarang'] ?? 0) ?>);
    var idKuotaBarangLamaDiPermohonan = parseInt(<?= json_encode($permohonan_edit['id_kuota_barang_digunakan'] ?? 0) ?>);
    
    function updateKuotaInfoEdit() {
        var selectedOption = $('#id_kuota_barang_selected_dropdown option:selected');
        var idKuotaBarangDipilih = selectedOption.val();
        var sisaKuotaAsliDB = parseFloat(selectedOption.data('sisa_kuota_asli')) || 0;
        var skep = selectedOption.data('skep') || 'SKEP Tidak Tersedia';
        var namaBarang = selectedOption.data('nama_barang') || '';

        var sisaKuotaEfektif = sisaKuotaAsliDB;
        if (idKuotaBarangDipilih && parseInt(idKuotaBarangDipilih) === idKuotaBarangLamaDiPermohonan) {
            sisaKuotaEfektif += jumlahBarangLamaDiPermohonan;
        }

        $('#sisaKuotaInfoEdit').text('Sisa kuota efektif untuk barang (' + namaBarang + '): ' + sisaKuotaEfektif.toLocaleString() + ' Unit');
        $('#NoSkepOtomatis').val(skep);
        $('#JumlahBarang').attr('max', sisaKuotaEfektif);
        
        $('#id_kuota_barang_selected').val(idKuotaBarangDipilih);
        $('#NamaBarangHidden').val(namaBarang);
        
        validateSubmitButton();
    }

    function validateSubmitButton() {
        var jumlahDimohon = parseFloat($('#JumlahBarang').val()) || 0;
        var sisaKuotaMax = parseFloat($('#JumlahBarang').attr('max')) || 0;
        var idKuotaBarangDipilih = $('#id_kuota_barang_selected_dropdown').val();
        var submitButton = $('#submitEditPermohonanBtn');

        if (!idKuotaBarangDipilih) {
            submitButton.prop('disabled', true).attr('title', 'Pilih barang berkuota terlebih dahulu.');
            return;
        }
        if (jumlahDimohon <= 0) {
            submitButton.prop('disabled', true).attr('title', 'Jumlah barang harus lebih dari 0.');
            return;
        }
        if (jumlahDimohon > sisaKuotaMax) {
            submitButton.prop('disabled', true).attr('title', 'Jumlah barang melebihi sisa kuota efektif.');
            return;
        }
        submitButton.prop('disabled', false).attr('title', '');
    }

    $('#id_kuota_barang_selected_dropdown').on('change', function() {
        var idKuotaBarangDipilih = $(this).val();
        if (idKuotaBarangDipilih && parseInt(idKuotaBarangDipilih) === idKuotaBarangLamaDiPermohonan) {
            $('#JumlahBarang').val(jumlahBarangLamaDiPermohonan);
        } else {
            $('#JumlahBarang').val('');
        }
        updateKuotaInfoEdit();
    });

    $('#JumlahBarang').on('input', function() {
        var jumlahDimohon = parseFloat($(this).val()) || 0;
        var sisaKuotaMax = parseFloat($(this).attr('max')) || 0;
        if (jumlahDimohon > sisaKuotaMax) {
            $('#sisaKuotaInfoEdit').append(' <strong class="text-danger" id="error-msg">(Jumlah melebihi sisa efektif!)</strong>');
        } else {
            $('#error-msg').remove();
        }
        validateSubmitButton();
    });

    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });

    // Initial call
    updateKuotaInfoEdit();
});
</script>
<?= $this->endSection() ?>
