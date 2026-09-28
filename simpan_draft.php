<?php
session_start();
include 'koneksi.php';

// Set zona waktu agar jam sesuai waktu Indonesia (WIB)
date_default_timezone_set('Asia/Jakarta');

// Aktifkan pelaporan error PHP
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan'])) {
    
    // Tangkap data dari form dan bersihkan dari kata-kata tambahan yang tidak diinginkan
    $jenis_kegiatan   = trim(str_replace(' KHUSUS', '', $_POST['jenis_kegiatan'] ?? ''));   
    $nama_kegiatan    = trim(str_replace(' KHUSUS', '', $_POST['nama_kegiatan'] ?? ''));    
    $rincian_kegiatan = trim($_POST['rincian_kegiatan'] ?? ''); 
    $press_release    = $_POST['press_release'] ?? '';    
    $tanggal_hari_ini = date('Y-m-d');
    $waktu_sekarang   = date('H:i:s');

    // === PENCEGAHAN OTOMATIS SUPAYA TIDAK SALAH KATEGORI ===
    // Kalau nama kegiatannya "Narasumber", paksa jenis kegiatannya jadi "Kegiatan Lain"
    if (strtolower($nama_kegiatan) === 'narasumber') {
        $jenis_kegiatan = 'Kegiatan Lain';
    }
    // Atau kalau form jenis kegiatannya kosong tapi ngisi nama kegiatan, jadikan "Kegiatan Lain"
    if (empty($jenis_kegiatan)) {
        $jenis_kegiatan = 'Kegiatan Lain';
    }
    // ========================================================

    // Cek dan buat folder uploads jika belum ada
    if (!is_dir('uploads')) {
        if (!mkdir('uploads', 0777, true)) {
            die("<b>GAGAL:</b> Folder 'uploads' tidak dapat dibuat. Periksa izin folder.");
        }
    }

    // Proses upload file dokumentasi
    $uploaded_files = [];
    if (isset($_FILES['dok'])) {
        $total_file = count($_FILES['dok']['name']);
        for ($i = 0; $i < $total_file; $i++) {
            if ($_FILES['dok']['error'][$i] == 0) {
                $nama_file = $_FILES['dok']['name'][$i];
                $file_tmp  = $_FILES['dok']['tmp_name'][$i];
                $nama_file_baru = time() . '_' . $i . '_' . basename($nama_file);
                
                if (move_uploaded_file($file_tmp, 'uploads/' . $nama_file_baru)) {
                    $uploaded_files[] = $nama_file_baru;
                }
            }
        }
    }
    
    $dokumentasi_str = implode(',', $uploaded_files);

    // Escape string untuk keamanan database
    $jenis_db     = mysqli_real_escape_string($koneksi, $jenis_kegiatan);
    $sub_db       = mysqli_real_escape_string($koneksi, $nama_kegiatan);
    $rincian_db   = mysqli_real_escape_string($koneksi, $rincian_kegiatan);
    $deskripsi_db = mysqli_real_escape_string($koneksi, $press_release);  

    // Gabungkan sub kegiatan dan rincian jika ada
    $gabung_sub = !empty($rincian_db) ? "$sub_db - $rincian_db" : $sub_db;
    $opsi_db      = mysqli_real_escape_string($koneksi, $gabung_sub);

    $query = "INSERT INTO draft_laporan (tanggal, waktu, nama_kegiatan, jenis_kegiatan, opsi, deskripsi, dokumentasi) 
              VALUES ('$tanggal_hari_ini', '$waktu_sekarang', '$sub_db', '$jenis_db', '$opsi_db', '$deskripsi_db', '$dokumentasi_str')";
    
    $exec = mysqli_query($koneksi, $query);
    
    if (!$exec) {
        mysqli_query($koneksi, "ALTER TABLE draft_laporan ADD COLUMN nama_kegiatan VARCHAR(150) AFTER waktu");
        
        $exec_retry = mysqli_query($koneksi, $query);
        if (!$exec_retry) {
            echo "<div style='background: #ffebee; color: #c62828; padding: 20px; border: 1px solid #ef9a9a; font-family: monospace; border-radius: 8px;'>";
            echo "<h3>Terjadi Kesalahan Database (MySQL):</h3>";
            echo "<p>" . mysqli_error($koneksi) . "</p>";
            echo "<hr><b>Query SQL:</b><br>" . $query;
            echo "</div>";
            exit;
        }
    }
    
    header("Location: draft_laporan.php");
    exit;

} else {
    header("Location: dashboard.php");
    exit;
}
?>