<?php
$pageTitle = 'Profile';
ob_start();
?>

<div class="profile-page">
    <h2>Profile User</h2>

    <div class="profile-info">
        <p><strong>Nama Lengkap:</strong> <?php echo $user['full_name']; ?></p>
        <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
        <p><strong>Region:</strong> <?php echo $user['region_code']; ?></p>
        <p><strong>Terdaftar sejak:</strong> <?php echo formatDate($user['created_at']); ?></p>
    </div>

    <p class="note">Catatan: Edit profile belum tersedia pada versi ini.</p>

    <a href="<?php echo url('klien'); ?>" class="btn">Kembali ke Beranda</a>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>