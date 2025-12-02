<?php

/**
 * WarehouseItemController - Manage Stock in Warehouses
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/WarehouseItem.php';
require_once __DIR__ . '/../models/Warehouse.php';
require_once __DIR__ . '/../models/Product.php';

class WarehouseItemController
{
    private $db;
    private $warehouseItemModel;
    private $warehouseModel;
    private $productModel;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->warehouseItemModel = new WarehouseItem($this->db);
        $this->warehouseModel = new Warehouse($this->db);
        $this->productModel = new Product($this->db);
    }

    public function index()
    {
        requireAdmin();
        $items = $this->warehouseItemModel->getAll();
        require_once __DIR__ . '/../../views/dashboard/warehouse_item.php';
    }

    public function create()
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = array(
                'warehouse_id' => intval($_POST['warehouse_id']),
                'product_id' => intval($_POST['product_id']),
                'stock' => intval($_POST['stock'])
            );

            if ($this->warehouseItemModel->create($data)) {
                $_SESSION['success'] = 'Stok berhasil ditambahkan';
            } else {
                $_SESSION['error'] = 'Gagal menambahkan stok';
            }

            redirect('/dashboard/warehouse_item');
        }

        $warehouses = $this->warehouseModel->getAll();
        $products = $this->productModel->getAll();
        require_once __DIR__ . '/../../views/dashboard/warehouse_item_form.php';
    }

    public function edit()
    {
        requireAdmin();
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stock = intval($_POST['stock']);

            if ($this->warehouseItemModel->updateStock($id, $stock)) {
                $_SESSION['success'] = 'Stok berhasil diupdate';
            } else {
                $_SESSION['error'] = 'Gagal mengupdate stok';
            }

            redirect('/dashboard/warehouse_item');
        }

        $item = $this->warehouseItemModel->findById($id);
        require_once __DIR__ . '/../../views/dashboard/warehouse_item_edit.php';
    }

    public function delete()
    {
        requireAdmin();
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($this->warehouseItemModel->delete($id)) {
            $_SESSION['success'] = 'Stok berhasil dihapus';
        } else {
            $_SESSION['error'] = 'Gagal menghapus stok';
        }

        redirect('/dashboard/warehouse_item');
    }
}
