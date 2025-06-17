<?php

namespace App\Models; // Gunakan namespace App\Models

use CodeIgniter\Model; // Import kelas Model dasar CI4

class User_model extends Model
{
    // Nama tabel database yang terkait dengan model ini.
    protected $table = 'user'; // Model ini mengelola tabel 'user'
    // Kunci utama tabel.
    protected $primaryKey = 'id';
    // Mengizinkan auto-increment untuk primary key.
    protected $useAutoIncrement = true;

    // Menentukan field mana yang diizinkan untuk diisi (fillable) melalui metode insert/update Model.
    // Ini harus mencakup semua kolom yang dapat diubah dari form, seperti name, email, password, dll.
    protected $allowedFields = ['name', 'email', 'image', 'password', 'role_id', 'is_active', 'force_change_password', 'date_created', 'google2fa_secret', 'is_mfa_enabled']; // Sesuaikan dengan kolom yang relevan di tabel 'user'

    // Tipe data yang akan dikembalikan: 'array' atau 'object'.
    protected $returnType = 'array';
    // Menggunakan soft deletes? (Menandai record sebagai terhapus daripada menghapusnya secara fisik).
    protected $useSoftDeletes = false; // Sesuaikan jika ada kolom 'deleted_at'

    // Menggunakan timestamps? (created_at, updated_at).
    protected $useTimestamps = true; // Asumsi ada date_created dan mungkin updated_at
    // Nama kolom untuk waktu pembuatan.
    protected $createdField  = 'date_created'; // Sesuaikan jika nama kolom berbeda
    // Nama kolom untuk waktu pembaruan terakhir.
    protected $updatedField  = 'updated_at'; // Perlu ditambahkan di tabel jika tidak ada
    // Nama kolom untuk waktu penghapusan lunak.
    protected $deletedField  = 'deleted_at'; // Perlu ditambahkan di tabel jika tidak ada

    // Aturan validasi untuk operasi insert/update.
    protected $validationRules = [
        'email' => 'permit_empty|valid_email|is_unique[user.email,id,{id}]', // 'id' akan diganti secara dinamis saat update
        'password' => 'permit_empty|min_length[6]',
        'name' => 'permit_empty|max_length[100]',
        // Tambahkan aturan validasi lainnya sesuai kebutuhan Anda
    ];
    // Pesan validasi kustom untuk aturan di atas.
    protected $validationMessages = [
        'email' => [
            'is_unique' => 'Email ini sudah terdaftar.',
            'valid_email' => 'Format email tidak valid.'
        ],
        'password' => [
            'min_length' => 'Password terlalu pendek! (Minimal 6 karakter)'
        ]
    ];
    // Melewati validasi untuk insert/update tertentu.
    protected $skipValidation = false;

    /**
     * Catatan: Metode deleteMenu() di sini seharusnya tidak berada di User_model.
     * Metode ini menghapus data dari 'user_menu', yang seharusnya menjadi tanggung jawab Menu_model.
     * Saya menyimpannya di sini hanya untuk menunjukkan adaptasi jika Anda berkeras mempertahankannya (tidak disarankan).
     */

    /**
     * Menghapus sebuah menu berdasarkan ID-nya. (Tidak disarankan berada di model ini)
     *
     * @param int $id ID menu yang akan dihapus.
     * @return bool True jika berhasil dihapus, false jika gagal.
     */
    public function deleteMenu(int $id): bool
    {
        // Akses tabel user_menu
        return $this->db->table('user_menu')->delete(['id' => $id]);
    }

    /**
     * Memperbarui data pengguna berdasarkan ID-nya.
     * Menggantikan $this->db->where('id', $id)->update('user', $data);
     *
     * @param int $id ID pengguna yang akan diperbarui.
     * @param array $data Data yang akan diperbarui.
     * @return bool True jika berhasil diperbarui, false jika gagal.
     */
    public function updateUser(int $id, array $data): bool
    {
        // Menggunakan metode update() bawaan dari CodeIgniter\Model
        // Model akan secara otomatis memeriksa allowedFields dan validationRules
        return $this->update($id, $data);
    }

    // Anda bisa menambahkan metode-metode lain untuk operasi CRUD tabel 'user' di sini.
    // Contoh:
    /**
     * Mengambil pengguna berdasarkan ID.
     * @param int $id
     * @return array|object|null
     */
    public function getUserById(int $id)
    {
        return $this->find($id);
    }

    /**
     * Mengambil pengguna berdasarkan email.
     * @param string $email
     * @return array|object|null
     */
    public function getUserByEmail(string $email)
    {
        return $this->where('email', $email)->first();
    }
}
