<?php
session_start();
include 'koneksi.php';

$id_draft = isset($_GET['id']) ? intval($_GET['id']) : 0;
$query_draft = mysqli_query($koneksi, "SELECT * FROM draft_laporan WHERE id = '$id_draft'");
$draft = mysqli_fetch_assoc($query_draft);

if (!$draft) {
    echo "Draft laporan tidak ditemukan!";
    exit;
}

$tanggal = $draft['tanggal'];
$list_kegiatan = mysqli_query($koneksi, "SELECT * FROM detail_kegiatan WHERE draft_id = '$id_draft' ORDER BY jam_kegiatan ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jurnal Harian Kegiatan - Unit Siaga SAR Bogor</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10pt; color: #000; margin: 20px; line-height: 1.3; }
        .header { text-align: center; font-weight: bold; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header h3 { margin: 2px 0; text-transform: uppercase; font-size: 12pt; }
        .info-tgl { margin-bottom: 15px; font-size: 11pt; font-weight: bold; }
        .kegiatan-section { border: 1px solid #000; padding: 10px; margin-bottom: 15px; page-break-inside: avoid; }
        .jam-badge { background: #eee; padding: 3px 6px; font-weight: bold; border: 1px solid #000; display: inline-block; margin-bottom: 5px; }
        .foto-eviden { width: 45%; max-height: 180px; object-fit: cover; border: 1px solid #000; margin-top: 5px; }
        
        /* STYLE KHUSUS GAMBAR TEMPLATE FULL (PENGECEKAN PALSAR & JURNAL ABSENSI) */
        .template-full-container {
            text-align: center;
            width: 100%;
            margin-top: 10px;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .template-full-img {
            width: 90%;
            max-width: 800px;
            height: auto;
            border: 1px solid #000;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            display: block;
            margin: 0 auto;
        }

        .ttd-area { margin-top: 40px; float: right; text-align: center; width: 35%; page-break-inside: avoid; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>

    <div class="no-print" style="background: #fffae6; padding: 10px; border: 1px solid #ffeeba; margin-bottom: 20px; text-align: center;">
        <b>Jurnal Siap Cetak:</b> Klik tombol di bawah ini lalu pilih <b>"Save as PDF"</b>.
        <br><br>
        <button onclick="window.print()" style="background: #d35400; color: white; border: none; padding: 8px 15px; font-weight: bold; border-radius: 4px; cursor: pointer;">🖨️ Cetak / Simpan PDF</button>
    </div>

    <div class="header">
        <h3>JURNAL KEGIATAN HARIAN OPERASIONAL</h3>
        <h3>UNIT SIAGA SAR BOGOR</h3>
    </div>

    <div class="info-tgl">
        Hari / Tanggal : <?php echo date('l, d F Y', strtotime($tanggal)); ?>
    </div>

    <?php 
    $no = 1;
    while ($row = mysqli_fetch_assoc($list_kegiatan)) {
        $nama_kegiatan = $row['nama_kegiatan'];
        
        // CEK APAKAH INI PENGECEKAN PALSAR ATAU JURNAL ABSENSI
        if ($nama_kegiatan == 'Pengecekan Palsar' || strpos($nama_kegiatan, 'Jurnal') !== false || strpos($nama_kegiatan, 'Absensi') !== false) {
            echo '<div class="template-full-container">';
            echo '<h3 style="text-transform: uppercase; margin-bottom: 5px;">' . htmlspecialchars($nama_kegiatan) . '</h3>';
            
            if (!empty($row['dokumentasi']) && file_exists('uploads/' . $row['dokumentasi'])) {
                echo '<img src="uploads/' . $row['dokumentasi'] . '" class="template-full-img">';
            } else {
                echo '<p style="color: red; font-style: italic;">Foto / Lembar template ' . htmlspecialchars($nama_kegiatan) . ' belum diunggah.</p>';
            }
            echo '</div>';
        } else {
            // TAMPILAN NORMAL (UNTUK KEGIATAN BIASA / BRIEFING / DLL)
            echo '<div class="kegiatan-section">';
            echo '<div class="jam-badge">Pukul ' . date('H:i', strtotime($row['jam_kegiatan'])) . ' WIB</div>';
            echo '<p><b>Nama Kegiatan:</b> ' . htmlspecialchars($nama_kegiatan) . '</p>';
            echo '<p><b>Uraian / Deskripsi:</b><br>' . nl2br(htmlspecialchars($row['deskripsi'])) . '</p>';
            if (!empty($row['dokumentasi']) && file_exists('uploads/' . $row['dokumentasi'])) {
                echo '<p><b>Dokumentasi:</b><br><img src="uploads/' . $row['dokumentasi'] . '" class="foto-eviden"></p>';
            }
            echo '</div>';
        }
        $no++;
    }
    ?>

    <div class="ttd-area">
        <p>Bogor, <?php echo date('d F Y', strtotime($tanggal)); ?></p>
        <p><b>Komandan Tim / Pemeriksa</b></p>
        <br><br><br>
        <p><b>( _________________________ )</b></p>
    </div>

</body>
</html>