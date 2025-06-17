<?php

namespace App\Controllers; // Pastikan namespace ini sesuai

use App\Controllers\BaseController; // Menggunakan BaseController yang baru
// use App\Models\Menu_model; // Jika Anda akan membuat atau menggunakan Menu_model CI4

class Menu extends BaseController // Meng-extend BaseController
{
    protected $session;
    protected $db;
    protected $validation; // Properti untuk validasi
    // protected $Menu_model; // Deklarasikan properti untuk model jika di-load

    public function __construct()
    {
        // Konstruktor kosong karena inisialisasi dasar ditangani oleh BaseController
    }

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->validation = \Config\Services::validation(); // Inisialisasi validasi

        // Fungsi is_loggedin() kemungkinan adalah helper. Pastikan helper ini dimuat
        // dan logikanya disesuaikan untuk CI4 (misalnya, cek session)
        if (function_exists('is_loggedin')) {
            is_loggedin();
        } else {
            // Jika is_loggedin bukan helper global, Anda mungkin perlu memindahkan logikanya
            // ke dalam konstruktor atau metode sebelum pemanggilan aksi.
            if (!$this->session->get('email')) {
                $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Sesi Anda tidak valid. Silakan login kembali.</div>');
                return redirect()->to(base_url('auth/logout'));
            }
        }
        // $this->Menu_model = new Menu_model(); // Inisialisasi model jika tidak di-autload atau diservice
    }

    public function index()
    {
        $data['menu'] = $this->db->table('user_menu')->get()->getResultArray();
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Manajemen Menu';

        $this->validation->setRules([
            'menu' => [
                'label' => 'Menu',
                'rules' => 'required',
                'errors' => [
                    'required' => '{field} wajib diisi.'
                ]
            ]
        ]);

        if (!$this->validation->withRequest($this->request)->run()) {
            echo view('templates/header', $data);
            echo view('templates/sidebar', $data);
            echo view('templates/topbar', $data);
            echo view('menu/index', $data);
            echo view('templates/footer');
        } else {
            $this->db->table('user_menu')->insert(['menu' => $this->request->getPost('menu')]);
            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Menu berhasil ditambahkan!</div>');
            return redirect()->to(base_url('menu'));
        }
    }

    public function delete($id)
    {
        // PENTING: Anda perlu mengadaptasi 'Menu_model' ke CI4 atau
        // langsung menggunakan query builder di sini.
        // Jika Menu_model CI3 memiliki method deleteMenu($id), Anda perlu mereplikasi logikanya di CI4.
        // Contoh langsung query builder:
        $this->db->table('user_menu')->where('id', $id)->delete();
        $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Menu berhasil dihapus!</div>');
        return redirect()->to(base_url('menu'));
    }

    public function update($id)
    {
        $data['menu'] = $this->db->table('user_menu')->where('id', $id)->get()->getRowArray();
        $data['id'] = $id; // ID tetap perlu dikirim ke view jika digunakan di form
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Update Menu';

        $this->validation->setRules([
            'menu' => [
                'label' => 'Menu',
                'rules' => 'required',
                'errors' => [
                    'required' => '{field} wajib diisi.'
                ]
            ]
        ]);

        if (!$this->validation->withRequest($this->request)->run()) {
            echo view('templates/header', $data);
            echo view('templates/sidebar', $data);
            echo view('templates/topbar', $data);
            echo view('menu/update', $data);
            echo view('templates/footer');
        } else {
            // Perhatikan bahwa di CI3 Anda menggunakan insert di sini, yang mungkin salah jika tujuannya update
            // Asumsi tujuan method ini adalah menampilkan form update, dan update_menu() yang melakukan update
            // Jika method ini juga seharusnya melakukan update setelah POST, maka logikanya perlu diubah.
            // Contoh: $this->db->table('user_menu')->where('id', $id)->update(['menu' => $this->request->getPost('menu')]);
            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Menu berhasil diupdate!</div>');
            return redirect()->to(base_url('menu')); // Redirect setelah POST berhasil
        }
    }

    public function update_menu()
    {
        $data_update = [
            'menu' => $this->request->getPost('update_menu')
        ];
        $id = $this->request->getPost('id');

        // PENTING: Anda perlu mengadaptasi 'Menu_model' ke CI4 atau
        // langsung menggunakan query builder di sini.
        // Jika Menu_model CI3 memiliki method updateMenu($id, $data), Anda perlu mereplikasi logikanya di CI4.
        // Contoh langsung query builder:
        $this->db->table('user_menu')->where('id', $id)->update($data_update);
        $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Menu berhasil diupdate!</div>');
        return redirect()->to(base_url('menu'));
    }

    public function submenu()
    {
        // PENTING: Jika Menu_model digunakan untuk getSubMenu(), Anda perlu mengadaptasinya ke CI4
        // atau mereplikasi logikanya langsung di sini menggunakan query builder.
        // Contoh langsung query builder jika getSubMenu() hanya mengambil semua dari user_sub_menu:
        $data['submenu'] = $this->db->table('user_sub_menu')->get()->getResultArray();
        // Jika getSubMenu() melakukan join, Anda perlu membangun query builder yang sesuai.
        /*
        // Contoh jika getSubMenu melakukan join ke user_menu
        $builder = $this->db->table('user_sub_menu usm');
        $builder->select('usm.*, um.menu as menu_name');
        $builder->join('user_menu um', 'um.id = usm.menu_id', 'left');
        $data['submenu'] = $builder->get()->getResultArray();
        */

        $data['menu'] = $this->db->table('user_menu')->get()->getResultArray();
        $data['user'] = $this->db->table('user')->where('email', $this->session->get('email'))->get()->getRowArray();
        $data['title'] = 'Returnable Package';
        $data['subtitle'] = 'Manajemen Sub Menu';

        $this->validation->setRules([
            'title' => 'required',
            'menu_id' => 'required',
            'url' => 'required',
            'icon' => 'required'
        ]);

        if (!$this->validation->withRequest($this->request)->run()) {
            echo view('templates/header', $data);
            echo view('templates/sidebar', $data);
            echo view('templates/topbar', $data);
            echo view('menu/submenu', $data);
            echo view('templates/footer');
        } else {
            $data_insert = [
                'title' => $this->request->getPost('title'),
                'menu_id' => $this->request->getPost('menu_id'),
                'url' => $this->request->getPost('url'),
                'icon' => $this->request->getPost('icon'),
                'is_active' => $this->request->getPost('is_active') // Pastikan ini ada di form
            ];
            $this->db->table('user_sub_menu')->insert($data_insert);
            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Sub Menu berhasil ditambahkan!</div>');
            return redirect()->to(base_url('menu/submenu'));
        }
    }
}
