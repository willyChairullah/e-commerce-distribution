<?php

/**
 * Routes Configuration
 * Maps URL patterns to Controllers
 */

function route($url)
{
    // Remove trailing slash
    $url = rtrim($url, '/');

    // Parse URL
    $segments = $url ? explode('/', $url) : [];

    // Default route
    if (empty($segments)) {
        redirect('/klien');
        return;
    }

    $section = $segments[0];
    $controller = isset($segments[1]) ? $segments[1] : null;
    $action = isset($segments[2]) ? $segments[2] : null;

    // Authentication Routes
    if ($section === 'login') {
        require_once __DIR__ . '/app/controllers/AuthController.php';
        $ctrl = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->login();
        } else {
            $ctrl->showLogin();
        }
        return;
    }

    if ($section === 'register') {
        require_once __DIR__ . '/app/controllers/AuthController.php';
        $ctrl = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctrl->register();
        } else {
            $ctrl->showRegister();
        }
        return;
    }

    if ($section === 'logout') {
        require_once __DIR__ . '/app/controllers/AuthController.php';
        $ctrl = new AuthController();
        $ctrl->logout();
        return;
    }

    // Dashboard Routes (Admin)
    if ($section === 'dashboard') {
        if ($controller === null) {
            require_once __DIR__ . '/app/controllers/DashboardController.php';
            $ctrl = new DashboardController();
            $ctrl->index();
            return;
        }

        switch ($controller) {
            case 'product':
                require_once __DIR__ . '/app/controllers/ProductController.php';
                $ctrl = new ProductController();
                if ($action === 'create') {
                    $ctrl->create();
                } elseif ($action === 'edit') {
                    $ctrl->edit();
                } elseif ($action === 'delete') {
                    $ctrl->delete();
                } else {
                    $ctrl->index();
                }
                break;

            case 'category':
                require_once __DIR__ . '/app/controllers/CategoryController.php';
                $ctrl = new CategoryController();
                if ($action === 'create') {
                    $ctrl->create();
                } elseif ($action === 'edit') {
                    $ctrl->edit();
                } elseif ($action === 'delete') {
                    $ctrl->delete();
                } else {
                    $ctrl->index();
                }
                break;

            case 'warehouse':
                require_once __DIR__ . '/app/controllers/WarehouseController.php';
                $ctrl = new WarehouseController();
                if ($action === 'create') {
                    $ctrl->create();
                } elseif ($action === 'edit') {
                    $ctrl->edit();
                } elseif ($action === 'delete') {
                    $ctrl->delete();
                } else {
                    $ctrl->index();
                }
                break;

            case 'warehouse_item':
                require_once __DIR__ . '/app/controllers/WarehouseItemController.php';
                $ctrl = new WarehouseItemController();
                if ($action === 'create') {
                    $ctrl->create();
                } elseif ($action === 'edit') {
                    $ctrl->edit();
                } elseif ($action === 'delete') {
                    $ctrl->delete();
                } else {
                    $ctrl->index();
                }
                break;

            case 'order':
                require_once __DIR__ . '/app/controllers/OrderController.php';
                $ctrl = new OrderController();
                if ($action === 'detail') {
                    $ctrl->detail();
                } else {
                    $ctrl->index();
                }
                break;

            case 'user':
                require_once __DIR__ . '/app/controllers/OrderController.php';
                $ctrl = new OrderController();
                $ctrl->userList();
                break;

            case 'report':
                require_once __DIR__ . '/app/controllers/OrderController.php';
                $ctrl = new OrderController();
                $ctrl->report();
                break;

            default:
                echo "404 - Page Not Found";
                break;
        }
        return;
    }

    // Client Routes (User Website)
    if ($section === 'klien') {
        require_once __DIR__ . '/app/controllers/ClientController.php';
        $ctrl = new ClientController();

        if ($controller === null) {
            $ctrl->index();
            return;
        }

        switch ($controller) {
            case 'produk_kategori':
                $ctrl->productByCategory();
                break;

            case 'detil_produk':
                $ctrl->productDetail();
                break;

            case 'keranjang':
                require_once __DIR__ . '/app/controllers/CartController.php';
                $cartCtrl = new CartController();
                $cartCtrl->index();
                break;

            case 'cart':
                require_once __DIR__ . '/app/controllers/CartController.php';
                $cartCtrl = new CartController();
                if ($action === 'add') {
                    $cartCtrl->add();
                } elseif ($action === 'update') {
                    $cartCtrl->updateQuantity();
                } elseif ($action === 'delete') {
                    $cartCtrl->delete();
                }
                break;

            case 'checkout':
                $ctrl->checkout();
                break;

            case 'order_history':
                $ctrl->orderHistory();
                break;

            case 'order_detail':
                $ctrl->orderDetail();
                break;

            case 'profile':
                $ctrl->profile();
                break;

            default:
                echo "404 - Page Not Found";
                break;
        }
        return;
    }

    // 404 Not Found
    echo "404 - Page Not Found";
}
