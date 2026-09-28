<?php
session_start();
include 'koneksi.php';

$id = $_GET['id'] ?? '';
if (empty($id)) {
    header("Location: draft_laporan.php");
    exit;
}

$query = mysqli_query($koneksi, "SELECT * FROM draft_laporan WHERE id = '$id'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    header("Location: draft_laporan.php");
    exit;
}

if (isset($_POST['hapus_foto_nama'])) {
    $foto_dihapus = trim($_POST['hapus_foto_nama']);
    $arr_f = !empty($data['dokumentasi']) ? explode(',', $data['dokumentasi']) : [];
    
    $arr_f = array_filter($arr_f, function($item) use ($foto_dihapus) {
        return trim($item) !== $foto_dihapus;
    });

    $dokumentasi_baru = implode(',', $arr_f);
    mysqli_query($koneksi, "UPDATE draft_laporan SET dokumentasi = '$dokumentasi_baru' WHERE id = '$id'");
    
    if (file_exists('uploads/' . $foto_dihapus)) {
        unlink('uploads/' . $foto_dihapus);
    }
    echo "success";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $waktu         = $_POST['waktu'] ?? '';
    // Ambil nilai dari sub kegiatan jika ada, kalau kosong ambil jenis, kalau kosong ambil menu
    $nama_kegiatan = $_POST['nama_kegiatan'] !== '' ? $_POST['nama_kegiatan'] : ($_POST['jenis_kegiatan'] !== '' ? $_POST['jenis_kegiatan'] : $_POST['menu_utama']);
    $press_release = $_POST['press_release'] ?? '';
    
    $waktu_db      = mysqli_real_escape_string($koneksi, $waktu);
    $nama_db       = mysqli_real_escape_string($koneksi, $nama_kegiatan);
    $press_db      = mysqli_real_escape_string($koneksi, $press_release);
    
    $existing_files = !empty($data['dokumentasi']) ? explode(',', $data['dokumentasi']) : [];

    $new_uploaded_files = [];
    if (isset($_FILES['dok']) && count($_FILES['dok']['name']) > 0 && $_FILES['dok']['error'][0] == 0) {
        $total_file = count($_FILES['dok']['name']);
        for ($i = 0; $i < $total_file; $i++) {
            if ($_FILES['dok']['error'][$i] == 0) {
                $nama_file = $_FILES['dok']['name'][$i];
                $file_tmp  = $_FILES['dok']['tmp_name'][$i];
                $nama_file_baru = time() . '_' . $i . '_' . basename($nama_file);
                
                if (!is_dir('uploads')) {
                    mkdir('uploads', 0777, true);
                }
                
                if (move_uploaded_file($file_tmp, 'uploads/' . $nama_file_baru)) {
                    $new_uploaded_files[] = $nama_file_baru;
                }
            }
        }
    }

    $all_photos = array_merge($existing_files, $new_uploaded_files);
    $all_photos = array_filter($all_photos);
    $dokumentasi_str = implode(',', $all_photos);

    $update_query = "UPDATE draft_laporan SET waktu='$waktu_db', opsi='$nama_db', deskripsi='$press_db', dokumentasi='$dokumentasi_str' WHERE id='$id'";
    $exec = mysqli_query($koneksi, $update_query);

    if ($exec) {
        header("Location: draft_laporan.php");
        exit;
    } else {
        $error = "Gagal memperbarui data: " . mysqli_error($koneksi);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Draft Laporan - Unit Siaga SAR Bogor</title>
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
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            font-size: 13pt;
            font-weight: 800;
            color: #1f2c34;
        }
        .card {
            background: #ffffff;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .form-group {
            margin-bottom: 14px;
        }
        .form-group label {
            display: block;
            font-size: 9pt;
            font-weight: 700;
            color: #1f2c34;
            margin-bottom: 6px;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 9pt;
            box-sizing: border-box;
        }
        .form-group textarea {
            resize: vertical;
            height: 90px;
        }
        
        #jenis-kegiatan-group, #sub-kegiatan-group {
            display: none;
        }
        .sub-group-box {
            background: #fdfefe;
            border: 1px dashed #34495e;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 14px;
        }

        .btn-simpan {
            background: #1f2c34;
            color: white;
            border: none;
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            font-size: 9.5pt;
            font-weight: 700;
            cursor: pointer;
            margin-top: 10px;
        }
        .btn-hapus {
            display: block;
            text-align: center;
            background: #c0392b;
            color: white;
            text-decoration: none;
            padding: 10px;
            border-radius: 10px;
            font-size: 9pt;
            font-weight: 700;
            margin-top: 8px;
        }
        .btn-kembali {
            display: block;
            text-align: center;
            background: #7f8c8d;
            color: white;
            text-decoration: none;
            padding: 10px;
            border-radius: 10px;
            font-size: 9pt;
            font-weight: 700;
            margin-top: 8px;
        }
        .preview-img {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 8px;
        }
        .img-wrapper {
            position: relative;
            width: 70px;
            height: 70px;
        }
        .img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #ddd;
        }
        .btn-del-img {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #c0392b;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 10pt;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body onload="initForm()">

<div class="mobile-container">
    <div class="header">
        <h2>EDIT DRAFT LAPORAN</h2>
    </div>

    <div class="card">
        <?php if(isset($error)) echo '<div style="color:red; font-size:9pt; margin-bottom:10px;">'.$error.'</div>'; ?>
        
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Waktu Kegiatan:</label>
                <input type="text" name="waktu" value="<?= htmlspecialchars($data['waktu']); ?>" required>
            </div>

            <!-- LEVEL 1: MENU UTAMA -->
            <div class="form-group">
                <label>Menu Dashboard:</label>
                <select name="menu_utama" id="menu_utama" required onchange="handleMenuChange()">
                    <option value="">-- Pilih Menu Dashboard --</option>
                    <option value="Siaga SAR">Siaga SAR</option>
                    <option value="Pembinaan Rescuer">Pembinaan Rescuer</option>
                    <option value="Pemeliharaan Palsar">Pemeliharaan Palsar</option>
                    <option value="Operasi SAR">Operasi SAR</option>
                    <option value="Kegiatan Lainnya">Kegiatan Lainnya</option>
                </select>
            </div>

            <!-- LEVEL 2: JENIS KEGIATAN -->
            <div class="form-group sub-group-box" id="jenis-kegiatan-group">
                <label>Jenis Kegiatan:</label>
                <select name="jenis_kegiatan" id="jenis_kegiatan" onchange="handleJenisChange()">
                    <option value="">-- Pilih Jenis Kegiatan --</option>
                </select>
            </div>

            <!-- LEVEL 3: NAMA / SUB KEGIATAN -->
            <div class="form-group sub-group-box" id="sub-kegiatan-group">
                <label>Nama / Sub Kegiatan:</label>
                <select name="nama_kegiatan" id="nama_kegiatan">
                    <option value="">-- Pilih Sub Kegiatan --</option>
                </select>
            </div>

            <div class="form-group">
                <label>Deskripsi / Press Release:</label>
                <textarea name="press_release" required><?= htmlspecialchars($data['deskripsi']); ?></textarea>
            </div>

            <div class="form-group">
                <label>Bukti Dokumentasi Saat Ini:</label>
                <div class="preview-img">
                    <?php 
                    if (!empty($data['dokumentasi'])) {
                        $arr_f = explode(',', $data['dokumentasi']);
                        foreach ($arr_f as $f) {
                            $f = trim($f);
                            if (!empty($f) && file_exists('uploads/' . $f)) {
                                echo '<div class="img-wrapper" id="foto-box-' . md5($f) . '">
                                        <img src="uploads/' . $f . '" alt="Foto">
                                        <button type="button" class="btn-del-img" onclick="hapusFoto(\'' . $f . '\', \'' . md5($f) . '\')">&times;</button>
                                      </div>';
                            }
                        }
                    } else {
                        echo '<span style="font-size:8.5pt; color:#777; font-style:italic;">Tidak ada foto.</span>';
                    }
                    ?>
                </div>
            </div>

            <div class="form-group">
                <label>Ganti / Tambah Foto Baru (Opsional):</label>
                <input type="file" name="dok[]" multiple>
            </div>

            <button type="submit" name="update" class="btn-simpan">Simpan Perubahan</button>
            <a href="hapus_draft.php?id=<?= $id; ?>" class="btn-hapus" onclick="return confirm('Yakin ingin menghapus kegiatan ini?')">Hapus Kegiatan Ini</a>
            <a href="draft_laporan.php" class="btn-kembali">Batal</a>
        </form>
    </div>
</div>

<script>
    const currentOpsi = "<?= htmlspecialchars($data['opsi']); ?>";

    // Struktur Tupoksi sesuai permintaan
    const masterData = {
        "Siaga SAR": {
            jenis: {
                "Siaga SAR Rutin": ["Briefing Siaga", "Pengecekan Kendaraan", "Pengecekan Palsar", "Jurnal Siaga SAR", "Monitoring "],
                "Siaga SAR Khusus": ["Briefing Siaga", "Pengecekan Kendaraan", "Pengecekan Palsar", "Jurnal Siaga SAR", "Monitoring "]
            },
            subLangsung: []
        },
        "Pembinaan Rescuer": {
            jenis: {
                "Pembinaan Fisik": ["Samapta A & B", "Samapta C"],
                "Keterampilan Teknis": ["HART", "Jungle", "MFR", "Water", "Urban SAR"],
                "Uji Periodik": ["Semester 1", "Semester 2"]
            },
            subLangsung: []
        },
        "Pemeliharaan Palsar": {
            jenis: {},
            subLangsung: ["Alat Air (Water Rescue)", "Alat Ekstrikasi", "Alat HART", "Alat Jungle"]
        },
        "Operasi SAR": {
            jenis: {},
            subLangsung: ["Kondisi Membahayakan Manusia", "Kecelakaan Darat", "Kecelakaan Udara", "Kecelakaan Laut"]
        },
        "Kegiatan Lainnya": {
            jenis: {},
            subLangsung: ["Narasumber", "Koordinasi"]
        }
    };

    function initForm() {
        let foundMenu = "";
        let foundJenis = "";
        let foundSub = "";

        for (let menu in masterData) {
            let item = masterData[menu];
            
            // Cek di subLangsung
            if (item.subLangsung.includes(currentOpsi)) {
                foundMenu = menu;
                foundSub = currentOpsi;
                break;
            }
            
            // Cek di jenis & sub-nya
            for (let jenis in item.jenis) {
                if (jenis === currentOpsi) {
                    foundMenu = menu;
                    foundJenis = jenis;
                    break;
                }
                if (item.jenis[jenis].includes(currentOpsi)) {
                    foundMenu = menu;
                    foundJenis = jenis;
                    foundSub = currentOpsi;
                    break;
                }
            }
            if (foundMenu) break;
        }

        if (!foundMenu) {
            foundMenu = "Kegiatan Lainnya";
            foundSub = currentOpsi;
        }

        document.getElementById('menu_utama').value = foundMenu;
        handleMenuChange(foundJenis, foundSub);
    }

    function handleMenuChange(selectedJenis = "", selectedSub = "") {
        const menuVal = document.getElementById('menu_utama').value;
        const jenisGroup = document.getElementById('jenis-kegiatan-group');
        const jenisSelect = document.getElementById('jenis_kegiatan');
        const subGroup = document.getElementById('sub-kegiatan-group');
        const subSelect = document.getElementById('nama_kegiatan');

        jenisSelect.innerHTML = '<option value="">-- Pilih Jenis Kegiatan --</option>';
        subSelect.innerHTML = '<option value="">-- Pilih Sub Kegiatan --</option>';
        jenisGroup.style.display = 'none';
        subGroup.style.display = 'none';

        if (menuVal && masterData[menuVal]) {
            let jenisList = Object.keys(masterData[menuVal].jenis);
            let subLangsungList = masterData[menuVal].subLangsung;

            if (jenisList.length > 0) {
                jenisGroup.style.display = 'block';
                jenisList.forEach(j => {
                    let opt = document.createElement('option');
                    opt.value = j;
                    opt.textContent = j;
                    if (j === selectedJenis) opt.selected = true;
                    jenisSelect.appendChild(opt);
                });
            } else if (subLangsungList.length > 0) {
                subGroup.style.display = 'block';
                subLangsungList.forEach(s => {
                    let opt = document.createElement('option');
                    opt.value = s;
                    opt.textContent = s;
                    if (s === selectedSub) opt.selected = true;
                    subSelect.appendChild(opt);
                });
            }
        }

        if (selectedJenis) {
            jenisSelect.value = selectedJenis;
            handleJenisChange(selectedSub);
        }
    }

    function handleJenisChange(selectedSub = "") {
        const menuVal = document.getElementById('menu_utama').value;
        const jenisVal = document.getElementById('jenis_kegiatan').value;
        const subGroup = document.getElementById('sub-kegiatan-group');
        const subSelect = document.getElementById('nama_kegiatan');

        subSelect.innerHTML = '<option value="">-- Pilih Sub Kegiatan --</option>';
        subGroup.style.display = 'none';

        if (menuVal && masterData[menuVal].jenis[jenisVal]) {
            let subList = masterData[menuVal].jenis[jenisVal];
            if (subList.length > 0) {
                subGroup.style.display = 'block';
                subList.forEach(s => {
                    let opt = document.createElement('option');
                    opt.value = s;
                    opt.textContent = s;
                    if (s === selectedSub) opt.selected = true;
                    subSelect.appendChild(opt);
                });
            }
        }
    }

    function hapusFoto(namaFoto, boxId) {
        if (confirm('Yakin ingin menghapus foto ini?')) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    if (xhr.responseText.trim() === "success") {
                        const el = document.getElementById('foto-box-' + boxId);
                        if (el) el.remove();
                    } else {
                        alert("Gagal menghapus foto.");
                    }
                }
            };
            xhr.send("hapus_foto_nama=" + encodeURIComponent(namaFoto));
        }
    }
</script>

</body>
</html>