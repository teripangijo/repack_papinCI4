<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <h1 class="h3 mb-4 text-gray-800"><?= esc($subtitle ?? 'Edit Laporan Hasil Pemeriksaan') ?></h1>
    
    <?php if (session()->getFlashdata('message')) : ?>
        <?= session()->getFlashdata('message') ?>
    <?php endif; ?>

    <?php if (isset($validation) && $validation->getErrors()) : ?>
        <div class="alert alert-danger" role="alert">
            <?= $validation->listErrors() ?>
        </div>
    <?php endif; ?>

    <h5>Status: 
        <?php
        if (($user['role_id'] ?? 0) != 1) {
            if (($user['is_active'] ?? 0) == 1) {
                echo "Active";
            } else {
                echo "Not Active! Please Update Profile Data Below";
            }
        } else {
            echo "Admin";
        }
        ?>
    </h5>

    <div class="col-lg">
        <div class="card mb-4">
            <div class="card-header m-0 font-weight-bold text-primary">
                Form LHP
            </div>
            <div class="card-body">
                <form action="<?= base_url('petugas/editLHP/' . ($permohonan['id'] ?? '')) ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="row">
                        <div class="col">
                            <label>Nama Perusahaan</label>
                            <input type="text" class="form-control" value="<?= esc($user_perusahaan['NamaPers'] ?? '') ?>" disabled>
                        </div>
                        <div class="col">
                            <label>Alamat</label>
                            <input type="text" class="form-control" value="<?= esc($user_perusahaan['alamat'] ?? '') ?>" disabled>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col">
                            <label>Nomor Surat</label>
                            <input type="text" class="form-control" value="<?= esc($permohonan['nomorSurat'] ?? '') ?>" disabled>
                        </div>
                        <div class="col">
                            <label>Tanggal Surat</label>
                            <input class="form-control" value="<?= esc($permohonan['TglSurat'] ?? '') ?>" disabled>
                        </div>
                        <div class="col">
                            <label>Perihal</label>
                            <input type="text" class="form-control" value="<?= esc($permohonan['Perihal'] ?? '') ?>" disabled />
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col">
                            <label>Nama / Jenis Barang</label>
                            <input type="text" class="form-control" value="<?= esc($permohonan['NamaBarang'] ?? '') ?>" disabled>
                        </div>
                        <div class="col">
                            <label>Jumlah Barang</label>
                            <input type="text" class="form-control" value="<?= esc($permohonan['JumlahBarang'] ?? '') ?>" disabled>
                        </div>
                        <div class="col">
                            <label>Negara Asal Barang</label>
                            <input type="text" class="form-control" value="<?= esc($permohonan['NegaraAsal'] ?? '') ?>" disabled>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col">
                            <label>Nama Kapal</label>
                            <input type="text" class="form-control" value="<?= esc($permohonan['NamaKapal'] ?? '') ?>" disabled>
                        </div>
                        <div class="col">
                            <label>No Voyage</label>
                            <input type="text" class="form-control" value="<?= esc($permohonan['noVoyage'] ?? '') ?>" disabled>
                        </div>
                        <div class="col">
                            <label>No SKEP</label>
                            <input type="text" class="form-control" value="<?= esc($user_perusahaan['NoSkep'] ?? '') ?>" disabled>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col">
                            <label>Tanggal Kegiatan</label>
                            <input class="form-control" value="<?= esc($permohonan['TglKedatangan'] ?? '') ?>" disabled>
                        </div>
                        <div class="col">
                            <label>Tanggal Bongkar</label>
                            <input class="form-control" value="<?= esc($permohonan['TglBongkar'] ?? '') ?>" disabled>
                        </div>
                        <div class="col">
                            <label>Lokasi Bongkar</label>
                            <input type="text" class="form-control" value="<?= esc($permohonan['lokasi'] ?? '') ?>" disabled>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                         <div class="col">
                            <label>Tanggal Pemeriksaan</label>
                            <input class="form-control gj-datepicker" id="TglPeriksa" name="TglPeriksa" value="<?= old('TglPeriksa', $lhp['TglPeriksa'] ?? '') ?>" placeholder="YYYY-MM-DD">
                        </div>
                        <div class="col">
                            <label>Waktu Mulai</label>
                            <input class="form-control gj-timepicker" id="wkmulai" name="wkmulai" value="<?= old('wkmulai', $lhp['wkmulai'] ?? '') ?>">
                        </div>
                        <div class="col">
                            <label>Waktu Selesai</label>
                            <input class="form-control gj-timepicker" id="wkselesai" name="wkselesai" value="<?= old('wkselesai', $lhp['wkselesai'] ?? '') ?>">
                        </div>
                        <div class="col">
                            <label>Lokasi</label>
                            <input type="text" class="form-control" value="<?= esc($permohonan['lokasi'] ?? '') ?>" disabled>
                        </div>
                        <div class="col">
                            <label>Nama Sarana Pengangkut</label>
                            <input type="text" class="form-control" value="<?= esc($permohonan['NamaKapal'] ?? '') ?>" disabled>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col">
                            <label>Nama Perusahaan</label>
                            <input type="text" class="form-control" value="<?= esc($permohonan['NamaPers'] ?? '') ?>" disabled>
                        </div>
                        <div class="col">
                            <label>Nomor Surat</label>
                            <input type="text" class="form-control" value="<?= esc($permohonan['nomorSurat'] ?? '') ?>" disabled>
                        </div>
                        <div class="col">
                            <label>Nomor ST</label>
                            <input type="text" class="form-control" id="nomorST" name="nomorST" value="<?= old('nomorST', $lhp['nomorST'] ?? '') ?>" placeholder="Nomor Surat Tugas">
                        </div>
                        <div class="col">
                            <label>Tanggal ST</label>
                            <input class="form-control gj-datepicker" id="tgl_st" name="tgl_st" value="<?= old('tgl_st', $lhp['tgl_st'] ?? '') ?>" placeholder="YYYY-MM-DD">
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col">
                            <label>Jenis Kemasan</label>
                            <input type="text" class="form-control" value="<?= esc($permohonan['NamaBarang'] ?? '') ?>" disabled>
                        </div>
                        <div class="col">
                            <label>Jumlah Barang Diberitahukan</label>
                            <input type="text" class="form-control" value="<?= esc($permohonan['JumlahBarang'] ?? '') ?>" disabled>
                        </div>
                        <div class="col">
                            <label>Jumlah Barang Sebenarnya</label>
                            <input type="number" class="form-control" id="JumlahBenar" name="JumlahBenar" value="<?= old('JumlahBenar', $lhp['JumlahBenar'] ?? '') ?>" placeholder="Jumlah Sebenarnya">
                        </div>
                        <div class="col">
                            <label>Kondisi</label>
                            <input type="text" class="form-control" id="Kondisi" name="Kondisi" value="<?= old('Kondisi', $lhp['Kondisi'] ?? '') ?>" placeholder="Kondisi">
                        </div>
                        <div class="col">
                            <label>Pemilik Barang</label>
                            <input type="text" class="form-control" id="pemilik" name="pemilik" value="<?= old('pemilik', $lhp['pemilik'] ?? '') ?>" placeholder="Pemilik Barang">
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col">
                            <label>Keterangan / Kesimpulan</label>
                            <textarea rows="3" class="form-control" id="Kesimpulan" name="Kesimpulan"><?= old('Kesimpulan', $lhp['Kesimpulan'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col">
                            <label for="hasil">Hasil Keputusan</label>
                            <select class="form-control" name="hasil" id="hasil">
                                <option value="">- Pilih -</option>
                                <option value="1" <?= old('hasil', $lhp['hasil'] ?? '') == '1' ? "selected" : "" ?>>Sesuai</option>
                                <option value="0" <?= old('hasil', $lhp['hasil'] ?? '') == '0' ? "selected" : "" ?>>Tidak Sesuai</option>
                            </select>
                        </div>
                    </div>
                    <br>
                    <button type="submit" class="btn btn-success">Simpan</button>
                    <a href="<?= base_url("petugas_administrasi/permohonanMasuk") ?>" class="btn btn-secondary">Kembali</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        var config = { uiLibrary: 'bootstrap4', format: 'yyyy-mm-dd' };
        $('#TglPeriksa').datepicker(config);
        $('#tgl_st').datepicker(config);
        
        var timeConfig = { uiLibrary: 'bootstrap4', format: 'HH:MM' };
        $('#wkmulai').timepicker(timeConfig);
        $('#wkselesai').timepicker(timeConfig);
    });
</script>
<?= $this->endSection() ?>
