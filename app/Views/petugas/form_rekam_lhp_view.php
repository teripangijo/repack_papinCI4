<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= esc($subtitle ?? 'Rekam LHP') ?></h1>
        <a href="<?= site_url('petugas/daftar_pemeriksaan') ?>" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Daftar Tugas
        </a>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <?= session()->getFlashdata('message') ?>
    <?php endif; ?>
    <?php if (isset($validation) && $validation->getErrors()): ?>
        <div class="alert alert-danger mb-3" role="alert">
            <?= $validation->listErrors() ?>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <?= isset($lhp_data) && !empty($lhp_data) ? 'Edit' : 'Perekaman' ?> LHP untuk Permohonan ID: <?= esc($permohonan['id']) ?>
                <br><small>(Perusahaan: <?= esc($permohonan['NamaPers'] ?? 'N/A') ?> - No. Surat: <?= esc($permohonan['nomorSurat'] ?? '-') ?>)</small>
            </h6>
        </div>
        <div class="card-body">
            <?php $jumlahDiajukanDariPermohonan = $permohonan['JumlahBarang'] ?? 0; ?>
            <div class="row mb-3">
                <div class="col-md-6"><p><strong>Nama Barang:</strong> <?= esc($permohonan['NamaBarang'] ?? '-') ?></p></div>
                <div class="col-md-6"><p><strong>Jumlah Diajukan:</strong> <span class="font-weight-bold"><?= esc(number_format($jumlahDiajukanDariPermohonan, 0, ',', '.')) ?></span> Unit</p></div>
            </div>
            <hr>

            <form action="<?= site_url('petugas/rekam_lhp/' . $permohonan['id']) ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="id_lhp_existing" value="<?= esc($lhp_data['id'] ?? ($lhp_data['id_lhp'] ?? '')) ?>">

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="NoLHP">Nomor LHP <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="NoLHP" name="NoLHP" value="<?= old('NoLHP', $lhp_data['NoLHP'] ?? '') ?>" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="TglLHP">Tanggal LHP <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="TglLHP" name="TglLHP" value="<?= old('TglLHP', ($lhp_data['TglLHP'] ?? '') != '0000-00-00' ? ($lhp_data['TglLHP'] ?? date('Y-m-d')) : date('Y-m-d')) ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="JumlahAjuInfo">Jumlah Diajukan (Info)</label>
                        <input type="number" class="form-control" id="JumlahAjuInfo" value="<?= esc($jumlahDiajukanDariPermohonan) ?>" readonly>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="JumlahBenar">Jumlah Disetujui (LHP) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="JumlahBenar" name="JumlahBenar" value="<?= old('JumlahBenar', $lhp_data['JumlahBenar'] ?? $jumlahDiajukanDariPermohonan) ?>" required min="0">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="JumlahSalah">Jumlah Ditolak (LHP) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="JumlahSalah" name="JumlahSalah" value="<?= old('JumlahSalah', $lhp_data['JumlahSalah'] ?? 0) ?>" required min="0">
                    </div>
                </div>

                <div class="form-group">
                    <label for="Catatan">Catatan Hasil Pemeriksaan</label>
                    <textarea class="form-control" id="Catatan" name="Catatan" rows="4"><?= old('Catatan', $lhp_data['Catatan'] ?? '') ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="FileLHP">Upload File LHP Resmi (PDF/DOC/JPG/PNG, maks 2MB) <?= (empty($lhp_data['FileLHP'])) ? '<span class="text-danger">*</span>' : '' ?></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="FileLHP" name="FileLHP" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <label class="custom-file-label" for="FileLHP"><?= esc($lhp_data['FileLHP'] ?? 'Pilih file...') ?></label>
                        </div>
                        <?php if (!empty($lhp_data['FileLHP'])): ?>
                            <small class="form-text text-muted">File saat ini: <a href="<?= site_url('petugas/download/lhp/' . esc($lhp_data['FileLHP'])) ?>" target="_blank"><?= esc($lhp_data['FileLHP']) ?></a>. Pilih file baru untuk mengganti.</small>
                        <?php else: ?>
                             <small class="form-text text-muted">Wajib diisi untuk perekaman LHP baru.</small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="file_dokumentasi_foto">Upload Foto Dokumentasi (JPG/PNG/GIF, maks 2MB)</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="file_dokumentasi_foto" name="file_dokumentasi_foto" accept="image/jpeg,image/png,image/gif">
                            <label class="custom-file-label" for="file_dokumentasi_foto"><?= esc($lhp_data['file_dokumentasi_foto'] ?? 'Pilih file...') ?></label>
                        </div>
                        <?php if (!empty($lhp_data['file_dokumentasi_foto'])): ?>
                            <small class="form-text text-muted">File saat ini: <a href="<?= site_url('petugas/download/dokumentasi_lhp/' . esc($lhp_data['file_dokumentasi_foto'])) ?>" target="_blank"><?= esc($lhp_data['file_dokumentasi_foto']) ?></a>. Pilih file baru untuk mengganti.</small>
                        <?php endif; ?>
                    </div>
                </div>
                
                <hr>
                <button type="submit" class="btn btn-primary btn-user btn-block">
                    <i class="fas fa-save fa-fw"></i> <?= isset($lhp_data) && !empty($lhp_data) ? 'Update' : 'Simpan' ?> Data LHP
                </button>
                <a href="<?= site_url('petugas/daftar_pemeriksaan') ?>" class="btn btn-secondary btn-user btn-block mt-2">
                    <i class="fas fa-times fa-fw"></i> Batal
                </a>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function(){
    function hitungJumlahSalah() {
        var diajukan = parseInt($('#JumlahAjuInfo').val()) || 0;
        var disetujui = parseInt($('#JumlahBenar').val()) || 0;
        var ditolak = diajukan - disetujui;
        $('#JumlahSalah').val(ditolak < 0 ? 0 : ditolak);
    }
    
    $('#JumlahBenar').on('input', hitungJumlahSalah);

    // Initial calculation
    hitungJumlahSalah();

    $('.custom-file-input').on('change', function(event) {
        var inputFile = event.target;
        var fileName = inputFile.files.length ? inputFile.files[0].name : $(this).next('.custom-file-label').data('original-text');
        $(this).next('.custom-file-label').html(fileName);
    });
    
    $('.custom-file-label').each(function() {
        $(this).data('original-text', $(this).html());
    });
});
</script>
<?= $this->endSection() ?>
