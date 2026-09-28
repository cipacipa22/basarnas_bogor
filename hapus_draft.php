<?php
session_start();
include 'koneksi.php';

$id = $_GET['id'] ?? '';
if (!empty($id)) {
    // Hapus data dari database berdasarkan ID
    mysqli_query($koneksi, "DELETE FROM draft_laporan WHERE id = '$id'");
}

header("Location: draft_laporan.php");
exit;
?>