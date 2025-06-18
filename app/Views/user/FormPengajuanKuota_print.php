<?php
if (!function_exists('dateConvertFull')) {
    function dateConvertFull($date_sql) {
        if (!is_string($date_sql) || empty(trim($date_sql)) || $date_sql == '0000-00-00' || $date_sql == '0000-00-00 00:00:00') {
            return '-';
        }
        try {
            $date_obj = new DateTime($date_sql);
            if ($date_obj) {
                $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                return (int)$date_obj->format('d') . ' ' . $bulan[(int)$date_obj->format('m')] . ' ' . $date_obj->format('Y');
            }
            return esc($date_sql);
        } catch (Exception $e) {
            return esc($date_sql);
        }
    }
}

$logo_perusahaan_file = ($user['image'] ?? 'default.jpg') != 'default.jpg' ? $user['image'] : null;
$ttd_pic_file = $user_perusahaan['ttd'] ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Pengajuan Kuota - <?= esc($pengajuan['nomor_surat_pengajuan'] ?? ('ID: ' . ($pengajuan['id'] ?? 'Detail'))) ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; margin: 20px; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 1px 2px; vertical-align: top; }
        p { margin-top: 0; margin-bottom: 3px; line-height: 1.3; }
        hr.header-separator { border: none; border-top: 1px solid black; margin-top: 5px; margin-bottom: 15px; }
        .header-table .label-cell { width: 10%; }
        .header-table .colon-cell { width: 2%; text-align: center; }
        .header-table .value-cell { width: 53%; }
        .header-table .date-cell { width: 35%; text-align: right; white-space: nowrap; }
        .signature-block { width: 35%; float: right; text-align: left; margin-top: 40px; }
        .signature-block img { margin-bottom: 5px; display: block; }
        .signature-block p { margin: 0; line-height: 1.4; }
        .clear { clear: both; }
        .text-indent-50 { text-indent: 50px; }
        .address-block p { margin-bottom: 2px; }
        .no-print { display: block; margin-bottom: 15px; }
        @media print {
            body { margin: 0.5in; font-size: 10pt; -webkit-print-color-adjust: exact; color-adjust: exact; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print">
        <button onclick="goBack()" style="padding: 8px 15px; background-color: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer;">&laquo; Kembali</button>
    </div>

    <table>
        <tr>
            <td style="width: 25%; text-align: center; vertical-align: middle;">
                <?php if ($logo_perusahaan_file) : ?>
                    <img src="<?= base_url('uploads/profile_images/' . esc($logo_perusahaan_file, 'url')) ?>" alt="Logo Perusahaan" style="max-width: 100px; max-height: 100px; object-fit: contain;">
                <?php else: ?>
                    <div style="width:100px; height:100px; border:1px solid #ccc; display:flex; align-items:center; justify-content:center; margin:auto; font-size:10px;">No Logo</div>
                <?php endif; ?>
            </td>
            <td style="width: 50%; text-align: center; vertical-align: middle;">
                <h3 style="margin-bottom: 5px;"><?= esc(strtoupper($user_perusahaan['NamaPers'] ?? 'NAMA PERUSAHAAN')) ?></h3>
                <h5 style="margin-top: 0; font-weight:normal;"><?= esc($user_perusahaan['alamat'] ?? 'Alamat Perusahaan') ?></h5>
            </td>
            <td style="width: 25%; text-align: center;"></td>
        </tr>
    </table>
    <hr class="header-separator">

    <table class="header-table">
        <tr>
            <td class="label-cell">No</td>
            <td class="colon-cell">:</td>
            <td class="value-cell"><?= esc($pengajuan['nomor_surat_pengajuan'] ?? '-') ?></td>
            <td class="date-cell">Pangkalpinang, <?= esc(dateConvertFull($pengajuan['tanggal_surat_pengajuan'] ?? date('Y-m-d'))) ?></td>
        </tr>
        <tr>
            <td class="label-cell">Hal</td>
            <td class="colon-cell">:</td>
            <td class="value-cell"><?= esc($pengajuan['perihal_pengajuan'] ?? 'Pengajuan Kuota Returnable Package') ?></td>
            <td></td>
        </tr>
    </table>

    <div style="margin-top: 25px;">
        <table>
            <tr><td class="address-block">
                <p>Kepada Yth.</p>
                <p>Kepala Kantor Pengawasan dan Pelayanan Bea dan Cukai</p>
                <p>Tipe Madya Pabean C Pangkalpinang</p>
                <p>Di Tempat</p>
            </td></tr>
        </table>
    </div>

    <div style="margin-top: 25px;">
        <table>
            <tr><td>
                <p>Dengan hormat,</p>
                <p class="text-indent-50" style="text-align: justify; line-height: 1.5;">
                    Bersama ini kami mengajukan permohonan penambahan kuota untuk impor kembali kemasan returnable package jenis 
                    <strong><?= esc(strtolower($pengajuan['nama_barang_kuota'] ?? 'barang')) ?></strong> 
                    sebanyak <strong><?= esc(number_format($pengajuan['requested_quota'] ?? 0, 0, ',', '.')) ?> unit</strong>.
                </p>
                <p class="text-indent-50" style="text-align: justify; line-height: 1.5;">
                    Adapun alasan pengajuan penambahan kuota ini adalah sebagai berikut:
                </p>
                <p style="margin-left: 50px; text-align: justify; line-height: 1.5; white-space: pre-wrap;"><?= nl2br(esc($pengajuan['reason'] ?? '-')) ?></p>
                <br>
                <p class="text-indent-50" style="text-align: justify; line-height: 1.5;">Demikian permohonan ini kami sampaikan, atas perhatian dan kerjasamanya kami ucapkan terima kasih.</p>
            </td></tr>
        </table>
    </div>
    
    <div class="signature-block">
        <p>Hormat Kami,</p>
        <?php if ($ttd_pic_file) : ?>
            <img src="<?= base_url('uploads/ttd/' . esc($ttd_pic_file, 'url')) ?>" alt="Tanda Tangan PIC" style="max-width: 120px; max-height: 60px; object-fit: contain;">
        <?php else : ?>
            <div style="height: 60px;">&nbsp;</div> 
        <?php endif; ?>
        <p style="font-weight: bold; text-decoration: underline; margin-bottom:2px;"><?= esc(strtoupper($user_perusahaan['pic'] ?? 'NAMA PIC')) ?></p>
        <p style="margin-bottom:2px;"><?= esc($user_perusahaan['jabatanPic'] ?? 'Jabatan PIC') ?></p>
        <p><?= esc($user_perusahaan['NamaPers'] ?? 'Nama Perusahaan') ?></p>
    </div>
    <div class="clear"></div>

    <script>
        function goBack() {
            if (history.length > 1 && document.referrer.indexOf(window.location.hostname) !== -1) {
                history.back();
            } else {
                window.location.href = "<?= site_url('user/daftar_pengajuan_kuota') ?>"; 
            }
        }
    </script>
</body>
</html>
