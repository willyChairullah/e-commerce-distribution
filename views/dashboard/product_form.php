<?php
$pageTitle = isset($product) ? 'Edit Produk' : 'Tambah Produk';
ob_start();
?>

<div class="section">
    <h2><?php echo $pageTitle; ?></h2>

    <form method="POST" enctype="multipart/form-data" class="form">
        <?php echo csrfField(); ?>

        <div class="form-group">
            <label>Nama Produk</label>
            <input type="text" name="name" value="<?php echo isset($product) ? $product['name'] : ''; ?>" required>
        </div>

        <div class="form-group">
            <label>Kategori</label>
            <select name="category_id" required>
                <option value="">Pilih Kategori</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['category_id']; ?>"
                        <?php echo (isset($product) && $product['category_id'] == $category['category_id']) ? 'selected' : ''; ?>>
                        <?php echo $category['category_name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Harga</label>
            <input type="number" name="price" value="<?php echo isset($product) ? $product['price'] : ''; ?>" required>
        </div>

        <div class="form-group">
            <label>Foto Produk</label>
            <?php if (isset($product) && $product['photo_url']): ?>
                <img src="<?php echo $product['photo_url']; ?>" alt="<?php echo $product['name']; ?>" width="100" style="display:block; margin-bottom:10px;">
            <?php endif; ?>
            <input type="file" name="photo" accept="image/*">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="<?php echo url('dashboard/product'); ?>" class="btn">Batal</a>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>