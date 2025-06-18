<?php
// Set headers for Word document download
header("Content-type: application/vnd.ms-word");
header("Content-Disposition: attachment; Filename=KonsepST.doc");

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
<html xmlns:w="urn:schemas-microsoft-com:office:word">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
    <title>Konsep Surat Tugas</title>
    <style>
        @page WordSection1 {
            size: 595.3pt 841.9pt;
            margin: 35.45pt 53.85pt 35.45pt 62.95pt;
        }
        div.WordSection1 {
            page: WordSection1;
        }
        p, li, div {
            font-size: 11.0pt;
            font-family: "Calibri", sans-serif;
        }
        p.centered { text-align: center; }
        p.justified { text-align: justify; }
        p.indented { text-indent: 36.0pt; }
        table { border-collapse: collapse; }
        td { vertical-align: top; padding: 0cm 5.4pt 0cm 5.4pt; }
    </style>
</head>
<body>
    <div class="WordSection1">
        <p class="centered">SURAT TUGAS</p>
        <p class="centered">NOMOR [@NomorND]</p>
        
        <p>&nbsp;</p>

        <p class="justified indented">
            Sehubungan dengan surat <?= esc($permohonan['NamaPers']) ?> nomor <?= esc($permohonan['nomorSurat']) ?> tanggal <?= esc(dateConvert($permohonan['TglSurat'])) ?> hal Permohonan Pemasukan <i>Returnable Package</i> dan dalam rangka melaksanakan kegiatan pelayanan dan pengawasan pemasukan <i>Returnable Package</i>, kami menugasi:
        </p>

        <p>&nbsp;</p>

        <table style="margin-left:19.6pt; width: 95%;">
            <tr>
                <td style="width:25%;">Nama / NIP</td>
                <td style="width:2%;">:</td>
                <td style="width:73%;"><?= esc($petugas['Nama']) ?> / <?= esc($petugas['NIP']) ?></td>
            </tr>
            <tr>
                <td>Pangkat / Gol</td>
                <td>:</td>
                <td><?= esc($petugas['Golongan']) ?></td>
            </tr>
            <tr>
                <td>Jabatan</td>
                <td>:</td>
                <td><?= esc($petugas['Jabatan']) ?></td>
            </tr>
        </table>
        
        <p>&nbsp;</p>

        <p class="justified">
            untuk melaksanakan kegiatan pemeriksaan <i>Returnable Package</i> dengan data:
        </p>

        <p>&nbsp;</p>

        <table style="margin-left:19.6pt; width: 95%;">
            <tr>
                <td style="width:30%;">Nama/ Jenis Barang</td>
                <td style="width:2%;">:</td>
                <td style="width:68%;"><?= esc($permohonan['NamaBarang']) ?></td>
            </tr>
            <tr>
                <td>Negara Asal</td>
                <td>:</td>
                <td><?= esc($permohonan['NegaraAsal']) ?></td>
            </tr>
            <tr>
                <td>Nomor SKEP Fasilitas</td>
                <td>:</td>
                <td><?= esc($permohonan['NoSkep']) ?></td>
            </tr>
             <tr>
                <td>Nama Perusahaan</td>
                <td>:</td>
                <td><?= esc($permohonan['NamaPers']) ?></td>
            </tr>
             <tr>
                <td>Jadwal Kedatangan</td>
                <td>:</td>
                <td><?= esc(dateConvert($permohonan['TglKedatangan'])) ?></td>
            </tr>
             <tr>
                <td>Nama Kapal</td>
                <td>:</td>
                <td><?= esc($permohonan['NamaKapal']) ?></td>
            </tr>
        </table>

        <p>&nbsp;</p>

        <p class="justified indented">
            Pelaksanaan tugas tersebut dilaksanakan pada tanggal <?= esc(dateConvert($permohonan['TglKedatangan'])) ?>.
        </p>

        <p class="justified indented">
            Surat tugas ini disusun untuk dilaksanakan dan setelah selesai dilaksanakan, pelaksana segera menyampaikan laporan. Kepada instansi terkait kami mohon bantuan demi kelancaran pelaksanaan tugas tersebut.
        </p>

        <p>&nbsp;</p>
        <p>&nbsp;</p>

        <table style="width:100%;">
            <tr>
                <td style="width:60%;">&nbsp;</td>
                <td style="width:40%;">
                    <p>Pangkalpinang, [@TanggalND]</p>
                    <p>Kepala Kantor Pengawasan dan Pelayanan Bea dan Cukai Tipe Madya Pabean C Pangkalpinang</p>
                    <p>&nbsp;</p>
                    <p>&nbsp;</p>
                    <p>&nbsp;</p>
                    <p style="color:#BFBFBF;">Ditandatangani secara elektronik</p>
                    <p>Yetty Yulianty</p>
                </td>
            </tr>
        </table>

        <p>&nbsp;</p>

        <p>Tembusan:</p>
        <p>1. Kepala Subbagian Umum</p>
        <p>2. Kepala Seksi Kepatuhan Internal dan Penyuluhan</p>

    </div>
</body>
</html>
