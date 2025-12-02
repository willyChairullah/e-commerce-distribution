<?php
$pageTitle = 'Dashboard';
ob_start();
?>

<div class="section">
    <?php if (isCentralMode()): ?>
        <h2>Dashboard Admin Pusat - Monitoring Semua Region</h2>
        <p style="color: #666; margin-bottom: 20px;">📊 Anda memiliki akses ke data seluruh region</p>
    <?php else: ?>
        <h2>Dashboard Admin Regional - <?php echo getRegionName(); ?></h2>
        <p style="color: #666; margin-bottom: 20px;">📍 Anda mengelola region: <strong><?php echo getRegionName(); ?></strong></p>
    <?php endif; ?>
</div>

<div class="dashboard-stats">
    <div class="stat-card">
        <h3>Total Produk</h3>
        <p class="stat-number"><?php echo $totalProducts; ?></p>
    </div>
    <div class="stat-card">
        <h3>Total Warehouse<?php if (!isCentralMode()): ?> (<?php echo getRegionName(); ?>)<?php endif; ?></h3>
        <p class="stat-number"><?php echo $totalWarehouses; ?></p>
    </div>
    <div class="stat-card">
        <h3>Total Stok</h3>
        <p class="stat-number"><?php echo $totalStock; ?></p>
    </div>
    <div class="stat-card">
        <h3>Total Pesanan<?php if (!isCentralMode()): ?> (<?php echo getRegionName(); ?>)<?php endif; ?></h3>
        <p class="stat-number"><?php echo $totalOrders; ?></p>
    </div>
</div>

<?php if (isCentralMode() && !empty($regionStats)): ?>
<div class="section">
    <h2>Statistik Per Region</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Region</th>
                <th>Warehouses</th>
                <th>Orders</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($regionStats as $code => $stats): ?>
                <tr>
                    <td><strong><?php echo $code; ?></strong> - <?php echo $stats['name']; ?></td>
                    <td><?php echo $stats['warehouses']; ?></td>
                    <td><?php echo $stats['orders']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<div class="section">
    <h2>Selamat Datang di Admin Panel</h2>
    <p>Gunakan menu di samping untuk mengelola produk, warehouse, stok, dan pesanan.</p>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>