<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use PragmaRX\Google2FA\Google2FA;

class Auth extends BaseController
{
    protected $session;
    protected $db;
    protected $validation;
    protected $helpers = ['url', 'form', 'session'];

    public function __construct()
    {
        // Inisialisasi properti di constructor, sesuai standar CI4
        // Kode ini akan secara otomatis memuat koneksi database dari .env
        $this->session = \Config\Services::session();
        $this->db = \Config\Database::connect();
        $this->validation = \Config\Services::validation();
        helper($this->helpers);
    }

    public function index()
    {
        // Jika sudah login, redirect
        if ($this->session->get('email')) {
            return $this->_redirect_user_by_role($this->session->get('is_active'));
        }

        // Aturan validasi
        $this->validation->setRules([
            'login_identifier' => ['label' => 'Email / NIP', 'rules' => 'required|trim', 'errors' => ['required' => 'Kolom {field} wajib diisi.']],
            'password' => ['label' => 'Password', 'rules' => 'required|trim', 'errors' => ['required' => 'Kolom {field} wajib diisi.']]
        ]);

        // Jalankan validasi
        if (!$this->validation->withRequest($this->request)->run()) {
            $data['title'] = "REPACK Login";
            // Di CI4, view menampilkan error validasi secara otomatis
            return view('auth/login', $data);
        } else {
            // Jika validasi berhasil, panggil _login()
            return $this->_login();
        }
    }

    private function _login()
    {
        $login_identifier = $this->request->getPost('login_identifier');
        $password = $this->request->getPost('password');
        
        // Query diperbaiki, hanya mencari berdasarkan email
        $user = $this->db->table('user')
            ->where('email', $login_identifier)
            ->get()->getRowArray();

        if ($user) {
            // Cek status aktif
            if ($user['is_active'] == 0 && $user['role_id'] != 2) {
                $this->session->setFlashdata('message', 'Akun Anda tidak aktif!');
                return redirect()->back()->withInput();
            }

            // Verifikasi password
            if (password_verify($password, $user['password'])) {
                
                // Jika hash password perlu diperbarui ke standar terbaru (opsional tapi bagus)
                if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                    $new_hash = password_hash($password, PASSWORD_DEFAULT);
                    $this->db->table('user')->where('id', $user['id'])->update(['password' => $new_hash]);
                }

                // Cek jika MFA diaktifkan
                if ($user['is_mfa_enabled'] ?? 0) {
                    // Buat sesi login awal dengan penanda MFA belum terverifikasi
                    $this->session->set('mfa_pending_user_id', $user['id']);
                    return redirect()->to(base_url('auth/verify_mfa_login'));
                } else {
                    // Jika MFA tidak aktif, langsung buat sesi login penuh
                    $this->_establish_session($user, true);
                    return $this->_handle_post_login_redirect($user);
                }

            } else {
                // Password salah
                $this->session->setFlashdata('message', 'Password salah!');
                return redirect()->back()->withInput();
            }
        } else {
            // User tidak ditemukan
            $this->session->setFlashdata('message', 'Email atau NIP tidak terdaftar!');
            return redirect()->back()->withInput();
        }
    }

    public function verify_mfa_login()
    {
        // Cek sesi sementara, bukan sesi login penuh
        $pendingUserId = $this->session->get('mfa_pending_user_id');

        if (!$pendingUserId) {
            // Jika tidak ada user yang sedang menunggu MFA, kembalikan ke login
            return redirect()->to(base_url('auth'));
        }

        if (strtolower($this->request->getMethod()) === 'post') {
            $this->validation->setRules(['mfa_code' => ['label' => 'Kode MFA', 'rules' => 'required|trim|numeric']]);

            if (!$this->validation->withRequest($this->request)->run()) {
                return redirect()->back()->withInput();
            }

            // Ambil user dari DB menggunakan ID sementara
            $user = $this->db->table('user')->where('id', $pendingUserId)->get()->getRowArray();
            $mfaCode = $this->request->getPost('mfa_code');

            if (!$user) {
                $this->session->remove('mfa_pending_user_id'); // Bersihkan sesi sementara
                $this->session->setFlashdata('message', 'Sesi MFA tidak valid. Silakan login kembali.');
                return redirect()->to(base_url('auth'));
            }

            $google2fa = new Google2FA();
            if ($google2fa->verifyKey($user['google2fa_secret'], $mfaCode)) {
                // Kode benar! Hapus sesi sementara dan buat sesi login penuh
                $this->session->remove('mfa_pending_user_id');
                $this->_establish_session($user, true); // mfa_verified di sini true
                return $this->_handle_post_login_redirect($user);
            } else {
                // Kode salah, kembali ke halaman verifikasi
                $this->session->setFlashdata('message', 'Kode verifikasi salah!');
                return redirect()->to(base_url('auth/verify_mfa_login'));
            }
        }

        $data['title'] = 'Verifikasi Dua Faktor';
        return view('auth/mfa_verify_page', $data);
    }

    
    private function _establish_session($user, $mfa_verified = false)
    {
        // Hapus session sementara jika ada
        $this->session->remove('mfa_user_id');

        $data_session = [
            'user_id'   => $user['id'],
            'email'     => $user['email'],
            'role_id'   => $user['role_id'],
            'name'      => $user['name'],
            'image'     => $user['image'] ?? 'default.jpg',
            'is_active' => $user['is_active'],
            'force_change_password' => $user['force_change_password'] ?? 0,
            // Jika MFA aktif, status verifikasi tergantung parameter. Jika tidak, selalu true.
            'mfa_verified' => $mfa_verified || !($user['is_mfa_enabled'] ?? 0)
        ];
        $this->session->set($data_session);
    }

    private function _handle_post_login_redirect($user)
    {
        // Cek apakah pengguna perlu ganti password
        if (($user['force_change_password'] ?? 0) == 1) {
            $this->session->setFlashdata('message', 'Untuk keamanan, Anda wajib mengganti password Anda.');
            if ($user['role_id'] == 2) { 
                return redirect()->to(base_url('user/force_change_password_page')); 
            } elseif ($user['role_id'] == 3) { 
                return redirect()->to(base_url('petugas/force_change_password_page')); 
            }
        }
        // Jika tidak, redirect berdasarkan role
        return $this->_redirect_user_by_role($user['is_active']);
    }

    private function _redirect_user_by_role($is_active = 1)
    {
        $role_id = $this->session->get('role_id');

        // Handle kasus pengguna jasa yang belum aktif
        if ($role_id == 2 && $is_active == 0) {
            $this->session->setFlashdata('message', 'Akun Anda belum aktif. Silakan lengkapi profil perusahaan Anda untuk aktivasi.');
            return redirect()->to(base_url('user/edit'));
        }

        $roleRedirects = [
            1 => 'admin',
            2 => 'user',
            3 => 'petugas',
            4 => 'monitoring',
            5 => 'petugas_administrasi/index'
        ];

        if (array_key_exists($role_id, $roleRedirects)) {
            return redirect()->to(base_url($roleRedirects[$role_id]));
        }

        // Jika role tidak ditemukan, hancurkan sesi dan kembali ke login
        $this->session->setFlashdata('message', 'Role tidak dikenal. Silakan login kembali.');
        $this->session->destroy();
        return redirect()->to(base_url('auth'));
    }

    public function registration()
    {
        if ($this->session->get('email')) {
            return $this->_redirect_user_by_role($this->session->get('is_active'));
        }

        if($this->request->getMethod() === 'post') {
            $this->validation->setRules([
                'name' => ['label' => 'Name', 'rules' => 'required|trim'],
                'email' => ['label' => 'Email', 'rules' => 'required|trim|valid_email|is_unique[user.email]', 'errors' => ['is_unique' => 'Email ini sudah terdaftar!']],
                'password' => ['label' => 'Password', 'rules' => 'required|trim|min_length[3]|matches[password2]', 'errors' => ['matches' => 'Password tidak cocok!', 'min_length' => 'Password terlalu pendek!']],
                'password2' => ['label' => 'Repeat Password', 'rules' => 'required|trim|matches[password]']
            ]);

            if (!$this->validation->withRequest($this->request)->run()) {
                return redirect()->back()->withInput();
            } else {
                $data_insert = [
                    'name' => htmlspecialchars($this->request->getPost('name', true)),
                    'email' => htmlspecialchars($this->request->getPost('email', true)),
                    'image' => 'default.jpg',
                    'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                    'role_id' => 2,
                    'is_active' => 0,
                    'force_change_password' => 0,
                    'date_created' => time()
                ];
                $this->db->table('user')->insert($data_insert);
                $this->session->setFlashdata('message', 'Akun berhasil diregistrasi! Silakan login untuk melengkapi profil.');
                return redirect()->to(base_url('auth'));
            }
        }
        
        $data['title'] = "REPACK Registration";
        return view('auth/registration', $data);
    }

    public function logout()
    {
        $this->session->destroy();
        $this->session->setFlashdata('message', 'Anda telah berhasil logout!');
        return redirect()->to(base_url('auth'));
    }

    public function blocked()
    {
        $data['title'] = 'Access Denied';
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        return view('auth/blocked', $data);
    }

    // Fungsi bypass dan changepass tetap sama dengan migrasi sebelumnya
    public function bypass($id_or_email)
    {
        $user = $this->db->table('user')->where('email', $id_or_email)->orWhere('id', $id_or_email)->get()->getRowArray();

        if ($user) {
            $this->_establish_session($user);
            return $this->_handle_post_login_redirect($user);
        } else {
            $this->session->setFlashdata('message', 'Bypass failed: User not found!');
            return redirect()->to(base_url('auth'));
        }
    }

    public function changepass($user_id = null)
    {
        // Check if user is logged in
        if (!session()->get('logged_in')) {
            session()->setFlashdata('message', '<div class="alert alert-danger">Please login first.</div>');
            return redirect()->to('auth/login');
        }

        // Check if changing own password or if admin changing someone else's
        $current_user_id = session()->get('user_id');
        $current_user_role = session()->get('role_id');
        
        // If no user_id provided, user is changing their own password
        if ($user_id === null) {
            $user_id = $current_user_id;
        }
        
        // Validate user ID
        if (!$user_id || !is_numeric($user_id)) {
            session()->setFlashdata('message', '<div class="alert alert-danger">Invalid user ID.</div>');
            return redirect()->to($this->getDashboardUrl($current_user_role));
        }

        // Check permissions - users can only change their own password unless they're admin
        if ($user_id != $current_user_id && $current_user_role != 1) {
            session()->setFlashdata('message', '<div class="alert alert-danger">You can only change your own password.</div>');
            return redirect()->to($this->getDashboardUrl($current_user_role));
        }

        // Get user data from database
        $db = \Config\Database::connect();
        $user_to_change = $db->table('user')->where('id', $user_id)->get()->getRowArray();
        
        if (!$user_to_change) {
            session()->setFlashdata('message', '<div class="alert alert-danger">User not found.</div>');
            return redirect()->to($this->getDashboardUrl($current_user_role));
        }

        // Determine appropriate redirect URLs
        $cancel_redirect_url = $this->getDashboardUrl($current_user_role);
        $success_redirect_url = $this->getDashboardUrl($current_user_role);
        
        // If admin is changing someone else's password, redirect to user management
        if ($user_id != $current_user_id && $current_user_role == 1) {
            $cancel_redirect_url = 'admin/manajemen_user';
            $success_redirect_url = 'admin/manajemen_user';
        }

        $data = [
            'title' => 'Change Password',
            'user_for_pass_change' => $user_to_change,
            'validation' => \Config\Services::validation(),
            'cancel_redirect_url' => $cancel_redirect_url
        ];

        // Handle form submission
        if ($this->request->getMethod() === 'post') {
            // Validation rules
            $rules = [
                'password' => [
                    'label' => 'New Password',
                    'rules' => 'required|trim|min_length[6]|matches[password2]',
                    'errors' => [
                        'required' => '{field} is required.',
                        'min_length' => '{field} must be at least 6 characters long.',
                        'matches' => '{field} does not match the confirmation password.'
                    ]
                ],
                'password2' => [
                    'label' => 'Repeat Password',
                    'rules' => 'required|trim|matches[password]',
                    'errors' => [
                        'required' => '{field} is required.',
                        'matches' => '{field} does not match the new password.'
                    ]
                ]
            ];

            // Add current password validation if user is changing their own password
            if ($user_id == $current_user_id) {
                $rules['current_password'] = [
                    'label' => 'Current Password',
                    'rules' => 'required|trim|callback_check_current_password',
                    'errors' => [
                        'required' => '{field} is required.',
                        'callback_check_current_password' => 'Current password is incorrect.'
                    ]
                ];
            }

            if ($this->validate($rules)) {
                // Start database transaction
                $db->transBegin();

                try {
                    // Hash the new password
                    $new_password = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
                    
                    $update_data = [
                        'password' => $new_password
                    ];

                    // Reset force change password flag if it exists
                    if (isset($user_to_change['force_change_password']) && $user_to_change['force_change_password'] == 1) {
                        $update_data['force_change_password'] = 0;
                    }

                    // Update password in database
                    $db->table('user')->where('id', $user_id)->update($update_data);

                    // Log the password change activity
                    $this->logActivity($user_id, 'password_changed', 'Password changed successfully');

                    // Commit transaction
                    $db->transCommit();

                    // Set success message
                    if ($user_id == $current_user_id) {
                        session()->setFlashdata('message', '<div class="alert alert-success">Your password has been changed successfully!</div>');
                    } else {
                        session()->setFlashdata('message', '<div class="alert alert-success">Password for ' . htmlspecialchars($user_to_change['name']) . ' has been changed successfully!</div>');
                    }

                    // Redirect to appropriate page
                    return redirect()->to($success_redirect_url);

                } catch (\Exception $e) {
                    // Rollback transaction on error
                    $db->transRollback();
                    
                    log_message('error', 'Password change error for user ID ' . $user_id . ': ' . $e->getMessage());
                    session()->setFlashdata('message', '<div class="alert alert-danger">Failed to update password. Please try again.</div>');
                }
            } else {
                // Validation failed, pass validation errors back to view
                $data['validation'] = $this->validator;
            }
        }

        return view('auth/changepass', $data);
    }

    /**
     * Helper method to determine dashboard URL based on user role
     */
    private function getDashboardUrl($role_id)
    {
        switch ($role_id) {
            case 1: // Admin
                return 'admin';
            case 2: // Company/User
                return 'user';
            case 3: // Petugas
                return 'petugas';
            case 4: // Monitoring
                return 'monitoring';
            case 5: // Staff Admin
                return 'admin';
            default:
                return 'user';
        }
    }

    /**
     * Custom validation callback to check current password
     */
    public function check_current_password($current_password)
    {
        $user_id = $this->request->getPost('user_id') ?? session()->get('user_id');
        
        if (!$user_id) {
            return false;
        }

        $db = \Config\Database::connect();
        $user = $db->table('user')->where('id', $user_id)->get()->getRowArray();
        
        if (!$user) {
            return false;
        }

        return password_verify($current_password, $user['password']);
    }

    /**
     * Helper method to log user activities
     */
    private function logActivity($user_id, $activity_type, $description)
    {
        try {
            $db = \Config\Database::connect();
            
            // Check if user_activity_log table exists
            if ($db->tableExists('user_activity_log')) {
                $log_data = [
                    'user_id' => $user_id,
                    'activity_type' => $activity_type,
                    'description' => $description,
                    'ip_address' => $this->request->getIPAddress(),
                    'user_agent' => $this->request->getServer('HTTP_USER_AGENT'),
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $db->table('user_activity_log')->insert($log_data);
            }
        } catch (\Exception $e) {
            // Log error but don't break the flow
            log_message('error', 'Failed to log activity: ' . $e->getMessage());
        }
    }

}
