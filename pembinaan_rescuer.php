<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';

// --- LOGIKA TANGGAL SHIFT SAR (Ganti hari setelah jam 09:00 pagi) ---
date_default_timezone_set('Asia/Jakarta');
$jam_sekarang = (int) date('H'); // Ambil jam format 00-23

if ($jam_sekarang < 9) {
    // Kalau sebelum jam 09:00 pagi, masih masuk tanggal dinas kemarin
    $timestamp_aktif = strtotime('-1 day');
} else {
    $timestamp_aktif = time();
}

$namahari = array('Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu');
$namabulan = array('January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret', 'April' => 'April', 'May' => 'Mei', 'June' => 'Juni', 'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September', 'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember');

$hari_ini = $namahari[date('l', $timestamp_aktif)];
$tanggal_ini = date('j', $timestamp_aktif);
$bulan_ini = $namabulan[date('F', $timestamp_aktif)];
$tahun_ini = date('Y', $timestamp_aktif);

$tanggal_log_dinas = "$hari_ini, $tanggal_ini $bulan_ini $tahun_ini";
// -------------------------------------------------------------------
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembinaan Rescuer - Unit Siaga SAR Bogor</title>
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
            max-width: 440px;
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
        .hamburger, .profile-icon {
            font-size: 16pt;
            cursor: pointer;
            color: #1f2c34;
            font-weight: bold;
        }
        .header h2 {
            margin: 0;
            font-size: 13pt;
            letter-spacing: 0.5px;
            font-weight: 800;
            color: #1f2c34;
            text-align: center;
        }

        .breadcrumb {
            text-align: center;
            font-size: 8.5pt;
            color: #1f2c34;
            margin-bottom: 14px;
            font-weight: 500;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.8);
            color: #1f2c34;
            padding: 8px 12px;
            border-radius: 10px;
            font-size: 8.5pt;
            font-weight: 700;
            text-decoration: none;
            margin-bottom: 12px;
            width: fit-content;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            transition: 0.2s;
        }
        .btn-back:hover {
            background: #ffffff;
            color: #e67e22;
        }

        .form-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-bottom: 20px;
        }

        .log-date {
            background: #f1f2f6;
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 9pt;
            font-weight: 700;
            color: #333;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .input-group label {
            font-size: 9pt;
            font-weight: 700;
            color: #1f2c34;
        }
        .input-group select, 
        .input-group input[type="text"],
        .input-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #dcdde1;
            border-radius: 10px;
            font-size: 9pt;
            box-sizing: border-box;
            background: #fafafa;
            font-family: inherit;
        }
        .input-group textarea {
            resize: vertical;
            height: 90px;
        }

        .preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 6px;
        }
        .preview-item {
            position: relative;
            width: 80px;
            height: 60px;
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #ddd;
        }
        .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .preview-item .delete-btn {
            position: absolute;
            top: 2px;
            right: 2px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 9pt;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .upload-box {
            border: 2px dashed #b2bec3;
            border-radius: 12px;
            padding: 16px;
            text-align: center;
            background: #fafafa;
            position: relative;
            cursor: pointer;
        }
        .upload-box input[type="file"] {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        .upload-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            font-size: 8.5pt;
            color: #555;
            font-weight: 600;
        }

        .btn-gps {
            background: #e67e22;
            color: white;
            padding: 12px;
            border-radius: 12px;
            font-size: 9.5pt;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .btn-simpan {
            background: #bdc3c7;
            color: #2c3e50;
            padding: 12px;
            border-radius: 12px;
            font-size: 9.5pt;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            width: 100%;
            transition: 0.2s;
        }
        .btn-simpan:hover {
            background: #27ae60;
            color: white;
        }
    </style>
</head>
<body>

<div class="mobile-container">
    <div class="header">
        <div class="hamburger">&#9776;</div>
        <h2>PEMBINAAN RESCUER</h2>
        <div class="profile-icon">&#128100;</div>
    </div>
    <div class="breadcrumb">Home / Dashboard / Pembinaan Rescuer</div>

    <a href="dashboard.php" class="btn-back">
        &#8592; Kembali ke Dashboard
    </a>

    <form action="simpan_draft.php" method="POST" enctype="multipart/form-data" class="form-card">
        
        <div class="log-date">
            📅 Log : <?= htmlspecialchars($tanggal_log_dinas); ?>
        </div>

        <!-- 1. JENIS KEGIATAN (Dipilih Duluan) -->
        <div class="input-group">
            <label>JENIS KEGIATAN:</label>
            <select name="jenis_kegiatan" id="jenis_kegiatan" required onchange="updateSubKegiatan()">
                <option value="" disabled selected>-- Pilih Jenis Kegiatan --</option>
                <option value="Pembinaan Fisik">Pembinaan Fisik</option>
                <option value="Keterampilan Teknis">Keterampilan Teknis</option>
                <option value="Uji Periodik">Uji Periodik</option>
            </select>
        </div>

        <!-- 2. NAMA / SUB KEGIATAN (Otomatis Menyesuaikan Pilihan di Atas) -->
        <div class="input-group">
            <label>NAMA / SUB KEGIATAN:</label>
            <select name="nama_kegiatan" id="nama_kegiatan" required>
                <option value="" disabled selected>-- Pilih Jenis Kegiatan Terlebih Dahulu --</option>
            </select>
        </div>

        <!-- PRESS RELEASE / DESKRIPSI UTAMA -->
        <div class="input-group">
            <label>PRESS RELEASE / DESKRIPSI UTAMA:</label>
            <textarea name="press_release" placeholder="Tuliskan press release kegiatan..." required></textarea>
        </div>

        <!-- UPLOAD DOKUMENTASI -->
        <div class="input-group">
            <label>UPLOAD DOKUMENTASI:</label>
            <div id="preview-container" class="preview-container"></div>
            <div class="upload-box">
                <input type="file" name="dok[]" id="file-input" accept="image/*" multiple required>
                <div class="upload-content">
                    <span style="font-size: 16pt;">📷 +</span>
                    <span>Tambah Foto Dokumentasi</span>
                </div>
            </div>
        </div>

        
        <button type="submit" name="simpan" class="btn-simpan">Simpan Laporan</button>
    </form>
</div>

<script>
    // Data sub kegiatan yang terikat rapi berdasarkan jenis kegiatan
    const subDataPembinaan = {
        "Pembinaan Fisik": ["Samapta A & B", "Samapta C"],
        "Keterampilan Teknis": ["HART", "MFR", "Jungle Rescue", "Water Rescue", "Urban SAR", "Webinar"],
        "Uji Periodik": ["Semester 1", "Semester 2"]
    };

    function updateSubKegiatan() {
        const jenis = document.getElementById('jenis_kegiatan').value;
        const selectSub = document.getElementById('nama_kegiatan');
        
        selectSub.innerHTML = '<option value="" disabled selected>-- Pilih Sub Kegiatan --</option>';
        
        if (subDataPembinaan[jenis]) {
            subDataPembinaan[jenis].forEach(item => {
                let opt = document.createElement('option');
                opt.value = item;
                opt.text = item;
                selectSub.add(opt);
            });
        }
    }

    const fileInput = document.getElementById('file-input');
    const previewContainer = document.getElementById('preview-container');
    let dataTransfer = new DataTransfer();

    fileInput.addEventListener('change', function(e) {
        const files = e.target.files;
        for (let i = 0; i < files.length; i++) {
            dataTransfer.items.add(files[i]);
            const reader = new FileReader();
            reader.onload = function(event) {
                const div = document.createElement('div');
                div.className = 'preview-item';
                div.innerHTML = `
                    <img src="${event.target.result}">
                    <button type="button" class="delete-btn">&times;</button>
                `;
                div.querySelector('.delete-btn').addEventListener('click', function() {
                    const idx = Array.from(previewContainer.children).indexOf(div);
                    dataTransfer.items.remove(idx);
                    div.remove();
                    fileInput.files = dataTransfer.files;
                });
                previewContainer.appendChild(div);
            }
            reader.readAsDataURL(files[i]);
        }
        fileInput.files = dataTransfer.files;
    });
</script>

</body>
</html>