<?php
$pageTitle = 'Kelola Produk';
ob_start();
?>

<div class="section">
    <div class="section-header">
        <h2>Daftar Produk</h2>
        <a href="<?php echo url('dashboard/product/create'); ?>" class="btn btn-primary">Tambah Produk</a>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Foto</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th>Harga</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($products)): ?>
                <tr>
                    <td colspan="6">Tidak ada data</td>
                </tr>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo $product['product_id']; ?></td>
                        <td><img src="<?php echo $product['photo_url']; ?>" alt="<?php echo $product['name']; ?>" width="50"></td>
                        <td><?php echo $product['name']; ?></td>
                        <td><?php echo $product['category_name']; ?></td>
                        <td><?php echo formatCurrency($product['price']); ?></td>
                        <td>
                            <a href="<?php echo url('dashboard/product/edit?id=' . $product['product_id']); ?>" class="btn btn-sm">Edit</a>
                            <a href="<?php echo url('dashboard/product/delete?id=' . $product['product_id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus produk ini?')">Hapus</a>
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