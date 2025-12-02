<?php

/**
 * CategoryController - CRUD Categories
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Category.php';

class CategoryController
{
    private $db;
    private $categoryModel;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->categoryModel = new Category($this->db);
    }

    public function index()
    {
        requireAdmin();
        $categories = $this->categoryModel->getAll();
        require_once __DIR__ . '/../../views/dashboard/category.php';
    }

    public function create()
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = array(
                'category_name' => sanitize($_POST['category_name'])
            );

            if ($this->categoryModel->create($data)) {
                $_SESSION['success'] = 'Kategori berhasil ditambahkan';
            } else {
                $_SESSION['error'] = 'Gagal menambahkan kategori';
            }

            redirect('/dashboard/category');
        }

        require_once __DIR__ . '/../../views/dashboard/category_form.php';
    }

    public function edit()
    {
        requireAdmin();
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = array(
                'category_name' => sanitize($_POST['category_name'])
            );

            if ($this->categoryModel->update($id, $data)) {
                $_SESSION['success'] = 'Kategori berhasil diupdate';
            } else {
                $_SESSION['error'] = 'Gagal mengupdate kategori';
            }

            redirect('/dashboard/category');
        }

        $category = $this->categoryModel->findById($id);
        require_once __DIR__ . '/../../views/dashboard/category_form.php';
    }

    public function delete()
    {
        requireAdmin();
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($this->categoryModel->delete($id)) {
            $_SESSION['success'] = 'Kategori berhasil dihapus';
        } else {
            $_SESSION['error'] = 'Gagal menghapus kategori';
        }

        redirect('/dashboard/category');
    }
}
