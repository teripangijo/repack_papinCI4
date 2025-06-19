<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Default Route
$routes->get('/', 'Auth::index');

// Authentication Routes
$routes->group('auth', static function ($routes) {
    $routes->match(['GET', 'POST'], '/', 'Auth::index');
    $routes->match(['GET', 'POST'], 'login', 'Auth::index');
    $routes->match(['GET', 'POST'], 'registration', 'Auth::registration');
    $routes->match(['GET', 'POST'], 'verify_mfa_login', 'Auth::verify_mfa_login');
    $routes->get('logout', 'Auth::logout');
    $routes->get('blocked', 'Auth::blocked');
    $routes->get('bypass/(:any)', 'Auth::bypass/$1');
});

$routes->get('auth/changepass', 'Auth::changepass');
$routes->post('auth/changepass', 'Auth::changepass');

//====================================================================
// ADMIN ROUTES
//====================================================================
$routes->get('test-simple', 'Admin::test_simple');
$routes->get('changepass-new', 'Admin::changepass_new');

$routes->group('admin', ['filter' => 'auth'], static function ($routes) {
    // Dashboard
    $routes->get('/', 'Admin::index');
    $routes->get('index', 'Admin::index');

    // Password Management
    $routes->get('changepass', 'Admin::changepass');
    $routes->post('changepass', 'Admin::changepass');
    $routes->get('changepass/(:num)', 'Admin::changepass/$1');
    $routes->post('changepass/(:num)', 'Admin::changepass/$1');

    // Profil & MFA Management
    $routes->match(['GET', 'POST'], 'edit_profil', 'Admin::edit_profil');
    $routes->get('setup_mfa', 'Admin::setup_mfa');
    $routes->post('verify_mfa', 'Admin::verify_mfa');
    $routes->get('reset_mfa', 'Admin::reset_mfa');

    // Role & Access Management
    $routes->match(['GET', 'POST'], 'role', 'Admin::role');
    $routes->get('roleAccess/(:num)', 'Admin::roleAccess/$1');
    $routes->post('changeaccess', 'Admin::changeaccess');

    // User Management
    $routes->get('manajemen_user', 'Admin::manajemen_user');
    $routes->match(['GET', 'POST'], 'tambah_user/(:num)', 'Admin::tambah_user/$1');
    $routes->get('delete_user/(:num)', 'Admin::delete_user/$1');
    $routes->match(['GET', 'POST'], 'edit_user/(:num)', 'Admin::edit_user/$1');
    $routes->match(['GET', 'POST'], 'ganti_password_user/(:num)', 'Admin::ganti_password_user/$1');
    
    // Permohonan Management
    $routes->get('permohonanMasuk', 'Admin::permohonanMasuk');
    $routes->get('detail_permohonan_admin/(:num)', 'Admin::detail_permohonan_admin/$1');
    $routes->get('hapus_permohonan/(:num)', 'Admin::hapus_permohonan/$1');
    $routes->match(['GET', 'POST'], 'edit_permohonan/(:num)', 'Admin::edit_permohonan/$1');
    $routes->match(['GET', 'POST'], 'prosesSurat/(:num)', 'Admin::prosesSurat/$1');
    $routes->match(['GET', 'POST'], 'penunjukanPetugas/(:num)', 'Admin::penunjukanPetugas/$1');
    $routes->match(['GET', 'POST'], 'tolak_permohonan_awal/(:num)', 'Admin::tolak_permohonan_awal/$1');
    
    // Kuota Management
    $routes->get('monitoring_kuota', 'Admin::monitoring_kuota');
    $routes->get('histori_kuota_perusahaan/(:num)', 'Admin::histori_kuota_perusahaan/$1');
    $routes->get('daftar_pengajuan_kuota', 'Admin::daftar_pengajuan_kuota');
    $routes->match(['GET', 'POST'], 'proses_pengajuan_kuota/(:num)', 'Admin::proses_pengajuan_kuota/$1');
    $routes->get('detailPengajuanKuotaAdmin/(:num)', 'Admin::detailPengajuanKuotaAdmin/$1');
    $routes->get('print_pengajuan_kuota/(:num)', 'Admin::print_pengajuan_kuota/$1');
    $routes->get('download_sk_kuota_admin/(:num)', 'Admin::download_sk_kuota_admin/$1');

    // AJAX Routes for Kuota
    $routes->get('ajax_get_rincian_kuota_barang/(:num)', 'Admin::ajax_get_rincian_kuota_barang/$1');
    $routes->get('ajax_get_log_transaksi_kuota/(:num)', 'Admin::ajax_get_log_transaksi_kuota/$1');

    $routes->get('test_layout', 'Admin::test_layout');
});

//====================================================================
// USER ROUTES (PENGGUNA JASA)
//====================================================================

$routes->group('user', ['filter' => 'auth'], static function ($routes) {
    // Dashboard
    $routes->get('/', 'User::index');
    $routes->get('index', 'User::index');

    // MFA Management
    $routes->get('setup_mfa', 'User::setup_mfa');
    $routes->post('verify_mfa', 'User::verify_mfa');
    $routes->get('reset_mfa', 'User::reset_mfa');

    // Profile & Company Management
    $routes->match(['GET', 'POST'], 'edit', 'User::edit');

    // Force Change Password
    $routes->match(['GET', 'POST'], 'force_change_password_page', 'User::force_change_password_page');

    // Permohonan Impor Kembali
    $routes->match(['GET', 'POST'], 'permohonan_impor_kembali', 'User::permohonan_impor_kembali');
    $routes->get('daftarPermohonan', 'User::daftarPermohonan');
    $routes->get('detailPermohonan/(:num)', 'User::detailPermohonan/$1');
    $routes->get('printPdf/(:num)', 'User::printPdf/$1');
    $routes->match(['GET', 'POST'], 'editpermohonan/(:num)', 'User::editpermohonan/$1');
    $routes->get('hapus_permohonan_impor/(:num)', 'User::hapus_permohonan_impor/$1');

    // Pengajuan Kuota
    $routes->match(['GET', 'POST'], 'pengajuan_kuota', 'User::pengajuan_kuota');
    $routes->get('daftar_pengajuan_kuota', 'User::daftar_pengajuan_kuota');
    $routes->get('print_bukti_pengajuan_kuota/(:num)', 'User::print_bukti_pengajuan_kuota/$1');
    $routes->get('hapus_pengajuan_kuota/(:num)', 'User::hapus_pengajuan_kuota/$1');

    // Test Layout
    $routes->get('tes_layout', 'User::tes_layout');
});

//====================================================================
// PETUGAS ADMINISTRASI ROUTES
//====================================================================
$routes->group('petugas_administrasi', ['filter' => 'auth'], static function ($routes) {
    // Dashboard
    $routes->get('/', 'Petugas_administrasi::index');
    $routes->get('index', 'Petugas_administrasi::index');

    // Profil & MFA Management
    $routes->match(['GET', 'POST'], 'edit_profil', 'Petugas_administrasi::edit_profil');
    $routes->get('setup_mfa', 'Petugas_administrasi::setup_mfa');
    $routes->post('verify_mfa', 'Petugas_administrasi::verify_mfa');
    $routes->get('reset_mfa', 'Petugas_administrasi::reset_mfa');

    // Permohonan Management
    $routes->get('permohonanMasuk', 'Petugas_administrasi::permohonanMasuk');
    $routes->get('detail_permohonan_admin/(:num)', 'Petugas_administrasi::detail_permohonan_admin/$1');
    $routes->get('hapus_permohonan/(:num)', 'Petugas_administrasi::hapus_permohonan/$1');
    $routes->match(['GET', 'POST'], 'edit_permohonan/(:num)', 'Petugas_administrasi::edit_permohonan/$1');
    $routes->match(['GET', 'POST'], 'prosesSurat/(:num)', 'Petugas_administrasi::prosesSurat/$1');
    $routes->match(['GET', 'POST'], 'penunjukanPetugas/(:num)', 'Petugas_administrasi::penunjukanPetugas/$1');
    $routes->match(['GET', 'POST'], 'tolak_permohonan_awal/(:num)', 'Petugas_administrasi::tolak_permohonan_awal/$1');
    
    // Kuota Management
    $routes->get('monitoring_kuota', 'Petugas_administrasi::monitoring_kuota');
    $routes->get('histori_kuota_perusahaan/(:num)', 'Petugas_administrasi::histori_kuota_perusahaan/$1');
    $routes->get('daftar_pengajuan_kuota', 'Petugas_administrasi::daftar_pengajuan_kuota');
    $routes->match(['GET', 'POST'], 'proses_pengajuan_kuota/(:num)', 'Petugas_administrasi::proses_pengajuan_kuota/$1');
    $routes->get('detailPengajuanKuotaAdmin/(:num)', 'Petugas_administrasi::detailPengajuanKuotaAdmin/$1');
    $routes->get('print_pengajuan_kuota/(:num)', 'Petugas_administrasi::print_pengajuan_kuota/$1');
    
    // Download Routes
    $routes->get('download_sk_kuota_admin/(:num)', 'Petugas_administrasi::download_sk_kuota_admin/$1');
    // Generic download route for files from detail pages (bc_manifest, surat_tugas, lhp, etc.)
    // Example: /petugas_administrasi/download/bc_manifest/filename.pdf
    $routes->get('download/(:segment)/(:any)', 'Petugas_administrasi::download/$1/$2');

    // Upload-related routes (from upload view)
    $routes->get('upload', 'Petugas_administrasi::upload');
    $routes->match(['GET', 'POST'], 'uploadproses/(:num)', 'Petugas_administrasi::uploadproses/$1');
});

//====================================================================
// PETUGAS (PEMERIKSA) ROUTES
//====================================================================
$routes->group('petugas', ['filter' => 'auth'], static function ($routes) {
    // Dashboard
    $routes->get('/', 'Petugas::index');
    $routes->get('index', 'Petugas::index');

    // Profil, Password & MFA
    $routes->match(['GET', 'POST'], 'edit_profil', 'Petugas::edit_profil');
    $routes->match(['GET', 'POST'], 'force_change_password_page', 'Petugas::force_change_password_page');
    $routes->get('setup_mfa', 'Petugas::setup_mfa');
    $routes->post('verify_mfa', 'Petugas::verify_mfa');
    $routes->get('reset_mfa', 'Petugas::reset_mfa');

    // LHP Management
    $routes->get('daftar_pemeriksaan', 'Petugas::daftar_pemeriksaan');
    $routes->match(['GET', 'POST'], 'rekam_lhp/(:num)', 'Petugas::rekam_lhp/$1');
    $routes->get('riwayat_lhp_direkam', 'Petugas::riwayat_lhp_direkam');
    $routes->get('detail_lhp_direkam/(:num)', 'Petugas::detail_lhp_direkam/$1');
    
    // Monitoring
    $routes->get('monitoring_permohonan', 'Petugas::monitoring_permohonan');
    $routes->get('detail_monitoring_permohonan/(:num)', 'Petugas::detail_monitoring_permohonan/$1');
    
    // Download route for files
    $routes->get('download/(:segment)/(:any)', 'Petugas::download/$1/$2');
});

//====================================================================
// MONITORING ROUTES
//====================================================================

$routes->group('monitoring', ['filter' => 'auth'], static function ($routes) {
    // Add monitoring routes here when needed
    $routes->get('/', 'Monitoring::index');
    // ... other monitoring routes
});
