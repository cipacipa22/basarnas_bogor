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
    <title>Siaga SAR - Unit Siaga SAR Bogor</title>
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

        #template-kendaraan {
            display: none;
            background: #fff8f0;
            border: 1px solid #f39c12;
            padding: 12px;
            border-radius: 12px;
            margin-top: 4px;
        }
        .section-sub-title {
            font-size: 8pt;
            font-weight: 800;
            background: #e5e5e5;
            color: #2c3e50;
            padding: 6px 8px;
            margin: 10px 0 6px 0;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .table-kendaraan {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
            margin-bottom: 8px;
            background: #fff;
        }
        .table-kendaraan th, .table-kendaraan td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: center;
        }
        .table-kendaraan th {
            background: #f39c12;
            color: white;
            font-weight: 700;
        }
        .table-kendaraan input {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 3px;
            text-align: center;
            font-size: 7.5pt;
            background: #fff;
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
        <h2>SIAGA SAR</h2>
        <div class="profile-icon">&#128100;</div>
    </div>
    <div class="breadcrumb">Home / Dashboard / Siaga SAR</div>

    <a href="dashboard.php" class="btn-back">
        &#8592; Kembali ke Dashboard
    </a>

    <form action="simpan_draft.php" method="POST" enctype="multipart/form-data" class="form-card">
        
        <div class="log-date">
            📅 Log : <?= htmlspecialchars($tanggal_log_dinas); ?>
        </div>

        <!-- 1. SUB KEGIATAN -->
        <div class="input-group">
            <label>NAMA / SUB KEGIATAN:</label>
            <select name="nama_kegiatan" id="pilih_kegiatan" required onchange="updateSubPilihKegiatan()">
                <option value="" disabled selected>-- Pilih Sub Kegiatan --</option>
                <option value="Briefing Siaga">Briefing Siaga</option>
                <option value="Pengecekan Kendaraan Operasional">Pengecekan Kendaraan Operasional</option>
                <option value="Pengecekan Palsar (Foto Tabel)">Pengecekan Palsar (Foto Tabel)</option>
                <option value="Pengecekan Palsar (Eviden Lapangan)">Pengecekan Palsar (Eviden Lapangan)</option>
                <option value="Pemeliharaan Palsar">Pemeliharaan Palsar</option>
                <option value="Jurnal Absensi">Jurnal Absensi</option>
                <option value="Monitoring ">Monitoring </option>
            </select>
        </div>

        <!-- 2. JENIS KEGIATAN -->
        <div class="input-group">
            <label>JENIS KEGIATAN:</label>
            <select name="jenis_kegiatan" id="jenis_kegiatan" required>
                <option value="" disabled selected>-- Pilih Jenis Kegiatan --</option>
                <option value="Siaga SAR Rutin">Siaga SAR Rutin</option>
                <option value="Siaga SAR Khusus">Siaga SAR Khusus</option>
            </select>
        </div>

        <!-- 3. RINCIAN / DETAIL -->
        <div class="input-group" id="group-sub-pilih" style="display: none;">
            <label id="label-sub-pilih">RINCIAN / DETAIL:</label>
            <select name="rincian_kegiatan" id="rincian_kegiatan">
                <option value="" disabled selected>-- Pilih Rincian --</option>
            </select>
        </div>

        <!-- TEMPLATE KHUSUS PENGECEKAN KENDARAAN OPERASIONAL -->
        <div id="template-kendaraan">
            <div style="font-size: 9pt; font-weight: 800; color: #c0392b; text-align: center; margin-bottom: 8px;">
                LAPORAN PEMERIKSAAN KENDARAAN OPERASIONAL
            </div>

            <div class="input-group" style="gap: 4px; margin-bottom: 8px;">
                <label style="font-size: 8pt;">Tim:</label>
                <input type="text" name="tim_lap" value="USS BOGOR">
            </div>
            <div class="input-group" style="gap: 4px; margin-bottom: 8px;">
                <label style="font-size: 8pt;">Pemeriksa:</label>
                <input type="text" name="pemeriksa_lap" value="-">
            </div>
            <div class="input-group" style="gap: 4px; margin-bottom: 8px;">
                <label style="font-size: 8pt;">Unit/Lokasi:</label>
                <input type="text" name="lokasi_lap" value="USS Bogor">
            </div>

            <div class="section-sub-title">Kendaraan dengan bahan bakar </div>
            <table class="table-kendaraan">
                <tr>
                    <th>No</th>
                    <th>Kendaraan</th>
                    <th>Plat</th>
                    <th>BBM</th>
                    <th>KM</th>
                    <th>Bersih</th>
                    <th>Ket</th>
                </tr>
                <tr>
                    <td>1</td>
                    <td>Isuzu D-Max</td>
                    <td><input type="text" name="plat_1" value="B 9232 PSD"></td>
                    <td><input type="text" name="bbm_1" value="100%"></td>
                    <td><input type="text" name="km_1" placeholder="..."></td>
                    <td><input type="text" name="bersih_1" value="Ya"></td>
                    <td><input type="text" name="ket_1" value="-"></td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Kawasaki Trail</td>
                    <td><input type="text" name="plat_2" value="B 3835 PFQ"></td>
                    <td><input type="text" name="bbm_2" value="100%"></td>
                    <td><input type="text" name="km_2" placeholder="..."></td>
                    <td><input type="text" name="bersih_2" value="Ya"></td>
                    <td><input type="text" name="ket_2" value="-"></td>
                </tr>
            </table>

            <div class="section-sub-title">Kendaraan dengan bahan bakar </div>
            <table class="table-kendaraan">
                <tr>
                    <th>No</th>
                    <th>Kendaraan</th>
                    <th>Plat</th>
                    <th>BBM</th>
                    <th>KM</th>
                    <th>Bersih</th>
                    <th>Ket</th>
                </tr>
                <tr>
                    <td>-</td>
                    <td><input type="text" name="k_bawah_kendaraan" value="-"></td>
                    <td><input type="text" name="k_bawah_plat" value="-"></td>
                    <td><input type="text" name="k_bawah_bbm" value="-"></td>
                    <td><input type="text" name="k_bawah_km" value="-"></td>
                    <td><input type="text" name="k_bawah_bersih" value="-"></td>
                    <td><input type="text" name="k_bawah_ket" value="-"></td>
                </tr>
            </table>

            <div class="section-sub-title">Kendaraan Sedang Diperbaiki</div>
            <div class="input-group" style="margin-bottom: 8px;">
                <input type="text" name="kendaraan_perbaikan" value="-">
            </div>

            <div class="section-sub-title">Deskripsi Lainnya</div>
            <div class="input-group">
                <textarea name="catatan_kendaraan">1. Isuzu D-Max B 9232 PSD
> Mika lampu belakang kiri pecah
> Power window pintu pengemudi tidak berfungsi
2. Kawasaki Trail B 3835 PFQ
> Kabel Kilometer speedometer harus ganti
> Gear Set harus di ganti
> Kampas rem depan habis
> Shockbreaker depan bocor
> Lampu Rem Mati</textarea>
            </div>
        </div>

        <!-- PRESS RELEASE / DESKRIPSI UTAMA -->
        <div class="input-group" id="group-press-release">
            <label>PRESS RELEASE / DESKRIPSI UTAMA:</label>
            <textarea name="press_release" id="press_release" placeholder="Tuliskan press release kegiatan..."></textarea>
        </div>

        <!-- UPLOAD DOKUMENTASI -->
        <div class="input-group" id="group-upload">
            <label id="label-upload">UPLOAD DOKUMENTASI:</label>
            <div id="preview-container" class="preview-container"></div>
            <div class="upload-box">
                <input type="file" name="dok[]" id="file-input" accept="image/*" multiple required>
                <div class="upload-content">
                    <span style="font-size: 16pt;">📷 +</span>
                    <span id="upload-text-info">Tambah Foto Dokumentasi</span>
                </div>
            </div>
        </div>

        
        <button type="submit" name="simpan" class="btn-simpan">Simpan Laporan</button>
    </form>
</div>

<script>
    const subSubData = {};

    function updateSubPilihKegiatan() {
        const pilihKegiatan = document.getElementById('pilih_kegiatan').value;
        const groupSubPilih = document.getElementById('group-sub-pilih');
        const selectSubPilih = document.getElementById('rincian_kegiatan');
        
        document.getElementById('template-kendaraan').style.display = 'none';
        document.getElementById('group-press-release').style.display = 'flex';
        document.getElementById('press_release').required = true;
        const fileInput = document.getElementById('file-input');
        const labelUpload = document.getElementById('label-upload');
        const uploadTextInfo = document.getElementById('upload-text-info');
        
        fileInput.multiple = true;
        labelUpload.innerText = "UPLOAD DOKUMENTASI:";
        uploadTextInfo.innerText = "Tambah Foto Dokumentasi";

        if (subSubData[pilihKegiatan]) {
            groupSubPilih.style.display = 'flex';
            selectSubPilih.innerHTML = '<option value="" disabled selected>-- Pilih Rincian --</option>';
            subSubData[pilihKegiatan].forEach(item => {
                let opt = document.createElement('option');
                opt.value = item;
                opt.text = item;
                selectSubPilih.add(opt);
            });
        } else {
            groupSubPilih.style.display = 'none';
        }

        // Pengaturan khusus untuk Pengecekan Kendaraan Operasional
        if (pilihKegiatan === 'Pengecekan Kendaraan Operasional') {
            document.getElementById('template-kendaraan').style.display = 'block';
        } 
        // Pengaturan khusus untuk Jurnal & Absensi (Tanpa Press Release, 1 Foto Template)
        else if (pilihKegiatan.includes('Jurnal') || pilihKegiatan.includes('Absensi')) {
            document.getElementById('group-press-release').style.display = 'none';
            document.getElementById('press_release').required = false;
            fileInput.multiple = false;
            labelUpload.innerText = "UPLOAD FOTO TEMPLATE (FULL UKURAN):";
            uploadTextInfo.innerText = "Upload Foto Template Kegiatan";
        }
        // Pengaturan khusus Pengecekan Palsar (Foto Tabel) -> Tetap ada Press Release, tapi 1 foto template
        else if (pilihKegiatan === 'Pengecekan Palsar (Foto Tabel)') {
            fileInput.multiple = false;
            labelUpload.innerText = "UPLOAD FOTO TEMPLATE (FULL UKURAN):";
            uploadTextInfo.innerText = "Upload Foto Template Kegiatan";
        }
    }

    const fileInput = document.getElementById('file-input');
    const previewContainer = document.getElementById('preview-container');
    let dataTransfer = new DataTransfer();

    fileInput.addEventListener('change', function(e) {
        const pilihKegiatan = document.getElementById('pilih_kegiatan').value;
        const isSinglePhoto = (pilihKegiatan === 'Pengecekan Palsar (Foto Tabel)' || pilihKegiatan.includes('Jurnal') || pilihKegiatan.includes('Absensi'));
        
        if (isSinglePhoto) {
            previewContainer.innerHTML = '';
            dataTransfer = new DataTransfer();
        }

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
                
                if (isSinglePhoto) {
                    previewContainer.innerHTML = '';
                }

                previewContainer.appendChild(div);
            }
            reader.readAsDataURL(files[i]);
        }
        fileInput.files = dataTransfer.files;
    });
</script>

</body>
</html>