<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        h1 {
            text-align: center;
            font-size: 20px;
        }

        .table2 {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
            font-size: 15px;
            border: 1px solid black;
            border-collapse: collapse;
        }

        .td {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            font-size: 15px;
            border: 1px solid black;
            border-collapse: collapse;
        }

        .table {
            font-family: arial, sans-serif;
            font-size: 15px;
        }

        .table3 {
            font-family: arial, sans-serif;
            font-size: 15px;
            width: 100%;
        }

        tr,
        td {
            text-align: left;
            padding: 4px;
        }

        .tdclose {
            text-align: left;
            padding: 1px;
        }
    </style>
</head>

<body>
    <h1><u>LAPORAN HASIL PEMERIKSAAN</u></h1>
    <br>
    <table class="table">
        <tr>
            <td>HARI/TANGGAL/WAKTU</td>
            <td>:</td>
            <td><?= getWeekday($lhp['TglPeriksa']); ?>, <?= dateConvert($lhp['TglPeriksa']); ?> / <?= $lhp['wkmulai']; ?> - <?= $lhp['wkselesai']; ?> WIB</td>
        </tr>
        <tr>
            <td>LOKASI</td>
            <td>:</td>
            <td><?= $permohonan['lokasi']; ?></td>
        </tr>
        <tr>
            <td>SARANA PENGANGKUT</td>
            <td>:</td>
            <td><?= $permohonan['NamaKapal']; ?></td>
        </tr>
        <tr>
            <td>NAMA PERUSAHAAN</td>
            <td>:</td>
            <td><?= $permohonan['NamaPers']; ?></td>
        </tr>
        <tr>
            <td>NOMOR SURAT PERMOHONAN</td>
            <td>:</td>
            <td><?= $permohonan['nomorSurat']; ?></td>
        </tr>
    </table>

    <table class="table2">
        <tr class="table2">
            <td class="td">NO</td>
            <td class="td">JENIS KEMASAN</td>
            <td class="td">JUMLAH KEMASAN</td>
            <td class="td">KONDISI</td>
            <td class="td">NEGARA ASAL</td>
        </tr>
        <tr class="table2">
            <td class="td">1<br></td>
            <td class="td"><?= $permohonan['NamaBarang']; ?><br></td>
            <td class="td"><?= $lhp['JumlahBenar']; ?><br></td>
            <td class="td"><?= $lhp['Kondisi']; ?><br></td>
            <td class="td"><?= $permohonan['NegaraAsal']; ?><br></td>
        </tr>
        <tr class="table2">
            <td class="td" colspan="5">KESIMPULAN :<br> <?= $lhp['Kesimpulan']; ?> </td>
        </tr>
    </table>

    <table class="table3">
        <tr class="table3">
            <td>PEMILIK BARANG</td>
            <td>PEMERIKSA</td>
        </tr>
        <tr class="table3">
            <td>Ttd.<br></td>
            <td>Ttd.<br></td>
        </tr>
        <tr class="table3">
            <td><?= $lhp['pemilik']; ?></td>
            <td><?= $petugas['Nama']; ?> </td>
        </tr>
        <tr class="table3">
            <td class="tdclose"></td>
            <td class="tdclose"><?= $petugas['NIP']; ?> </td>
        </tr>
    </table>
</body>

</html>