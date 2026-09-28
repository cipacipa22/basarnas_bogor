<?php
session_start();
include 'koneksi.php';

$error = false;

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    $result = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username'");

    // Cek apakah username ditemukan
    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        // Cek password (mendukung teks biasa atau password_hash)
        if ($password === $row['password'] || password_verify($password, $row['password'])) {
            $_SESSION['login'] = true;
            $_SESSION['username'] = $row['username'];
            header("Location: dashboard.php");
            exit;
        }
    }
    
    $error = true;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Unit Siaga SAR Bogor</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .login-content {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            flex-grow: 1;
            width: 100%;
        }
        .logo-container img {
            width: 90px;
            height: auto;
            margin-bottom: 10px;
        }
        .logo-container h2 {
            font-size: 18px;
            color: #2c3e50;
            margin: 5px 0;
            font-weight: bold;
        }
        .logo-container p {
            font-size: 13px;
            color: #4a3525;
            margin-bottom: 25px;
        }
        .form-group {
            margin-bottom: 15px;
            position: relative;
            width: 100%;
        }
        .form-control {
            width: 100%;
            padding: 14px 18px;
            border: none;
            border-radius: 30px;
            background-color: #fff;
            font-size: 14px;
            box-sizing: border-box;
            outline: none;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
        }
        .toggle-password {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 16px;
        }
        .btn-login {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 30px;
            background-color: #222;
            color: #fff;
            font-weight: bold;
            font-size: 15px;
            cursor: pointer;
            margin-top: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn-login:hover {
            background-color: #333;
        }
        .alert-error {
            color: #d9534f;
            background-color: #f2dede;
            border: 1px solid #ebccd1;
            padding: 10px;
            border-radius: 20px;
            font-size: 13px;
            margin-bottom: 15px;
            width: 100%;
            text-align: center;
        }
        /* Style untuk watermark / copyright */
        .app-footer {
            text-align: center;
            margin-top: 25px;
            font-size: 11px;
            color: #666;
            line-height: 1.4;
        }
    </style>
</head>
<body>

    <div class="mobile-frame">
        <div class="login-content">
            <div class="logo-container" style="text-align: center;">
                <img src="assets/img/logo_sar.png" alt="Logo SAR">
                <h2>BADAN SAR NASIONAL</h2>
                <p>Unit Siaga SAR Bogor</p>
            </div>

            <?php if ($error) : ?>
                <p class="alert-error">Username atau Kata Sandi salah!</p>
            <?php endif; ?>

            <form action="" method="POST" style="width: 100%;">
                <div class="form-group">
                    <input type="text" name="username" class="form-control" placeholder="Masukkan Username" required autocomplete="off">
                </div>
                <div class="form-group">
                    <input type="password" name="password" id="password" class="form-control" placeholder="Kata Sandi" required>
                    <span class="toggle-password" onclick="togglePass()">👁</span>
                </div>
                <button type="submit" name="login" class="btn-login">LOGIN</button>
            </form>

            <!-- Tanda Pengenal / Copyright -->
            <div class="app-footer">
                Sistem Laporan Harian Unit Siaga SAR Bogor<br>
                &copy; 2026 - Developed by <b>Syifa UBSI Bogor</b>
            </div>
        </div>
    </div>

    <script>
        function togglePass() {
            const passInput = document.getElementById('password');
            if (passInput.type === 'password') {
                passInput.type = 'text';
            } else {
                passInput.type = 'password';
            }
        }
    </script>
</body>
</html>