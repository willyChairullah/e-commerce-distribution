<?php
$pageTitle = 'Kelola Stok Inventory';
ob_start();
?>

<div class="section">
    <div class="section-header">
        <h2>Stok Inventory per Warehouse</h2>
        <a href="<?php echo url('dashboard/warehouse_item/create'); ?>" class="btn btn-primary">Tambah Stok</a>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Warehouse</th>
                <th>Produk</th>
                <th>Stok</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items)): ?>
                <tr>
                    <td colspan="5">Tidak ada data</td>
                </tr>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo $item['warehouse_item_id']; ?></td>
                        <td><?php echo $item['warehouse_name']; ?></td>
                        <td><?php echo $item['product_name']; ?></td>
                        <td><?php echo $item['stock']; ?></td>
                        <td>
                            <a href="<?php echo url('dashboard/warehouse_item/edit?id=' . $item['warehouse_item_id']); ?>" class="btn btn-sm">Edit</a>
                            <a href="<?php echo url('dashboard/warehouse_item/delete?id=' . $item['warehouse_item_id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus stok ini?')">Hapus</a>
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