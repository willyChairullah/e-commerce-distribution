<?php

/**
 * ClientController - Client/User Website
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/WarehouseItem.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/User.php';

class ClientController
{
    private $db;
    private $productModel;
    private $categoryModel;
    private $warehouseItemModel;
    private $orderModel;
    private $cartModel;
    private $userModel;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->productModel = new Product($this->db);
        $this->categoryModel = new Category($this->db);
        $this->warehouseItemModel = new WarehouseItem($this->db);
        $this->orderModel = new Order($this->db);
        $this->cartModel = new Cart($this->db);
        $this->userModel = new User($this->db);
    }

    public function index()
    {
        // Get current region from config
        $regionCode = getCurrentRegion();
        
        // Get products filtered by region (if regional mode)
        $products = $this->productModel->getAllWithRegionStock($regionCode);
        $categories = $this->categoryModel->getAll();
        
        require_once __DIR__ . '/../../views/client/index.php';
    }

    public function productByCategory()
    {
        $categoryId = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $regionCode = getCurrentRegion();
        
        // Get products by category filtered by region
        $products = $this->productModel->getByCategoryWithRegionStock($categoryId, $regionCode);
        $categories = $this->categoryModel->getAll();
        $category = $this->categoryModel->findById($categoryId);
        
        require_once __DIR__ . '/../../views/client/produk_kategori.php';
    }

    public function productDetail()
    {
        $productId = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $regionCode = getCurrentRegion();
        
        $product = $this->productModel->findById($productId);
        
        // Get warehouse items filtered by region
        $warehouseItems = $this->warehouseItemModel->getByProduct($productId, $regionCode);
        
        require_once __DIR__ . '/../../views/client/detil_produk.php';
    }

    public function checkout()
    {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $cartItems = $this->cartModel->getByUser($userId);

            if (empty($cartItems)) {
                $_SESSION['error'] = 'Keranjang kosong';
                redirect('/klien/keranjang');
                return;
            }

            // Calculate total
            $totalAmount = 0;
            foreach ($cartItems as $item) {
                $totalAmount += $item['price'] * $item['qty'];
            }

            // Create order
            $orderId = $this->orderModel->create($userId, $totalAmount);

            if ($orderId) {
                // Add order items and reduce stock
                foreach ($cartItems as $item) {
                    $this->orderModel->addOrderItem(
                        $orderId,
                        $item['warehouse_item_id'],
                        $item['qty'],
                        $item['price']
                    );

                    // Reduce warehouse stock
                    $this->warehouseItemModel->reduceStock($item['warehouse_item_id'], $item['qty']);
                }

                // Clear cart
                $this->cartModel->clearUserCart($userId);

                $_SESSION['success'] = 'Pesanan berhasil dibuat';
                redirect('/klien/order_history');
            } else {
                $_SESSION['error'] = 'Gagal membuat pesanan';
                redirect('/klien/keranjang');
            }
        } else {
            $cartItems = $this->cartModel->getByUser($_SESSION['user_id']);
            require_once __DIR__ . '/../../views/client/checkout.php';
        }
    }

    public function orderHistory()
    {
        requireLogin();
        $orders = $this->orderModel->getByUser($_SESSION['user_id']);
        require_once __DIR__ . '/../../views/client/order_history.php';
    }

    public function orderDetail()
    {
        requireLogin();
        $orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $order = $this->orderModel->findById($orderId);

        // Check if order belongs to logged in user
        if ($order['user_id'] != $_SESSION['user_id']) {
            redirect('/klien/order_history');
            return;
        }

        $orderItems = $this->orderModel->getOrderItems($orderId);
        require_once __DIR__ . '/../../views/client/order_detail.php';
    }

    public function profile()
    {
        requireLogin();
        $user = $this->userModel->findById($_SESSION['user_id']);
        require_once __DIR__ . '/../../views/client/profile.php';
    }
}
