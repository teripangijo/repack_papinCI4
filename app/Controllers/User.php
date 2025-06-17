<?php

namespace App\Controllers; // Pastikan namespace ini sesuai

use App\Controllers\BaseController; // Menggunakan BaseController yang baru
use PragmaRX\Google2FA\Google2FA; // Untuk MFA
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;

class User extends BaseController // Meng-extend BaseController
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

        // Helper url, form, dan download sudah dimuat di BaseController::$helpers
        // Pastikan juga repack_helper sudah dimuat di BaseController::$helpers

        $this->_check_auth(); // Panggil fungsi otentikasi di initController
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
        echo view('user/mfa_setup', $data);
        echo view('templates/footer');
    }

    public function verify_mfa()
    {
        $userId = $this->session->get('user_id');
        $user = $this->db->table('user')->where('id', $userId)->get()->getRowArray();
        $secret = $user['google2fa_secret'] ?? '';

        $oneTimePassword = $this->request->getPost('one_time_password');

        $google2fa = new Google2FA();
        $window = 4;
        $isValid = $google2fa->verifyKey($secret, $oneTimePassword, $window);

        if ($isValid) {
            $this->db->table('user')->where('id', $userId)->update(['is_mfa_enabled' => 1]);

            $this->session->set('mfa_verified', true);

            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Autentikasi Dua Faktor (MFA) berhasil diaktifkan!</div>');
            return redirect()->to(base_url('user/index'));
        } else {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Kode verifikasi salah. Silakan coba lagi.</div>');
            return redirect()->to(base_url('user/setup_mfa'));
        }
    }

    public function reset_mfa()
    {
        $user_id = $this->session->get('user_id');

        $this->db->table('user')->where('id', $user_id)->update([
            'is_mfa_enabled' => 0,
            'google2fa_secret' => null
        ]);

        $this->session->remove('mfa_verified');

        $this->session->setFlashdata('message', '<div class="alert alert-info" role="alert">MFA Anda telah dinonaktifkan. Silakan lakukan pengaturan ulang.</div>');
        return redirect()->to(base_url('user/setup_mfa'));
    }

    private function _check_auth()
    {
        // PENTING: Pastikan Anda memuat helper 'url' di BaseController atau di sini
        // $this->load->helper('url'); // Sudah dimuat di BaseController

        // Periksa apakah session email ada, jika tidak, arahkan ke halaman login
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Mohon login untuk melanjutkan.</div>');
            return redirect()->to(base_url('auth'));
        }

        // Periksa role_id pengguna
        $role_id_session = $this->session->get('role_id');
        if (($role_id_session ?? null) != 2) { // Menggunakan null coalescing operator untuk keamanan
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda tidak diotorisasi untuk mengakses halaman ini.</div>');
            if ($role_id_session == 1) return redirect()->to(base_url('admin'));
            elseif ($role_id_session == 3) return redirect()->to(base_url('petugas'));
            elseif ($role_id_session == 4) return redirect()->to(base_url('monitoring'));
            else return redirect()->to(base_url('auth/blocked'));
        }

        // Periksa status aktif pengguna dan metode yang diizinkan untuk pengguna tidak aktif
        $user_is_active = $this->session->get('is_active');
        $current_method = $this->router->methodName(); // Menggunakan methodName() di CI4
        $allowed_inactive_methods = ['edit', 'logout', 'ganti_password', 'force_change_password_page']; // Tambahkan 'force_change_password_page'

        if (($user_is_active ?? null) == 0 && !in_array($current_method, $allowed_inactive_methods)) { // Menggunakan null coalescing
            // Pengecekan spesifik untuk user/edit tidak lagi diperlukan karena edit sudah di allowed_inactive_methods
            $this->session->setFlashdata('message', '<div class="alert alert-warning" role="alert">Akun Anda belum aktif. Mohon lengkapi profil perusahaan Anda.</div>');
            return redirect()->to(base_url('user/edit'));
        }
    }

    public function index()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Dashboard Pengguna Jasa';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        $id_user_login = $data['user']['id'];

        $data['user_perusahaan'] = $this->db->table('user_perusahaan')->where('id_pers', $id_user_login)->get()->getRowArray();

        $data['total_kuota_awal_disetujui_barang'] = 0;
        $data['total_sisa_kuota_barang'] = 0;
        $data['total_kuota_terpakai_barang'] = 0;
        $data['daftar_kuota_per_barang'] = [];

        if ($data['user_perusahaan']) {
            $builder = $this->db->table('user_kuota_barang');
            $builder->selectSum('initial_quota_barang', 'total_initial');
            $builder->selectSum('remaining_quota_barang', 'total_remaining');
            $builder->where('id_pers', $id_user_login);
            $agregat_kuota = $builder->get()->getRowArray();

            if ($agregat_kuota) {
                $data['total_kuota_awal_disetujui_barang'] = (float)($agregat_kuota['total_initial'] ?? 0);
                $data['total_sisa_kuota_barang'] = (float)($agregat_kuota['total_remaining'] ?? 0);
                $data['total_kuota_terpakai_barang'] = $data['total_kuota_awal_disetujui_barang'] - $data['total_sisa_kuota_barang'];
            }
            log_message('debug', 'USER DASHBOARD - Agregat Kuota Barang: ' . print_r($agregat_kuota, true));

            $builder = $this->db->table('user_kuota_barang');
            $builder->select('nama_barang, initial_quota_barang, remaining_quota_barang, nomor_skep_asal');
            $builder->where('id_pers', $id_user_login);
            $builder->where('status_kuota_barang', 'active');
            $builder->orderBy('nama_barang', 'ASC');
            $data['daftar_kuota_per_barang'] = $builder->get()->getResultArray();

            $builder = $this->db->table('user_permohonan');
            $builder->select('id, nomorSurat, TglSurat, NamaBarang, JumlahBarang, status, time_stamp');
            $builder->where('id_pers', $id_user_login);
            $builder->orderBy('time_stamp', 'DESC');
            $builder->limit(5);
            $data['recent_permohonan'] = $builder->get()->getResultArray();
        } else {
            $data['recent_permohonan'] = [];
            // Gunakan null coalescing operator untuk menghindari notice jika 'is_active' tidak ada
            if (($data['user']['is_active'] ?? null) == 1) {
                $this->session->setFlashdata('message_dashboard', '<div class="alert alert-info" role="alert">Selamat datang! Mohon lengkapi profil perusahaan Anda di menu "Edit Profile & Perusahaan" untuk dapat menggunakan semua fitur.</div>');
            }
        }

        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('templates/topbar', $data);
        echo view('user/dashboard', $data);
        echo view('templates/footer', $data);
    }

    public function edit()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Edit Profil & Perusahaan';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        $id_user_login = $data['user']['id'];

        $data['user_perusahaan'] = $this->db->table('user_perusahaan')->where('id_pers', $id_user_login)->get()->getRowArray();
        $is_activating = empty($data['user_perusahaan']);
        $data['is_activating'] = $is_activating;

        if (!$is_activating) {
            $builder = $this->db->table('user_kuota_barang');
            $builder->select('nama_barang, initial_quota_barang, remaining_quota_barang, nomor_skep_asal, tanggal_skep_asal, status_kuota_barang');
            $builder->where('id_pers', $id_user_login);
            $builder->orderBy('nama_barang', 'ASC');
            $data['daftar_kuota_barang_user'] = $builder->get()->getResultArray();
        } else {
            $data['daftar_kuota_barang_user'] = [];
        }

        $rules = [
            'NamaPers' => 'trim|required|max_length[100]',
            'npwp' => 'trim|required|regex_match[/^[0-9]{2}\.[0-9]{3}\.[0-9]{3}\.[0-9]{1}-[0-9]{3}\.[0-9]{3}$/]',
            'alamat' => 'trim|required|max_length[255]',
            'telp' => 'trim|required|numeric|max_length[15]',
            'pic' => 'trim|required|max_length[100]',
            'jabatanPic' => 'trim|required|max_length[100]',
            'NoSkepFasilitas' => 'trim|max_length[100]',
        ];

        if ($is_activating) {
            // Hanya tambahkan rules ini jika form aktivasi dan ada inputnya
            if ($this->request->getPost('initial_skep_no') || $this->request->getPost('initial_skep_tgl') || $this->request->getPost('initial_nama_barang') || $this->request->getPost('initial_kuota_jumlah')) {
                $rules['initial_skep_no'] = 'trim|required|max_length[100]';
                $rules['initial_skep_tgl'] = 'trim|required';
                $rules['initial_nama_barang'] = 'trim|required|max_length[100]';
                $rules['initial_kuota_jumlah'] = 'trim|required|numeric|greater_than[0]';
            }
        }

        // Aturan validasi upload file (menggunakan CI4 built-in rules)
        if ($this->request->getFile('ttd')->isValid()) {
            $rules['ttd'] = [
                'label' => 'Tanda Tangan PIC',
                'rules' => 'uploaded[ttd]|max_size[ttd,1024]|ext_in[ttd,jpg,png,jpeg,pdf]',
                'errors' => [
                    'uploaded' => '{field} wajib diupload saat aktivasi akun.',
                    'max_size' => 'Ukuran file {field} melebihi 1MB.',
                    'ext_in' => 'Tipe file {field} tidak diizinkan (Hanya JPG, PNG, PDF).'
                ]
            ];
        } else if ($is_activating) { // Wajib diupload saat aktivasi, jika tidak ada file, tambahkan rule uploaded
             $rules['ttd'] = [
                'label' => 'Tanda Tangan PIC',
                'rules' => 'uploaded[ttd]',
                'errors' => ['uploaded' => '{field} wajib diupload saat aktivasi akun.']
            ];
        }


        if ($this->request->getFile('profile_image')->isValid()) {
            $rules['profile_image'] = [
                'label' => 'Gambar Profil/Logo',
                'rules' => 'max_size[profile_image,1024]|ext_in[profile_image,jpg,png,jpeg,gif]|max_dims[profile_image,1024,1024]',
                'errors' => [
                    'max_size' => 'Ukuran file {field} melebihi 1MB.',
                    'ext_in' => 'Tipe file {field} tidak diizinkan (Hanya JPG, PNG, GIF).',
                    'max_dims' => 'Dimensi gambar {field} terlalu besar (Max 1024x1024px).'
                ]
            ];
        }
        if ($this->request->getFile('file_skep_fasilitas')->isValid()) {
            $rules['file_skep_fasilitas'] = [
                'label' => 'File SKEP Fasilitas',
                'rules' => 'max_size[file_skep_fasilitas,2048]|ext_in[file_skep_fasilitas,pdf,jpg,jpeg,png]',
                'errors' => [
                    'max_size' => 'Ukuran file {field} melebihi 2MB.',
                    'ext_in' => 'Tipe file {field} tidak diizinkan (Hanya PDF, JPG, PNG).'
                ]
            ];
        }
        if ($is_activating && $this->request->getFile('initial_skep_file')->isValid()) {
            $rules['initial_skep_file'] = [
                'label' => 'File SKEP Kuota Awal',
                'rules' => 'max_size[initial_skep_file,2048]|ext_in[initial_skep_file,pdf,jpg,jpeg,png]',
                'errors' => [
                    'max_size' => 'Ukuran file {field} melebihi 2MB.',
                    'ext_in' => 'Tipe file {field} tidak diizinkan (Hanya PDF, JPG, PNG).'
                ]
            ];
        }

        if (!$this->validate($rules)) {
            $data['upload_error'] = $this->session->getFlashdata('upload_error_detail'); // Ambil flashdata error upload
            echo view('templates/header', $data);
            echo view('templates/sidebar', $data);
            echo view('templates/topbar', $data);
            echo view('user/edit-profile', $data);
            echo view('templates/footer', $data);
        } else {
            // Handle TTD upload
            $nama_file_ttd = $this->_handle_file_upload('ttd', [
                'upload_path' => 'uploads/ttd/',
                'allowed_types' => 'jpg|png|jpeg|pdf',
                'max_size' => 1024,
                'label' => 'Tanda Tangan PIC'
            ], $data['user_perusahaan']['ttd'] ?? null);
            if ($nama_file_ttd === false) { return redirect()->to(base_url('user/edit')); }

            // Handle Profile Image upload
            $nama_file_profile_image = $this->_handle_file_upload('profile_image', [
                'upload_path' => 'uploads/profile_images/',
                'allowed_types' => 'jpg|png|jpeg|gif',
                'max_size' => 1024,
                'max_width' => 1024,
                'max_height' => 1024,
                'label' => 'Gambar Profil/Logo'
            ], $data['user']['image'] ?? 'default.jpg');
            if ($nama_file_profile_image === false) { return redirect()->to(base_url('user/edit')); }

            // Handle File SKEP Fasilitas upload
            $nama_file_skep_fasilitas = $this->_handle_file_upload('file_skep_fasilitas', [
                'upload_path' => 'uploads/skep_fasilitas/',
                'allowed_types' => 'pdf|jpg|jpeg|png',
                'max_size' => 2048,
                'label' => 'File SKEP Fasilitas'
            ], $data['user_perusahaan']['FileSkepFasilitas'] ?? null);
            if ($nama_file_skep_fasilitas === false) { return redirect()->to(base_url('user/edit')); }

            // Handle Initial SKEP File upload (only for activating)
            $nama_file_initial_skep = null;
            if ($is_activating) {
                $nama_file_initial_skep = $this->_handle_file_upload('initial_skep_file', [
                    'upload_path' => 'uploads/skep_awal_user/',
                    'allowed_types' => 'pdf|jpg|jpeg|png',
                    'max_size' => 2048,
                    'label' => 'File SKEP Kuota Awal'
                ]);
                if ($nama_file_initial_skep === false) { return redirect()->to(base_url('user/edit')); }
            }


            // Update user table for profile image
            $data_user_update = [];
            if ($nama_file_profile_image !== null && $nama_file_profile_image != $data['user']['image']) {
                $data_user_update['image'] = $nama_file_profile_image;
            }
            if (!empty($data_user_update)) {
                 $this->db->table('user')->where('id', $id_user_login)->update($data_user_update);
                 if(isset($data_user_update['image'])) $this->session->set('user_image', $data_user_update['image']);
            }

            // Prepare company data
            $data_perusahaan = [
                'NamaPers' => $this->request->getPost('NamaPers'),
                'npwp' => $this->request->getPost('npwp'),
                'alamat' => $this->request->getPost('alamat'),
                'telp' => $this->request->getPost('telp'),
                'pic' => $this->request->getPost('pic'),
                'jabatanPic' => $this->request->getPost('jabatanPic'),
                'NoSkepFasilitas' => $this->request->getPost('NoSkepFasilitas') ?: null,
            ];
             if ($nama_file_ttd !== null) {
                 $data_perusahaan['ttd'] = $nama_file_ttd;
             }
             if ($nama_file_skep_fasilitas !== null) {
                 $data_perusahaan['FileSkepFasilitas'] = $nama_file_skep_fasilitas;
             }

            if ($is_activating) {
                $data_perusahaan['id_pers'] = $id_user_login;
                $this->db->table('user_perusahaan')->insert($data_perusahaan);

                $initial_skep_no = trim($this->request->getPost('initial_skep_no'));
                $initial_nama_barang = trim($this->request->getPost('initial_nama_barang'));
                $initial_kuota_jumlah = (float)$this->request->getPost('initial_kuota_jumlah');
                $initial_skep_tgl = $this->request->getPost('initial_skep_tgl');


                if (!empty($initial_skep_no) && !empty($initial_nama_barang) && $initial_kuota_jumlah > 0) {
                    $data_kuota_awal_barang = [
                        'id_pers' => $id_user_login,
                        'nama_barang' => $initial_nama_barang,
                        'initial_quota_barang' => $initial_kuota_jumlah,
                        'remaining_quota_barang' => $initial_kuota_jumlah,
                        'nomor_skep_asal' => $initial_skep_no,
                        'tanggal_skep_asal' => $initial_skep_tgl,
                        'status_kuota_barang' => 'active',
                        'dicatat_oleh_user_id' => $id_user_login,
                        'waktu_pencatatan' => date('Y-m-d H:i:s')
                    ];
                    if ($nama_file_initial_skep) {
                        $data_kuota_awal_barang['file_skep_asal'] = $nama_file_initial_skep;
                    }
                    $this->db->table('user_kuota_barang')->insert($data_kuota_awal_barang);
                    log_message('info', 'KUOTA AWAL BARANG dicatat saat aktivasi untuk user: ' . $id_user_login . ', barang: ' . $initial_nama_barang . ', jumlah: ' . $initial_kuota_jumlah);
                }

                $this->db->table('user')->where('id', $id_user_login)->update(['is_active' => 1]);
                $this->session->set('is_active', 1);
                $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Profil perusahaan berhasil disimpan dan akun Anda telah diaktifkan! Anda sekarang dapat mengajukan kuota atau membuat permohonan.</div>');
                return redirect()->to(base_url('user/index'));
            } else {
                $this->db->table('user_perusahaan')->where('id_pers', $id_user_login)->update($data_perusahaan);

                // Check for changes (simplified, as files handled separately)
                $perubahan_terdeteksi = false;
                if (!empty($data_user_update)) $perubahan_terdeteksi = true;

                // Compare main data_perusahaan fields with existing
                foreach ($data_perusahaan as $key => $value) {
                    if (($data['user_perusahaan'][$key] ?? null) !== $value) {
                        $perubahan_terdeteksi = true;
                        break;
                    }
                }

                 if ($perubahan_terdeteksi) {
                     $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Profil dan data perusahaan berhasil diperbarui!</div>');
                 } else {
                     $this->session->setFlashdata('message', '<div class="alert alert-info" role="alert">Tidak ada perubahan data yang terdeteksi.</div>');
                 }
                return redirect()->to(base_url('user/index'));
            }
        }
    }

    // Helper untuk konfigurasi upload (pindahkan ke BaseController jika sering digunakan)
    private function _get_upload_config($upload_path, $allowed_types, $max_size_kb, $max_width = null, $max_height = null)
    {
        $full_upload_path = FCPATH . $upload_path;
        if (!is_dir($full_upload_path)) {
            if (!@mkdir($full_upload_path, 0777, true)) {
                log_message('error', 'Gagal membuat direktori upload: ' . $full_upload_path);
                return false;
            }
        }
        if (!is_writable($full_upload_path)) {
            log_message('error', 'Direktori upload tidak writable: ' . $full_upload_path);
            return false;
        }

        // CI4 doesn't use this config array directly for its internal upload mechanism.
        // It's more for logical reference if you're manually processing.
        // The rules array in $this->validate() handles most of this.
        $config = [
            'upload_path'   => $full_upload_path,
            'allowed_types' => $allowed_types,
            'max_size'      => $max_size_kb,
            // 'max_width'     => $max_width, // Not used directly in CI4 File upload rules this way
            // 'max_height'    => $max_height, // Not used directly in CI4 File upload rules this way
            'encrypt_name'  => true,
        ];
        return $config;
    }

    // Mengganti _get_upload_config dengan metode umum untuk penanganan upload
    private function _handle_file_upload($fieldName, array $uploadConfig, $existingFile = null)
    {
        $file = $this->request->getFile($fieldName);

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $fullUploadPath = FCPATH . $uploadConfig['upload_path'];

            if (!is_dir($fullUploadPath)) {
                if (!@mkdir($fullUploadPath, 0777, true)) {
                    log_message('error', 'Gagal membuat direktori upload: ' . $fullUploadPath);
                    $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Gagal membuat direktori upload untuk ' . ($uploadConfig['label'] ?? $fieldName) . '.</div>');
                    return false;
                }
            }
            if (!is_writable($fullUploadPath)) {
                log_message('error', 'Direktori upload tidak writable: ' . $fullUploadPath);
                $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Direktori upload tidak writable untuk ' . ($uploadConfig['label'] ?? $fieldName) . '.</div>');
                return false;
            }

            $newName = $file->getRandomName();
            try {
                $file->move($fullUploadPath, $newName);
                if ($existingFile && file_exists($fullUploadPath . $existingFile) && $existingFile != 'default.jpg') {
                    @unlink($fullUploadPath . $existingFile);
                }
                return $newName;
            } catch (\Exception $e) {
                log_message('error', 'File upload error for ' . $fieldName . ': ' . $e->getMessage());
                $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Gagal mengunggah file ' . ($uploadConfig['label'] ?? $fieldName) . ': ' . $e->getMessage() . '</div>');
                return false;
            }
        }
        return $existingFile; // Return existing file name if no new file uploaded
    }

    public function permohonan_impor_kembali()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Permohonan Impor Kembali';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        $id_user_login = $data['user']['id'];

        $data['user_perusahaan'] = $this->db->table('user_perusahaan')->where('id_pers', $id_user_login)->get()->getRowArray();

        if (empty($data['user_perusahaan']) || ($data['user']['is_active'] ?? null) == 0) { // Menggunakan null coalescing
             $this->session->setFlashdata('message', '<div class="alert alert-warning" role="alert">Mohon lengkapi profil perusahaan Anda dan pastikan akun aktif sebelum membuat permohonan.</div>');
             return redirect()->to(base_url('user/edit'));
        }

        $builder = $this->db->table('user_kuota_barang');
        $builder->select('id_kuota_barang, nama_barang, remaining_quota_barang, nomor_skep_asal, tanggal_skep_asal');
        $builder->where('id_pers', $id_user_login);
        $builder->where('remaining_quota_barang >', 0);
        $builder->where('status_kuota_barang', 'active');
        $builder->orderBy('nama_barang ASC, tanggal_skep_asal DESC');
        $data['list_barang_berkuota'] = $builder->get()->getResultArray();

        log_message('debug', 'PERMOHONAN BARU - ID User: ' . $id_user_login . ', Data User Perusahaan: ' . print_r($data['user_perusahaan'], true));
        log_message('debug', 'PERMOHONAN BARU - Query List Barang Berkuota: ' . $this->db->getLastQuery()->getQueryString());
        log_message('debug', 'PERMOHONAN BARU - Data List Barang Berkuota: ' . print_r($data['list_barang_berkuota'], true));

        $rules = [
            'nomorSurat' => 'trim|required|max_length[100]',
            'TglSurat' => 'trim|required',
            'Perihal' => 'trim|required|max_length[255]',
            'id_kuota_barang_selected' => 'trim|required|numeric',
            'NamaBarang' => 'trim|required',
            'JumlahBarang' => 'trim|required|numeric|greater_than[0]|max_length[10]',
            'NegaraAsal' => 'trim|required|max_length[100]',
            'NamaKapal' => 'trim|required|max_length[100]',
            'noVoyage' => 'trim|required|max_length[50]',
            'TglKedatangan' => 'trim|required',
            'TglBongkar' => 'trim|required',
            'lokasi' => 'trim|required|max_length[100]',
            'file_bc_manifest' => [
                'label' => 'File BC 1.1 / Manifest',
                'rules' => 'uploaded[file_bc_manifest]|max_size[file_bc_manifest,2048]|ext_in[file_bc_manifest,pdf]',
                'errors' => [
                    'uploaded' => '{field} wajib diisi.',
                    'max_size' => 'Ukuran file {field} melebihi 2MB.',
                    'ext_in' => 'Tipe file {field} tidak diizinkan (Hanya PDF).'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            log_message('debug', 'PERMOHONAN BARU - Validasi form GAGAL. Errors: ' . json_encode($this->validator->getErrors()));
            if (empty($data['list_barang_berkuota']) && $this->request->getMethod() !== 'post') { // Menggunakan getMethod()
                 $this->session->setFlashdata('message_form_permohonan', '<div class="alert alert-warning" role="alert">Anda tidak memiliki kuota aktif untuk barang apapun saat ini. Tidak dapat membuat permohonan impor kembali. Silakan ajukan kuota terlebih dahulu.</div>');
            }
            echo view('templates/header', $data);
            echo view('templates/sidebar', $data);
            echo view('templates/topbar', $data);
            echo view('user/permohonan_impor_kembali_form', $data);
            echo view('templates/footer', $data);
        } else {
            log_message('debug', 'PERMOHONAN BARU - Validasi form SUKSES. Memulai proses data.');
            $id_kuota_barang_dipilih = (int)$this->request->getPost('id_kuota_barang_selected');
            $nama_barang_input_form = $this->request->getPost('NamaBarang');
            $jumlah_barang_dimohon = (float)$this->request->getPost('JumlahBarang');

            log_message('debug', 'PERMOHONAN BARU - POST Data: id_kuota_barang=' . $id_kuota_barang_dipilih . ', nama_barang_form=' . $nama_barang_input_form . ', jumlah_dimohon=' . $jumlah_barang_dimohon);

            $kuota_valid_db = $this->db->table('user_kuota_barang')->where([
                'id_kuota_barang' => $id_kuota_barang_dipilih,
                'id_pers' => $id_user_login,
                'status_kuota_barang' => 'active'
            ])->get()->getRowArray();

            if (!$kuota_valid_db) {
                log_message('error', 'PERMOHONAN BARU - Kuota barang (ID: '.$id_kuota_barang_dipilih.') tidak ditemukan/tidak aktif untuk user ID: ' . $id_user_login);
                $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Kuota barang yang dipilih tidak valid atau tidak aktif. Silakan pilih kembali.</div>');
                return redirect()->to(base_url('user/permohonan_impor_kembali'));
            }
            if (($kuota_valid_db['nama_barang'] ?? '') != $nama_barang_input_form) { // Menggunakan null coalescing
                log_message('error', 'PERMOHONAN BARU - Nama barang form ('.$nama_barang_input_form.') != nama barang DB ('.$kuota_valid_db['nama_barang'].') untuk ID kuota: '.$id_kuota_barang_dipilih);
                $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Terjadi ketidaksesuaian data barang. Silakan coba lagi.</div>');
                return redirect()->to(base_url('user/permohonan_impor_kembali'));
            }
            if ($jumlah_barang_dimohon > (float)($kuota_valid_db['remaining_quota_barang'] ?? 0)) { // Menggunakan null coalescing
                log_message('error', 'PERMOHONAN BARU - Jumlah dimohon ('.$jumlah_barang_dimohon.') > sisa kuota ('.$kuota_valid_db['remaining_quota_barang'].') barang: '.$nama_barang_input_form);
                $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Jumlah barang (' . $jumlah_barang_dimohon . ') melebihi sisa kuota (' . (float)($kuota_valid_db['remaining_quota_barang'] ?? 0) . ') untuk ' . htmlspecialchars($nama_barang_input_form) . '.</div>');
                return redirect()->to(base_url('user/permohonan_impor_kembali'));
            }
            $nomor_skep_final = $kuota_valid_db['nomor_skep_asal'];

            log_message('debug', 'PERMOHONAN BARU - Memulai blok upload. getFile(file_bc_manifest): ' . ($this->request->getFile('file_bc_manifest')->getName() ?? 'TIDAK ADA FILE'));
            $nama_file_bc_manifest = null;

            $upload_dir_bc = FCPATH . 'uploads/bc_manifest/';
            $uploadConfigBC = [
                'upload_path' => $upload_dir_bc,
                'allowed_types' => 'pdf',
                'max_size' => 2048,
                'label' => 'File BC 1.1 / Manifest'
            ];

            $uploadedBCFile = $this->_handle_file_upload('file_bc_manifest', $uploadConfigBC);
            if ($uploadedBCFile === false) {
                 return redirect()->to(base_url('user/permohonan_impor_kembali')); // Error sudah diset di _handle_file_upload
            }
            $nama_file_bc_manifest = $uploadedBCFile;
            log_message('info', 'PERMOHONAN BARU - UPLOAD BC MANIFEST SUKSES: ' . ($nama_file_bc_manifest ?? 'NULL'));


            log_message('debug', 'PERMOHONAN BARU - Nilai $nama_file_bc_manifest sebelum insert: ' . ($nama_file_bc_manifest ?? 'NULL (INI MASALAH JIKA FILE DIUPLOAD)'));

            $data_insert = [
                'NamaPers'      => $data['user_perusahaan']['NamaPers'],
                'alamat'        => $data['user_perusahaan']['alamat'],
                'nomorSurat'    => $this->request->getPost('nomorSurat'),
                'TglSurat'      => $this->request->getPost('TglSurat'),
                'Perihal'       => $this->request->getPost('Perihal'),
                'NamaBarang'    => $nama_barang_input_form,
                'JumlahBarang'  => $jumlah_barang_dimohon,
                'NegaraAsal'    => $this->request->getPost('NegaraAsal'),
                'NamaKapal'     => $this->request->getPost('NamaKapal'),
                'noVoyage'      => $this->request->getPost('noVoyage'),
                'NoSkep'        => $nomor_skep_final,
                'file_bc_manifest' => $nama_file_bc_manifest,
                'id_kuota_barang_digunakan' => $id_kuota_barang_dipilih,
                'TglKedatangan' => $this->request->getPost('TglKedatangan'),
                'TglBongkar'    => $this->request->getPost('TglBongkar'),
                'lokasi'        => $this->request->getPost('lokasi'),
                'id_pers'       => $id_user_login,
                'time_stamp'    => date('Y-m-d H:i:s'),
                'status'        => '0'
            ];

            log_message('debug', 'PERMOHONAN BARU - Data yang akan diinsert ke user_permohonan: ' . print_r($data_insert, true));

            try {
                $this->db->table('user_permohonan')->insert($data_insert);
                $id_permohonan_baru = $this->db->insertID();
                log_message('info', 'PERMOHONAN BARU - BERHASIL insert. ID Permohonan Baru: ' . $id_permohonan_baru . '. File BC Manifest Tersimpan: ' . ($nama_file_bc_manifest ?? 'TIDAK ADA FILE TERSIMPAN'));
                $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Permohonan Impor Kembali untuk barang "'.htmlspecialchars($nama_barang_input_form).'" telah berhasil diajukan.</div>');
                return redirect()->to(base_url('user/daftarPermohonan'));
            } catch (\Exception $e) {
                log_message('error', 'PERMOHONAN BARU - GAGAL insert ke database. Error: ' . $e->getMessage() . ' Data: ' . print_r($data_insert, true));
                $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Gagal menyimpan permohonan ke database. Error: ' . $e->getMessage() . '</div>');

                // Reload data for form if submission fails
                $builder = $this->db->table('user_kuota_barang');
                $builder->select('id_kuota_barang, nama_barang, remaining_quota_barang, nomor_skep_asal, tanggal_skep_asal');
                $builder->where('id_pers', $id_user_login);
                $builder->where('remaining_quota_barang >', 0);
                $builder->where('status_kuota_barang', 'active');
                $builder->orderBy('nama_barang ASC, tanggal_skep_asal DESC');
                $data['list_barang_berkuota'] = $builder->get()->getResultArray();

                echo view('templates/header', $data);
                echo view('templates/sidebar', $data);
                echo view('templates/topbar', $data);
                echo view('user/permohonan_impor_kembali_form', $data);
                echo view('templates/footer', $data);
                return;
            }
        }
    }

    public function pengajuan_kuota()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Pengajuan Penetapan/Penambahan Kuota';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        $id_user_login = $data['user']['id'];

        $data['user_perusahaan'] = $this->db->table('user_perusahaan')->where('id_pers', $id_user_login)->get()->getRowArray();

        if (empty($data['user_perusahaan'])) {
            $this->session->setFlashdata('message', '<div class="alert alert-warning" role="alert">Mohon lengkapi profil perusahaan Anda terlebih dahulu di menu "Edit Profil & Perusahaan" sebelum mengajukan kuota.</div>');
            return redirect()->to(base_url('user/edit'));
        }
        if (($data['user']['is_active'] ?? null) == 0) { // Menggunakan null coalescing
            $this->session->setFlashdata('message', '<div class="alert alert-warning" role="alert">Akun Anda belum aktif. Tidak dapat mengajukan kuota. Mohon lengkapi profil perusahaan Anda jika belum, atau hubungi Administrator.</div>');
            return redirect()->to(base_url('user/edit'));
        }

        $builder = $this->db->table('user_kuota_barang');
        $builder->selectSum('initial_quota_barang', 'total_initial_kuota_all_barang');
        $builder->selectSum('remaining_quota_barang', 'total_remaining_kuota_all_barang');
        $builder->where('id_pers', $id_user_login);
        $agregat_kuota = $builder->get()->getRowArray();
        $data['total_kuota_awal_semua_barang'] = (float)($agregat_kuota['total_initial_kuota_all_barang'] ?? 0);
        $data['total_sisa_kuota_semua_barang'] = (float)($agregat_kuota['total_remaining_kuota_all_barang'] ?? 0);

        $rules = [
            'nomor_surat_pengajuan' => 'trim|required|max_length[100]',
            'tanggal_surat_pengajuan' => 'trim|required',
            'perihal_pengajuan' => 'trim|required|max_length[255]',
            'nama_barang_kuota' => 'trim|required|max_length[255]',
            'requested_quota' => 'trim|required|numeric|greater_than[0]|max_length[10]',
            'reason' => 'trim|required',
        ];

        // Validasi untuk file lampiran
        if ($this->request->getFile('file_lampiran_pengajuan')->isValid()) {
            $rules['file_lampiran_pengajuan'] = [
                'label' => 'File Lampiran Pengajuan',
                'rules' => 'max_size[file_lampiran_pengajuan,2048]|ext_in[file_lampiran_pengajuan,pdf,doc,docx,jpg,jpeg,png]',
                'errors' => [
                    'max_size' => 'Ukuran file {field} melebihi 2MB.',
                    'ext_in' => 'Tipe file {field} tidak diizinkan (Hanya PDF, DOC, DOCX, JPG, JPEG, PNG).'
                ]
            ];
        }


        if (!$this->validate($rules)) {
            echo view('templates/header', $data);
            echo view('templates/sidebar', $data);
            echo view('templates/topbar', $data);
            echo view('user/pengajuan_kuota_form', $data);
            echo view('templates/footer');
        } else {
            $nama_file_lampiran = null;
            $upload_dir_lampiran = FCPATH . 'uploads/lampiran_kuota/';
            $uploadConfigLampiran = [
                'upload_path' => $upload_dir_lampiran,
                'allowed_types' => 'pdf|doc|docx|jpg|jpeg|png',
                'max_size' => 2048,
                'label' => 'File Lampiran Pengajuan'
            ];

            $uploadedLampiran = $this->_handle_file_upload('file_lampiran_pengajuan', $uploadConfigLampiran);
            if ($uploadedLampiran === false) {
                return redirect()->to(base_url('user/pengajuan_kuota'));
            }
            $nama_file_lampiran = $uploadedLampiran;


            $data_pengajuan = [
                'id_pers'                   => $id_user_login,
                'nomor_surat_pengajuan'     => $this->request->getPost('nomor_surat_pengajuan'),
                'tanggal_surat_pengajuan'   => $this->request->getPost('tanggal_surat_pengajuan'),
                'perihal_pengajuan'         => $this->request->getPost('perihal_pengajuan'),
                'nama_barang_kuota'         => $this->request->getPost('nama_barang_kuota'),
                'requested_quota'           => (float)$this->request->getPost('requested_quota'),
                'reason'                    => $this->request->getPost('reason'),
                'submission_date'           => date('Y-m-d H:i:s'),
                'status'                    => 'pending'
            ];

            if ($nama_file_lampiran !== null) {
                $data_pengajuan['file_lampiran_user'] = $nama_file_lampiran;
            }

            $this->db->table('user_pengajuan_kuota')->insert($data_pengajuan);

            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Pengajuan kuota Anda untuk barang "'.htmlspecialchars($this->request->getPost('nama_barang_kuota')).'" telah berhasil dikirim dan akan diproses.</div>');
            return redirect()->to(base_url('user/daftar_pengajuan_kuota'));
        }
    }

    public function daftar_pengajuan_kuota()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Daftar Pengajuan Kuota Saya';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        $id_user_login = $data['user']['id'];

        $builder = $this->db->table('user_pengajuan_kuota');
        $builder->select('id, nomor_surat_pengajuan, tanggal_surat_pengajuan, perihal_pengajuan, nama_barang_kuota, requested_quota, status, submission_date, processed_date, admin_notes, nomor_sk_petugas, file_sk_petugas, approved_quota');
        $builder->where('id_pers', $id_user_login);
        $builder->orderBy('submission_date', 'DESC');
        $data['daftar_pengajuan'] = $builder->get()->getResultArray();

        log_message('debug', 'USER DAFTAR PENGAJUAN KUOTA - User ID: ' . $id_user_login);
        log_message('debug', 'USER DAFTAR PENGAJUAN KUOTA - Query: ' . $this->db->getLastQuery()->getQueryString());
        log_message('debug', 'USER DAFTAR PENGAJUAN KUOTA - Jumlah Data: ' . count($data['daftar_pengajuan']));

        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('templates/topbar', $data);
        echo view('user/daftar_pengajuan_kuota_view', $data);
        echo view('templates/footer', $data);
    }

    public function print_bukti_pengajuan_kuota($id_pengajuan = 0)
    {
        if ($id_pengajuan == 0 || !is_numeric($id_pengajuan)) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">ID Pengajuan Kuota tidak valid.</div>');
            return redirect()->to(base_url('user/daftar_pengajuan_kuota')); // Mengubah redirect
        }
        $user_login = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        if (!$user_login) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Sesi tidak valid. Silakan login kembali.</div>');
            return redirect()->to(base_url('auth'));
        }
        $data['user'] = $user_login;
        $builder = $this->db->table('user_pengajuan_kuota upk');
        $builder->select('upk.*, upr.NamaPers, upr.alamat as alamat_perusahaan, upr.npwp as npwp_perusahaan, upr.pic, upr.jabatanPic, upr.ttd as file_ttd_pic, upr.telp as telp_perusahaan');
        $builder->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $builder->where('upk.id', $id_pengajuan);
        $builder->where('upk.id_pers', $user_login['id']);
        $pengajuan = $builder->get()->getRowArray();

        if (!$pengajuan) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Data pengajuan kuota tidak ditemukan atau Anda tidak berhak mengaksesnya.</div>');
            return redirect()->to(base_url('user/daftar_pengajuan_kuota')); // Mengubah redirect
        }
        $data['pengajuan'] = $pengajuan;
        $data['user_perusahaan'] = [
            'NamaPers'   => $pengajuan['NamaPers'] ?? null,
            'alamat'     => $pengajuan['alamat_perusahaan'] ?? null,
            'npwp'       => $pengajuan['npwp_perusahaan'] ?? null,
            'telp'       => $pengajuan['telp_perusahaan'] ?? null,
            'pic'        => $pengajuan['pic'] ?? null,
            'jabatanPic' => $pengajuan['jabatanPic'] ?? null,
            'ttd'        => $pengajuan['file_ttd_pic'] ?? null
        ];

        log_message('debug', 'PRINT PENGAJUAN KUOTA - Data User Login: ' . print_r($data['user'], true));
        log_message('debug', 'PRINT PENGAJUAN KUOTA - Data Pengajuan: ' . print_r($data['pengajuan'], true));
        log_message('debug', 'PRINT PENGAJUAN KUOTA - Data User Perusahaan (untuk view): ' . print_r($data['user_perusahaan'], true));

        echo view('user/FormPengajuanKuota_print', $data);
    }

    public function daftarPermohonan()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Daftar Permohonan Impor Kembali';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();

        $builder = $this->db->table('user_permohonan up');
        $builder->select(
            'up.*, ' .
            'p.Nama AS nama_petugas_pemeriksa, ' .
            'up.NamaBarang, ' .
            'lhp.JumlahBenar'
        );
        $builder->join('petugas p', 'up.petugas = p.id', 'left');
        $builder->join('lhp', 'lhp.id_permohonan = up.id', 'left');
        $builder->where('up.id_pers', $data['user']['id']);
        $builder->orderBy('up.time_stamp', 'DESC');
        $data['permohonan'] = $builder->get()->getResultArray();
        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('templates/topbar', $data);
        echo view('user/daftar-permohonan', $data);
        echo view('templates/footer');
    }

    public function detailPermohonan($id_permohonan = 0)
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Detail Permohonan Impor Kembali';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        $user_id = $data['user']['id'];

        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid.</div>');
            return redirect()->to(base_url('user/daftarPermohonan'));
        }

        $builder = $this->db->table('user_permohonan up');
        $builder->select(
            'up.*, ' .
            'upr.NamaPers, upr.npwp AS npwp_perusahaan, upr.alamat AS alamat_perusahaan, upr.NoSkep AS NoSkep_perusahaan, ' .
            'petugas_pemeriksa.Nama AS nama_petugas_pemeriksa'
        );
        $builder->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $builder->join('petugas petugas_pemeriksa', 'up.petugas = petugas_pemeriksa.id', 'left');
        $builder->where('up.id', $id_permohonan);
        $builder->where('up.id_pers', $user_id);
        $data['permohonan_detail'] = $builder->get()->getRowArray();

        if (!$data['permohonan_detail']) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Data permohonan tidak ditemukan atau Anda tidak memiliki akses. ID: ' . htmlspecialchars($id_permohonan) . '</div>');
            return redirect()->to(base_url('user/daftarPermohonan'));
        }
        $data['lhp_detail'] = $this->db->table('lhp')->where('id_permohonan', $id_permohonan)->get()->getRowArray();

        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('templates/topbar', $data);
        echo view('user/detail_permohonan_view', $data);
        echo view('templates/footer');
    }

    public function printPdf($id_permohonan)
    {
        if (empty($id_permohonan) || !is_numeric($id_permohonan)) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid.</div>');
            return redirect()->to(base_url('user/daftarPermohonan'));
        }

        $user_login = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        if (!$user_login) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Sesi tidak valid. Silakan login kembali.</div>');
            return redirect()->to(base_url('auth'));
        }
        $data['user'] = $user_login;

        $builder = $this->db->table('user_permohonan up');
        $builder->select('up.*, upr.NamaPers, upr.alamat as alamat_perusahaan, upr.npwp as npwp_perusahaan, upr.pic, upr.jabatanPic, upr.ttd as file_ttd_pic_perusahaan, upr.telp as telp_perusahaan');
        $builder->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $builder->where('up.id', $id_permohonan);
        $builder->where('up.id_pers', $user_login['id']);
        $permohonan_data_lengkap = $builder->get()->getRowArray();

        if (!$permohonan_data_lengkap) {
             $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Permohonan tidak ditemukan atau Anda tidak berhak mengaksesnya.</div>');
             return redirect()->to(base_url('user/daftarPermohonan'));
        }

        $data['permohonan'] = $permohonan_data_lengkap;
        $data['user_perusahaan'] = [
            'NamaPers'   => $permohonan_data_lengkap['NamaPers'] ?? null,
            'alamat'     => $permohonan_data_lengkap['alamat_perusahaan'] ?? null,
            'npwp'       => $permohonan_data_lengkap['npwp_perusahaan'] ?? null,
            'telp'       => $permohonan_data_lengkap['telp_perusahaan'] ?? null,
            'pic'        => $permohonan_data_lengkap['pic'] ?? null,
            'jabatanPic' => $permohonan_data_lengkap['jabatanPic'] ?? null,
            'ttd'        => $permohonan_data_lengkap['file_ttd_pic_perusahaan'] ?? null
        ];

        log_message('debug', 'PRINT PDF PERMOHONAN - Data User Login: ' . print_r($data['user'], true));
        log_message('debug', 'PRINT PDF PERMOHONAN - Data Permohonan Lengkap (termasuk NoSkep dari permohonan): ' . print_r($data['permohonan'], true));
        log_message('debug', 'PRINT PDF PERMOHONAN - Data User Perusahaan (untuk kop/ttd): ' . print_r($data['user_perusahaan'], true));

        echo view('user/FormPermohonan', $data);
    }

    public function editpermohonan($id_permohonan = 0)
    {
        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid.</div>');
            return redirect()->to(base_url('user/daftarPermohonan'));
        }

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Edit Permohonan Impor Kembali';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        $id_user_login = $data['user']['id'];

        $permohonan = $this->db->table('user_permohonan')->where(['id' => $id_permohonan, 'id_pers' => $id_user_login])->get()->getRowArray();

        if (!$permohonan) {
             $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Permohonan tidak ditemukan atau Anda tidak berhak mengeditnya.</div>');
             return redirect()->to(base_url('user/daftarPermohonan'));
        }

        if (($permohonan['status'] ?? null) != '0') { // Menggunakan null coalescing
            $this->session->setFlashdata('message', '<div class="alert alert-warning" role="alert">Permohonan ini sudah diproses (Status: '.htmlspecialchars($permohonan['status']).') dan tidak dapat diedit lagi.</div>');
            return redirect()->to(base_url('user/daftarPermohonan'));
        }
        $data['permohonan_edit'] = $permohonan;

        $data['user_perusahaan'] = $this->db->table('user_perusahaan')->where('id_pers', $id_user_login)->get()->getRowArray();

        $builder = $this->db->table('user_kuota_barang');
        $builder->select('id_kuota_barang, nama_barang, remaining_quota_barang, nomor_skep_asal');
        $builder->where('id_pers', $id_user_login);

        $builder->groupStart();
        $builder->where('remaining_quota_barang >', 0);
        if (isset($permohonan['id_kuota_barang_digunakan'])) {
            $builder->orWhere('id_kuota_barang', $permohonan['id_kuota_barang_digunakan']);
        }
        $builder->groupEnd();
        $builder->where('status_kuota_barang', 'active');
        $builder->orderBy('nama_barang', 'ASC');
        $data['list_barang_berkuota'] = $builder->get()->getResultArray();
        log_message('debug', 'USER EDIT PERMOHONAN - List Barang Berkuota: ' . print_r($data['list_barang_berkuota'], true));

        $rules = [
            'nomorSurat' => 'trim|required|max_length[100]',
            'TglSurat' => 'trim|required',
            'Perihal' => 'trim|required|max_length[255]',
            'id_kuota_barang_selected' => 'trim|required|numeric',
            'NamaBarang' => 'trim|required',
            'JumlahBarang' => 'trim|required|numeric|greater_than[0]|max_length[10]',
            'NegaraAsal' => 'trim|required|max_length[100]',
            'NamaKapal' => 'trim|required|max_length[100]',
            'noVoyage' => 'trim|required|max_length[50]',
            'TglKedatangan' => 'trim|required',
            'TglBongkar' => 'trim|required',
            'lokasi' => 'trim|required|max_length[100]',
        ];

        // Validasi untuk file BC Manifest (opsional, jika ada upload baru)
        if ($this->request->getFile('file_bc_manifest_edit')->isValid()) {
            $rules['file_bc_manifest_edit'] = [
                'label' => 'File BC 1.1 / Manifest (Baru)',
                'rules' => 'max_size[file_bc_manifest_edit,2048]|ext_in[file_bc_manifest_edit,pdf]',
                'errors' => [
                    'max_size' => 'Ukuran file {field} melebihi 2MB.',
                    'ext_in' => 'Tipe file {field} tidak diizinkan (Hanya PDF).'
                ]
            ];
        }


        if (!$this->validate($rules)) {
            $data['id_permohonan_form_action'] = $id_permohonan;
            echo view('templates/header', $data);
            echo view('templates/sidebar', $data);
            echo view('templates/topbar', $data);
            echo view('user/edit_permohonan_form', $data);
            echo view('templates/footer');
        } else {
            $id_kuota_barang_dipilih = (int)$this->request->getPost('id_kuota_barang_selected');
            $nama_barang_input_form = $this->request->getPost('NamaBarang');
            $jumlah_barang_dimohon = (float)$this->request->getPost('JumlahBarang');

            $kuota_valid_db = $this->db->table('user_kuota_barang')->where([
                'id_kuota_barang' => $id_kuota_barang_dipilih,
                'id_pers' => $id_user_login,
                'status_kuota_barang' => 'active'
            ])->get()->getRowArray();

            if (!$kuota_valid_db) {
                $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Kuota barang yang dipilih tidak valid atau tidak aktif.</div>');
                return redirect()->to(base_url('user/editpermohonan/' . $id_permohonan));
            }
            if (($kuota_valid_db['nama_barang'] ?? '') != $nama_barang_input_form) { // Menggunakan null coalescing
                $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Terjadi ketidaksesuaian data barang. Silakan coba lagi.</div>');
                return redirect()->to(base_url('user/editpermohonan/' . $id_permohonan));
            }

            $sisa_kuota_efektif_untuk_validasi = (float)($kuota_valid_db['remaining_quota_barang'] ?? 0); // Menggunakan null coalescing
            if (($permohonan['id_kuota_barang_digunakan'] ?? null) == $id_kuota_barang_dipilih) { // Menggunakan null coalescing
                $sisa_kuota_efektif_untuk_validasi += (float)($permohonan['JumlahBarang'] ?? 0); // Menggunakan null coalescing
            }

            if ($jumlah_barang_dimohon > $sisa_kuota_efektif_untuk_validasi) {
                $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Jumlah barang yang Anda ajukan (' . $jumlah_barang_dimohon . ' unit) melebihi sisa kuota yang tersedia (efektif: ' . $sisa_kuota_efektif_untuk_validasi . ' unit) untuk ' . htmlspecialchars($nama_barang_input_form) . '.</div>');
                return redirect()->to(base_url('user/editpermohonan/' . $id_permohonan));
            }
            $nomor_skep_final = $kuota_valid_db['nomor_skep_asal'];

            $nama_file_bc_manifest_update = $permohonan['file_bc_manifest'];
            $upload_dir_bc = FCPATH . 'uploads/bc_manifest/';
            $uploadConfigBC = [
                'upload_path' => $upload_dir_bc,
                'allowed_types' => 'pdf',
                'max_size' => 2048,
                'label' => 'File BC 1.1 / Manifest'
            ];

            if ($this->request->getFile('file_bc_manifest_edit')->isValid()) {
                $uploadedBCFile = $this->_handle_file_upload('file_bc_manifest_edit', $uploadConfigBC, $nama_file_bc_manifest_update);
                if ($uploadedBCFile === false) {
                    echo view('templates/header', $data);
                    echo view('templates/sidebar', $data);
                    echo view('templates/topbar', $data);
                    echo view('user/edit_permohonan_form', $data);
                    echo view('templates/footer');
                    return;
                }
                $nama_file_bc_manifest_update = $uploadedBCFile;
            }

            $data_update = [
                'nomorSurat'    => $this->request->getPost('nomorSurat'),
                'TglSurat'      => $this->request->getPost('TglSurat'),
                'Perihal'       => $this->request->getPost('Perihal'),
                'NamaBarang'    => $nama_barang_input_form,
                'JumlahBarang'  => $jumlah_barang_dimohon,
                'NegaraAsal'    => $this->request->getPost('NegaraAsal'),
                'NamaKapal'     => $this->request->getPost('NamaKapal'),
                'noVoyage'      => $this->request->getPost('noVoyage'),
                'NoSkep'        => $nomor_skep_final,
                'id_kuota_barang_digunakan' => $id_kuota_barang_dipilih,
                'TglKedatangan' => $this->request->getPost('TglKedatangan'),
                'TglBongkar'    => $this->request->getPost('TglBongkar'),
                'lokasi'        => $this->request->getPost('lokasi'),
                'time_stamp_update' => date('Y-m-d H:i:s'),
                'file_bc_manifest' => $nama_file_bc_manifest_update, // Pastikan ini diupdate
            ];

            $this->db->table('user_permohonan')->where('id', $id_permohonan)->where('id_pers', $id_user_login)->update($data_update);
            log_message('info', 'USER EDIT PERMOHONAN - Permohonan ID: ' . $id_permohonan . ' berhasil diupdate.');
            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Permohonan Impor Kembali berhasil diubah.</div>');
            return redirect()->to(base_url('user/daftarPermohonan'));
        }
    }

    public function force_change_password_page()
    {
        if (!$this->session->get('email') || (($this->session->get('force_change_password') ?? 0) != 1)) {
            return redirect()->to(base_url('user/index'));
        }

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Wajib Ganti Password';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();

        $rules = [
            'new_password' => [
                'label' => 'Password Baru',
                'rules' => 'required|trim|min_length[6]|matches[confirm_new_password]',
                'errors' => ['min_length' => 'Password minimal 6 karakter.', 'matches'    => 'Konfirmasi password tidak cocok.']
            ],
            'confirm_new_password' => 'required|trim'
        ];

        if (!$this->validate($rules)) {
            echo view('templates/header', $data);
            echo view('templates/sidebar', $data);
            echo view('templates/topbar', $data);
            echo view('user/form_force_change_password', $data);
            echo view('templates/footer');
        } else {
            $new_password_hash = password_hash($this->request->getPost('new_password'), PASSWORD_DEFAULT);
            $update_data = [
                'password' => $new_password_hash,
                'force_change_password' => 0
            ];

            $this->db->table('user')->where('id', $data['user']['id'])->update($update_data);
            $this->session->set('force_change_password', 0);
            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Password Anda telah berhasil diubah. Silakan lanjutkan.</div>');
            return redirect()->to(base_url('user/index'));
        }
    }

    public function tes_layout()
    {
        $data['title'] = 'Tes Layout';
        $data['subtitle'] = 'Halaman Uji Coba Template';

        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        if (empty($data['user'])) {
            $data['user'] = ['name' => 'Guest', 'image' => 'default.jpg', 'role_id' => 0, 'role_name' => 'Guest'];
        }

        log_message('debug', 'TES LAYOUT - Memulai load view header.');
        echo view('templates/header', $data);

        log_message('debug', 'TES LAYOUT - Memulai load view tes_konten.');
        echo view('user/tes_konten', $data);

        log_message('debug', 'TES LAYOUT - Memulai load view footer.');
        echo view('templates/footer', $data);
        log_message('debug', 'TES LAYOUT - Semua view selesai di-load.');
    }

    public function hapus_permohonan_impor($id_permohonan = 0)
    {
        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid.</div>');
            return redirect()->to(base_url('user/daftarPermohonan'));
        }

        $id_user_login = $this->session->get('user_id'); // Menggunakan 'user_id' dari session

        $permohonan = $this->db->table('user_permohonan')->where([
            'id' => $id_permohonan,
            'id_pers' => $id_user_login
        ])->get()->getRowArray();

        if (!$permohonan) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Permohonan tidak ditemukan atau Anda tidak berhak menghapusnya.</div>');
            return redirect()->to(base_url('user/daftarPermohonan'));
        }

        $deletable_statuses = ['0', '5'];
        if (!in_array(($permohonan['status'] ?? null), $deletable_statuses)) { // Menggunakan null coalescing
            $this->session->setFlashdata('message', '<div class="alert alert-warning" role="alert">Permohonan ini sudah dalam proses dan tidak dapat dihapus lagi.</div>');
            return redirect()->to(base_url('user/daftarPermohonan'));
        }

        $file_bc_manifest_path = FCPATH . 'uploads/bc_manifest/' . ($permohonan['file_bc_manifest'] ?? ''); // Menggunakan null coalescing
        if (!empty($permohonan['file_bc_manifest']) && file_exists($file_bc_manifest_path)) {
            if (@unlink($file_bc_manifest_path)) {
                log_message('info', 'User (ID: '.$id_user_login.') menghapus file BC Manifest: ' . $permohonan['file_bc_manifest'] . ' untuk permohonan ID: ' . $id_permohonan);
            } else {
                log_message('error', 'User (ID: '.$id_user_login.') GAGAL menghapus file BC Manifest: ' . $permohonan['file_bc_manifest'] . ' untuk permohonan ID: ' . $id_permohonan);
            }
        }

        $this->db->table('user_permohonan')->where('id', $id_permohonan)->where('id_pers', $id_user_login)->delete();
        log_message('info', 'User (ID: '.$id_user_login.') menghapus permohonan impor ID: ' . $id_permohonan);
        $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Permohonan Impor Kembali (ID Aju: '.htmlspecialchars($id_permohonan).') berhasil dihapus.</div>');
        return redirect()->to(base_url('user/daftarPermohonan'));
    }

    public function hapus_pengajuan_kuota($id_pengajuan = 0)
    {
        if ($id_pengajuan == 0 || !is_numeric($id_pengajuan)) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">ID Pengajuan Kuota tidak valid.</div>');
            return redirect()->to(base_url('user/daftar_pengajuan_kuota'));
        }

        $id_user_login = $this->session->get('user_id'); // Menggunakan 'user_id' dari session

        $pengajuan = $this->db->table('user_pengajuan_kuota')->where([
            'id' => $id_pengajuan,
            'id_pers' => $id_user_login
        ])->get()->getRowArray();

        if (!$pengajuan) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Pengajuan kuota tidak ditemukan atau Anda tidak berhak menghapusnya.</div>');
            return redirect()->to(base_url('user/daftar_pengajuan_kuota'));
        }

        $deletable_statuses = ['pending'];
        if (!in_array(($pengajuan['status'] ?? null), $deletable_statuses)) { // Menggunakan null coalescing
            $this->session->setFlashdata('message', '<div class="alert alert-warning" role="alert">Pengajuan kuota ini sudah dalam proses (Status: '.htmlspecialchars($pengajuan['status']).') dan tidak dapat dihapus lagi.</div>');
            return redirect()->to(base_url('user/daftar_pengajuan_kuota'));
        }

        $file_lampiran_path = FCPATH . 'uploads/lampiran_kuota/' . ($pengajuan['file_lampiran_user'] ?? ''); // Menggunakan null coalescing
        if (!empty($pengajuan['file_lampiran_user']) && file_exists($file_lampiran_path)) {
            if (@unlink($file_lampiran_path)) {
                log_message('info', 'User (ID: '.$id_user_login.') menghapus file lampiran kuota: ' . $pengajuan['file_lampiran_user'] . ' untuk pengajuan ID: ' . $id_pengajuan);
            } else {
                log_message('error', 'User (ID: '.$id_user_login.') GAGAL menghapus file lampiran kuota: ' . $pengajuan['file_lampiran_user'] . ' untuk pengajuan ID: ' . $id_pengajuan);
            }
        }

        $this->db->table('user_pengajuan_kuota')->where('id', $id_pengajuan)->where('id_pers', $id_user_login)->delete();
        log_message('info', 'User (ID: '.$id_user_login.') menghapus pengajuan kuota ID: ' . $id_pengajuan);
        $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Pengajuan kuota (ID: '.htmlspecialchars($id_pengajuan).') berhasil dihapus.</div>');
        return redirect()->to(base_url('user/daftar_pengajuan_kuota'));
    }
}
