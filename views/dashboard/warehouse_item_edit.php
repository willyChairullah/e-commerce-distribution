<?php
$pageTitle = 'Edit Stok';
ob_start();
?>

<div class="section">
    <h2>Edit Stok Inventory</h2>

    <form method="POST" class="form">
        <?php echo csrfField(); ?>

        <div class="form-group">
            <label>Warehouse</label>
            <input type="text" value="<?php echo $item['warehouse_name']; ?>" disabled>
        </div>

        <div class="form-group">
            <label>Produk</label>
            <input type="text" value="<?php echo $item['product_name']; ?>" disabled>
        </div>

        <div class="form-group">
            <label>Jumlah Stok</label>
            <input type="number" name="stock" value="<?php echo $item['stock']; ?>" min="0" required>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="<?php echo url('dashboard/warehouse_item'); ?>" class="btn">Batal</a>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>