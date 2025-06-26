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
        $this->session = \Config\Services::session();
        $this->db = \Config\Database::connect();
        $this->validation = \Config\Services::validation();
        helper($this->helpers);
    }

    public function index()
    {
        if ($this->session->get('email')) {
            return $this->_redirect_user_by_role($this->session->get('is_active'));
        }

        $this->validation->setRules([
            'login_identifier' => ['label' => 'Email / NIP', 'rules' => 'required|trim', 'errors' => ['required' => 'Kolom {field} wajib diisi.']],
            'password' => ['label' => 'Password', 'rules' => 'required|trim', 'errors' => ['required' => 'Kolom {field} wajib diisi.']]
        ]);

        if (!$this->validation->withRequest($this->request)->run()) {
            $data['title'] = "REPACK Login";
            return view('auth/login', $data);
        } else {
            return $this->_login();
        }
    }

    private function _login()
    {
        $login_identifier = $this->request->getPost('login_identifier');
        $password = $this->request->getPost('password');
        
        $user = $this->db->table('user')
            ->where('email', $login_identifier)
            ->get()->getRowArray();

        if ($user) {
            if ($user['is_active'] == 0 && $user['role_id'] != 2) {
                $this->session->setFlashdata('message', 'Akun Anda tidak aktif!');
                return redirect()->back()->withInput();
            }

            if (password_verify($password, $user['password'])) {
                
                if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                    $new_hash = password_hash($password, PASSWORD_DEFAULT);
                    $this->db->table('user')->where('id', $user['id'])->update(['password' => $new_hash]);
                }

                $isMfaSystemEnabled = (getenv('MFA_ENABLED') ?? 'true') === 'true';

                if ($isMfaSystemEnabled && ($user['is_mfa_enabled'] ?? 0)) {
                    $this->session->set('mfa_pending_user_id', $user['id']);
                    return redirect()->to(base_url('auth/verify_mfa_login'));
                } else {
                    $this->_establish_session($user, true);
                    return $this->_handle_post_login_redirect($user);
                }

            } else {
                $this->session->setFlashdata('message', 'Password salah!');
                return redirect()->back()->withInput();
            }
        } else {
            $this->session->setFlashdata('message', 'Email atau NIP tidak terdaftar!');
            return redirect()->back()->withInput();
        }
    }

    public function verify_mfa_login()
    {
        $pendingUserId = $this->session->get('mfa_pending_user_id');

        if (!$pendingUserId) {
            return redirect()->to(base_url('auth'));
        }

        if (strtolower($this->request->getMethod()) === 'post') {
            $this->validation->setRules(['mfa_code' => ['label' => 'Kode MFA', 'rules' => 'required|trim|numeric']]);

            if (!$this->validation->withRequest($this->request)->run()) {
                return redirect()->back()->withInput();
            }

            $user = $this->db->table('user')->where('id', $pendingUserId)->get()->getRowArray();
            $mfaCode = $this->request->getPost('mfa_code');

            if (!$user) {
                $this->session->remove('mfa_pending_user_id');
                $this->session->setFlashdata('message', 'Sesi MFA tidak valid. Silakan login kembali.');
                return redirect()->to(base_url('auth'));
            }

            $google2fa = new Google2FA();
            if ($google2fa->verifyKey($user['google2fa_secret'], $mfaCode)) {
                $this->session->remove('mfa_pending_user_id');
                $this->_establish_session($user, true);
                return $this->_handle_post_login_redirect($user);
            } else {
                $this->session->setFlashdata('message', 'Kode verifikasi salah!');
                return redirect()->to(base_url('auth/verify_mfa_login'));
            }
        }

        $data['title'] = 'Verifikasi Dua Faktor';
        return view('auth/mfa_verify_page', $data);
    }

    
    private function _establish_session($user, $mfa_verified = false)
    {
        $this->session->remove('mfa_user_id');

        $data_session = [
            'user_id'   => $user['id'],
            'email'     => $user['email'],
            'role_id'   => $user['role_id'],
            'name'      => $user['name'],
            'image'     => $user['image'] ?? 'default.jpg',
            'is_active' => $user['is_active'],
            'force_change_password' => $user['force_change_password'] ?? 0,
            'mfa_verified' => $mfa_verified || !($user['is_mfa_enabled'] ?? 0)
        ];
        $this->session->set($data_session);
    }

    private function _handle_post_login_redirect($user)
    {
        if (($user['force_change_password'] ?? 0) == 1) {
            $this->session->setFlashdata('message', 'Untuk keamanan, Anda wajib mengganti password Anda.');
            if ($user['role_id'] == 2) { 
                return redirect()->to(base_url('user/force_change_password_page')); 
            } elseif ($user['role_id'] == 3) { 
                return redirect()->to(base_url('petugas/force_change_password_page')); 
            }
        }
        return $this->_redirect_user_by_role($user['is_active']);
    }

    private function _redirect_user_by_role($is_active = 1)
    {
        $role_id = $this->session->get('role_id');

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
        if (!session()->get('user_id')) { // Simplified check
            session()->setFlashdata('message', '<div class="alert alert-danger">Please login first.</div>');
            return redirect()->to('auth');
        }

        $current_user_id = session()->get('user_id');
        $current_user_role = session()->get('role_id');
        
        if ($user_id === null) {
            $user_id = $current_user_id;
        }
        
        if (!$user_id || !is_numeric($user_id)) {
            session()->setFlashdata('message', '<div class="alert alert-danger">Invalid user ID.</div>');
            return redirect()->to($this->getDashboardUrl($current_user_role));
        }

        if ($user_id != $current_user_id && $current_user_role != 1) {
            session()->setFlashdata('message', '<div class="alert alert-danger">You can only change your own password.</div>');
            return redirect()->to($this->getDashboardUrl($current_user_role));
        }

        $user_to_change = $this->db->table('user')->where('id', $user_id)->get()->getRowArray();
        
        if (!$user_to_change) {
            session()->setFlashdata('message', '<div class="alert alert-danger">User not found.</div>');
            return redirect()->to($this->getDashboardUrl($current_user_role));
        }

        $cancel_redirect_url = $this->getDashboardUrl($current_user_role);
        $success_redirect_url = $this->getDashboardUrl($current_user_role);
        
        if ($user_id != $current_user_id && $current_user_role == 1) {
            $cancel_redirect_url = 'admin/manajemen_user';
            $success_redirect_url = 'admin/manajemen_user';
        }

        $data = [
            'title' => 'Change Password',
            'user_for_pass_change' => $user_to_change,
            'validation' => $this->validation,
            'cancel_redirect_url' => $cancel_redirect_url
        ];

        if ($this->request->getMethod() === 'post') {
            $rules = [
                'password' => [
                    'label' => 'New Password', 'rules' => 'required|trim|min_length[6]|matches[password2]',
                    'errors' => ['required' => '{field} is required.','min_length' => '{field} must be at least 6 characters.','matches' => '{field} does not match.']
                ],
                'password2' => ['label' => 'Repeat Password', 'rules' => 'required|trim|matches[password]']
            ];

            if ($this->validate($rules)) {
                $this->db->transBegin();
                try {
                    $new_password = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
                    
                    $update_data = ['password' => $new_password];

                    if (isset($user_to_change['force_change_password']) && $user_to_change['force_change_password'] == 1) {
                        $update_data['force_change_password'] = 0;
                    }

                    $this->db->table('user')->where('id', $user_id)->update($update_data);
                    $this->logActivity($user_id, 'password_changed', 'Password changed successfully');
                    $this->db->transCommit();

                    $success_message = ($user_id == $current_user_id) ? 'Your password has been changed successfully!' : 'Password for ' . htmlspecialchars($user_to_change['name']) . ' has been changed successfully!';
                    session()->setFlashdata('message', '<div class="alert alert-success">' . $success_message . '</div>');

                    return redirect()->to($success_redirect_url);

                } catch (\Exception $e) {
                    $this->db->transRollback();
                    log_message('error', 'Password change error for user ID ' . $user_id . ': ' . $e->getMessage());
                    session()->setFlashdata('message', '<div class="alert alert-danger">Failed to update password. Please try again.</div>');
                }
            } else {
                $data['validation'] = $this->validator;
            }
        }

        return view('auth/changepass', $data);
    }

    private function getDashboardUrl($role_id)
    {
        $redirects = [
            1 => 'admin', 2 => 'user', 3 => 'petugas', 4 => 'monitoring', 5 => 'admin'
        ];
        return $redirects[$role_id] ?? 'user';
    }

    private function logActivity($user_id, $activity_type, $description)
    {
        try {
            if ($this->db->tableExists('user_activity_log')) {
                $log_data = [
                    'user_id' => $user_id, 'activity_type' => $activity_type,
                    'description' => $description, 'ip_address' => $this->request->getIPAddress(),
                    'user_agent' => $this->request->getServer('HTTP_USER_AGENT'),
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $this->db->table('user_activity_log')->insert($log_data);
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to log activity: ' . $e->getMessage());
        }
    }
}
