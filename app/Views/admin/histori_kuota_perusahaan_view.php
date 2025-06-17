<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
    <?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Histori Kuota Perusahaan'; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($subtitle ?? 'Histori & Detail Kuota Perusahaan'); ?></h1>
        <a href="<?= site_url('admin/monitoring_kuota'); ?>" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Monitoring Kuota
        </a>
    </div>

    <?php if (session()->getFlashdata('message')) { echo session()->getFlashdata('message'); } ?>

    <?php if (isset($perusahaan) && !empty($perusahaan)): ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Detail Kuota untuk: <?= htmlspecialchars($perusahaan['NamaPers']); ?>
                (NPWP: <?= htmlspecialchars($perusahaan['npwp'] ?? 'N/A'); ?>)
                <br><small>Kontak User: <?= htmlspecialchars($perusahaan['nama_kontak_user'] ?? ($perusahaan['email_kontak'] ?? 'N/A')); ?></small>
            </h6>
        </div>
        <div class="card-body">
            <h5 class="text-gray-800">Ringkasan Total Kuota (Agregat dari Semua Jenis Barang)</h5>
            <div class="row mb-3">
                <div class="col-md-4">
                    <strong>Total Kuota Awal Diberikan:</strong> <span id="totalInitialAgregat">Memuat...</span> Unit
                </div>
                <div class="col-md-4">
                    <strong>Total Sisa Kuota Saat Ini:</strong>
                    <span id="totalRemainingAgregat" class="font-weight-bold">Memuat...</span> Unit
                </div>
                <div class="col-md-4">
                    <strong>Total Kuota Terpakai:</strong> <span id="totalTerpakaiAgregat">Memuat...</span> Unit
                </div>
            </div>
            <hr>

            <h5 class="text-gray-800 mt-4">Rincian Kuota per Jenis Barang</h5>
            <div class="table-responsive mb-4">
                <table class="table table-sm table-bordered table-hover" id="dataTableRincianKuotaBarang" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Barang</th>
                            <th class="text-right">Kuota Awal Barang</th>
                            <th class="text-right">Sisa Kuota Barang</th>
                            <th>No. SKEP Asal</th>
                            <th>Tgl. SKEP Asal</th>
                            <th>Status Kuota Barang</th>
                            <th>Waktu Pencatatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="8" class="text-center">Memuat data rincian kuota... <i class="fas fa-spinner fa-spin"></i></td></tr>
                    </tbody>
                </table>
            </div>
            <hr>

            <h5 class="text-gray-800 mt-4">Log Transaksi Kuota</h5>
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover" id="dataTableHistoriTransaksiKuota" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tgl Transaksi</th>
                            <th>Jenis Transaksi</th>
                            <th>Nama Barang Terkait</th>
                            <th class="text-right">Jumlah Perubahan</th>
                            <th class="text-right">Sisa Kuota Barang Sblm.</th>
                            <th class="text-right">Sisa Kuota Barang Stlh.</th>
                            <th>Keterangan</th>
                            <th>Ref. Tipe & ID</th>
                            <th>Dicatat Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="10" class="text-center">Memuat data log transaksi... <i class="fas fa-spinner fa-spin"></i></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php else: ?>
        <div class="alert alert-warning" role="alert"> Data perusahaan tidak ditemukan atau tidak dapat diakses. </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    var idPers = <?= isset($id_pers_untuk_histori) ? intval($id_pers_untuk_histori) : 0; ?>;
    var rincianKuotaTable;
    var historiTransaksiTable;

    console.log('Starting with ID Pers:', idPers);

    function formatTanggal(tanggalString) {
        if (!tanggalString || tanggalString === '0000-00-00') return '-';
        var date = new Date(tanggalString);
        var day = date.getDate();
        var monthNames = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Ags", "Sep", "Okt", "Nov", "Des"];
        var month = monthNames[date.getMonth()];
        var year = date.getFullYear();
        return day + ' ' + month + ' ' + year;
    }

    function formatTanggalWaktu(tanggalWaktuString) {
        if (!tanggalWaktuString) return '-';
        var date = new Date(tanggalWaktuString);
        var day = ('0' + date.getDate()).slice(-2);
        var month = ('0' + (date.getMonth() + 1)).slice(-2);
        var year = date.getFullYear();
        var hours = ('0' + date.getHours()).slice(-2);
        var minutes = ('0' + date.getMinutes()).slice(-2);
        return day + '/' + month + '/' + year + ' ' + hours + ':' + minutes;
    }

    function numberFormat(number, decimals, decPoint, thousandsSep) {
        number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
        var n = !isFinite(+number) ? 0 : +number;
        var prec = !isFinite(+decimals) ? 0 : Math.abs(decimals);
        var sep = (typeof thousandsSep === 'undefined') ? ',' : thousandsSep;
        var dec = (typeof decPoint === 'undefined') ? '.' : decPoint;
        var s = '';
        var toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + (Math.round(n * k) / k).toFixed(prec);
        };
        s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || '').length < prec) {
            s[1] = s[1] || '';
            s[1] += new Array(prec - s[1].length + 1).join('0');
        }
        return s.join(dec);
    }

    // Function to load and display Item Quota Details
    function loadRincianKuota() {
        var ajaxUrl = "<?= site_url('admin/ajax_get_rincian_kuota_barang/'); ?>" + idPers;
        console.log('Loading rincian kuota from:', ajaxUrl);

        if (typeof $.fn.DataTable !== 'undefined' && $.fn.DataTable.isDataTable('#dataTableRincianKuotaBarang')) {
            rincianKuotaTable.clear().draw();
            rincianKuotaTable.destroy();
        }
        $('#dataTableRincianKuotaBarang tbody').html('<tr><td colspan="8" class="text-center">Loading item quota details... <i class="fas fa-spinner fa-spin"></i></td></tr>');

        $.ajax({
            url: ajaxUrl,
            type: "GET",
            dataType: "json",
            success: function(response) {
                console.log('Rincian kuota response:', response);
                var html = '';
                var no = 1;
                var totalInitialAgregat = 0;
                var totalRemainingAgregat = 0;

                if (response.data && response.data.length > 0) {
                    response.data.forEach(function(item) {
                        totalInitialAgregat += parseFloat(item.initial_quota_barang || 0);
                        totalRemainingAgregat += parseFloat(item.remaining_quota_barang || 0);

                        var statusBadge = 'secondary';
                        var statusText = item.status_kuota_barang ? item.status_kuota_barang.charAt(0).toUpperCase() + item.status_kuota_barang.slice(1) : 'N/A';
                        if (item.status_kuota_barang === 'active') statusBadge = 'success';
                        else if (item.status_kuota_barang === 'habis') statusBadge = 'danger';
                        else if (item.status_kuota_barang === 'expired') statusBadge = 'warning';
                        else if (item.status_kuota_barang === 'canceled') statusBadge = 'dark';

                        html += '<tr>';
                        html += '<td>' + no++ + '</td>';
                        html += '<td>' + (item.nama_barang ? item.nama_barang : '-') + '</td>';
                        html += '<td class="text-right">' + numberFormat(item.initial_quota_barang || 0, 0, ',', '.') + '</td>';
                        html += '<td class="text-right font-weight-bold ' + ((parseFloat(item.remaining_quota_barang || 0) <= 0) ? 'text-danger' : 'text-success') + '">' + numberFormat(item.remaining_quota_barang || 0, 0, ',', '.') + '</td>';
                        html += '<td>' + (item.nomor_skep_asal ? item.nomor_skep_asal : '-') + '</td>';
                        html += '<td>' + formatTanggal(item.tanggal_skep_asal) + '</td>';
                        html += '<td><span class="badge badge-' + statusBadge + '">' + statusText + '</span></td>';
                        html += '<td>' + formatTanggalWaktu(item.waktu_pencatatan) + '</td>';
                        html += '</tr>';
                    });
                } else {
                    html = '<tr><td colspan="8" class="text-center">No item quota details recorded yet.</td></tr>';
                }
                $('#dataTableRincianKuotaBarang tbody').html(html);

                // Update Total Quota Summary
                var totalTerpakaiAgregat = totalInitialAgregat - totalRemainingAgregat;
                $('#totalInitialAgregat').text(numberFormat(totalInitialAgregat, 0, ',', '.'));
                $('#totalRemainingAgregat').text(numberFormat(totalRemainingAgregat, 0, ',', '.')).removeClass('text-success text-danger').addClass((totalRemainingAgregat <= 0 && totalInitialAgregat > 0) ? 'text-danger' : 'text-success');
                $('#totalTerpakaiAgregat').text(numberFormat(totalTerpakaiAgregat, 0, ',', '.'));

                if (typeof $.fn.DataTable !== 'undefined') {
                    rincianKuotaTable = $('#dataTableRincianKuotaBarang').DataTable({
                        "order": [[ 1, "asc" ]],
                        "language": { 
                            "emptyTable": "No item quota details for this company yet.",
                            "loadingRecords": "Loading...",
                            "processing": "Processing..."
                        }
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error (Quota Details): ", status, error);
                console.error("Response text:", xhr.responseText);
                console.error("Status code:", xhr.status);
                $('#dataTableRincianKuotaBarang tbody').html('<tr><td colspan="8" class="text-center text-danger">Failed to load quota details. Error: ' + status + '</td></tr>');
                $('#totalInitialAgregat, #totalRemainingAgregat, #totalTerpakaiAgregat').text('Error');
            }
        });
    }

    // Function to load and display Quota Transaction Log
    function loadLogTransaksi() {
        var ajaxUrl = "<?= site_url('admin/ajax_get_log_transaksi_kuota/'); ?>" + idPers;
        console.log('Loading log transaksi from:', ajaxUrl);

        if (typeof $.fn.DataTable !== 'undefined' && $.fn.DataTable.isDataTable('#dataTableHistoriTransaksiKuota')) {
            historiTransaksiTable.clear().draw();
            historiTransaksiTable.destroy();
        }
        $('#dataTableHistoriTransaksiKuota tbody').html('<tr><td colspan="10" class="text-center">Loading transaction log data... <i class="fas fa-spinner fa-spin"></i></td></tr>');

        $.ajax({
            url: ajaxUrl,
            type: "GET",
            dataType: "json",
            success: function(response) {
                console.log('Log transaksi response:', response);
                var html = '';
                var no = 1;
                if (response.data && response.data.length > 0) {
                    response.data.forEach(function(log) {
                        var jenisBadge = 'secondary';
                        if (log.jenis_transaksi === 'penambahan') jenisBadge = 'success';
                        else if (log.jenis_transaksi === 'pengurangan') jenisBadge = 'danger';
                        else if (log.jenis_transaksi === 'koreksi') jenisBadge = 'warning';
                        var jenisText = log.jenis_transaksi ? log.jenis_transaksi.charAt(0).toUpperCase() + log.jenis_transaksi.slice(1) : 'N/A';

                        var jumlahPerubahanText = (log.jenis_transaksi === 'penambahan' ? '+' : (log.jenis_transaksi === 'pengurangan' ? '-' : '')) + numberFormat(Math.abs(log.jumlah_perubahan || 0), 0, ',', '.') + ' Unit';
                        var jumlahPerubahanClass = (log.jenis_transaksi === 'penambahan') ? 'text-success' : ((log.jenis_transaksi === 'pengurangan') ? 'text-danger' : '');

                        var linkRef = '#';
                        var idRef = log.id_referensi_transaksi;
                        var refText = '';
                        if (idRef && log.tipe_referensi) {
                            var tipeRefDisplay = log.tipe_referensi.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                            if (['Pengajuan Kuota', 'Pengajuan Kuota Disetujui'].includes(tipeRefDisplay)) {
                                linkRef = "<?= site_url('admin/detailPengajuanKuotaAdmin/'); ?>" + idRef;
                            } else if (['Permohonan Impor', 'Permohonan Impor Barang', 'Permohonan Impor Selesai'].includes(tipeRefDisplay)) {
                                linkRef = "<?= site_url('admin/detail_permohonan_admin/'); ?>" + idRef;
                            }
                            refText = '<br><small><a href="' + linkRef + '" ' + (linkRef !== '#' ? 'target="_blank"' : '') + ' title="View reference details">(Ref: ' + tipeRefDisplay + ' ID ' + idRef + (log.id_kuota_barang_referensi ? ' / QuotaItem ID ' + log.id_kuota_barang_referensi : '') + ')</a></small>';
                        }
                        var idReferensiDisplay = (log.id_referensi_transaksi ? log.id_referensi_transaksi : '-') + (log.id_kuota_barang_referensi ? ' <small class="text-muted">(KB:'+log.id_kuota_barang_referensi+')</small>' : '');

                        html += '<tr>';
                        html += '<td>' + no++ + '</td>';
                        html += '<td>' + formatTanggalWaktu(log.tanggal_transaksi) + '</td>';
                        html += '<td><span class="badge badge-' + jenisBadge + '">' + jenisText + '</span></td>';
                        html += '<td>' + (log.nama_barang_terkait ? log.nama_barang_terkait : '<span class="text-muted"><em>General</em></span>') + '</td>';
                        html += '<td class="text-right ' + jumlahPerubahanClass + '">' + jumlahPerubahanText + '</td>';
                        html += '<td class="text-right">' + numberFormat(log.sisa_kuota_sebelum || 0, 0, ',', '.') + '</td>';
                        html += '<td class="text-right">' + numberFormat(log.sisa_kuota_setelah || 0, 0, ',', '.') + '</td>';
                        html += '<td style="max-width: 300px; word-wrap: break-word;">' + (log.keterangan ? log.keterangan : '-') + refText + '</td>';
                        html += '<td>' + idReferensiDisplay + '</td>';
                        html += '<td>' + (log.nama_pencatat ? log.nama_pencatat : 'System') + '</td>';
                        html += '</tr>';
                    });
                } else {
                    html = '<tr><td colspan="10" class="text-center">No quota transaction history.</td></tr>';
                }
                $('#dataTableHistoriTransaksiKuota tbody').html(html);

                if (typeof $.fn.DataTable !== 'undefined') {
                    historiTransaksiTable = $('#dataTableHistoriTransaksiKuota').DataTable({
                        "order": [[ 1, "desc" ]],
                        "language": { 
                            "emptyTable": "No quota transaction history for this company.",
                            "loadingRecords": "Loading...",
                            "processing": "Processing..."
                        }
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error (Transaction Log): ", status, error);
                console.error("Response text:", xhr.responseText);
                console.error("Status code:", xhr.status);
                $('#dataTableHistoriTransaksiKuota tbody').html('<tr><td colspan="10" class="text-center text-danger">Failed to load transaction log data. Error: ' + status + '</td></tr>');
            }
        });
    }

    // Initialize data loading
    if (idPers > 0) {
        console.log('Loading data for company ID:', idPers);
        loadRincianKuota();
        loadLogTransaksi();
    } else {
        console.warn('Invalid company ID:', idPers);
        $('#dataTableRincianKuotaBarang tbody').html('<tr><td colspan="8" class="text-center text-warning">Invalid Company ID to load data.</td></tr>');
        $('#dataTableHistoriTransaksiKuota tbody').html('<tr><td colspan="10" class="text-center text-warning">Invalid Company ID to load data.</td></tr>');
        $('#totalInitialAgregat, #totalRemainingAgregat, #totalTerpakaiAgregat').text('N/A');
    }
});
</script>
<?= $this->endSection() ?>
