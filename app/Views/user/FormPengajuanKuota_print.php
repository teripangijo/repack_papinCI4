<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Pengajuan Kuota - <?= isset($pengajuan['nomor_surat_pengajuan']) ? htmlspecialchars($pengajuan['nomor_surat_pengajuan']) : ('ID: ' . (isset($pengajuan['id']) ? htmlspecialchars($pengajuan['id']) : 'Detail')); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt; 
            margin: 20px; 
        }
        table {
            width: 100%;
            border-collapse: collapse; 
        }
        td, th {
            padding: 1px 2px; 
            vertical-align: top; 
        }
        p {
            margin-top: 0; 
            margin-bottom: 3px; 
            line-height: 1.3; 
        }
        hr.header-separator { /* Style khusus untuk HR di bawah kop */
            border: none;
            border-top: 1px solid black;
            margin-top: 5px;
            margin-bottom: 15px;
        }
        .header-table td.label-cell { 
            width: 10%; 
        }
        .header-table td.colon-cell { 
            width: 2%;
            text-align: center;
        }
        .header-table td.value-cell { 
            width: 53%;
        }
        .header-table td.date-cell { 
            width: 35%; 
            text-align: right;
            white-space: nowrap; 
        }
        .content-table td {
            padding-bottom: 3px;
        }
        .signature-block { 
            width: 35%; 
            float: right; 
            text-align: left; 
            margin-top: 40px; /* Beri jarak lebih untuk tanda tangan */
        }
        .signature-block img {
            margin-bottom: 5px;
            display: block;
        }
        .signature-block p {
            margin: 0;
            line-height: 1.4;
        }
        .clear { clear: both; }
        .text-indent-50 { text-indent: 50px; }
        .address-block p { margin-bottom: 2px; }

        @media print {
            body {
                margin: 0.5in;
                font-size: 10pt; 
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            .back-button-container { display: none !important; }
        }
        .detail-label { width: 35%; }
        .colon-separator { width: 2%; text-align: center; }
    </style>
</head>

<body onload="window.print()">
    <?php
    if (!function_exists('dateConvertFull')) {
        function dateConvertFull($date_sql) {
            if (!is_string($date_sql) || empty(trim($date_sql)) || $date_sql == '0000-00-00' || $date_sql == '0000-00-00 00:00:00') {
                return '-';
            }
            try {
                $date_obj = new DateTime($date_sql);
                if ($date_obj) {
                    $bulan = array(1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
                    $hari = array('Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu');
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

    <div class="back-button-container no-print">
        <button onclick="goBack()" style="padding: 8px 15px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">&laquo; Kembali</button>
    </div>

    <table>
        <tr>
            <td style="width: 25%; text-align: center; vertical-align: middle;">
                <?php if ($logo_perusahaan_file) : ?>
                    <img src="<?= base_url('uploads/profile_images/' . htmlspecialchars($logo_perusahaan_file)); ?>" alt="Logo Perusahaan" style="max-width: 100px; max-height: 100px; object-fit: contain;">
                <?php else: ?>
                    <div style="width:100px; height:100px; border:1px solid #ccc; display:flex; align-items:center; justify-content:center; margin:auto; font-size:10px;">No Logo</div>
                <?php endif; ?>
            </td>
            <td style="width: 50%; text-align: center; vertical-align: middle;">
                <h3 style="margin-bottom: 5px;"><?= isset($user_perusahaan['NamaPers']) ? strtoupper(htmlspecialchars($user_perusahaan['NamaPers'])) : 'NAMA PERUSAHAAN'; ?></h3>
                <h5 style="margin-top: 0; font-weight:normal;"><?= isset($user_perusahaan['alamat']) ? htmlspecialchars($user_perusahaan['alamat']) : 'Alamat Perusahaan'; ?></h5>
            </td>
            <td style="width: 25%; text-align: center;"></td>
        </tr>
    </table>
    <hr class="header-separator">

    <table class="header-table">
        <tr>
            <td class="label-cell">No</td>
            <td class="colon-cell">:</td>
            <td class="value-cell"><?= isset($pengajuan['nomor_surat_pengajuan']) ? htmlspecialchars($pengajuan['nomor_surat_pengajuan']) : '-'; ?></td>
            <td class="date-cell">Pangkalpinang, <?= isset($pengajuan['tanggal_surat_pengajuan']) ? dateConvertFull($pengajuan['tanggal_surat_pengajuan']) : dateConvertFull(date('Y-m-d')); ?></td>
        </tr>
        <tr>
            <td class="label-cell">Hal</td>
            <td class="colon-cell">:</td>
            <td class="value-cell"><?= isset($pengajuan['perihal_pengajuan']) ? htmlspecialchars($pengajuan['perihal_pengajuan']) : 'Pengajuan Kuota Returnable Package'; ?></td>
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
                    <strong><?= isset($pengajuan['nama_barang_kuota']) ? strtolower(htmlspecialchars($pengajuan['nama_barang_kuota'])) : 'barang'; ?></strong> 
                    sebanyak <strong><?= isset($pengajuan['requested_quota']) ? number_format($pengajuan['requested_quota'],0,',','.') : '0'; ?> unit</strong>.
                </p>
                <p class="text-indent-50" style="text-align: justify; line-height: 1.5;">
                    Adapun alasan pengajuan penambahan kuota ini adalah sebagai berikut:
                </p>
                <p style="margin-left: 50px; text-align: justify; line-height: 1.5; white-space: pre-wrap;"><?= isset($pengajuan['reason']) ? nl2br(htmlspecialchars($pengajuan['reason'])) : '-'; ?></p>
                <br>
                <p class="text-indent-50" style="text-align: justify; line-height: 1.5;">Demikian permohonan ini kami sampaikan, atas perhatian dan kerjasamanya kami ucapkan terima kasih.</p>
            </td></tr>
        </table>
    </div>
    
    <div class="signature-block">
        <p>Hormat Kami,</p>
        <?php if ($ttd_pic_file) : ?>
            <img src="<?= base_url('uploads/ttd/' . htmlspecialchars($ttd_pic_file)); ?>" alt="Tanda Tangan PIC" style="max-width: 120px; max-height: 60px; object-fit: contain;">
        <?php else : ?>
            <div style="height: 60px;">&nbsp;</div> 
        <?php endif; ?>
        <p style="font-weight: bold; text-decoration: underline; margin-bottom:2px;"><?= isset($user_perusahaan['pic']) ? strtoupper(htmlspecialchars($user_perusahaan['pic'])) : 'NAMA PIC'; ?></p>
        <p style="margin-bottom:2px;"><?= isset($user_perusahaan['jabatanPic']) ? htmlspecialchars($user_perusahaan['jabatanPic']) : 'Jabatan PIC'; ?></p>
        <p><?= isset($user_perusahaan['NamaPers']) ? htmlspecialchars($user_perusahaan['NamaPers']) : 'Nama Perusahaan'; ?></p>
    </div>
    <div class="clear"></div>

    <script>
        function goBack() {
            if (history.length > 1 && document.referrer.indexOf(window.location.host) !== -1) {
                history.back();
            } else {
                window.location.href = "<?= site_url('user/daftar_pengajuan_kuota'); ?>"; 
            }
        }
    </script>
</body>
</html>
