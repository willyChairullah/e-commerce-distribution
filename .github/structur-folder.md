project/
│
├── config/
│   ├── database.php         # koneksi SQL Server
│
├── public/
│   ├── index.php            # router / redirect
│   ├── assets/
│   │   ├── css/
│   │   ├── img/
│   │   └── js/
│
├── app/
│   ├── controllers/
│   │   ├── ProductController.php
│   │   ├── CategoryController.php
│   │   ├── WarehouseController.php
│   │   ├── WarehouseItemController.php
│   │   ├── OrderController.php
│   │   ├── CartController.php
│   │   └── AuthController.php
│   │
│   ├── models/
│   │   ├── Product.php
│   │   ├── Category.php
│   │   ├── Warehouse.php
│   │   ├── WarehouseItem.php
│   │   ├── Cart.php
│   │   ├── Order.php
│   │   └── User.php
│
├── views/
│   ├── dashboard/
│   │   ├── index.php
│   │   ├── product.php
│   │   ├── category.php
│   │   ├── warehouse.php
│   │   ├── warehouse_item.php
│   │   ├── order.php
│   │   ├── report.php
│   │   └── user.php
│   │
│   ├── client/
│   │   ├── index.php
│   │   ├── produk_kategori.php
│   │   ├── detil_produk.php
│   │   ├── keranjang.php
│   │   ├── checkout.php
│   │   ├── order_history.php
│   │   └── profile.php
│
├── helpers/
│   ├── csrf.php
│   ├── util.php
│
├── routes.php         # mapping url ke controller
└── .htaccess (opsional untuk routing)