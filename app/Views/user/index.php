<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
    <?= esc($subtitle ?? 'Profil Saya') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
// Set default display values
$display_nama_pers = '<span class="text-muted"><em>Data belum dilengkapi</em></span>';
$display_npwp = '<span class="text-muted"><em>Data belum dilengkapi</em></span>';
$display_alamat = '<span class="text-muted"><em>Data belum dilengkapi</em></span>';
$display_telp = '<span class="text-muted"><em>Data belum dilengkapi</em></span>';
$display_pic = '<span class="text-muted"><em>Data belum dilengkapi</em></span>';
$display_jabatan_pic = '<span class="text-muted"><em>Data belum dilengkapi</em></span>';
$display_no_skep = '<span class="text-muted"><em>Data belum dilengkapi</em></span>';
$display_quota = '<span class="text-muted"><em>Data belum dilengkapi</em></span>';
$perusahaan_data_exists = false;

if (!empty($user_perusahaan)) {
    $perusahaan_data_exists = true;
    $display_nama_pers = esc($user_perusahaan['NamaPers'] ?? 'Data tidak ditemukan');
    $display_npwp = esc($user_perusahaan['npwp'] ?? 'Data tidak ditemukan');
    $display_alamat = esc($user_perusahaan['alamat'] ?? 'Data tidak ditemukan');
    $display_telp = esc($user_perusahaan['telp'] ?? 'Data tidak ditemukan');
    $display_pic = esc($user_perusahaan['pic'] ?? 'Data tidak ditemukan');
    $display_jabatan_pic = esc($user_perusahaan['jabatanPic'] ?? 'Data tidak ditemukan');
    $display_no_skep = esc($user_perusahaan['NoSkep'] ?? 'Data tidak ditemukan');
    $display_quota = esc($user_perusahaan['quota'] ?? 'Data tidak ditemukan');
}

$profile_image_name = $user['image'] ?? 'default.jpg';
$profile_image_path = base_url('uploads/kop/' . esc($profile_image_name, 'url'));
?>

<div class="container-fluid">

    <h1 class="h3 mb-4 text-gray-800"><?= esc($subtitle ?? 'Profil Saya') ?></h1>

    <!-- Flashdata messages are handled by the main layout -->

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Informasi Pengguna</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 text-center">
                    <img src="<?= $profile_image_path ?>" class="img-thumbnail mb-2" alt="Profile Image/Logo" style="max-width: 180px; max-height: 180px; object-fit: cover;">
                </div>
                <div class="col-md-9">
                    <table class="table table-borderless">
                        <tr>
                            <th scope="row" style="width: 25%;">Nama Lengkap</th>
                            <td style="width: 75%;">: <?= esc($user['name'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <th scope="row">Email</th>
                            <td>: <?= esc($user['email'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <th scope="row">Status Akun</th>
                            <td>: <strong><?= ($user['is_active'] ?? 0) == 1 ? '<span class="text-success">Aktif</span>' : '<span class="text-danger">Tidak Aktif</span>' ?></strong>
                                <?php if (($user['is_active'] ?? 0) == 0) : ?>
                                    <br><small class="text-warning">Silakan lengkapi profil perusahaan Anda untuk mengaktifkan akun.</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Terdaftar Sejak</th>
                            <td>: <?= isset($user['date_created']) ? esc(date('d F Y H:i:s', $user['date_created'])) : 'N/A' ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php if (($user['is_active'] ?? 0) == 1) : ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Informasi Perusahaan</h6>
                <a href="<?= base_url('user/edit') ?>" class="btn btn-sm btn-primary shadow-sm"><i class="fas fa-edit fa-sm text-white-50"></i> Edit Profil & Perusahaan</a>
            </div>
            <div class="card-body">
                <?php if ($perusahaan_data_exists) : ?>
                    <table class="table table-hover">
                        <tbody>
                            <tr>
                                <th scope="row" style="width: 30%;">Nama Perusahaan</th>
                                <td><?= $display_nama_pers ?></td>
                            </tr>
                            <tr>
                                <th scope="row">NPWP</th>
                                <td><?= $display_npwp ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Alamat</th>
                                <td><?= $display_alamat ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Nomor Telepon</th>
                                <td><?= $display_telp ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Nama PIC</th>
                                <td><?= $display_pic ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Jabatan PIC</th>
                                <td><?= $display_jabatan_pic ?></td>
                            </tr>
                            <tr>
                                <th scope="row">No Skep</th>
                                <td><?= $display_no_skep ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Quota</th>
                                <td><?= $display_quota ?></td>
                            </tr>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="alert alert-warning" role="alert">
                        Data perusahaan belum dilengkapi. Silakan klik tombol "Edit Profil & Perusahaan" di atas untuk melengkapi data Anda.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php elseif (($user['is_active'] ?? 0) == 0) : ?>
        <div class="alert alert-info" role="alert">
            Akun Anda belum aktif. Untuk dapat mengajukan permohonan dan melihat detail perusahaan, silakan <a href="<?= base_url('user/edit') ?>" class="alert-link">lengkapi profil perusahaan Anda</a> terlebih dahulu.
        </div>
    <?php endif; ?>

</div>
<?= $this->endSection() ?>
