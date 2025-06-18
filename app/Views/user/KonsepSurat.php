<?php
// This is a direct file download, so we set the headers here.
// In a more robust CI4 application, you might use the Response object.
header("Content-type: application/vnd.ms-word");
header("Content-Disposition: attachment; Filename=KonsepSuratPersetujuan.doc");

// Helper function for date conversion
if (!function_exists('dateConvert')) {
    function dateConvert($date_sql) {
        if (!is_string($date_sql) || empty(trim($date_sql)) || $date_sql == '0000-00-00') {
            return '-';
        }
        try {
            return (new DateTime($date_sql))->format('d F Y');
        } catch (Exception $e) {
            return esc($date_sql);
        }
    }
}
?>
<!DOCTYPE html>
<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns:m="http://schemas.microsoft.com/office/2004/12/omml" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
    <title>Konsep Surat Persetujuan</title>
    <!-- Word-specific XML and CSS -->
    <style>
        @page WordSection1 {
            size: 595.3pt 841.9pt;
            margin: 35.45pt 53.85pt 35.45pt 62.95pt;
        }
        div.WordSection1 {
            page: WordSection1;
        }
        p.MsoNormal, li.MsoNormal, div.MsoNormal {
            margin: 0;
            margin-bottom: .0001pt;
            font-size: 11.0pt;
            font-family: "Calibri", sans-serif;
        }
        /* More Word-specific styles can be added here if needed */
    </style>
</head>
<body lang="IN">
    <div class="WordSection1">
        <table class="MsoNormalTable" border="0" cellspacing="0" cellpadding="0" style="width:100%;">
            <tr>
                <td style="width:15%; padding:1.0pt 5.4pt 0cm 0cm;">
                    <p class="MsoNormal">Nomor</p>
                </td>
                <td style="width:2%; text-align:center; padding:1.0pt 5.4pt 0cm 0cm;">:</td>
                <td style="width:53%; padding:1.0pt 5.4pt 0cm 0cm;">
                    <p class="MsoNormal">[@NomorND]</p>
                </td>
                <td style="width:30%; padding:1.0pt 5.4pt 0cm 0cm;">
                    <p class="MsoNormal" align="right">[@TanggalND]</p>
                </td>
            </tr>
            <tr>
                <td><p class="MsoNormal">Sifat</p></td>
                <td><p class="MsoNormal" align="center">:</p></td>
                <td colspan="2"><p class="MsoNormal">Biasa</p></td>
            </tr>
            <tr>
                <td><p class="MsoNormal">Hal</p></td>
                <td><p class="MsoNormal" align="center">:</p></td>
                <td colspan="2"><p class="MsoNormal">Izin Pemasukan <i>Returnable Package</i></p></td>
            </tr>
        </table>

        <p class="MsoNormal">&nbsp;</p>

        <p class="MsoNormal">Yth. <?= esc($permohonan['NamaPers']) ?></p>
        <p class="MsoNormal"><?= esc($permohonan['alamat']) ?></p>
        
        <p class="MsoNormal">&nbsp;</p>

        <p class="MsoNormal" style="text-align:justify; text-indent:36.0pt;">
            Sehubungan dengan surat Saudara nomor: <?= esc($permohonan['nomorSurat']) ?> tanggal <?= esc(dateConvert($permohonan['TglSurat'])) ?> hal Permohonan Pemasukan Returnable Package, dengan ini diberitahukan sebagai berikut:
        </p>
        
        <p class="MsoNormal" style="margin-left:18.0pt; text-indent:-18.0pt; text-align:justify;">
            1.&nbsp;&nbsp;&nbsp;Bahwa permohonan Saudara untuk melakukan pemasukan <i>Returnable Package</i> dapat disetujui;
        </p>
        
        <p class="MsoNormal" style="margin-left:18.0pt; text-indent:-18.0pt; text-align:justify;">
            2.&nbsp;&nbsp;&nbsp;Persetujuan tersebut diberikan terhadap:
        </p>

        <table class="MsoNormalTable" border="0" cellspacing="0" cellpadding="0" style="margin-left:36.0pt; width:95%;">
            <tr>
                <td style="width:30%;"><p class="MsoNormal">a. Sarana Pengangkut</p></td>
                <td style="width:2%; text-align:center;">:</td>
                <td style="width:68%;"><p class="MsoNormal"><?= esc($permohonan['NamaKapal']) ?></p></td>
            </tr>
            <tr>
                <td><p class="MsoNormal">b. Nama Barang</p></td>
                <td style="text-align:center;">:</td>
                <td><p class="MsoNormal"><?= esc($permohonan['NamaBarang']) ?></p></td>
            </tr>
            <tr>
                <td><p class="MsoNormal">c. Jumlah Barang</p></td>
                <td style="text-align:center;">:</td>
                <td><p class="MsoNormal"><?= esc($permohonan['JumlahBarang']) ?> Unit</p></td>
            </tr>
            <tr>
                <td><p class="MsoNormal">d. Nomor SKEP Fasilitas</p></td>
                <td style="text-align:center;">:</td>
                <td><p class="MsoNormal"><?= esc($permohonan['NoSkep']) ?></p></td>
            </tr>
            <tr>
                <td><p class="MsoNormal">e. Tanggal Tiba</p></td>
                <td style="text-align:center;">:</td>
                <td><p class="MsoNormal"><?= esc(dateConvert($permohonan['TglKedatangan'])) ?></p></td>
            </tr>
        </table>
        
        <p class="MsoNormal" style="margin-left:18.0pt; text-indent:-18.0pt; text-align:justify;">
            3.&nbsp;&nbsp;&nbsp;Persetujuan tersebut diberikan dengan ketentuan:
        </p>
        
        <p class="MsoNormal" style="margin-left:36.0pt; text-indent:-18.0pt; text-align:justify;">
            a.&nbsp;&nbsp;&nbsp;&nbsp;Telah dilakukan pemeriksaan fisik barang oleh petugas bea dan cukai dengan hasil pemeriksaan yang tercantum dalam laporan hasil pemeriksaan dan kedapatan sesuai;
        </p>
        <p class="MsoNormal" style="margin-left:36.0pt; text-indent:-18.0pt; text-align:justify;">
            b.&nbsp;&nbsp;&nbsp;&nbsp;Perusahaan menyelenggarakan pembukuan sesuai standar akuntansi dan membuat kartu stock yang sewaktu-waktu dapat dilakukan pemeriksaan oleh petugas Kantor Pengawasan dan Pelayanan Bea dan Cukai Tipe Madya Pabean C Pangkalpinang;
        </p>
        <p class="MsoNormal" style="margin-left:36.0pt; text-indent:-18.0pt; text-align:justify;">
            c.&nbsp;&nbsp;&nbsp;&nbsp;Perusahaan bersedia menanggung risiko hukum atas segala akibat yang timbul jika melanggar ketentuan kepabeanan yang berlaku.
        </p>

        <p class="MsoNormal" style="text-indent:36.0pt; text-align:justify;">
            Demikian disampaikan untuk diketahui.
        </p>

        <p class="MsoNormal">&nbsp;</p>
        <p class="MsoNormal">&nbsp;</p>
        
        <table class="MsoNormalTable" border="0" cellspacing="0" cellpadding="0" style="width:100%;">
            <tr>
                <td style="width:60%;">&nbsp;</td>
                <td style="width:40%;">
                    <p class="MsoNormal">Kepala Kantor Pengawasan dan Pelayanan Bea dan Cukai Tipe Madya Pabean C Pangkalpinang</p>
                    <p class="MsoNormal">&nbsp;</p>
                    <p class="MsoNormal">&nbsp;</p>
                    <p class="MsoNormal">&nbsp;</p>
                    <p class="MsoNormal" style="color:#BFBFBF;">Ditandatangani secara elektronik</p>
                    <p class="MsoNormal">Yetty Yulianty</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
