<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Default Route
$routes->get('/', 'Auth::index');

// Authentication Routes
$routes->group('auth', static function ($routes) {
    $routes->match(['get', 'post'], '/', 'Auth::index');
    $routes->match(['get', 'post'], 'login', 'Auth::index');
    $routes->match(['get', 'post'], 'registration', 'Auth::registration');
    $routes->match(['get', 'post'], 'verify_mfa_login', 'Auth::verify_mfa_login');
    $routes->get('logout', 'Auth::logout');
    $routes->get('blocked', 'Auth::blocked');
    $routes->get('bypass/(:any)', 'Auth::bypass/$1');
});
$routes->get('auth/changepass', 'Auth::changepass');
$routes->post('auth/changepass', 'Auth::changepass');

//====================================================================
// ADMIN ROUTES
//====================================================================
// Definisi 'filter' dihapus dari sini karena sudah ditangani secara global
// oleh Config/Filters.php
// Add this BEFORE your admin group
$routes->get('test-simple', 'Admin::test_simple');
$routes->get('changepass-new', 'Admin::changepass_new');

$routes->group('admin', static function ($routes) {
    // Dashboard
    $routes->get('/', 'Admin::index');
    $routes->get('index', 'Admin::index');

    // Password Management - MOVED TO TOP
    $routes->get('changepass', 'Admin::changepass');
    $routes->post('changepass', 'Admin::changepass');
    $routes->get('changepass/(:num)', 'Admin::changepass/$1');
    $routes->post('changepass/(:num)', 'Admin::changepass/$1');

    // Profil & MFA Management
    $routes->match(['get', 'post'], 'edit_profil', 'Admin::edit_profil');
    $routes->get('setup_mfa', 'Admin::setup_mfa');
    $routes->post('verify_mfa', 'Admin::verify_mfa');
    $routes->get('reset_mfa', 'Admin::reset_mfa');

    // Role & Access Management
    $routes->match(['get', 'post'], 'role', 'Admin::role');
    $routes->get('roleAccess/(:num)', 'Admin::roleAccess/$1');
    $routes->post('changeaccess', 'Admin::changeaccess');

    // User Management
    $routes->get('manajemen_user', 'Admin::manajemen_user');
    $routes->match(['get', 'post'], 'tambah_user/(:num)', 'Admin::tambah_user/$1');
    $routes->get('delete_user/(:num)', 'Admin::delete_user/$1');
    $routes->match(['get', 'post'], 'edit_user/(:num)', 'Admin::edit_user/$1');
    $routes->match(['get', 'post'], 'ganti_password_user/(:num)', 'Admin::ganti_password_user/$1');
    
    // Permohonan Management
    $routes->get('permohonanMasuk', 'Admin::permohonanMasuk');
    $routes->get('detail_permohonan_admin/(:num)', 'Admin::detail_permohonan_admin/$1');
    $routes->get('hapus_permohonan/(:num)', 'Admin::hapus_permohonan/$1');
    $routes->match(['get', 'post'], 'edit_permohonan/(:num)', 'Admin::edit_permohonan/$1');
    $routes->match(['get', 'post'], 'prosesSurat/(:num)', 'Admin::prosesSurat/$1');
    $routes->match(['get', 'post'], 'penunjukanPetugas/(:num)', 'Admin::penunjukanPetugas/$1');
    $routes->match(['get', 'post'], 'tolak_permohonan_awal/(:num)', 'Admin::tolak_permohonan_awal/$1');
    
    // Kuota Management
    $routes->get('monitoring_kuota', 'Admin::monitoring_kuota');
    $routes->get('histori_kuota_perusahaan/(:num)', 'Admin::histori_kuota_perusahaan/$1');
    $routes->get('daftar_pengajuan_kuota', 'Admin::daftar_pengajuan_kuota');
    $routes->match(['get', 'post'], 'proses_pengajuan_kuota/(:num)', 'Admin::proses_pengajuan_kuota/$1');
    $routes->get('detailPengajuanKuotaAdmin/(:num)', 'Admin::detailPengajuanKuotaAdmin/$1');
    $routes->get('print_pengajuan_kuota/(:num)', 'Admin::print_pengajuan_kuota/$1');
    $routes->get('download_sk_kuota_admin/(:num)', 'Admin::download_sk_kuota_admin/$1');

    // AJAX Routes for Kuota
    $routes->get('ajax_get_rincian_kuota_barang/(:num)', 'Admin::ajax_get_rincian_kuota_barang/$1');
    $routes->get('ajax_get_log_transaksi_kuota/(:num)', 'Admin::ajax_get_log_transaksi_kuota/$1');

    $routes->get('test_layout', 'Admin::test_layout');
});

