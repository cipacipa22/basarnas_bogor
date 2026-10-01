<?php
session_start();
include 'koneksi.php';

$status_laporan = $_SESSION['status_laporan'] ?? 'Draft';

// --- LOGIKA TANGGAL SHIFT SAR (Ganti hari setelah jam 09:00 pagi) ---
date_default_timezone_set('Asia/Jakarta');
$jam_sekarang = (int) date('H'); // Ambil format jam 00-23

if ($jam_sekarang < 9) {
    // Kalau masih subuh/pagi sebelum jam 9, masih masuk tanggal kemarin (hari dinas sebelumnya)
    $timestamp_aktif = strtotime('-1 day');
} else {
    $timestamp_aktif = time();
}

// Format tanggal untuk tampilan
$hari_arr = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
$bulan_arr = ['January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret', 'April' => 'April', 'May' => 'Mei', 'June' => 'Juni', 'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September', 'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'];

$nama_hari = $hari_arr[date('l', $timestamp_aktif)];
$nama_bulan = $bulan_arr[date('F', $timestamp_aktif)];
$tanggal_dinas = $nama_hari . ', ' . date('d', $timestamp_aktif) . ' ' . $nama_bulan . ' ' . date('Y', $timestamp_aktif);
// -------------------------------------------------------------------
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Draft Laporan Harian - Unit Siaga SAR Bogor</title>
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
        }

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
            text-decoration: none;
        }
        .header h2 {
            margin: 0;
            font-size: 13pt;
            letter-spacing: 0.5px;
            font-weight: 800;
            color: #1f2c34;
            text-align: center;
        }
        .profile-icon {
            font-size: 16pt;
            cursor: pointer;
            color: #1f2c34;
        }

        .breadcrumb {
            text-align: center;
            font-size: 8.5pt;
            color: #1f2c34;
            margin-bottom: 10px;
            font-weight: 500;
        }

        .btn-back-dashboard {
            background: #ffffff;
            color: #1f2c34;
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 8.5pt;
            font-weight: 700;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            margin-bottom: 14px;
            border: 1px solid #ddd;
        }
        .btn-back-dashboard:hover {
            background: #f7f9fa;
        }

        .date-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 14px 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 16px;
        }
        .date-text {
            font-size: 10pt;
            font-weight: 700;
            color: #1f2c34;
        }
        
        .badge-status {
            padding: 6px 14px;
            border-radius: 10px;
            font-size: 8.5pt;
            font-weight: 700;
            text-decoration: none;
        }
        .badge-draft {
            background: #fff3cd;
            color: #856404;
        }
        .badge-selesai {
            background: #d4edda;
            color: #155724;
        }

        .section-title {
            font-size: 10pt;
            font-weight: 700;
            color: #1f2c34;
            margin-bottom: 12px;
        }

        .main-wrapper-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-bottom: 20px;
        }

        .sub-kegiatan-item {
            border-bottom: 1px solid #eee;
            padding-bottom: 14px;
        }
        .sub-kegiatan-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .kegiatan-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
            gap: 8px;
        }
        
        .nama-sub-kegiatan {
            font-size: 10pt;
            font-weight: 800;
            color: #c0392b;
            margin-bottom: 2px;
        }

        .jenis-kegiatan-utama {
            font-size: 8.5pt;
            color: #555;
            font-weight: 600;
        }

        .btn-edit {
            background: #f39c12;
            color: white;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 7.5pt;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
        }
        .kegiatan-row {
            font-size: 9pt;
            color: #333;
            line-height: 1.4;
            margin-bottom: 4px;
        }
        .kegiatan-row span {
            font-weight: 700;
            color: #1f2c34;
        }
        .no-bukti {
            font-size: 8.5pt;
            color: #777;
            font-style: italic;
        }

        .action-footer {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 20px;
        }
        .btn-tambah {
            background: #1f2c34;
            color: white;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 9pt;
            font-weight: 700;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            text-align: center;
            flex: 1;
        }
        .btn-pdf {
            background: #e74c3c;
            color: white;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 9pt;
            font-weight: 700;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            text-align: center;
            flex: 1;
        }
    </style>
</head>
<body>

<div class="mobile-container">
    <div class="header">
        <a href="dashboard.php" class="hamburger">&#9776;</a>
        <h2>DRAFT LAPORAN HARIAN</h2>
        <div class="profile-icon">&#128100;</div>
    </div>
    <div class="breadcrumb">Home / Dashboard / Draft Harian</div>

    <div>
        <a href="dashboard.php" class="btn-back-dashboard">&#8592; Kembali ke Dashboard</a>
    </div>

    <div class="date-card">
        <div class="date-text">&#128197; <?= $tanggal_dinas; ?></div>
        <?php if ($status_laporan == 'Selesai'): ?>
            <span class="badge-status badge-selesai">[ Selesai ]</span>
        <?php else: ?>
            <span class="badge-status badge-draft">[ Draft ]</span>
        <?php endif; ?>
    </div>

    <div class="section-title">Log Kegiatan Hari Ini :</div>

    <div class="main-wrapper-card">
        <?php
        $query = mysqli_query($koneksi, "SELECT * FROM draft_laporan ORDER BY id ASC");
        
        if ($query && mysqli_num_rows($query) > 0) {
            while ($row = mysqli_fetch_assoc($query)) {
                $waktu_kegiatan = !empty($row['waktu']) ? $row['waktu'] : date('H:i') . ' WIB';
                
                // Ambil data dengan fleksibel supaya Pembinaan, Operasi, & Kegiatan Lain tidak tertimbun
                $nama_kegiatan_db  = isset($row['nama_kegiatan']) ? trim($row['nama_kegiatan']) : ''; 
                $jenis_kegiatan_db = isset($row['jenis_kegiatan']) ? trim($row['jenis_kegiatan']) : ''; 
                $opsi_fallback     = isset($row['opsi']) ? trim($row['opsi']) : '';
                $press_release     = isset($row['press_release']) ? trim($row['press_release']) : '';

                // Logika penamaan sub kegiatan dan kategori agar tampil semua dengan benar
                if (!empty($nama_kegiatan_db)) {
                    $nama_sub_kegiatan = $nama_kegiatan_db; 
                    $kategori_utama    = !empty($jenis_kegiatan_db) ? $jenis_kegiatan_db : "Kegiatan";
                } elseif (!empty($jenis_kegiatan_db)) {
                    $nama_sub_kegiatan = $jenis_kegiatan_db;
                    $kategori_utama    = "Siaga SAR";
                } elseif (!empty($opsi_fallback)) {
                    $nama_sub_kegiatan = $opsi_fallback;
                    $kategori_utama    = "Kegiatan Lain";
                } else {
                    $nama_sub_kegiatan = "Kegiatan Tanpa Nama";
                    $kategori_utama    = "Umum";
                }

                $desk_val = !empty($row['deskripsi']) ? $row['deskripsi'] : (!empty($press_release) ? $press_release : '-');
                $id_data  = $row['id'];
        ?>
            <div class="sub-kegiatan-item">
                <div class="kegiatan-header">
                    <div>
                        <div class="nama-sub-kegiatan"><?= htmlspecialchars($nama_sub_kegiatan); ?></div>
                        <div class="jenis-kegiatan-utama">
                            <?= htmlspecialchars($kategori_utama); ?> 
                        </div>
                    </div>
                    <a href="edit_draft.php?id=<?= $id_data; ?>" class="btn-edit">Edit</a>
                </div>
                <div class="kegiatan-row">
                    <span>Waktu:</span> <?= htmlspecialchars($waktu_kegiatan); ?>
                </div>
                <div class="kegiatan-row">
                    <span>Deskripsi:</span> <?= htmlspecialchars($desk_val); ?>
                </div>
                <div class="kegiatan-row">
                    <span>Bukti:</span><br>
                    <?php 
                    // Cek berbagai kemungkinan nama kolom foto dokumentasi di database
                    $kolom_foto = '';
                    if (!empty($row['dokumentasi'])) {
                        $kolom_foto = $row['dokumentasi'];
                    } elseif (!empty($row['dok'])) {
                        $kolom_foto = $row['dok'];
                    }

                    if (!empty($kolom_foto)) {
                        $array_foto = explode(',', $kolom_foto);
                        $ada_foto = false;
                        
                        echo '<div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 6px;">';
                        foreach ($array_foto as $foto) {
                            $foto = trim($foto);
                            if (!empty($foto) && file_exists('uploads/' . $foto)) {
                                echo '<img src="uploads/' . $foto . '" alt="Bukti Dokumentasi" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">';
                                $ada_foto = true;
                            }
                        }
                        echo '</div>';
                        
                        if (!$ada_foto) {
                            echo '<div class="no-bukti">Tidak ada foto dokumentasi</div>';
                        }
                    } else {
                        echo '<div class="no-bukti">Tidak ada foto dokumentasi</div>';
                    }
                    ?>
                </div>
            </div>
        <?>
            }
        } else {
            echo '<div style="text-align:center; color:#777; font-style:italic; padding: 10px 0;">Belum ada kegiatan yang disimpan hari ini.</div>';
        }
        ?>
    </div>

    <div class="action-footer">
        <!-- Tombol tambah mengarah kembali ke dashboard sesuai keinginanmu -->
        <a href="dashboard.php" class="btn-tambah">+ Tambah Kegiatan</a>
        <a href="cetak_laporan.php" target="_blank" class="btn-pdf">&#128462; Konversi PDF</a>
    </div>
</div>

</body>
</html>
