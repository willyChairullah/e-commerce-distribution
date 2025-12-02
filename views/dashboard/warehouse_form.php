<?php
$pageTitle = isset($warehouse) ? 'Edit Warehouse' : 'Tambah Warehouse';
ob_start();
?>

<div class="section">
    <h2><?php echo $pageTitle; ?></h2>

    <form method="POST" class="form">
        <?php echo csrfField(); ?>

        <div class="form-group">
            <label>Nama Warehouse</label>
            <input type="text" name="warehouse_name" value="<?php echo isset($warehouse) ? $warehouse['warehouse_name'] : ''; ?>" required>
        </div>

        <div class="form-group">
            <label>Region Code</label>
            <input type="text" name="region_code" value="<?php echo isset($warehouse) ? $warehouse['region_code'] : ''; ?>" required>
        </div>

        <div class="form-group">
            <label>Alamat</label>
            <textarea name="address" rows="4" required><?php echo isset($warehouse) ? $warehouse['address'] : ''; ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="<?php echo url('dashboard/warehouse'); ?>" class="btn">Batal</a>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>