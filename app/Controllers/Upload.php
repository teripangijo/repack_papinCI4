<?php

namespace App\Controllers; // Pastikan namespace ini sesuai

use App\Controllers\BaseController; // Menggunakan BaseController yang baru
// use CodeIgniter\Files\FileCollection; // Jika Anda ingin menggunakan FileCollection

class Upload extends BaseController // Meng-extend BaseController
{
    protected $session;
    protected $db;
    protected $validation; // Properti untuk validasi

    public function __construct()
    {
        // Konstruktor kosong karena inisialisasi dasar ditangani oleh BaseController
    }

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->validation = \Config\Services::validation(); // Inisialisasi validasi
        // Helper form dan url sudah dimuat di BaseController::$helpers
    }

    public function index()
    {
        // Di CI4, error atau pesan dikelola melalui session flashdata atau variabel di view
        $data['error'] = $this->session->getFlashdata('upload_error') ?? ' '; // Mengambil flashdata jika ada

        echo view('admin/upload_form', $data);
    }

    public function do_upload()
    {
        $upload_path = FCPATH . 'ttd/'; // Pastikan path absolut

        // Pastikan direktori upload ada dan writable
        if (!is_dir($upload_path)) {
            if (!@mkdir($upload_path, 0777, true)) {
                $this->session->setFlashdata('upload_error', 'Gagal membuat direktori upload: ' . $upload_path);
                return redirect()->to(base_url('upload'));
            }
        }
        if (!is_writable($upload_path)) {
            $this->session->setFlashdata('upload_error', 'Direktori upload tidak writable: ' . $upload_path);
            return redirect()->to(base_url('upload'));
        }

        // Aturan validasi CI4 untuk upload file
        $rules = [
            'userfile' => [
                'label' => 'File',
                'rules' => 'uploaded[userfile]|max_size[userfile,1000]|ext_in[userfile,jpeg,png,pdf]|max_dims[userfile,10240,7680]',
                'errors' => [
                    'uploaded' => 'Anda harus memilih file untuk diunggah.',
                    'max_size' => 'Ukuran file {field} melebihi batas (1MB).',
                    'ext_in' => 'Tipe file {field} tidak diizinkan (Hanya JPEG, PNG, PDF).',
                    'max_dims' => 'Dimensi gambar {field} terlalu besar.'
                ]
            ],
        ];

        if (!$this->validate($rules)) {
            // Jika validasi gagal, simpan error ke flashdata
            $errors = $this->validator->getErrors();
            $error_message = implode('<br>', $errors); // Gabungkan semua pesan error
            $this->session->setFlashdata('upload_error', $error_message);
            return redirect()->to(base_url('upload'));
        } else {
            // Dapatkan file yang diunggah
            $file = $this->request->getFile('userfile');

            // Pindahkan file ke direktori tujuan dengan nama acak
            $newName = $file->getRandomName();
            $file->move($upload_path, $newName);

            if ($file->hasMoved()) {
                // File berhasil diunggah
                $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">File berhasil diunggah! Nama file: ' . htmlspecialchars($newName) . '</div>');
                // Untuk menampilkan nama file, Anda bisa mengarahkan ke halaman dengan data yang relevan
                // atau menyimpan nama file ke database jika perlu
                return redirect()->to(base_url('upload')); // Redirect ke halaman upload atau sukses
            } else {
                // Gagal memindahkan file (jarang terjadi jika validasi awal berhasil)
                $this->session->setFlashdata('upload_error', 'Gagal memindahkan file: ' . $file->getErrorString());
                return redirect()->to(base_url('upload'));
            }
        }
    }
}
