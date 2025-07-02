<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>
    <?= isset($subtitle) ? htmlspecialchars($subtitle) : 'Tambah User'; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= htmlspecialchars($subtitle ?? 'Tambah User Baru'); ?></h1>
        <a href="<?= base_url('admin/manajemen_user'); ?>" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Manajemen User
        </a>
    </div>

    <!-- Display flash messages -->
    <?php if (session()->getFlashdata('message')): ?>
        <?= session()->getFlashdata('message') ?>
    <?php endif; ?>

    <!-- Display validation errors -->
    <?php if (session('errors')): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                <?php foreach (session('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Formulir Tambah User untuk Role: <?= htmlspecialchars($target_role_info['role'] ?? 'Tidak Diketahui'); ?></h6>
        </div>
        <div class="card-body">
            <form action="<?= base_url('admin/tambah_user/' . $role_id_to_add); ?>" method="post" id="tambahUserForm">
                <?= csrf_field(); ?>
                <input type="hidden" name="role_id_hidden" value="<?= htmlspecialchars($role_id_to_add); ?>">
                <input type="hidden" name="form_token" value="<?= htmlspecialchars($form_token ?? ''); ?>">

                <!-- Field Nama -->
                <div class="form-group">
                    <label for="name">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control <?= session('errors.name') ? 'is-invalid' : ''; ?>" 
                           id="name" 
                           name="name" 
                           value="<?= old('name'); ?>" 
                           placeholder="Masukkan nama lengkap"
                           required>
                    <?php if (session('errors.name')): ?>
                        <div class="invalid-feedback"><?= session('errors.name') ?></div>
                    <?php endif; ?>
                </div>

                <!-- Field Login Identifier -->
                <div class="form-group">
                    <label for="login_identifier"><?= htmlspecialchars($login_identifier_label_view ?? 'Login Identifier'); ?> <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control <?= session('errors.login_identifier') ? 'is-invalid' : ''; ?>" 
                           id="login_identifier" 
                           name="login_identifier" 
                           placeholder="<?= htmlspecialchars($login_identifier_placeholder ?? 'Masukkan Email atau NIP'); ?>" 
                           value="<?= old('login_identifier'); ?>" 
                           required>
                    <small class="form-text text-muted"><?= htmlspecialchars($login_identifier_help_text ?? 'Digunakan untuk login.'); ?></small>
                    <?php if (session('errors.login_identifier')): ?>
                        <div class="invalid-feedback"><?= session('errors.login_identifier') ?></div>
                    <?php endif; ?>
                </div>

                <!-- Field Password -->
                <div class="form-group">
                    <label for="password">Password Awal <span class="text-danger">*</span></label>
                    <input type="password" 
                           class="form-control <?= session('errors.password') ? 'is-invalid' : ''; ?>" 
                           id="password" 
                           name="password" 
                           placeholder="Masukkan password (minimal 6 karakter)"
                           required>
                    <small class="form-text text-muted">Minimal 6 karakter. User akan diminta mengganti password ini saat login pertama.</small>
                    <?php if (session('errors.password')): ?>
                        <div class="invalid-feedback"><?= session('errors.password') ?></div>
                    <?php endif; ?>
                </div>

                <!-- Field Confirm Password -->
                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password Awal <span class="text-danger">*</span></label>
                    <input type="password" 
                           class="form-control <?= session('errors.confirm_password') ? 'is-invalid' : ''; ?>" 
                           id="confirm_password" 
                           name="confirm_password" 
                           placeholder="Ulangi password yang sama"
                           required>
                    <?php if (session('errors.confirm_password')): ?>
                        <div class="invalid-feedback"><?= session('errors.confirm_password') ?></div>
                    <?php endif; ?>
                </div>

                <!-- Field Jabatan Petugas (khusus role 3) -->
                <?php if ($role_id_to_add == 3): ?>
                <hr>
                <h6 class="text-muted">Data Detail Spesifik untuk Role Petugas</h6>
                <div class="form-group">
                    <label for="jabatan_petugas">Jabatan Petugas <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control <?= session('errors.jabatan_petugas') ? 'is-invalid' : ''; ?>" 
                           id="jabatan_petugas" 
                           name="jabatan_petugas" 
                           value="<?= old('jabatan_petugas'); ?>" 
                           placeholder="Contoh: Pemeriksa, Kepala Seksi, dll"
                           required>
                    <?php if (session('errors.jabatan_petugas')): ?>
                        <div class="invalid-feedback"><?= session('errors.jabatan_petugas') ?></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan User
                    </button>
                    <a href="<?= base_url('admin/manajemen_user'); ?>" class="btn btn-secondary ml-2">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let isSubmitting = false;

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('tambahUserForm');
    const submitButton = form.querySelector('button[type="submit"]');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    // Password validation
    if (confirmPassword) {
        confirmPassword.addEventListener('input', function() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Password tidak cocok');
                confirmPassword.classList.add('is-invalid');
            } else {
                confirmPassword.setCustomValidity('');
                confirmPassword.classList.remove('is-invalid');
            }
        });
    }
    
    // Form submission with strict double-submit prevention
    form.addEventListener('submit', function(e) {
        // STRICT: Prevent if already submitting
        if (isSubmitting) {
            console.log('Already submitting, prevented');
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        
        console.log('Form submission triggered');
        
        // Basic validation
        const name = document.getElementById('name').value.trim();
        const loginId = document.getElementById('login_identifier').value.trim();
        const pwd = password.value;
        const confirmPwd = confirmPassword.value;
        
        if (!name || !loginId || !pwd || pwd.length < 6 || pwd !== confirmPwd) {
            alert('Mohon lengkapi semua field dengan benar!');
            e.preventDefault();
            return false;
        }
        
        // Mark as submitting
        isSubmitting = true;
        
        // Disable submit button immediately
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        }
        
        // Disable form
        form.style.pointerEvents = 'none';
        form.style.opacity = '0.7';
        
        console.log('Form locked, submitting...');
        return true;
    });
    
    // Prevent multiple clicks on submit button
    if (submitButton) {
        submitButton.addEventListener('click', function(e) {
            if (isSubmitting) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });
    }
});
</script>
<?= $this->endSection() ?>

