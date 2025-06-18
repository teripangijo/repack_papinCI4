<?php
namespace App\Controllers;
use App\Controllers\BaseController;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;
use CodeIgniter\Exceptions\PageNotFoundException;

class Admin extends BaseController
{
    protected $db;
    protected $validation;
    protected $user;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->validation = \Config\Services::validation();
        helper(['form', 'url', 'repack', 'download']);
        $this->user = $this->db->table('user')->where('email', session()->get('email'))->get()->getRowArray();
    }

    public function index()
    {
        $data = [
            'title' => 'Returnable Package',
            'subtitle' => 'Admin Dashboard',
            'user' => $this->user,
        ];
        $data['total_users'] = $this->db->table('user')->whereIn('role_id', [2, 3, 4])->countAllResults();
        $data['pending_permohonan'] = $this->db->table('user_permohonan')->whereIn('status', ['0', '1', '2', '5'])->countAllResults();
        $data['pending_kuota_requests'] = $this->db->table('user_pengajuan_kuota')->where('status', 'pending')->countAllResults();

        return view('admin/index', $data);
    }

    //================================================================
    // PROFIL & MFA MANAGEMENT
    //================================================================

    public function edit_profil()
    {
        $user_id = $this->user['id'];

        $data = [
            'title' => 'Returnable Package',
            'subtitle' => 'Edit Profil Saya',
            'user' => $this->user,
            'validation' => $this->validation
        ];

        if ($this->request->getMethod() === 'post') {
            $update_data_user = [];
            $name_input = $this->request->getPost('name');

            if (!empty($name_input) && $name_input !== $this->user['name']) {
                $update_data_user['name'] = htmlspecialchars($name_input, ENT_QUOTES, 'UTF-8');
            }

            $new_login_identifier = $this->request->getPost('login_identifier');
            
            if (!empty($new_login_identifier) && $new_login_identifier !== $this->user['email']) {
                $rules = [
                    'login_identifier' => "trim|required|valid_email|is_unique[user.email,id,{$user_id}]"
                ];

                if ($this->validate($rules)) {
                    $update_data_user['email'] = htmlspecialchars($new_login_identifier, ENT_QUOTES, 'UTF-8');
                } else {
                    return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
                }
            }

            if (!empty($update_data_user)) {
                $this->db->table('user')->where('id', $user_id)->update($update_data_user);
                session()->setFlashdata('message', '<div class="alert alert-success" role="alert">Profil berhasil diupdate.</div>');
                if (isset($update_data_user['name'])) {
                    session()->set('name', $update_data_user['name']);
                }
                if (isset($update_data_user['email'])) {
                    session()->set('email', $update_data_user['email']);
                }
            }

            $profileImage = $this->request->getFile('profile_image');
            if ($profileImage && $profileImage->isValid() && !$profileImage->hasMoved()) {
                $old_image = $this->user['image'];
                if ($old_image && $old_image != 'default.jpg' && file_exists(FCPATH . 'uploads/profile_images/' . $old_image)) {
                    @unlink(FCPATH . 'uploads/profile_images/' . $old_image);
                }

                $newName = $profileImage->getRandomName();
                $profileImage->move(FCPATH . 'uploads/profile_images', $newName);

                $this->db->table('user')->where('id', $user_id)->update(['image' => $newName]);
                session()->set('image', $newName);
                session()->setFlashdata('message', '<div class="alert alert-success" role="alert">Foto profil berhasil diupdate.</div>');
            }

            return redirect()->to('admin/edit_profil');
        }

        return view('admin/form_edit_profil_admin', $data);
    }
    
    public function setup_mfa()
    {
        $user_data = $this->db->table('user')->where('email', session()->get('email'))->get()->getRowArray();
        
        $google2fa = new Google2FA();
        $secretKey = $user_data['google2fa_secret'];

        if (empty($secretKey)) {
            $secretKey = $google2fa->generateSecretKey();
            $this->db->table('user')->where('id', $user_data['id'])->update(['google2fa_secret' => $secretKey]);
        }
        
        $qrCodeUrl = $google2fa->getQRCodeUrl('Repack Papin', $user_data['email'], $secretKey);

        $renderer = new ImageRenderer(new RendererStyle(400), new SvgImageBackEnd());
        $writer = new Writer($renderer);
        $qrCodeImage = $writer->writeString($qrCodeUrl);
        $qrCodeDataUri = 'data:image/svg+xml;base64,' . base64_encode($qrCodeImage);

        $data = [
            'title' => 'Returnable Package',
            'subtitle' => 'Setup Multi-Factor Authentication',
            'user' => $user_data,
            'qr_code_data_uri' => $qrCodeDataUri,
            'secret_key' => $secretKey,
        ];

        return view('admin/mfa_setup', $data);
    }

    public function verify_mfa()
    {
        $userId = session()->get('user_id');
        $user = $this->db->table('user')->where('id', $userId)->get()->getRowArray();
        $secret = $user['google2fa_secret'] ?? '';
        $oneTimePassword = $this->request->getPost('one_time_password');

        $google2fa = new Google2FA();
        
        if ($google2fa->verifyKey($secret, $oneTimePassword, 4)) {
            $this->db->table('user')->where('id', $userId)->update(['is_mfa_enabled' => 1]);
            session()->setFlashdata('message', '<div class="alert alert-success" role="alert">Autentikasi Dua Faktor (MFA) berhasil diaktifkan!</div>');
            return redirect()->to('admin/edit_profil');
        } else {
            session()->setFlashdata('error', 'Kode verifikasi salah. Silakan coba lagi.');
            return redirect()->to('admin/setup_mfa');
        }
    }

    public function reset_mfa()
    {
        $user_id = session()->get('user_id');
        $this->db->table('user')->where('id', $user_id)->update(['is_mfa_enabled' => 0, 'google2fa_secret' => null]);
        session()->setFlashdata('message', '<div class="alert alert-info" role="alert">MFA Anda telah dinonaktifkan. Silakan lakukan pengaturan ulang.</div>');
        return redirect()->to('admin/setup_mfa');
    }
    
    //================================================================
    // ROLE & ACCESS MANAGEMENT
    //================================================================

    public function role()
    {
        $data = [
            'user' => $this->user,
            'title' => 'Returnable Package',
            'subtitle' => 'Role Management',
            'role' => $this->db->table('user_role')->get()->getResultArray(),
            'validation' => $this->validation
        ];

        if ($this->request->getMethod() === 'post' && $this->validate(['role' => 'required|trim|is_unique[user_role.role]'])) {
            $this->db->table('user_role')->insert(['role' => $this->request->getPost('role')]);
            session()->setFlashdata('message', '<div class="alert alert-success" role="alert">Role baru berhasil ditambahkan!</div>');
            return redirect()->to('admin/role');
        }

        return view('admin/role', $data);
    }
    
    public function roleAccess($role_id)
    {
        $role = $this->db->table('user_role')->where('id', $role_id)->get()->getRowArray();
        if (!$role) {
            throw PageNotFoundException::forPageNotFound('Role tidak ditemukan.');
        }

        $data = [
            'user' => $this->user,
            'title' => 'Returnable Package',
            'subtitle' => 'Role Access Management',
            'role' => $role,
            'menu' => $this->db->table('user_menu')->get()->getResultArray(),
        ];

        return view('admin/role-access', $data);
    }

    public function changeaccess()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('admin');
        }

        $menu_id = $this->request->getPost('menuId');
        $role_id = $this->request->getPost('roleId');
        $data = ['role_id' => $role_id, 'menu_id' => $menu_id];
        
        $result = $this->db->table('user_access_menu')->where($data)->get();

        if ($result->getNumRows() < 1) {
            $this->db->table('user_access_menu')->insert($data);
        } else {
            $this->db->table('user_access_menu')->where($data)->delete();
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Access Changed!']);
    }

    //================================================================
    // USER MANAGEMENT
    //================================================================
    
    public function manajemen_user()
    {
        $builder = $this->db->table('user u');
        $builder->select('u.*, ur.role as role_name')->join('user_role ur', 'u.role_id = ur.id', 'left')->orderBy('u.name', 'ASC');
        
        $data = [
            'title' => 'Returnable Package',
            'subtitle' => 'Manajemen User',
            'user' => $this->user,
            'users_list' => $builder->get()->getResultArray(),
            'roles' => $this->db->table('user_role')->get()->getResultArray(),
        ];

        return view('admin/manajemen_user_view', $data);
    }

    public function tambah_user($role_id_to_add = 0)
    {
        log_message('debug', '=== TAMBAH USER START ===');
        log_message('debug', 'Role ID: ' . $role_id_to_add);
        log_message('debug', 'Method: ' . $this->request->getMethod());
        
        $target_role_info = $this->db->table('user_role')->where('id', $role_id_to_add)->get()->getRowArray();
        if (!$target_role_info || $role_id_to_add == 1) {
            log_message('error', 'Invalid role_id: ' . $role_id_to_add);
            session()->setFlashdata('message', '<div class="alert alert-danger" role="alert">Role target tidak valid atau tidak diizinkan.</div>');
            return redirect()->to('admin/manajemen_user');
        }

        // Setup login identifier rules
        $login_identifier_label = 'Login Identifier';
        $login_identifier_rules = 'required|trim|is_unique[user.email]';
        $login_identifier_placeholder = 'Masukkan Email atau NIP';
        $login_identifier_help_text = 'Digunakan untuk login.';
        
        if ($role_id_to_add == 2) { 
            $login_identifier_label = 'Email';
            $login_identifier_rules .= '|valid_email';
            $login_identifier_placeholder = 'Contoh: user@example.com';
        } elseif (in_array($role_id_to_add, [3, 4, 5])) { 
            $login_identifier_label = 'NIP';
            $login_identifier_rules .= '|numeric';
            $login_identifier_placeholder = 'Masukkan NIP';
            $login_identifier_help_text = 'NIP akan digunakan untuk login.';
        }

        // POST processing
        if ($this->request->getMethod() === 'POST') {
            log_message('debug', '=== POST REQUEST PROCESSING ===');
            
            // CHECK FORM TOKEN untuk prevent double submission
            $submitted_token = $this->request->getPost('form_token');
            $last_processed_token = session()->get('last_processed_token');
            
            if ($submitted_token && $last_processed_token === $submitted_token) {
                log_message('debug', 'Duplicate submission detected - redirecting');
                // Jangan set flash message lagi, langsung redirect
                return redirect()->to('admin/manajemen_user');
            }
            
            $postData = $this->request->getPost();
            log_message('debug', 'POST data: ' . json_encode($postData));

            // Validation rules
            $rules = [
                'name' => [
                    'label' => 'Nama Lengkap',
                    'rules' => 'required|trim',
                    'errors' => ['required' => '{field} wajib diisi.']
                ],
                'login_identifier' => [
                    'label' => $login_identifier_label,
                    'rules' => $login_identifier_rules,
                    'errors' => [
                        'required' => '{field} wajib diisi.',
                        'is_unique' => '{field} ini sudah terdaftar.',
                        'numeric' => '{field} harus berupa angka.',
                        'valid_email' => '{field} tidak valid.'
                    ]
                ],
                'password' => [
                    'label' => 'Password',
                    'rules' => 'required|trim|min_length[6]',
                    'errors' => [
                        'required' => '{field} wajib diisi.',
                        'min_length' => '{field} minimal 6 karakter.'
                    ]
                ],
                'confirm_password' => [
                    'label' => 'Konfirmasi Password',
                    'rules' => 'required|trim|matches[password]',
                    'errors' => [
                        'required' => '{field} wajib diisi.',
                        'matches' => '{field} tidak cocok dengan password.'
                    ]
                ]
            ];

            if ($role_id_to_add == 3) {
                $rules['jabatan_petugas'] = [
                    'label' => 'Jabatan Petugas',
                    'rules' => 'trim|required',
                    'errors' => ['required' => '{field} wajib diisi.']
                ];
            }
            
            if ($this->validate($rules)) {
                log_message('debug', '=== VALIDATION PASSED ===');
                
                try {
                    // MARK TOKEN as PROCESSED
                    session()->set('last_processed_token', $submitted_token);
                    
                    log_message('debug', '=== STARTING DATABASE TRANSACTION ===');
                    $this->db->transStart();
                    
                    $force_change_pass = in_array($role_id_to_add, [4, 5]) ? 0 : 1;
                    
                    $user_data_to_insert = [
                        'name' => htmlspecialchars($this->request->getPost('name'), ENT_QUOTES, 'UTF-8'),
                        'email' => htmlspecialchars($this->request->getPost('login_identifier'), ENT_QUOTES, 'UTF-8'),
                        'image' => 'default.jpg',
                        'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                        'role_id' => $role_id_to_add, 
                        'is_active' => 1, 
                        'force_change_password' => $force_change_pass, 
                        'date_created' => time()
                    ];
                    
                    log_message('debug', '=== USER DATA TO INSERT ===');
                    log_message('debug', json_encode($user_data_to_insert));
                    
                    // Check if user already exists (double-check)
                    $existing_user = $this->db->table('user')
                        ->where('email', $user_data_to_insert['email'])
                        ->get()->getRowArray();
                    
                    if ($existing_user) {
                        log_message('debug', 'User already exists, skipping insert');
                        session()->setFlashdata('message', '<div class="alert alert-info" role="alert">User dengan email tersebut sudah ada.</div>');
                        return redirect()->to('admin/manajemen_user');
                    }
                    
                    // Insert user
                    $insert_result = $this->db->table('user')->insert($user_data_to_insert);
                    log_message('debug', 'Insert result: ' . ($insert_result ? 'SUCCESS' : 'FAILED'));
                    
                    if (!$insert_result) {
                        $db_error = $this->db->error();
                        log_message('error', 'Database insert error: ' . json_encode($db_error));
                        throw new \Exception('Failed to insert user: ' . $db_error['message']);
                    }
                    
                    $new_user_id = $this->db->insertID();
                    log_message('debug', 'New user ID: ' . $new_user_id);

                    // Insert data petugas jika role 3
                    if ($new_user_id && $role_id_to_add == 3) {
                        $petugas_data = [
                            'id_user' => $new_user_id, 
                            'Nama' => $user_data_to_insert['name'], 
                            'NIP' => $user_data_to_insert['email'],
                            'Jabatan' => htmlspecialchars($this->request->getPost('jabatan_petugas'), ENT_QUOTES, 'UTF-8')
                        ];
                        
                        $petugas_insert = $this->db->table('petugas')->insert($petugas_data);
                        if (!$petugas_insert) {
                            $db_error = $this->db->error();
                            throw new \Exception('Failed to insert petugas: ' . $db_error['message']);
                        }
                    }
                    
                    // Complete transaction
                    $this->db->transComplete();
                    
                    if ($this->db->transStatus() === false) {
                        throw new \Exception('Database transaction failed');
                    }
                    
                    log_message('debug', '=== SUCCESS: USER CREATED ===');
                    
                    // ONLY SET FLASH MESSAGE ONCE
                    session()->setFlashdata('message', '<div class="alert alert-success" role="alert">User baru "' . htmlspecialchars($user_data_to_insert['name']) . '" berhasil ditambahkan.</div>');
                    
                    log_message('debug', '=== REDIRECTING TO MANAJEMEN_USER ===');
                    return redirect()->to('admin/manajemen_user');
                    
                } catch (\Exception $e) {
                    $this->db->transRollback();
                    log_message('error', '=== EXCEPTION OCCURRED ===');
                    log_message('error', 'Exception: ' . $e->getMessage());
                    
                    session()->setFlashdata('message', '<div class="alert alert-danger" role="alert">Error: ' . $e->getMessage() . '</div>');
                    return redirect()->to('admin/manajemen_user');
                }
            } else {
                log_message('debug', '=== VALIDATION FAILED ===');
                $errors = $this->validator->getErrors();
                log_message('debug', 'Validation errors: ' . json_encode($errors));
                
                session()->setFlashdata('errors', $errors);
            }
        }
        
        // Generate unique form token untuk GET request
        $form_token = uniqid('form_', true);
        
        $data = [
            'title' => 'Returnable Package',
            'subtitle' => 'Tambah User Baru: ' . htmlspecialchars($target_role_info['role']),
            'user' => $this->user,
            'target_role_info' => $target_role_info,
            'role_id_to_add' => $role_id_to_add,
            'login_identifier_label_view' => $login_identifier_label,
            'login_identifier_placeholder' => $login_identifier_placeholder,
            'login_identifier_help_text' => $login_identifier_help_text,
            'form_token' => $form_token
        ];
        
        log_message('debug', '=== RETURNING TO FORM VIEW ===');
        return view('admin/form_tambah_user_view', $data);
    }



    public function delete_user($user_id = 0)
    {
        if ($user_id == 0 || !is_numeric($user_id) || $user_id == session()->get('user_id') || $user_id == 1) {
            session()->setFlashdata('message', '<div class="alert alert-danger" role="alert">Aksi tidak diizinkan atau User ID tidak valid.</div>');
            return redirect()->to('admin/manajemen_user');
        }

        $user_to_delete = $this->db->table('user')->where('id', $user_id)->get()->getRowArray();
        if (!$user_to_delete) {
            throw PageNotFoundException::forPageNotFound('User tidak ditemukan.');
        }

        $this->db->transBegin();
        if (($user_to_delete['role_id'] ?? null) == 3) {
            $this->db->table('petugas')->where('id_user', $user_id)->delete();
        }
        $this->db->table('user')->where('id', $user_id)->delete();

        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            session()->setFlashdata('message', '<div class="alert alert-danger" role="alert">Gagal menghapus user.</div>');
        } else {
            $this->db->transCommit();
            session()->setFlashdata('message', '<div class="alert alert-success" role="alert">User ' . htmlspecialchars($user_to_delete['name']) . ' berhasil dihapus.</div>');
        }

        return redirect()->to('admin/manajemen_user');
    }

    public function ganti_password_user($target_user_id = 0)
    {
        if ($target_user_id == 0 || $target_user_id == 1) {
            return redirect()->to('admin/manajemen_user');
        }

        $target_user = $this->db->table('user')->where('id', $target_user_id)->get()->getRowArray();
        if (!$target_user) {
            session()->setFlashdata('message', '<div class="alert alert-danger" role="alert">User tidak ditemukan.</div>');
            return redirect()->to('admin/manajemen_user');
        }

        $data = [
            'title' => 'Returnable Package',
            'subtitle' => 'Ganti Password User',
            'user' => $this->user,
            'target_user' => $target_user,
            'validation' => $this->validation,
        ];

        $rules = [
            'new_password' => 'required|trim|min_length[6]|matches[confirm_password]',
            'confirm_password' => 'required|trim|matches[new_password]'
        ];

        if ($this->request->getMethod() === 'post' && $this->validate($rules)) {
            $new_password_hash = password_hash($this->request->getPost('new_password'), PASSWORD_DEFAULT);
            $update_data = [
                'password' => $new_password_hash,
                'force_change_password' => 1
            ];

            $this->db->table('user')->where('id', $target_user_id)->update($update_data);
            session()->setFlashdata('message', '<div class="alert alert-success" role="alert">Password untuk user ' . htmlspecialchars($target_user['name']) . ' berhasil diubah.</div>');
            return redirect()->to('admin/manajemen_user');
        }

        return view('admin/form_ganti_password_user', $data);
    }

    public function edit_user($target_user_id = 0)
    {
        if ($target_user_id == 0 || !is_numeric($target_user_id)) {
            session()->setFlashdata('message', '<div class="alert alert-danger" role="alert">User ID tidak valid.</div>');
            return redirect()->to('admin/manajemen_user');
        }

        $target_user_data = $this->db->table('user')->where('id', $target_user_id)->get()->getRowArray();
        if (!$target_user_data) {
            session()->setFlashdata('message', '<div class="alert alert-danger" role="alert">User yang akan diedit tidak ditemukan.</div>');
            return redirect()->to('admin/manajemen_user');
        }

        $is_editing_main_admin = ($target_user_data['id'] == 1);
        
        $data = [
            'title' => 'Returnable Package',
            'subtitle' => 'Edit Data User',
            'user' => $this->user,
            'target_user_data' => $target_user_data,
            'roles_list' => $this->db->table('user_role')->get()->getResultArray(),
            'validation' => $this->validation,
        ];

        $rules = ['name' => 'required|trim'];
        
        if (!$is_editing_main_admin) {
            $rules['role_id'] = 'required|numeric';
            $rules['is_active'] = 'required|in_list[0,1]';
        }

        if ($this->request->getMethod() === 'post' && $this->validate($rules)) {
            $update_data_user = [
                'name' => htmlspecialchars($this->request->getPost('name', true)),
                'email' => htmlspecialchars($this->request->getPost('login_identifier', true)),
            ];

            if (!$is_editing_main_admin) {
                $update_data_user['role_id'] = (int)$this->request->getPost('role_id');
                $update_data_user['is_active'] = (int)$this->request->getPost('is_active');
            }

            $this->db->table('user')->where('id', $target_user_id)->update($update_data_user);

            if (($this->request->getPost('role_id') ?? $target_user_data['role_id']) == 3) {
                $petugas_detail = $this->db->table('petugas')->where('id_user', $target_user_id)->get()->getRowArray();
                $data_petugas_update = [
                    'Nama' => $update_data_user['name'],
                    'NIP' => $update_data_user['email'],
                    'Jabatan' => $this->request->getPost('jabatan_petugas_edit')
                ];

                if ($petugas_detail) {
                    $this->db->table('petugas')->where('id_user', $target_user_id)->update($data_petugas_update);
                } else {
                    $data_petugas_update['id_user'] = $target_user_id;
                    $this->db->table('petugas')->insert($data_petugas_update);
                }
            }

            session()->setFlashdata('message', '<div class="alert alert-success" role="alert">Data user berhasil diupdate.</div>');
            return redirect()->to('admin/manajemen_user');
        }

        return view('admin/form_edit_user', $data);
    }

    //================================================================
    // PERMOHONAN MANAGEMENT
    //================================================================

    public function permohonanMasuk()
    {
        $builder = $this->db->table('user_permohonan up');
        $builder->select('up.id, up.nomorSurat, up.TglSurat, up.time_stamp, up.status, upr.NamaPers, u_pemohon.name as nama_pengaju, u_real_petugas.name as nama_petugas_assigned');
        $builder->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $builder->join('user u_pemohon', 'upr.id_pers = u_pemohon.id', 'left');
        $builder->join('petugas p_assigned', 'up.petugas = p_assigned.id', 'left');
        $builder->join('user u_real_petugas', 'p_assigned.id_user = u_real_petugas.id', 'left');
        
        $orderByCase = "CASE up.status WHEN '0' THEN 1 WHEN '5' THEN 2 WHEN '1' THEN 3 WHEN '2' THEN 4 ELSE 5 END ASC, up.time_stamp DESC";
        $builder->orderBy($orderByCase, '', false);

        $data = [
            'user' => $this->user, 'title' => 'Returnable Package', 'subtitle' => 'Daftar Permohonan Impor',
            'permohonan' => $builder->get()->getResultArray(),
        ];
        
        return view('admin/permohonan-masuk', $data);
    }

    public function detail_permohonan_admin($id_permohonan = 0)
    {
        $builder = $this->db->table('user_permohonan up');
        $builder->select('up.*, upr.NamaPers, upr.npwp, u_pemohon.name as nama_pengaju_permohonan, u_pemohon.email as email_pengaju_permohonan, u_petugas.name as nama_petugas_pemeriksa');
        $builder->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $builder->join('user u_pemohon', 'upr.id_pers = u_pemohon.id', 'left');
        $builder->join('petugas p', 'up.petugas = p.id', 'left'); 
        $builder->join('user u_petugas', 'p.id_user = u_petugas.id', 'left'); 
        $builder->where('up.id', $id_permohonan);
        $permohonan_detail = $builder->get()->getRowArray();

        if (!$id_permohonan || !$permohonan_detail) {
            throw PageNotFoundException::forPageNotFound('Data permohonan tidak ditemukan.');
        }

        $data = [
            'title' => 'Returnable Package',
            'subtitle' => 'Detail Permohonan Impor ID: ' . htmlspecialchars($id_permohonan),
            'user' => $this->user,
            'permohonan_detail' => $permohonan_detail,
            'lhp_detail' => $this->db->table('lhp')->where('id_permohonan', $id_permohonan)->get()->getRowArray(),
        ];

        return view('admin/detail_permohonan_admin_view', $data);
    }

    public function hapus_permohonan($id_permohonan = 0)
    {
        $permohonan = $this->db->table('user_permohonan')->where('id', $id_permohonan)->get()->getRowArray();
        if (!$id_permohonan || !$permohonan) {
            session()->setFlashdata('message', '<div class="alert alert-danger" role="alert">Permohonan tidak ditemukan.</div>');
            return redirect()->to('admin/permohonanMasuk');
        }

        $filePath = FCPATH . 'uploads/bc_manifest/' . $permohonan['file_bc_manifest'];
        if (!empty($permohonan['file_bc_manifest']) && file_exists($filePath)) {
            @unlink($filePath);
        }

        if ($this->db->table('user_permohonan')->where('id', $id_permohonan)->delete()) {
            session()->setFlashdata('message', '<div class="alert alert-success" role="alert">Permohonan berhasil dihapus.</div>');
        } else {
            session()->setFlashdata('message', '<div class="alert alert-danger" role="alert">Gagal menghapus permohonan.</div>');
        }
        return redirect()->to('admin/permohonanMasuk');
    }

    public function tolak_permohonan_awal($id_permohonan = 0)
    {
        $builder = $this->db->table('user_permohonan up');
        $builder->select('up.id, up.nomorSurat, upr.NamaPers, up.status');
        $builder->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $builder->where('up.id', $id_permohonan);
        $permohonan = $builder->get()->getRowArray();
        
        if (!$permohonan || ($permohonan['status'] ?? null) != '0') {
            session()->setFlashdata('message', '<div class="alert alert-warning" role="alert">Permohonan ini tidak ditemukan atau statusnya tidak bisa ditolak.</div>');
            return redirect()->to('admin/permohonanMasuk');
        }
        
        $data = [
            'title' => 'Returnable Package', 'subtitle' => 'Formulir Penolakan Permohonan',
            'user' => $this->user, 'permohonan' => $permohonan, 'validation' => $this->validation
        ];

        if ($this->request->getMethod() === 'post' && $this->validate(['alasan_penolakan' => 'trim|required'])) {
            $update_data = [
                'status' => '6', 'catatan_penolakan' => $this->request->getPost('alasan_penolakan'),
                'time_selesai' => date('Y-m-d H:i:s') 
            ];
            $this->db->table('user_permohonan')->where('id', $id_permohonan)->update($update_data);
            session()->setFlashdata('message', '<div class="alert alert-success" role="alert">Permohonan ID ' . htmlspecialchars($id_permohonan) . ' berhasil ditolak.</div>');
            return redirect()->to('admin/permohonanMasuk');
        }

        return view('admin/form_tolak_permohonan_view', $data);
    }

    public function penunjukanPetugas($id_permohonan)
    {
        $builder = $this->db->table('user_permohonan up');
        $builder->select('up.*, upr.NamaPers');
        $builder->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $builder->where('up.id', $id_permohonan);
        $permohonan = $builder->get()->getRowArray();

        if (!$permohonan) {
            session()->setFlashdata('message', '<div class="alert alert-danger" role="alert">Permohonan tidak ditemukan!</div>');
            return redirect()->to('admin/permohonanMasuk');
        }

        $data = [
            'user' => $this->user,
            'title' => 'Returnable Package',
            'subtitle' => 'Penunjukan Petugas Pemeriksa',
            'permohonan' => $permohonan,
            'list_petugas' => $this->db->table('petugas')->orderBy('Nama', 'ASC')->get()->getResultArray(),
            'validation' => $this->validation
        ];

        if ($permohonan['status'] == '0' && $this->request->getMethod() !== 'post') {
            $this->db->table('user_permohonan')->where('id', $id_permohonan)->update(['status' => '5']);
            $data['permohonan']['status'] = '5';
            session()->setFlashdata('message_transient', '<div class="alert alert-info" role="alert">Status permohonan diubah menjadi "Diproses Admin".</div>');
        }

        $rules = [
            'petugas_id' => 'required|numeric',
            'nomor_surat_tugas' => 'required|trim',
            'tanggal_surat_tugas' => 'required'
        ];

        if ($this->request->getMethod() === 'post' && $this->validate($rules)) {
            $update_data = [
                'petugas' => $this->request->getPost('petugas_id'),
                'NoSuratTugas' => $this->request->getPost('nomor_surat_tugas'),
                'TglSuratTugas' => $this->request->getPost('tanggal_surat_tugas'),
                'status' => '1',
                'WaktuPenunjukanPetugas' => date('Y-m-d H:i:s')
            ];

            $suratTugasFile = $this->request->getFile('file_surat_tugas');
            if ($suratTugasFile && $suratTugasFile->isValid() && !$suratTugasFile->hasMoved()) {
                $uploadPath = FCPATH . 'uploads/surat_tugas/';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }

                if (!empty($permohonan['FileSuratTugas']) && file_exists($uploadPath . $permohonan['FileSuratTugas'])) {
                    @unlink($uploadPath . $permohonan['FileSuratTugas']);
                }

                $newName = $suratTugasFile->getRandomName();
                $suratTugasFile->move($uploadPath, $newName);
                $update_data['FileSuratTugas'] = $newName;
            }

            $this->db->table('user_permohonan')->where('id', $id_permohonan)->update($update_data);
            session()->setFlashdata('message', '<div class="alert alert-success" role="alert">Petugas pemeriksa berhasil ditunjuk.</div>');
            return redirect()->to('admin/permohonanMasuk');
        }

        return view('admin/form_penunjukan_petugas', $data);
    }

    public function prosesSurat($id_permohonan = 0)
    {
        $builder = $this->db->table('user_permohonan up');
        $builder->select('up.*, upr.NamaPers, upr.npwp, upr.alamat, upr.NoSkep');
        $builder->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $builder->where('up.id', $id_permohonan);
        $permohonan = $builder->get()->getRowArray();

        if (!$permohonan) {
            session()->setFlashdata('message', '<div class="alert alert-danger" role="alert">Permohonan tidak ditemukan.</div>');
            return redirect()->to('admin/permohonanMasuk');
        }

        $lhp = $this->db->table('lhp')->where('id_permohonan', $id_permohonan)->get()->getRowArray();
        if (!$lhp || $permohonan['status'] != '2' || empty($lhp['NoLHP']) || empty($lhp['TglLHP'])) {
            session()->setFlashdata('message', '<div class="alert alert-warning" role="alert">LHP belum lengkap atau status tidak valid.</div>');
            return redirect()->to('admin/detail_permohonan_admin/' . $id_permohonan);
        }

        $data = [
            'user' => $this->user,
            'title' => 'Returnable Package',
            'subtitle' => 'Proses Finalisasi Permohonan Impor',
            'permohonan' => $permohonan,
            'lhp' => $lhp,
            'validation' => $this->validation
        ];

        $rules = [
            'status_final' => 'required|in_list[3,4]',
            'nomorSetuju' => 'trim|required|max_length[100]',
            'tgl_S' => 'trim|required',
            'link' => 'trim|valid_url_strict'
        ];

        if ($this->request->getPost('status_final') == '4') {
            $rules['catatan_penolakan'] = 'trim|required';
        } elseif ($this->request->getPost('status_final') == '3') {
            if (empty($permohonan['file_surat_keputusan'])) {
                $rules['file_surat_keputusan'] = 'uploaded[file_surat_keputusan]|max_size[file_surat_keputusan,2048]|ext_in[file_surat_keputusan,pdf,jpg,png,jpeg]';
            }
        }

        if ($this->request->getMethod() === 'post' && $this->validate($rules)) {
            $status_final = $this->request->getPost('status_final');
            
            $data_update = [
                'nomorSetuju' => $this->request->getPost('nomorSetuju'),
                'tgl_S' => $this->request->getPost('tgl_S'),
                'link' => $this->request->getPost('link'),
                'catatan_penolakan' => $status_final == '4' ? $this->request->getPost('catatan_penolakan') : null,
                'time_selesai' => date("Y-m-d H:i:s"),
                'status' => $status_final
            ];

            $skFile = $this->request->getFile('file_surat_keputusan');
            if ($status_final == '3' && $skFile && $skFile->isValid() && !$skFile->hasMoved()) {
                $uploadPath = FCPATH . 'uploads/sk_penyelesaian/';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }

                if (!empty($permohonan['file_surat_keputusan']) && file_exists($uploadPath . $permohonan['file_surat_keputusan'])) {
                    @unlink($uploadPath . $permohonan['file_surat_keputusan']);
                }

                $newName = $skFile->getRandomName();
                $skFile->move($uploadPath, $newName);
                $data_update['file_surat_keputusan'] = $newName;
            } elseif ($status_final == '4') {
                if (!empty($permohonan['file_surat_keputusan']) && file_exists(FCPATH . 'uploads/sk_penyelesaian/' . $permohonan['file_surat_keputusan'])) {
                    @unlink(FCPATH . 'uploads/sk_penyelesaian/' . $permohonan['file_surat_keputusan']);
                }
                $data_update['file_surat_keputusan'] = null;
            }

            $this->db->table('user_permohonan')->where('id', $id_permohonan)->update($data_update);

            // Process quota deduction if approved
            if ($status_final == '3' && isset($lhp['JumlahBenar']) && $lhp['JumlahBenar'] > 0) {
                $jumlah_dipotong = (float)$lhp['JumlahBenar'];
                $id_kuota_barang_terpakai = $permohonan['id_kuota_barang_digunakan'];

                if ($id_kuota_barang_terpakai) {
                    $this->db->transBegin();

                    $kuota_barang_saat_ini = $this->db->table('user_kuota_barang')
                        ->where('id_kuota_barang', $id_kuota_barang_terpakai)
                        ->get()->getRowArray();

                    if ($kuota_barang_saat_ini) {
                        $kuota_sebelum = (float)$kuota_barang_saat_ini['remaining_quota_barang'];
                        $kuota_sesudah = $kuota_sebelum - $jumlah_dipotong;

                        $this->db->table('user_kuota_barang')
                            ->where('id_kuota_barang', $id_kuota_barang_terpakai)
                            ->update(['remaining_quota_barang' => $kuota_sesudah]);

                        $keterangan_log = 'Pemotongan kuota dari persetujuan impor. No. Surat: ' . ($data_update['nomorSetuju'] ?? '-');
                        $this->_log_perubahan_kuota(
                            $permohonan['id_pers'],
                            'pengurangan',
                            $jumlah_dipotong,
                            $kuota_sebelum,
                            $kuota_sesudah,
                            $keterangan_log,
                            $id_permohonan,
                            'permohonan_impor_disetujui',
                            $this->user['id'],
                            $kuota_barang_saat_ini['nama_barang'],
                            $id_kuota_barang_terpakai
                        );
                    }

                    $this->db->transCommit();
                }
            }

            $pesan_status = ($status_final == '3') ? 'Disetujui' : 'Ditolak';
            session()->setFlashdata('message', '<div class="alert alert-success" role="alert">Status permohonan telah berhasil diproses menjadi "' . $pesan_status . '"!</div>');
            return redirect()->to('admin/permohonanMasuk');
        }

        return view('admin/prosesSurat', $data);
    }

    public function edit_permohonan($id_permohonan = 0)
    {
        $builder = $this->db->table('user_permohonan up');
        $builder->select('up.*, upr.NamaPers as NamaPerusahaanPemohon');
        $builder->join('user_perusahaan upr', 'up.id_pers = upr.id_pers', 'left');
        $builder->where('up.id', $id_permohonan);
        $permohonan = $builder->get()->getRowArray();

        if (!$permohonan) {
            session()->setFlashdata('message', '<div class="alert alert-danger" role="alert">Permohonan tidak ditemukan.</div>');
            return redirect()->to('admin/permohonanMasuk');
        }

        $data = [
            'title' => 'Returnable Package',
            'subtitle' => 'Edit Permohonan (Admin)',
            'user' => $this->user,
            'permohonan_edit' => $permohonan,
            'user_perusahaan_pemohon' => $this->db->table('user_perusahaan')->where('id_pers', $permohonan['id_pers'])->get()->getRowArray(),
            'validation' => $this->validation
        ];

        // Get available quota items
        $builder = $this->db->table('user_kuota_barang');
        $builder->select('id_kuota_barang, nama_barang, remaining_quota_barang, nomor_skep_asal');
        $builder->where('id_pers', $permohonan['id_pers']);
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

        if ($this->request->getMethod() === 'post' && $this->validate($rules)) {
            $id_kuota_barang_dipilih = (int)$this->request->getPost('id_kuota_barang_selected');
            $nama_barang_input = $this->request->getPost('NamaBarang');
            $jumlah_barang_dimohon = (float)$this->request->getPost('JumlahBarang');

            $kuota_valid = $this->db->table('user_kuota_barang')
                ->where([
                    'id_kuota_barang' => $id_kuota_barang_dipilih,
                    'id_pers' => $permohonan['id_pers'],
                    'status_kuota_barang' => 'active'
                ])->get()->getRowArray();

            if (!$kuota_valid || $kuota_valid['nama_barang'] != $nama_barang_input) {
                session()->setFlashdata('message', '<div class="alert alert-danger" role="alert">Data kuota tidak valid.</div>');
                return redirect()->back();
            }

            $sisa_kuota_efektif = (float)$kuota_valid['remaining_quota_barang'];
            if ($permohonan['id_kuota_barang_digunakan'] == $id_kuota_barang_dipilih) {
                $sisa_kuota_efektif += (float)$permohonan['JumlahBarang'];
            }

            if ($jumlah_barang_dimohon > $sisa_kuota_efektif) {
                session()->setFlashdata('message', '<div class="alert alert-danger" role="alert">Jumlah melebihi sisa kuota.</div>');
                return redirect()->back();
            }

            $update_data = [
                'nomorSurat' => $this->request->getPost('nomorSurat'),
                'TglSurat' => $this->request->getPost('TglSurat'),
                'NamaBarang' => $nama_barang_input,
                'JumlahBarang' => $jumlah_barang_dimohon,
                'id_kuota_barang_digunakan' => $id_kuota_barang_dipilih,
                'NoSkep' => $kuota_valid['nomor_skep_asal'],
                'file_bc_manifest' => $permohonan['file_bc_manifest']
            ];

            $bcFile = $this->request->getFile('file_bc_manifest_admin_edit');
            if ($bcFile && $bcFile->isValid() && !$bcFile->hasMoved()) {
                $uploadPath = FCPATH . 'uploads/bc_manifest/';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }

                if (!empty($permohonan['file_bc_manifest']) && file_exists($uploadPath . $permohonan['file_bc_manifest'])) {
                    @unlink($uploadPath . $permohonan['file_bc_manifest']);
                }

                $newName = $bcFile->getRandomName();
                $bcFile->move($uploadPath, $newName);
                $update_data['file_bc_manifest'] = $newName;
            }

            $this->db->table('user_permohonan')->where('id', $id_permohonan)->update($update_data);
            session()->setFlashdata('message', '<div class="alert alert-success" role="alert">Permohonan berhasil diupdate.</div>');
            return redirect()->to('admin/detail_permohonan_admin/' . $id_permohonan);
        }

        return view('admin/form_edit_permohonan_admin', $data);
    }

    //================================================================
    // KUOTA MANAGEMENT
    //================================================================

    public function monitoring_kuota()
    {
        $builder = $this->db->table('user_perusahaan up');
        $builder->select('up.id_pers, up.NamaPers, u.email as user_email,
            (SELECT GROUP_CONCAT(DISTINCT ukb.nomor_skep_asal SEPARATOR ", ") FROM user_kuota_barang ukb WHERE ukb.id_pers = up.id_pers AND ukb.status_kuota_barang = "active") as list_skep_aktif,
            (SELECT SUM(ukb.initial_quota_barang) FROM user_kuota_barang ukb WHERE ukb.id_pers = up.id_pers) as total_initial_kuota_barang,
            (SELECT SUM(ukb.remaining_quota_barang) FROM user_kuota_barang ukb WHERE ukb.id_pers = up.id_pers) as total_remaining_kuota_barang
        ');
        $builder->join('user u', 'up.id_pers = u.id', 'left')->orderBy('up.NamaPers', 'ASC');
        
        $data = [
            'title' => 'Returnable Package', 'subtitle' => 'Monitoring Kuota Perusahaan',
            'user' => $this->user, 'monitoring_data' => $builder->get()->getResultArray()
        ];
        
        return view('admin/monitoring_kuota_view', $data);
    }

    public function histori_kuota_perusahaan($id_pers = 0)
    {
        $builder = $this->db->table('user_perusahaan up');
        $builder->select('up.id_pers, up.NamaPers, up.npwp, u.email as email_kontak, u.name as nama_kontak_user');
        $builder->join('user u', 'up.id_pers = u.id', 'left')->where('up.id_pers', $id_pers);
        $perusahaan = $builder->get()->getRowArray();

        if (!$id_pers || !$perusahaan) {
            throw PageNotFoundException::forPageNotFound('Data perusahaan tidak ditemukan.');
        }

        $data = [
            'title' => 'Returnable Package', 'subtitle' => 'Histori & Detail Kuota Perusahaan',
            'user' => $this->user, 'perusahaan' => $perusahaan, 'id_pers_untuk_histori' => $id_pers,
        ];

        return view('admin/histori_kuota_perusahaan_view', $data);
    }
    
    public function ajax_get_rincian_kuota_barang($id_pers = 0)
    {
        if (!$this->request->isAJAX() || !$id_pers) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid Request']);
        }

        $rincian_kuota = $this->db->table('user_kuota_barang ukb')
            ->where('ukb.id_pers', $id_pers)
            ->orderBy('ukb.nama_barang ASC, ukb.waktu_pencatatan DESC')
            ->get()->getResultArray();

        return $this->response->setJSON(['data' => $rincian_kuota]);
    }

    public function ajax_get_log_transaksi_kuota($id_pers = 0)
    {
        if (!$this->request->isAJAX() || !$id_pers) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid Request']);
        }
        
        $log_transaksi = $this->db->table('log_kuota_perusahaan lk')
            ->select('lk.*, u_admin.name as nama_pencatat')
            ->join('user u_admin', 'lk.dicatat_oleh_user_id = u_admin.id', 'left')
            ->where('lk.id_pers', $id_pers)
            ->orderBy('lk.tanggal_transaksi DESC, lk.id_log DESC')
            ->get()->getResultArray();
            
        return $this->response->setJSON(['data' => $log_transaksi]);
    }

    public function daftar_pengajuan_kuota()
    {
        $builder = $this->db->table('user_pengajuan_kuota upk');
        $builder->select('upk.*, upr.NamaPers, u.email as user_email');
        $builder->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $builder->join('user u', 'upk.id_pers = u.id', 'left');
        $builder->orderBy('FIELD(upk.status, "pending") DESC, upk.submission_date DESC', '', false);
        
        $data = [
            'title' => 'Returnable Package',
            'subtitle' => 'Daftar Pengajuan Kuota',
            'user' => $this->user,
            'pengajuan_kuota' => $builder->get()->getResultArray(),
        ];

        return view('admin/daftar_pengajuan_kuota', $data);
    }

    public function proses_pengajuan_kuota($id_pengajuan)
    {
        // 1. Pengambilan Data Awal
        $builder = $this->db->table('user_pengajuan_kuota upk');
        $builder->select('upk.*, upr.NamaPers, upr.initial_quota as initial_quota_sebelum, upr.remaining_quota as remaining_quota_sebelum, u.email as user_email');
        $builder->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $builder->join('user u', 'upk.id_pers = u.id', 'left');
        $builder->where('upk.id', $id_pengajuan);
        $pengajuan = $builder->get()->getRowArray();

        // 2. Validasi Awal
        if (!$pengajuan || !in_array($pengajuan['status'], ['pending', 'diproses', 'approved'])) {
            $pesan_error = 'Pengajuan kuota tidak ditemukan atau statusnya tidak dapat diproses (Status saat ini: ' . ($pengajuan['status'] ?? 'N/A') . ').';
            session()->setFlashdata('message', '<div class="alert alert-danger" role="alert">' . $pesan_error . '</div>');
            return redirect()->to('admin/daftar_pengajuan_kuota');
        }

        // 3. Logika untuk menangani POST (jika form disubmit)
        if ($this->request->getMethod() === 'post') {
            dd($this->request->getPost());
            // Definisikan aturan validasi dasar
            $rules = [
                'status_pengajuan' => 'required|in_list[approved,rejected,diproses]',
                'admin_notes'      => 'trim'
            ];

            // Tambahkan aturan jika status 'approved'
            if ($this->request->getPost('status_pengajuan') == 'approved') {
                $rules['approved_quota']     = 'trim|required|numeric|greater_than[0]';
                $rules['nomor_sk_petugas']   = 'trim|required|max_length[100]';
                $rules['tanggal_sk_petugas'] = 'trim|required|valid_date[Y-m-d]';

                // Wajibkan upload file HANYA jika file sebelumnya tidak ada
                if (empty($pengajuan['file_sk_petugas'])) {
                    $rules['file_sk_petugas'] = 'uploaded[file_sk_petugas]|max_size[file_sk_petugas,2048]|ext_in[file_sk_petugas,pdf,jpg,png,jpeg]';
                }
            }
            
            // Wajibkan catatan jika ditolak
            if ($this->request->getPost('status_pengajuan') == 'rejected') {
                $rules['admin_notes'] = 'trim|required';
            }

            // Jalankan Validasi
            if (!$this->validate($rules)) {
                // Jika validasi gagal, kembali ke form dengan membawa error dan input lama
                return redirect()->back()->withInput();
            }

            // --- JIKA VALIDASI SUKSES ---

            // Ambil data dari form
            $status         = $this->request->getPost('status_pengajuan');
            $approved_quota = ($status == 'approved') ? (float)$this->request->getPost('approved_quota') : 0;

            $update_data = [
                'status'             => $status,
                'admin_notes'        => $this->request->getPost('admin_notes'),
                'processed_date'     => date('Y-m-d H:i:s'),
                'nomor_sk_petugas'   => $this->request->getPost('nomor_sk_petugas'),
                'tanggal_sk_petugas' => $this->request->getPost('tanggal_sk_petugas'),
                'approved_quota'     => $approved_quota
            ];

            // Handle file upload jika ada file baru
            $skFile = $this->request->getFile('file_sk_petugas');
            if ($skFile->isValid() && !$skFile->hasMoved()) {
                $uploadPath = FCPATH . 'uploads/sk_kuota/';
                // Hapus file lama jika ada
                if (!empty($pengajuan['file_sk_petugas']) && file_exists($uploadPath . $pengajuan['file_sk_petugas'])) {
                    @unlink($uploadPath . $pengajuan['file_sk_petugas']);
                }
                // Pindahkan file baru
                $newName = $skFile->getRandomName();
                $skFile->move($uploadPath, $newName);
                $update_data['file_sk_petugas'] = $newName;
            }

            // Update tabel pengajuan
            $this->db->table('user_pengajuan_kuota')->where('id', $id_pengajuan)->update($update_data);

            // Jika disetujui, proses penambahan kuota barang
            if ($status == 'approved' && $approved_quota > 0) {
                $id_pers = $pengajuan['id_pers'];
                $nama_barang = $pengajuan['nama_barang_kuota'];

                // Hapus entri kuota lama dari pengajuan ini jika ada (untuk kasus re-approval)
                $this->db->table('user_kuota_barang')->where('id_pengajuan_kuota', $id_pengajuan)->delete();

                if ($id_pers && !empty($nama_barang)) {
                    $kuota_barang_data = [
                        'id_pers'               => $id_pers,
                        'id_pengajuan_kuota'    => $id_pengajuan,
                        'nama_barang'           => $nama_barang,
                        'initial_quota_barang'  => $approved_quota,
                        'remaining_quota_barang'=> $approved_quota,
                        'nomor_skep_asal'       => $update_data['nomor_sk_petugas'],
                        'tanggal_skep_asal'     => $update_data['tanggal_sk_petugas'],
                        'status_kuota_barang'   => 'active',
                        'dicatat_oleh_user_id'  => $this->user['id'],
                        'waktu_pencatatan'      => date('Y-m-d H:i:s')
                    ];
                    $this->db->table('user_kuota_barang')->insert($kuota_barang_data);
                    $id_kuota_barang_baru = $this->db->insertID();

                    if ($id_kuota_barang_baru) {
                        $this->_log_perubahan_kuota(
                            $id_pers, 'penambahan', $approved_quota, 0, $approved_quota,
                            'Persetujuan Pengajuan Kuota. Barang: ' . $nama_barang . '. No. SK: ' . ($update_data['nomor_sk_petugas'] ?: '-'),
                            $id_pengajuan, 'pengajuan_kuota_disetujui', $this->user['id'], $nama_barang, $id_kuota_barang_baru
                        );
                    }
                }
            }

            session()->setFlashdata('message', '<div class="alert alert-success" role="alert">Pengajuan kuota telah berhasil diproses!</div>');
            return redirect()->to('admin/daftar_pengajuan_kuota');
        }

        // 4. Logika untuk menampilkan GET (jika halaman diakses pertama kali atau setelah redirect)
        $data = [
            'title'      => 'Returnable Package',
            'subtitle'   => 'Proses Pengajuan Kuota',
            'user'       => $this->user,
            'pengajuan'  => $pengajuan,
            'validation' => $this->validation, // Ambil dari property class
        ];

        return view('admin/proses_pengajuan_kuota_form', $data);
    }

    public function detailPengajuanKuotaAdmin($id_pengajuan)
    {
        $builder = $this->db->table('user_pengajuan_kuota upk');
        $builder->select('upk.*, upr.NamaPers, upr.npwp AS npwp_perusahaan, upr.alamat as alamat_perusahaan, upr.pic, upr.jabatanPic, u.email AS user_email_pemohon, u.name AS nama_pemohon');
        $builder->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $builder->join('user u', 'upk.id_pers = u.id', 'left');
        $builder->where('upk.id', $id_pengajuan);
        $pengajuan = $builder->get()->getRowArray();

        if (!$pengajuan) {
            session()->setFlashdata('message', '<div class="alert alert-danger" role="alert">Data pengajuan kuota tidak ditemukan.</div>');
            return redirect()->to('admin/daftar_pengajuan_kuota');
        }

        $data = [
            'title' => 'Returnable Package',
            'subtitle' => 'Detail Proses Pengajuan Kuota',
            'user' => $this->user,
            'pengajuan' => $pengajuan,
        ];

        return view('admin/detail_pengajuan_kuota_view', $data);
    }

    public function print_pengajuan_kuota($id_pengajuan)
    {
        $builder = $this->db->table('user_pengajuan_kuota upk');
        $builder->select('upk.*, upr.NamaPers, upr.npwp AS npwp_perusahaan, upr.alamat as alamat_perusahaan, upr.pic, upr.jabatanPic, u.email AS user_email, u.name AS user_name_pengaju, u.image AS logo_perusahaan_file');
        $builder->join('user_perusahaan upr', 'upk.id_pers = upr.id_pers', 'left');
        $builder->join('user u', 'upk.id_pers = u.id', 'left');
        $builder->where('upk.id', $id_pengajuan);
        $pengajuan = $builder->get()->getRowArray();

        if (!$pengajuan) {
            session()->setFlashdata('message', '<div class="alert alert-danger" role="alert">Data pengajuan kuota tidak ditemukan.</div>');
            return redirect()->to('admin/daftar_pengajuan_kuota');
        }

        $data = [
            'title' => 'Detail Proses Pengajuan Kuota',
            'user_login' => $this->user,
            'pengajuan' => $pengajuan,
            'user' => $this->db->table('user')->where('id', $pengajuan['id_pers'])->get()->getRowArray(),
            'user_perusahaan' => $this->db->table('user_perusahaan')->where('id_pers', $pengajuan['id_pers'])->get()->getRowArray(),
        ];

        return view('user/FormPengajuanKuota_print', $data);
    }

    public function download_sk_kuota_admin($id_pengajuan)
    {
        $pengajuan = $this->db->table('user_pengajuan_kuota')->where('id', $id_pengajuan)->get()->getRowArray();

        if ($pengajuan && !empty($pengajuan['file_sk_petugas'])) {
            $file_path = FCPATH . 'uploads/sk_kuota/' . $pengajuan['file_sk_petugas'];

            if (file_exists($file_path)) {
                return $this->response->download($file_path, null);
            } else {
                session()->setFlashdata('message', '<div class="alert alert-danger" role="alert">File Surat Keputusan tidak ditemukan di server.</div>');
                return redirect()->to('admin/daftar_pengajuan_kuota');
            }
        } else {
            session()->setFlashdata('message', '<div class="alert alert-warning" role="alert">Surat Keputusan belum tersedia untuk pengajuan ini.</div>');
            return redirect()->to('admin/daftar_pengajuan_kuota');
        }
    }

    //================================================================
    // PRIVATE METHODS
    //================================================================

    private function _log_perubahan_kuota($id_pers, $jenis, $jumlah, $sebelum, $sesudah, $keterangan, $ref_id = null, $tipe_ref = null, $user_id = null, $nama_barang = null, $id_kuota_ref = null)
    {
        $log_data = [
            'id_pers' => $id_pers, 'nama_barang_terkait' => $nama_barang, 'id_kuota_barang_referensi' => $id_kuota_ref,
            'jenis_transaksi' => $jenis, 'jumlah_perubahan' => $jumlah, 'sisa_kuota_sebelum' => $sebelum, 'sisa_kuota_setelah' => $sesudah,
            'keterangan' => $keterangan, 'id_referensi_transaksi' => $ref_id, 'tipe_referensi' => $tipe_ref,
            'dicatat_oleh_user_id' => $user_id, 'tanggal_transaksi' => date('Y-m-d H:i:s')
        ];

        if (!empty($log_data['id_pers']) && !empty($log_data['nama_barang_terkait'])) { 
            $this->db->table('log_kuota_perusahaan')->insert($log_data);
        }
    }

    //================================================================
    // PASSWORD MANAGEMENT
    //================================================================

    public function changepass($user_id = null)
    {
        helper(['form']);
        error_log("1. Helper loaded");
        
        // Remove this session check - let the AuthFilter handle authentication
        // The redirect loop happens when AuthFilter sends users with force_change_password=1 
        // back to changepass, but then changepass redirects to login
        
        $current_user_id = session()->get('user_id');
        error_log("2. Current user ID: " . $current_user_id);
        
        // If no user_id provided, admin is changing their own password
        if ($user_id === null) {
            $user_id = $current_user_id;
        }
        error_log("3. Target user ID: " . $user_id);
        
        $user_id = (int) $user_id;

        // Get user data
        $user_to_change = $this->db->table('user')->where('id', $user_id)->get()->getRowArray();
        error_log("4. User query executed");
        
        if (!$user_to_change) {
            error_log("5. USER NOT FOUND - redirecting to admin");
            session()->setFlashdata('message', '<div class="alert alert-danger">User not found.</div>');
            return redirect()->to('admin');
        }
        error_log("5. User found: " . $user_to_change['name']);

        error_log("6. About to handle POST request check");
        
        if ($this->request->getMethod() === 'post') {
            error_log("7. POST request detected");
            
            $validation_rules = [
                'password'  => 'required|min_length[6]',
                'password2' => 'required|matches[password]'
            ];
            
            if ($this->validation->setRules($validation_rules)->run($this->request->getPost())) {
                error_log("8. Validation passed - updating password");
                
                $new_password = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
                
                $this->db->table('user')->where('id', $user_id)->update([
                    'password' => $new_password,
                    'force_change_password' => 0,  // This is crucial - it stops the redirect loop
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                error_log("9. Password updated - redirecting to admin with success message");
                session()->setFlashdata('message', '<div class="alert alert-success">Password berhasil diubah.</div>');
                return redirect()->to('admin');
            } else {
                error_log("8. Validation failed");
            }
        } else {
            error_log("7. GET request - showing form");
        }

        error_log("10. Preparing view data");
        $data = [
            'title' => 'Change Password',
            'user_for_pass_change' => $user_to_change,
            'validation' => $this->validation,
            'cancel_redirect_url' => 'admin'
        ];

        error_log("11. About to return view");
        
        // Check if view exists
        if (!file_exists(APPPATH . 'Views/auth/changepass.php')) {
            error_log("12. ERROR - View file does not exist: " . APPPATH . 'Views/auth/changepass.php');
            die("View file missing: " . APPPATH . 'Views/auth/changepass.php');
        }
        
        error_log("12. View file exists - returning view");
        return view('auth/changepass', $data);
    }

}
