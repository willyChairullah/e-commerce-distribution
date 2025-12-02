<?php

/**
 * ProductController - CRUD Products
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';

class ProductController
{
    private $db;
    private $productModel;
    private $categoryModel;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->productModel = new Product($this->db);
        $this->categoryModel = new Category($this->db);
    }

    public function index()
    {
        requireAdmin();
        $products = $this->productModel->getAll();
        require_once __DIR__ . '/../../views/dashboard/product.php';
    }

    public function create()
    {
        requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $photoUrl = '/assets/img/products/default.svg';

            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
                $uploadedPhoto = uploadImage($_FILES['photo'], 'products');
                if ($uploadedPhoto) {
                    $photoUrl = $uploadedPhoto;
                }
            }

            $data = array(
                'name' => sanitize($_POST['name']),
                'price' => floatval($_POST['price']),
                'photo_url' => $photoUrl,
                'category_id' => intval($_POST['category_id'])
            );

            if ($this->productModel->create($data)) {
                $_SESSION['success'] = 'Produk berhasil ditambahkan';
            } else {
                $_SESSION['error'] = 'Gagal menambahkan produk';
            }

            redirect('/dashboard/product');
        }

        $categories = $this->categoryModel->getAll();
        require_once __DIR__ . '/../../views/dashboard/product_form.php';
    }

    public function edit()
    {
        requireAdmin();
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $product = $this->productModel->findById($id);
            $photoUrl = $product['photo_url'];

            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
                $uploadedPhoto = uploadImage($_FILES['photo'], 'products');
                if ($uploadedPhoto) {
                    if ($photoUrl !== '/assets/img/products/default.svg') {
                        deleteImage($photoUrl);
                    }
                    $photoUrl = $uploadedPhoto;
                }
            }

            $data = array(
                'name' => sanitize($_POST['name']),
                'price' => floatval($_POST['price']),
                'photo_url' => $photoUrl,
                'category_id' => intval($_POST['category_id'])
            );

            if ($this->productModel->update($id, $data)) {
                $_SESSION['success'] = 'Produk berhasil diupdate';
            } else {
                $_SESSION['error'] = 'Gagal mengupdate produk';
            }

            redirect('/dashboard/product');
        }

        $product = $this->productModel->findById($id);
        $categories = $this->categoryModel->getAll();
        require_once __DIR__ . '/../../views/dashboard/product_form.php';
    }

    public function delete()
    {
        requireAdmin();
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        $product = $this->productModel->findById($id);
        if ($product && $product['photo_url'] !== '/assets/img/products/default.svg') {
            deleteImage($product['photo_url']);
        }

        if ($this->productModel->delete($id)) {
            $_SESSION['success'] = 'Produk berhasil dihapus';
        } else {
            $_SESSION['error'] = 'Gagal menghapus produk';
        }

        redirect('/dashboard/product');
    }
}
