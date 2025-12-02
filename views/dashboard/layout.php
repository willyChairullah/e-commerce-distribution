<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?> - Admin</title>
    <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>">
</head>

<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Admin Panel</h2>
                <?php if (!isCentralMode()): ?>
                    <p style="font-size: 12px; color: #999; margin-top: 5px;">📍 Region: <?php echo getRegionName(); ?></p>
                <?php else: ?>
                    <p style="font-size: 12px; color: #999; margin-top: 5px;">🌐 Mode: Pusat</p>
                <?php endif; ?>
            </div>
            <nav class="sidebar-menu">
                <a href="<?php echo url('dashboard'); ?>" class="menu-item">
                    <?php echo isCentralMode() ? 'Dashboard Global' : 'Dashboard Regional'; ?>
                </a>
                
                <?php if (isCentralMode()): ?>
                    <!-- Menu untuk Admin Pusat -->
                    <a href="<?php echo url('dashboard/product'); ?>" class="menu-item">Produk (Global)</a>
                    <a href="<?php echo url('dashboard/category'); ?>" class="menu-item">Kategori (Global)</a>
                    <a href="<?php echo url('dashboard/warehouse'); ?>" class="menu-item">Warehouse (Semua Region)</a>
                    <a href="<?php echo url('dashboard/warehouse_item'); ?>" class="menu-item">Stok (Semua Region)</a>
                    <a href="<?php echo url('dashboard/order'); ?>" class="menu-item">Pesanan (Semua Region)</a>
                    <a href="<?php echo url('dashboard/user'); ?>" class="menu-item">User (Semua Region)</a>
                    <a href="<?php echo url('dashboard/report'); ?>" class="menu-item">Laporan Global</a>
                <?php else: ?>
                    <!-- Menu untuk Admin Regional -->
                    <a href="<?php echo url('dashboard/warehouse'); ?>" class="menu-item">Warehouse (<?php echo getCurrentRegion(); ?>)</a>
                    <a href="<?php echo url('dashboard/warehouse_item'); ?>" class="menu-item">Stok Inventory</a>
                    <a href="<?php echo url('dashboard/order'); ?>" class="menu-item">Pesanan</a>
                    <a href="<?php echo url('dashboard/user'); ?>" class="menu-item">User Regional</a>
                    <a href="<?php echo url('dashboard/report'); ?>" class="menu-item">Laporan Region</a>
                <?php endif; ?>
                
                <hr>
                <a href="<?php echo url('klien'); ?>" class="menu-item">Lihat Website</a>
                <a href="<?php echo url('logout'); ?>" class="menu-item">Logout</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <header class="topbar">
                <h1><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></h1>
                <div class="user-info">
                    <?php echo isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Admin'; ?>
                </div>
            </header>

            <div class="content">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php echo $_SESSION['success'];
                        unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <?php echo $_SESSION['error'];
                        unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <?php echo isset($content) ? $content : ''; ?>
            </div>
        </div>
    </div>
</body>

</html>