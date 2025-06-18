<?php
$display_nama_pers = '<span class="text-muted"><em>Data belum dilengkapi</em></span>';
$display_npwp = '<span class="text-muted"><em>Data belum dilengkapi</em></span>';
$display_alamat = '<span class="text-muted"><em>Data belum dilengkapi</em></span>';
$display_telp = '<span class="text-muted"><em>Data belum dilengkapi</em></span>';
$display_pic = '<span class="text-muted"><em>Data belum dilengkapi</em></span>';
$display_jabatan_pic = '<span class="text-muted"><em>Data belum dilengkapi</em></span>';
$display_no_skep = '<span class="text-muted"><em>Data belum dilengkapi</em></span>';
$display_quota = '<span class="text-muted"><em>Data belum dilengkapi</em></span>';
$perusahaan_data_exists = false;

if (isset($user_perusahaan) && is_array($user_perusahaan) && !empty($user_perusahaan)) {
    $perusahaan_data_exists = true;
    $display_nama_pers = isset($user_perusahaan['NamaPers']) ? htmlspecialchars($user_perusahaan['NamaPers']) : '<span class="text-danger"><em>Data tidak ditemukan</em></span>';
    $display_npwp = isset($user_perusahaan['npwp']) ? htmlspecialchars($user_perusahaan['npwp']) : '<span class="text-danger"><em>Data tidak ditemukan</em></span>';
    $display_alamat = isset($user_perusahaan['alamat']) ? htmlspecialchars($user_perusahaan['alamat']) : '<span class="text-danger"><em>Data tidak ditemukan</em></span>';
    $display_telp = isset($user_perusahaan['telp']) ? htmlspecialchars($user_perusahaan['telp']) : '<span class="text-danger"><em>Data tidak ditemukan</em></span>';
    $display_pic = isset($user_perusahaan['pic']) ? htmlspecialchars($user_perusahaan['pic']) : '<span class="text-danger"><em>Data tidak ditemukan</em></span>';
    $display_jabatan_pic = isset($user_perusahaan['jabatanPic']) ? htmlspecialchars($user_perusahaan['jabatanPic']) : '<span class="text-danger"><em>Data tidak ditemukan</em></span>';
    $display_no_skep = isset($user_perusahaan['NoSkep']) ? htmlspecialchars($user_perusahaan['NoSkep']) : '<span class="text-danger"><em>Data tidak ditemukan</em></span>';
    $display_quota = isset($user_perusahaan['quota']) ? htmlspecialchars($user_perusahaan['quota']) : '<span class="text-danger"><em>Data tidak ditemukan</em></span>';
}

$profile_image_name = isset($user['image']) && !empty($user['image']) ? $user['image'] : 'default.jpg';
$profile_image_path = base_url('uploads/kop/' . htmlspecialchars($profile_image_name));

?>

<div class="container-fluid">

    <h1 class="h3 mb-4 text-gray-800"><?= isset($subtitle) ? htmlspecialchars($subtitle) : 'My Profile'; ?></h1>

    <!-- <?php
    if ($this->session->flashdata('message')) {
        echo $this->session->flashdata('message');
    }
    ?> -->

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Informasi Pengguna</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 text-center">
                    <img src="<?= $profile_image_path; ?>" class="img-thumbnail mb-2" alt="Profile Image/Logo" style="max-width: 180px; max-height: 180px; object-fit: cover;">
                </div>
                <div class="col-md-9">
                    <table class="table table-borderless">
                        <tr>
                            <th scope="row" style="width: 25%;">Nama Lengkap</th>
                            <td style="width: 75%;">: <?= isset($user['name']) ? htmlspecialchars($user['name']) : 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <th scope="row">Email</th>
                            <td>: <?= isset($user['email']) ? htmlspecialchars($user['email']) : 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <th scope="row">Status Akun</th>
                            <td>: <strong><?php echo (isset($user['is_active']) && $user['is_active'] == 1) ? '<span class="text-success">Active</span>' : '<span class="text-danger">Not Active</span>'; ?></strong>
                                <?php if (isset($user['is_active']) && $user['is_active'] == 0) : ?>
                                    <br><small class="text-warning">Silakan lengkapi profil perusahaan Anda untuk mengaktifkan akun.</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Terdaftar Sejak</th>
                            <td>: <?= isset($user['date_created']) ? date('d F Y H:i:s', $user['date_created']) : 'N/A'; ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($user['is_active']) && $user['is_active'] == 1) : ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Informasi Perusahaan</h6>
                <a href="<?= site_url('user/edit'); ?>" class="btn btn-sm btn-primary shadow-sm"><i class="fas fa-edit fa-sm text-white-50"></i> Edit Profil & Perusahaan</a>
            </div>
            <div class="card-body">
                <?php if ($perusahaan_data_exists) : ?>
                    <table class="table table-hover">
                        <tbody>
                            <tr>
                                <th scope="row" style="width: 30%;">Nama Perusahaan</th>
                                <td style="width: 70%;"><?= $display_nama_pers; ?></td>
                            </tr>
                            <tr>
                                <th scope="row">NPWP</th>
                                <td><?= $display_npwp; ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Alamat</th>
                                <td><?= $display_alamat; ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Nomor Telepon</th>
                                <td><?= $display_telp; ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Nama PIC</th>
                                <td><?= $display_pic; ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Jabatan PIC</th>
                                <td><?= $display_jabatan_pic; ?></td>
                            </tr>
                            <tr>
                                <th scope="row">No Skep</th>
                                <td><?= $display_no_skep; ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Quota</th>
                                <td><?= $display_quota; ?></td>
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
    <?php elseif (isset($user['is_active']) && $user['is_active'] == 0) : ?>
        <div class="alert alert-info" role="alert">
            Akun Anda belum aktif. Untuk dapat mengajukan permohonan dan melihat detail perusahaan, silakan <a href="<?= site_url('user/edit'); ?>" class="alert-link">lengkapi profil perusahaan Anda</a> terlebih dahulu.
        </div>
    <?php endif; ?>

</div>
</div>
