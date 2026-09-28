<?php
/* 
  * Application: Sistem Laporan Operasional Basarnas Bogor
  * Developed by: Nama Kamu
  * Year: 2026
*/
?>
<?php
session_start();
include 'koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Unit Siaga SAR Bogor</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(to bottom, #ffbc78, #ff914d);
            background-attachment: fixed;
            margin: 0; 
            padding: 20px 16px; 
            color: #1a252f; 
            display: flex;
            justify-content: center;
            min-height: 100vh;
            box-sizing: border-box;
        }

        .mobile-container {
            width: 100%;
            max-width: 420px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4px;
            padding: 0 4px;
        }
        .hamburger {
            font-size: 16pt;
            cursor: pointer;
            color: #1f2c34;
            font-weight: bold;
        }
        .header h2 {
            margin: 0;
            font-size: 14pt;
            letter-spacing: 1px;
            font-weight: 800;
            color: #1f2c34;
            text-align: center;
        }
        .profile-icon {
            font-size: 16pt;
            cursor: pointer;
            color: #1f2c34;
        }

        /* Greeting */
        .greeting {
            text-align: center;
            font-size: 9.5pt;
            color: #1f2c34;
            margin-bottom: 22px;
            font-weight: 500;
        }
        .greeting span {
            font-weight: 700;
        }

        /* Menu Container & Card */
        .menu-container {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .menu-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 14px 20px;
            text-decoration: none;
            color: #1f2c34;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            transition: 0.15s ease;
        }
        .menu-card:active {
            transform: scale(0.98);
        }
        .menu-title {
            font-size: 11pt;
            font-weight: 800;
            color: #1f2c34;
        }
        .menu-icon {
            font-size: 18pt;
        }

        /* Logout button di bawah */
        .footer-action {
            margin-top: 30px;
            text-align: center;
            padding-bottom: 10px;
        }
        .btn-logout {
            background: rgba(255, 255, 255, 0.3);
            color: #1f2c34;
            border: 1px solid rgba(31, 44, 52, 0.2);
            padding: 9px 24px;
            border-radius: 20px;
            font-size: 9.5pt;
            font-weight: 700;
            text-decoration: none;
            display: inline-block;
            transition: 0.2s;
        }
        .btn-logout:hover {
            background: #e74c3c;
            color: white;
            border-color: #e74c3c;
        }
    </style>
</head>
<body>

<div class="mobile-container">
    <div>
        <!-- Top Navigation -->
        <div class="header">
            <div class="hamburger">&#9776;</div>
            <h2>DASHBOARD</h2>
            <div class="profile-icon">&#128100;</div>
        </div>

        <!-- Greeting Text -->
        <div class="greeting">
            Halo, <span>unitsiagasarbogor</span>!
        </div>

        <!-- Menu Cards -->
        <div class="menu-container">
            <!-- 1. Siaga SAR -->
            <a href="siaga_sar.php" class="menu-card">
                <span class="menu-title">Siaga SAR</span>
                <span class="menu-icon">🚁</span>
            </a>

            <!-- 2. Pembinaan Rescuer -->
            <a href="pembinaan_rescuer.php" class="menu-card">
                <span class="menu-title">Pembinaan Rescuer</span>
                <span class="menu-icon">🏋️‍♂️</span>
            </a>

            <!-- 3. Pemeliharaan Palsar -->
            <a href="pemeliharaan_palsar.php" class="menu-card">
                <span class="menu-title">Pemeliharaan Palsar</span>
                <span class="menu-icon">🛠️</span>
            </a>

            <!-- 4. Operasi SAR -->
            <a href="operasi_sar.php" class="menu-card">
                <span class="menu-title">Operasi SAR</span>
                <span class="menu-icon">🚨</span>
            </a>

            <!-- 5. Kegiatan Lainnya -->
            <a href="kegiatan_lain.php" class="menu-card">
                <span class="menu-title">Kegiatan Lainnya</span>
                <span class="menu-icon">🤝</span>
            </a>

            <!-- 6. Draft Laporan -->
            <a href="draft_laporan.php" class="menu-card">
                <span class="menu-title">Draft Laporan</span>
                <span class="menu-icon">📁</span>
            </a>

            <!-- 7. Rekap Bulanan -->
            <a href="rekap_bulanan.php" class="menu-card">
                <span class="menu-title">Rekap Bulanan</span>
                <span class="menu-icon">📅</span>
            </a>
        </div>
    </div>

    <!-- Logout -->
    <div class="footer-action">
        <a href="login.php" class="btn-logout">Keluar / Logout</a>
    </div>
</div>

</body>
</html>