<?= $this->extend('layouts/main') ?> // Menggunakan layout 'main.php'
<?= $this->section('title') ?>
    <?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Manajemen User'; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($subtitle ?? 'Manajemen User'); ?></h1>
        <div>
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-primary shadow-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-user-plus fa-sm text-white-50"></i> Tambah User Baru
                </button>
                <div class="dropdown-menu">
                    <?php if (!empty($roles)): ?>
                        <?php foreach($roles as $role): ?>
                            <?php if ($role['id'] != 1): // Admin role cannot be added here ?>
                                <a class="dropdown-item" href="<?= site_url('admin/tambah_user/' . $role['id']); ?>">Tambah <?= htmlspecialchars($role['role']); ?></a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <a class="dropdown-item" href="#">Tidak ada role tersedia</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (session()->getFlashdata('message')) { echo session()->getFlashdata('message'); } ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar User Sistem</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTableManajemenUser" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Login ID (Email/NIP)</th>
                            <th>Role</th>
                            <th>Status Aktif</th>
                            <th>Tgl. Dibuat</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users_list)): $no = 1; foreach ($users_list as $usr): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($usr['name']); ?></td>
                            <td><?= htmlspecialchars($usr['email']); ?></td>
                            <td><span class="badge badge-info"><?= htmlspecialchars($usr['role_name'] ?? 'N/A'); ?></span></td>
                            <td>
                                <?php if ($usr['is_active'] == 1): ?>
                                    <span class="badge badge-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Tidak Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td><?= isset($usr['date_created']) ? date('d/m/Y H:i', $usr['date_created']) : '-'; ?></td>
                            <td>
                                <a href="<?= site_url('admin/edit_user/' . $usr['id']); ?>" class="btn btn-warning btn-circle btn-sm my-1" title="Edit User">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php
                                // Allow changing password if not the current logged-in user and not the main admin (ID 1)
                                if ($usr['id'] != 1 && $usr['id'] != $user['id']) : ?>
                                <a href="<?= site_url('admin/ganti_password_user/' . $usr['id']); ?>" class="btn btn-info btn-circle btn-sm my-1" title="Ganti Password User Ini">
                                    <i class="fas fa-key"></i>
                                </a>
                                <?php endif; ?>

                                <?php // Allow deleting user if not the main admin (ID 1)
                                if ($usr['id'] != 1) : ?>
                                <a href="<?= site_url('admin/delete_user/' . $usr['id']); ?>" class="btn btn-danger btn-circle btn-sm my-1" title="Hapus User" onclick="return confirm('Apakah Anda yakin ingin menghapus user <?= htmlspecialchars($usr['name']); ?>? Tindakan ini juga akan menghapus data terkait jika ada (misal data detail petugas).');">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="7" class="text-center">Tidak ada data user.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#dataTableManajemenUser').DataTable({
            "order": [[1, "asc"]], // Urutkan berdasarkan Nama
            "columnDefs": [
                { "orderable": false, "targets": [0, 6] } // Kolom # dan Action
            ]
        });
    }
});
</script>
<?= $this->endSection() ?>
