<?php

namespace App\Controllers; // Pastikan namespace ini sesuai

use App\Controllers\BaseController; // Menggunakan BaseController yang baru
use PragmaRX\Google2FA\Google2FA; // Untuk MFA
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;

class Petugas extends BaseController // Meng-extend BaseController
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

        // Helper form, url, dan download sudah dimuat di BaseController::$helpers
        // Pastikan juga repack_helper sudah dimuat di BaseController::$helpers

        $this->_check_auth_petugas(); // Panggil fungsi otentikasi di initController
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
        echo view('petugas/mfa_setup', $data);
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
            return redirect()->to(base_url('petugas/index'));
        } else {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Kode verifikasi salah. Silakan coba lagi.</div>');
            return redirect()->to(base_url('petugas/setup_mfa'));
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
        return redirect()->to(base_url('petugas/setup_mfa'));
    }

    private function _check_auth_petugas()
    {
        // Mendapatkan nama metode saat ini
        $current_method = $this->router->methodName();

        // Jika metode adalah 'logout', biarkan melewati tanpa pemeriksaan otentikasi
        if ($current_method == 'logout') {
            return;
        }

        // Periksa apakah pengguna harus mengganti password paksa
        if (($this->session->get('force_change_password') ?? 0) == 1 && $current_method != 'force_change_password_page') {
            $this->session->setFlashdata('message', '<div class="alert alert-warning" role="alert">Untuk keamanan, Anda wajib mengganti password Anda terlebih dahulu.</div>');
            return redirect()->to(base_url('petugas/force_change_password_page'));
        }

        // Metode yang dikecualikan dari pemeriksaan otentikasi penuh, tetapi memerlukan session yang ada
        $other_excluded_methods = ['edit_profil', 'reset_mfa', 'setup_mfa', 'verify_mfa'];
        if (in_array($current_method, $other_excluded_methods)) {
            // Jika tidak ada email di session, redirect ke auth
            if (!$this->session->get('email')) {
                $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Sesi Anda tidak valid. Silakan login kembali.</div>');
                return redirect()->to(base_url('auth')); // Atau auth/logout
            }
            return; // Lanjutkan eksekusi metode yang dikecualikan
        }

        // Periksa apakah session email ada, jika tidak, arahkan ke halaman login
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Mohon login untuk melanjutkan.</div>');
            return redirect()->to(base_url('auth'));
        }

        // Periksa role_id pengguna
        $role_id_session = $this->session->get('role_id');
        if (($role_id_session ?? null) != 3) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Akses Ditolak! Anda tidak diotorisasi untuk mengakses area Petugas.</div>');
            if ($role_id_session == 1) return redirect()->to(base_url('admin'));
            elseif ($role_id_session == 2) return redirect()->to(base_url('user'));
            elseif ($role_id_session == 4) return redirect()->to(base_url('monitoring'));
            else return redirect()->to(base_url('auth/blocked'));
        }

        // Periksa status aktif pengguna
        if (($this->session->get('is_active') ?? null) === 0) {
            $this->session->setFlashdata('message', '<div class="alert alert-warning" role="alert">Akun Petugas Anda tidak aktif. Hubungi Administrator.</div>');
            return redirect()->to(base_url('auth/blocked'));
        }
    }

    public function index()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Dashboard Petugas';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        $petugas_user_id = $data['user']['id'];

        // Ambil detail petugas dari tabel 'petugas' berdasarkan id_user
        $petugas_detail = $this->db->table('petugas')->where('id_user', $petugas_user_id)->get()->getRowArray();
        $petugas_id_in_petugas_table = $petugas_detail ? $petugas_detail['id'] : null;

        // Hitung jumlah tugas LHP yang ditugaskan kepada petugas ini (status '1')
        if ($petugas_id_in_petugas_table) {
            $data['jumlah_tugas_lhp'] = $this->db->table('user_permohonan')
                                                 ->where('petugas', $petugas_id_in_petugas_table)
                                                 ->where('status', '1')
                                                 ->countAllResults();
        } else {
            $data['jumlah_tugas_lhp'] = 0;
        }

        // Hitung jumlah LHP yang sudah diselesaikan oleh petugas ini
        $data['jumlah_lhp_selesai'] = $this->db->table('lhp')
                                               ->where('id_petugas_pemeriksa', $petugas_user_id)
                                               ->countAllResults();

        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('templates/topbar', $data);
        echo view('petugas/dashboard_petugas_view', $data);
        echo view('templates/footer');
    }

    public function daftar_pemeriksaan()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Daftar Pemeriksaan Ditugaskan';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        $petugas_user_id = $data['user']['id'];

        $petugas_detail = $this->db->table('petugas')->where('id_user', $petugas_user_id)->get()->getRowArray();
        $petugas_id_in_petugas_table = $petugas_detail ? $petugas_detail['id'] : null;

        if (!$petugas_id_in_petugas_table) {
            $this->session->setFlashdata('message', '<div class="alert alert-warning" role="alert">Data detail petugas tidak ditemukan. Tidak dapat menampilkan tugas.</div>');
            $data['daftar_tugas'] = [];
        } else {
            $builder = $this->db->table('user_permohonan up');
            $builder->select('up.*, upr.NamaPers, u_pemohon.name as nama_pemohon');
            $builder->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
            $builder->join('user u_pemohon', 'upr.id_pers = u_pemohon.id', 'left');
            $builder->where('up.petugas', $petugas_id_in_petugas_table);
            $builder->where('up.status', '1'); // Hanya permohonan dengan status '1' (Penunjukan Pemeriksa)
            $builder->orderBy('up.TglSuratTugas DESC, up.WaktuPenunjukanPetugas DESC');
            $data['daftar_tugas'] = $builder->get()->getResultArray();
        }

        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('templates/topbar', $data);
        echo view('petugas/daftar_pemeriksaan_view', $data);
        echo view('templates/footer');
    }

    private function _handle_file_upload($fieldName, $uploadConfig, $existingFile = null)
    {
        $file = $this->request->getFile($fieldName);

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $validationRules = [
                $fieldName => [
                    'label' => $uploadConfig['label'] ?? 'File',
                    'rules' => 'uploaded[' . $fieldName . ']|max_size[' . $fieldName . ',' . $uploadConfig['max_size'] . ']|ext_in[' . $fieldName . ',' . $uploadConfig['allowed_types'] . ']',
                    'errors' => [
                        'uploaded' => 'Silakan pilih file untuk diunggah.',
                        'max_size' => 'Ukuran file {field} terlalu besar.',
                        'ext_in' => 'Tipe file {field} tidak diizinkan.'
                    ]
                ]
            ];

            if (isset($uploadConfig['max_width']) && isset($uploadConfig['max_height'])) {
                $validationRules[$fieldName]['rules'] .= '|max_dims[' . $fieldName . ',' . $uploadConfig['max_width'] . ',' . $uploadConfig['max_height'] . ']';
                $validationRules[$fieldName]['errors']['max_dims'] = 'Dimensi gambar {field} terlalu besar.';
            }

            if (!$this->validate($validationRules)) {
                $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Upload Gagal: ' . $this->validator->getError($fieldName) . '</div>');
                return false;
            }

            $uploadPath = $uploadConfig['upload_path'];
            $newName = $file->getRandomName();

            try {
                $file->move($uploadPath, $newName);

                if ($existingFile && file_exists($uploadPath . $existingFile)) {
                    @unlink($uploadPath . $existingFile);
                }
                return $newName;
            } catch (\Exception $e) {
                log_message('error', 'File upload error: ' . $e->getMessage());
                $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Gagal mengunggah file. Silakan coba lagi.</div>');
                return false;
            }
        }
        return $existingFile;
    }


    public function rekam_lhp($id_permohonan = 0)
    {
        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid.</div>');
            return redirect()->to(base_url('petugas/daftar_pemeriksaan'));
        }

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Perekaman Laporan Hasil Pemeriksaan (LHP) - ID Aju: ' . htmlspecialchars($id_permohonan);
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        $petugas_user_id = $data['user']['id'];

        $petugas_detail_db = $this->db->table('petugas')->where('id_user', $petugas_user_id)->get()->getRowArray();
        $petugas_id_for_permohonan = $petugas_detail_db ? $petugas_detail_db['id'] : null;

        $builder = $this->db->table('user_permohonan up');
        $builder->select('up.*, upr.NamaPers, upr.npwp, u_pemohon.name as nama_pemohon');
        $builder->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $builder->join('user u_pemohon', 'upr.id_pers = u_pemohon.id', 'left');
        $builder->where('up.id', $id_permohonan);

        if ($petugas_id_for_permohonan) {
            $builder->where('up.petugas', $petugas_id_for_permohonan);
        } else {
             $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Detail petugas tidak valid atau tidak ditemukan. Tidak dapat memverifikasi tugas.</div>');
             return redirect()->to(base_url('petugas/daftar_pemeriksaan'));
        }
        $builder->where('up.status', '1'); // Hanya permohonan dengan status '1' (Penunjukan Pemeriksa)
        $permohonan = $builder->get()->getRowArray();

        if (!$permohonan) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Permohonan tidak ditemukan, tidak ditugaskan kepada Anda, atau status tidak sesuai untuk perekaman LHP.</div>');
            return redirect()->to(base_url('petugas/daftar_pemeriksaan'));
        }

        log_message('debug', 'Petugas Rekam LHP - Data Permohonan: ' . print_r($permohonan, true));
        $data['permohonan'] = $permohonan;

        $existing_lhp = $this->db->table('lhp')->where('id_permohonan', $id_permohonan)->get()->getRowArray();
        $data['lhp_data'] = $existing_lhp;

        $rules = [
            'NoLHP' => 'trim|required',
            'TglLHP' => 'trim|required',
            'JumlahBenar' => 'trim|required|numeric|greater_than_equal_to[0]',
            'JumlahSalah' => 'trim|required|numeric|greater_than_equal_to[0]',
            'Catatan' => 'trim'
        ];

        // Aturan untuk FileLHP
        if (!$existing_lhp && !$this->request->getFile('FileLHP')->isValid()) {
            $rules['FileLHP'] = [
                'label' => 'File LHP Resmi',
                'rules' => 'uploaded[FileLHP]|max_size[FileLHP,2048]|ext_in[FileLHP,pdf,doc,docx,jpg,jpeg,png]',
                'errors' => ['uploaded' => 'Kolom {field} wajib diisi.']
            ];
        } else if ($this->request->getFile('FileLHP')->isValid()) {
            $rules['FileLHP'] = [
                'label' => 'File LHP Resmi',
                'rules' => 'max_size[FileLHP,2048]|ext_in[FileLHP,pdf,doc,docx,jpg,jpeg,png]',
                'errors' => [
                    'max_size' => 'Ukuran file {field} terlalu besar.',
                    'ext_in' => 'Tipe file {field} tidak diizinkan.'
                ]
            ];
        }

        // Aturan untuk file_dokumentasi_foto
        if ($this->request->getFile('file_dokumentasi_foto')->isValid()) {
            $rules['file_dokumentasi_foto'] = [
                'label' => 'File Dokumentasi Foto',
                'rules' => 'max_size[file_dokumentasi_foto,2048]|ext_in[file_dokumentasi_foto,jpg,jpeg,png,gif]',
                'errors' => [
                    'max_size' => 'Ukuran file {field} terlalu besar.',
                    'ext_in' => 'Tipe file {field} tidak diizinkan.'
                ]
            ];
        }


        if (!$this->validate($rules)) {
            echo view('templates/header', $data);
            echo view('templates/sidebar', $data);
            echo view('templates/topbar', $data);
            echo view('petugas/form_rekam_lhp_view', $data);
            echo view('templates/footer');
        } else {
            $jumlah_diajukan_pemohon = $permohonan['JumlahBarang'] ?? 0;
            if (!isset($permohonan['JumlahBarang'])) {
                log_message('error', 'Petugas Rekam LHP - Kolom "JumlahBarang" tidak ditemukan dalam data permohonan untuk ID: ' . $id_permohonan);
            } else if ($jumlah_diajukan_pemohon == 0) {
                log_message('warning', 'Petugas Rekam LHP - Jumlah diajukan dari permohonan (JumlahBarang) adalah 0 untuk ID: ' . $id_permohonan);
            }

            $data_lhp_to_save = [
                'id_permohonan'         => $id_permohonan,
                'id_petugas_pemeriksa'  => $petugas_user_id,
                'NoLHP'                 => $this->request->getPost('NoLHP'),
                'TglLHP'                => $this->request->getPost('TglLHP'),
                'JumlahAju'             => (int)$jumlah_diajukan_pemohon,
                'JumlahBenar'           => (int)$this->request->getPost('JumlahBenar'),
                'JumlahSalah'           => (int)$this->request->getPost('JumlahSalah'),
                'Catatan'               => $this->request->getPost('Catatan'),
            ];
            if (!$existing_lhp) {
                $data_lhp_to_save['submit_time'] = date('Y-m-d H:i:s');
            }

            // Handle FileLHP upload
            $nama_file_lhp_resmi = $existing_lhp['FileLHP'] ?? null;
            $upload_dir_lhp = FCPATH . 'uploads/lhp/';
            $uploadConfigLHP = [
                'upload_path' => $upload_dir_lhp,
                'allowed_types' => 'pdf|doc|docx|jpg|jpeg|png',
                'max_size' => 2048,
                'label' => 'File LHP Resmi'
            ];
            $uploadedLHPFile = $this->_handle_file_upload('FileLHP', $uploadConfigLHP, $nama_file_lhp_resmi);
            if ($uploadedLHPFile === false) {
                return redirect()->to(base_url('petugas/rekam_lhp/' . $id_permohonan));
            }
            $data_lhp_to_save['FileLHP'] = $uploadedLHPFile;

            // Handle file_dokumentasi_foto upload
            $nama_file_doc_foto = $existing_lhp['file_dokumentasi_foto'] ?? null;
            $upload_dir_doc_foto = FCPATH . 'uploads/dokumentasi_lhp/';
            $uploadConfigDocFoto = [
                'upload_path' => $upload_dir_doc_foto,
                'allowed_types' => 'jpg|jpeg|png|gif',
                'max_size' => 2048,
                'label' => 'File Dokumentasi Foto'
            ];
            $uploadedDocFotoFile = $this->_handle_file_upload('file_dokumentasi_foto', $uploadConfigDocFoto, $nama_file_doc_foto);
            if ($uploadedDocFotoFile === false) {
                return redirect()->to(base_url('petugas/rekam_lhp/' . $id_permohonan));
            }
            $data_lhp_to_save['file_dokumentasi_foto'] = $uploadedDocFotoFile;


            $lhp_processed_id = null;
            if ($existing_lhp) {
                $this->db->table('lhp')->where('id', $existing_lhp['id'])->update($data_lhp_to_save);
                $lhp_processed_id = $existing_lhp['id'];
                $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">LHP berhasil diperbarui!</div>');
            } else {
                $this->db->table('lhp')->insert($data_lhp_to_save);
                $lhp_processed_id = $this->db->insertID();
                $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">LHP berhasil direkam!</div>');
            }

            if ($lhp_processed_id) {
                $this->db->table('user_permohonan')->where('id', $id_permohonan)->update(['status' => '2']);
                log_message('info', 'Status permohonan ID ' . $id_permohonan . ' diubah menjadi LHP Direkam (2). LHP ID: ' . $lhp_processed_id);
            } else {
                $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Gagal menyimpan data LHP ke database. Silakan coba lagi.</div>');
            }
            return redirect()->to(base_url('petugas/daftar_pemeriksaan'));
        }
    }

    public function force_change_password_page()
    {
        // Periksa apakah pengguna sedang login dan harus ganti password
        if (!$this->session->get('email') ||
            ($this->session->get('force_change_password') ?? 0) != 1 || // Gunakan null coalescing
            ($this->session->get('role_id') ?? null) != 3) { // Gunakan null coalescing
            if (($this->session->get('role_id') ?? null) == 3) {
                return redirect()->to(base_url('petugas/index'));
            } else {
                return redirect()->to(base_url('auth/logout'));
            }
        }
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Wajib Ganti Password (Petugas)';
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
            echo view('petugas/form_force_change_password', $data);
            echo view('templates/footer');
        } else {
            $new_password_hash = password_hash($this->request->getPost('new_password'), PASSWORD_DEFAULT);
            $user_id = $data['user']['id'];
            $update_data_db = ['password' => $new_password_hash, 'force_change_password' => 0];
            $this->db->table('user')->where('id', $user_id)->update($update_data_db);

            $this->session->set('force_change_password', 0); // Menggunakan set()
            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Password Anda telah berhasil diubah. Selamat datang di dashboard Anda.</div>');
            return redirect()->to(base_url('petugas/index'));
        }
    }

    public function edit_profil()
    {
        $this->_check_auth_petugas();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Edit Profil Saya';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        $user_id = $data['user']['id'];

        // Mengganti field_exists dengan cara CI4 untuk memeriksa keberadaan kolom
        // Jika Anda menggunakan Model, Anda bisa mendefinisikannya di Model dan mengaksesnya dari sana.
        // Untuk contoh ini, saya akan menggunakan describeTable().
        $db_forge = \Config\Services::forge();
        if ($this->db->fieldExists('id_user', 'petugas')) { //fieldExists() masih ada di CI4 database
            $data['petugas_detail'] = $this->db->table('petugas')->where('id_user', $user_id)->get()->getRowArray();
        } else {
            $data['petugas_detail'] = null;
            log_message('error', 'Kolom id_user tidak ditemukan di tabel petugas untuk edit_profil Petugas.');
        }

        if ($this->request->getMethod() === 'post') {
            // Logika upload foto profil
            $profileImage = $this->request->getFile('profile_image'); // Menggunakan getFile()

            if ($profileImage && $profileImage->isValid() && !$profileImage->hasMoved()) {
                $upload_dir_profile = FCPATH . 'uploads/profile_images/';
                $uploadConfigProfile = [
                    'upload_path' => $upload_dir_profile,
                    'allowed_types' => 'jpg|png|jpeg|gif',
                    'max_size' => 2048,
                    'max_width' => 1024,
                    'max_height' => 1024,
                    'label' => 'Foto Profil'
                ];
                $uploadedProfileImage = $this->_handle_file_upload('profile_image', $uploadConfigProfile, $data['user']['image']);
                if ($uploadedProfileImage === false) {
                    return redirect()->to(base_url('petugas/edit_profil'));
                }

                if ($uploadedProfileImage !== $data['user']['image']) { // Jika ada perubahan nama file
                    $this->db->table('user')->where('id', $user_id)->update(['image' => $uploadedProfileImage]);
                    $this->session->set('user_image', $uploadedProfileImage); // Menggunakan set()
                    $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Foto profil berhasil diupdate.</div>');
                } else {
                     $this->session->setFlashdata('message', '<div class="alert alert-info" role="alert">Tidak ada perubahan pada foto profil.</div>');
                }
            } else if ($profileImage && $profileImage->isValid() && $profileImage->hasMoved()) {
                 $this->session->setFlashdata('message', '<div class="alert alert-info" role="alert">File foto profil sudah pernah diupload atau tidak ada perubahan.</div>');
            } else if ($profileImage && $profileImage->getError() !== UPLOAD_ERR_NO_FILE) {
                $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Upload Foto Profil Gagal: ' . $profileImage->getErrorString() . '</div>');
            }


            // Logika update nama/email (seperti di Admin/Petugas_administrasi)
            $update_data_user = [];
            $name_input = $this->request->getPost('name', true);
            if (!empty($name_input) && $name_input !== $data['user']['name']) {
                $update_data_user['name'] = htmlspecialchars($name_input);
            }

            $current_login_identifier = $data['user']['email'];
            $new_login_identifier = $this->request->getPost('login_identifier', true); // Asumsi ada field ini di form

            // Validasi email/NIP jika berubah
            if (!empty($new_login_identifier) && $new_login_identifier !== $current_login_identifier) {
                $is_nip = ($data['user']['role_id'] == 3); // Asumsi role 3 adalah petugas dengan NIP
                $identifier_label = $is_nip ? 'NIP Petugas' : 'Email Login';
                $identifier_rules = $is_nip ? 'trim|required|numeric|is_unique[user.email,id,'.$user_id.']' : 'trim|required|valid_email|is_unique[user.email,id,'.$user_id.']';

                $this->validation->setRules([
                    'login_identifier' => [
                        'label' => $identifier_label,
                        'rules' => $identifier_rules,
                        'errors' => [
                            'is_unique' => $identifier_label . ' ini sudah terdaftar.',
                            'numeric'   => $identifier_label . ' harus berupa angka.',
                            'valid_email'=> $identifier_label . ' tidak valid.'
                        ]
                    ]
                ]);

                if ($this->validation->withRequest($this->request)->run()) {
                    $update_data_user['email'] = htmlspecialchars($new_login_identifier);
                } else {
                    $errors = $this->validation->getErrors();
                    foreach ($errors as $field => $error) {
                        $this->session->setFlashdata('message', ($this->session->getFlashdata('message') ?? '') . '<div class="alert alert-danger" role="alert">' . $error . '</div>');
                    }
                    return redirect()->to(base_url('petugas/edit_profil'));
                }
            }

            // Update data user jika ada perubahan
            if (!empty($update_data_user)) {
                $this->db->table('user')->where('id', $user_id)->update($update_data_user);
                $this->session->setFlashdata('message', ($this->session->getFlashdata('message') ?? '') . '<div class="alert alert-success" role="alert">Profil berhasil diupdate.</div>');
                if (isset($update_data_user['name'])) {
                    $this->session->set('name', $update_data_user['name']);
                }
                if (isset($update_data_user['email'])) {
                    $this->session->set('email', $update_data_user['email']);
                }
            }

            return redirect()->to(base_url('petugas/edit_profil'));
        }

        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('templates/topbar', $data);
        echo view('petugas/form_edit_profil_petugas', $data);
        echo view('templates/footer');
    }

    public function riwayat_lhp_direkam()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Riwayat LHP yang Telah Direkam';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        $petugas_user_id = $data['user']['id'];

        $builder = $this->db->table('lhp');
        $builder->select(
            'lhp.*, '.
            'up.nomorSurat as nomor_surat_permohonan, '.
            'up.TglSurat as tanggal_surat_permohonan, '.
            'upr.NamaPers as nama_perusahaan_pemohon, '.
            'up.status as status_permohonan_terkini, '.
            'up.NamaBarang as nama_barang_permohonan'
        );
        $builder->join('user_permohonan up', 'lhp.id_permohonan = up.id', 'left');
        $builder->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $builder->where('lhp.id_petugas_pemeriksa', $petugas_user_id);
        $builder->orderBy('lhp.submit_time', 'DESC');
        $data['riwayat_lhp'] = $builder->get()->getResultArray();

        log_message('debug', 'PETUGAS RIWAYAT LHP - User ID: ' . $petugas_user_id);
        log_message('debug', 'PETUGAS RIWAYAT LHP - Query: ' . $this->db->getLastQuery()->getQueryString());
        log_message('debug', 'PETUGAS RIWAYAT LHP - Jumlah Data: ' . count($data['riwayat_lhp']));

        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('templates/topbar', $data);
        echo view('petugas/riwayat_lhp_direkam_view', $data);
        echo view('templates/footer', $data);
    }

    public function detail_lhp_direkam($id_lhp = 0)
    {
        if ($id_lhp == 0 || !is_numeric($id_lhp)) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">ID LHP tidak valid.</div>');
            return redirect()->to(base_url('petugas/riwayat_lhp_direkam'));
        }

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Detail LHP Direkam (ID LHP: '.htmlspecialchars($id_lhp).')';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        $petugas_user_id = $data['user']['id'];

        $builder = $this->db->table('lhp');
        $builder->select(
            'lhp.*, '.
            'up.id as id_permohonan_ajuan, up.nomorSurat as nomor_surat_permohonan, up.TglSurat as tanggal_surat_pemohon, '.
            'up.NamaBarang as nama_barang_di_permohonan, up.JumlahBarang as jumlah_barang_di_permohonan, '.
            'upr.NamaPers as nama_perusahaan_pemohon, upr.npwp as npwp_perusahaan'
        );
        $builder->join('user_permohonan up', 'lhp.id_permohonan = up.id', 'left');
        $builder->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $builder->where('lhp.id', $id_lhp);
        $builder->where('lhp.id_petugas_pemeriksa', $petugas_user_id);
        $data['lhp_detail'] = $builder->get()->getRowArray();

        if (!$data['lhp_detail']) {
            $this->session->setFlashdata('message', '<div class="alert alert-warning" role="alert">Detail LHP tidak ditemukan atau Anda tidak memiliki akses untuk melihatnya.</div>');
            return redirect()->to(base_url('petugas/riwayat_lhp_direkam'));
        }

        log_message('debug', 'PETUGAS DETAIL LHP - Data LHP: ' . print_r($data['lhp_detail'], true));

        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('templates/topbar', $data);
        echo view('petugas/detail_lhp_view', $data);
        echo view('templates/footer', $data);
    }

    public function monitoring_permohonan()
    {
        log_message('debug', 'Petugas: monitoring_permohonan() called.');
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Monitoring Seluruh Permohonan Impor';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();

        $builder = $this->db->table('user_permohonan up');
        $builder->select(
            'up.id, up.nomorSurat, up.TglSurat, up.time_stamp, up.status, ' .
            'upr.NamaPers, ' .
            'u_pemohon.name as nama_pengaju_permohonan, ' .
            'u_petugas_pemeriksa.name as nama_petugas_pemeriksa'
        );
        $builder->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $builder->join('user u_pemohon', 'upr.id_pers = u_pemohon.id', 'left');
        $builder->join('petugas p_pemeriksa', 'up.petugas = p_pemeriksa.id', 'left');
        $builder->join('user u_petugas_pemeriksa', 'p_pemeriksa.id_user = u_petugas_pemeriksa.id', 'left');

        // Order by FIELD not directly supported by CI4 Query Builder,
        // but can be done with raw expression if needed.
        // For simplicity, we can order by status ASC and time_stamp DESC
        $builder->orderBy('up.status ASC, up.time_stamp DESC'); // Ini mungkin tidak sama persis dengan FIELD di CI3

        $data['permohonan_list'] = $builder->get()->getResultArray();

        log_message('debug', 'Petugas: monitoring_permohonan() - Query: ' . $this->db->getLastQuery()->getQueryString());
        log_message('debug', 'Petugas: monitoring_permohonan() - Jumlah Data: ' . count($data['permohonan_list']));

        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('templates/topbar', $data);
        echo view('petugas/monitoring_permohonan_view', $data);
        echo view('templates/footer');
    }

    public function detail_monitoring_permohonan($id_permohonan = 0)
    {
        log_message('debug', 'Petugas: detail_monitoring_permohonan() called with ID: ' . $id_permohonan);
        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid.</div>');
            return redirect()->to(base_url('petugas/monitoring_permohonan'));
        }

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Detail Permohonan Impor (Monitoring) ID: ' . htmlspecialchars($id_permohonan);
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();

        $builder = $this->db->table('user_permohonan up');
        $builder->select(
            'up.*, ' .
            'upr.NamaPers, upr.npwp AS npwp_perusahaan, upr.alamat AS alamat_perusahaan, upr.NoSkep AS NoSkep_perusahaan, ' .
            'u_pemohon.name as nama_pengaju_permohonan, u_pemohon.email as email_pengaju_permohonan, '.
            'p_pemeriksa.NIP as nip_petugas_pemeriksa, u_petugas.name as nama_petugas_pemeriksa'
        );
        $builder->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $builder->join('user u_pemohon', 'upr.id_pers = u_pemohon.id', 'left');
        $builder->join('petugas p_pemeriksa', 'up.petugas = p_pemeriksa.id', 'left');
        $builder->join('user u_petugas', 'p_pemeriksa.id_user = u_petugas.id', 'left');
        $builder->where('up.id', $id_permohonan);
        $data['permohonan_detail'] = $builder->get()->getRowArray();

        log_message('debug', 'Petugas: detail_monitoring_permohonan() - Query Permohonan: ' . $this->db->getLastQuery()->getQueryString());
        log_message('debug', 'Petugas: detail_monitoring_permohonan() - Data Permohonan: ' . print_r($data['permohonan_detail'], true));

        if (!$data['permohonan_detail']) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Data permohonan dengan ID ' . htmlspecialchars($id_permohonan) . ' tidak ditemukan.</div>');
            return redirect()->to(base_url('petugas/monitoring_permohonan'));
        }

        $data['lhp_detail'] = $this->db->table('lhp')->where('id_permohonan', $id_permohonan)->get()->getRowArray();
        log_message('debug', 'Petugas: detail_monitoring_permohonan() - Data LHP: ' . print_r($data['lhp_detail'], true));
        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('templates/topbar', $data);
        echo view('user/detail_permohonan_view', $data); // Asumsi view ini bisa digunakan juga oleh Petugas
        echo view('templates/footer');
    }
}
