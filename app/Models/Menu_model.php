<?php

namespace App\Models; // Gunakan namespace App\Models

use CodeIgniter\Model; // Import kelas Model dasar CI4

class Menu_model extends Model
{
    // Nama tabel database yang terkait dengan model ini.
    protected $table = 'user_menu'; 
    // Kunci utama tabel.
    protected $primaryKey = 'id';
    // Mengizinkan auto-increment untuk primary key.
    protected $useAutoIncrement = true;

    // Menentukan field mana yang diizinkan untuk diisi (fillable) melalui metode insert/update Model.
    protected $allowedFields = ['menu']; // Sesuaikan dengan kolom yang bisa diisi di tabel user_menu

    // Tipe data yang akan dikembalikan: 'array' atau 'object'.
    protected $returnType = 'array';
    // Menggunakan soft deletes? (Menandai record sebagai terhapus daripada menghapusnya secara fisik).
    protected $useSoftDeletes = false; // Karena tidak ada kolom deleted_at di user_menu

    // Menggunakan timestamps? (created_at, updated_at).
    protected $useTimestamps = false; // Sesuaikan jika tabel user_menu memiliki kolom timestamps
    // Nama kolom untuk waktu pembuatan.
    protected $createdField  = 'created_at';
    // Nama kolom untuk waktu pembaruan terakhir.
    protected $updatedField  = 'updated_at';
    // Nama kolom untuk waktu penghapusan lunak.
    protected $deletedField  = 'deleted_at';

    // Aturan validasi untuk operasi insert/update.
    protected $validationRules = [];
    // Pesan validasi kustom untuk aturan di atas.
    protected $validationMessages = [];
    // Melewati validasi untuk insert/update tertentu.
    protected $skipValidation = false;

    /**
     * Menghapus sebuah menu berdasarkan ID-nya.
     * Menggantikan $this->db->where('id', $id)->delete('user_menu');
     *
     * @param int $id ID menu yang akan dihapus.
     * @return bool True jika berhasil dihapus, false jika gagal.
     */
    public function deleteMenu(int $id): bool
    {
        // Menggunakan metode delete() bawaan dari CodeIgniter\Model
        return $this->delete($id);
    }

    /**
     * Memperbarui sebuah menu berdasarkan ID-nya.
     * Menggantikan $this->db->where('id', $id)->update('user_menu', $data);
     *
     * @param int $id ID menu yang akan diperbarui.
     * @param array $data Data yang akan diperbarui.
     * @return bool True jika berhasil diperbarui, false jika gagal.
     */
    public function updateMenu(int $id, array $data): bool
    {
        // Menggunakan metode update() bawaan dari CodeIgniter\Model
        return $this->update($id, $data);
    }

    /**
     * Mengambil semua sub-menu beserta nama menu utamanya.
     * Menggantikan join query di CI3.
     *
     * @return array Daftar sub-menu.
     */
    public function getSubmenu(): array
    {
        // Menggunakan Query Builder CI4
        return $this->db->table('user_sub_menu')
                        ->select('user_sub_menu.*, user_menu.menu')
                        ->join('user_menu', 'user_menu.id = user_sub_menu.menu_id', 'inner') // Asumsi INNER JOIN
                        ->get()
                        ->getResultArray();
    }
}
