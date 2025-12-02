<?php
$pageTitle = isset($category) ? 'Edit Kategori' : 'Tambah Kategori';
ob_start();
?>

<div class="section">
    <h2><?php echo $pageTitle; ?></h2>

    <form method="POST" class="form">
        <?php echo csrfField(); ?>

        <div class="form-group">
            <label>Nama Kategori</label>
            <input type="text" name="category_name" value="<?php echo isset($category) ? $category['category_name'] : ''; ?>" required>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="<?php echo url('dashboard/category'); ?>" class="btn">Batal</a>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>