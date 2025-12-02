<?php
$pageTitle = 'Laporan';
ob_start();
?>

<div class="section">
    <h2>Laporan Pesanan Bulanan</h2>

    <table class="data-table">
        <thead>
            <tr>
                <th>Tahun</th>
                <th>Bulan</th>
                <th>Total Pesanan</th>
                <th>Total Revenue</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($monthlyOrders)): ?>
                <tr>
                    <td colspan="4">Tidak ada data</td>
                </tr>
            <?php else: ?>
                <?php foreach ($monthlyOrders as $report): ?>
                    <tr>
                        <td><?php echo $report['year']; ?></td>
                        <td><?php echo $report['month']; ?></td>
                        <td><?php echo $report['total_orders']; ?></td>
                        <td><?php echo formatCurrency($report['total_revenue']); ?></td>
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