<?php
require_once 'config/koneksi.php';
require_once 'functions/helpers.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['email'] = $user['email'];

            header("Location: index.php");
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - My Planner</title>
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
                <h2 class="auth-title">Welcome Back 🌿</h2>
                <div class="auth-sub">Login untuk melanjutkan ke My Planner</div>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <div class="form-group">
                        <label>Your email</label>
                        <input type="email" name="email" class="form-control" placeholder="nama@gmail.com" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <div class="password-field-container" style="position: relative; display: flex; align-items: center;">
                            <input type="password" name="password" id="loginPassword" class="form-control" placeholder="Masukkan password" required style="width: 100%; padding-right: 45px;">
                            <button type="button" id="toggleLoginBtn" class="toggle-password-btn" onclick="togglePassword('loginPassword', this)" style="position: absolute; right: 12px; background: none; border: none; cursor: pointer; color: #6b7280; display: flex; align-items: center;"></button>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary-auth">Login</button>
                </form>

                <div class="auth-footer">
                    Belum punya akun? <a href="register.php">Daftar di sini</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>