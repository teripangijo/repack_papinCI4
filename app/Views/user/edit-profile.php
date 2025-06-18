<?php
defined('BASEPATH') OR exit('No direct script access allowed');


$default_nama_pers = '';
$default_npwp = '';
$default_alamat = '';
$default_telp = '';
$default_pic = '';
$default_jabatan_pic = '';
$default_no_skep_fasilitas = ''; 
$existing_ttd_file = null;
$existing_skep_fasilitas_file = null;

if (isset($user_perusahaan) && is_array($user_perusahaan) && !empty($user_perusahaan)) {
    $default_nama_pers = $user_perusahaan['NamaPers'] ?? '';
    $default_npwp = $user_perusahaan['npwp'] ?? '';
    $default_alamat = $user_perusahaan['alamat'] ?? '';
    $default_telp = $user_perusahaan['telp'] ?? '';
    $default_pic = $user_perusahaan['pic'] ?? '';
    $default_jabatan_pic = $user_perusahaan['jabatanPic'] ?? '';
    $default_no_skep_fasilitas = $user_perusahaan['NoSkepFasilitas'] ?? '';
    $existing_ttd_file = $user_perusahaan['ttd'] ?? null;
    $existing_skep_fasilitas_file = $user_perusahaan['FileSkepFasilitas'] ?? null;
}

$current_user_image = isset($user['image']) ? $user['image'] : 'default.jpg';
$profileImagePath = base_url('uploads/profile_images/') . htmlspecialchars($current_user_image);
$fallbackImagePath = base_url('assets/img/default-avatar.png'); 

$form_action_url = site_url('user/edit');
?>

<div class="container-fluid"> <?php ?>

    <h1 class="h3 mb-4 text-gray-800"> <?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Edit Profil & Perusahaan'; ?></h1>

    <?php
    if (validation_errors()) { echo '<div class="alert alert-danger mt-3" role="alert">' . validation_errors() . '</div>'; }
    if (isset($upload_error) && !empty($upload_error)) { echo '<div class="alert alert-danger mt-3" role="alert">' . $upload_error . '</div>'; }
    ?>

    <?php if (isset($user['is_active'])) : ?>
        <?php if ($user['is_active'] == 1 && !$is_activating) : ?>
            <div class="alert alert-success" role="alert"><h5 class="alert-heading mb-0"><i class="fas fa-check-circle"></i> Status Akun: Aktif</h5><p class="mb-0 small">Profil perusahaan Anda sudah lengkap.</p></div>
        <?php elseif ($user['is_active'] == 1 && $is_activating) : ?>
            <div class="alert alert-info" role="alert"><h5 class="alert-heading"><i class="fas fa-building"></i> Lengkapi Profil Perusahaan</h5><p class="mb-0 small">Akun Anda sudah aktif, namun silakan lengkapi data perusahaan di bawah ini.</p></div>
        <?php else : ?>
            <div class="alert alert-warning" role="alert"><h5 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Status Akun: Belum Aktif!</h5><p class="mb-0 small">Mohon lengkapi data profil dan perusahaan di bawah ini untuk mengaktifkan akun.</p></div>
        <?php endif; ?>
    <?php endif; ?>
    <hr>

    <?php echo form_open_multipart($form_action_url); ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Informasi Akun Pengguna</h6></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="name">Nama Lengkap (Kontak Utama)</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name'] ?? ''); ?>" readonly title="Nama lengkap tidak dapat diubah.">
                    </div>
                    <div class="form-group">
                        <label for="email">Email (Login)</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? ''); ?>" readonly title="Email login tidak dapat diubah.">
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <label>Gambar Profil/Logo Perusahaan Saat Ini</label><br>
                    <img src="<?= $profileImagePath; ?>" onerror="this.onerror=null; this.src='<?= $fallbackImagePath; ?>';" class="img-thumbnail mb-2" alt="Logo Perusahaan" style="width: 150px; height: 150px; object-fit: contain;">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input <?= (form_error('profile_image')) ? 'is-invalid' : ''; ?>" id="profile_image" name="profile_image" accept="image/jpeg,image/png,image/gif">
                        <label class="custom-file-label text-left" for="profile_image"><?= ($current_user_image != 'default.jpg' && !empty($current_user_image)) ? htmlspecialchars($current_user_image) : 'Ganti Gambar/Logo...'; ?></label>
                    </div>
                    <?= form_error('profile_image', '<small class="text-danger d-block text-center mt-1">', '</small>'); ?>
                    <small class="form-text text-muted">Format: JPG, PNG, GIF. Maks 1MB.</small>
                </div>
            </div>

            <hr>
    
            <div class="form-group row">
                <div class="col-sm-3">Keamanan Akun</div>
                <div class="col-sm-9">
                    <p>Amankan akun Anda dengan lapisan verifikasi tambahan.</p>
                    <a href="<?= base_url('user/reset_mfa'); ?>" class="btn btn-primary">
                        Atur Ulang Multi-Factor Authentication (MFA)
                    </a>
                </div>
            </div>
            
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Informasi Detail Perusahaan</h6></div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-6"><label for="NamaPers">Nama Perusahaan <span class="text-danger">*</span></label><input type="text" class="form-control <?= (form_error('NamaPers')) ? 'is-invalid' : ''; ?>" id="NamaPers" name="NamaPers" placeholder="Nama Lengkap Perusahaan" value="<?= set_value('NamaPers', $default_nama_pers) ?>" required><?= form_error('NamaPers', '<small class="text-danger pl-1">', '</small>'); ?></div>
                <div class="form-group col-md-6"><label for="npwp">NPWP <span class="text-danger">*</span></label><input type="text" class="form-control <?= (form_error('npwp')) ? 'is-invalid' : ''; ?>" id="npwp" name="npwp" placeholder="00.000.000.0-000.000" value="<?= set_value('npwp', $default_npwp) ?>" required><?= form_error('npwp', '<small class="text-danger pl-1">', '</small>'); ?></div>
            </div>
            <div class="form-group"><label for="alamat">Alamat Lengkap Perusahaan <span class="text-danger">*</span></label><textarea class="form-control <?= (form_error('alamat')) ? 'is-invalid' : ''; ?>" id="alamat" name="alamat" placeholder="Alamat lengkap sesuai domisili perusahaan" rows="3" required><?= set_value('alamat', $default_alamat) ?></textarea><?= form_error('alamat', '<small class="text-danger pl-1">', '</small>'); ?></div>
            <div class="form-group"><label for="telp">Nomor Telepon Perusahaan <span class="text-danger">*</span></label><input type="text" class="form-control <?= (form_error('telp')) ? 'is-invalid' : ''; ?>" id="telp" name="telp" placeholder="Contoh: 021-xxxxxxx atau 08xxxxxxxxxx" value="<?= set_value('telp', $default_telp) ?>" required><?= form_error('telp', '<small class="text-danger pl-1">', '</small>'); ?></div>
            <div class="form-row">
                <div class="form-group col-md-6"><label for="pic">Nama PIC (Person In Charge) <span class="text-danger">*</span></label><input type="text" class="form-control <?= (form_error('pic')) ? 'is-invalid' : ''; ?>" id="pic" name="pic" placeholder="Nama lengkap PIC" value="<?= set_value('pic', $default_pic) ?>" required><?= form_error('pic', '<small class="text-danger pl-1">', '</small>'); ?></div>
                <div class="form-group col-md-6"><label for="jabatanPic">Jabatan PIC <span class="text-danger">*</span></label><input type="text" class="form-control <?= (form_error('jabatanPic')) ? 'is-invalid' : ''; ?>" id="jabatanPic" name="jabatanPic" placeholder="Jabatan PIC di perusahaan" value="<?= set_value('jabatanPic', $default_jabatan_pic) ?>" required><?= form_error('jabatanPic', '<small class="text-danger pl-1">', '</small>'); ?></div>
            </div>
             <div class="form-group">
                <label for="NoSkepFasilitas">No. SKEP Fasilitas Umum (Jika Ada)</label>
                <input type="text" class="form-control <?= (form_error('NoSkepFasilitas')) ? 'is-invalid' : ''; ?>" id="NoSkepFasilitas" name="NoSkepFasilitas" placeholder="Nomor SKEP Fasilitas (KB, GB, dll.)" value="<?= set_value('NoSkepFasilitas', $default_no_skep_fasilitas); ?>">
                <?= form_error('NoSkepFasilitas', '<small class="text-danger pl-1">', '</small>'); ?>
            </div>
            <div class="form-group">
                <label for="file_skep_fasilitas">Upload File SKEP Fasilitas (Opsional, PDF/Gambar maks 2MB)</label>
                <div class="custom-file">
                     <input type="file" class="custom-file-input <?= (form_error('file_skep_fasilitas')) ? 'is-invalid' : ''; ?>" id="file_skep_fasilitas" name="file_skep_fasilitas" accept=".pdf,.jpg,.jpeg,.png">
                     <label class="custom-file-label" for="file_skep_fasilitas"><?= $existing_skep_fasilitas_file ? htmlspecialchars($existing_skep_fasilitas_file) : 'Pilih file SKEP Fasilitas...'; ?></label>
                </div>
                <?= form_error('file_skep_fasilitas', '<small class="text-danger d-block mt-1">', '</small>'); ?>
                <?php if ($existing_skep_fasilitas_file): ?>
                    <small class="form-text text-info mt-1">File SKEP Fasilitas saat ini: <a href="<?= base_url('uploads/skep_fasilitas/' . htmlspecialchars($existing_skep_fasilitas_file)); ?>" target="_blank"><?= htmlspecialchars($existing_skep_fasilitas_file); ?></a></small>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php ?>
    <?php if (isset($is_activating) && $is_activating): ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Input Kuota Awal (Jika Sudah Memiliki SKEP & Kuota Sebelumnya)</h6></div>
        <div class="card-body">
            <p class="small text-muted">Jika perusahaan Anda sudah memiliki SKEP dan penetapan kuota sebelumnya (di luar sistem ini), silakan masukkan detailnya di bawah untuk **satu jenis barang**. Ini hanya untuk pencatatan awal. Jika ada lebih dari satu jenis barang/SKEP dengan kuota berbeda, Anda bisa menambahkannya melalui menu "Pengajuan Kuota" setelah profil ini disimpan, atau hubungi Admin.</p>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="initial_skep_no">Nomor SKEP Kuota Awal <span class="text-info">(Wajib jika input kuota)</span></label>
                    <input type="text" class="form-control <?= (form_error('initial_skep_no')) ? 'is-invalid' : ''; ?>" id="initial_skep_no" name="initial_skep_no" value="<?= set_value('initial_skep_no'); ?>" placeholder="No. SKEP terkait kuota awal">
                    <?= form_error('initial_skep_no', '<small class="text-danger pl-1">', '</small>'); ?>
                </div>
                <div class="form-group col-md-6">
                    <label for="initial_skep_tgl">Tanggal SKEP Kuota Awal <span class="text-info">(Wajib jika input kuota)</span></label>
                    <input type="date" class="form-control <?= (form_error('initial_skep_tgl')) ? 'is-invalid' : ''; ?>" id="initial_skep_tgl" name="initial_skep_tgl" value="<?= set_value('initial_skep_tgl'); ?>">
                    <?= form_error('initial_skep_tgl', '<small class="text-danger pl-1">', '</small>'); ?>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-7">
                    <label for="initial_nama_barang">Nama Barang untuk Kuota Awal <span class="text-info">(Wajib jika input kuota)</span></label>
                    <input type="text" class="form-control <?= (form_error('initial_nama_barang')) ? 'is-invalid' : ''; ?>" id="initial_nama_barang" name="initial_nama_barang" value="<?= set_value('initial_nama_barang'); ?>" placeholder="Contoh: Plastic Box, Fiber Pallet">
                    <?= form_error('initial_nama_barang', '<small class="text-danger pl-1">', '</small>'); ?>
                </div>
                <div class="form-group col-md-5">
                    <label for="initial_kuota_jumlah">Jumlah Kuota Awal (Unit) <span class="text-info">(Wajib jika input kuota)</span></label>
                    <input type="number" class="form-control <?= (form_error('initial_kuota_jumlah')) ? 'is-invalid' : ''; ?>" id="initial_kuota_jumlah" name="initial_kuota_jumlah" value="<?= set_value('initial_kuota_jumlah'); ?>" min="1" placeholder="Jumlah unit">
                    <?= form_error('initial_kuota_jumlah', '<small class="text-danger pl-1">', '</small>'); ?>
                </div>
            </div>
            <div class="form-group">
                <label for="initial_skep_file">Upload File SKEP Kuota Awal (Opsional, PDF/Gambar maks 2MB)</label>
                <div class="custom-file">
                     <input type="file" class="custom-file-input <?= (form_error('initial_skep_file')) ? 'is-invalid' : ''; ?>" id="initial_skep_file" name="initial_skep_file" accept=".pdf,.jpg,.jpeg,.png">
                     <label class="custom-file-label" for="initial_skep_file">Pilih file SKEP Kuota Awal...</label>
                </div>
                <?= form_error('initial_skep_file', '<small class="text-danger d-block mt-1">', '</small>'); ?>
            </div>
            <small class="form-text text-info">Field di atas wajib diisi jika Anda ingin mencatatkan kuota awal untuk satu jenis barang.</small>
        </div>
    </div>
    <?php endif; ?>
    <?php ?>

    <?php ?>
    <?php if (isset($is_activating) && !$is_activating && isset($daftar_kuota_barang_user)): ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Daftar Kuota per Jenis Barang Saat Ini (Read-Only)</h6></div>
        <div class="card-body">
            <?php if(!empty($daftar_kuota_barang_user)): ?>
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover" id="dataTableKuotaBarangUser" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th class="text-right">Kuota Awal Diberikan</th>
                            <th class="text-right">Sisa Kuota</th>
                            <th>No. SKEP Asal</th>
                            <th>Tgl. SKEP Asal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($daftar_kuota_barang_user as $kuota_brg): ?>
                        <tr>
                            <td><?= htmlspecialchars($kuota_brg['nama_barang']); ?></td>
                            <td class="text-right"><?= htmlspecialchars(number_format($kuota_brg['initial_quota_barang'] ?? 0, 0, ',', '.')); ?> Unit</td>
                            <td class="text-right font-weight-bold <?= (($kuota_brg['remaining_quota_barang'] ?? 0) <= 0) ? 'text-danger' : 'text-success'; ?>"><?= htmlspecialchars(number_format($kuota_brg['remaining_quota_barang'] ?? 0, 0, ',', '.')); ?> Unit</td>
                            <td><?= htmlspecialchars($kuota_brg['nomor_skep_asal'] ?? '-'); ?></td>
                            <td><?= (isset($kuota_brg['tanggal_skep_asal']) && $kuota_brg['tanggal_skep_asal'] != '0000-00-00') ? date('d M Y', strtotime($kuota_brg['tanggal_skep_asal'])) : '-'; ?></td>
                            <td>
                                <?php
                                $status_kb_badge = 'secondary'; $status_kb_text = ucfirst(htmlspecialchars($kuota_brg['status_kuota_barang'] ?? 'N/A'));
                                if (isset($kuota_brg['status_kuota_barang'])) {
                                    if ($kuota_brg['status_kuota_barang'] == 'active') $status_kb_badge = 'success';
                                    else if ($kuota_brg['status_kuota_barang'] == 'habis') $status_kb_badge = 'danger';
                                    else if ($kuota_brg['status_kuota_barang'] == 'expired') $status_kb_badge = 'warning';
                                }
                                ?>
                                <span class="badge badge-<?= $status_kb_badge; ?>"><?= $status_kb_text; ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <p class="text-muted"><em>Belum ada data kuota per jenis barang untuk perusahaan ini.</em></p>
            <?php endif; ?>
            <small class="form-text text-muted mt-2">Daftar kuota di atas dikelola oleh Administrator. Anda dapat mengajukan penambahan kuota melalui menu "Pengajuan Kuota".</small>
        </div>
    </div>
    <?php endif; ?>
    <?php ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Upload Dokumen Perusahaan</h6></div>
        <div class="card-body">
            <div class="form-group">
                <label for="ttd">
                    Upload File Tanda Tangan PIC (Gambar/PDF, maks 1MB)
                    <?php if (isset($is_activating) && $is_activating): ?><span class="text-danger">*</span><?php else: ?> (Kosongkan jika tidak ingin mengubah)<?php endif; ?>
                </label>
                <div class="custom-file">
                     <input type="file" class="custom-file-input <?= (form_error('ttd')) ? 'is-invalid' : ''; ?>" id="ttd" name="ttd" aria-describedby="ttdHelp" accept="image/jpeg,image/png,application/pdf">
                     <label class="custom-file-label" for="ttd"><?= $existing_ttd_file ? htmlspecialchars($existing_ttd_file) : 'Pilih file TTD PIC...'; ?></label>
                </div>
                 <?= form_error('ttd', '<small class="text-danger d-block mt-1">', '</small>'); ?>
                <small id="ttdHelp" class="form-text text-muted">Format: JPG, PNG, PDF. Maksimum ukuran 1MB.</small>
                <?php if ($existing_ttd_file): ?>
                    <small class="form-text text-info mt-1">File TTD saat ini: <a href="<?= base_url('uploads/ttd/' . htmlspecialchars($existing_ttd_file)); ?>" target="_blank"><?= htmlspecialchars($existing_ttd_file); ?></a></small>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-user btn-block mt-4 mb-4">
         <i class="fas fa-save fa-fw"></i> <?php echo (isset($is_activating) && $is_activating) ? 'Simpan Data & Aktifkan Akun' : 'Update Data Profil & Perusahaan'; ?>
    </button>
    <?php echo form_close(); ?>
</div> <?php ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Skrip untuk custom file input
    var fileInputs = document.querySelectorAll('.custom-file-input');
    Array.prototype.forEach.call(fileInputs, function(input) {
        var label = input.nextElementSibling;
        // Simpan teks original dari atribut data atau dari innerHTML jika belum ada
        var originalLabelText = label.getAttribute('data-original-text') || label.innerHTML;
        label.setAttribute('data-original-text', originalLabelText); // Pastikan tersimpan

        input.addEventListener('change', function (e) {
            if (e.target.files.length > 0) {
                label.innerText = e.target.files[0].name;
            } else {
                label.innerText = originalLabelText;
            }
        });
    });

    // Inisialisasi DataTables untuk tabel kuota barang jika ada dan library sudah dimuat
    if (typeof $ !== 'undefined' && typeof $.fn.DataTable !== 'undefined' && $('#dataTableKuotaBarangUser').length) {
        $('#dataTableKuotaBarangUser').DataTable({
            "order": [[0, "asc"]],
            "language": {
                "emptyTable": "Tidak ada data kuota per jenis barang untuk ditampilkan.",
                "zeroRecords": "Tidak ada data yang cocok ditemukan",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                "infoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
                "infoFiltered": "(disaring dari _MAX_ total entri)",
                "lengthMenu": "Tampilkan _MENU_ entri",
                "search": "Cari:",
                "paginate": { "first": "Awal", "last": "Akhir", "next": "Berikutnya", "previous": "Sebelumnya"}
            },
            "pageLength": 5,
            "lengthMenu": [ [5, 10, 25, -1], [5, 10, 25, "Semua"] ]
        });
    }
});
</script>