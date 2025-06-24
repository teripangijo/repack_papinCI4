<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
    <?= esc($subtitle ?? 'Edit Profil & Perusahaan') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
// Set default values
$default_nama_pers = old('NamaPers', $user_perusahaan['NamaPers'] ?? '');
$default_npwp = old('npwp', $user_perusahaan['npwp'] ?? '');
$default_alamat = old('alamat', $user_perusahaan['alamat'] ?? '');
$default_telp = old('telp', $user_perusahaan['telp'] ?? '');
$default_pic = old('pic', $user_perusahaan['pic'] ?? '');
$default_jabatan_pic = old('jabatanPic', $user_perusahaan['jabatanPic'] ?? '');
$default_no_skep_fasilitas = old('NoSkepFasilitas', $user_perusahaan['NoSkepFasilitas'] ?? '');

$existing_ttd_file = $user_perusahaan['ttd'] ?? null;
$existing_skep_fasilitas_file = $user_perusahaan['FileSkepFasilitas'] ?? null;

$current_user_image = $user['image'] ?? 'default.jpg';
// [DIREVISI] Menggunakan controller downloadFile untuk menampilkan gambar
$profileImagePath = ($current_user_image != 'default.jpg' && !empty($current_user_image)) ? site_url('user/downloadFile/' . esc($current_user_image)) : base_url('assets/img/default-avatar.png');
$fallbackImagePath = base_url('assets/img/default-avatar.png');

// Get validation errors service
$validation = \Config\Services::validation();
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"> <?= esc($subtitle ?? 'Edit Profil & Perusahaan') ?></h1>

    <!-- CI4 Validation Errors -->
    <?= $validation->listErrors('list') ?>

    <!-- Flash Messages (handled by layout) -->

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

    <form action="<?= site_url('user/edit') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Informasi Akun Pengguna</h6></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="name">Nama Lengkap (Kontak Utama)</label>
                            <input type="text" class="form-control" id="name" value="<?= esc($user['name'] ?? '') ?>" readonly title="Nama lengkap tidak dapat diubah.">
                        </div>
                        <div class="form-group">
                            <label for="email">Email (Login)</label>
                            <input type="email" class="form-control" id="email" value="<?= esc($user['email'] ?? '') ?>" readonly title="Email login tidak dapat diubah.">
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <label>Gambar Profil/Logo Perusahaan Saat Ini</label><br>
                        <img src="<?= $profileImagePath ?>" onerror="this.onerror=null; this.src='<?= $fallbackImagePath ?>';" class="img-thumbnail mb-2" alt="Logo Perusahaan" style="width: 150px; height: 150px; object-fit: contain;">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input <?= $validation->hasError('profile_image') ? 'is-invalid' : '' ?>" id="profile_image" name="profile_image" accept="image/jpeg,image/png,image/gif">
                            <label class="custom-file-label text-left" for="profile_image"><?= ($current_user_image != 'default.jpg' && !empty($current_user_image)) ? 'Ganti Gambar/Logo...' : 'Pilih Gambar/Logo...' ?></label>
                        </div>
                        <div class="invalid-feedback d-block text-center mt-1">
                            <?= $validation->getError('profile_image') ?>
                        </div>
                        <small class="form-text text-muted">Format: JPG, PNG, GIF. Maks 1MB.</small>
                    </div>
                </div>

                <hr>
        
                <div class="form-group row">
                    <div class="col-sm-3">Keamanan Akun</div>
                    <div class="col-sm-9">
                        <p>Amankan akun Anda dengan lapisan verifikasi tambahan.</p>
                        <a href="<?= site_url('user/reset_mfa') ?>" class="btn btn-primary">
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
                    <div class="form-group col-md-6">
                        <label for="NamaPers">Nama Perusahaan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= $validation->hasError('NamaPers') ? 'is-invalid' : '' ?>" id="NamaPers" name="NamaPers" placeholder="Nama Lengkap Perusahaan" value="<?= $default_nama_pers ?>" required>
                        <div class="invalid-feedback"><?= $validation->getError('NamaPers') ?></div>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="npwp">NPWP <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= $validation->hasError('npwp') ? 'is-invalid' : '' ?>" id="npwp" name="npwp" placeholder="00.000.000.0-000.000" value="<?= $default_npwp ?>" required>
                        <div class="invalid-feedback"><?= $validation->getError('npwp') ?></div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="alamat">Alamat Lengkap Perusahaan <span class="text-danger">*</span></label>
                    <textarea class="form-control <?= $validation->hasError('alamat') ? 'is-invalid' : '' ?>" id="alamat" name="alamat" placeholder="Alamat lengkap sesuai domisili perusahaan" rows="3" required><?= $default_alamat ?></textarea>
                    <div class="invalid-feedback"><?= $validation->getError('alamat') ?></div>
                </div>
                <div class="form-group">
                    <label for="telp">Nomor Telepon Perusahaan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?= $validation->hasError('telp') ? 'is-invalid' : '' ?>" id="telp" name="telp" placeholder="Contoh: 021-xxxxxxx atau 08xxxxxxxxxx" value="<?= $default_telp ?>" required>
                    <div class="invalid-feedback"><?= $validation->getError('telp') ?></div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="pic">Nama PIC (Person In Charge) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= $validation->hasError('pic') ? 'is-invalid' : '' ?>" id="pic" name="pic" placeholder="Nama lengkap PIC" value="<?= $default_pic ?>" required>
                        <div class="invalid-feedback"><?= $validation->getError('pic') ?></div>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="jabatanPic">Jabatan PIC <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= $validation->hasError('jabatanPic') ? 'is-invalid' : '' ?>" id="jabatanPic" name="jabatanPic" placeholder="Jabatan PIC di perusahaan" value="<?= $default_jabatan_pic ?>" required>
                        <div class="invalid-feedback"><?= $validation->getError('jabatanPic') ?></div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="NoSkepFasilitas">No. SKEP Fasilitas Umum (Jika Ada)</label>
                    <input type="text" class="form-control <?= $validation->hasError('NoSkepFasilitas') ? 'is-invalid' : '' ?>" id="NoSkepFasilitas" name="NoSkepFasilitas" placeholder="Nomor SKEP Fasilitas (KB, GB, dll.)" value="<?= $default_no_skep_fasilitas ?>">
                    <div class="invalid-feedback"><?= $validation->getError('NoSkepFasilitas') ?></div>
                </div>
                <div class="form-group">
                    <label for="file_skep_fasilitas">Upload File SKEP Fasilitas (Opsional, PDF/Gambar maks 2MB)</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input <?= $validation->hasError('file_skep_fasilitas') ? 'is-invalid' : '' ?>" id="file_skep_fasilitas" name="file_skep_fasilitas" accept=".pdf,.jpg,.jpeg,.png">
                        <label class="custom-file-label" for="file_skep_fasilitas">Pilih file SKEP Fasilitas...</label>
                    </div>
                    <div class="invalid-feedback d-block mt-1"><?= $validation->getError('file_skep_fasilitas') ?></div>
                    <?php if ($existing_skep_fasilitas_file): ?>
                        <small class="form-text text-info mt-1">File SKEP Fasilitas saat ini: <a href="<?= site_url('user/downloadFile/' . esc($existing_skep_fasilitas_file)) ?>" target="_blank">Lihat File</a></small>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (isset($is_activating) && $is_activating): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Input Kuota Awal (Jika Sudah Memiliki SKEP & Kuota Sebelumnya)</h6></div>
            <div class="card-body">
                <p class="small text-muted">Jika perusahaan Anda sudah memiliki SKEP dan penetapan kuota sebelumnya (di luar sistem ini), silakan masukkan detailnya di bawah untuk **satu jenis barang**. Ini hanya untuk pencatatan awal. Jika ada lebih dari satu jenis barang/SKEP dengan kuota berbeda, Anda bisa menambahkannya melalui menu "Pengajuan Kuota" setelah profil ini disimpan, atau hubungi Admin.</p>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="initial_skep_no">Nomor SKEP Kuota Awal <span class="text-info">(Wajib jika input kuota)</span></label>
                        <input type="text" class="form-control <?= $validation->hasError('initial_skep_no') ? 'is-invalid' : '' ?>" id="initial_skep_no" name="initial_skep_no" value="<?= old('initial_skep_no') ?>" placeholder="No. SKEP terkait kuota awal">
                        <div class="invalid-feedback"><?= $validation->getError('initial_skep_no') ?></div>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="initial_skep_tgl">Tanggal SKEP Kuota Awal <span class="text-info">(Wajib jika input kuota)</span></label>
                        <input type="date" class="form-control <?= $validation->hasError('initial_skep_tgl') ? 'is-invalid' : '' ?>" id="initial_skep_tgl" name="initial_skep_tgl" value="<?= old('initial_skep_tgl') ?>">
                        <div class="invalid-feedback"><?= $validation->getError('initial_skep_tgl') ?></div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-7">
                        <label for="initial_nama_barang">Nama Barang untuk Kuota Awal <span class="text-info">(Wajib jika input kuota)</span></label>
                        <input type="text" class="form-control <?= $validation->hasError('initial_nama_barang') ? 'is-invalid' : '' ?>" id="initial_nama_barang" name="initial_nama_barang" value="<?= old('initial_nama_barang') ?>" placeholder="Contoh: Plastic Box, Fiber Pallet">
                        <div class="invalid-feedback"><?= $validation->getError('initial_nama_barang') ?></div>
                    </div>
                    <div class="form-group col-md-5">
                        <label for="initial_kuota_jumlah">Jumlah Kuota Awal (Unit) <span class="text-info">(Wajib jika input kuota)</span></label>
                        <input type="number" class="form-control <?= $validation->hasError('initial_kuota_jumlah') ? 'is-invalid' : '' ?>" id="initial_kuota_jumlah" name="initial_kuota_jumlah" value="<?= old('initial_kuota_jumlah') ?>" min="1" placeholder="Jumlah unit">
                        <div class="invalid-feedback"><?= $validation->getError('initial_kuota_jumlah') ?></div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="initial_skep_file">Upload File SKEP Kuota Awal (Opsional, PDF/Gambar maks 2MB)</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input <?= $validation->hasError('initial_skep_file') ? 'is-invalid' : '' ?>" id="initial_skep_file" name="initial_skep_file" accept=".pdf,.jpg,.jpeg,.png">
                        <label class="custom-file-label" for="initial_skep_file">Pilih file SKEP Kuota Awal...</label>
                    </div>
                    <div class="invalid-feedback d-block mt-1"><?= $validation->getError('initial_skep_file') ?></div>
                </div>
                <small class="form-text text-info">Field di atas wajib diisi jika Anda ingin mencatatkan kuota awal untuk satu jenis barang.</small>
            </div>
        </div>
        <?php endif; ?>

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
                                <td><?= esc($kuota_brg['nama_barang']) ?></td>
                                <td class="text-right"><?= esc(number_format($kuota_brg['initial_quota_barang'] ?? 0, 0, ',', '.')) ?> Unit</td>
                                <td class="text-right font-weight-bold <?= (($kuota_brg['remaining_quota_barang'] ?? 0) <= 0) ? 'text-danger' : 'text-success' ?>"><?= esc(number_format($kuota_brg['remaining_quota_barang'] ?? 0, 0, ',', '.')) ?> Unit</td>
                                <td><?= esc($kuota_brg['nomor_skep_asal'] ?? '-') ?></td>
                                <td><?= (isset($kuota_brg['tanggal_skep_asal']) && $kuota_brg['tanggal_skep_asal'] != '0000-00-00') ? esc(date('d M Y', strtotime($kuota_brg['tanggal_skep_asal']))) : '-' ?></td>
                                <td>
                                    <?php
                                    $status_kb_badge = 'secondary'; $status_kb_text = ucfirst(esc($kuota_brg['status_kuota_barang'] ?? 'N/A'));
                                    if (isset($kuota_brg['status_kuota_barang'])) {
                                        if ($kuota_brg['status_kuota_barang'] == 'active') $status_kb_badge = 'success';
                                        else if ($kuota_brg['status_kuota_barang'] == 'habis') $status_kb_badge = 'danger';
                                        else if ($kuota_brg['status_kuota_barang'] == 'expired') $status_kb_badge = 'warning';
                                    }
                                    ?>
                                    <span class="badge badge-<?= $status_kb_badge ?>"><?= $status_kb_text ?></span>
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

        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Upload Dokumen Perusahaan</h6></div>
            <div class="card-body">
                <div class="form-group">
                    <label for="ttd">
                        Upload File Tanda Tangan PIC (Gambar/PDF, maks 1MB)
                        <?php if (isset($is_activating) && $is_activating): ?><span class="text-danger">*</span><?php else: ?> (Kosongkan jika tidak ingin mengubah)<?php endif; ?>
                    </label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input <?= $validation->hasError('ttd') ? 'is-invalid' : '' ?>" id="ttd" name="ttd" accept="image/jpeg,image/png,application/pdf">
                        <label class="custom-file-label" for="ttd">Pilih file TTD PIC...</label>
                    </div>
                    <div class="invalid-feedback d-block mt-1"><?= $validation->getError('ttd') ?></div>
                    <small class="form-text text-muted">Format: JPG, PNG, PDF. Maksimum ukuran 1MB.</small>
                    <?php if ($existing_ttd_file): ?>
                        <small class="form-text text-info mt-1">File TTD saat ini: <a href="<?= site_url('user/downloadFile/' . esc($existing_ttd_file)) ?>" target="_blank">Lihat File</a></small>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-user btn-block mt-4 mb-4">
            <i class="fas fa-save fa-fw"></i> <?= (isset($is_activating) && $is_activating) ? 'Simpan Data & Aktifkan Akun' : 'Update Data Profil & Perusahaan' ?>
        </button>
    </form>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Script for custom file input
    document.querySelectorAll('.custom-file-input').forEach(function(input) {
        var label = input.nextElementSibling;
        var originalLabelText = label.innerHTML;

        input.addEventListener('change', function (e) {
            var fileName = e.target.files.length > 0 ? e.target.files[0].name : originalLabelText;
            label.innerText = fileName;
        });
    });

    // Initialize DataTables if the library is loaded and the table exists
    if (typeof $ !== 'undefined' && $.fn.DataTable && $('#dataTableKuotaBarangUser').length) {
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
<?= $this->endSection() ?>
