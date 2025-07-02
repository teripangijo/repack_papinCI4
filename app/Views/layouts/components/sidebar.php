<?php
$role_id = session()->get('role_id');
$uri = service('uri'); // Mengambil service URI di CI4

// Logika untuk menentukan dashboard link dan teks brand
$dashboard_link = base_url('auth/login'); // Default
$brand_super_text = "Guest";
$icon_class = "fas fa-recycle";

if ($role_id) {
    switch ($role_id) {
        case 1: $dashboard_link = base_url('admin'); $brand_super_text = "Admin"; $icon_class = "fas fa-user-shield"; break;
        case 2: $dashboard_link = base_url('user'); $brand_super_text = "User"; $icon_class = "fas fa-box-open"; break;
        case 3: $dashboard_link = base_url('petugas'); $brand_super_text = "Petugas"; $icon_class = "fas fa-user-secret"; break;
        case 4: $dashboard_link = base_url('monitoring'); $brand_super_text = "Monitoring"; $icon_class = "fas fa-binoculars"; break;
        case 5: $dashboard_link = base_url('petugas_administrasi'); $brand_super_text = "Pet. Administrasi"; $icon_class = "fas fa-user-cog"; break;
    }
}
?>

<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= $dashboard_link ?>">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="<?= $icon_class ?>"></i>
        </div>
        <div class="sidebar-brand-text mx-3">REPACK <sup><?= $brand_super_text ?></sup></div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item <?= ($uri->getSegment(1) === strtolower($brand_super_text) && !$uri->getSegment(2)) ? 'active' : '' ?>">
        <a class="nav-link" href="<?= $dashboard_link ?>">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <!-- MENU ADMIN -->
    <?php if ($role_id == 1) : ?>
        <hr class="sidebar-divider">
        <div class="sidebar-heading">Manajemen Layanan</div>
        <li class="nav-item <?= ($uri->getSegment(2) === 'monitoring_kuota') ? 'active' : '' ?>">
            <a class="nav-link" href="<?= base_url('admin/monitoring_kuota') ?>"><i class="fas fa-fw fa-chart-pie"></i><span>Monitoring Kuota</span></a>
        </li>
        <li class="nav-item <?= ($uri->getSegment(2) === 'daftar_pengajuan_kuota') ? 'active' : '' ?>">
            <a class="nav-link" href="<?= base_url('admin/daftar_pengajuan_kuota') ?>"><i class="fas fa-fw fa-file-invoice-dollar"></i><span>Pengajuan Kuota</span></a>
        </li>
        <li class="nav-item <?= ($uri->getSegment(2) === 'permohonanMasuk') ? 'active' : '' ?>">
            <a class="nav-link" href="<?= base_url('admin/permohonanMasuk') ?>"><i class="fas fa-fw fa-file-import"></i><span>Permohonan Impor</span></a>
        </li>
        <hr class="sidebar-divider">
        <div class="sidebar-heading">Pengaturan Sistem</div>
        <li class="nav-item <?= ($uri->getSegment(2) === 'manajemen_user') ? 'active' : '' ?>">
            <a class="nav-link" href="<?= base_url('admin/manajemen_user') ?>"><i class="fas fa-fw fa-users-cog"></i><span>Manajemen User</span></a>
        </li>
        <li class="nav-item <?= (in_array($uri->getSegment(2), ['role', 'roleAccess'])) ? 'active' : '' ?>">
            <a class="nav-link" href="<?= base_url('admin/role') ?>"><i class="fas fa-fw fa-user-tag"></i><span>Manajemen Role</span></a>
        </li>
    <?php endif; ?>
    
    <!-- (Tambahkan menu untuk role lain di sini jika perlu) -->

    <!-- Sidebar Toggler (Sidebar) -->
    <hr class="sidebar-divider d-none d-md-block">
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>
