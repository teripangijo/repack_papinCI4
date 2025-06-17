<?php

namespace App\Controllers; // Pastikan namespace ini sesuai

use App\Controllers\BaseController; // Menggunakan BaseController yang baru

class Umum extends BaseController // Meng-extend BaseController
{
    protected $session;
    protected $db;
    // Helper repack_helper sudah dimuat di BaseController::$helpers

    public function __construct()
    {
        // Konstruktor kosong karena inisialisasi dasar ditangani oleh BaseController
    }

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // Fungsi is_loggedin() kemungkinan adalah helper. Pastikan helper ini dimuat
        // dan logikanya disesuaikan untuk CI4 (misalnya, cek session)
        if (function_exists('is_loggedin')) {
            is_loggedin();
        } else {
            // Fallback jika helper tidak ada atau tidak berfungsi seperti yang diharapkan
            if (!$this->session->get('email')) {
                $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Sesi Anda tidak valid. Silakan login kembali.</div>');
                return redirect()->to(base_url('auth/logout'));
            }
        }
    }

    public function index()
    {
        $data['menu'] = $this->db->table('user_menu')->get()->getResultArray();
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        $data['permohonan'] = $this->db->table('user_permohonan')->get()->getResultArray(); // Query tanpa kondisi where
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Status Permohonan';

        echo view('templates/header', $data);
        echo view('templates/sidebar', $data);
        echo view('templates/topbar', $data);
        echo view('umum/permohonan-masuk', $data);
        echo view('templates/footer');
    }

    public function printPdf($id)
    {
        $user = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        $permohonan = $this->db->table('user_permohonan')->where('id', $id)->get()->getRowArray();
        
        // Pastikan $permohonan tidak null sebelum mencoba mengaksesnya
        $id_pers = $permohonan['id_pers'] ?? null;
        $user_perusahaan = null;
        if ($id_pers) {
            $user_perusahaan = $this->db->table('user_perusahaan')->where('id_pers', $id_pers)->get()->getRowArray();
        }
        
        $data = array(
            'user' => $user,
            'permohonan' => $permohonan,
            'user_perusahaan' => $user_perusahaan,
        );

        echo view('user/FormPermohonan', $data);
    }
}
