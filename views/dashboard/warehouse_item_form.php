<?php
$pageTitle = 'Tambah Stok';
ob_start();
?>

<div class="section">
    <h2>Tambah Stok Inventory</h2>

    <form method="POST" class="form">
        <?php echo csrfField(); ?>

        <div class="form-group">
            <label>Warehouse</label>
            <select name="warehouse_id" required>
                <option value="">Pilih Warehouse</option>
                <?php foreach ($warehouses as $warehouse): ?>
                    <option value="<?php echo $warehouse['warehouse_id']; ?>">
                        <?php echo $warehouse['warehouse_name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Produk</label>
            <select name="product_id" required>
                <option value="">Pilih Produk</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?php echo $product['product_id']; ?>">
                        <?php echo $product['product_name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Jumlah Stok</label>
            <input type="number" name="stock" min="0" required>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="<?php echo url('dashboard/warehouse_item'); ?>" class="btn">Batal</a>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>