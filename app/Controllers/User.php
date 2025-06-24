<?php

namespace App\Controllers;

use App\Libraries\TobaUploader;
use App\Controllers\BaseController;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;

class User extends BaseController
{
    protected $session;
    protected $db;
    protected $validation;

    /**
     * Initialize controller properties.
     */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // Initialize services
        $this->session = \Config\Services::session();
        $this->db = \Config\Database::connect();
        $this->validation = \Config\Services::validation();

        // Load helpers
        helper(['url', 'form', 'download']);

        // Check authentication and authorization on every request
        $this->_check_auth();
    }

    /**
     * Check user authentication, role, and active status.
     * This method acts as a private middleware for this controller.
     */
    private function _check_auth()
    {
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Mohon login untuk melanjutkan.</div>');
            return redirect()->to('auth');
        }

        if ($this->session->get('role_id') != 2) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda tidak diotorisasi untuk mengakses halaman ini.</div>');
            switch ($this->session->get('role_id')) {
                case 1:
                    return redirect()->to('admin');
                case 3:
                    return redirect()->to('petugas');
                case 4:
                    return redirect()->to('monitoring');
                default:
                    return redirect()->to('auth/blocked');
            }
        }

        $user_is_active = $this->session->get('is_active');
        $current_method = $this->request->getUri()->getSegment(2);
        $allowed_inactive_methods = ['edit', 'logout', 'ganti_password', 'force_change_password_page', 'downloadFile'];

        if ($user_is_active == 0 && !in_array($current_method, $allowed_inactive_methods)) {
            if (!($this->request->getUri()->getSegment(1) == 'user' && $current_method == 'edit')) {
                $this->session->setFlashdata('message', '<div class="alert alert-warning" role="alert">Akun Anda belum aktif. Mohon lengkapi profil perusahaan Anda.</div>');
                return redirect()->to('user/edit');
            }
        }
    }
    
    /**
     * Setup Multi-Factor Authentication page.
     */
    public function setup_mfa()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Setup Multi-Factor Authentication';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();

        $google2fa = new Google2FA();

        if (empty($data['user']['google2fa_secret'])) {
            $secretKey = $google2fa->generateSecretKey();
            $this->db->table('user')->where('id', $data['user']['id'])->update(['google2fa_secret' => $secretKey]);
        } else {
            $secretKey = $data['user']['google2fa_secret'];
        }

        $qrCodeUrl = $google2fa->getQRCodeUrl('Repack Papin', $data['user']['email'], $secretKey);

        $renderer = new ImageRenderer(new RendererStyle(400), new SvgImageBackEnd());
        $writer = new Writer($renderer);
        $qrCodeImage = $writer->writeString($qrCodeUrl);
        
        $data['qr_code_data_uri'] = 'data:image/svg+xml;base64,' . base64_encode($qrCodeImage);
        $data['secret_key'] = $secretKey;

        return view('user/mfa_setup', $data);
    }

    /**
     * Verify the MFA one-time password.
     */
    public function verify_mfa()
    {
        $userId = $this->session->get('user_id');
        $user = $this->db->table('user')->where('id', $userId)->get()->getRowArray();
        $secret = $user['google2fa_secret'];

        $oneTimePassword = $this->request->getPost('one_time_password');

        $google2fa = new Google2FA();
        $isValid = $google2fa->verifyKey($secret, $oneTimePassword);

        if ($isValid) {
            $this->db->table('user')->where('id', $userId)->update(['is_mfa_enabled' => 1]);
            $this->session->set('mfa_verified', true);
            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Autentikasi Dua Faktor (MFA) berhasil diaktifkan!</div>');
            return redirect()->to('user/index');
        } else {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Kode verifikasi salah. Silakan coba lagi.</div>');
            return redirect()->to('user/setup_mfa');
        }
    }

    /**
     * Reset MFA for the current user.
     */
    public function reset_mfa()
    {
        $user_id = $this->session->get('user_id');
        $this->db->table('user')->where('id', $user_id)->update(['is_mfa_enabled'   => 0, 'google2fa_secret' => null]);
        $this->session->remove('mfa_verified');
        $this->session->setFlashdata('message', '<div class="alert alert-info" role="alert">MFA Anda telah dinonaktifkan. Silakan lakukan pengaturan ulang.</div>');
        return redirect()->to('user/setup_mfa');
    }
    
    /**
     * User dashboard page.
     */
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
            $agregat_kuota = $this->db->table('user_kuota_barang')->selectSum('initial_quota_barang', 'total_initial')->selectSum('remaining_quota_barang', 'total_remaining')->where('id_pers', $id_user_login)->get()->getRowArray();
            if ($agregat_kuota) {
                $data['total_kuota_awal_disetujui_barang'] = (float)($agregat_kuota['total_initial'] ?? 0);
                $data['total_sisa_kuota_barang'] = (float)($agregat_kuota['total_remaining'] ?? 0);
                $data['total_kuota_terpakai_barang'] = $data['total_kuota_awal_disetujui_barang'] - $data['total_sisa_kuota_barang'];
            }
            $data['daftar_kuota_per_barang'] = $this->db->table('user_kuota_barang')->select('nama_barang, initial_quota_barang, remaining_quota_barang, nomor_skep_asal')->where('id_pers', $id_user_login)->where('status_kuota_barang', 'active')->orderBy('nama_barang', 'ASC')->get()->getResultArray();
            $data['recent_permohonan'] = $this->db->table('user_permohonan')->select('id, nomorSurat, TglSurat, NamaBarang, JumlahBarang, status, time_stamp')->where('id_pers', $id_user_login)->orderBy('time_stamp', 'DESC')->limit(5)->get()->getResultArray();
        } else {
            $data['recent_permohonan'] = [];
            if ($data['user']['is_active'] == 1) { 
                $this->session->setFlashdata('message_dashboard', '<div class="alert alert-info" role="alert">Selamat datang! Mohon lengkapi profil perusahaan Anda di menu "Edit Profile & Perusahaan" untuk dapat menggunakan semua fitur.</div>');
            }
        }
        
        return view('user/dashboard', $data);
    }
    
    /**
     * Edit profile and company data page and handle form submission using TobaUploader.
     */
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
            $data['daftar_kuota_barang_user'] = $this->db->table('user_kuota_barang')->select('nama_barang, initial_quota_barang, remaining_quota_barang, nomor_skep_asal, tanggal_skep_asal, status_kuota_barang')->where('id_pers', $id_user_login)->orderBy('nama_barang', 'ASC')->get()->getResultArray();
        } else {
            $data['daftar_kuota_barang_user'] = [];
        }

        $rules = [
            'NamaPers' => ['label' => 'Nama Perusahaan', 'rules' => 'required|max_length[100]'],
            'npwp' => ['label' => 'NPWP', 'rules' => 'required|regex_match[/^[0-9]{2}\.[0-9]{3}\.[0-9]{3}\.[0-9]{1}-[0-9]{3}\.[0-9]{3}$/]', 'errors' => ['regex_match' => 'Format NPWP tidak valid. Contoh: 00.000.000.0-000.000']],
            'alamat' => ['label' => 'Alamat Perusahaan', 'rules' => 'required|max_length[255]'],
            'telp' => ['label' => 'Nomor Telepon Perusahaan', 'rules' => 'required|numeric|max_length[15]'],
            'pic' => ['label' => 'Nama PIC', 'rules' => 'required|max_length[100]'],
            'jabatanPic' => ['label' => 'Jabatan PIC', 'rules' => 'required|max_length[100]'],
            'NoSkepFasilitas' => ['label' => 'No. SKEP Fasilitas Umum', 'rules' => 'permit_empty|max_length[100]']
        ];

        if ($is_activating && ($this->request->getPost('initial_skep_no') || $this->request->getPost('initial_nama_barang'))) {
            $rules['initial_skep_no'] = ['label' => 'Nomor SKEP Kuota Awal', 'rules' => 'required|max_length[100]'];
            $rules['initial_skep_tgl'] = ['label' => 'Tanggal SKEP Kuota Awal', 'rules' => 'required'];
            $rules['initial_nama_barang'] = ['label' => 'Nama Barang Kuota Awal', 'rules' => 'required|max_length[100]'];
            $rules['initial_kuota_jumlah'] = ['label' => 'Jumlah Kuota Awal', 'rules' => 'required|numeric|greater_than[0]'];
        }

        if ($is_activating) {
            $rules['ttd'] = ['label' => 'Tanda Tangan PIC', 'rules' => 'uploaded[ttd]|max_size[ttd,1024]|ext_in[ttd,jpg,png,jpeg,pdf]'];
        } else if ($this->request->getFile('ttd') && $this->request->getFile('ttd')->isValid()) {
            $rules['ttd'] = ['label' => 'Tanda Tangan PIC', 'rules' => 'max_size[ttd,1024]|ext_in[ttd,jpg,png,jpeg,pdf]'];
        }

        if ($this->request->getFile('profile_image') && $this->request->getFile('profile_image')->isValid()) {
            $rules['profile_image'] = ['label' => 'Gambar Profil/Logo', 'rules' => 'max_size[profile_image,1024]|ext_in[profile_image,jpg,png,jpeg,gif]|max_dims[profile_image,1024,1024]'];
        }

        if ($this->request->getFile('file_skep_fasilitas') && $this->request->getFile('file_skep_fasilitas')->isValid()) {
            $rules['file_skep_fasilitas'] = ['label' => 'File SKEP Fasilitas', 'rules' => 'max_size[file_skep_fasilitas,2048]|ext_in[file_skep_fasilitas,pdf,jpg,jpeg,png]'];
        }

        if ($is_activating && $this->request->getFile('initial_skep_file') && $this->request->getFile('initial_skep_file')->isValid()) {
            $rules['initial_skep_file'] = ['label' => 'File SKEP Kuota Awal', 'rules' => 'max_size[initial_skep_file,2048]|ext_in[initial_skep_file,pdf,jpg,jpeg,png]'];
        }

        if (!$this->validate($rules)) {
            return view('user/edit-profile', $data);
        } else {
            $tobaUploader = new TobaUploader();
            $data_perusahaan = [
                'NamaPers' => $this->request->getPost('NamaPers'), 'npwp' => $this->request->getPost('npwp'),
                'alamat' => $this->request->getPost('alamat'), 'telp' => $this->request->getPost('telp'),
                'pic' => $this->request->getPost('pic'), 'jabatanPic' => $this->request->getPost('jabatanPic'),
                'NoSkepFasilitas' => $this->request->getPost('NoSkepFasilitas') ?: null,
            ];

            try {
                // Handle upload TTD
                if ($this->request->getFile('ttd') && $this->request->getFile('ttd')->isValid()) {
                    $uploadResult = $tobaUploader->upload($this->request->getFile('ttd'));
                    if ($uploadResult && $uploadResult['status'] === 'success') {
                        $data_perusahaan['ttd'] = $uploadResult['data']['keyFile'];
                    } else { throw new \Exception('Gagal upload Tanda Tangan PIC: '.($uploadResult['message'] ?? 'Error')); }
                }

                // Handle upload Gambar Profil/Logo
                if ($this->request->getFile('profile_image') && $this->request->getFile('profile_image')->isValid()) {
                    $uploadResult = $tobaUploader->upload($this->request->getFile('profile_image'));
                    if ($uploadResult && $uploadResult['status'] === 'success') {
                        $this->db->table('user')->where('id', $id_user_login)->update(['image' => $uploadResult['data']['keyFile']]);
                        $this->session->set('user_image', $uploadResult['data']['keyFile']);
                    } else { throw new \Exception('Gagal upload Gambar Profil/Logo: '.($uploadResult['message'] ?? 'Error')); }
                }

                // Handle upload SKEP Fasilitas
                if ($this->request->getFile('file_skep_fasilitas') && $this->request->getFile('file_skep_fasilitas')->isValid()) {
                     $uploadResult = $tobaUploader->upload($this->request->getFile('file_skep_fasilitas'));
                    if ($uploadResult && $uploadResult['status'] === 'success') {
                        $data_perusahaan['FileSkepFasilitas'] = $uploadResult['data']['keyFile'];
                    } else { throw new \Exception('Gagal upload File SKEP Fasilitas: '.($uploadResult['message'] ?? 'Error')); }
                }
                
                // Handle SKEP Awal hanya saat aktivasi
                $keyFileInitialSkep = null;
                if ($is_activating && $this->request->getFile('initial_skep_file') && $this->request->getFile('initial_skep_file')->isValid()) {
                    $uploadResult = $tobaUploader->upload($this->request->getFile('initial_skep_file'));
                    if ($uploadResult && $uploadResult['status'] === 'success') {
                        // Simpan keyFile untuk dimasukkan ke tabel kuota
                        $keyFileInitialSkep = $uploadResult['data']['keyFile'];
                    } else { throw new \Exception('Gagal upload File SKEP Kuota Awal: '.($uploadResult['message'] ?? 'Error')); }
                }
            } catch (\Exception $e) {
                $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">' . $e->getMessage() . '</div>');
                return redirect()->to('user/edit');
            }
            
            // Lanjutkan proses database
            if ($is_activating) {
                $data_perusahaan['id_pers'] = $id_user_login;
                $this->db->table('user_perusahaan')->insert($data_perusahaan);

                $initial_skep_no = trim($this->request->getPost('initial_skep_no') ?? '');
                $initial_nama_barang = trim($this->request->getPost('initial_nama_barang') ?? '');
                $initial_kuota_jumlah = (float)($this->request->getPost('initial_kuota_jumlah') ?? 0);

                if (!empty($initial_skep_no) && !empty($initial_nama_barang) && $initial_kuota_jumlah > 0) {
                    $this->db->table('user_kuota_barang')->insert([
                        'id_pers' => $id_user_login, 'nama_barang' => $initial_nama_barang, 'initial_quota_barang' => $initial_kuota_jumlah,
                        'remaining_quota_barang' => $initial_kuota_jumlah, 'nomor_skep_asal' => $initial_skep_no, 'tanggal_skep_asal' => $this->request->getPost('initial_skep_tgl'),
                        'status_kuota_barang' => 'active', 'dicatat_oleh_user_id' => $id_user_login, 'waktu_pencatatan' => date('Y-m-d H:i:s'),
                        'file_skep_asal' => $keyFileInitialSkep, // Simpan keyFile-nya
                    ]);
                }

                $this->db->table('user')->where('id', $id_user_login)->update(['is_active' => 1]);
                $this->session->set('is_active', 1);
                $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Profil perusahaan berhasil disimpan dan akun Anda telah diaktifkan!</div>');
            } else {
                $this->db->table('user_perusahaan')->where('id_pers', $id_user_login)->update($data_perusahaan);
                $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Profil dan data perusahaan berhasil diperbarui!</div>');
            }
            return redirect()->to('user/index');
        }
    }

    /**
     * Form for creating a new import request using TobaUploader.
     */
    public function permohonan_impor_kembali()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Permohonan Impor Kembali';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        $id_user_login = $data['user']['id'];
        $data['user_perusahaan'] = $this->db->table('user_perusahaan')->where('id_pers', $id_user_login)->get()->getRowArray();

        if (empty($data['user_perusahaan']) || $data['user']['is_active'] == 0) {
            $this->session->setFlashdata('message', '<div class="alert alert-warning" role="alert">Mohon lengkapi profil perusahaan Anda dan pastikan akun aktif sebelum membuat permohonan.</div>');
            return redirect()->to('user/edit');
        }
        
        $data['list_barang_berkuota'] = $this->db->table('user_kuota_barang')->select('id_kuota_barang, nama_barang, remaining_quota_barang, nomor_skep_asal, tanggal_skep_asal')->where('id_pers', $id_user_login)->where('remaining_quota_barang >', 0)->where('status_kuota_barang', 'active')->orderBy('nama_barang ASC, tanggal_skep_asal DESC')->get()->getResultArray();
        
        $rules = [
            'nomorSurat' => ['label' => 'Nomor Surat Pengajuan', 'rules' => 'required|max_length[100]'], 'TglSurat' => ['label' => 'Tanggal Surat', 'rules' => 'required'],
            'Perihal' => ['label' => 'Perihal Surat', 'rules' => 'required|max_length[255]'], 'id_kuota_barang_selected' => ['label' => 'Pilihan Kuota Barang', 'rules' => 'required|numeric'],
            'NamaBarang' => ['label' => 'Nama/Jenis Barang', 'rules' => 'required'], 'JumlahBarang' => ['label' => 'Jumlah Barang Diajukan', 'rules' => 'required|numeric|greater_than[0]|max_length[10]'],
            'NegaraAsal' => ['label' => 'Negara Asal Barang', 'rules' => 'required|max_length[100]'], 'NamaKapal' => ['label' => 'Nama Kapal/Sarana Pengangkut', 'rules' => 'required|max_length[100]'],
            'noVoyage' => ['label' => 'No. Voyage/Flight', 'rules' => 'required|max_length[50]'], 'TglKedatangan' => ['label' => 'Tanggal Perkiraan Kedatangan', 'rules' => 'required'],
            'TglBongkar' => ['label' => 'Tanggal Perkiraan Bongkar', 'rules' => 'required'], 'lokasi' => ['label' => 'Lokasi Bongkar', 'rules' => 'required|max_length[100]'],
            'file_bc_manifest' => ['label' => 'File BC 1.1 / Manifest', 'rules' => 'uploaded[file_bc_manifest]|max_size[file_bc_manifest,2048]|ext_in[file_bc_manifest,pdf]']
        ];

        if (!$this->validate($rules)) {
            if (empty($data['list_barang_berkuota']) && $this->request->getMethod() !== 'post') {
                $this->session->setFlashdata('message_form_permohonan', '<div class="alert alert-warning" role="alert">Anda tidak memiliki kuota aktif. Tidak dapat membuat permohonan.</div>');
            }
            return view('user/permohonan_impor_kembali_form', $data);
        } else {
            $id_kuota_barang_dipilih = (int)$this->request->getPost('id_kuota_barang_selected');
            $nama_barang_input_form = $this->request->getPost('NamaBarang');
            $jumlah_barang_dimohon = (float)$this->request->getPost('JumlahBarang');

            $kuota_valid_db = $this->db->table('user_kuota_barang')->where(['id_kuota_barang' => $id_kuota_barang_dipilih, 'id_pers' => $id_user_login, 'status_kuota_barang' => 'active'])->get()->getRowArray();

            if (!$kuota_valid_db || $kuota_valid_db['nama_barang'] != $nama_barang_input_form) {
                $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Data kuota barang tidak valid. Silakan pilih kembali.</div>');
                return redirect()->to('user/permohonan_impor_kembali');
            }
            
            if ($jumlah_barang_dimohon > (float)$kuota_valid_db['remaining_quota_barang']) {
                $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Jumlah barang melebihi sisa kuota.</div>');
                return redirect()->to('user/permohonan_impor_kembali');
            }

            $nama_file_bc_manifest = null;
            try {
                $tobaUploader = new TobaUploader();
                $uploadResult = $tobaUploader->upload($this->request->getFile('file_bc_manifest'));
                if ($uploadResult && $uploadResult['status'] === 'success') {
                    $nama_file_bc_manifest = $uploadResult['data']['keyFile'];
                } else {
                    throw new \Exception('Gagal upload File BC 1.1: ' . ($uploadResult['message'] ?? 'Error'));
                }
            } catch (\Exception $e) {
                $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">' . $e->getMessage() . '</div>');
                return redirect()->to('user/permohonan_impor_kembali');
            }
            
            $data_insert = [
                'NamaPers' => $data['user_perusahaan']['NamaPers'], 'alamat' => $data['user_perusahaan']['alamat'], 'nomorSurat' => $this->request->getPost('nomorSurat'),
                'TglSurat' => $this->request->getPost('TglSurat'), 'Perihal' => $this->request->getPost('Perihal'), 'NamaBarang' => $nama_barang_input_form, 'JumlahBarang' => $jumlah_barang_dimohon,
                'NegaraAsal' => $this->request->getPost('NegaraAsal'), 'NamaKapal' => $this->request->getPost('NamaKapal'), 'noVoyage' => $this->request->getPost('noVoyage'),
                'NoSkep' => $kuota_valid_db['nomor_skep_asal'], 'file_bc_manifest' => $nama_file_bc_manifest, 'id_kuota_barang_digunakan' => $id_kuota_barang_dipilih,
                'TglKedatangan' => $this->request->getPost('TglKedatangan'), 'TglBongkar' => $this->request->getPost('TglBongkar'), 'lokasi' => $this->request->getPost('lokasi'),
                'id_pers' => $id_user_login, 'time_stamp' => date('Y-m-d H:i:s'), 'status' => '0',
            ];
            
            $this->db->table('user_permohonan')->insert($data_insert);
            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Permohonan Impor Kembali telah berhasil diajukan.</div>');
            return redirect()->to('user/daftarPermohonan');
        }
    }

    /**
     * Form for creating a new quota request using TobaUploader.
     */
    public function pengajuan_kuota()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Pengajuan Penetapan/Penambahan Kuota';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        $id_user_login = $data['user']['id'];
        $data['user_perusahaan'] = $this->db->table('user_perusahaan')->where('id_pers', $id_user_login)->get()->getRowArray();

        if (empty($data['user_perusahaan']) || $data['user']['is_active'] == 0) {
            $this->session->setFlashdata('message', '<div class="alert alert-warning" role="alert">Mohon lengkapi profil perusahaan dan pastikan akun aktif sebelum mengajukan kuota.</div>');
            return redirect()->to('user/edit');
        }

        $rules = [
            'nomor_surat_pengajuan' => ['label' => 'Nomor Surat Pengajuan', 'rules' => 'required|max_length[100]'],
            'tanggal_surat_pengajuan' => ['label' => 'Tanggal Surat Pengajuan', 'rules' => 'required'],
            'perihal_pengajuan' => ['label' => 'Perihal Surat Pengajuan', 'rules' => 'required|max_length[255]'],
            'nama_barang_kuota' => ['label' => 'Nama/Jenis Barang', 'rules' => 'required|max_length[255]'],
            'requested_quota' => ['label' => 'Jumlah Kuota Diajukan', 'rules' => 'required|numeric|greater_than[0]|max_length[10]'],
            'reason' => ['label' => 'Alasan Pengajuan', 'rules' => 'required']
        ];

        if ($this->request->getFile('file_lampiran_pengajuan') && $this->request->getFile('file_lampiran_pengajuan')->isValid()) {
            $rules['file_lampiran_pengajuan'] = ['label' => 'File Lampiran', 'rules' => 'max_size[file_lampiran_pengajuan,2048]|ext_in[file_lampiran_pengajuan,pdf,doc,docx,jpg,jpeg,png]'];
        }

        if (!$this->validate($rules)) {
            return view('user/pengajuan_kuota_form', $data);
        } else {
            $data_pengajuan = [
                'id_pers' => $id_user_login, 'nomor_surat_pengajuan' => $this->request->getPost('nomor_surat_pengajuan'),
                'tanggal_surat_pengajuan' => $this->request->getPost('tanggal_surat_pengajuan'), 'perihal_pengajuan' => $this->request->getPost('perihal_pengajuan'),
                'nama_barang_kuota' => $this->request->getPost('nama_barang_kuota'), 'requested_quota' => (float)$this->request->getPost('requested_quota'),
                'reason' => $this->request->getPost('reason'), 'submission_date' => date('Y-m-d H:i:s'), 'status' => 'pending'
            ];
            
            if ($this->request->getFile('file_lampiran_pengajuan') && $this->request->getFile('file_lampiran_pengajuan')->isValid()) {
                try {
                    $tobaUploader = new TobaUploader();
                    $uploadResult = $tobaUploader->upload($this->request->getFile('file_lampiran_pengajuan'));
                    if ($uploadResult && $uploadResult['status'] === 'success') {
                        $data_pengajuan['file_lampiran_user'] = $uploadResult['data']['keyFile'];
                    } else { throw new \Exception('Gagal upload Lampiran: '.($uploadResult['message'] ?? 'Error')); }
                } catch (\Exception $e) {
                    $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">' . $e->getMessage() . '</div>');
                    return redirect()->to('user/pengajuan_kuota');
                }
            }

            $this->db->table('user_pengajuan_kuota')->insert($data_pengajuan);
            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Pengajuan kuota Anda telah berhasil dikirim.</div>');
            return redirect()->to('user/daftar_pengajuan_kuota');
        }
    }

    /**
     * List of user's quota requests.
     */
    public function daftar_pengajuan_kuota()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Daftar Pengajuan Kuota Saya';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        $id_user_login = $data['user']['id'];

        $data['daftar_pengajuan'] = $this->db->table('user_pengajuan_kuota')->select('*')->where('id_pers', $id_user_login)->orderBy('submission_date', 'DESC')->get()->getResultArray();

        return view('user/daftar_pengajuan_kuota_view', $data);
    }
    
    /**
     * Print proof of quota application. This is a standalone view.
     */
    public function print_bukti_pengajuan_kuota($id_pengajuan = 0)
    {
        if ($id_pengajuan == 0 || !is_numeric($id_pengajuan)) {
            return redirect()->to('user/daftar_pengajuan_kuota');
        }
        
        $user_login = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        $data['user'] = $user_login;

        $pengajuan = $this->db->table('user_pengajuan_kuota upk')->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left')->select('upk.*, upr.NamaPers, upr.alamat as alamat_perusahaan, upr.npwp as npwp_perusahaan, upr.pic, upr.jabatanPic, upr.ttd as file_ttd_pic, upr.telp as telp_perusahaan')->where('upk.id', $id_pengajuan)->where('upk.id_pers', $user_login['id'])->get()->getRowArray();

        if (!$pengajuan) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Data pengajuan kuota tidak ditemukan.</div>');
            return redirect()->to('user/daftar_pengajuan_kuota');
        }
        
        $data['pengajuan'] = $pengajuan;
        return view('user/FormPengajuanKuota_print', $data);
    }
    
    /**
     * Displays the list of user's applications.
     */
    public function daftarPermohonan()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Daftar Permohonan Impor Kembali';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();

        $data['permohonan'] = $this->db->table('user_permohonan up')->join('petugas p', 'up.petugas = p.id', 'left')->join('lhp', 'lhp.id_permohonan = up.id', 'left')->select('up.*, p.Nama AS nama_petugas_pemeriksa, up.NamaBarang, lhp.JumlahBenar')->where('up.id_pers', $data['user']['id'])->orderBy('up.time_stamp', 'DESC')->get()->getResultArray();
        
        return view('user/daftar-permohonan', $data);
    }
    
    /**
     * Displays detail of a specific application.
     */
    public function detailPermohonan($id_permohonan = 0)
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Detail Permohonan Impor Kembali';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();

        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            return redirect()->to('user/daftarPermohonan');
        }

        $data['permohonan_detail'] = $this->db->table('user_permohonan up')->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left')->join('petugas p', 'up.petugas = p.id', 'left')->select('up.*, upr.NamaPers, upr.npwp, p.Nama AS nama_petugas_pemeriksa')->where('up.id', $id_permohonan)->where('up.id_pers', $data['user']['id'])->get()->getRowArray();

        if (!$data['permohonan_detail']) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Data permohonan tidak ditemukan.</div>');
            return redirect()->to('user/daftarPermohonan');
        }
        
        $data['lhp_detail'] = $this->db->table('lhp')->where('id_permohonan', $id_permohonan)->get()->getRowArray();

        return view('user/detail_permohonan_view', $data);
    }

    /**
     * Print PDF for a specific application. This is a standalone view.
     */
    public function printPdf($id_permohonan)
    {
        if (empty($id_permohonan) || !is_numeric($id_permohonan)) {
            return redirect()->to('user/daftarPermohonan');
        }
    
        $user_login = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        $data['user'] = $user_login;

        $permohonan_data_lengkap = $this->db->table('user_permohonan up')->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left')->select('up.*, upr.NamaPers, upr.alamat as alamat_perusahaan, upr.npwp as npwp_perusahaan, upr.pic, upr.jabatanPic, upr.ttd as file_ttd_pic_perusahaan, upr.telp as telp_perusahaan')->where('up.id', $id_permohonan)->where('up.id_pers', $user_login['id'])->get()->getRowArray();

        if (!$permohonan_data_lengkap) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Permohonan tidak ditemukan.</div>');
            return redirect()->to('user/daftarPermohonan');
        }

        $data['permohonan'] = $permohonan_data_lengkap;
        return view('user/FormPermohonan', $data);
    }

    /**
     * Edit a specific application.
     */
    public function editpermohonan($id_permohonan = 0)
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Edit Permohonan Impor Kembali';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        $id_user_login = $data['user']['id'];
        
        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            return redirect()->to('user/daftarPermohonan');
        }
        
        $permohonan = $this->db->table('user_permohonan')->where(['id' => $id_permohonan, 'id_pers' => $id_user_login])->get()->getRowArray();

        if (!$permohonan || $permohonan['status'] != '0') {
            $this->session->setFlashdata('message', '<div class="alert alert-warning" role="alert">Permohonan tidak dapat diedit.</div>');
            return redirect()->to('user/daftarPermohonan');
        }
        $data['permohonan_edit'] = $permohonan;
        
        $builder = $this->db->table('user_kuota_barang')->select('id_kuota_barang, nama_barang, remaining_quota_barang, nomor_skep_asal')->where('id_pers', $id_user_login);
        $builder->groupStart()->where('remaining_quota_barang >', 0)->orWhere('id_kuota_barang', $permohonan['id_kuota_barang_digunakan'])->groupEnd();
        $data['list_barang_berkuota'] = $builder->where('status_kuota_barang', 'active')->orderBy('nama_barang', 'ASC')->get()->getResultArray();
        
        $rules = [
            'nomorSurat' => ['label' => 'Nomor Surat', 'rules' => 'required|max_length[100]'], 'TglSurat' => ['label' => 'Tanggal Surat', 'rules' => 'required'],
            'Perihal' => ['label' => 'Perihal', 'rules' => 'required|max_length[255]'], 'id_kuota_barang_selected' => ['label' => 'Pilihan Kuota Barang', 'rules' => 'required|numeric'],
            'NamaBarang' => ['label' => 'Nama/Jenis Barang', 'rules' => 'required'], 'JumlahBarang' => ['label' => 'Jumlah Barang', 'rules' => 'required|numeric|greater_than[0]|max_length[10]'],
            'NegaraAsal' => ['label' => 'Negara Asal', 'rules' => 'required|max_length[100]'], 'NamaKapal' => ['label' => 'Nama Kapal', 'rules' => 'required|max_length[100]'],
            'noVoyage' => ['label' => 'Nomor Voyage', 'rules' => 'required|max_length[50]'], 'TglKedatangan' => ['label' => 'Tanggal Kedatangan', 'rules' => 'required'],
            'TglBongkar' => ['label' => 'Tanggal Bongkar', 'rules' => 'required'], 'lokasi' => ['label' => 'Lokasi Bongkar', 'rules' => 'required|max_length[100]']
        ];

        if (!$this->validate($rules)) {
            $data['id_permohonan_form_action'] = $id_permohonan;
            return view('user/edit_permohonan_form', $data);
        } else {
            $id_kuota_barang_dipilih = (int)$this->request->getPost('id_kuota_barang_selected');
            $jumlah_barang_dimohon = (float)$this->request->getPost('JumlahBarang');
            $kuota_valid_db = $this->db->table('user_kuota_barang')->where(['id_kuota_barang' => $id_kuota_barang_dipilih, 'id_pers' => $id_user_login, 'status_kuota_barang' => 'active'])->get()->getRowArray();
            
            $sisa_kuota_efektif = (float)$kuota_valid_db['remaining_quota_barang'];
            if ($permohonan['id_kuota_barang_digunakan'] == $id_kuota_barang_dipilih) {
                $sisa_kuota_efektif += (float)$permohonan['JumlahBarang'];
            }

            if ($jumlah_barang_dimohon > $sisa_kuota_efektif) {
                $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Jumlah barang melebihi sisa kuota.</div>');
                return redirect()->to('user/editpermohonan/' . $id_permohonan);
            }

            $data_update = [
                'nomorSurat' => $this->request->getPost('nomorSurat'), 'TglSurat' => $this->request->getPost('TglSurat'), 'Perihal' => $this->request->getPost('Perihal'),
                'NamaBarang' => $this->request->getPost('NamaBarang'), 'JumlahBarang' => $jumlah_barang_dimohon, 'NegaraAsal' => $this->request->getPost('NegaraAsal'),
                'NamaKapal' => $this->request->getPost('NamaKapal'), 'noVoyage' => $this->request->getPost('noVoyage'), 'NoSkep' => $kuota_valid_db['nomor_skep_asal'],
                'id_kuota_barang_digunakan' => $id_kuota_barang_dipilih, 'TglKedatangan' => $this->request->getPost('TglKedatangan'), 'TglBongkar' => $this->request->getPost('TglBongkar'),
                'lokasi' => $this->request->getPost('lokasi'), 'time_stamp_update' => date('Y-m-d H:i:s')
            ];

            $this->db->table('user_permohonan')->where('id', $id_permohonan)->where('id_pers', $id_user_login)->update($data_update);
            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Permohonan berhasil diubah.</div>');
            return redirect()->to('user/daftarPermohonan');
        }
    }

    /**
     * Page for user to forcefully change their password.
     */
    public function force_change_password_page()
    {
        if (!$this->session->get('email') || $this->session->get('force_change_password') != 1) {
            return redirect()->to('user/index');
        }

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Wajib Ganti Password';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();

        $rules = [
            'new_password' => ['label' => 'Password Baru', 'rules' => 'required|min_length[6]|matches[confirm_new_password]'],
            'confirm_new_password' => ['label' => 'Konfirmasi Password Baru', 'rules' => 'required']
        ];

        if (!$this->validate($rules)) {
            return view('user/form_force_change_password', $data);
        } else {
            $new_password_hash = password_hash($this->request->getPost('new_password'), PASSWORD_DEFAULT);
            $this->db->table('user')->where('id', $data['user']['id'])->update(['password' => $new_password_hash, 'force_change_password' => 0]);
            $this->session->set('force_change_password', 0);
            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Password Anda telah berhasil diubah.</div>');
            return redirect()->to('user/index');
        }
    }

    /**
     * [DIREVISI] Delete a specific application, removing local unlink().
     */
    public function hapus_permohonan_impor($id_permohonan = 0)
    {
        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            return redirect()->to('user/daftarPermohonan');
        }

        $id_user_login = $this->session->get('id');
        $permohonan = $this->db->table('user_permohonan')->where(['id' => $id_permohonan, 'id_pers' => $id_user_login])->get()->getRowArray();

        if (!$permohonan || !in_array($permohonan['status'], ['0', '5'])) {
            $this->session->setFlashdata('message', '<div class="alert alert-warning" role="alert">Permohonan tidak dapat dihapus.</div>');
            return redirect()->to('user/daftarPermohonan');
        }

        // TO-DO: Panggil API Toba untuk menghapus file jika ada.
        // Anda akan butuh keyFile dari $permohonan['file_bc_manifest']
        // Contoh: if(!empty($permohonan['file_bc_manifest'])) { (new TobaUploader())->delete($permohonan['file_bc_manifest']); }

        if ($this->db->table('user_permohonan')->where(['id' => $id_permohonan, 'id_pers' => $id_user_login])->delete()) {
            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Permohonan berhasil dihapus.</div>');
        } else {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Gagal menghapus permohonan.</div>');
        }
        return redirect()->to('user/daftarPermohonan');
    }

    /**
     * Delete a specific quota application, removing local unlink().
     */
    public function hapus_pengajuan_kuota($id_pengajuan = 0)
    {
        if ($id_pengajuan == 0 || !is_numeric($id_pengajuan)) {
            return redirect()->to('user/daftar_pengajuan_kuota');
        }

        $id_user_login = $this->session->get('id');
        $pengajuan = $this->db->table('user_pengajuan_kuota')->where(['id' => $id_pengajuan, 'id_pers' => $id_user_login])->get()->getRowArray();

        if (!$pengajuan || $pengajuan['status'] !== 'pending') {
            $this->session->setFlashdata('message', '<div class="alert alert-warning" role="alert">Pengajuan kuota tidak dapat dihapus.</div>');
            return redirect()->to('user/daftar_pengajuan_kuota');
        }

        // TO-DO: Panggil API Toba untuk menghapus file jika ada.
        // Contoh: if(!empty($pengajuan['file_lampiran_user'])) { (new TobaUploader())->delete($pengajuan['file_lampiran_user']); }

        if ($this->db->table('user_pengajuan_kuota')->where(['id' => $id_pengajuan, 'id_pers' => $id_user_login])->delete()) {
            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Pengajuan kuota berhasil dihapus.</div>');
        } else {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Gagal menghapus pengajuan kuota.</div>');
        }
        return redirect()->to('user/daftar_pengajuan_kuota');
    }
    
    /**
     * Proxy untuk men-download file dari Toba
     */
    public function downloadFile(string $keyFile = '')
    {
        if (empty($keyFile)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('File key tidak valid.');
        }

        try {
            $tobaUploader = new TobaUploader();
            $fileData = $tobaUploader->download($keyFile);

            if ($fileData && isset($fileData->result) && $fileData->result === 'success' && isset($fileData->data->base64)) {
                $binaryData = base64_decode($fileData->data->base64);
                $mimeType = $fileData->data->type ?? 'application/octet-stream';
                $fileName = $fileData->data->fileName ?? 'downloaded_file';

                return $this->response
                    ->setHeader('Content-Type', $mimeType)
                    ->setHeader('Content-Disposition', 'inline; filename="' . $fileName . '"')
                    ->setBody($binaryData)
                    ->send();
            } else {
                $errorMessage = $fileData->detail ?? 'File tidak ditemukan di server hosting.';
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound($errorMessage);
            }
        } catch (\Exception $e) {
            log_message('error', '[TobaUploader Download Exception - User] ' . $e->getMessage());
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Gagal memproses download.');
        }
    }
}
