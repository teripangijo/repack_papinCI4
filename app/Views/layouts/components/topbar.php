<?php
$userName = session()->get('name') ?? 'Guest';
$userImageName = session()->get('image') ?? 'default.webp';
$profileImagePath = base_url('uploads/profile_images/' . htmlspecialchars($userImageName));
$fallbackImagePath = base_url('assets/img/default.webp');
$role_id = session()->get('role_id');

// Determine URLs based on role
switch ($role_id) {
    case 1: // Admin
        $profileUrl = 'admin/edit_profil';
        $changepassUrl = 'admin/changepass';
        break;
    case 2: // Company/User
        $profileUrl = 'user/edit_profil';
        $changepassUrl = 'user/changepass';
        break;
    case 3: // Petugas
        $profileUrl = 'petugas/edit_profil';
        $changepassUrl = 'petugas/changepass';
        break;
    case 4: // Monitoring
        $profileUrl = 'monitoring/edit_profil';
        $changepassUrl = 'monitoring/changepass';
        break;
    default:
        $profileUrl = 'user/edit_profil';
        $changepassUrl = 'user/changepass';
        break;
}
?>

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
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?= $userName ?></span>
                <img class="img-profile rounded-circle"
                     src="<?= $profileImagePath ?>"
                     alt="<?= $userName ?> profile picture"
                     onerror="this.onerror=null; this.src='<?= $fallbackImagePath ?>';">
            </a>
            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="userDropdown">
                <a class="dropdown-item" href="<?= site_url($profileUrl) ?>">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Profil Saya
                </a>
                <a class="dropdown-item" href="<?= site_url($changepassUrl) ?>">
                    <i class="fas fa-key fa-sm fa-fw mr-2 text-gray-400"></i>
                    Ganti Password
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Logout
                </a>
            </div>
        </li>
    </ul>
</nav>
<!-- Flashdata Messages -->
<?php if (session()->getFlashdata('message')) : ?>
    <div class="container-fluid">
        <?= session()->getFlashdata('message'); ?>
    </div>
<?php endif; ?>
