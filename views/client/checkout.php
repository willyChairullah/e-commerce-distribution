<?php
$pageTitle = 'Checkout';
ob_start();
?>

<div class="checkout-page">
    <h2>Checkout</h2>

    <?php if (empty($cartItems)): ?>
        <p>Keranjang Anda kosong</p>
        <a href="/klien" class="btn">Belanja Sekarang</a>
    <?php else: ?>
        <h3>Review Pesanan Anda</h3>
        <table class="cart-table">
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
                <?php
                $total = 0;
                foreach ($cartItems as $item):
                    $subtotal = $item['price'] * $item['qty'];
                    $total += $subtotal;
                ?>
                    <tr>
                        <td><?php echo $item['product_name']; ?></td>
                        <td><?php echo $item['warehouse_name']; ?></td>
                        <td><?php echo formatCurrency($item['price']); ?></td>
                        <td><?php echo $item['qty']; ?></td>
                        <td><?php echo formatCurrency($subtotal); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align:right;"><strong>Total:</strong></td>
                    <td><strong><?php echo formatCurrency($total); ?></strong></td>
                </tr>
            </tfoot>
        </table>

        <div class="checkout-actions">
            <p><strong>Catatan:</strong> Ini adalah checkout sederhana. Pesanan akan langsung dibuat dan stok akan berkurang.</p>
            <form method="POST" action="<?php echo url('klien/checkout'); ?>">
                <?php echo csrfField(); ?>
                <button type="submit" class="btn btn-primary" onclick="return confirm('Konfirmasi pesanan ini?')">Bayar Sekarang</button>
                <a href="/klien/keranjang" class="btn">Kembali ke Keranjang</a>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>