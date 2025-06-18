<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('dateConvertFullIndonesia')) {
    function dateConvertFullIndonesia($date_sql) {
        if (!is_string($date_sql) || empty(trim($date_sql)) || $date_sql == '0000-00-00' || $date_sql == '0000-00-00 00:00:00') {
            return '-';
        }
        try {
            $date_obj = new DateTime($date_sql);
            if ($date_obj) {
                $bulan = array(1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
                return (int)$date_obj->format('d') . ' ' . $bulan[(int)$date_obj->format('m')] . ' ' . $date_obj->format('Y');
            }
            return htmlspecialchars($date_sql);
        } catch (Exception $e) {
            return htmlspecialchars($date_sql);
        }
    }
}

$logo_perusahaan_file = (isset($user['image']) && $user['image'] != 'default.jpg' && !empty($user['image'])) ? $user['image'] : null;
$ttd_pic_file = (isset($user_perusahaan['ttd']) && !empty($user_perusahaan['ttd'])) ? $user_perusahaan['ttd'] : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Permohonan Impor Kembali - <?= isset($permohonan['nomorSurat']) ? htmlspecialchars($permohonan['nomorSurat']) : ('ID: ' . (isset($permohonan['id']) ? htmlspecialchars($permohonan['id']) : 'Detail')); ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; margin: 20px; line-height: 1.4; }
        table { width: 100%; border-collapse: collapse; }
        /* Default td, th styling */
        td, th { padding: 1px 2px; vertical-align: top; } /* Default vertical-align top */

        p { margin-top: 0; margin-bottom: 5px; line-height: 1.4; }
        hr.header-separator { border: none; border-top: 1.5px solid black; margin-top: 5px; margin-bottom: 20px; }

        .header-table td { padding-bottom: 2px; }
        .header-table td.label-cell { width: 12%; white-space: nowrap;}
        .header-table td.colon-cell { width: 2%; text-align: center;}
        .header-table td.value-cell { width: 51%;}
        .header-table td.date-cell { width: 35%; text-align: left; padding-left: 20px; white-space: nowrap;}

        .content-section-title { font-weight: bold; margin-top: 15px; margin-bottom: 8px; }
        .detail-table {
            width: 100%;
            margin-bottom: 15px;
        }
        .detail-table td {
            padding-top: 2px; /* Sedikit padding atas-bawah untuk spasi */
            padding-bottom: 2px;
            vertical-align: top; /* PENTING: Konten sel rata atas */
        }
        .detail-table td.label {
            width: 38%; /* Coba perlebar kolom label */
            padding-right: 5px; /* Jarak antara teks label dan titik dua */
            font-weight: normal;
            white-space: normal; /* Biarkan teks label bisa wrap jika sangat panjang */
        }
        .detail-table td.jadwal-sublabel { /* Untuk sub-item di jadwal */
            width: 38%; /* Samakan dengan label atau sesuaikan */
            padding-left: 20px; /* Indentasi */
            padding-right: 5px;
            font-weight: normal;
            white-space: normal;
        }
        .detail-table td.separator {
            width: 2%; /* Lebar minimal untuk titik dua */
            text-align: left; /* Titik dua rata kiri, menempel pada label */
            padding-left: 0;
            padding-right: 8px; /* Jarak antara titik dua dan data */
        }
        .detail-table td.data {
            width: 60%; /* Sisa lebar, biarkan browser menyesuaikan sedikit jika perlu */
            word-break: break-word; /* Agar data panjang bisa pindah baris */
        }

        .signature-block { width: 40%; float: right; text-align: center; margin-top: 30px; }
        .signature-block img { margin-top:10px; margin-bottom: 10px; display: block; margin-left:auto; margin-right:auto;}
        .signature-block p { margin: 0; line-height: 1.4; }
        .clear { clear: both; height:1px; }
        .text-indent-50 { text-indent: 50px; text-align: justify;}
        .address-block p { margin-bottom: 1px; }

        .no-print { display: block; margin-bottom: 15px; }
        .back-button, .print-button { /* ... style tombol ... */ }

        @media print {
            body { margin: 0.6in 0.75in 0.5in 0.75in; font-size: 11pt; -webkit-print-color-adjust: exact !important; color-adjust: exact !important; }
            .no-print { display: none !important; }
            p, table, div { page-break-inside: auto; }
            tr, td { page-break-inside: avoid; } /* Ini bisa menyebabkan halaman kosong jika konten sel sangat panjang */
        }
    </style>
</head>

<body onload="window.print();">

    <div class="no-print">
        <button onclick="goBack()" class="back-button">&laquo; Kembali</button>
        <button onclick="window.print()" class="print-button"><i class="fas fa-print"></i> Cetak Ulang</button>
    </div>

    <table>
        <tr>
            <td style="width: 20%; text-align: center; vertical-align: top;">
                <?php if ($logo_perusahaan_file) : ?>
                    <img src="<?= base_url('uploads/profile_images/' . htmlspecialchars($logo_perusahaan_file)); ?>" alt="Logo Perusahaan" style="max-width: 90px; max-height: 90px; object-fit: contain;">
                <?php else: ?>
                    <div style="width:90px; height:90px; border:1px solid #eee; display:flex; align-items:center; justify-content:center; margin:auto; font-size:10px; color: #ccc;">LOGO</div>
                <?php endif; ?>
            </td>
            <td style="width: 80%; text-align: center; vertical-align: top;">
                <h3 style="margin:0; font-size: 14pt;"><?= isset($user_perusahaan['NamaPers']) ? strtoupper(htmlspecialchars($user_perusahaan['NamaPers'])) : 'NAMA PERUSAHAAN'; ?></h3>
                <p style="margin:2px 0; font-size: 10pt;"><?= isset($user_perusahaan['alamat']) ? htmlspecialchars($user_perusahaan['alamat']) : 'Alamat Perusahaan'; ?></p>
                <?php if (isset($user_perusahaan['telp']) && !empty($user_perusahaan['telp'])): ?>
                    <p style="margin:2px 0; font-size: 10pt;">Telp: <?= htmlspecialchars($user_perusahaan['telp']); ?>
                    <?php if (isset($user['email']) && !empty($user['email'])): ?>
                        | Email: <?= htmlspecialchars($user['email']); ?>
                    <?php endif; ?>
                    </p>
                <?php elseif (isset($user['email']) && !empty($user['email'])): ?>
                     <p style="margin:2px 0; font-size: 10pt;">Email: <?= htmlspecialchars($user['email']); ?></p>
                <?php endif; ?>
            </td>
        </tr>
    </table>
    <hr class="header-separator">

    <table class="header-table">
         <tr>
            <td class="label-cell">Nomor</td><td class="colon-cell">:</td><td class="value-cell"><?= isset($permohonan['nomorSurat']) ? htmlspecialchars($permohonan['nomorSurat']) : '-'; ?></td>
            <td class="date-cell">Pangkalpinang, <?= isset($permohonan['TglSurat']) ? dateConvertFullIndonesia($permohonan['TglSurat']) : dateConvertFullIndonesia(date('Y-m-d')); ?></td>
        </tr>
        <tr><td class="label-cell">Lampiran</td><td class="colon-cell">:</td><td class="value-cell">-</td><td></td></tr>
        <tr><td class="label-cell">Perihal</td><td class="colon-cell">:</td><td class="value-cell"><b><?= isset($permohonan['Perihal']) ? htmlspecialchars($permohonan['Perihal']) : 'Permohonan Impor Kembali Returnable Package'; ?></b></td><td></td></tr>
    </table>

    <div style="margin-top: 20px; margin-bottom: 15px;" class="address-block">
        <p>Kepada Yth.</p>
        <p>Kepala Kantor Pengawasan dan Pelayanan Bea dan Cukai</p>
        <p>Tipe Madya Pabean C Pangkalpinang</p>
        <p>Di Tempat</p>
    </div>

    <p>Dengan hormat,</p>
    <p class="text-indent-50">
        Sehubungan dengan kegiatan impor kembali kemasan yang digunakan berulang (Returnable Package) yang sebelumnya telah diekspor oleh perusahaan kami, dengan ini PT. <?= htmlspecialchars($user_perusahaan['NamaPers'] ?? '[Nama Perusahaan]'); ?> mengajukan permohonan untuk melakukan impor kembali atas returnable package tersebut, dengan rincian sebagai berikut:
    </p>

    <div class="content-section-title">A. DATA BARANG IMPOR KEMBALI</div>
    <table class="detail-table">
        <tr><td class="label">1. Nama/Jenis Barang</td><td class="separator">:</td><td class="data"><b><?= htmlspecialchars($permohonan['NamaBarang'] ?? '-'); ?></b></td></tr>
        <tr><td class="label">2. Jumlah</td><td class="separator">:</td><td class="data"><?= htmlspecialchars(number_format($permohonan['JumlahBarang'] ?? 0, 0, ',', '.')); ?> Unit</td></tr>
        <tr><td class="label">3. Negara Asal</td><td class="separator">:</td><td class="data"><?= htmlspecialchars($permohonan['NegaraAsal'] ?? '-'); ?></td></tr>
        <tr><td class="label">4. No. SKEP Dasar</td><td class="separator">:</td><td class="data"><?= htmlspecialchars($permohonan['NoSkep'] ?? '-'); ?></td></tr>
    </table>

    <div class="content-section-title">B. DATA PENGANGKUTAN</div>
     <table class="detail-table">
        <tr><td class="label">1. Nama Kapal/Sarana Pengangkut</td><td class="separator">:</td><td class="data"><?= htmlspecialchars($permohonan['NamaKapal'] ?? '-'); ?></td></tr>
        <tr><td class="label">2. Nomor Voyage/Flight</td><td class="separator">:</td><td class="data"><?= htmlspecialchars($permohonan['noVoyage'] ?? '-'); ?></td></tr>
        <tr><td class="label">3. Tanggal Perkiraan Kedatangan</td><td class="separator">:</td><td class="data"><?= isset($permohonan['TglKedatangan']) ? dateConvertFullIndonesia($permohonan['TglKedatangan']) : '-'; ?></td></tr>
        <tr><td class="label">4. Tanggal Perkiraan Bongkar</td><td class="separator">:</td><td class="data"><?= isset($permohonan['TglBongkar']) ? dateConvertFullIndonesia($permohonan['TglBongkar']) : '-'; ?></td></tr>
        <tr><td class="label">5. Lokasi Bongkar</td><td class="separator">:</td><td class="data"><?= htmlspecialchars($permohonan['lokasi'] ?? '-'); ?></td></tr>
    </table>
    <p class="text-indent-50" style="margin-top: 15px;">
        Demikian permohonan ini kami sampaikan. Atas perhatian dan kerjasamanya diucapkan terima kasih.
    </p>

    <div class="signature-block">
        <p>Hormat Kami,</p>
        <p>PT. <?= strtoupper(htmlspecialchars($user_perusahaan['NamaPers'] ?? 'NAMA PERUSAHAAN')); ?></p>
        <?php if ($ttd_pic_file) : ?>
            <img src="<?= base_url('uploads/ttd/' . htmlspecialchars($ttd_pic_file)); ?>" alt="Tanda Tangan PIC" style="max-height: 50px; margin-top:10px; margin-bottom:10px;">
        <?php else : ?>
            <div style="height: 70px;">&nbsp;</div>
        <?php endif; ?>
        <p style="font-weight: bold; text-decoration: underline; margin-bottom:0;"><?= strtoupper(htmlspecialchars($user_perusahaan['pic'] ?? 'NAMA PIC')); ?></p>
        <p style="margin-top:0;"><?= htmlspecialchars($user_perusahaan['jabatanPic'] ?? 'Jabatan PIC'); ?></p>
    </div>
    <div class="clear"></div>

    <script>
        function goBack() {
            if (document.referrer && document.referrer.indexOf(window.location.host) !== -1 && history.length > 1) {
                history.back();
            } else {
                window.location.href = "<?= site_url('user/daftarPermohonan'); ?>"; // Pastikan link ini benar
            }
        }
    </script>
</body>
</html>