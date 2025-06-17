<?php

namespace App\Models; // Gunakan namespace App\Models

use CodeIgniter\Model; // Import kelas Model dasar CI4

class Monitoring_model extends Model
{
    // Nama tabel database utama yang terkait dengan model ini.
    // Jika model ini untuk monitoring secara umum, mungkin tidak terikat langsung ke satu tabel.
    // Namun, jika ada tabel "monitoring_data" atau sejenisnya, ini bisa diset.
    // Untuk tujuan ini, kita akan asumsikan ini adalah model untuk data monitoring secara luas,
    // sehingga $table mungkin tidak selalu digunakan secara langsung dalam setiap metode.
    // Jika Monitoring_model digunakan untuk mengakses data dari tabel 'user_menu' dan 'user_sub_menu'
    // itu tidak disarankan. Sebaiknya gunakan Menu_model untuk data menu.
    // Saya akan mengoreksi getPerusahaan() untuk mengambil data dari tabel 'user_perusahaan'.
    protected $table = 'monitoring_log'; // Contoh: jika ada tabel log monitoring
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = []; // Sesuaikan jika ada kolom yang diizinkan untuk diisi
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;

    /**
     * Catatan: Metode deleteMenu() dan updateMenu() seharusnya tidak berada di Monitoring_model.
     * Mereka seharusnya berada di Menu_model. Saya menyimpannya di sini hanya untuk menunjukkan
     * adaptasi jika Anda berkeras mempertahankannya (tidak disarankan).
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
     * Memperbarui sebuah menu berdasarkan ID-nya. (Tidak disarankan berada di model ini)
     *
     * @param int $id ID menu yang akan diperbarui.
     * @param array $data Data yang akan diperbarui.
     * @return bool True jika berhasil diperbarui, false jika gagal.
     */
    public function updateMenu(int $id, array $data): bool
    {
        // Akses tabel user_menu
        return $this->db->table('user_menu')->update($data, ['id' => $id]);
    }

    /**
     * Mengambil daftar perusahaan.
     * Query asli di CI3 model ini tampaknya salah (mengambil user_sub_menu dan user_menu).
     * Ini dikoreksi untuk mengambil data dari tabel user_perusahaan dan user.
     *
     * @return array Daftar perusahaan.
     */
    public function getPerusahaan(): array
    {
        // Menggunakan Query Builder CI4 untuk mengambil data perusahaan
        // Asumsi ingin mengambil NamaPerusahaan, email user terkait, dan ID.
        return $this->db->table('user_perusahaan up')
                        ->select('up.id_pers, up.NamaPers, up.npwp, up.alamat, u.email as user_email, u.name as user_name')
                        ->join('user u', 'u.id = up.id_pers', 'left') // Menggabungkan dengan tabel user
                        ->orderBy('up.NamaPers', 'ASC')
                        ->get()
                        ->getResultArray();
    }
}
