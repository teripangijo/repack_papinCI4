<?php
namespace App\Controllers; // Pastikan namespace ini sesuai

use App\Controllers\BaseController; // Menggunakan BaseController yang baru
use PragmaRX\Google2FA\Google2FA; // Untuk MFA
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;

class Monitoring extends BaseController // Meng-extend BaseController
{
    protected $session;
    protected $db;
    protected $validation; // Properti untuk validasi
    protected $router; // Diperlukan untuk router service

    public function __construct()
    {
        // Konstruktor kosong karena inisialisasi dasar ditangani oleh BaseController
    }

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->validation = \Config\Services::validation(); // Inisialisasi validasi
        $this->router = \Config\Services::router(); // Inisialisasi router service

        // Helper url dan repack_helper sudah dimuat di BaseController::$helpers
        
        $excluded_methods_for_full_auth_check = ['logout'];
        $current_method = $this->router->methodName(); // Menggunakan methodName() di CI4

        if (!in_array($current_method, $excluded_methods_for_full_auth_check)) {
            $this->_check_auth_monitoring();
        }
        // Kondisi elseif tidak lagi diperlukan karena _check_auth_monitoring sudah menangani redirect
        // elseif (!$this->session->get('email') && $current_method != 'logout') {
        //      $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Sesi tidak valid atau telah berakhir. Silakan login kembali.</div>');
        //      return redirect()->to(base_url('auth'));
        // }
        log_message('debug', 'Monitoring Class Initialized. Method: ' . $current_method);
    }

    private function _check_auth_monitoring()
    {
        log_message('debug', 'Monitoring: _check_auth_monitoring() called. Email session: ' . ($this->session->get('email') ?? 'NULL'));
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Mohon login untuk melanjutkan.</div>');
            return redirect()->to(base_url('auth'));
        }

        $role_id_session = $this->session->get('role_id');

        log_message('debug', 'Monitoring: _check_auth_monitoring() - Role ID: ' . ($role_id_session ?? 'NULL'));

        if (($role_id_session ?? null) != 4) { // Gunakan null coalescing operator untuk keamanan
            log_message('warning', 'Monitoring: _check_auth_monitoring() - Akses ditolak, role ID tidak sesuai: ' . $role_id_session);
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Akses Ditolak! Anda tidak diotorisasi untuk mengakses halaman ini.</div>');
            if ($role_id_session == 1) return redirect()->to(base_url('admin'));
            elseif ($role_id_session == 2) return redirect()->to(base_url('user'));
            elseif ($role_id_session == 3) return redirect()->to(base_url('petugas'));
            else return redirect()->to(base_url('auth/blocked'));
        }

        log_message('debug', 'Monitoring: _check_auth_monitoring() passed.');
    }

    public function index()
    {
        log_message('debug', 'Monitoring: index() called.');
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Dashboard Monitoring';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();

        $data['total_pengajuan_kuota_all'] = $this->db->table('user_pengajuan_kuota')->countAllResults();
        $data['total_permohonan_impor_all'] = $this->db->table('user_permohonan')->countAllResults();

        $data['pengajuan_kuota_pending'] = $this->db->table('user_pengajuan_kuota')->where('status', 'pending')->countAllResults();
        $data['pengajuan_kuota_approved'] = $this->db->table('user_pengajuan_kuota')->where('status', 'approved')->countAllResults();
        $data['pengajuan_kuota_rejected'] = $this->db->table('user_pengajuan_kuota')->where('status', 'rejected')->countAllResults();

        $data['permohonan_impor_baru_atau_diproses_admin'] = $this->db->table('user_permohonan')->whereIn('status', ['0', '5'])->countAllResults();
        $data['permohonan_impor_penunjukan_petugas'] = $this->db->table('user_permohonan')->where('status', '1')->countAllResults();
        $data['permohonan_impor_lhp_direkam'] = $this->db->table('user_permohonan')->where('status', '2')->countAllResults();
        $data['permohonan_impor_selesai_disetujui'] = $this->db->table('user_permohonan')->where('status', '3')->countAllResults();
        $data['permohonan_impor_selesai_ditolak'] = $this->db->table('user_permohonan')->where('status', '4')->countAllResults();


        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('templates/topbar', $data);
        echo view('monitoring/dashboard_monitoring_view', $data);
        echo view('templates/footer');
    }

    public function pengajuan_kuota()
    {
        log_message('debug', 'Monitoring: pengajuan_kuota() (daftar) called.');
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Pantauan Data Pengajuan Kuota';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();

        $builder = $this->db->table('user_pengajuan_kuota upk');
        $builder->select('upk.*, upr.NamaPers, u_pemohon.name as nama_pengaju_kuota, u_pemohon.email as email_pengaju_kuota');
        $builder->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $builder->join('user u_pemohon', 'upk.id_pers = u_pemohon.id', 'left');
        $builder->orderBy('upk.submission_date', 'DESC');
        $data['daftar_pengajuan_kuota'] = $builder->get()->getResultArray();
        log_message('debug', 'Monitoring: pengajuan_kuota() - Query: ' . $this->db->getLastQuery()->getQueryString());

        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('templates/topbar', $data);
        echo view('monitoring/daftar_pengajuan_kuota_view', $data);
        echo view('templates/footer');
    }

    public function permohonan_impor()
    {
        log_message('debug', 'Monitoring: permohonan_impor() (daftar) called.');
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Pantauan Data Permohonan Impor';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();

        $builder = $this->db->table('user_permohonan up');
        $builder->select('up.*, upr.NamaPers, u_pemohon.name as nama_pemohon_impor, u_pemohon.email as email_pemohon_impor, u_petugas.name as nama_petugas_pemeriksa');
        $builder->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $builder->join('user u_pemohon', 'upr.id_pers = u_pemohon.id', 'left');
        $builder->join('petugas p_petugas', 'up.petugas = p_petugas.id', 'left');
        $builder->join('user u_petugas', 'p_petugas.id_user = u_petugas.id', 'left');
        $builder->orderBy('up.time_stamp', 'DESC');
        $data['daftar_permohonan_impor'] = $builder->get()->getResultArray();
        log_message('debug', 'Monitoring: permohonan_impor() - Query: ' . $this->db->getLastQuery()->getQueryString());


        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('templates/topbar', $data);
        echo view('monitoring/daftar_permohonan_impor_view', $data);
        echo view('templates/footer');
    }

    public function detail_pengajuan_kuota($id_pengajuan = 0)
    {
        log_message('debug', 'Monitoring: detail_pengajuan_kuota() called with ID: ' . $id_pengajuan);
        if ($id_pengajuan == 0 || !is_numeric($id_pengajuan)) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">ID Pengajuan Kuota tidak valid.</div>');
            return redirect()->to(base_url('monitoring/pengajuan_kuota'));
        }
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Detail Pantauan Pengajuan Kuota ID: ' . htmlspecialchars($id_pengajuan);
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();

        $builder = $this->db->table('user_pengajuan_kuota upk');
        $builder->select('upk.*, upr.NamaPers, upr.npwp as npwp_perusahaan, upr.alamat as alamat_perusahaan, u_pemohon.name as nama_pengaju_kuota, u_pemohon.email as email_pengaju_kuota');
        $builder->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $builder->join('user u_pemohon', 'upk.id_pers = u_pemohon.id', 'left');
        $builder->where('upk.id', $id_pengajuan);
        $data['pengajuan'] = $builder->get()->getRowArray();
        log_message('debug', 'Monitoring: detail_pengajuan_kuota() - Query: ' . $this->db->getLastQuery()->getQueryString());

        if (!$data['pengajuan']) {
            $this->session->setFlashdata('message', '<div class="alert alert-warning" role="alert">Data pengajuan kuota tidak ditemukan.</div>');
            return redirect()->to(base_url('monitoring/pengajuan_kuota'));
        }

        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('templates/topbar', $data);
        echo view('monitoring/detail_pengajuan_kuota_view', $data);
        echo view('templates/footer');
    }

    public function detail_permohonan_impor($id_permohonan = 0)
    {
        log_message('debug', 'Monitoring: detail_permohonan_impor() called with ID: ' . $id_permohonan);
         if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid.</div>');
            return redirect()->to(base_url('monitoring/permohonan_impor'));
        }
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Detail Pantauan Permohonan Impor ID: ' . htmlspecialchars($id_permohonan);
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();

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
        log_message('debug', 'Monitoring: detail_permohonan_impor() - Query: ' . $this->db->getLastQuery()->getQueryString());

        if (!$data['permohonan_detail']) {
            $this->session->setFlashdata('message', '<div class="alert alert-warning" role="alert">Data permohonan impor tidak ditemukan.</div>');
            return redirect()->to(base_url('monitoring/permohonan_impor'));
        }

        $data['lhp_detail'] = $this->db->table('lhp')->where('id_permohonan', $id_permohonan)->get()->getRowArray();

        $data['is_monitoring_view'] = TRUE;
        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('templates/topbar', $data);
        echo view('admin/detail_permohonan_admin_view', $data); // Asumsi view ini bisa digunakan juga oleh Monitoring
        echo view('templates/footer');
    }

    public function pantau_kuota_perusahaan()
    {
        log_message('debug', 'Monitoring: pantau_kuota_perusahaan() called.');
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Pantauan Kuota Perusahaan';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();

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

        log_message('debug', 'Monitoring: pantau_kuota_perusahaan() - Query: ' . $this->db->getLastQuery()->getQueryString());
        log_message('debug', 'Monitoring: pantau_kuota_perusahaan() - Data Count: ' . count($data['perusahaan_kuota_list']));

        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('templates/topbar', $data);
        echo view('monitoring/pantau_kuota_perusahaan_view', $data);
        echo view('templates/footer');
    }

    public function detail_kuota_perusahaan($id_pers = 0)
    {
        log_message('debug', 'Monitoring: detail_kuota_perusahaan() called for id_pers: ' . $id_pers);
        if ($id_pers == 0 || !is_numeric($id_pers)) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">ID Perusahaan tidak valid.</div>');
            return redirect()->to(base_url('monitoring/pantau_kuota_perusahaan'));
        }

        $data['title'] = 'Returnable Package';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();

        $builder = $this->db->table('user_perusahaan up');
        $builder->select('up.id_pers, up.NamaPers, up.npwp, up.alamat, u.email as user_email_kontak, u.name as nama_kontak_user');
        $builder->join('user u', 'up.id_pers = u.id', 'left');
        $builder->where('up.id_pers', $id_pers);
        $data['perusahaan_info'] = $builder->get()->getRowArray();

        if (!$data['perusahaan_info']) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Data perusahaan tidak ditemukan untuk ID: ' . htmlspecialchars($id_pers) . '</div>');
            return redirect()->to(base_url('monitoring/pantau_kuota_perusahaan'));
        }
        $data['subtitle'] = 'Detail Rincian & Histori Kuota: ' . htmlspecialchars($data['perusahaan_info']['NamaPers']);

        $builder = $this->db->table('user_kuota_barang ukb');
        $builder->select('ukb.id_kuota_barang, ukb.nama_barang, ukb.initial_quota_barang, ukb.remaining_quota_barang, ukb.nomor_skep_asal, ukb.tanggal_skep_asal, ukb.status_kuota_barang, ukb.waktu_pencatatan, admin_pencatat.name as nama_admin_pencatat_kuota');
        $builder->join('user admin_pencatat', 'ukb.dicatat_oleh_user_id = admin_pencatat.id', 'left');
        $builder->where('ukb.id_pers', $id_pers);
        $builder->orderBy('ukb.nama_barang ASC, ukb.tanggal_skep_asal DESC, ukb.waktu_pencatatan DESC');
        $data['detail_kuota_items'] = $builder->get()->getResultArray();
        log_message('debug', 'Monitoring: detail_kuota_perusahaan() - Query Detail Kuota Items: ' . $this->db->getLastQuery()->getQueryString());

        $builder = $this->db->table('log_kuota_perusahaan lk');
        $builder->select('lk.*, u_pencatat.name as nama_pencatat_log');
        $builder->join('user u_pencatat', 'lk.dicatat_oleh_user_id = u_pencatat.id', 'left');
        $builder->where('lk.id_pers', $id_pers);
        $builder->orderBy('lk.tanggal_transaksi', 'DESC');
        $builder->orderBy('lk.id_log', 'DESC');
        $data['histori_transaksi_kuota'] = $builder->get()->getResultArray();
        log_message('debug', 'Monitoring: detail_kuota_perusahaan() - Query Histori Transaksi Kuota: ' . $this->db->getLastQuery()->getQueryString());
        log_message('debug', 'Monitoring: detail_kuota_perusahaan() - Data Histori Transaksi Kuota Count: ' . count($data['histori_transaksi_kuota']));

        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('templates/topbar', $data);
        echo view('monitoring/detail_kuota_perusahaan_view', $data);
        echo view('templates/footer');
    }

    public function setup_mfa()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Setup Multi-Factor Authentication';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();

        $google2fa = new Google2FA();

        if (empty($data['user']['google2fa_secret'])) {
            $secretKey = $google2fa->generateSecretKey();
            $this->db->table('user')->where('id', $data['user']['id'])->update(['google2fa_secret' => $secretKey]);
            $data['user']['google2fa_secret'] = $secretKey; // Update data user di array agar QR code terbuat
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

        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('templates/topbar', $data);
        echo view('monitoring/mfa_setup', $data);
        echo view('templates/footer');
    }

    public function verify_mfa()
    {
        $userId = $this->session->get('user_id');
        $user = $this->db->table('user')->where('id', $userId)->get()->getRowArray();
        $secret = $user['google2fa_secret'] ?? '';

        $oneTimePassword = $this->request->getPost('one_time_password');

        $google2fa = new Google2FA();
        $isValid = $google2fa->verifyKey($secret, $oneTimePassword);

        if ($isValid) {
            $this->db->table('user')->where('id', $userId)->update(['is_mfa_enabled' => 1]);

            $this->session->set('mfa_verified', true); // Menggunakan set() untuk session data

            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Autentikasi Dua Faktor (MFA) berhasil diaktifkan!</div>');
            return redirect()->to(base_url('monitoring/index'));
        } else {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Kode verifikasi salah. Silakan coba lagi.</div>');
            return redirect()->to(base_url('monitoring/setup_mfa'));
        }
    }

    public function reset_mfa()
    {
        $user_id = $this->session->get('user_id');

        $this->db->table('user')->where('id', $user_id)->update([
            'is_mfa_enabled' => 0,
            'google2fa_secret' => null
        ]);

        $this->session->remove('mfa_verified'); // Menghapus data mfa_verified

        $this->session->setFlashdata('message', '<div class="alert alert-info" role="alert">MFA Anda telah dinonaktifkan. Silakan lakukan pengaturan ulang.</div>');
        return redirect()->to(base_url('monitoring/setup_mfa'));
    }

    public function edit_profil()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Edit Profil Saya';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();

        // Implementasi logika POST request untuk update profil, serupa dengan Admin.php
        if ($this->request->getMethod() === 'post') {
            $update_data_user = [];
            $name_input = $this->request->getPost('name', true);
            $user_id = $data['user']['id'];

            if (!empty($name_input) && $name_input !== $data['user']['name']) {
                $update_data_user['name'] = htmlspecialchars($name_input);
            }

            $current_login_identifier = $data['user']['email'];
            $new_login_identifier = $this->request->getPost('login_identifier', true);

            if (!empty($new_login_identifier) && $new_login_identifier !== $current_login_identifier) {
                $this->validation->setRules([
                    'login_identifier' => [
                        'label' => 'Email Login',
                        'rules' => "trim|required|valid_email|is_unique[user.email,id,{$user_id}]",
                        'errors' => [
                            'is_unique' => 'Email ini sudah terdaftar.',
                            'valid_email' => 'Format email tidak valid.'
                        ]
                    ]
                ]);

                if ($this->validation->withRequest($this->request)->run()) {
                    $update_data_user['email'] = htmlspecialchars($new_login_identifier);
                } else {
                    $errors = $this->validation->getErrors();
                    foreach ($errors as $field => $error) {
                        $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">' . $error . '</div>');
                    }
                    return redirect()->to(base_url('monitoring/edit_profil'));
                }
            }

            if (!empty($update_data_user)) {
                $this->db->table('user')->where('id', $user_id)->update($update_data_user);
                $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Profil berhasil diupdate.</div>');
                if (isset($update_data_user['name'])) {
                    $this->session->set('name', $update_data_user['name']);
                }
                if (isset($update_data_user['email'])) {
                    $this->session->set('email', $update_data_user['email']);
                }
            }

            $profileImage = $this->request->getFile('profile_image');

            if ($profileImage && $profileImage->isValid() && !$profileImage->hasMoved()) {
                $upload_dir_profile = FCPATH . 'uploads/profile_images/';

                if (!is_dir($upload_dir_profile)) {
                    if (!@mkdir($upload_dir_profile, 0777, true)) {
                        $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Gagal membuat direktori upload foto profil.</div>');
                        return redirect()->to(base_url('monitoring/edit_profil'));
                    }
                }

                if (!is_writable($upload_dir_profile)) {
                    $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Upload error: Direktori foto profil tidak writable.</div>');
                    return redirect()->to(base_url('monitoring/edit_profil'));
                }

                $fileName = $profileImage->getRandomName();
                $profileImage->move($upload_dir_profile, $fileName);

                if ($profileImage->hasMoved()) {
                    $old_image = $data['user']['image'];
                    if ($old_image != 'default.jpg' && !empty($old_image) && file_exists($upload_dir_profile . $old_image)) {
                        @unlink($upload_dir_profile . $old_image);
                    }

                    $this->db->table('user')->where('id', $user_id)->update(['image' => $fileName]);
                    $this->session->set('user_image', $fileName);
                    $current_flash = $this->session->getFlashdata('message');
                    $this->session->setFlashdata('message', ($current_flash ? $current_flash . '<br>' : '') . '<div class="alert alert-success" role="alert">Foto profil berhasil diupdate.</div>');
                } else {
                    $current_flash = $this->session->getFlashdata('message');
                    $this->session->setFlashdata('message', ($current_flash ? $current_flash . '<br>' : '') . '<div class="alert alert-danger" role="alert">Upload Foto Profil Gagal: ' . $profileImage->getErrorString() . '</div>');
                }
            }
            return redirect()->to(base_url('monitoring/edit_profil'));
        }
        // End of POST request handling for edit_profil

        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();

        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('templates/topbar', $data);
        echo view('monitoring/edit_profil_view', $data); // View baru
        echo view('templates/footer');
    }
}
