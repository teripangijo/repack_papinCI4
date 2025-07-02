<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <h1 class="h3 mb-4 text-gray-800"> <?= esc($subtitle ?? 'Proses Permohonan') ?></h1>
    
    <?php if (session()->getFlashdata('message')) : ?>
        <?= session()->getFlashdata('message') ?>
    <?php endif; ?>

    <?php if (isset($validation) && $validation->getErrors()) : ?>
        <div class="alert alert-danger" role="alert"><?= $validation->listErrors() ?></div>
    <?php endif; ?>

    <h5>Status: 
        <?php 
        if (($user['is_active'] ?? 0) == 1) {
            echo "Active";
        } else {
            echo "Not Active! Please Update Profile Data Below";
        }
        ?>
    </h5>

    <div class="col-lg">
        <div class="card mb-4">
            <div class="card-header m-0 font-weight-bold text-primary">
                Form Permohonan
            </div>
            <div class="card-body">
                <form action="<?= base_url('petugas_administrasi/proses/' . ($permohonan['id'] ?? '')) ?>" method="POST">
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
                        <div class="form-group col">
                            <label for="petugas">Nama Petugas</label>
                            <select class="form-control" name="petugas" id="petugas">
                                <option value="">- Pilih -</option>
                                <?php // Note: This should be populated from a database in a real application ?>
                                <option value="8" <?= set_select('petugas', '8') ?>>Harso Haryadi</option>
                                <option value="7" <?= set_select('petugas', '7') ?>>Septian Budi Subroto</option>
                                <option value="6" <?= set_select('petugas', '6') ?>>Bayu Raharjo Putra</option>
                                <option value="9" <?= set_select('petugas', '9') ?>>Ismail Martawinata</option>
                                <option value="10" <?= set_select('petugas', '10') ?>>Jihad Fadhil Mudhoffar</option>
                                <option value="11" <?= set_select('petugas', '11') ?>>Kristian Jimmy Hamonangan</option>
                            </select>
                        </div>
                    </div>
                    <br>
                    <button type="submit" class="btn btn-success">Simpan</button>
                    <a href="#" class="btn btn-primary">Preview</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
