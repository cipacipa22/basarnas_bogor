<?php
// Panggil koneksi database
include 'koneksi.php';

// Ambil filter bulan & tahun
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

$nama_bulan_indo = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];
$nama_bulan = isset($nama_bulan_indo[$bulan]) ? $nama_bulan_indo[$bulan] : date('F', mktime(0, 0, 0, $bulan, 10));

$query = mysqli_query($koneksi, "SELECT * FROM laporan_final WHERE MONTH(tanggal) = '$bulan' AND YEAR(tanggal) = '$tahun' ORDER BY tanggal ASC");

$kategori_struktur = [
    'Pembinaan Rescuer' => [
        'Pembinaan Fisik' => ['Samapta A & B', 'Samapta C'],
        'Keterampilan Teknis' => ['HART', 'Jungle', 'MFR', 'Water', 'Urban SAR'],
        'Uji Periodik' => ['Semester 1', 'Semester 2']
    ],
    'Pemeliharaan Palsar' => [
        'Palsar' => ['Alat air (Water rescue)', 'Alat ekstrikasi', 'Alat HART', 'Alat Jungle', 'Pemeliharaan Palsar', 'Alat Air', 'Alat Ektrifikasi']
    ],
    'Operasi SAR' => [
        'Operasi' => ['Kondisi membahayakan manusia', 'Kecelakaan darat', 'Kecelakaan udara', 'Kecelakaan laut']
    ],
    'Kegiatan Lainnya' => [
        'Lainnya' => ['Narasumber', 'Koordinasi']
    ]
];

$rekap_data = [];
foreach ($kategori_struktur as $kat => $subkat_list) {
    foreach ($subkat_list as $subkat => $items) {
        foreach ($items as $item) {
            $rekap_data[$kat][$item] = ['jumlah' => 0, 'tanggal' => []];
        }
    }
}

// Array penampung data eviden dari database per kategori & per minggu (1-4)
$eviden_kategori_minggu = [
    'Pembinaan Rescuer' => [1 => [], 2 => [], 3 => [], 4 => []],
    'Pemeliharaan Palsar' => [1 => [], 2 => [], 3 => [], 4 => []],
    'Operasi SAR' => [1 => [], 2 => [], 3 => [], 4 => []],
    'Kegiatan Lainnya' => [1 => [], 2 => [], 3 => [], 4 => []]
];

while ($row = mysqli_fetch_assoc($query)) {
    $nama_kegiatan_db  = isset($row['nama_kegiatan']) ? trim($row['nama_kegiatan']) : ''; 
    $jenis_kegiatan_db = isset($row['jenis_kegiatan']) ? trim($row['jenis_kegiatan']) : ''; 
    $opsi_fallback     = isset($row['opsi']) ? trim($row['opsi']) : '';

    $gabung_teks = strtolower($nama_kegiatan_db . ' ' . $jenis_kegiatan_db . ' ' . $opsi_fallback);

    if (strpos($gabung_teks, 'siaga') !== false || strpos($gabung_teks, 'foto tabel') !== false || strpos($gabung_teks, 'jurnal absensi') !== false || strpos($gabung_teks, 'briefing') !== false || strpos($gabung_teks, 'kendaraan') !== false) {
        continue;
    }

    $tgl = $row['tanggal'];
    $day = (int)date('d', strtotime($tgl));
    $minggu_ke = ceil($day / 7);
    if ($minggu_ke > 4) $minggu_ke = 4;

    $sudah_masuk = false;
    $kategori_terpilih = '';

    if (strpos($gabung_teks, 'operasi') !== false || strpos($gabung_teks, 'kecelakaan') !== false || strpos($gabung_teks, 'kondisi membahayakan') !== false) {
        $item_ops = 'Kondisi membahayakan manusia';
        if (strpos($gabung_teks, 'darat') !== false) $item_ops = 'Kecelakaan darat';
        elseif (strpos($gabung_teks, 'udara') !== false) $item_ops = 'Kecelakaan udara';
        elseif (strpos($gabung_teks, 'laut') !== false) $item_ops = 'Kecelakaan laut';

        $rekap_data['Operasi SAR'][$item_ops]['jumlah'] += 1;
        if(!in_array($tgl, $rekap_data['Operasi SAR'][$item_ops]['tanggal'])) {
            $rekap_data['Operasi SAR'][$item_ops]['tanggal'][] = $tgl;
        }
        
        $kategori_terpilih = 'Operasi SAR';
        $sudah_masuk = true;
    }
    elseif (strpos($gabung_teks, 'palsar') !== false || strpos($gabung_teks, 'alat') !== false || strpos($gabung_teks, 'ekstrikasi') !== false) {
        $item_palsar = 'Pemeliharaan Palsar';
        if (strpos($gabung_teks, 'air') !== false) $item_palsar = 'Alat air (Water rescue)';
        elseif (strpos($gabung_teks, 'ekstrikasi') !== false || strpos($gabung_teks, 'ektrifikasi') !== false) $item_palsar = 'Alat ekstrikasi';
        elseif (strpos($gabung_teks, 'hart') !== false) $item_palsar = 'Alat HART';
        elseif (strpos($gabung_teks, 'jungle') !== false) $item_palsar = 'Alat Jungle';

        if (!isset($rekap_data['Pemeliharaan Palsar'][$item_palsar])) {
            $rekap_data['Pemeliharaan Palsar'][$item_palsar] = ['jumlah' => 0, 'tanggal' => []];
        }

        $rekap_data['Pemeliharaan Palsar'][$item_palsar]['jumlah'] += 1;
        if(!in_array($tgl, $rekap_data['Pemeliharaan Palsar'][$item_palsar]['tanggal'])) {
            $rekap_data['Pemeliharaan Palsar'][$item_palsar]['tanggal'][] = $tgl;
        }

        $kategori_terpilih = 'Pemeliharaan Palsar';
        $sudah_masuk = true;
    }

    if (!$sudah_masuk) {
        foreach ($kategori_struktur as $kat => $subkat_list) {
            foreach ($subkat_list as $subkat => $items) {
                foreach ($items as $item) {
                    if (stripos($gabung_teks, strtolower($item)) !== false || stripos(strtolower($item), $gabung_teks) !== false) {
                        $rekap_data[$kat][$item]['jumlah'] += 1;
                        if(!in_array($tgl, $rekap_data[$kat][$item]['tanggal'])) {
                            $rekap_data[$kat][$item]['tanggal'][] = $tgl;
                        }
                        $kategori_terpilih = $kat;
                        $sudah_masuk = true;
                        break 3;
                    }
                }
            }
        }
    }

    if ($sudah_masuk && isset($eviden_kategori_minggu[$kategori_terpilih][$minggu_ke])) {
        $eviden_kategori_minggu[$kategori_terpilih][$minggu_ke][] = [
            'tgl' => date('d-m-Y', strtotime($tgl)),
            'nama' => $nama_kegiatan_db != '' ? $nama_kegiatan_db : $jenis_kegiatan_db,
            'uraian' => $jenis_kegiatan_db . (!empty($opsi_fallback) ? ' - ' . $opsi_fallback : ''),
            'img' => isset($row['gambar']) && !empty($row['gambar']) ? $row['gambar'] : ''
        ];
    }
}

$chart_per_kategori = [];
foreach ($kategori_struktur as $kat => $subkat_list) {
    $labels = [];
    $data = [];
    foreach ($subkat_list as $subkat => $items) {
        foreach ($items as $item) {
            $jml = $rekap_data[$kat][$item]['jumlah'];
            if ($jml > 0) {
                $labels[] = $item;
                $data[] = $jml;
            }
        }
    }
    $chart_per_kategori[$kat] = [
        'labels' => $labels,
        'data' => $data
    ];
}

$chart_grouped = [];
foreach ($kategori_struktur as $kat => $subkat_list) {
    foreach ($subkat_list as $subkat => $items) {
        foreach ($items as $item) {
            $jml = $rekap_data[$kat][$item]['jumlah'];
            if ($jml > 0) {
                if ($kat == 'Pemeliharaan Palsar') {
                    $label_chart = $item; 
                } else {
                    if (stripos($item, 'Samapta') !== false) {
                        $label_chart = 'Samapta A & B';
                    } elseif (stripos($item, 'Kondisi membahayakan') !== false) {
                        $label_chart = 'Kondisi Membahayakan Manusia';
                    } elseif (stripos($item, 'Kecelakaan darat') !== false) {
                        $label_chart = 'Kecelakaan Darat';
                    } elseif (stripos($item, 'Kecelakaan udara') !== false) {
                        $label_chart = 'Kecelakaan Udara';
                    } elseif (stripos($item, 'Kecelakaan laut') !== false) {
                        $label_chart = 'Kecelakaan Laut';
                    } else {
                        $label_chart = $item;
                    }
                }

                if (!isset($chart_grouped[$label_chart])) {
                    $chart_grouped[$label_chart] = 0;
                }
                $chart_grouped[$label_chart] += $jml;
            }
        }
    }
}

$chart_labels_final = [];
$chart_data_final = [];
foreach($chart_grouped as $lbl => $val) {
    if($val > 0) {
        $chart_labels_final[] = $lbl;
        $chart_data_final[] = $val;
    }
}

$total_kegiatan_all = 0;
$rekap_totals = [
    'Kesiapsiagaan' => 0,
    'Pemeliharaan PALSAR' => 0,
    'Pembinaan Kompetensi' => 0,
    'Operasi SAR' => 0,
    'Kegiatan Lainnya' => 0
];

foreach ($kategori_struktur as $kat => $subkat_list) {
    $total_kat = 0;
    foreach ($subkat_list as $subkat => $items) {
        foreach ($items as $item) {
            $total_kat += $rekap_data[$kat][$item]['jumlah'];
        }
    }
    $total_kegiatan_all += $total_kat;
    
    if ($kat == 'Pembinaan Rescuer') $rekap_totals['Pembinaan Kompetensi'] += $total_kat;
    elseif ($kat == 'Pemeliharaan Palsar') $rekap_totals['Pemeliharaan PALSAR'] += $total_kat;
    elseif ($kat == 'Operasi SAR') $rekap_totals['Operasi SAR'] += $total_kat;
    elseif ($kat == 'Kegiatan Lainnya') $rekap_totals['Kegiatan Lainnya'] += $total_kat;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Bulanan - Unit Siaga SAR Bogor</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-datalabels/2.2.0/chartjs-plugin-datalabels.min.js"></script>
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
            max-width: 850px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding: 0 4px;
        }
        .header h2 {
            margin: 0;
            font-size: 13pt;
            letter-spacing: 1px;
            font-weight: 800;
            color: #1f2c34;
            text-align: center;
        }
        .btn-kembali, .btn-action {
            text-decoration: none;
            color: #1f2c34;
            font-weight: bold;
            font-size: 10pt;
            background: rgba(255, 255, 255, 0.4);
            padding: 6px 12px;
            border-radius: 12px;
            transition: 0.2s;
            border: none;
            cursor: pointer;
        }
        .btn-kembali:hover, .btn-action:hover {
            background: rgba(255, 255, 255, 0.7);
        }
        .btn-print {
            background: #27ae60;
            color: white;
        }
        .btn-print:hover {
            background: #219653;
        }

        .card-box {
            background: #ffffff;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            margin-bottom: 15px;
        }

        .filter-form {
            display: flex;
            gap: 8px;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            background: #f8f9fa;
            padding: 12px;
            border-radius: 12px;
        }
        .filter-form select, .filter-form input {
            padding: 6px 8px;
            border: 1px solid #ced4da;
            border-radius: 8px;
            font-size: 9.5pt;
        }
        .filter-form button {
            padding: 7px 14px;
            background: #ff914d;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            font-size: 9.5pt;
        }
        .filter-form button:hover {
            background: #e07a35;
        }

        .kategori-box {
            background: #fff9f0;
            border: 1px solid #ffe3cc;
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 12px;
        }
        .kategori-title {
            font-weight: 800;
            font-size: 10pt;
            color: #d35400;
            margin-bottom: 8px;
            border-bottom: 2px solid #ffd5b5;
            padding-bottom: 4px;
        }
        .sub-kategori-title {
            font-weight: 700;
            font-size: 9pt;
            color: #333;
            margin-top: 8px;
            margin-bottom: 4px;
        }
        .info-item {
            font-size: 9pt;
            padding: 4px 0;
            color: #444;
            border-bottom: 1px dashed #eee;
        }
        .info-item:last-child {
            border-bottom: none;
        }

        .chart-container {
            position: relative;
            height: 350px;
            width: 100%;
            max-width: 480px;
            margin: 20px auto;
            background: #fafafa;
            border: 1px solid #eee;
            border-radius: 12px;
            padding: 15px;
        }

        .empty-text {
            text-align: center;
            color: #777;
            font-size: 9.5pt;
            padding: 15px 0;
        }

        .editor-section {
            background: #fff;
            border: 2px dashed #ff914d;
            border-radius: 12px;
            padding: 25px;
            margin-top: 20px;
        }
        .editor-header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            background: #fff3e0;
            padding: 10px 15px;
            border-radius: 8px;
        }
        .word-document {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
            background: #fdfbf7;
            padding: 30px;
            border: 1px solid #ccc;
            box-shadow: inset 0 0 10px rgba(0,0,0,0.03);
            min-height: 500px;
        }
        .word-document p {
            text-align: justify;
            margin: 0 0 10px 0;
        }
        .word-document ul, .word-document ol {
            text-align: justify;
            margin-bottom: 10px;
        }
        .word-document [contenteditable="true"] {
            outline: 1px dashed #3498db;
            padding: 2px 4px;
            border-radius: 3px;
            background: rgba(52, 152, 219, 0.05);
            transition: 0.2s;
        }
        .word-document [contenteditable="true"]:focus {
            outline: 2px solid #2980b9;
            background: rgba(52, 152, 219, 0.1);
        }
        .word-title {
            text-align: center;
            font-weight: bold;
            font-size: 14pt;
            margin-bottom: 5px;
        }
        .word-subtitle {
            text-align: center;
            font-weight: bold;
            font-size: 13pt;
            margin-bottom: 20px;
        }
        .word-section-heading {
            font-weight: bold;
            font-size: 12pt;
            margin-top: 15px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .word-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .word-table th, .word-table td {
            border: 1px solid #000;
            padding: 6px 10px;
            font-size: 11pt;
            text-align: left;
        }
        .word-table th {
            text-align: center;
            background: #eee;
        }

        .page-break {
            page-break-after: always;
            margin-top: 40px;
        }

        .eviden-img-wrapper {
            position: relative;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 85px;
            border: 1px dashed #ccc;
            border-radius: 3px;
            background: #fafafa;
            margin-bottom: 4px;
            overflow: hidden;
        }
        .eviden-img-wrapper:hover::after {
            content: "Ganti Foto";
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.6);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8pt;
            font-weight: bold;
        }

        @media print {
            body {
                background: white !important;
                padding: 0 !important;
            }
            .mobile-container > div > *:not(.editor-section), .filter-form, .header, .btn-action, .chart-container:not(.word-document .chart-container), .card-box:not(:has(.editor-section)) {
                display: none !important;
            }
            .editor-section {
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .editor-header-bar {
                display: none !important;
            }
            .word-document {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                background: white !important;
            }
            .word-document [contenteditable="true"] {
                outline: none !important;
                background: transparent !important;
            }
            .eviden-img-wrapper:hover::after {
                display: none !important;
            }
            .upload-text-label {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="mobile-container">
    <div>
        <div class="header">
            <a href="dashboard.php" class="btn-kembali">&larr; Kembali</a>
            <h2>REKAP BULANAN</h2>
            <button onclick="window.print()" class="btn-action btn-print">&#128438; Cetak PDF</button>
        </div>

        <div class="card-box">
            <form method="GET" class="filter-form">
                <div>
                    <select name="bulan">
                        <?php for($i=1; $i<=12; $i++): ?>
                            <option value="<?= sprintf('%02d', $i) ?>" <?= ($bulan == sprintf('%02d', $i)) ? 'selected' : '' ?>>
                                <?= $nama_bulan_indo[sprintf('%02d', $i)] ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <input type="number" name="tahun" value="<?= $tahun ?>" style="width: 65px;">
                </div>
                <button type="submit">Filter</button>
            </form>

            <h4 style="margin: 0 0 12px 0; font-size: 10.5pt; color: #1f2c34;">Rincian Kuantitas Kegiatan:</h4>

            <?php 
            $ada_data = false;
            foreach ($kategori_struktur as $kat => $subkat_list) {
                $ada_isi_kat = false;
                foreach ($subkat_list as $subkat => $items) {
                    foreach ($items as $item) {
                        if ($rekap_data[$kat][$item]['jumlah'] > 0) {
                            $ada_isi_kat = true;
                            $ada_data = true;
                        }
                    }
                }

                if ($ada_isi_kat):
            ?>
                <div class="kategori-box">
                    <div class="kategori-title"><?= $kat ?></div>
                    <?php foreach ($subkat_list as $subkat => $items): 
                        $filtered_items = array_filter($items, function($it) use ($rekap_data, $kat) {
                            return $rekap_data[$kat][$it]['jumlah'] > 0;
                        });

                        if (!empty($filtered_items)):
                    ?>
                        <?php if ($subkat != 'Palsar' && $subkat != 'Operasi' && $subkat != 'Lainnya'): ?>
                            <div class="sub-kategori-title"><?= $subkat ?>:</div>
                        <?php endif; ?>

                        <?php foreach ($filtered_items as $item): 
                            $val = $rekap_data[$kat][$item];
                        ?>
                            <div class="info-item">
                                &bull; <b><?= $item ?></b>: <?= $val['jumlah']; ?>x (Tgl: <?= implode(', ', array_unique($val['tanggal'])); ?>)
                            </div>
                        <?php endforeach; ?>

                    <?php endif; endforeach; ?>

                    <?php if (!empty($chart_per_kategori[$kat]['data'])): ?>
                        <div class="chart-container" style="height: 250px; max-width: 350px;">
                            <canvas id="chart_<?= md5($kat) ?>"></canvas>
                        </div>
                    <?php endif; ?>
                </div>
            <?php 
                endif;
            } 

            if (!$ada_data):
            ?>
                <div class="empty-text">Tidak ada data rekap kegiatan pada bulan ini.</div>
            <?php endif; ?>
        </div>

        <div class="editor-section">
            <div class="editor-header-bar">
                <span style="font-size: 9.5pt; font-weight: bold; color: #d35400;">&#9998; Format Dokumen Siap Edit & Cetak PDF</span>
                <button onclick="window.print()" class="btn-action btn-print" style="font-size: 9pt; padding: 4px 10px;">Cetak / Simpan PDF</button>
            </div>
            <div class="word-document" contenteditable="true" spellcheck="false">
                <div style="height: 100vh; display: flex; flex-direction: column; justify-content: space-between; align-items: center; text-align: center; page-break-after: always; padding: 40px 20px; box-sizing: border-box;">
                    <div class="word-title" style="font-size: 14pt; font-weight: bold; width: 100%;">
                        LAPORAN BULANAN KEGIATAN PETUGAS PRANATA PENCARIAN DAN PERTOLONGAN
                    </div>
                    <div style="margin: auto 0;">
                        <img src="assets/img/logo_sar.png" alt="Logo SAR" style="width: 300px; height: auto; display: block; margin: 0 auto;">
                    </div>
                    <div class="word-subtitle" style="font-size: 11pt; width: 100%;">
                        UNIT SIAGA SAR BOGOR<br>Bulan <span contenteditable="true" spellcheck="false"><?= $nama_bulan ?></span> Tahun <span contenteditable="true" spellcheck="false"><?= $tahun ?></span>
                    </div>
                </div>

                <div class="word-section-heading">I. PENDAHULUAN</div>
                <div style="font-size: 11pt; font-weight: bold; margin-top: 15px; margin-bottom: 5px;">A. Latar Belakang</div>
                <p>Pencarian dan Pertolongan merupakan salah satu bentuk pelayanan pemerintah dalam memberikan perlindungan dan bantuan kepada masyarakat yang mengalami kondisi membahayakan manusia, kecelakaan, maupun bencana. Dalam pelaksanaannya, kegiatan Pencarian dan Pertolongan membutuhkan kesiapsiagaan personel, ketersediaan serta kesiapan sarana dan prasarana, kemampuan teknis yang memadai, serta koordinasi dan kerja sama yang baik dengan berbagai pihak terkait.</p>
                <p>Unit Siaga SAR Bogor sebagai bagian dari Kantor Pencarian dan Pertolongan Jakarta memiliki peran dalam mendukung pelaksanaan tugas Pencarian dan Pertolongan di wilayah kerja yang menjadi tanggung jawabnya. Untuk menjamin kesiapan pelaksanaan tugas tersebut, Petugas Pranata Pencarian dan Pertolongan melaksanakan berbagai kegiatan secara rutin dan berkesinambungan, baik dalam kondisi siaga maupun pada saat pelaksanaan operasi SAR.</p>
                <p>Kegiatan yang dilaksanakan meliputi kesiapsiagaan Siaga SAR Rutin dan Siaga SAR Khusus, pemeliharaan peralatan SAR (PALSAR), pembinaan kompetensi personel, pelaksanaan Operasi SAR, serta kegiatan lainnya seperti menjadi narasumber dan melaksanakan lintas koordinasi. Seluruh kegiatan tersebut merupakan bagian dari upaya untuk menjaga kesiapan personel dan peralatan sehingga dapat memberikan respons yang cepat, tepat, aman, dan profesional dalam menghadapi setiap kejadian yang membutuhkan pelayanan Pencarian dan Pertolongan.</p>

                <div style="font-size: 11pt; font-weight: bold; margin-top: 15px; margin-bottom: 5px;">B. Maksud dan Tujuan</div>
                <p><b>1. Maksud:</b><br>Sebagai bahan dokumentasi dan pelaporan pelaksanaan kegiatan Petugas Pranata Pencarian dan Pertolongan Unit Siaga SAR Bogor selama satu periode.</p>
                <p><b>2. Tujuan:</b></p>
                <ul>
                    <li>Mendokumentasikan pelaksanaan kegiatan selama satu bulan.</li>
                    <li>Mengetahui tingkat kesiapsiagaan personel dan peralatan SAR.</li>
                    <li>Menjadi bahan evaluasi pelaksanaan tugas.</li>
                    <li>Meningkatkan profesionalisme dan kompetensi personel.</li>
                    <li>Menjadi bahan perencanaan kegiatan pada periode berikutnya.</li>
                </ul>

                <div style="font-size: 11pt; font-weight: bold; margin-top: 15px; margin-bottom: 5px;">C. Dasar Pelaksanaan</div>
                <ol>
                    <li>Undang-Undang Nomor 29 Tahun 2014 tentang Pencarian dan Pertolongan.</li>
                    <li>Peraturan Pemerintah Nomor 22 Tahun 2017 tentang Operasi Pencarian dan Pertolongan.</li>
                    <li>Peraturan Presiden Nomor 83 Tahun 2016 tentang Badan Nasional Pencarian dan Pertolongan.</li>
                    <li>Peraturan Badan Nasional Pencarian dan Pertolongan tentang Organisasi dan Tata Kerja Badan Nasional Pencarian dan Pertolongan.</li>
                    <li>Surat Tugas/Perintah dan ketentuan kedinasan yang berlaku di lingkungan Badan Nasional Pencarian dan Pertolongan.</li>
                    <li>Rencana dan Program Kerja Kantor Pencarian dan Pertolongan Jakarta serta Unit Siaga SAR Bogor.</li>
                </ol>

                <div class="word-section-heading">II. PELAKSANAAN KEGIATAN</div>
                <div style="font-size: 11pt; font-weight: bold; margin-top: 15px; margin-bottom: 5px;">A. Kesiapsiagaan</div>
                <p>Kegiatan kesiapsiagaan merupakan kegiatan yang dilaksanakan untuk memastikan kesiapan personel, peralatan, kendaraan, sarana komunikasi, serta perlengkapan pendukung dalam menghadapi setiap kejadian yang membutuhkan pelayanan Pencarian dan Pertolongan.</p>

                <div style="font-size: 11pt; font-weight: bold; margin-top: 15px; margin-bottom: 5px;">B. Pemeliharaan PALSAR</div>
                <p>Kegiatan pemeliharaan PALSAR dilaksanakan untuk menjaga kondisi, kelengkapan, fungsi, dan kesiapan peralatan SAR agar selalu dalam keadaan baik dan dapat digunakan sewaktu-waktu dalam pelaksanaan tugas.</p>

                <div style="font-size: 11pt; font-weight: bold; margin-top: 15px; margin-bottom: 5px;">C. Pembinaan Kompetensi</div>
                <p>Pembinaan kompetensi merupakan kegiatan untuk meningkatkan pengetahuan, keterampilan, kemampuan teknis, serta kesiapan personel dalam melaksanakan tugas Pencarian dan Pertolongan.</p>

                <div style="font-size: 11pt; font-weight: bold; margin-top: 15px; margin-bottom: 5px;">D. Operasi SAR</div>
                <p>Operasi SAR merupakan kegiatan Pencarian dan Pertolongan yang dilaksanakan sebagai respons terhadap laporan atau informasi mengenai kejadian yang membutuhkan penanganan SAR.</p>

                <div style="font-size: 11pt; font-weight: bold; margin-top: 15px; margin-bottom: 5px;">E. Kegiatan Lainnya</div>
                <p>Kegiatan lainnya merupakan kegiatan pendukung yang dilaksanakan dalam rangka menunjang pelaksanaan tugas dan fungsi Petugas Pranata Pencarian dan Pertolongan.</p>

                <div class="word-section-heading">III. REKAPITULASI KEGIATAN</div>
                <table class="word-table">
                    <thead>
                        <tr>
                            <th style="width: 10%;">NO</th>
                            <th style="width: 70%;">JENIS KEGIATAN</th>
                            <th style="width: 20%;">JUMLAH</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="text-align: center;">1</td>
                            <td>Kesiapsiagaan</td>
                            <td style="text-align: center;" contenteditable="true" spellcheck="false"><?= $rekap_totals['Kesiapsiagaan'] > 0 ? $rekap_totals['Kesiapsiagaan'] : '-' ?></td>
                        </tr>
                        <tr>
                            <td style="text-align: center;">2</td>
                            <td>Pemeliharaan PALSAR</td>
                            <td style="text-align: center;" contenteditable="true" spellcheck="false"><?= $rekap_totals['Pemeliharaan PALSAR'] ?></td>
                        </tr>
                        <tr>
                            <td style="text-align: center;">3</td>
                            <td>Pembinaan Kompetensi</td>
                            <td style="text-align: center;" contenteditable="true" spellcheck="false"><?= $rekap_totals['Pembinaan Kompetensi'] ?></td>
                        </tr>
                        <tr>
                            <td style="text-align: center;">4</td>
                            <td>Operasi SAR</td>
                            <td style="text-align: center;" contenteditable="true" spellcheck="false"><?= $rekap_totals['Operasi SAR'] ?></td>
                        </tr>
                        <tr>
                            <td style="text-align: center;">5</td>
                            <td>Kegiatan Lainnya</td>
                            <td style="text-align: center;" contenteditable="true" spellcheck="false"><?= $rekap_totals['Kegiatan Lainnya'] ?></td>
                        </tr>
                        <tr style="font-weight: bold; background: #f2f2f2;">
                            <td colspan="2" style="text-align: right;">TOTAL</td>
                            <td style="text-align: center;" contenteditable="true" spellcheck="false"><?= $total_kegiatan_all ?></td>
                        </tr>
                    </tbody>
                </table>

                <?php if (!empty($chart_data_final)): ?>
                    <div style="text-align: center; margin: 25px 0;">
                        <p style="font-weight: bold; margin-bottom: 5px;">Grafik Persentase Kuantitas Jenis Kegiatan</p>
                        <div class="chart-container" style="display: inline-block;">
                            <canvas id="grafikPieDoc"></canvas>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="word-section-heading">IV. PENUTUP</div>
                <div style="font-size: 11pt; font-weight: bold; margin-top: 15px; margin-bottom: 5px;">A. Kesimpulan</div>
                <p>Pelaksanaan kegiatan Petugas Pranata Pencarian dan Pertolongan Unit Siaga SAR Bogor selama periode <span contenteditable="true" spellcheck="false"><?= $nama_bulan ?></span> Tahun <span contenteditable="true" spellcheck="false"><?= $tahun ?></span> secara umum telah terlaksana dengan baik.</p>
                
                <div style="font-size: 11pt; font-weight: bold; margin-top: 15px; margin-bottom: 5px;">B. Saran</div>
                <p>Dalam rangka meningkatkan kualitas dan efektivitas pelaksanaan tugas, diperlukan peningkatan kompetensi personel secara berkelanjutan.</p>

                <!-- V. LAMPIRAN EVIDEN KEGIATAN PER KATEGORI & PER MINGGU -->
                <div class="page-break"></div>
                <div class="word-section-heading">V. LAMPIRAN EVIDEN KEGIATAN</div>
                
                <?php 
                $daftar_kategori = [
                    'Pembinaan Rescuer',
                    'Pemeliharaan Palsar',
                    'Operasi SAR',
                    'Kegiatan Lainnya'
                ];

                foreach ($daftar_kategori as $kat_item):
                ?>
                    <div style="font-size: 11.5pt; font-weight: bold; margin-top: 25px; margin-bottom: 10px; color: #d35400; border-bottom: 1.5px solid #d35400; padding-bottom: 4px;">
                        <?= $kat_item ?>
                    </div>

                    <!-- Container Foto Berderet 4 Minggu -->
                    <div style="display: flex; justify-content: space-between; gap: 8px; margin-bottom: 15px; flex-wrap: wrap;">
                        <?php for ($m = 1; $m <= 4; $m++): 
                            $rows_eviden = $eviden_kategori_minggu[$kat_item][$m] ?? [];
                            $ev_first = !empty($rows_eviden) ? $rows_eviden[0] : null;
                            $unique_id = 'img_upload_' . md5($kat_item . $m);
                            $date_id = 'date_label_' . md5($kat_item . $m);
                        ?>
                            <div style="flex: 1; min-width: 110px; text-align: center; border: 1px solid #ccc; padding: 6px; border-radius: 6px; background: #fff;">
                                <div style="font-weight: bold; font-size: 8.5pt; margin-bottom: 5px; background: #ff914d; color: white; padding: 2px 0; border-radius: 3px;" contenteditable="true" spellcheck="false">
                                    Minggu Ke-<?= $m ?>
                                </div>
                                
                                <!-- Input File Tersembunyi -->
                                <input type="file" id="<?= $unique_id ?>" accept="image/*" style="display: none;" onchange="previewManualImage(event, 'preview_<?= $unique_id ?>', 'text_<?= $unique_id ?>', '<?= $date_id ?>')">

                                <div class="eviden-img-wrapper" onclick="document.getElementById('<?= $unique_id ?>').click();" title="Klik untuk upload foto manual">
                                    <?php if ($ev_first && !empty($ev_first['img']) && file_exists($ev_first['img'])): ?>
                                        <img id="preview_<?= $unique_id ?>" src="<?= $ev_first['img'] ?>" alt="Eviden Minggu <?= $m ?>" style="width: 100%; height: 85px; object-fit: cover; display: block;">
                                        <span id="text_<?= $unique_id ?>" class="upload-text-label" style="display: none; font-size: 8.5pt; color: #777; font-weight: bold;">Upload Foto</span>
                                    <?php else: ?>
                                        <img id="preview_<?= $unique_id ?>" src="" style="display: none; width: 100%; height: 85px; object-fit: cover;">
                                        <span id="text_<?= $unique_id ?>" class="upload-text-label" style="font-size: 8.5pt; color: #777; font-weight: bold;">Upload Foto</span>
                                    <?php endif; ?>
                                </div>

                                <!-- TANGGAL (Di atas keterangan) -->
                                <div id="<?= $date_id ?>" style="font-size: 8pt; color: #222; font-weight: bold; margin-top: 4px; margin-bottom: 2px;" contenteditable="true" spellcheck="false"><?= $ev_first ? $ev_first['tgl'] : '-' ?></div>
                                
                                <!-- NAMA KEGIATAN / KETERANGAN (Di bawah tanggal) -->
                                <div style="font-size: 7.5pt; color: #555; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" contenteditable="true" spellcheck="false" title="<?= $ev_first ? $ev_first['nama'] : 'Keterangan' ?>"><?= $ev_first ? $ev_first['nama'] : 'Tambah Keterangan' ?></div>
                            </div>
                        <?php endfor; ?>
                    </div>

                <?php endforeach; ?>

            </div>
        </div>
    </div>
</div>

<script>
    // Fungsi JavaScript untuk upload manual, mengubah tampilan, dan otomatis mengisi tanggal hari ini
    function previewManualImage(event, targetId, textId, dateId) {
        const reader = new FileReader();
        reader.onload = function(){
            const output = document.getElementById(targetId);
            const textLabel = document.getElementById(textId);
            if(output) {
                output.src = reader.result;
                output.style.display = 'block';
            }
            if(textLabel) {
                textLabel.style.display = 'none';
            }
        };
        if(event.target.files[0]) {
            reader.readAsDataURL(event.target.files[0]);
            
            // Otomatis isi kolom tanggal jika masih kosong atau berisi strip (-)
            const dateLabel = document.getElementById(dateId);
            if(dateLabel && (dateLabel.innerText.trim() === '-' || dateLabel.innerText.trim() === '')) {
                const today = new Date();
                const dd = String(today.getDate()).padStart(2, '0');
                const mm = String(today.getMonth() + 1).padStart(2, '0');
                const yyyy = today.getFullYear();
                dateLabel.innerText = dd + '-' + mm + '-' + yyyy;
            }
        }
    }

    <?php foreach ($chart_per_kategori as $kat => $val): ?>
        <?php if (!empty($val['data'])): ?>
            const ctx_<?= md5($kat) ?> = document.getElementById('chart_<?= md5($kat) ?>');
            if (ctx_<?= md5($kat) ?>) {
                new Chart(ctx_<?= md5($kat) ?>.getContext('2d'), {
                    type: 'pie',
                    data: {
                        labels: <?= json_encode($val['labels']) ?>,
                        datasets: [{
                            data: <?= json_encode($val['data']) ?>,
                            backgroundColor: ['#ff6384', '#36a2eb', '#cc65fe', '#ffce56', '#4bc0c0', '#ff9f40'],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 12, font: { size: 10 } }
                            },
                            datalabels: {
                                color: '#fff',
                                font: { weight: 'bold', size: 11 },
                                formatter: (value, ctx) => {
                                    let sum = ctx.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                    let percentage = (value * 100 / sum).toFixed(1) + "%";
                                    return value > 0 ? percentage : '';
                                }
                            }
                        }
                    },
                    plugins: [ChartDataLabels]
                });
            }
        <?php endif; ?>
    <?php endforeach; ?>

    <?php if (!empty($chart_data_final)): ?>
    const chartLabels = <?= json_encode($chart_labels_final); ?>;
    const chartData = <?= json_encode($chart_data_final); ?>;

    const backgroundColors = [
        '#ff914d', '#3498db', '#2ecc71', '#9b59b6', '#f1c40f', '#e74c3c'
    ];

    const configDoc = {
        type: 'pie',
        data: {
            labels: chartLabels,
            datasets: [{
                data: chartData,
                backgroundColor: backgroundColors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 12, font: { size: 11 } }
                },
                datalabels: {
                    color: '#fff',
                    font: { weight: 'bold', size: 11 },
                    formatter: (value, ctx) => {
                        let sum = ctx.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                        let percentage = (value * 100 / sum).toFixed(1) + "%";
                        return value > 0 ? percentage : '';
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    };

    const ctxDoc = document.getElementById('grafikPieDoc');
    if (ctxDoc) {
        new Chart(ctxDoc, configDoc);
    }
    <?php endif; ?>
</script>

</body>
</html>