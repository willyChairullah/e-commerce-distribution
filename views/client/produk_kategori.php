<?php
$pageTitle = 'Produk - ' . ($category['category_name'] ?? 'Kategori');
ob_start();
?>

<div class="client-home">
    <h2>Produk: <?php echo $category['category_name']; ?></h2>

    <!-- Filter by Category -->
    <div class="category-filter">
        <a href="<?php echo url('klien'); ?>" class="category-link">Semua</a>
        <?php foreach ($categories as $cat): ?>
            <a href="<?php echo url('klien/produk_kategori?id=' . $cat['category_id']); ?>"
                class="category-link <?php echo ($cat['category_id'] == $categoryId) ? 'active' : ''; ?>">
                <?php echo $cat['category_name']; ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Product Grid -->
    <div class="product-grid">
        <?php if (empty($products)): ?>
            <p>Tidak ada produk dalam kategori ini</p>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <img src="<?php echo $product['photo_url']; ?>" alt="<?php echo $product['name']; ?>">
                    <h3><?php echo $product['name']; ?></h3>
                    <p class="category"><?php echo $product['category_name']; ?></p>
                    <p class="price"><?php echo formatCurrency($product['price']); ?></p>
                    <a href="<?php echo url('klien/detil_produk?id=' . $product['product_id']); ?>" class="btn btn-primary">Lihat Detail</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>