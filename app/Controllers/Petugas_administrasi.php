<?php
namespace App\Controllers; // Pastikan namespace ini sesuai

use App\Controllers\BaseController; // Menggunakan BaseController yang baru
use PragmaRX\Google2FA\Google2FA; // Untuk MFA
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;

class Petugas_administrasi extends BaseController // Meng-extend BaseController
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

        // Helper form, url, repack_helper, download sudah dimuat di BaseController::$helpers
        
        $excluded_methods = ['logout'];
        $current_method = $this->router->methodName(); // Menggunakan methodName() di CI4

        if (!in_array($current_method, $excluded_methods)) {
            $this->_check_auth_petugas_administrasi();
        }
        // Kondisi elseif tidak lagi diperlukan karena _check_auth_petugas_administrasi sudah menangani redirect
        // elseif (!$this->session->get('email') && $current_method != 'logout') {
        //      $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Sesi tidak valid atau telah berakhir. Silakan login kembali.</div>');
        //      return redirect()->to(base_url('auth'));
        // }
        log_message('debug', 'Petugas_administrasi Class Initialized. Method: ' . $this->router->methodName());
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
        echo view('petugas_administrasi/mfa_setup', $data);
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
            return redirect()->to(base_url('petugas_administrasi/index'));
        } else {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Kode verifikasi salah. Silakan coba lagi.</div>');
            return redirect()->to(base_url('petugas_administrasi/setup_mfa'));
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
        return redirect()->to(base_url('petugas_administrasi/setup_mfa'));
    }

    public function edit_profil()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Edit Profil Saya';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        $user_id = $data['user']['id'];

        if ($this->request->getMethod() === 'post') { // Menggunakan getMethod()
            $update_data_user = [];
            $name_input = $this->request->getPost('name', true); // Menggunakan getPost()

            if (!empty($name_input) && $name_input !== $data['user']['name']) {
                $update_data_user['name'] = htmlspecialchars($name_input);
            }

            $current_login_identifier = $data['user']['email'];
            $new_login_identifier = $this->request->getPost('login_identifier', true);

            if (!empty($new_login_identifier) && $new_login_identifier !== $current_login_identifier) {
                $this->validation->setRules([ // Menggunakan $this->validation
                    'login_identifier' => [
                        'label' => 'Email Login',
                        'rules' => "trim|required|valid_email|is_unique[user.email,id,{$user_id}]",
                        'errors' => [
                            'is_unique' => 'Email ini sudah terdaftar.',
                            'valid_email' => 'Format email tidak valid.'
                        ]
                    ]
                ]);

                if ($this->validation->withRequest($this->request)->run()) { // Menjalankan validasi
                    $update_data_user['email'] = htmlspecialchars($new_login_identifier);
                } else {
                    $errors = $this->validation->getErrors();
                    foreach ($errors as $field => $error) {
                        $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">' . $error . '</div>');
                    }
                    return redirect()->to(base_url('petugas_administrasi/edit_profil'));
                }
            }

            if (!empty($update_data_user)) {
                $this->db->table('user')->where('id', $user_id)->update($update_data_user);
                $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Profil berhasil diupdate.</div>');
                if (isset($update_data_user['name'])) {
                    $this->session->set('name', $update_data_user['name']); // Menggunakan set()
                }
                if (isset($update_data_user['email'])) {
                    $this->session->set('email', $update_data_user['email']); // Menggunakan set()
                }
            }

            $profileImage = $this->request->getFile('profile_image'); // Menggunakan getFile()

            if ($profileImage && $profileImage->isValid() && !$profileImage->hasMoved()) {
                $upload_dir_profile = FCPATH . 'uploads/profile_images/';

                if (!is_dir($upload_dir_profile)) {
                    if (!@mkdir($upload_dir_profile, 0777, true)) {
                        $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Gagal membuat direktori upload foto profil.</div>');
                        return redirect()->to(base_url('petugas_administrasi/edit_profil'));
                    }
                }

                if (!is_writable($upload_dir_profile)) {
                    $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Upload error: Direktori foto profil tidak writable.</div>');
                    return redirect()->to(base_url('petugas_administrasi/edit_profil'));
                }

                $fileName = $profileImage->getRandomName();
                $profileImage->move($upload_dir_profile, $fileName);

                if ($profileImage->hasMoved()) {
                    $old_image = $data['user']['image'];
                    if ($old_image != 'default.jpg' && !empty($old_image) && file_exists($upload_dir_profile . $old_image)) {
                        @unlink($upload_dir_profile . $old_image);
                    }

                    $this->db->table('user')->where('id', $user_id)->update(['image' => $fileName]);
                    $this->session->set('user_image', $fileName); // Menggunakan set()
                    $current_flash = $this->session->getFlashdata('message');
                    $this->session->setFlashdata('message', ($current_flash ? $current_flash . '<br>' : '') . '<div class="alert alert-success" role="alert">Foto profil berhasil diupdate.</div>');
                } else {
                    $current_flash = $this->session->getFlashdata('message');
                    $this->session->setFlashdata('message', ($current_flash ? $current_flash . '<br>' : '') . '<div class="alert alert-danger" role="alert">Upload Foto Profil Gagal: ' . $profileImage->getErrorString() . '</div>');
                }
            }
            return redirect()->to(base_url('petugas_administrasi/edit_profil'));
        }

        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();

        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('templates/topbar', $data);
        echo view('petugas_administrasi/form_edit_profil_admin', $data); // Asumsi ini view yang benar
        echo view('templates/footer');
    }

    private function _check_auth_petugas_administrasi()
    {
        log_message('debug', 'Petugas_administrasi: _check_auth_petugas_administrasi() called. Email session: ' . ($this->session->get('email') ?? 'NULL'));
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Mohon login untuk melanjutkan.</div>');
            return redirect()->to(base_url('auth'));
        }

        $role_id_session = $this->session->get('role_id');
        log_message('debug', 'Petugas_administrasi: _check_auth_petugas_administrasi() - Role ID: ' . ($role_id_session ?? 'NULL'));

        if (($role_id_session ?? null) != 5) {
            log_message('error', 'Petugas_administrasi: _check_auth_petugas_administrasi() - Akses ditolak, role ID tidak sesuai: ' . $role_id_session);
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Akses Ditolak! Anda tidak diotorisasi untuk mengakses halaman ini.</div>');

            if ($role_id_session == 1) return redirect()->to(base_url('admin'));
            elseif ($role_id_session == 2) return redirect()->to(base_url('user'));
            elseif ($role_id_session == 3) return redirect()->to(base_url('petugas'));
            elseif ($role_id_session == 4) return redirect()->to(base_url('monitoring'));
            else return redirect()->to(base_url('auth/blocked'));
        }
        log_message('debug', 'Petugas_administrasi: _check_auth_petugas_administrasi() passed.');
    }

    public function index()
    {
        log_message('debug', 'Petugas_administrasi: index() called.');
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Dashboard Petugas Administrasi';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();

        $data['pending_permohonan'] = $this->db->table('user_permohonan')->whereIn('status', ['0', '1', '2', '5'])->countAllResults();
        $data['pending_kuota_requests'] = $this->db->table('user_pengajuan_kuota')->where('status', 'pending')->countAllResults();

        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('templates/topbar', $data);
        echo view('petugas_administrasi/index', $data);
        echo view('templates/footer');
    }

    public function monitoring_kuota()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Monitoring Kuota Perusahaan (per Jenis Barang)';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();

        $builder = $this->db->table('user_perusahaan up');
        $builder->select('
            up.id_pers,
            up.NamaPers,
            u.email as user_email,
            (SELECT GROUP_CONCAT(DISTINCT ukb.nomor_skep_asal SEPARATOR ", ")
            FROM user_kuota_barang ukb
            WHERE ukb.id_pers = up.id_pers AND ukb.status_kuota_barang = "active"
            ) as list_skep_aktif,
            (SELECT SUM(ukb.initial_quota_barang)
            FROM user_kuota_barang ukb
            WHERE ukb.id_pers = up.id_pers
            ) as total_initial_kuota_barang,
            (SELECT SUM(ukb.remaining_quota_barang)
            FROM user_kuota_barang ukb
            WHERE ukb.id_pers = up.id_pers
            ) as total_remaining_kuota_barang
        ');
        $builder->join('user u', 'up.id_pers = u.id', 'left');

        $builder->orderBy('up.NamaPers', 'ASC');
        $data['monitoring_data'] = $builder->get()->getResultArray();

        log_message('debug', 'PETUGAS_ADMINISTRASI MONITORING KUOTA - Query: ' . $this->db->getLastQuery()->getQueryString());
        log_message('debug', 'PETUGAS_ADMINISTRASI MONITORING KUOTA - Data: ' . print_r($data['monitoring_data'], true));

        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('templates/topbar', $data);
        echo view('petugas_administrasi/monitoring_kuota_view', $data);
        echo view('templates/footer');
    }

    public function permohonanMasuk()
    {
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Daftar Permohonan Impor';

        $builder = $this->db->table('user_permohonan up');
        $builder->select(
            'up.id, up.nomorSurat, up.TglSurat, up.time_stamp, up.status, ' .
            'upr.NamaPers, ' .
            'u_pemohon.name as nama_pengaju, ' .
            'u_real_petugas.name as nama_petugas_assigned'
        );
        $builder->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $builder->join('user u_pemohon', 'upr.id_pers = u_pemohon.id', 'left');
        $builder->join('petugas p_assigned', 'up.petugas = p_assigned.id', 'left');
        $builder->join('user u_real_petugas', 'p_assigned.id_user = u_real_petugas.id', 'left');

        // Order by FIELD not directly supported by CI4 Query Builder,
        // but can be done with raw expression if needed.
        // For simplicity, we can order by status ASC and time_stamp DESC
        $builder->orderBy('up.status ASC, up.time_stamp DESC'); // Ini mungkin tidak sama persis dengan FIELD di CI3

        $data['permohonan'] = $builder->get()->getResultArray();

        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('templates/topbar', $data);
        echo view('petugas_administrasi/permohonan-masuk', $data);
        echo view('templates/footer');
    }

    private function _get_upload_config($upload_path, $allowed_types, $max_size_kb, $max_width = null, $max_height = null)
    {
        log_message('debug', "Petugas_administrasi Controller: _get_upload_config() called. Path: {$upload_path}, Types: {$allowed_types}, Size: {$max_size_kb}KB");
        $upload_path_full = FCPATH . $upload_path; // Pastikan path absolut

        if (!is_dir($upload_path_full)) {
            log_message('debug', 'Petugas_administrasi Controller: _get_upload_config() - Upload path does not exist: ' . $upload_path_full);
            if (!@mkdir($upload_path_full, 0777, true)) {
                log_message('error', 'Petugas_administrasi Controller: _get_upload_config() - Gagal membuat direktori upload: ' . $upload_path_full . ' - Periksa izin parent direktori.');
                return false;
            }
            log_message('debug', 'Petugas_administrasi Controller: _get_upload_config() - Direktori upload berhasil dibuat: ' . $upload_path_full);
        }
        if (!is_writable($upload_path_full)) {
            log_message('error', 'Petugas_administrasi Controller: _get_upload_config() - Direktori upload tidak writable: ' . $upload_path_full . ' - Periksa izin (chown www-data:www-data dan chmod 775).');
            return false;
        }

        $config = [
            'upload_path'   => $upload_path_full,
            'allowed_types' => $allowed_types,
            'max_size'      => $max_size_kb,
            'encrypt_name'  => true,
        ];
        if ($max_width) $config['max_width'] = $max_width;
        if ($max_height) $config['max_height'] = $max_height;

        log_message('debug', 'Petugas_administrasi Controller: _get_upload_config() - Config created: ' . print_r($config, true));
        return $config;
    }

    // Helper untuk upload file agar tidak duplikasi kode
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


    public function prosesSurat($id_permohonan = 0)
    {
        $pa_user = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        $data['user'] = $pa_user;
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Proses Finalisasi Permohonan Impor';

        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid.</div>');
            return redirect()->to(base_url('petugas_administrasi/permohonanMasuk'));
        }

        $builder = $this->db->table('user_permohonan up');
        $builder->select('up.*, upr.NamaPers, upr.npwp, upr.alamat, upr.NoSkep');
        $builder->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $builder->where('up.id', $id_permohonan);
        $data['permohonan'] = $builder->get()->getRowArray();

        if (!$data['permohonan']) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Permohonan tidak ditemukan.</div>');
            return redirect()->to(base_url('petugas_administrasi/permohonanMasuk'));
        }

        $data['user_perusahaan'] = $this->db->table('user_perusahaan')->where('id_pers', $data['permohonan']['id_pers'])->get()->getRowArray();
        if (!$data['user_perusahaan']) {
            $data['user_perusahaan'] = ['NamaPers' => 'N/A', 'alamat' => 'N/A', 'NoSkep' => 'N/A', 'npwp' => 'N/A'];
        }

        $data['lhp'] = $this->db->table('lhp')->where('id_permohonan', $id_permohonan)->get()->getRowArray();
        if (!$data['lhp'] || ($data['permohonan']['status'] ?? null) != '2' || empty($data['lhp']['NoLHP']) || empty($data['lhp']['TglLHP'])) {
            $this->session->setFlashdata('message', '<div class="alert alert-warning" role="alert">LHP belum lengkap atau status permohonan (ID '.htmlspecialchars($id_permohonan).') tidak valid untuk finalisasi.</div>');
            return redirect()->to(base_url('petugas_administrasi/detail_permohonan_admin/' . $id_permohonan));
        }

        $rules = [
            'status_final' => [
                'label' => 'Status Final Permohonan',
                'rules' => 'required|in_list[3,4]'
            ],
            'nomorSetuju' => [
                'label' => 'Nomor Surat Persetujuan/Penolakan',
                'rules' => 'trim|required|max_length[100]'
            ],
            'tgl_S' => [
                'label' => 'Tanggal Surat Persetujuan/Penolakan',
                'rules' => 'trim|required'
            ],
            'link' => [
                'label' => 'Link Surat Keputusan (Opsional)',
                'rules' => 'trim|valid_url',
                'errors' => ['valid_url' => '{field} harus berisi URL yang valid (contoh: http://example.com).']
            ]
        ];

        if ($this->request->getPost('status_final') == '4') {
            $rules['catatan_penolakan'] = [
                'label' => 'Catatan Penolakan',
                'rules' => 'trim|required'
            ];
        } elseif ($this->request->getPost('status_final') == '3') {
            if (empty($data['permohonan']['file_surat_keputusan']) && !$this->request->getFile('file_surat_keputusan')->isValid()) {
                $rules['file_surat_keputusan'] = [
                    'label' => 'File Surat Persetujuan Pengeluaran',
                    'rules' => 'uploaded[file_surat_keputusan]|max_size[file_surat_keputusan,2048]|ext_in[file_surat_keputusan,pdf,jpg,png,jpeg]',
                    'errors' => [
                        'uploaded' => 'Kolom {field} wajib diisi.',
                        'max_size' => 'Ukuran file {field} melebihi 2MB.',
                        'ext_in' => 'Tipe file {field} tidak valid (Hanya PDF, JPG, PNG, JPEG).'
                    ]
                ];
            } else if ($this->request->getFile('file_surat_keputusan')->isValid()) {
                $rules['file_surat_keputusan'] = [
                    'label' => 'File Surat Persetujuan Pengeluaran',
                    'rules' => 'max_size[file_surat_keputusan,2048]|ext_in[file_surat_keputusan,pdf,jpg,png,jpeg]',
                    'errors' => [
                        'max_size' => 'Ukuran file {field} melebihi 2MB.',
                        'ext_in' => 'Tipe file {field} tidak valid (Hanya PDF, JPG, PNG, JPEG).'
                    ]
                ];
            }
        }

        if (!$this->validate($rules)) {
            log_message('debug', 'PETUGAS_ADMINISTRASI PROSES SURAT - Form validation failed. Errors: ' . json_encode($this->validator->getErrors()));
            echo view('templates/header', $data);
            echo view('templates/sidebar', $data);
            echo view('templates/topbar', $data);
            echo view('petugas_administrasi/prosesSurat', $data);
            echo view('templates/footer');
        } else {
            log_message('debug', 'PETUGAS_ADMINISTRASI PROSES SURAT - Form validation success. Processing data...');
            $status_final_permohonan = $this->request->getPost('status_final');
            $nomor_surat_keputusan = $this->request->getPost('nomorSetuju');
            $tanggal_surat_keputusan = $this->request->getPost('tgl_S');
            $catatan_penolakan_input = $this->request->getPost('catatan_penolakan');

            $data_update_permohonan = [
                'nomorSetuju'   => $nomor_surat_keputusan,
                'tgl_S'         => !empty($tanggal_surat_keputusan) ? $tanggal_surat_keputusan : null,
                'link'          => $this->request->getPost('link'),
                'catatan_penolakan' => ($status_final_permohonan == '4') ? $catatan_penolakan_input : null,
                'time_selesai'  => date("Y-m-d H:i:s"),
                'status'        => $status_final_permohonan,
            ];

            $nama_file_sk_baru = $data['permohonan']['file_surat_keputusan'];
            $upload_dir_sk = FCPATH . 'uploads/sk_penyelesaian/';

            if ($status_final_permohonan == '3' && $this->request->getFile('file_surat_keputusan')->isValid()) {
                $uploadConfigSK = $this->_get_upload_config($upload_dir_sk, 'pdf|jpg|png|jpeg', 2048, null, null);
                $uploadConfigSK['label'] = 'File Surat Keputusan';

                if (!$uploadConfigSK) {
                    $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Konfigurasi direktori upload SK gagal.</div>');
                    return redirect()->to(base_url('petugas_administrasi/prosesSurat/' . $id_permohonan));
                }

                $uploadedFileName = $this->_handle_file_upload('file_surat_keputusan', $uploadConfigSK, $nama_file_sk_baru);

                if ($uploadedFileName === false) {
                    echo view('templates/header', $data);
                    echo view('templates/sidebar', $data);
                    echo view('templates/topbar', $data);
                    echo view('petugas_administrasi/prosesSurat', $data);
                    echo view('templates/footer');
                    return;
                }
                $nama_file_sk_baru = $uploadedFileName;
            }
            if ($status_final_permohonan == '3') {
                $data_update_permohonan['file_surat_keputusan'] = $nama_file_sk_baru;
            } else {
                if (!empty($data['permohonan']['file_surat_keputusan']) && file_exists(FCPATH . 'uploads/sk_penyelesaian/' . $data['permohonan']['file_surat_keputusan'])) {
                    @unlink(FCPATH . 'uploads/sk_penyelesaian/' . $data['permohonan']['file_surat_keputusan']);
                }
                $data_update_permohonan['file_surat_keputusan'] = null;
            }

            $this->db->table('user_permohonan')->where('id', $id_permohonan)->update($data_update_permohonan);
            if ($status_final_permohonan == '3' && isset($data['lhp']['JumlahBenar']) && $data['lhp']['JumlahBenar'] > 0) {

                $jumlah_dipotong = (float)$data['lhp']['JumlahBenar'];
                $id_kuota_barang_terpakai = $data['permohonan']['id_kuota_barang_digunakan'];
                $id_perusahaan = $data['permohonan']['id_pers'];

                if ($id_kuota_barang_terpakai) {
                    $this->db->transBegin();
                    try {
                        $kuota_barang_saat_ini = $this->db->table('user_kuota_barang')->where('id_kuota_barang', $id_kuota_barang_terpakai)->get()->getRowArray();

                        if ($kuota_barang_saat_ini) {
                            $kuota_sebelum = (float)$kuota_barang_saat_ini['remaining_quota_barang'];
                            $kuota_sesudah = $kuota_sebelum - $jumlah_dipotong;

                            $this->db->table('user_kuota_barang')
                                     ->where('id_kuota_barang', $id_kuota_barang_terpakai)
                                     ->set('remaining_quota_barang', 'remaining_quota_barang - ' . $this->db->escape($jumlah_dipotong), FALSE)
                                     ->update();

                            $keterangan_log = 'Pemotongan kuota dari persetujuan impor. No. Surat: ' . ($data_update_permohonan['nomorSetuju'] ?? '-');
                            $this->_log_perubahan_kuota(
                                $id_perusahaan,
                                'pengurangan',
                                $jumlah_dipotong,
                                $kuota_sebelum,
                                $kuota_sesudah,
                                $keterangan_log,
                                $id_permohonan,
                                'permohonan_impor_disetujui',
                                $pa_user['id'], // dicatat oleh PA
                                $kuota_barang_saat_ini['nama_barang'],
                                $id_kuota_barang_terpakai
                            );
                        }

                        $this->db->transCommit();
                    } catch (\Exception $e) {
                        $this->db->transRollback();
                        log_message('error', 'Transaksi pemotongan kuota gagal: ' . $e->getMessage());
                        $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Gagal memotong kuota barang karena kesalahan database: ' . $e->getMessage() . '</div>');
                        return redirect()->to(base_url('petugas_administrasi/prosesSurat/' . $id_permohonan));
                    }
                }
            }

            $pesan_status_akhir = ($status_final_permohonan == '3') ? 'Disetujui' : 'Ditolak';
            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Status permohonan ID '.htmlspecialchars($id_permohonan).' telah berhasil diproses menjadi "'. $pesan_status_akhir .'"!</div>');
            return redirect()->to(base_url('petugas_administrasi/permohonanMasuk'));
        }
    }

    // Metode ini dihapus karena validasi upload di CI4 menggunakan rules langsung.
    // public function petugas_administrasi_check_file_sk_upload($str) {}

    // Metode ini dihapus karena CI4 memiliki rule 'valid_url' built-in.
    // public function _valid_url_format_check($str) {}

    private function _log_perubahan_kuota(
        $id_pers_param,
        $jenis_transaksi_param,
        $jumlah_param,
        $kuota_sebelum_param,
        $kuota_sesudah_param,
        $keterangan_param,
        $id_referensi_param = null,
        $tipe_referensi_param = null,
        $dicatat_oleh_user_id_param = null,
        $nama_barang_terkait_param = null,
        $id_kuota_barang_ref_param = null
    ) {
        $log_data = [
            'id_pers'                 => $id_pers_param,
            'nama_barang_terkait'     => $nama_barang_terkait_param,
            'id_kuota_barang_referensi'=> $id_kuota_barang_ref_param,
            'jenis_transaksi'         => $jenis_transaksi_param,
            'jumlah_perubahan'        => $jumlah_param,
            'sisa_kuota_sebelum'      => $kuota_sebelum_param,
            'sisa_kuota_setelah'      => $kuota_sesudah_param,
            'keterangan'              => $keterangan_param,
            'id_referensi_transaksi'  => $id_referensi_param,
            'tipe_referensi'          => $tipe_referensi_param,
            'dicatat_oleh_user_id'    => $dicatat_oleh_user_id_param,
            'tanggal_transaksi'       => date('Y-m-d H:i:s')
        ];

        if (!empty($log_data['id_pers']) && !empty($log_data['nama_barang_terkait'])) {
            $this->db->table('log_kuota_perusahaan')->insert($log_data);
        } else {
            log_message('error', 'Data log kuota tidak lengkap, tidak disimpan: ' . print_r($log_data, true));
        }
    }

    public function penunjukanPetugas($id_permohonan)
    {
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Penunjukan Petugas Pemeriksa';

        $builder = $this->db->table('user_permohonan up');
        $builder->select('up.*, upr.NamaPers');
        $builder->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $builder->where('up.id', $id_permohonan);
        $permohonan = $builder->get()->getRowArray();

        if (!$permohonan) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Permohonan tidak ditemukan!</div>');
            return redirect()->to(base_url('petugas_administrasi/permohonanMasuk'));
        }
        $data['permohonan'] = $permohonan;

        if (($permohonan['status'] ?? null) == '0' && !$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            $this->db->table('user_permohonan')->where('id', $id_permohonan)->update(['status' => '5']);
            $permohonan['status'] = '5';
            $data['permohonan']['status'] = '5';
            $this->session->setFlashdata('message_transient', '<div class="alert alert-info" role="alert">Status permohonan ID ' . htmlspecialchars($id_permohonan) . ' telah diubah menjadi "Diproses Admin". Lanjutkan dengan menunjuk petugas.</div>');
        }

        $data['list_petugas'] = $this->db->table('petugas')->orderBy('Nama', 'ASC')->get()->getResultArray();
        if (empty($data['list_petugas'])) {
            log_message('error', 'Tidak ada data petugas ditemukan di tabel petugas.');
        }

        $rules = [
            'petugas_id' => [
                'label' => 'Petugas Pemeriksa',
                'rules' => 'required|numeric'
            ],
            'nomor_surat_tugas' => [
                'label' => 'Nomor Surat Tugas',
                'rules' => 'required|trim'
            ],
            'tanggal_surat_tugas' => [
                'label' => 'Tanggal Surat Tugas',
                'rules' => 'required'
            ],
            'file_surat_tugas' => [
                'label' => 'File Surat Tugas',
                'rules' => 'max_size[file_surat_tugas,2048]|ext_in[file_surat_tugas,pdf,jpg,png,jpeg,doc,docx]',
                'errors' => [
                    'max_size' => 'Ukuran file {field} melebihi 2MB.',
                    'ext_in' => 'Tipe file {field} tidak valid.'
                ]
            ]
        ];
        if (empty($permohonan['FileSuratTugas']) && $this->request->getFile('file_surat_tugas')->isValid()) {
             $rules['file_surat_tugas']['rules'] = 'uploaded[file_surat_tugas]|' . $rules['file_surat_tugas']['rules'];
        }

        if (!$this->validate($rules)) {
            echo view('templates/header', $data);
            echo view('templates/sidebar', $data);
            echo view('templates/topbar', $data);
            echo view('petugas_administrasi/form_penunjukan_petugas', $data);
            echo view('templates/footer');
        } else {
            $update_data = [
                'petugas' => $this->request->getPost('petugas_id'),
                'NoSuratTugas' => $this->request->getPost('nomor_surat_tugas'),
                'TglSuratTugas' => $this->request->getPost('tanggal_surat_tugas'),
                'status' => '1',
                'WaktuPenunjukanPetugas' => date('Y-m-d H:i:s')
            ];

            $nama_file_surat_tugas = $permohonan['FileSuratTugas'] ?? null;
            $upload_dir_st = FCPATH . 'uploads/surat_tugas/';

            $uploadConfigST = [
                'upload_path' => $upload_dir_st,
                'allowed_types' => 'pdf|jpg|png|jpeg|doc|docx',
                'max_size' => 2048,
                'label' => 'File Surat Tugas'
            ];

            if ($this->request->getFile('file_surat_tugas')->isValid()) {
                $uploadedFileName = $this->_handle_file_upload('file_surat_tugas', $uploadConfigST, $nama_file_surat_tugas);
                if ($uploadedFileName === false) {
                    return redirect()->to(base_url('petugas_administrasi/penunjukanPetugas/' . $id_permohonan));
                }
                $nama_file_surat_tugas = $uploadedFileName;
            }
            $update_data['FileSuratTugas'] = $nama_file_surat_tugas;

            $this->db->table('user_permohonan')->where('id', $id_permohonan)->update($update_data);

            $updated_permohonan = $this->db->table('user_permohonan')->where('id', $id_permohonan)->get()->getRowArray();
            log_message('debug', 'PENUNJUKAN PETUGAS (PA) - Data Permohonan Setelah Update: ' . print_r($updated_permohonan, true));
            log_message('debug', 'PENUNJUKAN PETUGAS (PA) - Nilai petugas_id yang di-POST: ' . $this->request->getPost('petugas_id'));

            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Petugas pemeriksa berhasil ditunjuk untuk permohonan ID ' . htmlspecialchars($id_permohonan) . '. Status diubah menjadi "Penunjukan Pemeriksa".</div>');
            return redirect()->to(base_url('petugas_administrasi/permohonanMasuk'));
        }
    }

    public function daftar_pengajuan_kuota()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Daftar Pengajuan Kuota';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();

        $builder = $this->db->table('user_pengajuan_kuota upk');
        $builder->select('upk.*, upr.NamaPers, u.email as user_email');
        $builder->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $builder->join('user u', 'upk.id_pers = u.id', 'left');
        $builder->orderBy('FIELD(upk.status, "pending") DESC, upk.submission_date DESC');
        $data['pengajuan_kuota'] = $builder->get()->getResultArray();

        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('templates/topbar', $data);
        echo view('petugas_administrasi/daftar_pengajuan_kuota', $data);
        echo view('templates/footer');
    }

    public function proses_pengajuan_kuota($id_pengajuan)
    {
        log_message('debug', 'PETUGAS_ADMINISTRASI PROSES PENGAJUAN KUOTA - Method dipanggil untuk id_pengajuan: ' . $id_pengajuan);
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Proses Pengajuan Kuota';
        $pa_user = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        $data['user'] = $pa_user;

        $builder = $this->db->table('user_pengajuan_kuota upk');
        $builder->select('upk.*, upr.NamaPers, upr.initial_quota as initial_quota_umum_sebelum, upr.remaining_quota as remaining_quota_umum_sebelum, u.email as user_email');
        $builder->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $builder->join('user u', 'upk.id_pers = u.id', 'left');
        $builder->where('upk.id', $id_pengajuan);
        $data['pengajuan'] = $builder->get()->getRowArray();
        log_message('debug', 'PETUGAS_ADMINISTRASI PROSES PENGAJUAN KUOTA - Data pengajuan yang diambil: ' . print_r($data['pengajuan'], true));

        if (!$data['pengajuan'] || !in_array(($data['pengajuan']['status'] ?? null), ['pending', 'diproses'])) { // Tambahkan null coalescing
            $pesan_error_awal = 'Pengajuan kuota tidak ditemukan atau statusnya tidak memungkinkan untuk diproses (Status saat ini: ' . ($data['pengajuan']['status'] ?? 'Tidak Diketahui') . '). Hanya status "pending" atau "diproses" yang bisa dilanjutkan.';
            log_message('error', 'PETUGAS_ADMINISTRASI PROSES PENGAJUAN KUOTA - Validasi awal gagal: ' . $pesan_error_awal);
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">' . $pesan_error_awal . '</div>');
            return redirect()->to(base_url('petugas_administrasi/daftar_pengajuan_kuota'));
        }

        $rules = [
            'status_pengajuan' => [
                'label' => 'Status Pengajuan',
                'rules' => 'required|in_list[approved,rejected,diproses]'
            ],
            'admin_notes' => 'trim'
        ];

        if ($this->request->getPost('status_pengajuan') == 'approved') {
            $rules['approved_quota'] = [
                'label' => 'Kuota Disetujui',
                'rules' => 'trim|required|numeric|greater_than[0]'
            ];
            $rules['nomor_sk_petugas'] = [
                'label' => 'Nomor Surat Keputusan',
                'rules' => 'trim|required|max_length[100]'
            ];
            $rules['tanggal_sk_petugas'] = [
                'label' => 'Tanggal Surat Keputusan',
                'rules' => 'trim|required'
            ];
            if (empty($data['pengajuan']['file_sk_petugas']) && !$this->request->getFile('file_sk_petugas')->isValid()) {
                $rules['file_sk_petugas'] = [
                    'label' => 'File SK Petugas',
                    'rules' => 'uploaded[file_sk_petugas]|max_size[file_sk_petugas,2048]|ext_in[file_sk_petugas,pdf,jpg,png,jpeg]',
                    'errors' => [
                        'uploaded' => 'Kolom {field} wajib diisi.'
                    ]
                ];
            } else if ($this->request->getFile('file_sk_petugas')->isValid()) {
                 $rules['file_sk_petugas'] = [
                    'label' => 'File SK Petugas',
                    'rules' => 'max_size[file_sk_petugas,2048]|ext_in[file_sk_petugas,pdf,jpg,png,jpeg]',
                ];
            }

        } else {
            $rules['approved_quota'] = 'trim|numeric';
            $rules['nomor_sk_petugas'] = 'trim|max_length[100]';
            $rules['tanggal_sk_petugas'] = 'trim';
             if ($this->request->getFile('file_sk_petugas')->isValid()) {
                 $rules['file_sk_petugas'] = [
                    'label' => 'File SK Petugas',
                    'rules' => 'max_size[file_sk_petugas,2048]|ext_in[file_sk_petugas,pdf,jpg,png,jpeg]',
                ];
            }
        }


        if (!$this->validate($rules)) {
            log_message('debug', 'PETUGAS_ADMINISTRASI PROSES PENGAJUAN KUOTA - Validasi Form Gagal. Errors: ' . json_encode($this->validator->getErrors()) . ' POST Data: ' . print_r($this->request->getPost(), true));
            echo view('templates/header', $data);
            echo view('templates/sidebar', $data);
            echo view('templates/topbar', $data);
            echo view('petugas_administrasi/proses_pengajuan_kuota_form', $data);
            echo view('templates/footer', $data);
        } else {
            log_message('debug', 'PETUGAS_ADMINISTRASI PROSES PENGAJUAN KUOTA - Validasi Form Sukses. Memproses data...');
            $status_pengajuan = $this->request->getPost('status_pengajuan');
            $approved_quota_input = ($status_pengajuan == 'approved') ? (float)$this->request->getPost('approved_quota') : 0;
            $nomor_sk_petugas = $this->request->getPost('nomor_sk_petugas');
            $tanggal_sk_petugas = $this->request->getPost('tanggal_sk_petugas');
            $admin_notes = $this->request->getPost('admin_notes');

            $data_update_pengajuan = [
                'status' => $status_pengajuan,
                'admin_notes' => $admin_notes,
                'processed_date' => date('Y-m-d H:i:s'),
                'nomor_sk_petugas' => $nomor_sk_petugas,
                'tanggal_sk_petugas' => !empty($tanggal_sk_petugas) ? $tanggal_sk_petugas : null,
                'approved_quota' => $approved_quota_input
            ];

            $nama_file_sk = $data['pengajuan']['file_sk_petugas'] ?? null;
            $upload_dir_sk = FCPATH . 'uploads/sk_kuota/';

            if (($status_pengajuan == 'approved' || $status_pengajuan == 'rejected') && $this->request->getFile('file_sk_petugas')->isValid()) {
                $uploadConfigSK = [
                    'upload_path' => $upload_dir_sk,
                    'allowed_types' => 'pdf|jpg|png|jpeg',
                    'max_size' => 2048,
                    'label' => 'File SK Petugas'
                ];

                $uploadedFileName = $this->_handle_file_upload('file_sk_petugas', $uploadConfigSK, $nama_file_sk);
                if ($uploadedFileName === false) {
                    return redirect()->to(base_url('petugas_administrasi/proses_pengajuan_kuota/' . $id_pengajuan));
                }
                $nama_file_sk = $uploadedFileName;
            }
            $data_update_pengajuan['file_sk_petugas'] = $nama_file_sk;

            $this->db->table('user_pengajuan_kuota')->where('id', $id_pengajuan)->update($data_update_pengajuan);
            log_message('debug', 'PETUGAS_ADMINISTRASI PROSES PENGAJUAN KUOTA - user_pengajuan_kuota diupdate. Affected: ' . $this->db->affectedRows());

            if ($status_pengajuan == 'approved' && $approved_quota_input > 0) {
                $id_pers_terkait = $data['pengajuan']['id_pers'];
                $nama_barang_diajukan = $data['pengajuan']['nama_barang_kuota'];

                if ($id_pers_terkait && !empty($nama_barang_diajukan)) {
                    $data_kuota_barang = [
                        'id_pers' => $id_pers_terkait,
                        'id_pengajuan_kuota' => $id_pengajuan,
                        'nama_barang' => $nama_barang_diajukan,
                        'initial_quota_barang' => $approved_quota_input,
                        'remaining_quota_barang' => $approved_quota_input,
                        'nomor_skep_asal' => $nomor_sk_petugas,
                        'tanggal_skep_asal' => !empty($tanggal_sk_petugas) ? $tanggal_sk_petugas : null,
                        'status_kuota_barang' => 'active',
                        'dicatat_oleh_user_id' => $pa_user['id'],
                        'waktu_pencatatan' => date('Y-m-d H:i:s')
                    ];
                    $this->db->table('user_kuota_barang')->insert($data_kuota_barang);
                    $id_kuota_barang_baru = $this->db->insertID();
                    log_message('info', 'PETUGAS_ADMINISTRASI PROSES PENGAJUAN KUOTA - Data kuota barang baru disimpan. ID: ' . $id_kuota_barang_baru . ' untuk barang: ' . $nama_barang_diajukan);

                    if ($id_kuota_barang_baru) {
                        $this->_log_perubahan_kuota(
                            $id_pers_terkait, 'penambahan', $approved_quota_input,
                            0,
                            $approved_quota_input,
                            'Persetujuan Pengajuan Kuota. Barang: ' . $nama_barang_diajukan . '. No. SK: ' . ($nomor_sk_petugas ?: '-'),
                            $id_pengajuan, 'pengajuan_kuota_disetujui', $pa_user['id'],
                            $nama_barang_diajukan, $id_kuota_barang_baru
                        );
                    }
                } else {
                    log_message('error', 'PETUGAS_ADMINISTRASI PROSES PENGAJUAN KUOTA - Gagal menambah kuota barang: id_pers atau nama_barang_kuota kosong. ID Pers: ' . $id_pers_terkait . ', Nama Barang: ' . $nama_barang_diajukan);
                }
            }

            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Pengajuan kuota telah berhasil diproses!</div>');
            return redirect()->to(base_url('petugas_administrasi/daftar_pengajuan_kuota'));
        }
    }

    public function print_pengajuan_kuota($id_pengajuan)
    {
        $data['title'] = 'Detail Proses Pengajuan Kuota';
        $data['user_login'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();

        $builder = $this->db->table('user_pengajuan_kuota upk');
        $builder->select('upk.*, upr.NamaPers, upr.npwp AS npwp_perusahaan, upr.alamat as alamat_perusahaan, upr.pic, upr.jabatanPic, u.email AS user_email, u.name AS user_name_pengaju, u.image AS logo_perusahaan_file');
        $builder->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $builder->join('user u', 'upk.id_pers = u.id', 'left');
        $builder->where('upk.id', $id_pengajuan);
        $data['pengajuan'] = $builder->get()->getRowArray();

        if (!$data['pengajuan']) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Data pengajuan kuota tidak ditemukan.</div>');
            return redirect()->to(base_url('petugas_administrasi/daftar_pengajuan_kuota'));
        }

        $data['user'] = $this->db->table('user')->where('id', $data['pengajuan']['id_pers'])->get()->getRowArray();
        $data['user_perusahaan'] = $this->db->table('user_perusahaan')->where('id_pers', $data['pengajuan']['id_pers'])->get()->getRowArray();

        echo view('user/FormPengajuanKuota_print', $data);
    }

    public function detailPengajuanKuotaAdmin($id_pengajuan)
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Detail Proses Pengajuan Kuota';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();

        $builder = $this->db->table('user_pengajuan_kuota upk');
        $builder->select('upk.*, upr.NamaPers, upr.npwp AS npwp_perusahaan, upr.alamat as alamat_perusahaan, upr.pic, upr.jabatanPic, u.email AS user_email_pemohon, u.name AS nama_pemohon');
        $builder->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $builder->join('user u', 'upk.id_pers = u.id', 'left');
        $builder->where('upk.id', $id_pengajuan);
        $data['pengajuan'] = $builder->get()->getRowArray();

        if (!$data['pengajuan']) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Data pengajuan kuota tidak ditemukan.</div>');
            return redirect()->to(base_url('petugas_administrasi/daftar_pengajuan_kuota'));
        }

        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('templates/topbar', $data);
        echo view('petugas_administrasi/detail_pengajuan_kuota_view', $data);
        echo view('templates/footer');
    }

    public function download_sk_kuota_admin($id_pengajuan)
    {
        helper('download');
        $pengajuan = $this->db->table('user_pengajuan_kuota')->where('id', $id_pengajuan)->get()->getRowArray();

        if ($pengajuan && !empty($pengajuan['file_sk_petugas'])) {
            $file_name = $pengajuan['file_sk_petugas'];
            $file_path = FCPATH . 'uploads/sk_kuota/' . $file_name;

            if (file_exists($file_path)) {
                return $this->response->download($file_path, null);
            } else {
                log_message('error', 'Petugas_administrasi: File SK Kuota tidak ditemukan di path: ' . $file_path . ' untuk id_pengajuan: ' . $id_pengajuan);
                $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">File Surat Keputusan tidak ditemukan di server.</div>');
                return redirect()->to(base_url('petugas_administrasi/daftar_pengajuan_kuota'));
            }
        } else {
            $this->session->setFlashdata('message', '<div class="alert alert-warning" role="alert">Surat Keputusan belum tersedia untuk pengajuan ini.</div>');
            return redirect()->to(base_url('petugas_administrasi/daftar_pengajuan_kuota'));
        }
    }

    public function histori_kuota_perusahaan($id_pers = 0)
    {
        log_message('debug', 'PETUGAS_ADMINISTRASI HISTORI KUOTA - Method dipanggil dengan id_pers: ' . $id_pers);

        if ($id_pers == 0 || !is_numeric($id_pers)) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">ID Perusahaan tidak valid.</div>');
            return redirect()->to(base_url('petugas_administrasi/monitoring_kuota'));
        }

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Histori & Detail Kuota Perusahaan';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();

        $builder = $this->db->table('user_perusahaan up');
        $builder->select('up.id_pers, up.NamaPers, up.npwp, u.email as email_kontak, u.name as nama_kontak_user');
        $builder->join('user u', 'up.id_pers = u.id', 'left');
        $builder->where('up.id_pers', $id_pers);
        $data['perusahaan'] = $builder->get()->getRowArray();
        log_message('debug', 'PETUGAS_ADMINISTRASI HISTORI KUOTA - Data Perusahaan: ' . print_r($data['perusahaan'], true));

        if (!$data['perusahaan']) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Data perusahaan tidak ditemukan untuk ID: ' . $id_pers . '</div>');
            return redirect()->to(base_url('petugas_administrasi/monitoring_kuota'));
        }
        $data['id_pers_untuk_histori'] = $id_pers;

        $builder = $this->db->table('user_kuota_barang ukb');
        $builder->select('ukb.*');
        $builder->where('ukb.id_pers', $id_pers);
        $builder->orderBy('ukb.nama_barang ASC, ukb.waktu_pencatatan DESC');
        $data['daftar_kuota_barang_perusahaan'] = $builder->get()->getResultArray();
        log_message('debug', 'PETUGAS_ADMINISTRASI HISTORI KUOTA - Query Daftar Kuota Barang: ' . $this->db->getLastQuery()->getQueryString());
        log_message('debug', 'PETUGAS_ADMINISTRASI HISTORI KUOTA - Data Daftar Kuota Barang: ' . print_r($data['daftar_kuota_barang_perusahaan'], true));

        $builder = $this->db->table('log_kuota_perusahaan lk');
        $builder->select('lk.*, u_admin.name as nama_pencatat');
        $builder->join('user u_admin', 'lk.dicatat_oleh_user_id = u_admin.id', 'left');
        $builder->where('lk.id_pers', $id_pers);
        $builder->orderBy('lk.tanggal_transaksi', 'DESC');
        $builder->orderBy('lk.id_log', 'DESC');
        $data['histori_kuota_transaksi'] = $builder->get()->getResultArray();
        log_message('debug', 'PETUGAS_ADMINISTRASI HISTORI KUOTA - Query Log Transaksi: ' . $this->db->getLastQuery()->getQueryString());
        log_message('debug', 'PETUGAS_ADMINISTRASI HISTORI KUOTA - Data Log Transaksi: ' . print_r($data['histori_kuota_transaksi'], true));

        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('templates/topbar', $data);
        echo view('petugas_administrasi/histori_kuota_perusahaan_view', $data);
        echo view('templates/footer');
    }

    public function detail_permohonan_admin($id_permohonan = 0)
    {
        log_message('debug', 'PETUGAS_ADMINISTRASI DETAIL PERMOHONAN - Method dipanggil dengan id_permohonan: ' . $id_permohonan);

        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid.</div>');
            log_message('error', 'PETUGAS_ADMINISTRASI DETAIL PERMOHONAN - ID Permohonan tidak valid: ' . $id_permohonan);
            return redirect()->to(base_url('petugas_administrasi/permohonanMasuk'));
        }

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Detail Permohonan Impor ID: ' . htmlspecialchars($id_permohonan);
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();

        $builder = $this->db->table('user_permohonan up');
        $builder->select('up.*, up.file_bc_manifest, upr.NamaPers, upr.npwp, u_pemohon.name as nama_pengaju_permohonan, u_pemohon.email as email_pengaju_permohonan, u_petugas.name as nama_petugas_pemeriksa');
        $builder->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $builder->join('user u_pemohon', 'upr.id_pers = u_pemohon.id', 'left');
        $builder->join('petugas p', 'up.petugas = p.id', 'left');
        $builder->join('user u_petugas', 'p.id_user = u_petugas.id', 'left');
        $builder->where('up.id', $id_permohonan);
        $data['permohonan_detail'] = $builder->get()->getRowArray();

        log_message('debug', 'PETUGAS_ADMINISTRASI DETAIL PERMOHONAN - Query Permohonan: ' . $this->db->getLastQuery()->getQueryString());
        log_message('debug', 'PETUGAS_ADMINISTRASI DETAIL PERMOHONAN - Data Permohonan: ' . print_r($data['permohonan_detail'], true));

        if (!$data['permohonan_detail']) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Data permohonan dengan ID ' . htmlspecialchars($id_permohonan) . ' tidak ditemukan.</div>');
            log_message('error', 'PETUGAS_ADMINISTRASI DETAIL PERMOHONAN - Data permohonan tidak ditemukan untuk ID: ' . $id_permohonan);
            return redirect()->to(base_url('petugas_administrasi/permohonanMasuk'));
        }

        $data['lhp_detail'] = $this->db->table('lhp')->where('id_permohonan', $id_permohonan)->get()->getRowArray();
        log_message('debug', 'PETUGAS_ADMINISTRASI DETAIL PERMOHONAN - Data LHP: ' . print_r($data['lhp_detail'], true));

        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('templates/topbar', $data);
        echo view('petugas_administrasi/detail_permohonan_view', $data);
        echo view('templates/footer');
    }

    public function hapus_permohonan($id_permohonan = 0)
    {
        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid untuk dihapus.</div>');
            return redirect()->to(base_url('petugas_administrasi/permohonanMasuk'));
        }

        $permohonan = $this->db->table('user_permohonan')->where('id', $id_permohonan)->get()->getRowArray();

        if (!$permohonan) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Permohonan dengan ID '.htmlspecialchars($id_permohonan).' tidak ditemukan.</div>');
            return redirect()->to(base_url('petugas_administrasi/permohonanMasuk'));
        }

        $upload_dir_bc_manifest = FCPATH . 'uploads/bc_manifest/';
        if (!empty($permohonan['file_bc_manifest']) && file_exists($upload_dir_bc_manifest . $permohonan['file_bc_manifest'])) {
            if (@unlink($upload_dir_bc_manifest . $permohonan['file_bc_manifest'])) {
                log_message('info', 'File BC Manifest ' . $permohonan['file_bc_manifest'] . ' berhasil dihapus untuk permohonan ID: ' . $id_permohonan . ' oleh Petugas Administrasi ID: ' . $this->session->get('user_id'));
            } else {
                log_message('error', 'Gagal menghapus file BC Manifest ' . $permohonan['file_bc_manifest'] . ' untuk permohonan ID: ' . $id_permohonan);
            }
        }

        if ($this->db->table('user_permohonan')->where('id', $id_permohonan)->delete()) {
            log_message('info', 'Permohonan ID ' . $id_permohonan . ' berhasil dihapus oleh Petugas Administrasi ID: ' . $this->session->get('user_id'));
            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Permohonan dengan ID Aju '.htmlspecialchars($id_permohonan).' berhasil dihapus.</div>');
        } else {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Gagal menghapus permohonan. Silakan coba lagi.</div>');
        }
        return redirect()->to(base_url('petugas_administrasi/permohonanMasuk'));
    }

    public function edit_permohonan($id_permohonan = 0)
    {
        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid.</div>');
            return redirect()->to(base_url('petugas_administrasi/permohonanMasuk'));
        }

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Edit Permohonan (Petugas Administrasi)';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();

        $builder = $this->db->table('user_permohonan up');
        $builder->select('up.*, upr.NamaPers as NamaPerusahaanPemohon');
        $builder->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $builder->where('up.id', $id_permohonan);
        $permohonan = $builder->get()->getRowArray();

        if (!$permohonan) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Permohonan tidak ditemukan.</div>');
            return redirect()->to(base_url('petugas_administrasi/permohonanMasuk'));
        }

        $data['permohonan_edit'] = $permohonan;
        $data['user_perusahaan_pemohon'] = $this->db->table('user_perusahaan')->where('id_pers', $permohonan['id_pers'])->get()->getRowArray();

        $id_user_pemohon = $permohonan['id_pers'];
        $builder = $this->db->table('user_kuota_barang');
        $builder->select('id_kuota_barang, nama_barang, remaining_quota_barang, nomor_skep_asal');
        $builder->where('id_pers', $id_user_pemohon);
        $builder->groupStart();
        $builder->where('remaining_quota_barang >', 0);
        if (isset($permohonan['id_kuota_barang_digunakan'])) {
            $builder->orWhere('id_kuota_barang', $permohonan['id_kuota_barang_digunakan']);
        }
        $builder->groupEnd();
        $builder->where('status_kuota_barang', 'active');
        $builder->orderBy('nama_barang', 'ASC');
        $data['list_barang_berkuota'] = $builder->get()->getResultArray();

        $rules = [
            'nomorSurat' => 'trim|required|max_length[100]',
            'TglSurat' => 'trim|required',
            'NamaBarang' => 'trim|required',
            'id_kuota_barang_selected' => 'trim|required|numeric',
            'JumlahBarang' => 'trim|required|numeric|greater_than[0]'
        ];

        if ($this->request->getFile('file_bc_manifest_pa_edit')->isValid()) {
            $rules['file_bc_manifest_pa_edit'] = [
                'label' => 'File BC 1.1 / Manifest (Baru)',
                'rules' => 'max_size[file_bc_manifest_pa_edit,2048]|ext_in[file_bc_manifest_pa_edit,pdf]',
                'errors' => [
                    'max_size' => 'Ukuran file {field} melebihi 2MB.',
                    'ext_in' => 'Tipe file {field} tidak valid (Hanya PDF).'
                ]
            ];
        }

        if (!$this->validate($rules)) {
            echo view('templates/header', $data);
            echo view('templates/sidebar', $data);
            echo view('templates/topbar', $data);
            echo view('petugas_administrasi/form_edit_permohonan', $data);
            echo view('templates/footer');
        } else {
            $id_kuota_barang_dipilih = (int)$this->request->getPost('id_kuota_barang_selected');
            $nama_barang_input_form = $this->request->getPost('NamaBarang');
            $jumlah_barang_dimohon = (float)$this->request->getPost('JumlahBarang');

            $kuota_valid_db = $this->db->table('user_kuota_barang')->where([
                'id_kuota_barang' => $id_kuota_barang_dipilih,
                'id_pers' => $id_user_pemohon,
                'status_kuota_barang' => 'active'
            ])->get()->getRowArray();

            if (!$kuota_valid_db) {
                 $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Data kuota barang tidak valid.</div>');
                 return redirect()->to(base_url('petugas_administrasi/edit_permohonan/' . $id_permohonan));
            }
            if (($kuota_valid_db['nama_barang'] ?? '') != $nama_barang_input_form) { // Tambahkan null coalescing
                $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Nama barang tidak sesuai dengan kuota yang dipilih.</div>');
                return redirect()->to(base_url('petugas_administrasi/edit_permohonan/' . $id_permohonan));
            }

            $sisa_kuota_efektif_untuk_validasi = (float)($kuota_valid_db['remaining_quota_barang'] ?? 0); // Tambahkan null coalescing
            if (($permohonan['id_kuota_barang_digunakan'] ?? null) == $id_kuota_barang_dipilih) { // Tambahkan null coalescing
                $sisa_kuota_efektif_untuk_validasi += (float)($permohonan['JumlahBarang'] ?? 0); // Tambahkan null coalescing
            }
            if ($jumlah_barang_dimohon > $sisa_kuota_efektif_untuk_validasi) {
                $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Jumlah barang dimohon melebihi sisa kuota efektif.</div>');
                return redirect()->to(base_url('petugas_administrasi/edit_permohonan/' . $id_permohonan));
            }

            $nama_file_bc_manifest_update = $permohonan['file_bc_manifest'];
            $upload_dir_bc = FCPATH . 'uploads/bc_manifest/';

            if ($this->request->getFile('file_bc_manifest_pa_edit')->isValid()) {
                $uploadConfigBC = $this->_get_upload_config($upload_dir_bc, 'pdf', 2048);
                $uploadConfigBC['label'] = 'File BC 1.1 / Manifest';

                if (!$uploadConfigBC) {
                     $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Konfigurasi upload file BC gagal.</div>');
                     return redirect()->to(base_url('petugas_administrasi/edit_permohonan/' . $id_permohonan));
                }

                $uploadedFileName = $this->_handle_file_upload('file_bc_manifest_pa_edit', $uploadConfigBC, $nama_file_bc_manifest_update);
                if ($uploadedFileName === false) {
                    echo view('templates/header', $data);
                    echo view('templates/sidebar', $data);
                    echo view('templates/topbar', $data);
                    echo view('petugas_administrasi/form_edit_permohonan', $data);
                    echo view('templates/footer');
                    return;
                }
                $nama_file_bc_manifest_update = $uploadedFileName;
            }

            $data_update = [
                'nomorSurat'    => $this->request->getPost('nomorSurat'),
                'TglSurat'      => $this->request->getPost('TglSurat'),
                'NamaBarang'    => $nama_barang_input_form,
                'JumlahBarang'  => $jumlah_barang_dimohon,
                'id_kuota_barang_digunakan' => $id_kuota_barang_dipilih,
                'NoSkep'        => $kuota_valid_db['nomor_skep_asal'],
                'file_bc_manifest' => $nama_file_bc_manifest_update,
            ];

            $this->db->table('user_permohonan')->where('id', $id_permohonan)->update($data_update);
            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Permohonan berhasil diupdate oleh Petugas Administrasi.</div>');
            return redirect()->to(base_url('petugas_administrasi/detail_permohonan_admin/' . $id_permohonan));
        }
    }

    // Metode ini dihapus karena validasi upload di CI4 menggunakan rules langsung.
    // public function pa_check_file_bc_manifest_upload($str) {}

    public function tolak_permohonan_awal($id_permohonan = 0)
    {
        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid.</div>');
            return redirect()->to(base_url('petugas_administrasi/permohonanMasuk')); // Mengubah redirect ke controller sendiri
        }

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Formulir Penolakan Permohonan';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();

        $builder = $this->db->table('user_permohonan up');
        $builder->select('up.id, up.nomorSurat, upr.NamaPers, up.status');
        $builder->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $builder->where('up.id', $id_permohonan);
        $data['permohonan'] = $builder->get()->getRowArray();


        if (!$data['permohonan'] || ($data['permohonan']['status'] ?? null) != '0') { // Tambahkan null coalescing
            $this->session->setFlashdata('message', '<div class="alert alert-warning" role="alert">Permohonan ini tidak ditemukan atau statusnya bukan "Baru Masuk" sehingga tidak bisa ditolak langsung.</div>');
            return redirect()->to(base_url('petugas_administrasi/permohonanMasuk')); // Mengubah redirect ke controller sendiri
        }

        $rules = [
            'alasan_penolakan' => 'trim|required'
        ];

        if (!$this->validate($rules)) {
            echo view('templates/header', $data);
            echo view('templates/sidebar', $data);
            echo view('templates/topbar', $data);
            echo view('petugas_administrasi/form_tolak_permohonan_view', $data);
            echo view('templates/footer');
        } else {
            $alasan_penolakan = $this->request->getPost('alasan_penolakan', true);

            $update_data = [
                'status' => '6',
                'catatan_penolakan' => $alasan_penolakan,
                'time_selesai' => date('Y-m-d H:i:s')
            ];

            $this->db->table('user_permohonan')->where('id', $id_permohonan)->update($update_data);

            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Permohonan ID ' . htmlspecialchars($id_permohonan) . ' berhasil ditolak.</div>');
            return redirect()->to(base_url('petugas_administrasi/permohonanMasuk')); // Mengubah redirect ke controller sendiri
        }
    }
}
