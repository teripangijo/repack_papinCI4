<?php namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = \Config\Services::session();

        // Dapatkan URI saat ini untuk pengecualian
        $currentUri = service('uri')->getPath();

        // Daftar URI yang DIİZINKAN diakses TANPA LOGIN (misalnya halaman otentikasi)
        // Ini harus mencakup semua rute di grup 'auth' dan rute '/' jika itu halaman login Anda.
        $publicUris = [
            'auth',                    // Rute dasar /auth
            'auth/registration',
            'auth/verify_mfa_login',
            'auth/blocked',
            'auth/changepass',
            'auth/bypass',
            '/',                       // Jika root URL adalah halaman login
            'auth/logout'              // Tambahkan logout juga agar bisa diakses untuk hancurkan sesi
        ];

        // Cek apakah URI saat ini adalah salah satu dari URI publik
        // str_starts_with($currentUri, 'auth/') digunakan untuk mencocokkan 'auth/apa_saja'
        if (in_array($currentUri, $publicUris) || str_starts_with($currentUri, 'auth/')) {
            return; // Jangan lakukan pengecekan sesi untuk rute publik ini
        }

        // --- Lanjutkan dengan pengecekan sesi untuk rute yang DILINDUNGI ---
        if (!$session->get('email')) {
            $session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Sesi Anda telah berakhir. Silakan login kembali.</div>');
            return redirect()->to(base_url('auth'));
        }

        // --- Logika MFA dan Force Change Password (jika user SUDAH LOGIN) ---
        // Ini adalah tempat yang tepat untuk menerapkan pengecekan MFA/FCP global
        // Pastikan Anda mendapatkan data user di sini.
        $user_id = $session->get('user_id');
        if ($user_id) {
            $db = \Config\Database::connect();
            $user = $db->table('user')->where('id', $user_id)->get()->getRowArray();

            // Pastikan user ditemukan di database setelah login
            if (!$user) {
                $session->destroy();
                $session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Sesi tidak valid. Pengguna tidak ditemukan.</div>');
                return redirect()->to(base_url('auth'));
            }

            $roles_wajib_mfa = [1, 2, 3, 4, 5]; // Definisi role yang wajib MFA

            // Logika MFA: Jika user memiliki role yang wajib MFA dan MFA belum diaktifkan
            if (in_array(($user['role_id'] ?? null), $roles_wajib_mfa) && !($user['is_mfa_enabled'] ?? false)) {
                // Kecualikan rute setup_mfa, verify_mfa, dan logout dari redirect paksa
                // Anda perlu tahu URL setup_mfa Anda (misal: user/setup_mfa, admin/setup_mfa)
                // Disarankan menggunakan segmen URL yang lebih spesifik untuk pengecualian
                if (
                    !str_contains($currentUri, 'setup_mfa') && // Cek apakah URI mengandung 'setup_mfa'
                    !str_contains($currentUri, 'verify_mfa') && // Cek apakah URI mengandung 'verify_mfa' (untuk proses)
                    $currentUri != 'auth/logout' &&
                    $currentUri != 'auth/verify_mfa_login' // Tambahkan ini jika itu rute verifikasi MFA
                ) {
                    $session->setFlashdata('message', '<div class="alert alert-warning" role="alert">Untuk keamanan, Anda wajib mengaktifkan Multi-Factor Authentication (MFA).</div>');
                    // Redirect ke halaman setup MFA yang relevan berdasarkan role
                    if ($user['role_id'] == 2) {
                        return redirect()->to(base_url('user/setup_mfa'));
                    } elseif ($user['role_id'] == 3) {
                        return redirect()->to(base_url('petugas/setup_mfa'));
                    } else {
                        // Redirect default jika role lain (sesuaikan)
                        return redirect()->to(base_url('admin/setup_mfa'));
                    }
                }
            }

            // Logika MFA: Jika MFA sudah diaktifkan tapi belum diverifikasi di sesi ini
            if (($user['is_mfa_enabled'] ?? false) && ($session->get('mfa_verified') !== true)) {
                // Kecualikan halaman verify_mfa_login dari redirect paksa
                if ($currentUri != 'auth/verify_mfa_login' && $currentUri != 'auth/logout') {
                     return redirect()->to(base_url('auth/verify_mfa_login'));
                }
            }

            // // Logika Force Change Password
            // if (($user['force_change_password'] ?? 0) == 1) {
            //     // Kecualikan semua halaman changepass dan logout dari redirect paksa
            //     if (!str_contains($currentUri, 'changepass') && $currentUri != 'auth/logout') {
            //         $session->setFlashdata('message', '<div class="alert alert-warning" role="alert">Untuk keamanan, Anda wajib mengganti password Anda.</div>');
                    
            //         // Redirect to role-appropriate changepass route
            //         $role_id = $user['role_id'] ?? 2;
            //         switch ($role_id) {
            //             case 1:
            //                 return redirect()->to(base_url('admin/changepass'));
            //             case 2:
            //                 return redirect()->to(base_url('user/changepass'));
            //             case 3:
            //                 return redirect()->to(base_url('petugas/changepass'));
            //             case 4:
            //                 return redirect()->to(base_url('monitoring/changepass'));
            //             default:
            //                 return redirect()->to(base_url('auth/changepass'));
            //         }
            //     }
            // }

        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}