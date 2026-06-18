<?php
require_once 'config/koneksi.php';
require_once 'functions/helpers.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $cek = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");

    if (mysqli_num_rows($cek) > 0) {
        $error = "Email sudah terdaftar, silakan gunakan email lain.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO users (nama, email, password) VALUES ('$nama', '$email', '$hashedPassword')";

        if (mysqli_query($conn, $query)) {
            $success = "Registrasi berhasil! Silakan login.";
        } else {
            $error = "Terjadi kesalahan: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - My Planner</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="auth-banner">
                <div class="banner-logo">✻</div>
                <div class="banner-text">
                    <p class="banner-sub">You can easily</p>
                    <h1 class="banner-main">Get access your personal hub for clarity and productivity</h1>
                </div>
            </div>
            
            <div class="auth-form-content">
                <div class="auth-header-logo">✻</div>
                <h2 class="auth-title">Buat Akun Baru 🌸</h2>
                <div class="auth-sub">Daftar untuk mulai mengatur hidupmu</div>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?= $error ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>

                <form method="POST" action="register.php">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap" required>
                    </div>
                    <div class="form-group">
                        <label>Your email</label>
                        <input type="email" name="email" class="form-control" placeholder="nama@gmail.com" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <div class="password-field-container" style="position: relative; display: flex; align-items: center;">
                            <input type="password" name="password" id="registerPassword" class="form-control" placeholder="Minimal 6 karakter" minlength="6" required style="width: 100%; padding-right: 45px;">
                        </div>
                    </div>
                    <button type="submit" class="btn-primary-auth">Get Started</button>
                </form>

                <div class="auth-footer">
                    Sudah punya akun? <a href="login.php">Login</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>