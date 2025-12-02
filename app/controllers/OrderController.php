<?php

/**
 * OrderController - Manage Orders
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/User.php';

class OrderController
{
    private $db;
    private $orderModel;
    private $userModel;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->orderModel = new Order($this->db);
        $this->userModel = new User($this->db);
    }

    public function index()
    {
        requireAdmin();
        
        // Get orders filtered by region (null for central mode)
        $regionCode = getCurrentRegion();
        $orders = $this->orderModel->getAll($regionCode);
        
        require_once __DIR__ . '/../../views/dashboard/order.php';
    }

    public function detail()
    {
        requireAdmin();
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $order = $this->orderModel->findById($id);
        $orderItems = $this->orderModel->getOrderItems($id);
        require_once __DIR__ . '/../../views/dashboard/order_detail.php';
    }

    public function report()
    {
        requireAdmin();
        $monthlyOrders = $this->orderModel->getMonthlyOrders();
        require_once __DIR__ . '/../../views/dashboard/report.php';
    }

    public function userList()
    {
        requireAdmin();
        $users = $this->userModel->getAll();
        require_once __DIR__ . '/../../views/dashboard/user.php';
    }
}
