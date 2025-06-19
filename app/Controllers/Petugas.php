<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Config\Services;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;

class Petugas extends BaseController
{
    protected $db;
    protected $session;
    protected $validation;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->session = Services::session();
        $this->validation = Services::validation();
        helper(['url', 'form', 'download']);

        $this->_checkAuthPetugas();
    }

    private function _checkAuthPetugas()
    {
        $method = Services::router()->methodName();
        $excluded_methods = ['logout', 'force_change_password_page', 'edit_profil', 'setup_mfa', 'verify_mfa', 'reset_mfa'];

        if (in_array($method, $excluded_methods)) {
            return;
        }

        if ($this->session->get('force_change_password') == 1) {
            $this->session->setFlashdata('message', '<div class="alert alert-warning" role="alert">Untuk keamanan, Anda wajib mengganti password Anda terlebih dahulu.</div>');
            // Use return with redirect to ensure script execution stops.
            return redirect()->to('petugas/force_change_password_page');
        }

        if ($this->session->get('role_id') != 3) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Akses Ditolak! Anda tidak diotorisasi untuk mengakses area Petugas.</div>');
            $role_id_session = $this->session->get('role_id');
            $redirect_url = 'auth/blocked';
            if ($role_id_session == 1) $redirect_url = 'admin';
            elseif ($role_id_session == 2) $redirect_url = 'user';
            elseif ($role_id_session == 4) $redirect_url = 'monitoring';
            return redirect()->to($redirect_url);
        }

        if ($this->session->get('is_active') === 0) {
            $this->session->setFlashdata('message', '<div class="alert alert-warning" role="alert">Akun Petugas Anda tidak aktif. Hubungi Administrator.</div>');
            return redirect()->to('auth/blocked');
        }
    }
    
    public function index()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Dashboard Petugas';
        $data['user'] = $this->db->table('user')->getWhere(['email' => $this->session->get('email')])->getRowArray();
        $petugas_user_id = $data['user']['id'];

        $petugas_detail = $this->db->table('petugas')->getWhere(['id_user' => $petugas_user_id])->getRowArray();
        $petugas_id_in_petugas_table = $petugas_detail ? $petugas_detail['id'] : null;

        if ($petugas_id_in_petugas_table) {
            $data['jumlah_tugas_lhp'] = $this->db->table('user_permohonan')
                ->where('petugas', $petugas_id_in_petugas_table)
                ->where('status', '1')
                ->countAllResults();
        } else {
            $data['jumlah_tugas_lhp'] = 0;
        }
        
        $data['jumlah_lhp_selesai'] = $this->db->table('lhp')
            ->where('id_petugas_pemeriksa', $petugas_user_id)
            ->countAllResults();

        return view('petugas/dashboard_petugas_view', $data);
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

        $renderer = new ImageRenderer(new RendererStyle(400), new SvgImageBackEnd());
        $writer = new Writer($renderer);
        $qrCodeImage = $writer->writeString($qrCodeUrl);
        $qrCodeDataUri = 'data:image/svg+xml;base64,' . base64_encode($qrCodeImage);

        $data['qr_code_data_uri'] = $qrCodeDataUri;
        $data['secret_key'] = $secretKey;

        return view('petugas/mfa_setup', $data);
    }

    public function verify_mfa()
    {
        $userId = $this->session->get('user_id');
        $user = $this->db->table('user')->getWhere(['id' => $userId])->getRowArray();
        $secret = $user['google2fa_secret'];
        $oneTimePassword = $this->request->getPost('one_time_password');

        $google2fa = new Google2FA();
        if ($google2fa->verifyKey($secret, $oneTimePassword)) {
            $this->db->table('user')->where('id', $userId)->update(['is_mfa_enabled' => 1]);
            $this->session->set('mfa_verified', true);
            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Autentikasi Dua Faktor (MFA) berhasil diaktifkan!</div>');
            return redirect()->to('petugas/index');
        } else {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Kode verifikasi salah. Silakan coba lagi.</div>');
            return redirect()->to('petugas/setup_mfa');
        }
    }

    public function reset_mfa()
    {
        $user_id = $this->session->get('user_id');
        $this->db->table('user')->where('id', $user_id)->update(['is_mfa_enabled' => 0, 'google2fa_secret' => null]);
        $this->session->remove('mfa_verified');
        $this->session->setFlashdata('message', '<div class="alert alert-info" role="alert">MFA Anda telah dinonaktifkan. Silakan lakukan pengaturan ulang.</div>');
        return redirect()->to('petugas/setup_mfa');
    }

    public function daftar_pemeriksaan()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Daftar Pemeriksaan Ditugaskan';
        $data['user'] = $this->db->table('user')->getWhere(['email' => $this->session->get('email')])->getRowArray();
        $petugas_user_id = $data['user']['id'];

        $petugas_detail = $this->db->table('petugas')->getWhere(['id_user' => $petugas_user_id])->getRowArray();
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
            $builder->where('up.status', '1');
            $builder->orderBy('up.TglSuratTugas DESC, up.WaktuPenunjukanPetugas DESC');
            $data['daftar_tugas'] = $builder->get()->getResultArray();
        }

        return view('petugas/daftar_pemeriksaan_view', $data);
    }

    public function rekam_lhp($id_permohonan = 0)
    {
        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid.</div>');
            return redirect()->to('petugas/daftar_pemeriksaan');
        }

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Perekaman Laporan Hasil Pemeriksaan (LHP) - ID Aju: ' . esc($id_permohonan);
        $data['user'] = $this->db->table('user')->getWhere(['email' => $this->session->get('email')])->getRowArray();
        $petugas_user_id = $data['user']['id'];

        $petugas_detail_db = $this->db->table('petugas')->getWhere(['id_user' => $petugas_user_id])->getRowArray();
        $petugas_id_for_permohonan = $petugas_detail_db ? $petugas_detail_db['id'] : null;

        $builder = $this->db->table('user_permohonan up');
        $builder->select('up.*, upr.NamaPers, upr.npwp, u_pemohon.name as nama_pemohon');
        $builder->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $builder->join('user u_pemohon', 'upr.id_pers = u_pemohon.id', 'left');
        $builder->where('up.id', $id_permohonan)->where('up.status', '1');
        if ($petugas_id_for_permohonan) {
            $builder->where('up.petugas', $petugas_id_for_permohonan);
        } else {
             $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Detail petugas tidak valid atau tidak ditemukan.</div>');
             return redirect()->to('petugas/daftar_pemeriksaan');
        }
        $permohonan = $builder->get()->getRowArray();

        if (!$permohonan) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Tugas tidak ditemukan atau status tidak sesuai.</div>');
            return redirect()->to('petugas/daftar_pemeriksaan');
        }
        $data['permohonan'] = $permohonan;
        $data['lhp_data'] = $this->db->table('lhp')->getWhere(['id_permohonan' => $id_permohonan])->getRowArray();

        if (strtolower($this->request->getMethod()) !== 'post') {
            return view('petugas/form_rekam_lhp_view', $data);
        }
        
        // =================================================================
        // LOGIKA VALIDASI
        // =================================================================
        $rules = [
            'NoLHP' => 'trim|required',
            'TglLHP' => 'trim|required|valid_date',
            'JumlahBenar' => 'trim|required|numeric|greater_than_equal_to[0]',
            'JumlahSalah' => 'trim|required|numeric|greater_than_equal_to[0]',
            'Catatan' => 'trim',
        ];

        $fileLHP = $this->request->getFile('FileLHP');
        $fileDoc = $this->request->getFile('file_dokumentasi_foto');

        // File LHP wajib jika ini adalah LHP baru (belum ada file tersimpan)
        if (empty($data['lhp_data']['FileLHP'])) {
            $rules['FileLHP'] = 'uploaded[FileLHP]|max_size[FileLHP,2048]|ext_in[FileLHP,pdf,doc,docx,jpg,jpeg,png]';
        } 
        // Jika LHP sudah ada, file tidak wajib, tapi jika diupload, harus valid
        elseif ($fileLHP && $fileLHP->isValid()) {
            $rules['FileLHP'] = 'max_size[FileLHP,2048]|ext_in[FileLHP,pdf,doc,docx,jpg,jpeg,png]';
        }

        // File dokumentasi selalu opsional, tapi jika diupload, harus valid
        if ($fileDoc && $fileDoc->isValid()) {
             $rules['file_dokumentasi_foto'] = 'max_size[file_dokumentasi_foto,2048]|is_image[file_dokumentasi_foto]';
        }
 
        if (!$this->validate($rules)) {
            $data['validation'] = $this->validation;
            return view('petugas/form_rekam_lhp_view', $data);
        }

        $data_lhp_to_save = [
            'id_permohonan' => $id_permohonan,
            'id_petugas_pemeriksa' => $petugas_user_id,
            'NoLHP' => $this->request->getPost('NoLHP'),
            'TglLHP' => $this->request->getPost('TglLHP'),
            'JumlahAju' => (int) ($permohonan['JumlahBarang'] ?? 0),
            'JumlahBenar' => (int) $this->request->getPost('JumlahBenar'),
            'JumlahSalah' => (int) $this->request->getPost('JumlahSalah'),
            'Catatan' => $this->request->getPost('Catatan'),
        ];
        if (empty($data['lhp_data'])) {
            $data_lhp_to_save['submit_time'] = date('Y-m-d H:i:s');
        }

        // Handle FileLHP upload
        $fileLHP = $this->request->getFile('FileLHP');
        if ($fileLHP && $fileLHP->isValid() && !$fileLHP->hasMoved()) {
            $upload_dir_lhp = WRITEPATH . 'uploads/lhp/';
            if (!empty($data['lhp_data']['FileLHP'])) @unlink($upload_dir_lhp . $data['lhp_data']['FileLHP']);
            $newName = $fileLHP->getRandomName();
            $fileLHP->move($upload_dir_lhp, $newName);
            $data_lhp_to_save['FileLHP'] = $newName;
        }

        // Handle file_dokumentasi_foto upload
        $fileDoc = $this->request->getFile('file_dokumentasi_foto');
        if ($fileDoc && $fileDoc->isValid() && !$fileDoc->hasMoved()) {
            $upload_dir_doc = WRITEPATH . 'uploads/dokumentasi_lhp/';
            if (!empty($data['lhp_data']['file_dokumentasi_foto'])) @unlink($upload_dir_doc . $data['lhp_data']['file_dokumentasi_foto']);
            $newName = $fileDoc->getRandomName();
            $fileDoc->move($upload_dir_doc, $newName);
            $data_lhp_to_save['file_dokumentasi_foto'] = $newName;
        }

        if (!empty($data['lhp_data'])) {
            $this->db->table('lhp')->where('id', $data['lhp_data']['id'])->update($data_lhp_to_save);
            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">LHP berhasil diperbarui!</div>');
        } else {
            $this->db->table('lhp')->insert($data_lhp_to_save);
            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">LHP berhasil direkam!</div>');
        }

        $this->db->table('user_permohonan')->where('id', $id_permohonan)->update(['status' => '2']);
        return redirect()->to('petugas/daftar_pemeriksaan');
    }

    public function force_change_password_page()
    {
        if (!$this->session->get('email') || $this->session->get('force_change_password') != 1) {
            return redirect()->to('petugas/index');
        }
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Wajib Ganti Password (Petugas)';
        $data['user'] = $this->db->table('user')->getWhere(['email' => $this->session->get('email')])->getRowArray();
        
        $rules = [
            'new_password' => 'required|trim|min_length[6]|matches[confirm_new_password]',
            'confirm_new_password' => 'required|trim'
        ];
        if (!$this->validate($rules)) {
            $data['validation'] = $this->validation;
            return view('petugas/form_force_change_password', $data);
        } else {
            $new_password_hash = password_hash($this->request->getPost('new_password'), PASSWORD_DEFAULT);
            $this->db->table('user')->where('id', $data['user']['id'])->update(['password' => $new_password_hash, 'force_change_password' => 0]);
            $this->session->set('force_change_password', 0);
            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Password Anda telah berhasil diubah. Selamat datang di dashboard Anda.</div>');
            return redirect()->to('petugas/index');
        }
    }

    public function edit_profil()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Edit Profil Saya';
        $user_email = $this->session->get('email');
        $data['user'] = $this->db->table('user')->getWhere(['email' => $user_email])->getRowArray();
        $user_id = $data['user']['id'];
        $data['petugas_detail'] = $this->db->table('petugas')->getWhere(['id_user' => $user_id])->getRowArray();

        if (strtolower($this->request->getMethod()) === 'post') {
            $file = $this->request->getFile('profile_image');
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $rules = ['profile_image' => 'uploaded[profile_image]|max_size[profile_image,2048]|is_image[profile_image]'];
                if (!$this->validate($rules)) {
                    $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Upload Foto Profil Gagal: ' . $this->validation->getError('profile_image') . '</div>');
                } else {
                    $upload_dir = WRITEPATH . 'uploads/profile_images/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                    
                    $old_image = $data['user']['image'];
                    if ($old_image != 'default.jpg' && !empty($old_image) && file_exists($upload_dir . $old_image)) {
                        @unlink($upload_dir . $old_image);
                    }
                    $newName = $file->getRandomName();
                    $file->move($upload_dir, $newName);
                    $this->db->table('user')->where('id', $user_id)->update(['image' => $newName]);
                    $this->session->set('user_image', $newName);
                    $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Foto profil berhasil diupdate.</div>');
                }
            }
            return redirect()->to('petugas/edit_profil');
        }
        
        $data['user'] = $this->db->table('user')->getWhere(['email' => $user_email])->getRowArray();
        return view('petugas/form_edit_profil_petugas', $data);
    }

    public function riwayat_lhp_direkam()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Riwayat LHP yang Telah Direkam';
        $data['user'] = $this->db->table('user')->getWhere(['email' => $this->session->get('email')])->getRowArray();
        $petugas_user_id = $data['user']['id'];

        $builder = $this->db->table('lhp');
        $builder->select('lhp.*, up.nomorSurat as nomor_surat_permohonan, up.TglSurat as tanggal_surat_permohonan, upr.NamaPers as nama_perusahaan_pemohon, up.status as status_permohonan_terkini, up.NamaBarang as nama_barang_permohonan');
        $builder->join('user_permohonan up', 'lhp.id_permohonan = up.id', 'left');
        $builder->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $builder->where('lhp.id_petugas_pemeriksa', $petugas_user_id); 
        $builder->orderBy('lhp.submit_time', 'DESC'); 
        $data['riwayat_lhp'] = $builder->get()->getResultArray();

        return view('petugas/riwayat_lhp_direkam_view', $data);
    }
    
    public function detail_lhp_direkam($id_lhp = 0)
    {
        if ($id_lhp == 0 || !is_numeric($id_lhp)) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">ID LHP tidak valid.</div>');
            return redirect()->to('petugas/riwayat_lhp_direkam'); 
        }

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Detail LHP Direkam (ID LHP: '.esc($id_lhp).')';
        $data['user'] = $this->db->table('user')->getWhere(['email' => $this->session->get('email')])->getRowArray();
        
        $builder = $this->db->table('lhp');
        $builder->select('lhp.*, up.id as id_permohonan_ajuan, up.nomorSurat as nomor_surat_permohonan, up.TglSurat as tanggal_surat_pemohon, up.NamaBarang as nama_barang_di_permohonan, up.JumlahBarang as jumlah_barang_di_permohonan, upr.NamaPers as nama_perusahaan_pemohon, upr.npwp as npwp_perusahaan');
        $builder->join('user_permohonan up', 'lhp.id_permohonan = up.id', 'left');
        $builder->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $builder->where('lhp.id', $id_lhp); 
        $builder->where('lhp.id_petugas_pemeriksa', $data['user']['id']); 
        $data['lhp_detail'] = $builder->get()->getRowArray();

        if (!$data['lhp_detail']) {
            $this->session->setFlashdata('message', '<div class="alert alert-warning" role="alert">Detail LHP tidak ditemukan atau Anda tidak memiliki akses untuk melihatnya.</div>');
            return redirect()->to('petugas/riwayat_lhp_direkam');
        }

        return view('petugas/detail_lhp_view', $data);
    }

    public function monitoring_permohonan()
    {
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Monitoring Seluruh Permohonan Impor';
        $data['user'] = $this->db->table('user')->getWhere(['email' => $this->session->get('email')])->getRowArray();

        $builder = $this->db->table('user_permohonan up');
        $builder->select('up.id, up.nomorSurat, up.TglSurat, up.time_stamp, up.status, upr.NamaPers, u_pemohon.name as nama_pengaju_permohonan, u_petugas_pemeriksa.name as nama_petugas_pemeriksa');
        $builder->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $builder->join('user u_pemohon', 'upr.id_pers = u_pemohon.id', 'left'); 
        $builder->join('petugas p_pemeriksa', 'up.petugas = p_pemeriksa.id', 'left'); 
        $builder->join('user u_petugas_pemeriksa', 'p_pemeriksa.id_user = u_petugas_pemeriksa.id', 'left'); 
        $builder->orderBy("CASE up.status WHEN '0' THEN 1 WHEN '5' THEN 2 WHEN '1' THEN 3 WHEN '2' THEN 4 ELSE 5 END ASC, up.time_stamp DESC");
        $data['permohonan_list'] = $builder->get()->getResultArray();
        
        return view('petugas/monitoring_permohonan_view', $data);
    }
    
    public function detail_monitoring_permohonan($id_permohonan = 0)
    {
        if ($id_permohonan == 0 || !is_numeric($id_permohonan)) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">ID Permohonan tidak valid.</div>');
            return redirect()->to('petugas/monitoring_permohonan');
        }

        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Detail Permohonan Impor (Monitoring) ID: ' . esc($id_permohonan);
        $data['user'] = $this->db->table('user')->getWhere(['email' => $this->session->get('email')])->getRowArray();

        $builder = $this->db->table('user_permohonan up');
        $builder->select('up.*, upr.NamaPers, upr.npwp AS npwp_perusahaan, upr.alamat AS alamat_perusahaan, upr.NoSkep AS NoSkep_perusahaan, u_pemohon.name as nama_pengaju_permohonan, u_pemohon.email as email_pengaju_permohonan, p_pemeriksa.NIP as nip_petugas_pemeriksa, u_petugas.name as nama_petugas_pemeriksa');
        $builder->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $builder->join('user u_pemohon', 'upr.id_pers = u_pemohon.id', 'left');
        $builder->join('petugas p_pemeriksa', 'up.petugas = p_pemeriksa.id', 'left');
        $builder->join('user u_petugas', 'p_pemeriksa.id_user = u_petugas.id', 'left');
        $builder->where('up.id', $id_permohonan);
        $data['permohonan_detail'] = $builder->get()->getRowArray();

        if (!$data['permohonan_detail']) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Data permohonan dengan ID ' . esc($id_permohonan) . ' tidak ditemukan.</div>');
            return redirect()->to('petugas/monitoring_permohonan');
        }

        $data['lhp_detail'] = $this->db->table('lhp')->getWhere(['id_permohonan' => $id_permohonan])->getRowArray();
        
        // This is a special case where a Petugas might see a User's detailed view
        return view('user/detail_permohonan_view', $data);
    }
}
