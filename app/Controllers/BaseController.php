<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController menyediakan tempat yang nyaman untuk memuat komponen
 * dan menjalankan fungsi yang dibutuhkan oleh semua controller Anda.
 * Perluas kelas ini di setiap controller baru:
 * class Home extends BaseController
 *
 * Untuk keamanan, pastikan untuk mendeklarasikan metode baru sebagai protected atau private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance dari objek Request utama.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * Sebuah array helper yang akan dimuat secara otomatis saat
     * instansiasi kelas. Helper ini akan tersedia
     * untuk semua controller lain yang memperluas BaseController.
     *
     * @var list<string>
     */
    protected $helpers = ['form', 'url', 'repack_helper', 'download']; // Tambahkan helper yang Anda gunakan secara global

    /**
     * Pastikan untuk mendeklarasikan properti untuk setiap pengambilan properti yang Anda inisialisasi.
     * Pembuatan properti dinamis tidak diizinkan di PHP 8.2.
     */
    protected $session; // Deklarasikan properti session
    protected $db;      // Deklarasikan properti db
    protected $router;  // Deklarasikan properti router
    protected $validation; // Deklarasikan properti validation


    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Jangan Edit Baris Ini
        parent::initController($request, $response, $logger);

        // Preload model, library, dll, di sini.

        // Inisialisasi session, database, router, dan validation service
        $this->session = \Config\Services::session();
        $this->db = \Config\Database::connect(); // Pastikan Anda memiliki konfigurasi database di app/Config/Database.php
        $this->router = \Config\Services::router();
        $this->validation = \Config\Services::validation();
    }
}
