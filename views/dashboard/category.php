<?php
$pageTitle = 'Kelola Kategori';
ob_start();
?>

<div class="section">
    <div class="section-header">
        <h2>Daftar Kategori</h2>
        <a href="<?php echo url('dashboard/category/create'); ?>" class="btn btn-primary">Tambah Kategori</a>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Kategori</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($categories)): ?>
                <tr>
                    <td colspan="3">Tidak ada data</td>
                </tr>
            <?php else: ?>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?php echo $category['category_id']; ?></td>
                        <td><?php echo $category['category_name']; ?></td>
                        <td>
                            <a href="<?php echo url('dashboard/category/edit?id=' . $category['category_id']); ?>" class="btn btn-sm">Edit</a>
                            <a href="<?php echo url('dashboard/category/delete?id=' . $category['category_id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus kategori ini?')">Hapus</a>
                        </td>
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