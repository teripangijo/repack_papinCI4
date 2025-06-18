<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <h1 class="h3  text-gray-800"> <?= $subtitle; ?></h1>
    <h5>Status: <?php if ($user['is_active'] == 1) {
                    echo "Active";
                } else {
                    echo "Not Active! Please Update Profile Data";
                } ?></h5>
    <div class="card " style="max-width: 540px;">
        <div class="row g-0">
            <div class="col-md-4">
                <img src="<?= base_url('assets') ?>/img/<?= $user['image'] ?>" alt="...">
            </div>
            <div class="col-md-8">
                <div class="card-body">
                    <h5 class="card-title"><?= $user['name'] ?></h5>
                    <p class="card-text"><?= $user['email'] ?></p>
                    <p class="card-text">Member since <?= date('d F Y', $user['date_created']); ?></p>
                    <a href="<?= base_url('user/edit'); ?>" class="btn btn-primary">Update Data</a>

                </div>
            </div>
        </div>
    </div>

</div>
<!-- /.container-fluid -->

</div>
<!-- End of Main Content -->