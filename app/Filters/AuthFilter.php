<?php namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Baca 'saklar utama' MFA dari file .env
        // Default 'true' jika variabel tidak ditemukan (aman untuk produksi)
        $isMfaSystemEnabled = (getenv('MFA_ENABLED') ?? 'true') === 'true';
        
        $session = \Config\Services::session();
        $currentUri = service('uri')->getPath();

        // Daftar URI yang DIİZINKAN diakses TANPA LOGIN
        $publicUris = [
            'auth',
            'auth/registration',
            'auth/verify_mfa_login',
            'auth/blocked',
            'auth/changepass',
            'auth/bypass',
            '/',
            'auth/logout'
        ];

        // Cek jika URI saat ini bersifat publik, lewati filter
        if (in_array($currentUri, $publicUris) || str_starts_with($currentUri, 'auth/')) {
            return;
        }

        // --- Pengecekan sesi untuk rute yang DILINDUNGI ---
        if (!$session->get('email')) {
            $session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Sesi Anda telah berakhir. Silakan login kembali.</div>');
            return redirect()->to(base_url('auth'));
        }

        // --- Logika lanjutan jika user SUDAH LOGIN ---
        $user_id = $session->get('user_id');
        if ($user_id) {
            $db = \Config\Database::connect();
            $user = $db->table('user')->where('id', $user_id)->get()->getRowArray();

            // Pastikan user ditemukan di database
            if (!$user) {
                $session->destroy();
                $session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Sesi tidak valid. Pengguna tidak ditemukan.</div>');
                return redirect()->to(base_url('auth'));
            }

            // Jika saklar MFA aktif, jalankan semua logika MFA
            if ($isMfaSystemEnabled) {
                
                $roles_wajib_mfa = [1, 2, 3, 4, 5];

                // Logika MFA 1: Jika user memiliki role yang wajib MFA dan MFA belum diaktifkan
                if (in_array(($user['role_id'] ?? null), $roles_wajib_mfa) && !($user['is_mfa_enabled'] ?? false)) {
                    // Kecualikan halaman setup & verifikasi dari redirect paksa
                    if (
                        !str_contains($currentUri, 'setup_mfa') &&
                        !str_contains($currentUri, 'verify_mfa') &&
                        $currentUri != 'auth/logout' &&
                        $currentUri != 'auth/verify_mfa_login'
                    ) {
                        $session->setFlashdata('message', '<div class="alert alert-warning" role="alert">Untuk keamanan, Anda wajib mengaktifkan Multi-Factor Authentication (MFA).</div>');
                        // Arahkan ke halaman setup MFA yang sesuai dengan role
                        if ($user['role_id'] == 2) {
                            return redirect()->to(base_url('user/setup_mfa'));
                        } elseif ($user['role_id'] == 3) {
                            return redirect()->to(base_url('petugas/setup_mfa'));
                        } else {
                            return redirect()->to(base_url('admin/setup_mfa'));
                        }
                    }
                }

                // Logika MFA 2: Jika MFA sudah diaktifkan tapi belum diverifikasi di sesi ini
                if (($user['is_mfa_enabled'] ?? false) && ($session->get('mfa_verified') !== true)) {
                    // Kecualikan halaman verifikasi MFA dari redirect paksa
                    if ($currentUri != 'auth/verify_mfa_login' && $currentUri != 'auth/logout') {
                         return redirect()->to(base_url('auth/verify_mfa_login'));
                    }
                }
            }

            // // Logika Force Change Password
            // if (($user['force_change_password'] ?? 0) == 1) {
            //     if (!str_contains($currentUri, 'changepass') && $currentUri != 'auth/logout') {
            //         $session->setFlashdata('message', '<div class="alert alert-warning" role="alert">Anda wajib mengganti password Anda.</div>');
            //         // ... (logika redirect ganti password)
            //     }
            // }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
