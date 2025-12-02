<?php
$pageTitle = 'Keranjang Belanja';
ob_start();
?>

<div class="cart-page">
    <h2>Keranjang Belanja</h2>

    <?php if (empty($cartItems)): ?>
        <p>Keranjang Anda kosong</p>
        <a href="<?php echo url('klien'); ?>" class="btn">Belanja Sekarang</a>
    <?php else: ?>
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Warehouse</th>
                    <th>Harga</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                    <th>Aksi</th>
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
                        <td>
                            <img src="<?php echo $item['photo_url']; ?>" alt="<?php echo $item['product_name']; ?>" width="50">
                            <?php echo $item['product_name']; ?>
                        </td>
                        <td><?php echo $item['warehouse_name']; ?> (<?php echo $item['region_code']; ?>)</td>
                        <td><?php echo formatCurrency($item['price']); ?></td>
                        <td>
                            <form method="POST" action="<?php echo url('klien/cart/update'); ?>" style="display:inline;">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">
                                <input type="number" name="qty" value="<?php echo $item['qty']; ?>" min="1" max="<?php echo $item['stock']; ?>" style="width:60px;">
                                <button type="submit" class="btn btn-sm">Update</button>
                            </form>
                        </td>
                        <td><?php echo formatCurrency($subtotal); ?></td>
                        <td>
                            <a href="<?php echo url('klien/cart/delete?id=' . $item['cart_item_id']); ?>"
                                class="btn btn-sm btn-danger"
                                onclick="return confirm('Hapus item ini?')">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align:right;"><strong>Total:</strong></td>
                    <td colspan="2"><strong><?php echo formatCurrency($total); ?></strong></td>
                </tr>
            </tfoot>
        </table>

        <div class="cart-actions">
            <a href="<?php echo url('klien'); ?>" class="btn">Lanjut Belanja</a>
            <a href="<?php echo url('klien/checkout'); ?>" class="btn btn-primary">Checkout</a>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>