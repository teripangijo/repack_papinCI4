<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800"> <?= $subtitle; ?></h1>
    <?= session()->getFlashdata('message'); ?>
    <?php
    $validation = \Config\Services::validation();
    if ($validation->getErrors()) : ?>
        <div class="alert alert-danger" role="alert"><?= implode('', $validation->getErrors()); ?></div>
        <?= session()->getFlashdata('message'); ?>
    <?php endif; ?>
    <h5>Status: <?php
                if ($user['role_id'] != 1) {
                    if ($user['is_active'] == 1) {
                        echo "Active";
                    } else {
                        echo "Not Active! Please Update Profile Data Below";
                    }
                } else {
                    echo "Admin";
                }
                ?></h5>

    <div class="col-lg">

        <!-- Default Card Example -->
        <div class="card mb-4">
            <div class="card-header m-0 font-weight-bold text-primary">
                Form LHP
            </div>
            <div class="card-body">

                <form action="<?= base_url('admin/prosesLHP/' . $permohonan['id']); ?>" method="POST">
                    <?= csrf_field(); // CSRF token for CI4 ?>
                    <div class="row">
                        <div class="col">
                            <label>Nama Perusahaan</label>
                            <input type="text" class="form-control" id="NamaPers" name="NamaPers" value="<?= $user_perusahaan['NamaPers'] ?>" disabled>
                        </div>
                        <div class="col">
                            <label>Alamat</label>
                            <input type="text" class="form-control" id="alamat" name="alamat" value="<?= $user_perusahaan['alamat'] ?>" disabled>
                        </div>
                    </div>
                    </br>
                    <div class="row">
                        <div class="col">
                            <label>Nomor Surat</label>
                            <input type="text" class="form-control" id="nomorSurat" name="nomorSurat" placeholder="Nomor Surat" value="<?= $permohonan['nomorSurat']; ?>" disabled>
                        </div>
                        <div class="col">
                            <label>Tanggal Surat</label>
                            <input id="TglSurat" name="TglSurat" value="<?= $permohonan['TglSurat']; ?>" placeholder="Tanggal Surat" disabled>
                        </div>
                        <div class="col">
                            <label>Perihal</label>
                            <input type="text" class="form-control" id="Perihal" name="Perihal" value="<?= $permohonan['Perihal']; ?>" placeholder="Perihal" disabled />
                        </div>
                    </div>
                    </br>
                    <div class="row">
                        <div class="col">
                            <label>Nama / Jenis Barang</label>
                            <input type="text" class="form-control" id="NamaBarang" name="NamaBarang" value="<?= $permohonan['NamaBarang']; ?>" placeholder="Nama / Jenis Barang" disabled>
                        </div>
                        <div class="col">
                            <label>Jumlah Barang</label>
                            <input type="text" class="form-control" id="JumlahBarang" name="JumlahBarang" value="<?= $permohonan['JumlahBarang']; ?>" placeholder="Jumlah Barang" disabled>
                        </div>
                        <div class="col">
                            <label>Negara Asal Barang</label>
                            <input type="text" class="form-control" id="NegaraAsal" name="NegaraAsal" value="<?= $permohonan['NegaraAsal']; ?>" placeholder="Negara Asal barang" disabled>
                        </div>
                    </div>
                    </br>
                    <div class="row">
                        <div class="col">
                            <label>Nama Kapal</label>
                            <input type="text" class="form-control" id="NamaKapal" name="NamaKapal" value="<?= $permohonan['NamaKapal']; ?>" placeholder="Nama Kapal" disabled>
                        </div>
                        <div class="col">
                            <label>No Voyage</label>
                            <input type="text" class="form-control" id="noVoyage" name="noVoyage" value="<?= $permohonan['noVoyage']; ?>" placeholder="No Voyage" disabled>
                        </div>
                        <div class="col">
                            <label>No SKEP</label>
                            <input type="text" class="form-control" id="NoSkep" name="NoSkep" value="<?= $user_perusahaan['NoSkep'] ?>" disabled>
                        </div>
                    </div>
                    </br>
                    <div class="row">
                        <div class="col">
                            <label>Tanggal Kegiatan</label>
                            <input id="TglKedatangan" name="TglKedatangan" value="<?= $permohonan['TglKedatangan']; ?>" placeholder="Tanggal Kegiatan" disabled>
                        </div>
                        <div class="col">
                            <label>Tanggal Bongkar</label>
                            <input id="TglBongkar" name="TglBongkar" value="<?= $permohonan['TglBongkar']; ?>" placeholder="Tanggal Bongkar" disabled>
                        </div>
                        <div class="col">
                            <label>Lokasi Bongkar</label>
                            <input type="text" class="form-control" id="lokasi" name="lokasi" value="<?= $permohonan['lokasi']; ?>" placeholder="Lokasi Bongkar" disabled>
                        </div>
                    </div>
                    </br>
                    <div class="row">
                        <div class="col">
                            <label>Tanggal Pemeriksaan</label>
                            <input id="TglPeriksa" name="TglPeriksa" value="<?= old('TglPeriksa'); ?>" placeholder="Tanggal Pemeriksaan">
                        </div>
                        <div class="col">
                            <label>Waktu Mulai</label>
                            <input id="wkmulai" name="wkmulai" value="<?= old('wkmulai'); ?>" placeholder="Waktu Mulai Pemeriksaan">
                        </div>
                        <div class="col">
                            <label>Waktu Selesai</label>
                            <input id="wkselesai" name="wkselesai" value="<?= old('wkselesai'); ?>" placeholder="Waktu Selesai Pemeriksaan">
                        </div>
                        <div class="col">
                            <label>Lokasi</label>
                            <input type="text" class="form-control" id="lokasi" name="lokasi" value="<?= $permohonan['lokasi']; ?>" placeholder="Lokasi Bongkar" disabled>
                        </div>
                        <div class="col">
                            <label>Nama Sarana Pengangkut</label>
                            <input type="text" class="form-control" id="NamaKapal" name="NamaKapal" value="<?= $permohonan['NamaKapal']; ?>" placeholder="Nama Kapal" disabled>
                        </div>
                    </div>
                    </br>
                    <div class="row">
                        <div class="col">
                            <label>Nama Perusahaan</label>
                            <input type="text" class="form-control" id="NamaPers" name="NamaPers" placeholder="Nomor Surat" value="<?= $permohonan['NamaPers']; ?>" disabled>
                        </div>
                        <div class="col">
                            <label>Nomor Surat</label>
                            <input type="text" class="form-control" id="nomorSurat" name="nomorSurat" value="<?= $permohonan['nomorSurat']; ?>" placeholder="Tanggal Surat" disabled>
                        </div>
                        <div class="col">
                            <label>Nomor ST</label>
                            <input type="text" class="form-control" id="nomorST" name="nomorST" value="<?= old('nomorST'); ?>" placeholder="Nomor Surat Tugas">
                        </div>
                        <div class="col">
                            <label>Tanggal ST</label>
                            <input id="tgl_st" name="tgl_st" value="<?= old('tgl_st'); ?>" placeholder="Tanggal Surat Tugas">
                        </div>
                        <div class="col">
                            <label>Link ST</label>
                            <input type="text" class="form-control" id="linkST" name="linkST" value="<?= old('linkST'); ?>" placeholder="Link Surat">
                            <button type="button" onclick=" window.open($('#linkST').val())" class="btn btn-primary ">Cek Link</button>
                        </div>
                    </div>
                    </br>
                    <div class="row">
                        <div class="col">
                            <label>Jenis Kemasan</label>
                            <input type="text" class="form-control" id="NamaBarang" name="NamaBarang" value="<?= $permohonan['NamaBarang']; ?>" placeholder="Nama / Jenis Barang" disabled>
                        </div>
                        <div class="col">
                            <label>Jumlah Barang Diberitahukan</label>
                            <input type="text" class="form-control" id="JumlahBarang" name="JumlahBarang" value="<?= $permohonan['JumlahBarang']; ?>" placeholder="Jumlah Barang" disabled>
                        </div>
                        <div class="col">
                            <label>Jumlah Barang Sebenarnya</label>
                            <input type="text" class="form-control" id="JumlahBenar" name="JumlahBenar" value="<?= old('JumlahBenar'); ?>" placeholder="Jumlah Sebenarnya">
                        </div>
                        <div class="col">
                            <label>Kondisi</label>
                            <input type="text" class="form-control" id="Kondisi" name="Kondisi" value="<?= old('Kondisi'); ?>" placeholder="Kondisi">
                        </div>
                        <div class="col">
                            <label>Pemilik Barang</label>
                            <input type="text" class="form-control" id="pemilik" name="pemilik" value="<?= old('pemilik'); ?>" placeholder="Pemilik Barang">
                        </div>
                    </div>
                    </br>
                    <div class="row">
                        <div class="col">
                            <label>Keterangan / Kesimpulan</label>
                            <textarea rows="3" type="text" class="form-control" id="Kesimpulan" name="Kesimpulan"><?= old('Kesimpulan'); ?></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col">
                            <label for="hasil">Hasil Keputusan</label>
                            <select class="form-control" name="hasil" id="hasil">
                                <option value="">- Pilih -</option>
                                <option value="1" <?= old('hasil') == '1' ? 'selected' : ''; ?>>Sesuai</option>
                                <option value="0" <?= old('hasil') == '0' ? 'selected' : ''; ?>>Tidak Sesuai</option>
                            </select>
                            <?php if ($validation->hasError('hasil')) : ?>
                                <div class="invalid-feedback"><?= $validation->getError('hasil'); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    </br>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </form>
            </div>
        </div>

        <!-- Basic Card Example -->


    </div>

</div>
<!-- /.container-fluid -->

</div>
<!-- End of Main Content -->

<script>
    // Initialize Gijgo Datepicker and Timepicker
    $(document).ready(function() {
        var datepickerConfig = {
            uiLibrary: 'bootstrap4',
            format: 'yyyy-mm-dd',
            showOnFocus: true,
            showRightIcon: true,
            autoClose: true
        };
        var timepickerConfig = {
            uiLibrary: 'bootstrap4',
            format: 'HH:MM', // 24-hour format
            showOnFocus: true,
            showRightIcon: true,
            autoClose: true
        };

        $('#TglSurat').datepicker(datepickerConfig);
        $('#TglKedatangan').datepicker(datepickerConfig);
        $('#TglBongkar').datepicker(datepickerConfig);
        $('#TglPeriksa').datepicker(datepickerConfig);
        $('#tgl_st').datepicker(datepickerConfig);
        $('#wkmulai').timepicker(timepickerConfig);
        $('#wkselesai').timepicker(timepickerConfig);
    });
</script>
