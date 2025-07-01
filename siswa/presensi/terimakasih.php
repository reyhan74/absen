<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("location: ../../auth/login.php?pesan=belum_login");
    exit;
}

include_once('../../config.php');
include('../layout/header.php');

// Ambil ID siswa dari session
$id_pegawai = $_SESSION['id'] ?? null;
$tanggal_hari_ini = date('Y-m-d');

if (!$id_pegawai) {
    echo "<script>alert('ID pengguna tidak ditemukan.'); window.location='../home/home.php';</script>";
    exit;
}

// Ambil data presensi hari ini
$query = "SELECT * FROM presensi 
          WHERE id_siswa = '$id_pegawai' AND tanggal_masuk = '$tanggal_hari_ini' 
          ORDER BY tanggal_masuk DESC, jam_masuk DESC 
          LIMIT 1";
$result = mysqli_query($conection, $query);

// Cek apakah data ada
if (mysqli_num_rows($result) == 0) {
    echo "<script>alert('Data presensi tidak ditemukan. Silakan lakukan presensi masuk terlebih dahulu.'); window.location='../home/home.php';</script>";
    exit;
}

// Ambil data presensi
$data = mysqli_fetch_assoc($result);

$nama = htmlspecialchars($_SESSION['nama']);
$foto = htmlspecialchars($data['foto_masuk']);
$tanggal = htmlspecialchars($data['tanggal_masuk']);
$jam = htmlspecialchars($data['jam_masuk']);
$nama_lokasi = strtolower(trim($data['nama_lokasi']));

// Ambil batas jam masuk dari tabel lokasi_presensi
$sql_lokasi = "SELECT jam_masuk FROM lokasi_presensi 
               WHERE LOWER(TRIM(nama_lokasi)) = '$nama_lokasi' 
               LIMIT 1";
$query_lokasi = mysqli_query($conection, $sql_lokasi);

if (mysqli_num_rows($query_lokasi) > 0) {
    $data_lokasi = mysqli_fetch_assoc($query_lokasi);
    $jam_batas_db = $data_lokasi['jam_masuk'];
    $batas_jam = strtotime($jam_batas_db);
} else {
    // fallback jika data lokasi tidak ditemukan
    $batas_jam = strtotime("07:00:00");
}

$jam_presensi = strtotime($jam);
$terlambat = $jam_presensi > $batas_jam;
?>

<div class="page-body">
    <div class="container-xl">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card text-center">
                    <div class="card-header bg-primary text-white">
                        <h3>Terima Kasih, <?= $nama ?>!</h3>
                    </div>
                    <div class="card-body">
                        <h2>Presensi masuk Anda telah berhasil dicatat pada sistem.</h2>
                        <p class="mt-2">Kami menghargai kedisiplinan Anda dalam melakukan presensi.</p>
                        <p><strong>Tanggal Masuk:</strong> <?= $tanggal ?></p>
                        <p><strong>Jam Masuk:</strong> <?= $jam ?></p>
                        <p><strong>Status:</strong>
                            <?php if ($terlambat): ?>
                                <span class="badge text-white bg-danger">Terlambat</span>
                            <?php else: ?>
                                <span class="badge bg-success">Tepat Waktu</span>
                            <?php endif; ?>
                        </p>
                        <img src="foto/<?= $foto ?>" alt="Foto Presensi" style="max-width: 300px; border-radius: 10px; margin-top: 10px;">
                        <br><br>
                        <a href="../home/home.php" class="btn btn-outline-primary mt-3">Kembali ke Beranda</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../layout/foother.php'); ?>
