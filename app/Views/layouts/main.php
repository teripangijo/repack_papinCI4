<?php
// Mengambil data sesi dan service URI
$session = session();
$role_id = $session->get('role_id');
$user_name = $session->get('name'); // Ambil nama dari sesi
$user_image = $session->get('user_image') ?? 'default.webp'; // Ambil gambar profil dari sesi
$uri = service('uri');

// Logika untuk menentukan dashboard link dan teks brand berdasarkan role_id
$dashboard_link = site_url('/');
$brand_super_text = "Guest";
$icon_class = "fas fa-recycle";
$current_controller = "auth"; // Default controller

if ($role_id) {
    switch ($role_id) {
        case 1: $current_controller = 'admin'; $brand_super_text = "Admin"; $icon_class = "fas fa-user-shield"; break;
        case 2: $current_controller = 'user'; $brand_super_text = "User"; $icon_class = "fas fa-box-open"; break;
        case 3: $current_controller = 'petugas'; $brand_super_text = "Petugas"; $icon_class = "fas fa-user-secret"; break;
        case 4: $current_controller = 'monitoring'; $brand_super_text = "Monitoring"; $icon_class = "fas fa-binoculars"; break;
        case 5: $current_controller = 'petugas_administrasi'; $brand_super_text = "Pet. Administrasi"; $icon_class = "fas fa-user-cog"; break;
    }
    $dashboard_link = site_url($current_controller);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Sistem Returnable Package">
    <meta name="author" content="Developer">
    <title><?= esc($title ?? 'Repack') ?> &mdash; <?= $this->renderSection('title', 'Dashboard') ?></title>

    <!-- Custom fonts for this template-->
    <link href="<?= base_url('assets/vendor/fontawesome-free/css/all.min.css') ?>" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- SB Admin 2 styles -->
    <link href="<?= base_url('assets/css/sb-admin-2.min.css') ?>" rel="stylesheet">
    <!-- Custom Modern CSS -->
    <link href="<?= base_url('assets/css/custom-modern.css') ?>" rel="stylesheet">
    <!-- Gijgo Datepicker CSS -->
    <link href="https://unpkg.com/gijgo@1.9.13/css/gijgo.min.css" rel="stylesheet" type="text/css" />
    <!-- DataTables CSS -->
    <link href="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.css') ?>" rel="stylesheet">
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-dark sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= $dashboard_link ?>">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="<?= $icon_class ?>"></i>
                </div>
                <div class="sidebar-brand-text mx-3">REPACK <sup><?= esc($brand_super_text) ?></sup></div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <?php if ($role_id): // Hanya tampilkan jika sudah login ?>
            <li class="nav-item <?= ($uri->getSegment(1) == $current_controller && in_array($uri->getSegment(2), ['', 'index'])) ? 'active' : '' ?>">
                <a class="nav-link" href="<?= $dashboard_link ?>">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>
            <?php endif; ?>

            <!-- MENU ADMIN (role_id = 1) -->
            <?php if ($role_id == 1): ?>
                <hr class="sidebar-divider">
                <div class="sidebar-heading">Manajemen Layanan</div>
                <li class="nav-item <?= in_array($uri->getSegment(2), ['monitoring_kuota', 'histori_kuota_perusahaan']) ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= site_url('admin/monitoring_kuota') ?>"><i class="fas fa-fw fa-chart-pie"></i><span>Monitoring Kuota</span></a>
                </li>
                <li class="nav-item <?= in_array($uri->getSegment(2), ['daftar_pengajuan_kuota', 'proses_pengajuan_kuota', 'detailPengajuanKuotaAdmin']) ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= site_url('admin/daftar_pengajuan_kuota') ?>"><i class="fas fa-fw fa-file-invoice-dollar"></i><span>Pengajuan Kuota</span></a>
                </li>
                <li class="nav-item <?= in_array($uri->getSegment(2), ['permohonanMasuk', 'penunjukanPetugas', 'prosesSurat', 'detail_permohonan_admin']) ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= site_url('admin/permohonanMasuk') ?>"><i class="fas fa-fw fa-file-import"></i><span>Permohonan Impor</span></a>
                </li>
                <hr class="sidebar-divider">
                <div class="sidebar-heading">Pengaturan Sistem</div>
                <li class="nav-item <?= ($uri->getSegment(2) == 'manajemen_user') ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= site_url('admin/manajemen_user') ?>"><i class="fas fa-fw fa-users-cog"></i><span>Manajemen User</span></a>
                </li>
                <li class="nav-item <?= in_array($uri->getSegment(2), ['role', 'roleAccess']) ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= site_url('admin/role') ?>"><i class="fas fa-fw fa-user-tag"></i><span>Manajemen Role</span></a>
                </li>

            <!-- MENU PENGGUNA JASA (role_id = 2) -->
            <?php elseif ($role_id == 2): ?>
                <hr class="sidebar-divider">
                <div class="sidebar-heading">Layanan</div>
                <li class="nav-item <?= ($uri->getSegment(2) == 'pengajuan_kuota') ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= site_url('user/pengajuan_kuota') ?>"><i class="fas fa-fw fa-file-signature"></i><span>Pengajuan Kuota</span></a>
                </li>
                <li class="nav-item <?= in_array($uri->getSegment(2), ['daftar_pengajuan_kuota', 'print_bukti_pengajuan_kuota']) ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= site_url('user/daftar_pengajuan_kuota') ?>"><i class="fas fa-fw fa-list-alt"></i><span>Daftar Pengajuan Kuota</span></a>
                </li>
                <li class="nav-item <?= ($uri->getSegment(2) == 'permohonan_impor_kembali') ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= site_url('user/permohonan_impor_kembali') ?>"><i class="fas fa-fw fa-pallet"></i><span>Buat Permohonan Impor</span></a>
                </li>
                <li class="nav-item <?= in_array($uri->getSegment(2), ['daftarPermohonan', 'editpermohonan', 'printPdf', 'detailPermohonan']) ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= site_url('user/daftarPermohonan') ?>"><i class="fas fa-fw fa-history"></i><span>Daftar Permohonan</span></a>
                </li>

            <!-- MENU PETUGAS (role_id = 3) -->
            <?php elseif ($role_id == 3): ?>
                <hr class="sidebar-divider">
                <div class="sidebar-heading">Pemeriksaan</div>
                <li class="nav-item <?= in_array($uri->getSegment(2), ['daftar_pemeriksaan', 'rekam_lhp']) ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= site_url('petugas/daftar_pemeriksaan') ?>"><i class="fas fa-fw fa-tasks"></i><span>Tugas Pemeriksaan</span></a>
                </li>
                <li class="nav-item <?= in_array($uri->getSegment(2), ['riwayat_lhp_direkam', 'detail_lhp_direkam']) ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= site_url('petugas/riwayat_lhp_direkam') ?>"><i class="fas fa-fw fa-history"></i><span>Riwayat LHP Direkam</span></a>
                </li>
                <hr class="sidebar-divider mt-2 mb-2">
                <div class="sidebar-heading">Pantauan (Petugas)</div>
                <li class="nav-item <?= in_array($uri->getSegment(2), ['monitoring_permohonan', 'detail_monitoring_permohonan']) ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= site_url('petugas/monitoring_permohonan') ?>"><i class="fas fa-fw fa-search-location"></i><span>Monitoring Permohonan</span></a>
                </li>

            <!-- MENU MONITORING (role_id = 4) -->
            <?php elseif ($role_id == 4): ?>
                <hr class="sidebar-divider">
                <div class="sidebar-heading">Pantauan Data Utama</div>
                <li class="nav-item <?= in_array($uri->getSegment(2), ['pengajuan_kuota', 'detail_pengajuan_kuota']) ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= site_url('monitoring/pengajuan_kuota') ?>"><i class="fas fa-fw fa-file-contract"></i><span>Pantauan Pengajuan Kuota</span></a>
                </li>
                <li class="nav-item <?= in_array($uri->getSegment(2), ['permohonan_impor', 'detail_permohonan_impor']) ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= site_url('monitoring/permohonan_impor') ?>"><i class="fas fa-fw fa-ship"></i><span>Pantauan Permohonan Impor</span></a>
                </li>
                <li class="nav-item <?= in_array($uri->getSegment(2), ['pantau_kuota_perusahaan', 'detail_kuota_perusahaan']) ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= site_url('monitoring/pantau_kuota_perusahaan') ?>"><i class="fas fa-fw fa-chart-pie"></i><span>Pantauan Kuota Perusahaan</span></a>
                </li>

            <!-- MENU PETUGAS ADMINISTRASI (role_id = 5) -->
            <?php elseif ($role_id == 5): ?>
                <hr class="sidebar-divider">
                <div class="sidebar-heading">Manajemen Layanan</div>
                <li class="nav-item <?= in_array($uri->getSegment(2), ['monitoring_kuota', 'histori_kuota_perusahaan']) ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= site_url('petugas_administrasi/monitoring_kuota') ?>"><i class="fas fa-fw fa-chart-pie"></i><span>Monitoring Kuota</span></a>
                </li>
                <li class="nav-item <?= in_array($uri->getSegment(2), ['daftar_pengajuan_kuota', 'proses_pengajuan_kuota', 'detailPengajuanKuotaAdmin']) ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= site_url('petugas_administrasi/daftar_pengajuan_kuota') ?>"><i class="fas fa-fw fa-file-invoice-dollar"></i><span>Pengajuan Kuota</span></a>
                </li>
                <li class="nav-item <?= in_array($uri->getSegment(2), ['permohonanMasuk', 'penunjukanPetugas', 'prosesSurat', 'detail_permohonan_admin']) ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= site_url('petugas_administrasi/permohonanMasuk') ?>"><i class="fas fa-fw fa-file-import"></i><span>Permohonan Impor</span></a>
                </li>

            <?php else: // GUEST atau role tidak dikenal ?>
                <hr class="sidebar-divider my-0">
                <li class="nav-item <?= ($uri->getSegment(1) == 'auth' || $uri->getSegment(1) == '') ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= site_url('auth/login') ?>"><i class="fas fa-fw fa-sign-in-alt"></i><span>Login</span></a>
                </li>
            <?php endif; ?>

            <?php if ($role_id): // Menu Umum untuk semua role yang login ?>
                <hr class="sidebar-divider">
                <div class="sidebar-heading">Akun Saya</div>
                <?php
                $edit_profil_method = ($role_id == 2) ? 'edit' : 'edit_profil';
                $edit_profil_url = site_url($current_controller . '/' . $edit_profil_method);
                ?>
                <li class="nav-item <?= ($uri->getSegment(1) == $current_controller && $uri->getSegment(2) == $edit_profil_method) ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= $edit_profil_url ?>">
                        <i class="fas fa-fw fa-user-edit"></i>
                        <span>Edit Profil Saya</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-toggle="modal" data-target="#logoutModal">
                        <i class="fas fa-fw fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <div class="topbar-divider d-none d-sm-block"></div>
                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?= esc($user_name) ?></span>
                                <img class="img-profile rounded-circle" src="<?= base_url('uploads/profile_images/' . esc($user_image)) ?>">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <?php
                                    $topbar_edit_profil_method = ($role_id == 2) ? 'edit' : 'edit_profil';
                                    $topbar_edit_profil_url = site_url($current_controller . '/' . $topbar_edit_profil_method);
                                ?>
                                <a class="dropdown-item" href="<?= $topbar_edit_profil_url ?>">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i> Profile
                                </a>
                                <a class="dropdown-item" href="<?= site_url('user/setup_mfa') ?>">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i> Settings
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i> Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <?php if (session()->getFlashdata('message')): ?>
                        <?= session()->getFlashdata('message') ?>
                    <?php endif; ?>
                    
                    <!-- RENDER KONTEN UTAMA DARI VIEW -->
                    <?= $this->renderSection('content') ?>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Bea Cukai Pangkalpinang <?= date('Y') ?></span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Yakin ingin Logout?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Pilih "Logout" di bawah ini jika Anda siap untuk mengakhiri sesi Anda saat ini.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                    <a class="btn btn-primary" href="<?= site_url('auth/logout') ?>">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="<?= base_url('assets/vendor/jquery/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>

    <!-- Core plugin JavaScript-->
    <script src="<?= base_url('assets/vendor/jquery-easing/jquery.easing.min.js') ?>"></script>

    <!-- Custom scripts for all pages-->
    <script src="<?= base_url('assets/js/sb-admin-2.min.js') ?>"></script>
    
    <!-- Gijgo Datepicker JS -->
    <script src="https://unpkg.com/gijgo@1.9.13/js/gijgo.min.js" type="text/javascript"></script>

    <!-- DataTables JS -->
    <script src="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.js') ?>"></script>
    <script src="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.js') ?>"></script>

    <!-- Page level custom scripts -->
    <?= $this->renderSection('scripts') ?>

</body>

</html>
