<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
    Ubah Password
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$user_to_change = isset($user_for_pass_change) ? $user_for_pass_change : null;
$user_id_to_change = isset($user_to_change['id']) ? $user_to_change['id'] : null;
$user_name_to_change = isset($user_to_change['name']) ? htmlspecialchars($user_to_change['name']) : 'N/A';
$user_email_to_change = isset($user_to_change['email']) ? htmlspecialchars($user_to_change['email']) : 'N/A';

// Get the redirect URL or default to user dashboard
$cancel_url = isset($cancel_redirect_url) ? $cancel_redirect_url : 'user';

// Check if current user is changing their own password
$is_own_password = ($user_id_to_change == session()->get('user_id'));

if ($user_id_to_change === null) {
    echo '<div class="alert alert-danger">Error: User ID for password change is missing.</div>';
    return;
}

// Get validation service properly
$validationService = isset($validation) ? $validation : \Config\Services::validation();
?>

<div class="container">
    <div class="card o-hidden border-0 shadow-lg my-5 col-lg-7 mx-auto">
        <div class="card-body p-0">
            <div class="row">
                <div class="col-lg">
                    <div class="p-5">
                        <div class="text-center">
                            <h1 class="h4 text-gray-900 mb-4">
                                <?= $is_own_password ? 'Change Your Password' : 'Change Password for ' . $user_name_to_change; ?>
                            </h1>
                        </div>

                        <?php if (session()->getFlashdata('message')): ?>
                            <?= session()->getFlashdata('message'); ?>
                        <?php endif; ?>

                        <form class="user" method="post" action="<?= site_url('auth/changepass/' . $user_id_to_change); ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="user_id" value="<?= $user_id_to_change; ?>">
                            
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" class="form-control form-control-user" id="name" name="name" placeholder="Full Name" value="<?= $user_name_to_change; ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="text" class="form-control form-control-user" id="email" name="email" placeholder="Email Address" value="<?= $user_email_to_change; ?>" readonly>
                            </div>
                            <hr>
                            
                            <?php if ($is_own_password): ?>
                            <div class="form-group">
                                <label for="current_password">Current Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control form-control-user <?= ($validationService->hasError('current_password')) ? 'is-invalid' : ''; ?>"
                                       id="current_password" name="current_password" placeholder="Current Password">
                                <?php if($validationService->hasError('current_password')): ?>
                                    <div class="invalid-feedback">
                                        <?= $validationService->getError('current_password'); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <hr>
                            <?php endif; ?>
                            
                            <div class="form-group row">
                                <div class="col-sm-6 mb-3 mb-sm-0">
                                    <label for="password">New Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control form-control-user <?= ($validationService->hasError('password')) ? 'is-invalid' : ''; ?>"
                                           id="password" name="password" placeholder="New Password">
                                    <?php if($validationService->hasError('password')): ?>
                                        <div class="invalid-feedback">
                                            <?= $validationService->getError('password'); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-sm-6">
                                    <label for="password2">Repeat New Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control form-control-user <?= ($validationService->hasError('password2')) ? 'is-invalid' : ''; ?>"
                                           id="password2" name="password2" placeholder="Repeat Password">
                                    <?php if($validationService->hasError('password2')): ?>
                                        <div class="invalid-feedback">
                                            <?= $validationService->getError('password2'); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-user btn-block">
                                Change Password
                            </button>
                            <hr>
                        </form>
                        <hr>
                        <div class="text-center">
                            <a class="btn btn-secondary btn-sm" href="<?= site_url($cancel_url); ?>">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
