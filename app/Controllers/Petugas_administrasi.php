<?php

namespace App\Controllers;

// Import class-class yang diperlukan dari CodeIgniter 4 dan library eksternal
use App\Controllers\BaseController;
use App\Libraries\TobaUploader;
use Config\Services;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;

// Pastikan helper yang dibutuhkan sudah di-load, bisa di BaseController.php atau di sini
// helper(['form', 'url', 'repack_helper', 'download']);

class Petugas_administrasi extends BaseController
{
    /**
     * @var \CodeIgniter\Database\BaseConnection
     */
    protected $db;

    /**
     * @var \CodeIgniter\Session\Session
     */
    protected $session;

    /**
     * @var \CodeIgniter\Validation\ValidationInterface
     */
    protected $validation;

    /**
     * Daftar method yang tidak memerlukan pengecekan otentikasi.
     * @var array
     */
    protected $excluded_methods = ['logout']; // Anda bisa menambahkan method lain jika perlu

    public function __construct()
    {
        // Inisialisasi service utama
        $this->db = \Config\Database::connect();
        $this->session = Services::session();
        $this->validation = Services::validation();

        // Load helper yang mungkin dibutuhkan di seluruh controller
        helper(['form', 'url', 'repack_helper', 'download']);

        // Menjalankan pengecekan otentikasi
        $router = Services::router();
        $method = $router->methodName();

        if (!in_array($method, $this->excluded_methods)) {
            $this->_checkAuthPetugasAdministrasi();
        }
        log_message('debug', 'Petugas_administrasi Class Initialized. Method: ' . $method);
    }

    private function _checkAuthPetugasAdministrasi()
    {
        log_message('debug', 'Petugas_administrasi: _checkAuthPetugasAdministrasi() called. Email session: ' . ($this->session->get('email') ?? 'NULL'));
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Mohon login untuk melanjutkan.</div>');
            // Menggunakan return redirect()->to() akan menghentikan eksekusi lebih lanjut
            // sehingga exit tidak diperlukan.
            return redirect()->to('auth');
        }

        $role_id_session = $this->session->get('role_id');
        log_message('debug', 'Petugas_administrasi: _checkAuthPetugasAdministrasi() - Role ID: ' . ($role_id_session ?? 'NULL'));

        if ($role_id_session != 5) {
            log_message('error', 'Petugas_administrasi: _checkAuthPetugasAdministrasi() - Akses ditolak, role ID tidak sesuai: ' . $role_id_session);
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Akses Ditolak! Anda tidak diotorisasi untuk mengakses halaman ini.</div>');
            
            $redirect_url = 'auth/blocked';
            if ($role_id_session == 1) $redirect_url = 'admin';
            elseif ($role_id_session == 2) $redirect_url = 'user';
            elseif ($role_id_session == 3) $redirect_url = 'petugas';
            elseif ($role_id_session == 4) $redirect_url = 'monitoring';
            
            // Sama seperti di atas, return redirect sudah cukup.
            return redirect()->to($redirect_url);
        }
        log_message('debug', 'Petugas_administrasi: _checkAuthPetugasAdministrasi() passed.');
    }

    public function setup_mfa()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Setup Multi-Factor Authentication';
        $data['user'] = $this->db->table('user')->getWhere(['email' => $this->session->get('email')])->getRowArray();

        $google2fa = new Google2FA();
        
        if (empty($data['user']['google2fa_secret'])) {
            $secretKey = $google2fa->generateSecretKey();
            $this->db->table('user')->where('id', $data['user']['id'])->update(['google2fa_secret' => $secretKey]);
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

        return view('petugas_administrasi/mfa_setup', $data);
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
            $this->db->table('user')->where('id', $userId)->update(['is_mfa_enabled' => 1]);
            $this->session->set('mfa_verified', true);
            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Autentikasi Dua Faktor (MFA) berhasil diaktifkan!</div>');
            return redirect()->to('petugas_administrasi/index');
        } else {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Kode verifikasi salah. Silakan coba lagi.</div>');
            return redirect()->to('petugas_administrasi/setup_mfa');
        }
    }

    public function reset_mfa()
    {
        $user_id = $this->session->get('user_id');

        $this->db->table('user')->where('id', $user_id)->update([
            'is_mfa_enabled' => 0,
            'google2fa_secret' => NULL
        ]);

        $this->session->remove('mfa_verified');

        $this->session->setFlashdata('message', '<div class="alert alert-info" role="alert">MFA Anda telah dinonaktifkan. Silakan lakukan pengaturan ulang.</div>');
        return redirect()->to('petugas_administrasi/setup_mfa');
    }
    
    public function edit_profil()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Edit Profil Saya';
        $data['user'] = $this->db->table('user')->getWhere(['email' => $this->session->get('email')])->getRowArray();
        $user_id = $data['user']['id'];

        if (strtolower($this->request->getMethod()) === 'post') {
            $update_data_user = [];
            $name_input = $this->request->getPost('name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            if (!empty($name_input) && $name_input !== $data['user']['name']) {
                $update_data_user['name'] = $name_input;
            }
            
            $current_login_identifier = $data['user']['email'];
            $new_login_identifier = $this->request->getPost('login_identifier', FILTER_SANITIZE_EMAIL);

            if (!empty($new_login_identifier) && $new_login_identifier !== $current_login_identifier) {
                // CI4 validation rule for is_unique is slightly different
                $email_rules = ['login_identifier' => "trim|required|valid_email|is_unique[user.email,id,{$user_id}]"];
                if ($this->validate($email_rules)) {
                    $update_data_user['email'] = $new_login_identifier;
                } else {
                    // Jika validasi email gagal, kirim pesan error
                    $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">' . $this->validation->getError('login_identifier') . '</div>');
                    return redirect()->to('petugas_administrasi/edit_profil');
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
            
            $profile_image_file = $this->request->getFile('profile_image');

            if ($profile_image_file && $profile_image_file->isValid() && !$profile_image_file->hasMoved()) {
                // Validasi file tetap dilakukan sebelum upload
                 $validation_rules = [
                    'profile_image' => [
                        'rules' => 'uploaded[profile_image]|is_image[profile_image]|max_size[profile_image,2048]',
                    ],
                ];
                if ($this->validate($validation_rules)) {
                    try {
                        $tobaUploader = new TobaUploader();
                        $uploadResult = $tobaUploader->upload($profile_image_file);

                        if ($uploadResult && isset($uploadResult['status']) && $uploadResult['status'] === 'success') {
                            $newFileKey = $uploadResult['data']['keyFile']; 

                            $this->db->table('user')->where('id', $user_id)->update(['image' => $newFileKey]);
                            $this->session->set('user_image', $newFileKey);
                            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Foto profil berhasil diupdate.</div>');
                        } else {
                            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Gagal upload ke Toba: '.($uploadResult['message'] ?? 'Error tidak diketahui').'</div>');
                        }
                    } catch (\Exception $e) {
                        log_message('error', '[TobaUploader Exception] ' . $e->getMessage());
                        $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Gagal memproses upload: ' . $e->getMessage() . '</div>');
                    }
                } else {
                    $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Upload Foto Profil Gagal: ' . $this->validation->getError('profile_image') . '</div>');
                }
            }
            
            return redirect()->to('petugas_administrasi/edit_profil');
        }
        $data['user'] = $this->db->table('user')->getWhere(['email' => $this->session->get('email')])->getRowArray();
        return view('petugas_administrasi/form_edit_profil_admin', $data);
    }

    public function index()
    {
        log_message('debug', 'Petugas_administrasi: index() called.');
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Dashboard Petugas Administrasi';
        $data['user'] = $this->db->table('user')->getWhere(['email' => $this->session->get('email')])->getRowArray();
        
        $data['pending_permohonan'] = $this->db->table('user_permohonan')->whereIn('status', ['0', '1', '2', '5'])->countAllResults();
        $data['pending_kuota_requests'] = $this->db->table('user_pengajuan_kuota')->where('status', 'pending')->countAllResults();

        return view('petugas_administrasi/index', $data);
    }

    public function monitoring_kuota()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Monitoring Kuota Perusahaan (per Jenis Barang)';
        $data['user'] = $this->db->table('user')->getWhere(['email' => $this->session->get('email')])->getRowArray();

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

        log_message('debug', 'PETUGAS_ADMINISTRASI MONITORING KUOTA - Query: ' . $this->db->getLastQuery());
        log_message('debug', 'PETUGAS_ADMINISTRASI MONITORING KUOTA - Data: ' . print_r($data['monitoring_data'], true));

        return view('petugas_administrasi/monitoring_kuota_view', $data);
    }

    public function permohonanMasuk()
    {
        $data['user'] = $this->db->table('user')->getWhere(['email' => $this->session->get('email')])->getRowArray();
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
        
        $builder->orderBy("
            CASE up.status
                WHEN '0' THEN 1 
                WHEN '5' THEN 2 
                WHEN '1' THEN 3 
                WHEN '2' THEN 4 
                ELSE 5
            END ASC, up.time_stamp DESC");
        $data['permohonan'] = $builder->get()->getResultArray();
        
        return view('petugas_administrasi/permohonan-masuk', $data);
    }
    
    // NOTE: Fungsi _get_upload_config di CI3 digantikan dengan validasi file di CI4
    // yang lebih terintegrasi. Logika untuk membuat direktori dipindahkan ke method yang memerlukan.
    private function _ensureUploadDirExists($path)
    {
        if (!is_dir($path)) {
            log_message('debug', "Directory does not exist, creating: {$path}");
            if (!@mkdir($path, 0777, true)) {
                log_message('error', "Failed to create upload directory: {$path}");
                return false;
            }
        }
        if (!is_writable($path)) {
            log_message('error', "Directory is not writable: {$path}");
            return false;
        }
        return true;
    }

    public function prosesSurat($id_permohonan = 0)
    {
        $admin_user = $this->db->table('user')->getWhere(['email' => $this->session->get('email')])->getRowArray();
        $data['user'] = $admin_user;
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Proses Finalisasi Permohonan Impor';

        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid.</div>');
            return redirect()->to('petugas_administrasi/permohonanMasuk');
        }

        $data['permohonan'] = $this->db->table('user_permohonan up')
            ->select('up.*, upr.NamaPers, upr.npwp, upr.alamat, upr.NoSkep')
            ->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left')
            ->where('up.id', $id_permohonan)
            ->get()->getRowArray();

        if (!$data['permohonan']) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Permohonan tidak ditemukan.</div>');
            return redirect()->to('petugas_administrasi/permohonanMasuk');
        }
        
        $data['user_perusahaan'] = $this->db->table('user_perusahaan')->getWhere(['id_pers' => $data['permohonan']['id_pers']])->getRowArray();
        if (!$data['user_perusahaan']) {
            $data['user_perusahaan'] = ['NamaPers' => 'N/A', 'alamat' => 'N/A', 'NoSkep' => 'N/A', 'npwp' => 'N/A'];
        }

        $data['lhp'] = $this->db->table('lhp')->getWhere(['id_permohonan' => $id_permohonan])->getRowArray();
        if (!$data['lhp'] || $data['permohonan']['status'] != '2' || empty($data['lhp']['NoLHP']) || empty($data['lhp']['TglLHP'])) {
            $this->session->setFlashdata('message', '<div class="alert alert-warning" role="alert">LHP belum lengkap atau status permohonan (ID ' . esc($id_permohonan) . ') tidak valid untuk finalisasi.</div>');
            return redirect()->to('petugas_administrasi/detail_permohonan_admin/' . $id_permohonan);
        }

        // Setup validation rules
        $rules = [
            'status_final' => 'required|in_list[3,4]',
        ];

        if ($this->request->getPost('status_final') == '4') { // Jika DITOLAK
            $rules['catatan_penolakan'] = 'trim|required';
        
        } elseif ($this->request->getPost('status_final') == '3') { // Jika DISETUJUI
            // Pindahkan aturan untuk field persetujuan ke sini!
            $rules['nomorSetuju'] = 'trim|required|max_length[100]';
            $rules['tgl_S'] = 'trim|required';
            $rules['link'] = 'trim|permit_empty|valid_url_strict';

            // Logika validasi file Anda yang sudah ada
            $file = $this->request->getFile('file_surat_keputusan');
            if (empty($data['permohonan']['file_surat_keputusan']) && (!$file || !$file->isValid())) {
                 $rules['file_surat_keputusan'] = 'uploaded[file_surat_keputusan]';
            }
            if ($file && $file->isValid()) {
                 $rules['file_surat_keputusan'] = 'max_size[file_surat_keputusan,2048]|ext_in[file_surat_keputusan,pdf,jpg,png,jpeg]';
            }
        }
        
        if (!$this->validate($rules)) {
            log_message('debug', 'PETUGAS_ADMINISTRASI PROSES SURAT - Form validation failed. Errors: ' . print_r($this->validation->getErrors(), true));
            // Passing errors to the view
            $data['validation'] = $this->validation;
            return view('petugas_administrasi/prosesSurat', $data);
        } else {
            log_message('debug', 'PETUGAS_ADMINISTRASI PROSES SURAT - Form validation success. Processing data...');
            $status_final_permohonan = $this->request->getPost('status_final');
            
            $data_update_permohonan = [
                'nomorSetuju'       => $this->request->getPost('nomorSetuju'),
                'tgl_S'             => $this->request->getPost('tgl_S'),
                'link'              => $this->request->getPost('link'),
                'catatan_penolakan' => ($status_final_permohonan == '4') ? $this->request->getPost('catatan_penolakan') : null,
                'time_selesai'      => date("Y-m-d H:i:s"),
                'status'            => $status_final_permohonan,
            ];

            $file_sk = $this->request->getFile('file_surat_keputusan');
            if ($status_final_permohonan == '3' && $file_sk && $file_sk->isValid()) {
                try {
                    $tobaUploader = new TobaUploader();
                    $uploadResult = $tobaUploader->upload($file_sk);
                    if ($uploadResult && $uploadResult['status'] === 'success') {
                        // TO-DO: Hapus file lama di Toba jika ada
                        $data_update_permohonan['file_surat_keputusan'] = $uploadResult['data']['keyFile'];
                    } else {
                        $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Gagal upload file SK: ' . ($uploadResult['message'] ?? 'Error server') . '</div>');
                        return redirect()->to('petugas_administrasi/prosesSurat/' . $id_permohonan);
                    }
                } catch (\Exception $e) {
                     $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Error sistem upload: ' . $e->getMessage() . '</div>');
                     return redirect()->to('petugas_administrasi/prosesSurat/' . $id_permohonan);
                }
            } elseif ($status_final_permohonan == '4') {
                // TO-DO: Hapus file lama di Toba jika ditolak
                $data_update_permohonan['file_surat_keputusan'] = null;
            }

            // Start transaction
            $this->db->transStart();

            $this->db->table('user_permohonan')->where('id', $id_permohonan)->update($data_update_permohonan);
            
            if ($status_final_permohonan == '3' && isset($data['lhp']['JumlahBenar']) && $data['lhp']['JumlahBenar'] > 0) {
                $jumlah_dipotong = (float)$data['lhp']['JumlahBenar'];
                $id_kuota_barang_terpakai = $data['permohonan']['id_kuota_barang_digunakan'];
                $id_perusahaan = $data['permohonan']['id_pers'];

                if ($id_kuota_barang_terpakai) {
                    $kuota_barang_saat_ini = $this->db->table('user_kuota_barang')->getWhere(['id_kuota_barang' => $id_kuota_barang_terpakai])->getRowArray();
                    
                    if ($kuota_barang_saat_ini) {
                        $kuota_sebelum = (float)$kuota_barang_saat_ini['remaining_quota_barang'];
                        $kuota_sesudah = $kuota_sebelum - $jumlah_dipotong;

                        $this->db->table('user_kuota_barang')
                            ->where('id_kuota_barang', $id_kuota_barang_terpakai)
                            ->set('remaining_quota_barang', 'remaining_quota_barang - ' . $this->db->escape($jumlah_dipotong), false)
                            ->update();
                        
                        $keterangan_log = 'Pemotongan kuota dari persetujuan impor. No. Surat: ' . ($data_update_permohonan['nomorSetuju'] ?? '-');
                        $this->_logPerubahanKuota(
                            $id_perusahaan, 'pengurangan', $jumlah_dipotong, $kuota_sebelum, $kuota_sesudah,
                            $keterangan_log, $id_permohonan, 'permohonan_impor_disetujui', $admin_user['id'],
                            $kuota_barang_saat_ini['nama_barang'], $id_kuota_barang_terpakai
                        );
                    }
                }
            }
            
            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                 $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Gagal memproses permohonan karena ada masalah database.</div>');
            } else {
                $pesan_status_akhir = ($status_final_permohonan == '3') ? 'Disetujui' : 'Ditolak';
                $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Status permohonan ID ' . esc($id_permohonan) . ' telah berhasil diproses menjadi "' . $pesan_status_akhir . '"!</div>');
            }

            return redirect()->to('petugas_administrasi/permohonanMasuk');
        }
    }
    
    // Note: Callback validasi tidak lagi diperlukan karena sudah ditangani oleh rules di method utama.
    // public function valid_url_format_check($str) {} sudah ada di CI4 sebagai 'valid_url_strict'

    private function _logPerubahanKuota(
        $id_pers_param, $jenis_transaksi_param, $jumlah_param, $kuota_sebelum_param, $kuota_sesudah_param, 
        $keterangan_param, $id_referensi_param = null, $tipe_referensi_param = null, $dicatat_oleh_user_id_param = null,
        $nama_barang_terkait_param = null, $id_kuota_barang_ref_param = null
    ) {
        $log_data = [
            'id_pers'                   => $id_pers_param,
            'nama_barang_terkait'       => $nama_barang_terkait_param,
            'id_kuota_barang_referensi' => $id_kuota_barang_ref_param,
            'jenis_transaksi'           => $jenis_transaksi_param,
            'jumlah_perubahan'          => $jumlah_param,
            'sisa_kuota_sebelum'        => $kuota_sebelum_param,
            'sisa_kuota_setelah'        => $kuota_sesudah_param,
            'keterangan'                => $keterangan_param,
            'id_referensi_transaksi'    => $id_referensi_param,
            'tipe_referensi'            => $tipe_referensi_param,
            'dicatat_oleh_user_id'      => $dicatat_oleh_user_id_param,
            'tanggal_transaksi'         => date('Y-m-d H:i:s')
        ];

        if (!empty($log_data['id_pers']) && !empty($log_data['nama_barang_terkait'])) {
            $this->db->table('log_kuota_perusahaan')->insert($log_data);
        } else {
            log_message('error', 'Data log kuota tidak lengkap, tidak disimpan: ' . print_r($log_data, true));
        }
    }

    public function penunjukanPetugas($id_permohonan)
    {
        $data['user'] = $this->db->table('user')->getWhere(['email' => $this->session->get('email')])->getRowArray();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Penunjukan Petugas Pemeriksa';

        $permohonan = $this->db->table('user_permohonan up')
            ->select('up.*, upr.NamaPers')
            ->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left')
            ->where('up.id', $id_permohonan)
            ->get()->getRowArray();

        if (!$permohonan) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Permohonan tidak ditemukan!</div>');
            return redirect()->to('petugas_administrasi/permohonanMasuk');
        }
        $data['permohonan'] = $permohonan;

        if ($permohonan['status'] == '0' && strtolower($this->request->getMethod()) !== 'post') {
            $this->db->table('user_permohonan')->where('id', $id_permohonan)->update(['status' => '5']);
            $data['permohonan']['status'] = '5';
            $this->session->setFlashdata('message_transient', '<div class="alert alert-info" role="alert">Status permohonan ID ' . esc($id_permohonan) . ' telah diubah menjadi "Diproses Admin". Lanjutkan dengan menunjuk petugas.</div>');
        }

        $data['list_petugas'] = $this->db->table('petugas')->orderBy('Nama', 'ASC')->get()->getResultArray();
        if (empty($data['list_petugas'])) {
            log_message('error', 'Tidak ada data petugas ditemukan di tabel petugas.');
        }

        $rules = [
            'petugas_id' => 'required|numeric',
            'nomor_surat_tugas' => 'required|trim',
            'tanggal_surat_tugas' => 'required',
            'file_surat_tugas' => 'max_size[file_surat_tugas,2048]|ext_in[file_surat_tugas,pdf,jpg,png,jpeg,doc,docx]'
        ];

        if (!$this->validate($rules)) {
            $data['validation'] = $this->validation;
            return view('petugas_administrasi/form_penunjukan_petugas', $data);
        } else {
            $update_data = [
                'petugas' => $this->request->getPost('petugas_id'),
                'NoSuratTugas' => $this->request->getPost('nomor_surat_tugas'),
                'TglSuratTugas' => $this->request->getPost('tanggal_surat_tugas'),
                'status' => '1',
                'WaktuPenunjukanPetugas' => date('Y-m-d H:i:s')
            ];

            $file_st = $this->request->getFile('file_surat_tugas');
            $nama_file_surat_tugas = $permohonan['FileSuratTugas'] ?? null;

            if ($file_st && $file_st->isValid()) {
                try {
                    $tobaUploader = new TobaUploader();
                    $uploadResult = $tobaUploader->upload($file_st);
                    if ($uploadResult && $uploadResult['status'] === 'success') {
                        $update_data['FileSuratTugas'] = $uploadResult['data']['keyFile'];
                    } else {
                         $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Gagal upload surat tugas: ' . ($uploadResult['message'] ?? 'Error server') . '</div>');
                         return redirect()->to('petugas_administrasi/penunjukanPetugas/' . $id_permohonan);
                    }
                } catch (\Exception $e) {
                     $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Error sistem upload: ' . $e->getMessage() . '</div>');
                     return redirect()->to('petugas_administrasi/penunjukanPetugas/' . $id_permohonan);
                }
            }
            $update_data['FileSuratTugas'] = $nama_file_surat_tugas;

            $this->db->table('user_permohonan')->where('id', $id_permohonan)->update($update_data);
            
            log_message('debug', 'PENUNJUKAN PETUGAS (PA) - Data Permohonan Setelah Update: ' . print_r($this->db->table('user_permohonan')->getWhere(['id' => $id_permohonan])->getRowArray(), true));
            log_message('debug', 'PENUNJUKAN PETUGAS (PA) - Nilai petugas_id yang di-POST: ' . $this->request->getPost('petugas_id'));

            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Petugas pemeriksa berhasil ditunjuk untuk permohonan ID ' . esc($id_permohonan) . '. Status diubah menjadi "Penunjukan Pemeriksa".</div>');
            return redirect()->to('petugas_administrasi/permohonanMasuk');
        }
    }

    public function daftar_pengajuan_kuota()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Daftar Pengajuan Kuota';
        $data['user'] = $this->db->table('user')->getWhere(['email' => $this->session->get('email')])->getRowArray();

        $builder = $this->db->table('user_pengajuan_kuota upk');
        $builder->select('upk.*, upr.NamaPers, u.email as user_email');
        $builder->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $builder->join('user u', 'upk.id_pers = u.id', 'left');
        $builder->orderBy('FIELD(upk.status, "pending") DESC, upk.submission_date DESC');
        $data['pengajuan_kuota'] = $builder->get()->getResultArray();

        return view('petugas_administrasi/daftar_pengajuan_kuota', $data);
    }
    
    public function proses_pengajuan_kuota($id_pengajuan)
    {
        log_message('debug', 'PETUGAS_ADMINISTRASI PROSES PENGAJUAN KUOTA - Method dipanggil untuk id_pengajuan: ' . $id_pengajuan);
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Proses Pengajuan Kuota';
        $pa_user = $this->db->table('user')->getWhere(['email' => $this->session->get('email')])->getRowArray();
        $data['user'] = $pa_user;

        $builder = $this->db->table('user_pengajuan_kuota upk');
        $builder->select('upk.*, upr.NamaPers, upr.initial_quota as initial_quota_umum_sebelum, upr.remaining_quota as remaining_quota_umum_sebelum, u.email as user_email');
        $builder->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $builder->join('user u', 'upk.id_pers = u.id', 'left');
        $builder->where('upk.id', $id_pengajuan);
        $data['pengajuan'] = $builder->get()->getRowArray();
        log_message('debug', 'PETUGAS_ADMINISTRASI PROSES PENGAJUAN KUOTA - Data pengajuan yang diambil: ' . print_r($data['pengajuan'], true));

        if (!$data['pengajuan'] || !in_array($data['pengajuan']['status'], ['pending', 'diproses'])) {
            $pesan_error_awal = 'Pengajuan kuota tidak ditemukan atau statusnya tidak memungkinkan untuk diproses (Status saat ini: ' . ($data['pengajuan']['status'] ?? 'Tidak Diketahui') . '). Hanya status "pending" atau "diproses" yang bisa dilanjutkan.';
            log_message('error', 'PETUGAS_ADMINISTRASI PROSES PENGAJUAN KUOTA - Validasi awal gagal: ' . $pesan_error_awal);
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">' . $pesan_error_awal . '</div>');
            return redirect()->to('petugas_administrasi/daftar_pengajuan_kuota');
        }

        $rules = [
            'status_pengajuan' => 'required|in_list[approved,rejected,diproses]',
            'admin_notes' => 'trim',
        ];

        if ($this->request->getPost('status_pengajuan') == 'approved') {
            $rules['approved_quota'] = 'trim|required|numeric|greater_than[0]';
            $rules['nomor_sk_petugas'] = 'trim|required|max_length[100]';
            $rules['tanggal_sk_petugas'] = 'trim|required';
            $file_sk = $this->request->getFile('file_sk_petugas');
            if (empty($data['pengajuan']['file_sk_petugas']) && (!$file_sk || !$file_sk->isValid())) {
                $rules['file_sk_petugas'] = 'uploaded[file_sk_petugas]';
            }
        }
        
        if (!$this->validate($rules)) {
            log_message('debug', 'PETUGAS_ADMINISTRASI PROSES PENGAJUAN KUOTA - Validasi Form Gagal. Errors: ' . print_r($this->validation->getErrors(), true));
            $data['validation'] = $this->validation;
            return view('petugas_administrasi/proses_pengajuan_kuota_form', $data);
        } else {
            log_message('debug', 'PETUGAS_ADMINISTRASI PROSES PENGAJUAN KUOTA - Validasi Form Sukses. Memproses data...');
            $status_pengajuan = $this->request->getPost('status_pengajuan');
            
            $data_update_pengajuan = [
                'status' => $status_pengajuan,
                'admin_notes' => $this->request->getPost('admin_notes'),
                'processed_date' => date('Y-m-d H:i:s'),
                'nomor_sk_petugas' => $this->request->getPost('nomor_sk_petugas'),
                'tanggal_sk_petugas' => $this->request->getPost('tanggal_sk_petugas'),
                'approved_quota' => ($status_pengajuan == 'approved') ? (float)$this->request->getPost('approved_quota') : 0
            ];

            $file_sk_upload = $this->request->getFile('file_sk_petugas');
            $nama_file_sk = $data['pengajuan']['file_sk_petugas'] ?? null;

            if ($file_sk_upload && $file_sk_upload->isValid()) {
                try {
                    $tobaUploader = new TobaUploader();
                    $uploadResult = $tobaUploader->upload($file_sk_upload);
                    if ($uploadResult && $uploadResult['status'] === 'success') {
                        $data_update_pengajuan['file_sk_petugas'] = $uploadResult['data']['keyFile'];
                    } else {
                        $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Gagal upload SK Kuota: ' . ($uploadResult['message'] ?? 'Error server') . '</div>');
                        return redirect()->to('petugas_administrasi/proses_pengajuan_kuota/' . $id_pengajuan);
                    }
                } catch (\Exception $e) {
                     $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Error sistem upload: ' . $e->getMessage() . '</div>');
                     return redirect()->to('petugas_administrasi/proses_pengajuan_kuota/' . $id_pengajuan);
                }
            }
            $data_update_pengajuan['file_sk_petugas'] = $nama_file_sk;

            $this->db->table('user_pengajuan_kuota')->where('id', $id_pengajuan)->update($data_update_pengajuan);
            log_message('debug', 'PETUGAS_ADMINISTRASI PROSES PENGAJUAN KUOTA - user_pengajuan_kuota diupdate. Affected: ' . $this->db->affectedRows());

            if ($status_pengajuan == 'approved' && $data_update_pengajuan['approved_quota'] > 0) {
                $id_pers_terkait = $data['pengajuan']['id_pers'];
                $nama_barang_diajukan = $data['pengajuan']['nama_barang_kuota'];

                if ($id_pers_terkait && !empty($nama_barang_diajukan)) {
                    $data_kuota_barang = [
                        'id_pers' => $id_pers_terkait,
                        'id_pengajuan_kuota' => $id_pengajuan,
                        'nama_barang' => $nama_barang_diajukan,
                        'initial_quota_barang' => $data_update_pengajuan['approved_quota'],
                        'remaining_quota_barang' => $data_update_pengajuan['approved_quota'],
                        'nomor_skep_asal' => $data_update_pengajuan['nomor_sk_petugas'],
                        'tanggal_skep_asal' => $data_update_pengajuan['tanggal_sk_petugas'],
                        'status_kuota_barang' => 'active',
                        'dicatat_oleh_user_id' => $pa_user['id'],
                        'waktu_pencatatan' => date('Y-m-d H:i:s')
                    ];
                    $this->db->table('user_kuota_barang')->insert($data_kuota_barang);
                    $id_kuota_barang_baru = $this->db->insertID();
                    log_message('info', 'PETUGAS_ADMINISTRASI PROSES PENGAJUAN KUOTA - Data kuota barang baru disimpan. ID: ' . $id_kuota_barang_baru . ' untuk barang: ' . $nama_barang_diajukan);
                    
                    if ($id_kuota_barang_baru) {
                        $this->_logPerubahanKuota(
                            $id_pers_terkait, 'penambahan', $data_update_pengajuan['approved_quota'],
                            0, $data_update_pengajuan['approved_quota'],
                            'Persetujuan Pengajuan Kuota. Barang: ' . $nama_barang_diajukan . '. No. SK: ' . ($data_update_pengajuan['nomor_sk_petugas'] ?: '-'),
                            $id_pengajuan, 'pengajuan_kuota_disetujui', $pa_user['id'],
                            $nama_barang_diajukan, $id_kuota_barang_baru
                        );
                    }
                } else {
                    log_message('error', 'PETUGAS_ADMINISTRASI PROSES PENGAJUAN KUOTA - Gagal menambah kuota barang: id_pers atau nama_barang_kuota kosong. ID Pers: ' . $id_pers_terkait . ', Nama Barang: ' . $nama_barang_diajukan);
                }
            }

            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Pengajuan kuota telah berhasil diproses!</div>');
            return redirect()->to('petugas_administrasi/daftar_pengajuan_kuota');
        }
    }
    
    public function print_pengajuan_kuota($id_pengajuan)
    {
        $data['title'] = 'Detail Proses Pengajuan Kuota';
        $data['user_login'] = $this->db->table('user')->getWhere(['email' => $this->session->get('email')])->getRowArray();

        $builder = $this->db->table('user_pengajuan_kuota upk');
        $builder->select('upk.*, upr.NamaPers, upr.npwp AS npwp_perusahaan, upr.alamat as alamat_perusahaan, upr.pic, upr.jabatanPic, u.email AS user_email, u.name AS user_name_pengaju, u.image AS logo_perusahaan_file');
        $builder->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $builder->join('user u', 'upk.id_pers = u.id', 'left');
        $builder->where('upk.id', $id_pengajuan);
        $data['pengajuan'] = $builder->get()->getRowArray();

        if (!$data['pengajuan']) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Data pengajuan kuota tidak ditemukan.</div>');
            return redirect()->to('petugas_administrasi/daftar_pengajuan_kuota');
        }

        $data['user'] = $this->db->table('user')->getWhere(['id' => $data['pengajuan']['id_pers']])->getRowArray();
        $data['user_perusahaan'] = $this->db->table('user_perusahaan')->getWhere(['id_pers' => $data['pengajuan']['id_pers']])->getRowArray();

        return view('user/FormPengajuanKuota_print', $data);
    }
    
    public function detailPengajuanKuotaAdmin($id_pengajuan)
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Detail Proses Pengajuan Kuota';
        $data['user'] = $this->db->table('user')->getWhere(['email' => $this->session->get('email')])->getRowArray();

        $builder = $this->db->table('user_pengajuan_kuota upk');
        $builder->select('upk.*, upr.NamaPers, upr.npwp AS npwp_perusahaan, upr.alamat as alamat_perusahaan, upr.pic, upr.jabatanPic, u.email AS user_email_pemohon, u.name AS nama_pemohon');
        $builder->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $builder->join('user u', 'upk.id_pers = u.id', 'left');
        $builder->where('upk.id', $id_pengajuan);
        $data['pengajuan'] = $builder->get()->getRowArray();

        if (!$data['pengajuan']) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Data pengajuan kuota tidak ditemukan.</div>');
            return redirect()->to('petugas_administrasi/daftar_pengajuan_kuota');
        }

        return view('petugas_administrasi/detail_pengajuan_kuota_view', $data);
    }
    
    public function download_sk_kuota_admin($id_pengajuan)
    {
        $pengajuan = $this->db->table('user_pengajuan_kuota')->getWhere(['id' => $id_pengajuan])->getRowArray();

        if ($pengajuan && !empty($pengajuan['file_sk_petugas'])) {
            return redirect()->to('petugas_administrasi/downloadFile/' . $pengajuan['file_sk_petugas']);
        } else {
            $this->session->setFlashdata('message', '<div class="alert alert-warning" role="alert">Surat Keputusan belum tersedia.</div>');
            return redirect()->to('petugas_administrasi/daftar_pengajuan_kuota');
        }
    }

    public function histori_kuota_perusahaan($id_pers = 0)
    {
        log_message('debug', 'PETUGAS_ADMINISTRASI HISTORI KUOTA - Method dipanggil dengan id_pers: ' . $id_pers);

        if ($id_pers == 0 || !is_numeric($id_pers)) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">ID Perusahaan tidak valid.</div>');
            return redirect()->to('petugas_administrasi/monitoring_kuota');
        }

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Histori & Detail Kuota Perusahaan';
        $data['user'] = $this->db->table('user')->getWhere(['email' => $this->session->get('email')])->getRowArray();

        $data['perusahaan'] = $this->db->table('user_perusahaan up')
            ->select('up.id_pers, up.NamaPers, up.npwp, u.email as email_kontak, u.name as nama_kontak_user')
            ->join('user u', 'up.id_pers = u.id', 'left')
            ->where('up.id_pers', $id_pers)
            ->get()->getRowArray();
        
        log_message('debug', 'PETUGAS_ADMINISTRASI HISTORI KUOTA - Data Perusahaan: ' . print_r($data['perusahaan'], true));

        if (!$data['perusahaan']) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Data perusahaan tidak ditemukan untuk ID: ' . $id_pers . '</div>');
            return redirect()->to('petugas_administrasi/monitoring_kuota');
        }
        $data['id_pers_untuk_histori'] = $id_pers;

        $data['daftar_kuota_barang_perusahaan'] = $this->db->table('user_kuota_barang ukb')
            ->select('ukb.*')
            ->where('ukb.id_pers', $id_pers)
            ->orderBy('ukb.nama_barang ASC, ukb.waktu_pencatatan DESC')
            ->get()->getResultArray();
        log_message('debug', 'PETUGAS_ADMINISTRASI HISTORI KUOTA - Query Daftar Kuota Barang: ' . $this->db->getLastQuery());
        
        $data['histori_kuota_transaksi'] = $this->db->table('log_kuota_perusahaan lk')
            ->select('lk.*, u_admin.name as nama_pencatat')
            ->join('user u_admin', 'lk.dicatat_oleh_user_id = u_admin.id', 'left')
            ->where('lk.id_pers', $id_pers)
            ->orderBy('lk.tanggal_transaksi', 'DESC')
            ->orderBy('lk.id_log', 'DESC')
            ->get()->getResultArray();
        log_message('debug', 'PETUGAS_ADMINISTRASI HISTORI KUOTA - Query Log Transaksi: ' . $this->db->getLastQuery());

        return view('petugas_administrasi/histori_kuota_perusahaan_view', $data);
    }
    
    public function detail_permohonan_admin($id_permohonan = 0)
    {
        log_message('debug', 'PETUGAS_ADMINISTRASI DETAIL PERMOHONAN - Method dipanggil dengan id_permohonan: ' . $id_permohonan);

        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid.</div>');
            log_message('error', 'PETUGAS_ADMINISTRASI DETAIL PERMOHONAN - ID Permohonan tidak valid: ' . $id_permohonan);
            return redirect()->to('petugas_administrasi/permohonanMasuk');
        }

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Detail Permohonan Impor ID: ' . esc($id_permohonan);
        $data['user'] = $this->db->table('user')->getWhere(['email' => $this->session->get('email')])->getRowArray();

        $builder = $this->db->table('user_permohonan up');
        $builder->select('up.*, up.file_bc_manifest, upr.NamaPers, upr.npwp, u_pemohon.name as nama_pengaju_permohonan, u_pemohon.email as email_pengaju_permohonan, u_petugas.name as nama_petugas_pemeriksa');
        $builder->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $builder->join('user u_pemohon', 'upr.id_pers = u_pemohon.id', 'left');
        $builder->join('petugas p', 'up.petugas = p.id', 'left');
        $builder->join('user u_petugas', 'p.id_user = u_petugas.id', 'left');
        $builder->where('up.id', $id_permohonan);
        $data['permohonan_detail'] = $builder->get()->getRowArray();
        
        log_message('debug', 'PETUGAS_ADMINISTRASI DETAIL PERMOHONAN - Query Permohonan: ' . $this->db->getLastQuery());

        if (!$data['permohonan_detail']) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Data permohonan dengan ID ' . esc($id_permohonan) . ' tidak ditemukan.</div>');
            log_message('error', 'PETUGAS_ADMINISTRASI DETAIL PERMOHONAN - Data permohonan tidak ditemukan untuk ID: ' . $id_permohonan);
            return redirect()->to('petugas_administrasi/permohonanMasuk');
        }

        $data['lhp_detail'] = $this->db->table('lhp')->getWhere(['id_permohonan' => $id_permohonan])->getRowArray();
        $data['download_url_segment'] = 'petugas_administrasi/downloadFile/';

        log_message('debug', 'PETUGAS_ADMINISTRASI DETAIL PERMOHONAN - Data LHP: ' . print_r($data['lhp_detail'], true));

        return view('petugas_administrasi/detail_permohonan_view', $data);
    }
    
    public function hapus_permohonan($id_permohonan = 0)
    {
        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid untuk dihapus.</div>');
            return redirect()->to('petugas_administrasi/permohonanMasuk');
        }

        $permohonan = $this->db->table('user_permohonan')->getWhere(['id' => $id_permohonan])->getRowArray();

        if (!$permohonan) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Permohonan dengan ID '.esc($id_permohonan).' tidak ditemukan.</div>');
            return redirect()->to('petugas_administrasi/permohonanMasuk');
        }

        $file_path = WRITEPATH . 'uploads/bc_manifest/' . $permohonan['file_bc_manifest'];
        if (!empty($permohonan['file_bc_manifest']) && file_exists($file_path)) {
            if (@unlink($file_path)) {
                log_message('info', 'File BC Manifest ' . $permohonan['file_bc_manifest'] . ' berhasil dihapus untuk permohonan ID: ' . $id_permohonan . ' oleh Petugas Administrasi ID: ' . $this->session->get('user_id'));
            } else {
                log_message('error', 'Gagal menghapus file BC Manifest ' . $permohonan['file_bc_manifest'] . ' untuk permohonan ID: ' . $id_permohonan);
            }
        }
        
        if ($this->db->table('user_permohonan')->where('id', $id_permohonan)->delete()) {
            log_message('info', 'Permohonan ID ' . $id_permohonan . ' berhasil dihapus oleh Petugas Administrasi ID: ' . $this->session->get('user_id'));
            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Permohonan dengan ID Aju '.esc($id_permohonan).' berhasil dihapus.</div>');
        } else {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Gagal menghapus permohonan. Silakan coba lagi.</div>');
        }
        return redirect()->to('petugas_administrasi/permohonanMasuk');
    }
    
    public function edit_permohonan($id_permohonan = 0)
    {
        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid.</div>');
            return redirect()->to('petugas_administrasi/permohonanMasuk');
        }

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Edit Permohonan (Petugas Administrasi)';
        $data['user'] = $this->db->table('user')->getWhere(['email' => $this->session->get('email')])->getRowArray();

        $permohonan = $this->db->table('user_permohonan up')
            ->select('up.*, upr.NamaPers as NamaPerusahaanPemohon')
            ->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left')
            ->where('up.id', $id_permohonan)
            ->get()->getRowArray();

        if (!$permohonan) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Permohonan tidak ditemukan.</div>');
            return redirect()->to('petugas_administrasi/permohonanMasuk');
        }
        
        $data['permohonan_edit'] = $permohonan;
        $data['user_perusahaan_pemohon'] = $this->db->table('user_perusahaan')->getWhere(['id_pers' => $permohonan['id_pers']])->getRowArray();

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
            'JumlahBarang' => 'trim|required|numeric|greater_than[0]',
            'file_bc_manifest_pa_edit' => 'max_size[file_bc_manifest_pa_edit,2048]|ext_in[file_bc_manifest_pa_edit,pdf]'
        ];

        if (!$this->validate($rules)) {
            $data['validation'] = $this->validation;
            return view('petugas_administrasi/form_edit_permohonan', $data);
        } else {
            $id_kuota_barang_dipilih = (int)$this->request->getPost('id_kuota_barang_selected');
            $nama_barang_input_form = $this->request->getPost('NamaBarang');
            $jumlah_barang_dimohon = (float)$this->request->getPost('JumlahBarang');

            $kuota_valid_db = $this->db->table('user_kuota_barang')->getWhere([
                'id_kuota_barang' => $id_kuota_barang_dipilih,
                'id_pers' => $id_user_pemohon,
                'status_kuota_barang' => 'active'
            ])->getRowArray();

            if (!$kuota_valid_db) {
                $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Data kuota barang tidak valid.</div>');
                return redirect()->to('petugas_administrasi/edit_permohonan/' . $id_permohonan);
            }
            if ($kuota_valid_db['nama_barang'] != $nama_barang_input_form) {
                $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Nama barang tidak sesuai dengan kuota yang dipilih.</div>');
                return redirect()->to('petugas_administrasi/edit_permohonan/' . $id_permohonan);
            }
            
            $sisa_kuota_efektif_untuk_validasi = (float)$kuota_valid_db['remaining_quota_barang'];
            if ($permohonan['id_kuota_barang_digunakan'] == $id_kuota_barang_dipilih) {
                $sisa_kuota_efektif_untuk_validasi += (float)$permohonan['JumlahBarang'];
            }
            if ($jumlah_barang_dimohon > $sisa_kuota_efektif_untuk_validasi) {
                $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Jumlah barang dimohon melebihi sisa kuota efektif.</div>');
                return redirect()->to('petugas_administrasi/edit_permohonan/' . $id_permohonan);
            }

            $nama_file_bc_manifest_update = $permohonan['file_bc_manifest'];
            $file_bc_upload = $this->request->getFile('file_bc_manifest_pa_edit');
            if ($file_bc_upload && $file_bc_upload->isValid()) {
                try {
                    $tobaUploader = new TobaUploader();
                    $uploadResult = $tobaUploader->upload($file_bc_upload);
                    if ($uploadResult && $uploadResult['status'] === 'success') {
                        $nama_file_bc_manifest_update = $uploadResult['data']['keyFile'];
                    } else {
                         $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Gagal upload BC Manifest: ' . ($uploadResult['message'] ?? 'Error server') . '</div>');
                         return redirect()->to('petugas_administrasi/edit_permohonan/' . $id_permohonan);
                    }
                } catch (\Exception $e) {
                    $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Error sistem upload: ' . $e->getMessage() . '</div>');
                    return redirect()->to('petugas_administrasi/edit_permohonan/' . $id_permohonan);
                }
            }

            $data_update = [
                'nomorSurat' => $this->request->getPost('nomorSurat'),
                'TglSurat' => $this->request->getPost('TglSurat'),
                'NamaBarang' => $nama_barang_input_form,
                'JumlahBarang' => $jumlah_barang_dimohon,
                'id_kuota_barang_digunakan' => $id_kuota_barang_dipilih,
                'NoSkep' => $kuota_valid_db['nomor_skep_asal'],
                'file_bc_manifest' => $nama_file_bc_manifest_update,
            ];

            $this->db->table('user_permohonan')->where('id', $id_permohonan)->update($data_update);
            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Permohonan berhasil diupdate oleh Petugas Administrasi.</div>');
            return redirect()->to('petugas_administrasi/detail_permohonan_admin/' . $id_permohonan);
        }
    }
    
    public function tolak_permohonan_awal($id_permohonan = 0)
    {
        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid.</div>');
            return redirect()->to('petugas_administrasi/permohonanMasuk');
        }

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Formulir Penolakan Permohonan';
        $data['user'] = $this->db->table('user')->getWhere(['email' => $this->session->get('email')])->getRowArray();

        $data['permohonan'] = $this->db->table('user_permohonan up')
            ->select('up.id, up.nomorSurat, upr.NamaPers, up.status')
            ->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left')
            ->where('up.id', $id_permohonan)
            ->get()->getRowArray();

        if (!$data['permohonan'] || $data['permohonan']['status'] != '0') {
            $this->session->setFlashdata('message', '<div class="alert alert-warning" role="alert">Permohonan ini tidak ditemukan atau statusnya bukan "Baru Masuk" sehingga tidak bisa ditolak langsung.</div>');
            return redirect()->to('petugas_administrasi/permohonanMasuk');
        }

        if (!$this->validate(['alasan_penolakan' => 'trim|required'])) {
            $data['validation'] = $this->validation;
            return view('petugas_administrasi/form_tolak_permohonan_view', $data);
        } else {
            $alasan_penolakan = $this->request->getPost('alasan_penolakan', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $update_data = [
                'status' => '6', // Ditolak di awal
                'catatan_penolakan' => $alasan_penolakan,
                'time_selesai' => date('Y-m-d H:i:s')
            ];

            $this->db->table('user_permohonan')->where('id', $id_permohonan)->update($update_data);

            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Permohonan ID ' . esc($id_permohonan) . ' berhasil ditolak.</div>');
            return redirect()->to('petugas_administrasi/permohonanMasuk');
        }
    }

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
            log_message('error', '[TobaUploader Download Exception - Petugas Admin] ' . $e->getMessage());
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Gagal memproses download.');
        }
    }

}
