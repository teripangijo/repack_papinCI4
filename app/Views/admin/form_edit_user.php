<?= $this->extend('layouts/main') ?> // Menggunakan layout 'main.php'
<?= $this->section('title') ?>
    <?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Edit User'; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($subtitle ?? 'Edit User'); ?></h1>
        <a href="<?= site_url('admin/manajemen_user'); ?>" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Manajemen User
        </a>
    </div>

    <?php
    // Display validation errors for CodeIgniter 4
    $validation = \Config\Services::validation();
    if ($validation->getErrors()) : ?>
        <div class="alert alert-danger" role="alert">
            <?php foreach ($validation->getErrors() as $error) : ?>
                <p><?= $error ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('message')) { echo session()->getFlashdata('message'); } ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Edit Data untuk User: <?= htmlspecialchars($target_user_data['name'] ?? 'Tidak Ditemukan'); ?>
                (<?= htmlspecialchars($target_user_data['email'] ?? ''); ?>) </h6>
        </div>
        <div class="card-body">
            <?php if (isset($target_user_data) && $target_user_data) : ?>
            <?php
                $is_petugas_or_monitoring_role = in_array($target_user_data['role_id'], [3, 4]);
                $login_identifier_label = $is_petugas_or_monitoring_role ? 'NIP (Nomor Induk Pegawai)' : 'Email';
                $login_identifier_type = $is_petugas_or_monitoring_role ? 'text' : 'email';
            ?>
            <form action="<?= site_url('admin/edit_user/' . $target_user_data['id']); ?>" method="post">
                <?= csrf_field(); // CSRF token for CI4 ?>
                <div class="form-group">
                    <label for="name">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= old('name', $target_user_data['name']); ?>" required>
                    <?php if ($validation->hasError('name')) : ?>
                        <small class="text-danger pl-3"><?= $validation->getError('name'); ?></small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="login_identifier"><?= $login_identifier_label; ?> <span class="text-danger">*</span></label>
                    <input type="<?= $login_identifier_type; ?>" class="form-control" id="login_identifier" name="login_identifier" value="<?= old('login_identifier', $target_user_data['email']); ?>" required>
                    <?php if ($validation->hasError('login_identifier')) : ?>
                        <small class="text-danger pl-3"><?= $validation->getError('login_identifier'); ?></small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="role_id">Role <span class="text-danger">*</span></label>
                    <select class="form-control" id="role_id" name="role_id" required <?= ($target_user_data['id'] == 1) ? 'disabled' : ''; ?>>
                        <option value="">-- Pilih Role --</option>
                        <?php foreach ($roles_list as $role_item) : ?>
                            <option value="<?= htmlspecialchars($role_item['id']); ?>" <?= old('role_id', $target_user_data['role_id']) == $role_item['id'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($role_item['role']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($target_user_data['id'] == 1) : ?>
                        <small class="form-text text-muted">Role Admin Utama tidak dapat diubah.</small>
                        <input type="hidden" name="role_id" value="<?= htmlspecialchars($target_user_data['role_id']); ?>">
                    <?php endif; ?>
                    <?php if ($validation->hasError('role_id')) : ?>
                        <small class="text-danger pl-3"><?= $validation->getError('role_id'); ?></small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="is_active">Status Akun <span class="text-danger">*</span></label>
                    <select class="form-control" id="is_active" name="is_active" required <?= ($target_user_data['id'] == 1) ? 'disabled' : ''; ?>>
                        <option value="1" <?= old('is_active', $target_user_data['is_active']) == 1 ? 'selected' : ''; ?>>Aktif</option>
                        <option value="0" <?= old('is_active', $target_user_data['is_active']) == 0 ? 'selected' : ''; ?>>Tidak Aktif</option>
                    </select>
                     <?php if ($target_user_data['id'] == 1) : ?>
                        <small class="form-text text-muted">Status Admin Utama tidak dapat diubah.</small>
                         <input type="hidden" name="is_active" value="<?= htmlspecialchars($target_user_data['is_active']); ?>">
                    <?php endif; ?>
                    <?php if ($validation->hasError('is_active')) : ?>
                        <small class="text-danger pl-3"><?= $validation->getError('is_active'); ?></small>
                    <?php endif; ?>
                </div>

                <?php
                    // Logic for petugas_detail_edit (assuming $this->db is available or passed from controller)
                    // In CodeIgniter 4 views, you typically don't access the database directly.
                    // This data should be prepared in the controller and passed to the view.
                    $is_target_petugas = (old('role_id', $target_user_data['role_id']) == 3);
                    $petugas_detail_edit = $petugas_detail_edit ?? null; // Assume this is passed from controller if role is 3
                ?>
                <div id="petugas_fields_edit" style="<?= $is_target_petugas ? 'display:block;' : 'display:none;'; ?>">
                    <hr>
                    <h6 class="text-muted">Data Detail Petugas (Khusus Role Petugas)</h6>
                    <div class="form-group">
                        <label for="nip_petugas_edit">NIP (Nomor Induk Pegawai)</label>
                        <input type="text" class="form-control" id="nip_petugas_edit" name="nip_petugas_edit"
                               value="<?= old('nip_petugas_edit', $petugas_detail_edit['NIP'] ?? ($is_target_petugas ? $target_user_data['email'] : '') ); ?>"
                               <?= $is_target_petugas ? 'readonly' : ''; ?>>
                        <small class="form-text text-muted">Untuk role Petugas, NIP diambil dari field NIP di atas dan akan disinkronkan. Field ini hanya untuk referensi atau jika ada NIP terpisah.</small>
                    </div>
                    <div class="form-group">
                        <label for="jabatan_petugas_edit">Jabatan Petugas</label>
                        <input type="text" class="form-control" id="jabatan_petugas_edit" name="jabatan_petugas_edit" value="<?= old('jabatan_petugas_edit', $petugas_detail_edit['Jabatan'] ?? ''); ?>">
                    </div>
                </div>


                <button type="submit" class="btn btn-primary">Update Data User</button>
            </form>
            <?php else: ?>
                <p class="text-danger">Data user tidak ditemukan.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// JavaScript to show/hide staff detail fields based on role selection
document.addEventListener('DOMContentLoaded', function() {
    const roleDropdown = document.getElementById('role_id');
    const petugasFieldsDiv = document.getElementById('petugas_fields_edit');
    const nipPetugasEditInput = document.getElementById('nip_petugas_edit');
    const loginIdentifierInput = document.getElementById('login_identifier');
    const loginIdentifierLabel = document.querySelector('label[for="login_identifier"]');


    function togglePetugasFields() {
        const selectedRoleId = parseInt(roleDropdown.value);
        // Assuming Role ID for Staff = 3, Monitoring = 4
        if (selectedRoleId === 3) { // Staff
            petugasFieldsDiv.style.display = 'block';
            if (loginIdentifierInput) { // Sync NIP if login_identifier field exists
                 nipPetugasEditInput.value = loginIdentifierInput.value; // NIP in staff details follows login NIP
                 nipPetugasEditInput.setAttribute('readonly', 'readonly');
            }
            if (loginIdentifierLabel) loginIdentifierLabel.textContent = 'NIP (Nomor Induk Pegawai) *';
            if (loginIdentifierInput) loginIdentifierInput.type = 'text'; // or 'number'

        } else if (selectedRoleId === 4) { // Monitoring
            petugasFieldsDiv.style.display = 'none'; // Monitoring does not have NIP/Position details in staff table
            if (loginIdentifierLabel) loginIdentifierLabel.textContent = 'NIP (Nomor Induk Pegawai) *';
            if (loginIdentifierInput) loginIdentifierInput.type = 'text'; // or 'number'
        }
        else { // Other roles (Admin, Service User)
            petugasFieldsDiv.style.display = 'none';
            if (loginIdentifierLabel) loginIdentifierLabel.textContent = 'Email *';
            if (loginIdentifierInput) loginIdentifierInput.type = 'email';
        }
    }

    if (roleDropdown) {
        // Call on page load to set initial display
        togglePetugasFields();
        // Add event listener for role dropdown changes
        roleDropdown.addEventListener('change', togglePetugasFields);
    }

    // If role is Staff or Monitoring, NIP/Email in login_identifier field also fills NIP field in staff details (if any)
    if (loginIdentifierInput && nipPetugasEditInput) {
        loginIdentifierInput.addEventListener('input', function() {
            const selectedRoleId = parseInt(roleDropdown.value);
            if (selectedRoleId === 3) { // Only if role is Staff
                nipPetugasEditInput.value = this.value;
            }
        });
    }
});
</script>
<?= $this->endSection() ?>
