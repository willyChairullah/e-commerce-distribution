<?php
$pageTitle = 'Daftar User';
ob_start();
?>

<div class="section">
    <h2>Daftar User Terdaftar</h2>

    <table class="data-table">
        <thead>
            <tr>
                <th>User ID</th>
                <th>Nama Lengkap</th>
                <th>Email</th>
                <th>Region</th>
                <th>Role</th>
                <th>Terdaftar</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="6">Tidak ada data</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['user_id']; ?></td>
                        <td><?php echo $user['full_name']; ?></td>
                        <td><?php echo $user['email']; ?></td>
                        <td><?php echo $user['region_code']; ?></td>
                        <td><?php echo $user['is_admin'] == 1 ? 'Admin' : 'User'; ?></td>
                        <td><?php echo formatDate($user['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>