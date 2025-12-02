<?php
$pageTitle = 'Riwayat Pesanan';
ob_start();
?>

<div class="order-history">
    <h2>Riwayat Pesanan</h2>

    <?php if (empty($orders)): ?>
        <p>Anda belum memiliki pesanan</p>
        <a href="<?php echo url('klien'); ?>" class="btn">Mulai Belanja</a>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Tanggal</th>
                    <th>Total</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['order_id']; ?></td>
                        <td><?php echo formatDate($order['order_date']); ?></td>
                        <td><?php echo formatCurrency($order['total_amount']); ?></td>
                        <td>
                            <a href="<?php echo url('klien/order_detail?id=' . $order['order_id']); ?>" class="btn btn-sm">Detail</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>