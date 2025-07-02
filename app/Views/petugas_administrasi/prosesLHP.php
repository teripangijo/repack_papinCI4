<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <h1 class="h3 mb-4 text-gray-800"> <?= esc($subtitle ?? 'Proses LHP') ?></h1>

    <?php if (session()->getFlashdata('message')) : ?>
        <?= session()->getFlashdata('message') ?>
    <?php endif; ?>

    <?php if (isset($validation) && $validation->getErrors()) : ?>
        <div class="alert alert-danger" role="alert"><?= $validation->listErrors() ?></div>
    <?php endif; ?>

    <div class="col-lg">
        <div class="card mb-4">
            <div class="card-header m-0 font-weight-bold text-primary">
                Form LHP
            </div>
            <div class="card-body">
                <form action="<?= base_url('petugas_administrasi/prosesLHP/' . ($permohonan['id'] ?? '')) ?>" method="POST">
                    <?= csrf_field() ?>
                    
                    <fieldset class="border p-3 mb-3">
                        <legend class="w-auto px-2 small">Info Permohonan</legend>
                        <div class="row">
                            <div class="col-md-6"><p><strong>Nama Perusahaan:</strong> <?= esc($user_perusahaan['NamaPers'] ?? '') ?></p></div>
                            <div class="col-md-6"><p><strong>No. Surat:</strong> <?= esc($permohonan['nomorSurat'] ?? '') ?></p></div>
                        </div>
                    </fieldset>

                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Tanggal Pemeriksaan</label>
                            <input class="form-control gj-datepicker" id="TglPeriksa" name="TglPeriksa" value="<?= old('TglPeriksa') ?>" placeholder="YYYY-MM-DD">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Waktu Mulai</label>
                            <input class="form-control gj-timepicker" id="wkmulai" name="wkmulai" value="<?= old('wkmulai') ?>" placeholder="HH:MM">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Waktu Selesai</label>
                            <input class="form-control gj-timepicker" id="wkselesai" name="wkselesai" value="<?= old('wkselesai') ?>" placeholder="HH:MM">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label>Nomor ST</label>
                            <input type="text" class="form-control" id="nomorST" name="nomorST" value="<?= old('nomorST') ?>" placeholder="Nomor Surat Tugas">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Tanggal ST</label>
                            <input class="form-control gj-datepicker" id="tgl_st" name="tgl_st" value="<?= old('tgl_st') ?>" placeholder="YYYY-MM-DD">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Jumlah Barang Diberitahukan</label>
                            <input type="text" class="form-control" value="<?= esc($permohonan['JumlahBarang'] ?? '') ?>" disabled>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Jumlah Barang Sebenarnya</label>
                            <input type="number" step="any" class="form-control" id="JumlahBenar" name="JumlahBenar" value="<?= old('JumlahBenar') ?>" placeholder="Jumlah Sebenarnya">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Kondisi</label>
                            <input type="text" class="form-control" id="Kondisi" name="Kondisi" value="<?= old('Kondisi') ?>" placeholder="Kondisi">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Pemilik Barang</label>
                        <input type="text" class="form-control" id="pemilik" name="pemilik" value="<?= old('pemilik') ?>" placeholder="Pemilik Barang">
                    </div>
                    
                    <div class="form-group">
                        <label>Keterangan / Kesimpulan</label>
                        <textarea rows="3" class="form-control" id="Kesimpulan" name="Kesimpulan"><?= old('Kesimpulan') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="hasil">Hasil Keputusan</label>
                        <select class="form-control" name="hasil" id="hasil">
                            <option value="">- Pilih -</option>
                            <option value="1" <?= set_select('hasil', '1') ?>>Sesuai</option>
                            <option value="0" <?= set_select('hasil', '0') ?>>Tidak Sesuai</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-success">Simpan LHP</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        var dateConfig = { uiLibrary: 'bootstrap4', format: 'yyyy-mm-dd' };
        $('#TglPeriksa').datepicker(dateConfig);
        $('#tgl_st').datepicker(dateConfig);

        var timeConfig = { uiLibrary: 'bootstrap4', format: 'HH:MM' };
        $('#wkmulai').timepicker(timeConfig);
        $('#wkselesai').timepicker(timeConfig);
    });
</script>
<?= $this->endSection() ?>
