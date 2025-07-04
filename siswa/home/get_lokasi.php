<?php
include_once('../../config.php');
header('Content-Type: application/json');

if (!isset($_POST['lokasi'])) {
    echo json_encode(['error' => 'Lokasi tidak dikirim']);
    exit;
}

$nama = $_POST['lokasi'];

$stmt = mysqli_prepare($conection, "SELECT * FROM lokasi_presensi WHERE nama_lokasi = ?");
if (!$stmt) {
    echo json_encode(['error' => 'Gagal mempersiapkan query']);
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $nama);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'Lokasi tidak ditemukan']);
}
?>
