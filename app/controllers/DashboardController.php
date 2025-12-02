<?php

/**
 * DashboardController - Admin Dashboard
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Warehouse.php';
require_once __DIR__ . '/../models/WarehouseItem.php';
require_once __DIR__ . '/../models/Order.php';

class DashboardController
{
    private $db;
    private $productModel;
    private $warehouseModel;
    private $warehouseItemModel;
    private $orderModel;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->productModel = new Product($this->db);
        $this->warehouseModel = new Warehouse($this->db);
        $this->warehouseItemModel = new WarehouseItem($this->db);
        $this->orderModel = new Order($this->db);
    }

    public function index()
    {
        requireAdmin();

        // Get current region for filtering (null for central mode)
        $regionCode = getCurrentRegion();
        $isCentral = isCentralMode();

        // Get statistics filtered by region
        $totalProducts = $this->productModel->getTotalProducts();
        $totalWarehouses = $this->warehouseModel->getTotalWarehouses($regionCode);
        $totalStock = $this->warehouseItemModel->getTotalStock();
        $totalOrders = $this->orderModel->getTotalOrders($regionCode);

        // If central mode, get stats per region
        $regionStats = null;
        if ($isCentral) {
            require_once __DIR__ . '/../../config/app.php';
            $regionStats = array();
            foreach (AVAILABLE_REGIONS as $code => $name) {
                $regionStats[$code] = array(
                    'name' => $name,
                    'warehouses' => $this->warehouseModel->getTotalWarehouses($code),
                    'orders' => $this->orderModel->getTotalOrders($code)
                );
            }
        }

        require_once __DIR__ . '/../../views/dashboard/index.php';
    }
}
