<?php

// Lakukan koneksi database kamu

include 'koneksi.php';



// --- LOGIKA TANGGAL SHIFT SAR (Otomatis ganti hari setelah jam 09:00 pagi) ---

date_default_timezone_set('Asia/Jakarta');

$jam_sekarang = (int) date('H');



if ($jam_sekarang < 9) {

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



$tanggal_hari_ini = "$hari_ini, $tanggal_ini $bulan_ini $tahun_ini";

$tanggal_format_db = date('Y-m-d', $timestamp_aktif); // Format standar database (YYYY-MM-DD)



// --- LOGIKA TOMBOL SIMPAN REKAP ---

if (isset($_GET['aksi']) && $_GET['aksi'] == 'simpan') {

    $query_draft = mysqli_query($koneksi, "SELECT * FROM draft_laporan");



    if ($query_draft && mysqli_num_rows($query_draft) > 0) {

        while ($row = mysqli_fetch_assoc($query_draft)) {

            // Gunakan tanggal aktif shift SAR yang akurat agar masuk database dengan benar

            $tanggal          = mysqli_real_escape_string($koneksi, $tanggal_format_db);

            $waktu            = mysqli_real_escape_string($koneksi, $row['waktu'] ?? date('H:i:s'));

            $nama_kegiatan    = mysqli_real_escape_string($koneksi, $row['nama_kegiatan'] ?? '');

            $jenis_kegiatan   = mysqli_real_escape_string($koneksi, $row['jenis_kegiatan'] ?? '');

            $opsi             = mysqli_real_escape_string($koneksi, $row['opsi'] ?? '');



            // Insert ke tabel laporan_final dengan tanggal yang sudah disesuaikan

            mysqli_query($koneksi, "INSERT INTO laporan_final (tanggal, waktu, nama_kegiatan, jenis_kegiatan, opsi)

            VALUES ('$tanggal', '$waktu', '$nama_kegiatan', '$jenis_kegiatan', '$opsi')");

        }



        // Kosongkan draft_laporan setelah sukses dipindah

        mysqli_query($koneksi, "DELETE FROM draft_laporan");



        echo "<script>alert('Berhasil! Data draft sudah masuk ke Laporan Final dan draft dikosongkan.'); window.location='cetak_laporan.php';</script>";

        exit;

    } else {

        echo "<script>alert('Tidak ada data di draft untuk disimpan.'); window.location='cetak_laporan.php';</script>";

        exit;

    }

}



// Ambil data dari database tabel draft_laporan secara dinamis

$query = mysqli_query($koneksi, "SELECT * FROM draft_laporan ORDER BY id ASC");

$data_kegiatan = [];



while($row = mysqli_fetch_assoc($query)) {

    $nama_keg    = $row['nama_kegiatan'] ?? '';

    $jenis_keg   = $row['jenis_kegiatan'] ?? '';

    $opsi        = $row['opsi'] ?? '';

    $deskripsi   = $row['deskripsi'] ?? $row['press_release'] ?? '';

    $foto        = $row['dokumentasi'] ?? $row['dok'] ?? '';



    $data_kegiatan[] = [

        'id'            => $row['id'],

        'nama_kegiatan' => $nama_keg,

        'jenis_kegiatan'=> $jenis_keg,

        'opsi_kegiatan' => $opsi,

        'deskripsi'     => $deskripsi,

        'foto_bukti'    => $foto,

        'bbm_1'         => $row['bbm_1'] ?? '59%',

        'km_1'          => $row['km_1'] ?? '',

        'bersih_1'      => $row['bersih_1'] ?? 'Ya',

        'ket_1'         => $row['ket_1'] ?? '',

        'bbm_2'         => $row['bbm_2'] ?? '50%',

        'km_2'          => $row['km_2'] ?? '',

        'bersih_2'      => $row['bersih_2'] ?? 'Ya',

        'ket_2'         => $row['ket_2'] ?? ''

    ];

}



function cari_foto_spesifik($data, $keyword) {

    foreach($data as $row) {

        if(stripos($row['nama_kegiatan'], $keyword) !== false || stripos($row['opsi_kegiatan'], $keyword) !== false) {

            $fotos = explode(',', $row['foto_bukti']);

            foreach($fotos as $f) {

                $f_trim = trim($f);

                if($f_trim != '') return $f_trim;

            }

        }

    }

    return '';

}



$foto_palsar = cari_foto_spesifik($data_kegiatan, 'Pengecekan Palsar (Foto Tabel)');

if ($foto_palsar == '') {

    $foto_palsar = cari_foto_spesifik($data_kegiatan, 'Pengecekan Palsar');

}



$foto_absensi = cari_foto_spesifik($data_kegiatan, 'Jurnal');

if ($foto_absensi == '') {

    $foto_absensi = cari_foto_spesifik($data_kegiatan, 'Absensi');

}

?>

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Cetak Laporan - Unit Siaga SAR Bogor</title>

    <style>

        body { font-family: Arial, sans-serif; font-size: 11px; color: #000; line-height: 1.4; background-color: #525659; margin: 0; padding: 20px 0; }

        .page { background: white; width: 210mm; min-height: 297mm; padding: 20mm 15mm; margin: 0 auto 20mm auto; box-shadow: 0 0 10px rgba(0,0,0,0.3); box-sizing: border-box; position: relative; page-break-after: always; }

        h2, h3, h4 { text-align: center; margin: 5px 0; }

        table { width: 100%; border-collapse: collapse; margin-top: 8px; margin-bottom: 12px; }

        table, th, td { border: 1px solid black; }

        th, td { padding: 5px 8px; text-align: left; vertical-align: middle; }

        th { text-align: center; background-color: #f2f2f2; }

        .text-center { text-align: center; }

        .no-border, .no-border td { border: none; }

        .section-title { background-color: #e2e2e2; font-weight: bold; padding: 5px; margin-top: 10px; margin-bottom: 5px; font-size: 11px; }

        .page-foto-besar { display: flex; flex-direction: column; align-items: center; justify-content: center; height: calc(297mm - 40mm); text-align: center; }

        .page-foto-besar img { max-width: 100%; max-height: 230mm; width: auto; height: auto; object-fit: contain; border: 1px solid #ccc; box-shadow: 0 0 5px rgba(0,0,0,0.1); }

        #save-status { position: fixed; top: 10px; right: 10px; background: rgba(0, 0, 0, 0.8); color: white; padding: 5px 10px; border-radius: 4px; font-size: 10px; z-index: 9999; display: none; }

        [contenteditable="true"] { cursor: text; }

        [contenteditable="true"]:hover { outline: 1px dashed #bbb; }

        [contenteditable="true"]:focus { outline: 1px solid #007bff; background-color: #f8f9fa; }

        @media print {

            body { background: none; padding: 0; }

            .page { box-shadow: none; margin: 0; width: 100%; min-height: 100%; padding: 20mm 15mm; }

            .no-print { display: none; }

            [contenteditable="true"] { outline: none !important; background-color: transparent !important; }

        }

    </style>

</head>

<body>



    <div id="save-status">Menyimpan perubahan...</div>



    <div class="no-print" style="text-align: center; margin-bottom: 20px;">

        <a href="draft_laporan.php" style="text-decoration: none; display: inline-block; padding: 10px 15px; background: #6c757d; color: white; border-radius: 5px; font-weight: bold; margin-right: 10px;">

            ⬅️ Kembali ke Dashboard

        </a>

       

        <a href="cetak_laporan.php?aksi=simpan" onclick="return confirm('Yakin ingin menyimpan rekap? Data di draft akan dipindahkan ke Laporan Final dengan tanggal yang sesuai dan draft akan dikosongkan.')" style="text-decoration: none; display: inline-block; padding: 10px 15px; background: #28a745; color: white; border-radius: 5px; font-weight: bold; margin-right: 10px;">

            💾 Simpan Rekap

        </a>



        <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; margin-right: 10px;">

            🖨️ Cetak / Konversi PDF

        </button>



        <a href="https://webmail.basarnas.go.id/" target="_blank" style="text-decoration: none; display: inline-block; padding: 10px 15px; background: #17a2b8; color: white; border-radius: 5px; font-weight: bold;">

            🌐 Webmail Basarnas

        </a>

    </div>



    <!-- 1. LAPORAN KEGIATAN HARIAN -->

    <div class="page">

        <h2>LAPORAN KEGIATAN HARIAN</h2>

        <h2>UNIT SIAGA SAR BOGOR</h2>

        <br>

        <p><strong>Hari / tanggal :</strong> <span class="editable-tanggal" contenteditable="true" data-key="tanggal_laporan_1"><?php echo $tanggal_hari_ini; ?></span></p>

        <p><strong>Butir Kegiatan :</strong></p>

        <ul>

            <?php

            $ada_kegiatan_valid = false;

            if (!empty($data_kegiatan)) {

                foreach($data_kegiatan as $row) {

                    $tampilkan_teks = !empty($row['nama_kegiatan']) ? $row['nama_kegiatan'] : $row['opsi_kegiatan'];

                    if (!empty(trim($tampilkan_teks))) {

                        $ada_kegiatan_valid = true;

            ?>

                <li><?php echo htmlspecialchars($tampilkan_teks); ?></li>

            <?php

                    }

                }

            }

            if (!$ada_kegiatan_valid) {

                echo '<li>Belum ada kegiatan</li>';

            }

            ?>

        </ul>

        <p><strong>Eviden Kegiatan :</strong> Terlampir</p>

    </div>



    <!-- 2. LAPORAN PEMERIKSAAN KENDARAAN OPERASIONAL -->

    <div class="page">

        <h2>LAPORAN PEMERIKSAAN KENDARAAN OPERASIONAL</h2>

        <br>

        <p><strong>Hari/Tanggal</strong> : <span class="editable-tanggal" contenteditable="true" data-key="tanggal_laporan_2"><?php echo $tanggal_hari_ini; ?></span></p>

        <p><strong>Tim</strong> : USS BOGOR</p>

        <p><strong>Pemeriksa</strong> : -</p>

        <p><strong>Unit/Lokasi</strong> : USS Bogor</p>

        <p><strong>Kegiatan</strong> : Pemanasan dan memeriksa kendaraan operasional</p>



        <div class="section-title">PERSENTASE BAHAN BAKAR KENDARAAN</div>

        <table>

            <thead>

                <tr>

                    <th>No</th>

                    <th>Kendaraan</th>

                    <th>Plat</th>

                    <th>BBM</th>

                    <th>KM</th>

                    <th>Bersih</th>

                    <th>Keterangan</th>

                </tr>

            </thead>

            <tbody>

                <?php

                $ada_data_kendaraan = false;

                foreach($data_kegiatan as $row) {

                    if (stripos($row['opsi_kegiatan'], 'kendaraan') !== false || stripos($row['nama_kegiatan'], 'kendaraan') !== false) {

                ?>

                <tr>

                    <td class="text-center">1</td>

                    <td>Isuzu D-Max</td>

                    <td>B 9232 PSD</td>

                    <td class="text-center" contenteditable="true" data-id="<?php echo $row['id']; ?>" data-field="bbm_1"><?php echo htmlspecialchars($row['bbm_1']); ?></td>

                    <td class="text-center" contenteditable="true" data-id="<?php echo $row['id']; ?>" data-field="km_1"><?php echo htmlspecialchars($row['km_1']); ?></td>

                    <td class="text-center" contenteditable="true" data-id="<?php echo $row['id']; ?>" data-field="bersih_1"><?php echo htmlspecialchars($row['bersih_1']); ?></td>

                    <td contenteditable="true" data-id="<?php echo $row['id']; ?>" data-field="ket_1"><?php echo htmlspecialchars($row['ket_1']); ?></td>

                </tr>

                <tr>

                    <td class="text-center">2</td>

                    <td>Kawasaki Trail</td>

                    <td>B 3835 PFQ</td>

                    <td class="text-center" contenteditable="true" data-id="<?php echo $row['id']; ?>" data-field="bbm_2"><?php echo htmlspecialchars($row['bbm_2']); ?></td>

                    <td class="text-center" contenteditable="true" data-id="<?php echo $row['id']; ?>" data-field="km_2"><?php echo htmlspecialchars($row['km_2']); ?></td>

                    <td class="text-center" contenteditable="true" data-id="<?php echo $row['id']; ?>" data-field="bersih_2"><?php echo htmlspecialchars($row['bersih_2']); ?></td>

                    <td contenteditable="true" data-id="<?php echo $row['id']; ?>" data-field="ket_2"><?php echo htmlspecialchars($row['ket_2']); ?></td>

                </tr>

                <?php

                        $ada_data_kendaraan = true;

                        break;

                    }

                }

                if (!$ada_data_kendaraan) {

                ?>

                <tr>

                    <td class="text-center">1</td>

                    <td>Isuzu D-Max</td>

                    <td>B 9232 PSD</td>

                    <td class="text-center">70%</td>

                    <td class="text-center"></td>

                    <td class="text-center">Ya</td>

                    <td>-</td>

                </tr>

                <tr>

                    <td class="text-center">2</td>

                    <td>Kawasaki Trail</td>

                    <td>B 3835 PFQ</td>

                    <td class="text-center">80%</td>

                    <td class="text-center"></td>

                    <td class="text-center">Ya</td>

                    <td>-</td>

                </tr>

                <?php } ?>

            </tbody>

        </table>



        <div class="section-title">KENDARAAN SEDANG DIPERBAIKI</div>

        <p style="margin: 5px 0;">-</p>



        <div class="section-title">DESKRIPSI LAINNYA</div>

        <div style="font-size: 11px; margin-top: 5px; line-height: 1.4;">

            <p style="margin: 2px 0; font-weight: bold;">1. Isuzu D-Max B 9232 PSD</p>

            <p style="margin: 2px 0 2px 15px;">> Mika lampu belakang kiri pecah</p>

            <p style="margin: 2px 0 5px 15px;">> Power window pintu pengemudi tidak berfungsi</p>

           

            <p style="margin: 2px 0; font-weight: bold;">2. Kawasaki Trail B 3835 PFQ</p>

            <p style="margin: 2px 0 15px;">

                > Kabel Kilometer speedometer harus ganti<br>

                > Gear Set harus di ganti<br>

                > Kampas rem depan habis<br>

                > Shockbreaker depan bocor<br>

                > Lampu Rem Mati

            </p>

        </div>

    </div>



    <!-- 3. PENGECEKAN PALSAR -->

    <?php if($foto_palsar != '') { ?>

    <div class="page">

        <h2>PENGECEKAN PALSAR</h2>

        <div class="page-foto-besar">

            <img src="uploads/<?php echo htmlspecialchars($foto_palsar); ?>" alt="Pengecekan Palsar">

        </div>

    </div>

    <?php } ?>



    <!-- 4. DAFTAR KEHADIRAN & JURNAL SIAGA -->

    <?php if($foto_absensi != '') { ?>

    <div class="page">

        <h2>DAFTAR KEHADIRAN & JURNAL SIAGA SAR</h2>

        <div class="page-foto-besar">

            <img src="uploads/<?php echo htmlspecialchars($foto_absensi); ?>" alt="Daftar Kehadiran dan Jurnal">

        </div>

    </div>

    <?php } ?>



    <!-- 5. HALAMAN TERAKHIR: LAMPIRAN EVIDEN -->

    <div class="page">

        <h2>LAMPIRAN EVIDEN / DOKUMENTASI KEGIATAN</h2>

        <h2>UNIT SIAGA SAR BOGOR</h2>

        <p><strong>Hari / tanggal :</strong> <span class="editable-tanggal" contenteditable="true" data-key="tanggal_laporan_5"><?php echo $tanggal_hari_ini; ?></span></p>

        <br>



        <?php

        $ada_eviden_lain = false;

        if (!empty($data_kegiatan)) {

            foreach($data_kegiatan as $row) {

                $nama_keg = $row['nama_kegiatan'];

                $opsi = $row['opsi_kegiatan'];

               

                if(empty(trim($nama_keg)) && empty(trim($opsi))) continue;

                if(stripos($nama_keg, 'Palsar (Foto Tabel)') !== false || stripos($opsi, 'Palsar (Foto Tabel)') !== false) continue;

                if(stripos($nama_keg, 'Jurnal') !== false || stripos($opsi, 'Jurnal') !== false) continue;

                if(stripos($nama_keg, 'Absensi') !== false || stripos($opsi, 'Absensi') !== false) continue;



                $ada_eviden_lain = true;

                $judul_tampil = !empty($nama_keg) ? $nama_keg : $opsi;

        ?>

            <h4 style="text-align: left; border-bottom: 1px solid #000; padding-bottom: 3px; margin-top: 15px;">

                <?php echo htmlspecialchars($judul_tampil); ?>

            </h4>

            <p style="font-size: 10px; color: #555; margin: 3px 0;" contenteditable="true" data-id="<?php echo $row['id']; ?>" data-field="deskripsi">Deskripsi: <?php echo htmlspecialchars($row['deskripsi']); ?></p>

           

            <table class="no-border" style="width: 100%; margin-top: 5px;">

                <tr>

                    <?php

                    $fotos = explode(',', $row['foto_bukti']);

                    $counter = 0;

                    foreach($fotos as $foto) {

                        $foto_trim = trim($foto);

                        if($foto_trim != "") {

                            if($counter > 0 && $counter % 3 == 0) {

                                echo '</tr><tr>';

                            }

                    ?>

                            <td style="text-align: left; width: 333px; padding: 4px;">

                                <img src="uploads/<?php echo htmlspecialchars($foto_trim); ?>" alt="Bukti Kegiatan" style="width: 180px; height: 120px; object-fit: cover; border: 1px solid #ccc;">

                            </td>

                    <?php

                            $counter++;

                        }

                    }

                    while($counter % 3 != 0) {

                        echo '<td style="width: 333px;"></td>';

                        $counter++;

                    }

                    ?>

                </tr>

            </table>

        <?php

            }

        }

        if (!$ada_eviden_lain) {

            echo '<p style="font-style: italic; color: #555;">Tidak ada lampiran eviden kegiatan lapangan lainnya.</p>';

        }

        ?>

    </div>



    <script>

        // Bersihkan cache tanggal lama di localStorage jika tidak sama dengan tanggal aktif hari ini

        ['tanggal_laporan_1', 'tanggal_laporan_2', 'tanggal_laporan_5'].forEach(key => {

            const savedVal = localStorage.getItem(key);

            const tanggalAktifServer = "<?php echo $tanggal_hari_ini; ?>";

            if (savedVal && savedVal !== tanggalAktifServer) {

                localStorage.removeItem(key);

            }

        });



        document.querySelectorAll('.editable-tanggal').forEach(el => {

            const key = el.getAttribute('data-key');

            const savedVal = localStorage.getItem(key);

            if (savedVal) {

                el.innerText = savedVal;

            }



            el.addEventListener('blur', function() {

                localStorage.setItem(key, this.innerText.trim());

               

                const statusBox = document.getElementById('save-status');

                statusBox.innerText = 'Tanggal Tersimpan!';

                statusBox.style.display = 'block';

                setTimeout(() => { statusBox.style.display = 'none'; }, 1500);

            });

        });



        document.querySelectorAll('[contenteditable="true"]:not(.editable-tanggal)').forEach(element => {

            element.addEventListener('blur', function() {

                const id = this.getAttribute('data-id');

                const field = this.getAttribute('data-field');

                const value = this.innerText.trim();



                if (!id || !field) return;



                const statusBox = document.getElementById('save-status');

                statusBox.innerText = 'Menyimpan...';

                statusBox.style.display = 'block';



                fetch('update_data.php', {

                    method: 'POST',

                    headers: {

                        'Content-Type': 'application/x-www-form-urlencoded',

                    },

                    body: `id=${encodeURIComponent(id)}&field=${encodeURIComponent(field)}&value=${encodeURIComponent(value)}`

                })

                .then(response => response.json())

                .then(data => {

                    if (data.success) {

                        statusBox.innerText = 'Tersimpan!';

                        setTimeout(() => { statusBox.style.display = 'none'; }, 1500);

                    } else {

                        statusBox.innerText = 'Gagal menyimpan!';

                        setTimeout(() => { statusBox.style.display = 'none'; }, 2000);

                    }

                })

                .catch(error => {

                    console.error('Error:', error);

                    statusBox.innerText = 'Kesalahan jaringan!';

                    setTimeout(() => { statusBox.style.display = 'none'; }, 2000);

                });

            });

        });

    </script>

</body>

</html>