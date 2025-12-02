<?php
$pageTitle = 'Daftar Pesanan';
ob_start();
?>

<div class="section">
    <h2>Daftar Pesanan</h2>

    <table class="data-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>User</th>
                <th>Email</th>
                <th>Total</th>
                <th>Tanggal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="6">Tidak ada data</td>
                </tr>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo $order['order_id']; ?></td>
                        <td><?php echo $order['full_name']; ?></td>
                        <td><?php echo $order['email']; ?></td>
                        <td><?php echo formatCurrency($order['total_amount']); ?></td>
                        <td><?php echo formatDate($order['order_date']); ?></td>
                        <td>
                            <a href="<?php echo url('dashboard/order/detail?id=' . $order['order_id']); ?>" class="btn btn-sm">Detail</a>
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