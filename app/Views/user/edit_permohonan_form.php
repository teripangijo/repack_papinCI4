<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
    <?= esc($subtitle ?? 'Edit Permohonan') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
// Get validation service
$validation = \Config\Services::validation();

// Prepare data for the form
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
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= esc($subtitle ?? 'Edit Permohonan Impor Kembali') ?></h1>
        <a href="<?= site_url('user/daftarPermohonan') ?>" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Daftar Permohonan
        </a>
    </div>

    <?= $validation->listErrors('list') ?>
    <!-- Flash Messages (handled by layout) -->

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

            <form action="<?= site_url('user/editpermohonan/' . $permohonan_edit['id']) ?>" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                <?= csrf_field() ?>
            
                <input type="hidden" name="id_kuota_barang_selected" id="id_kuota_barang_selected" value="<?= old('id_kuota_barang_selected', $id_kuota_barang_saat_ini) ?>">
                <input type="hidden" name="NamaBarang" id="NamaBarangHidden" value="<?= old('NamaBarang', $nama_barang_saat_ini) ?>">

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="nomorSurat">Nomor Surat Pengajuan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= $validation->hasError('nomorSurat') ? 'is-invalid' : '' ?>" id="nomorSurat" name="nomorSurat" value="<?= old('nomorSurat', $permohonan_edit['nomorSurat'] ?? '') ?>" required>
                        <div class="invalid-feedback"><?= $validation->getError('nomorSurat') ?></div>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="TglSurat">Tanggal Surat <span class="text-danger">*</span></label>
                        <input type="text" class="form-control gj-datepicker <?= $validation->hasError('TglSurat') ? 'is-invalid' : '' ?>" id="TglSurat" name="TglSurat" placeholder="YYYY-MM-DD" value="<?= old('TglSurat', ($permohonan_edit['TglSurat'] ?? '') != '0000-00-00' ? $permohonan_edit['TglSurat'] : '') ?>" required>
                        <div class="invalid-feedback"><?= $validation->getError('TglSurat') ?></div>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="Perihal">Perihal Surat <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= $validation->hasError('Perihal') ? 'is-invalid' : '' ?>" id="Perihal" name="Perihal" value="<?= old('Perihal', $permohonan_edit['Perihal'] ?? '') ?>" required>
                        <div class="invalid-feedback"><?= $validation->getError('Perihal') ?></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-5">
                        <label for="id_kuota_barang_selected_dropdown">Nama / Jenis Barang (sesuai kuota) <span class="text-danger">*</span></label>
                        <select class="form-control <?= ($validation->hasError('id_kuota_barang_selected') || $validation->hasError('NamaBarang')) ? 'is-invalid' : '' ?>" id="id_kuota_barang_selected_dropdown" required>
                            <option value="">-- Pilih Barang & Kuota SKEP --</option>
                            <?php if (!empty($list_barang_berkuota)): ?>
                                <?php foreach($list_barang_berkuota as $barang): ?>
                                    <?php $isSelected = ($id_kuota_barang_saat_ini == $barang['id_kuota_barang']); ?>
                                    <option value="<?= esc($barang['id_kuota_barang'], 'attr') ?>"
                                            data-nama_barang="<?= esc($barang['nama_barang'], 'attr') ?>"
                                            data-sisa_kuota_asli="<?= esc($barang['remaining_quota_barang'] ?? 0, 'attr') ?>"
                                            data-skep="<?= esc($barang['nomor_skep_asal'] ?? '', 'attr') ?>"
                                            <?= $isSelected ? 'selected' : '' ?>>
                                        <?= esc($barang['nama_barang']) ?> (Sisa Asli: <?= number_format($barang['remaining_quota_barang'] ?? 0, 0, ',', '.') ?> Unit - SKEP: <?= esc($barang['nomor_skep_asal'] ?? 'N/A') ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="invalid-feedback"><?= $validation->getError('id_kuota_barang_selected') ?: $validation->getError('NamaBarang') ?></div>
                        <small id="sisaKuotaInfoEdit" class="form-text text-info">Sisa kuota efektif: <?= esc($sisa_kuota_efektif_awal_display) ?> Unit</small>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="JumlahBarang">Jumlah Barang Diajukan <span class="text-danger">*</span></label>
                        <input type="number" class="form-control <?= $validation->hasError('JumlahBarang') ? 'is-invalid' : '' ?>" id="JumlahBarang" name="JumlahBarang" value="<?= old('JumlahBarang', $permohonan_edit['JumlahBarang'] ?? '') ?>" required min="1" max="9999999999">
                        <div class="invalid-feedback"><?= $validation->getError('JumlahBarang') ?></div>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="NoSkepOtomatis">No. SKEP (Dasar Permohonan)</label>
                        <input type="text" class="form-control" id="NoSkepOtomatis" value="<?= old('NoSkepOtomatis', $no_skep_saat_ini) ?>" readonly title="No. SKEP otomatis terisi berdasarkan barang yang dipilih.">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="NegaraAsal">Negara Asal Barang <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= $validation->hasError('NegaraAsal') ? 'is-invalid' : '' ?>" id="NegaraAsal" name="NegaraAsal" value="<?= old('NegaraAsal', $permohonan_edit['NegaraAsal'] ?? '') ?>" required>
                        <div class="invalid-feedback"><?= $validation->getError('NegaraAsal') ?></div>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="NamaKapal">Nama Kapal / Sarana Pengangkut <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= $validation->hasError('NamaKapal') ? 'is-invalid' : '' ?>" id="NamaKapal" name="NamaKapal" value="<?= old('NamaKapal', $permohonan_edit['NamaKapal'] ?? '') ?>" required>
                        <div class="invalid-feedback"><?= $validation->getError('NamaKapal') ?></div>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="noVoyage">No. Voyage / Flight <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= $validation->hasError('noVoyage') ? 'is-invalid' : '' ?>" id="noVoyage" name="noVoyage" value="<?= old('noVoyage', $permohonan_edit['noVoyage'] ?? '') ?>" required>
                        <div class="invalid-feedback"><?= $validation->getError('noVoyage') ?></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="TglKedatangan">Tanggal Perkiraan Kedatangan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control gj-datepicker <?= $validation->hasError('TglKedatangan') ? 'is-invalid' : '' ?>" id="TglKedatangan" name="TglKedatangan" placeholder="YYYY-MM-DD" value="<?= old('TglKedatangan', ($permohonan_edit['TglKedatangan'] ?? '') != '0000-00-00' ? $permohonan_edit['TglKedatangan'] : '') ?>" required>
                        <div class="invalid-feedback"><?= $validation->getError('TglKedatangan') ?></div>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="TglBongkar">Tanggal Perkiraan Bongkar <span class="text-danger">*</span></label>
                        <input type="text" class="form-control gj-datepicker <?= $validation->hasError('TglBongkar') ? 'is-invalid' : '' ?>" id="TglBongkar" name="TglBongkar" placeholder="YYYY-MM-DD" value="<?= old('TglBongkar', ($permohonan_edit['TglBongkar'] ?? '') != '0000-00-00' ? $permohonan_edit['TglBongkar'] : '') ?>" required>
                        <div class="invalid-feedback"><?= $validation->getError('TglBongkar') ?></div>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="lokasi">Lokasi Bongkar <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= $validation->hasError('lokasi') ? 'is-invalid' : '' ?>" id="lokasi" name="lokasi" value="<?= old('lokasi', $permohonan_edit['lokasi'] ?? '') ?>" required>
                        <div class="invalid-feedback"><?= $validation->getError('lokasi') ?></div>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <label>File BC 1.1 / Manifest Saat Ini:</label>
                    <?php if (!empty($permohonan_edit['file_bc_manifest'])): ?>
                        <p>
                            <a href="<?= base_url('uploads/bc_manifest/' . esc($permohonan_edit['file_bc_manifest'])) ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                <i class="fas fa-file-pdf"></i> <?= esc($permohonan_edit['file_bc_manifest']) ?>
                            </a>
                        </p>
                    <?php else: ?>
                        <p class="text-muted"><em>Tidak ada file BC 1.1 / Manifest yang terlampir sebelumnya.</em></p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="file_bc_manifest_edit">Upload File BC 1.1 / Manifest Baru <span class="text-info small">(Kosongkan jika tidak ingin mengganti)</span></label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input <?= $validation->hasError('file_bc_manifest_edit') ? 'is-invalid' : '' ?>" id="file_bc_manifest_edit" name="file_bc_manifest_edit" accept=".pdf">
                        <label class="custom-file-label" for="file_bc_manifest_edit">Pilih file baru (PDF)...</label>
                    </div>
                    <div class="invalid-feedback d-block mt-1"><?= $validation->getError('file_bc_manifest_edit') ?></div>
                </div>

                <button type="submit" class="btn btn-primary btn-user btn-block mt-4" id="submitEditPermohonanBtn">
                    <i class="fas fa-save fa-fw"></i> Simpan Perubahan Permohonan
                </button>
                <a href="<?= site_url('user/daftarPermohonan') ?>" class="btn btn-secondary btn-user btn-block mt-2">
                    <i class="fas fa-times fa-fw"></i> Batal
                </a>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Inisialisasi Gijgo Datepicker
    if (typeof $ !== 'undefined' && typeof $.fn.datepicker !== 'undefined') {
        const datepickerConfig = { uiLibrary: 'bootstrap4', format: 'yyyy-mm-dd', showOnFocus: true, showRightIcon: true, autoClose: true };
        $('#TglSurat').datepicker(datepickerConfig);
        $('#TglKedatangan').datepicker(datepickerConfig);
        $('#TglBongkar').datepicker(datepickerConfig);
    }

    const jumlahBarangLamaDiPermohonan = parseFloat(<?= json_encode($permohonan_edit['JumlahBarang'] ?? 0) ?>);
    const idKuotaBarangLamaDiPermohonan = parseInt(<?= json_encode($permohonan_edit['id_kuota_barang_digunakan'] ?? 0) ?>);
    const initialSelectedIdKuotaBarang = $('#id_kuota_barang_selected_dropdown').val();

    function updateKuotaInfoEdit() {
        const selectedOption = $('#id_kuota_barang_selected_dropdown option:selected');
        const idKuotaBarangDipilih = selectedOption.val();
        const sisaKuotaAsliDB = parseFloat(selectedOption.data('sisa_kuota_asli')) || 0;
        const skep = selectedOption.data('skep') || 'SKEP Tidak Tersedia';
        const namaBarang = selectedOption.data('nama_barang') || '';

        let sisaKuotaEfektif = sisaKuotaAsliDB;
        if (idKuotaBarangDipilih && parseInt(idKuotaBarangDipilih) === idKuotaBarangLamaDiPermohonan) {
            sisaKuotaEfektif += jumlahBarangLamaDiPermohonan;
        }

        $('#sisaKuotaInfoEdit').text('Sisa kuota efektif untuk barang (' + namaBarang + '): ' + sisaKuotaEfektif.toLocaleString() + ' Unit');
        $('#NoSkepOtomatis').val(skep);
        $('#JumlahBarang').attr('max', sisaKuotaEfektif);
        
        $('#id_kuota_barang_selected').val(idKuotaBarangDipilih);
        $('#NamaBarangHidden').val(namaBarang);

        if(sisaKuotaEfektif <= 0 || idKuotaBarangDipilih === "") {
            $('#submitEditPermohonanBtn').prop('disabled', true).attr('title', 'Tidak ada kuota tersedia atau barang belum dipilih.');
            if(idKuotaBarangDipilih !== "") $('#JumlahBarang').val(0).prop('readonly', true);
            else $('#JumlahBarang').prop('readonly', false);
        } else {
            $('#submitEditPermohonanBtn').prop('disabled', false).attr('title', '');
            $('#JumlahBarang').prop('readonly', false);
        }
        $('#JumlahBarang').trigger('input');
    }

    if (initialSelectedIdKuotaBarang) {
        updateKuotaInfoEdit();
    }

    $('#id_kuota_barang_selected_dropdown').on('change', function() {
        const idKuotaBarangDipilih = $(this).val();
        if (idKuotaBarangDipilih && parseInt(idKuotaBarangDipilih) === idKuotaBarangLamaDiPermohonan) {
            $('#JumlahBarang').val(jumlahBarangLamaDiPermohonan);
        } else {
            $('#JumlahBarang').val('');
        }
        updateKuotaInfoEdit();
    });

    $('#JumlahBarang').on('input', function() {
        let jumlahDimohon = parseFloat($(this).val()) || 0;
        const sisaKuotaMax = parseFloat($(this).attr('max')) || 0;
        const idKuotaBarangDipilih = $('#id_kuota_barang_selected_dropdown').val();
        const submitButton = $('#submitEditPermohonanBtn');

        $('#sisaKuotaInfoEdit .text-danger').remove();

        if (idKuotaBarangDipilih === "") {
            submitButton.prop('disabled', true).attr('title', 'Pilih barang berkuota terlebih dahulu.');
            return;
        }

        if (jumlahDimohon > sisaKuotaMax) {
            $(this).val(sisaKuotaMax);
            $('#sisaKuotaInfoEdit').append(' <strong class="text-danger">(Jumlah melebihi sisa efektif!)</strong>');
            jumlahDimohon = sisaKuotaMax;
        }

        if (jumlahDimohon <= 0) {
            submitButton.prop('disabled', true).attr('title', 'Jumlah barang harus lebih dari 0.');
        } else if (sisaKuotaMax > 0 && jumlahDimohon <= sisaKuotaMax) {
            submitButton.prop('disabled', false).attr('title', '');
        } else {
            submitButton.prop('disabled', true).attr('title', 'Tidak ada kuota tersedia atau jumlah tidak valid.');
        }
    });

    $('#file_bc_manifest_edit').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });
});
</script>
<?= $this->endSection() ?>
