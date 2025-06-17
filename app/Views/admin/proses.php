<?= $this->extend('layouts/main') ?> // Menggunakan layout 'main.php'
<?= $this->section('title') ?>
    <?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Proses Permohonan & LHP'; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
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
    <h5>Status: <?php if ($user['is_active'] == 1) {
                    echo "Active";
                } else {
                    echo "Not Active! Please Update Profile Data Below";
                }
                ?></h5>

    <div class="col-lg">

        <!-- Default Card Example -->
        <div class="card mb-4">
            <div class="card-header m-0 font-weight-bold text-primary">
                Form Permohonan
            </div>
            <div class="card-body">
                <form action="<?= base_url('admin/proses/' . $permohonan['id']); ?>" method="POST">
                    <?= csrf_field(); ?>
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
                        <div class="form-group col">
                            <label for="petugas">Nama Petugas</label>
                            <select class="form-control" name="petugas" id="petugas_id_dropdown">
                                <option value="">- Pilih -</option>
                                <?php // Loop through petugas options, assuming $petugas_list is passed from controller ?>
                                <option value="8" <?= old('petugas') == '8' ? 'selected' : ''; ?>>Harso Haryadi</option>
                                <option value="7" <?= old('petugas') == '7' ? 'selected' : ''; ?>>Septian Budi Subroto</option>
                                <option value="6" <?= old('petugas') == '6' ? 'selected' : ''; ?>>Bayu Raharjo Putra</option>
                                <option value="9" <?= old('petugas') == '9' ? 'selected' : ''; ?>>Ismail Martawinata</option>
                                <option value="10" <?= old('petugas') == '10' ? 'selected' : ''; ?>>Jihad Fadhil Mudhoffar</option>
                                <option value="11" <?= old('petugas') == '11' ? 'selected' : ''; ?>>Kristian Jimmy Hamonangan</option>
                            </select>
                            <?php if ($validation->hasError('petugas')) : ?>
                                <small class="text-danger pl-3"><?= $validation->getError('petugas'); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    </br>
                    <button type="submit" class="btn btn-success">Simpan</button>
                    <a href="#" class="btn btn-primary">Preview</a>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- /.container-fluid -->

</div>
<!-- End of Main Content -->

<script>
    // Initialize Gijgo Datepicker
    $(document).ready(function() {
        var datepickerConfig = {
            uiLibrary: 'bootstrap4',
            format: 'yyyy-mm-dd',
            showOnFocus: true,
            showRightIcon: true,
            autoClose: true
        };
        $('#TglSurat').datepicker(datepickerConfig);
        $('#TglKedatangan').datepicker(datepickerConfig);
        $('#TglBongkar').datepicker(datepickerConfig);
    });
</script>
<?= $this->endSection() ?>