<?php

/**
 * WarehouseController - CRUD Warehouses
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Warehouse.php';

class WarehouseController
{
    private $db;
    private $warehouseModel;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->warehouseModel = new Warehouse($this->db);
    }

    public function index()
    {
        requireAdmin();
        
        // Get warehouses filtered by region (null for central mode)
        $regionCode = getCurrentRegion();
        $warehouses = $this->warehouseModel->getAllByRegion($regionCode);
        
        require_once __DIR__ . '/../../views/dashboard/warehouse.php';
    }

    public function create()
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = array(
                'warehouse_name' => sanitize($_POST['warehouse_name']),
                'region_code' => sanitize($_POST['region_code']),
                'address' => sanitize($_POST['address'])
            );

            if ($this->warehouseModel->create($data)) {
                $_SESSION['success'] = 'Warehouse berhasil ditambahkan';
            } else {
                $_SESSION['error'] = 'Gagal menambahkan warehouse';
            }

            redirect('/dashboard/warehouse');
        }

        require_once __DIR__ . '/../../views/dashboard/warehouse_form.php';
    }

    public function edit()
    {
        requireAdmin();
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = array(
                'warehouse_name' => sanitize($_POST['warehouse_name']),
                'region_code' => sanitize($_POST['region_code']),
                'address' => sanitize($_POST['address'])
            );

            if ($this->warehouseModel->update($id, $data)) {
                $_SESSION['success'] = 'Warehouse berhasil diupdate';
            } else {
                $_SESSION['error'] = 'Gagal mengupdate warehouse';
            }

            redirect('/dashboard/warehouse');
        }

        $warehouse = $this->warehouseModel->findById($id);
        require_once __DIR__ . '/../../views/dashboard/warehouse_form.php';
    }

    public function delete()
    {
        requireAdmin();
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($this->warehouseModel->delete($id)) {
            $_SESSION['success'] = 'Warehouse berhasil dihapus';
        } else {
            $_SESSION['error'] = 'Gagal menghapus warehouse';
        }

        redirect('/dashboard/warehouse');
    }
}
