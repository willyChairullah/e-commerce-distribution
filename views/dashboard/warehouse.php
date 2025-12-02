<?php
$pageTitle = 'Kelola Warehouse';
ob_start();
?>

<div class="section">
    <div class="section-header">
        <h2>Daftar Warehouse</h2>
        <a href="<?php echo url('dashboard/warehouse/create'); ?>" class="btn btn-primary">Tambah Warehouse</a>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Warehouse</th>
                <th>Region</th>
                <th>Alamat</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($warehouses)): ?>
                <tr>
                    <td colspan="5">Tidak ada data</td>
                </tr>
            <?php else: ?>
                <?php foreach ($warehouses as $warehouse): ?>
                    <tr>
                        <td><?php echo $warehouse['warehouse_id']; ?></td>
                        <td><?php echo $warehouse['warehouse_name']; ?></td>
                        <td><?php echo $warehouse['region_code']; ?></td>
                        <td><?php echo $warehouse['address']; ?></td>
                        <td>
                            <a href="<?php echo url('dashboard/warehouse/edit?id=' . $warehouse['warehouse_id']); ?>" class="btn btn-sm">Edit</a>
                            <a href="<?php echo url('dashboard/warehouse/delete?id=' . $warehouse['warehouse_id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus warehouse ini?')">Hapus</a>
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