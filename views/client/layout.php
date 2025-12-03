<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Toko Online'; ?></title>
    <link rel="stylesheet" href="<?php echo asset('css/client.css'); ?>">
</head>

<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <h1 class="logo"><a href="<?php echo url('klien'); ?>">Toko Online</a></h1>

                <?php if (!isCentralMode()): ?>
                    <div style="display: inline-block; padding: 8px 16px; background: transparent; border-radius: 4px; margin-left: 20px;">
                        <span style="font-size: 14px;">📍 <strong>Region: <?php echo getRegionName(); ?></strong></span>
                    </div>
                <?php endif; ?>

                <nav class="nav">
                    <a href="<?php echo url('klien'); ?>">Beranda</a>
                    <?php if (isLoggedIn()): ?>
                        <a href="<?php echo url('klien/profile'); ?>">Profile</a>
                        <?php if (isAdmin()): ?>
                            <a href="<?php echo url('dashboard'); ?>">Dashboard Admin</a>
                        <?php else: ?>
                            <a href="<?php echo url('klien/keranjang'); ?>">Keranjang</a>
                            <a href="<?php echo url('klien/order_history'); ?>">Pesanan Saya</a>
                        <?php endif; ?>
                        <a href="<?php echo url('logout'); ?>">Logout</a>
                    <?php else: ?>
                        <a href="<?php echo url('login'); ?>">Login</a>
                        <a href="<?php echo url('register'); ?>">Register</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
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
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Toko Online. All rights reserved.</p>
        </div>
    </footer>
</body>

</html>