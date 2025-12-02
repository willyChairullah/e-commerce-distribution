<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Toko Online</title>
    <link rel="stylesheet" href="<?php echo asset('css/auth.css'); ?>">
</head>

<body>
    <div class="auth-container">
        <div class="auth-box">
            <h2>Register</h2>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php echo $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo url('register'); ?>" class="auth-form">
                <?php echo csrfField(); ?>

                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="full_name" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required minlength="6">
                </div>

                <?php if (isCentralMode()): ?>
                    <!-- Central mode: user harus pilih region -->
                    <div class="form-group">
                        <label>Pilih Region <span style="color: red;">*</span></label>
                        <select name="region_code" required>
                            <option value="">-- Pilih Region --</option>
                            <?php foreach (AVAILABLE_REGIONS as $code => $name): ?>
                                <option value="<?php echo $code; ?>"><?php echo $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small style="color: #666;">Pilih region domisili Anda</small>
                    </div>
                <?php else: ?>
                    <!-- Regional mode: region auto-detect dari server -->
                    <div class="form-group">
                        <label>Region</label>
                        <input type="text" value="<?php echo getRegionName(); ?>" disabled style="background: #f5f5f5;">
                        <small style="color: #666;">Region otomatis sesuai lokasi server</small>
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-primary btn-block">Register</button>
            </form>

            <p class="auth-link">Sudah punya akun? <a href="<?php echo url('login'); ?>">Login di sini</a></p>
            <p class="auth-link"><a href="<?php echo url('klien'); ?>">Kembali ke Beranda</a></p>
        </div>
    </div>
</body>

</html>