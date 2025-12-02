<?php

/**
 * CartController - Manage Shopping Cart
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/WarehouseItem.php';

class CartController
{
    private $db;
    private $cartModel;
    private $warehouseItemModel;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->cartModel = new Cart($this->db);
        $this->warehouseItemModel = new WarehouseItem($this->db);
    }

    public function index()
    {
        requireLogin();
        $cartItems = $this->cartModel->getByUser($_SESSION['user_id']);
        require_once __DIR__ . '/../../views/client/keranjang.php';
    }

    public function add()
    {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $warehouseItemId = sanitize($_POST['warehouse_item_id']); // VARCHAR now
            $qty = intval($_POST['qty']);

            // Check stock availability
            if (!$this->warehouseItemModel->checkStock($warehouseItemId, $qty)) {
                $_SESSION['error'] = 'Stok tidak mencukupi';
                redirect($_SERVER['HTTP_REFERER']);
                return;
            }

            $data = array(
                'user_id' => $_SESSION['user_id'],
                'warehouse_item_id' => $warehouseItemId,
                'qty' => $qty
            );

            if ($this->cartModel->add($data)) {
                $_SESSION['success'] = 'Produk ditambahkan ke keranjang';
            } else {
                $_SESSION['error'] = 'Gagal menambahkan ke keranjang';
            }

            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function updateQuantity()
    {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cartItemId = sanitize($_POST['cart_item_id']); // VARCHAR now
            $qty = intval($_POST['qty']);

            if ($qty <= 0) {
                $this->cartModel->delete($cartItemId);
            } else {
                $this->cartModel->updateQuantity($cartItemId, $qty);
            }

            redirect('/klien/keranjang');
        }
    }

    public function delete()
    {
        requireLogin();
        $id = isset($_GET['id']) ? sanitize($_GET['id']) : '';

        if (empty($id)) {
            $_SESSION['error'] = 'ID tidak valid';
            redirect('/klien/keranjang');
            return;
        }

        if ($this->cartModel->delete($id)) {
            $_SESSION['success'] = 'Item dihapus dari keranjang';
        } else {
            $_SESSION['error'] = 'Gagal menghapus item';
        }

        redirect('/klien/keranjang');
    }
}
