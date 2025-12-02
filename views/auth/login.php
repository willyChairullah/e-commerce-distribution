<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Toko Online</title>
    <link rel="stylesheet" href="<?php echo asset('css/auth.css'); ?>">
</head>

<body>
    <div class="auth-container">
        <div class="auth-box">
            <h2>Login</h2>

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

            <form method="POST" action="<?php echo url('login'); ?>" class="auth-form">
                <?php echo csrfField(); ?>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>

            <p class="auth-link">Belum punya akun? <a href="<?php echo url('register'); ?>">Register di sini</a></p>
            <p class="auth-link"><a href="<?php echo url('klien'); ?>">Kembali ke Beranda</a></p>
        </div>
    </div>
</body>

</html>