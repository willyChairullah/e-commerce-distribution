<?php
$pageTitle = 'Detail Pesanan';
ob_start();
?>

<div class="order-detail">
    <h2>Detail Pesanan #<?php echo $order['order_id']; ?></h2>

    <div class="order-info">
        <p><strong>Tanggal:</strong> <?php echo formatDate($order['order_date']); ?></p>
        <p><strong>Total:</strong> <?php echo formatCurrency($order['total_amount']); ?></p>
    </div>

    <h3>Item Pesanan</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Produk</th>
                <th>Warehouse</th>
                <th>Harga</th>
                <th>Qty</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orderItems as $item): ?>
                <tr>
                    <td><?php echo $item['product_name']; ?></td>
                    <td><?php echo $item['warehouse_name']; ?></td>
                    <td><?php echo formatCurrency($item['price_at_order']); ?></td>
                    <td><?php echo $item['qty']; ?></td>
                    <td><?php echo formatCurrency($item['price_at_order'] * $item['qty']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="<?php echo url('klien/order_history'); ?>" class="btn">Kembali</a>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>