<?php

namespace App\Controllers; // Pastikan namespace ini sesuai

// Welcome controller tidak perlu meng-extend BaseController
// jika tidak memerlukan session, database, atau helper global lainnya
// atau Anda bisa membuatnya meng-extend BaseController jika diperlukan
// use App\Controllers\BaseController; 

class Welcome extends \CodeIgniter\Controller // Meng-extend CodeIgniter\Controller dasar
{
    /**
     * Metode Index untuk controller ini.
     *
     * Memetakan ke URL berikut
     * http://example.com/index.php/welcome
     * - atau -
     * http://example.com/index.php/welcome/index
     * - atau -
     * Karena controller ini diatur sebagai controller default di
     * config/routes.php, ini ditampilkan di http://example.com/
     *
     * Jadi metode publik lainnya yang tidak diawali dengan garis bawah akan
     * memetakan ke /index.php/welcome/<method_name>
     * @see https://codeigniter.com/user_guide/general/urls.html
     */
    public function index()
    {
        // Menggunakan helper view() untuk memuat tampilan
        // Tidak perlu $this->load->view() lagi
        echo view('welcome_message');
    }
}
