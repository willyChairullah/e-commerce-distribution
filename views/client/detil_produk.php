<?php

$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$pageTitle = $product['product_name'];
ob_start();
?>

<div class="product-detail">
    <div class="product-detail-main">
        <div class="product-detail">
            <img src="<?php echo $basePath . $product['photo_url']; ?>" alt="<?php echo $product['product_name']; ?>">
        </div>

        <div class="product-info">
            <h2><?php echo $product['product_name']; ?></h2>
            <p class="category">Kategori: <?php echo $product['category_name']; ?></p>
            <p class="price"><?php echo formatCurrency($product['price']); ?></p>

            <h3>Stok Tersedia<?php if (!isCentralMode()): ?> di <?php echo getRegionName(); ?><?php endif; ?>:</h3>
            <?php if (empty($warehouseItems)): ?>
                <p class="out-of-stock">
                    ❌ Stok tidak tersedia<?php if (!isCentralMode()): ?> di region <?php echo getRegionName(); ?><?php endif; ?>
                </p>
            <?php else: ?>
                <table class="stock-table">
                    <thead>
                        <tr>
                            <th>Warehouse</th>
                            <th>Region</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($warehouseItems as $item): ?>
                            <tr>
                                <td><?php echo $item['warehouse_name']; ?></td>
                                <td><?php echo $item['region_code']; ?></td>
                                <td><?php echo $item['stock']; ?></td>
                                <td>
                                    <?php if (isLoggedIn()): ?>
                                        <form method="POST" action="<?php echo url('klien/cart/add'); ?>" style="display:inline;">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="warehouse_item_id" value="<?php echo $item['warehouse_item_id']; ?>">
                                            <input type="number" name="qty" value="1" min="1" max="<?php echo $item['stock']; ?>" style="width:60px;">
                                            <button type="submit" class="btn btn-primary">Tambah ke Keranjang</button>
                                        </form>
                                    <?php else: ?>
                                        <a href="<?php echo url('login'); ?>" class="btn">Login untuk membeli</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <a href="<?php echo url('klien'); ?>" class="btn">Kembali ke Katalog</a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>