<?php

// Fungsi is_loggedin() dan checked_access() telah dihapus dari helper ini.
// Logika is_loggedin() akan ditangani di app/Controllers/BaseController.php.
// Logika checked_access() akan ditangani di Controller (misalnya, AdminController)
// atau dengan Model khusus jika kompleks, karena memiliki dependensi database.

if (!function_exists('getWeekday')) {
    /**
     * Mengubah tanggal menjadi nama hari dalam bahasa Indonesia.
     *
     * @param string $date_input Tanggal dalam format yang dikenali oleh DateTime (misal: YYYY-MM-DD, MM/DD/YYYY).
     * @return string Nama hari dalam bahasa Indonesia atau string asli jika format tidak valid.
     */
    function getWeekday(string $date_input): string
    {
        if (empty(trim($date_input))) {
            return '-';
        }

        try {
            $date_obj = new DateTime($date_input);
            $numdate = (int)$date_obj->format('w'); // 0 for Sunday, 6 for Saturday

            $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            return $hari[$numdate];
        } catch (Exception $e) {
            // Log the error if date parsing fails, and return original input or a default
            log_message('error', 'Helper getWeekday: Failed to parse date "' . $date_input . '". Error: ' . $e->getMessage());
            return htmlspecialchars($date_input); // Mengembalikan input asli jika tidak bisa di-parse
        }
    }
}

if (!function_exists('dateConvert')) {
    /**
     * Mengkonversi tanggal dari format SQL (YYYY-MM-DD atau YYYY-MM-DD HH:MM:SS)
     * ke format Indonesia (DD Bulan YYYY).
     *
     * @param string $date_sql Tanggal dari database SQL.
     * @return string Tanggal dalam format Indonesia atau '-' jika input tidak valid.
     */
    function dateConvert(string $date_sql): string
    {
        if (empty(trim($date_sql)) || $date_sql == '0000-00-00' || $date_sql == '0000-00-00 00:00:00') {
            return '-';
        }

        try {
            $date_obj = new DateTime($date_sql);
            $bulan = [
                1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];

            $day = (int)$date_obj->format('d');
            $month_num = (int)$date_obj->format('m');
            $year = (int)$date_obj->format('Y');

            // Cek apakah tanggal valid secara kalender sebelum konversi
            if (checkdate($month_num, $day, $year)) {
                return $day . ' ' . $bulan[$month_num] . ' ' . $year;
            } else {
                log_message('warning', 'Helper dateConvert: Invalid date parts for "' . $date_sql . '".');
                return htmlspecialchars($date_sql);
            }
        } catch (Exception $e) {
            log_message('error', 'Helper dateConvert: Failed to parse date "' . $date_sql . '". Error: ' . $e->getMessage());
            return htmlspecialchars($date_sql); // Mengembalikan input asli jika tidak bisa di-parse
        }
    }
}

if (!function_exists('status_permohonan_text_badge')) {
    /**
     * Mengembalikan teks status dan kelas badge CSS untuk status permohonan.
     *
     * @param string $status_code Kode status permohonan (misal: '0', '1', '3').
     * @return array Array asosiatif dengan 'text' dan 'badge' class.
     */
    function status_permohonan_text_badge(string $status_code): array
    {
        $status_text = 'Tidak Diketahui';
        $status_badge = 'light';
        switch ($status_code) {
            case '0': $status_text = 'Baru Masuk'; $status_badge = 'dark'; break;
            case '5': $status_text = 'Diproses Admin'; $status_badge = 'info'; break;
            case '1': $status_text = 'Penunjukan Pemeriksa'; $status_badge = 'primary'; break;
            case '2': $status_text = 'LHP Direkam'; $status_badge = 'warning'; break;
            case '3': $status_text = 'Selesai (Disetujui)'; $status_badge = 'success'; break;
            case '4': $status_text = 'Selesai (Ditolak)'; $status_badge = 'danger'; break;
            case '6': $status_text = 'Ditolak Awal'; $status_badge = 'danger'; break; // Tambahan status 6
            default: $status_text = 'Status Tidak Dikenal (' . htmlspecialchars($status_code) . ')';
        }
        return ['text' => $status_text, 'badge' => $status_badge];
    }
}

if (!function_exists('status_pengajuan_kuota_text_badge')) {
    /**
     * Mengembalikan teks status dan kelas badge CSS untuk status pengajuan kuota.
     *
     * @param string $status_code Kode status pengajuan kuota (misal: 'pending', 'approved').
     * @return array Array asosiatif dengan 'text' dan 'badge' class.
     */
    function status_pengajuan_kuota_text_badge(string $status_code): array
    {
        $status_text = ucfirst($status_code ?? 'N/A');
        $status_badge = 'secondary';
        switch (strtolower($status_code ?? '')) {
            case 'pending': $status_badge = 'warning'; $status_text = 'Pending'; break;
            case 'approved': $status_badge = 'success'; $status_text = 'Disetujui'; break;
            case 'rejected': $status_badge = 'danger'; $status_text = 'Ditolak'; break;
            case 'diproses': $status_badge = 'info'; $status_text = 'Diproses'; break;
        }
        return ['text' => $status_text, 'badge' => $status_badge];
    }
}

if (!function_exists('status_kuota_barang_text_badge')) {
    /**
     * Mengembalikan teks status dan kelas badge CSS untuk status kuota barang.
     *
     * @param string $status_code Kode status kuota barang (misal: 'active', 'inactive').
     * @return array Array asosiatif dengan 'text' dan 'badge' class.
     */
    function status_kuota_barang_text_badge(string $status_code): array
    {
        $status_text = ucfirst($status_code ?? 'N/A');
        $status_badge = 'secondary';
        switch (strtolower($status_code ?? '')) {
            case 'active':
                $status_text = 'Aktif';
                $status_badge = 'success';
                break;
            case 'inactive':
                $status_text = 'Non-Aktif';
                $status_badge = 'warning';
                break;
            case 'habis':
                $status_text = 'Habis';
                $status_badge = 'danger';
                break;
            case 'pending_approval':
                $status_text = 'Menunggu Persetujuan';
                $status_badge = 'info';
                break;
        }
        return ['text' => $status_text, 'badge' => $status_badge];
    }
}
