<?php

namespace App\Controllers;

use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class Monitoring extends BaseController
{
    protected $db;
    protected $session;

    public function __construct()
    {
        // Pustaka/helper standar dimuat melalui BaseController atau autoload.php
        // Contoh: helper('url'), session()
        helper(['url', 'repack_helper']);
        $this->session = session();
        $this->db = \Config\Database::connect();

        $router = \Config\Services::router();
        $current_method = $router->methodName();

        $excluded_methods_for_full_auth_check = ['logout'];

        if (!in_array($current_method, $excluded_methods_for_full_auth_check)) {
            $this->_check_auth_monitoring();
        } elseif (!$this->session->get('email') && $current_method != 'logout') {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Sesi tidak valid atau telah berakhir. Silakan login kembali.</div>');
            // Redirect response akan ditangani oleh return di method pemanggil
            // Namun karena ini di constructor, exit lebih aman untuk menghentikan eksekusi
            header('Location: ' . base_url('auth'));
            exit;
        }
        log_message('debug', 'Monitoring Class Initialized. Method: ' . $current_method);
    }

    private function _check_auth_monitoring()
    {
        log_message('debug', 'Monitoring: _check_auth_monitoring() called. Email session: ' . ($this->session->get('email') ?? 'NULL'));
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Mohon login untuk melanjutkan.</div>');
            header('Location: ' . base_url('auth'));
            exit;
        }

        $role_id_session = $this->session->get('role_id');
        log_message('debug', 'Monitoring: _check_auth_monitoring() - Role ID: ' . ($role_id_session ?? 'NULL'));

        if ($role_id_session != 4) {
            log_message('warning', 'Monitoring: _check_auth_monitoring() - Akses ditolak, role ID tidak sesuai: ' . $role_id_session);
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Akses Ditolak! Anda tidak diotorisasi untuk mengakses halaman ini.</div>');
            
            $redirect_url = 'auth/blocked';
            if ($role_id_session == 1) $redirect_url = 'admin';
            elseif ($role_id_session == 2) $redirect_url = 'user';
            elseif ($role_id_session == 3) $redirect_url = 'petugas';

            header('Location: ' . base_url($redirect_url));
            exit;
        }

        log_message('debug', 'Monitoring: _check_auth_monitoring() passed.');
    }

    public function index()
    {
        log_message('debug', 'Monitoring: index() called.');
        $builder_user = $this->db->table('user');
        $builder_pengajuan = $this->db->table('user_pengajuan_kuota');
        $builder_permohonan = $this->db->table('user_permohonan');

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Dashboard Monitoring';
        $data['user'] = $builder_user->getWhere(['email' => $this->session->get('email')])->getRowArray();

        $data['total_pengajuan_kuota_all'] = $builder_pengajuan->countAllResults();
        $data['total_permohonan_impor_all'] = $builder_permohonan->countAllResults();

        $data['pengajuan_kuota_pending'] = $builder_pengajuan->where('status', 'pending')->countAllResults();
        $data['pengajuan_kuota_approved'] = $builder_pengajuan->where('status', 'approved')->countAllResults();
        $data['pengajuan_kuota_rejected'] = $builder_pengajuan->where('status', 'rejected')->countAllResults();

        $data['permohonan_impor_baru_atau_diproses_admin'] = $builder_permohonan->whereIn('status', ['0', '5'])->countAllResults();
        $data['permohonan_impor_penunjukan_petugas'] = $builder_permohonan->where('status', '1')->countAllResults();
        $data['permohonan_impor_lhp_direkam'] = $builder_permohonan->where('status', '2')->countAllResults();
        $data['permohonan_impor_selesai_disetujui'] = $builder_permohonan->where('status', '3')->countAllResults();
        $data['permohonan_impor_selesai_ditolak'] = $builder_permohonan->where('status', '4')->countAllResults();

        return view('monitoring/dashboard_monitoring_view', $data);
    }

    public function pengajuan_kuota()
    {
        log_message('debug', 'Monitoring: pengajuan_kuota() (daftar) called.');
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Pantauan Data Pengajuan Kuota';
        $data['user'] = $this->db->table('user')->getWhere(['email' => $this->session->get('email')])->getRowArray();

        $builder = $this->db->table('user_pengajuan_kuota upk');
        $builder->select('upk.*, upr.NamaPers, u_pemohon.name as nama_pengaju_kuota, u_pemohon.email as email_pengaju_kuota');
        $builder->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $builder->join('user u_pemohon', 'upk.id_pers = u_pemohon.id', 'left');
        $builder->orderBy('upk.submission_date', 'DESC');
        $data['daftar_pengajuan_kuota'] = $builder->get()->getResultArray();
        log_message('debug', 'Monitoring: pengajuan_kuota() - Query: ' . $this->db->getLastQuery());

        return view('monitoring/daftar_pengajuan_kuota_view', $data);
    }

    public function permohonan_impor()
    {
        log_message('debug', 'Monitoring: permohonan_impor() (daftar) called.');
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Pantauan Data Permohonan Impor';
        $data['user'] = $this->db->table('user')->getWhere(['email' => $this->session->get('email')])->getRowArray();
        
        $builder = $this->db->table('user_permohonan up');
        $builder->select('up.*, upr.NamaPers, u_pemohon.name as nama_pemohon_impor, u_pemohon.email as email_pemohon_impor, u_petugas.name as nama_petugas_pemeriksa');
        $builder->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $builder->join('user u_pemohon', 'upr.id_pers = u_pemohon.id', 'left');
        $builder->join('petugas p_petugas', 'up.petugas = p_petugas.id', 'left');
        $builder->join('user u_petugas', 'p_petugas.id_user = u_petugas.id', 'left');
        $builder->orderBy('up.time_stamp', 'DESC');
        $data['daftar_permohonan_impor'] = $builder->get()->getResultArray();
        log_message('debug', 'Monitoring: permohonan_impor() - Query: ' . $this->db->getLastQuery());

        return view('monitoring/daftar_permohonan_impor_view', $data);
    }

    public function detail_pengajuan_kuota($id_pengajuan = 0)
    {
        log_message('debug', 'Monitoring: detail_pengajuan_kuota() called with ID: ' . $id_pengajuan);
        if ($id_pengajuan == 0 || !is_numeric($id_pengajuan)) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">ID Pengajuan Kuota tidak valid.</div>');
            return redirect()->to('monitoring/pengajuan_kuota');
        }

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Detail Pantauan Pengajuan Kuota ID: ' . htmlspecialchars($id_pengajuan);
        $data['user'] = $this->db->table('user')->getWhere(['email' => $this->session->get('email')])->getRowArray();

        $builder = $this->db->table('user_pengajuan_kuota upk');
        $builder->select('upk.*, upr.NamaPers, upr.npwp as npwp_perusahaan, upr.alamat as alamat_perusahaan, u_pemohon.name as nama_pengaju_kuota, u_pemohon.email as email_pengaju_kuota');
        $builder->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $builder->join('user u_pemohon', 'upk.id_pers = u_pemohon.id', 'left');
        $builder->where('upk.id', $id_pengajuan);
        $data['pengajuan'] = $builder->get()->getRowArray();
        log_message('debug', 'Monitoring: detail_pengajuan_kuota() - Query: ' . $this->db->getLastQuery());

        if (!$data['pengajuan']) {
            $this->session->setFlashdata('message', '<div class="alert alert-warning" role="alert">Data pengajuan kuota tidak ditemukan.</div>');
            return redirect()->to('monitoring/pengajuan_kuota');
        }

        return view('monitoring/detail_pengajuan_kuota_view', $data);
    }

    public function detail_permohonan_impor($id_permohonan = 0)
    {
        log_message('debug', 'Monitoring: detail_permohonan_impor() called with ID: ' . $id_permohonan);
        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid.</div>');
            return redirect()->to('monitoring/permohonan_impor');
        }
        
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Detail Pantauan Permohonan Impor ID: ' . htmlspecialchars($id_permohonan);
        $data['user'] = $this->db->table('user')->getWhere(['email' => $this->session->get('email')])->getRowArray();

        $builder = $this->db->table('user_permohonan up');
        $builder->select(
            'up.*, '.
            'upr.NamaPers, upr.npwp AS npwp_perusahaan, upr.alamat AS alamat_perusahaan, upr.NoSkep AS NoSkep_perusahaan_asal, ' .
            'u_pemohon.name as nama_pengaju_permohonan, u_pemohon.email as email_pengaju_permohonan, '.
            'p_petugas.NIP as nip_petugas_pemeriksa, u_petugas.name as nama_petugas_pemeriksa'
        );
        $builder->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $builder->join('user u_pemohon', 'upr.id_pers = u_pemohon.id', 'left');
        $builder->join('petugas p_petugas', 'up.petugas = p_petugas.id', 'left');
        $builder->join('user u_petugas', 'p_petugas.id_user = u_petugas.id', 'left');
        $builder->where('up.id', $id_permohonan);
        $data['permohonan_detail'] = $builder->get()->getRowArray();
        log_message('debug', 'Monitoring: detail_permohonan_impor() - Query: ' . $this->db->getLastQuery());

        if (!$data['permohonan_detail']) {
            $this->session->setFlashdata('message', '<div class="alert alert-warning" role="alert">Data permohonan impor tidak ditemukan.</div>');
            return redirect()->to('monitoring/permohonan_impor');
        }

        $data['lhp_detail'] = $this->db->table('lhp')->getWhere(['id_permohonan' => $id_permohonan])->getRowArray();
        $data['is_monitoring_view'] = true;
        
        return view('admin/detail_permohonan_admin_view', $data);
    }

    public function pantau_kuota_perusahaan()
    {
        log_message('debug', 'Monitoring: pantau_kuota_perusahaan() called.');
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Pantauan Kuota Perusahaan';
        $data['user'] = $this->db->table('user')->getWhere(['email' => $this->session->get('email')])->getRowArray();

        $builder = $this->db->table('user_perusahaan up');
        $builder->select('
            up.id_pers,
            up.NamaPers,
            u.email as user_email_kontak,
            (SELECT GROUP_CONCAT(DISTINCT ukb.nomor_skep_asal ORDER BY ukb.nomor_skep_asal SEPARATOR ", ")
             FROM user_kuota_barang ukb
             WHERE ukb.id_pers = up.id_pers AND ukb.status_kuota_barang = "active"
            ) as list_skep_aktif_barang,
            (SELECT SUM(ukb.initial_quota_barang)
             FROM user_kuota_barang ukb
             WHERE ukb.id_pers = up.id_pers 
            ) as total_initial_kuota_all_items,
            (SELECT SUM(ukb.remaining_quota_barang)
             FROM user_kuota_barang ukb
             WHERE ukb.id_pers = up.id_pers
            ) as total_remaining_kuota_all_items
        ');
        $builder->join('user u', 'up.id_pers = u.id', 'left');
        $builder->orderBy('up.NamaPers', 'ASC');
        $data['perusahaan_kuota_list'] = $builder->get()->getResultArray();
        
        log_message('debug', 'Monitoring: pantau_kuota_perusahaan() - Query: ' . $this->db->getLastQuery());
        log_message('debug', 'Monitoring: pantau_kuota_perusahaan() - Data Count: ' . count($data['perusahaan_kuota_list']));
        
        return view('monitoring/pantau_kuota_perusahaan_view', $data);
    }

    public function detail_kuota_perusahaan($id_pers = 0)
    {
        log_message('debug', 'Monitoring: detail_kuota_perusahaan() called for id_pers: ' . $id_pers);
        if ($id_pers == 0 || !is_numeric($id_pers)) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">ID Perusahaan tidak valid.</div>');
            return redirect()->to('monitoring/pantau_kuota_perusahaan');
        }

        $data['title'] = 'Returnable Package';
        $data['user'] = $this->db->table('user')->getWhere(['email' => $this->session->get('email')])->getRowArray();

        $builder_pers = $this->db->table('user_perusahaan up');
        $builder_pers->select('up.id_pers, up.NamaPers, up.npwp, up.alamat, u.email as user_email_kontak, u.name as nama_kontak_user');
        $builder_pers->join('user u', 'up.id_pers = u.id', 'left');
        $builder_pers->where('up.id_pers', $id_pers);
        $data['perusahaan_info'] = $builder_pers->get()->getRowArray();

        if (!$data['perusahaan_info']) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Data perusahaan tidak ditemukan untuk ID: ' . htmlspecialchars($id_pers) . '</div>');
            return redirect()->to('monitoring/pantau_kuota_perusahaan');
        }
        $data['subtitle'] = 'Detail Rincian & Histori Kuota: ' . htmlspecialchars($data['perusahaan_info']['NamaPers']);

        $builder_kuota = $this->db->table('user_kuota_barang ukb');
        $builder_kuota->select('ukb.id_kuota_barang, ukb.nama_barang, ukb.initial_quota_barang, ukb.remaining_quota_barang, ukb.nomor_skep_asal, ukb.tanggal_skep_asal, ukb.status_kuota_barang, ukb.waktu_pencatatan, admin_pencatat.name as nama_admin_pencatat_kuota');
        $builder_kuota->join('user admin_pencatat', 'ukb.dicatat_oleh_user_id = admin_pencatat.id', 'left');
        $builder_kuota->where('ukb.id_pers', $id_pers);
        $builder_kuota->orderBy('ukb.nama_barang ASC, ukb.tanggal_skep_asal DESC, ukb.waktu_pencatatan DESC');
        $data['detail_kuota_items'] = $builder_kuota->get()->getResultArray();
        log_message('debug', 'Monitoring: detail_kuota_perusahaan() - Query Detail Kuota Items: ' . $this->db->getLastQuery());

        $builder_log = $this->db->table('log_kuota_perusahaan lk');
        $builder_log->select('lk.*, u_pencatat.name as nama_pencatat_log');
        $builder_log->join('user u_pencatat', 'lk.dicatat_oleh_user_id = u_pencatat.id', 'left');
        $builder_log->where('lk.id_pers', $id_pers);
        $builder_log->orderBy('lk.tanggal_transaksi', 'DESC');
        $builder_log->orderBy('lk.id_log', 'DESC');
        $data['histori_transaksi_kuota'] = $builder_log->get()->getResultArray();
        log_message('debug', 'Monitoring: detail_kuota_perusahaan() - Query Histori Transaksi Kuota: ' . $this->db->getLastQuery());
        log_message('debug', 'Monitoring: detail_kuota_perusahaan() - Data Histori Transaksi Kuota Count: ' . count($data['histori_transaksi_kuota']));

        return view('monitoring/detail_kuota_perusahaan_view', $data);
    }

    public function setup_mfa()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Setup Multi-Factor Authentication';
        $data['user'] = $this->db->table('user')->getWhere(['email' => $this->session->get('email')])->getRowArray();

        $google2fa = new Google2FA();
        $user_builder = $this->db->table('user');

        if (empty($data['user']['google2fa_secret'])) {
            $secretKey = $google2fa->generateSecretKey();
            $user_builder->where('id', $data['user']['id']);
            $user_builder->update(['google2fa_secret' => $secretKey]);
        } else {
            $secretKey = $data['user']['google2fa_secret'];
        }

        $companyName = 'Repack Papin';
        $userEmail = $data['user']['email'];

        $qrCodeUrl = $google2fa->getQRCodeUrl($companyName, $userEmail, $secretKey);

        $renderer = new ImageRenderer(
            new RendererStyle(400),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeImage = $writer->writeString($qrCodeUrl);
        $qrCodeDataUri = 'data:image/svg+xml;base64,' . base64_encode($qrCodeImage);

        $data['qr_code_data_uri'] = $qrCodeDataUri;
        $data['secret_key'] = $secretKey;

        return view('monitoring/mfa_setup', $data);
    }

    public function verify_mfa()
    {
        $userId = $this->session->get('user_id');
        $user = $this->db->table('user')->getWhere(['id' => $userId])->getRowArray();
        $secret = $user['google2fa_secret'];

        $oneTimePassword = $this->request->getPost('one_time_password');

        $google2fa = new Google2FA();
        $isValid = $google2fa->verifyKey($secret, $oneTimePassword);

        if ($isValid) {
            $user_builder = $this->db->table('user');
            $user_builder->where('id', $userId);
            $user_builder->update(['is_mfa_enabled' => 1]);

            $this->session->set('mfa_verified', true);
            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Autentikasi Dua Faktor (MFA) berhasil diaktifkan!</div>');
            return redirect()->to('monitoring/index');
        } else {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Kode verifikasi salah. Silakan coba lagi.</div>');
            return redirect()->to('monitoring/setup_mfa');
        }
    }

    public function reset_mfa()
    {
        $user_id = $this->session->get('user_id');
        $user_builder = $this->db->table('user');
        
        $user_builder->where('id', $user_id);
        $user_builder->update([
            'is_mfa_enabled' => 0,
            'google2fa_secret' => null
        ]);

        $this->session->remove('mfa_verified');
        $this->session->setFlashdata('message', '<div class="alert alert-info" role="alert">MFA Anda telah dinonaktifkan. Silakan lakukan pengaturan ulang.</div>');
        return redirect()->to('monitoring/setup_mfa');
    }

    public function edit_profil()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Edit Profil Saya';
        $data['user'] = $this->db->table('user')->getWhere(['email' => $this->session->get('email')])->getRowArray();
        
        return view('monitoring/edit_profil_view', $data);
    }
}