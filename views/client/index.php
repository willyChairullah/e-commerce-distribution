<?php

$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$pageTitle = 'Beranda';
ob_start();
?>

<div class="client-home">
    <h2>Katalog Produk</h2>

    <!-- Filter by Category -->
    <div class="category-filter">
        <a href="<?php echo url('klien'); ?>" class="category-link">Semua</a>
        <?php foreach ($categories as $cat): ?>
            <a href="<?php echo url('klien/produk_kategori?id=' . $cat['category_id']); ?>" class="category-link">
                <?php echo $cat['category_name']; ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Product Grid -->
    <div class="product-grid">
        <?php if (empty($products)): ?>
            <p>Tidak ada produk tersedia</p>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <img src="<?php echo $basePath . $product['photo_url']; ?>" alt="<?php echo $product['product_name']; ?>">
                    <h3><?php echo $product['product_name']; ?></h3>
                    <p class="category"><?php echo $product['category_name']; ?></p>
                    <p class="price"><?php echo formatCurrency($product['price']); ?></p>

                    <?php if (!isCentralMode()): ?>
                        <?php if (isset($product['total_stock']) && $product['total_stock'] > 0): ?>
                            <p style="color: #4caf50; font-size: 13px; margin: 8px 0;">
                                ✅ Tersedia di <?php echo getRegionName(); ?> (Stok: <?php echo $product['total_stock']; ?>)
                            </p>
                            <a href="<?php echo url('klien/detil_produk?id=' . $product['product_id']); ?>" class="btn btn-primary">Lihat Detail</a>
                        <?php else: ?>
                            <p style="color: #999; font-size: 13px; margin: 8px 0;">
                                ❌ Tidak tersedia di region ini
                            </p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>