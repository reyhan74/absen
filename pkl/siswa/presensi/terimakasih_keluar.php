<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("location: ../../auth/login.php?pesan=belum_login");
    exit;
}

include_once('../../config.php');
include('../layout/header.php');

$id_pegawai = $_SESSION['id'] ?? null;
$tanggal_hari_ini = date('Y-m-d');

if (!$id_pegawai) {
    echo "<script>alert('ID pengguna tidak ditemukan.'); window.location='../home/home.php';</script>";
    exit;
}

// Ambil data presensi keluar terbaru hari ini
$query = "SELECT * FROM presensi_out 
          WHERE id_siswa = '$id_pegawai' AND tanggal_keluar = '$tanggal_hari_ini' 
          ORDER BY tanggal_keluar DESC, jam_keluar DESC 
          LIMIT 1";
$result = mysqli_query($conection, $query);

if (mysqli_num_rows($result) == 0) {
    echo "<script>alert('Data presensi keluar tidak ditemukan.'); window.location='../home/home.php';</script>";
    exit;
}

$data = mysqli_fetch_assoc($result);

$nama = htmlspecialchars($_SESSION['nama']);
$foto = htmlspecialchars($data['foto_keluar']);
$tanggal = htmlspecialchars($data['tanggal_keluar']);
$jam = htmlspecialchars($data['jam_keluar']);
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
                        <h2>Presensi keluar Anda telah berhasil dicatat pada sistem.</h2>
                        <p class="mt-2">Semoga aktivitas Anda hari ini menyenangkan dan bermanfaat.</p>
                        <p><strong>Tanggal Keluar:</strong> <?= $tanggal ?></p>
                        <p><strong>Jam Keluar:</strong> <?= $jam ?></p>
                        <img src="foto/<?= $foto ?>" alt="Foto Presensi Keluar" style="max-width: 300px; border-radius: 10px; margin-top: 10px;">
                        <br><br>
                        <a href="../home/home.php" class="btn btn-outline-primary mt-3">Kembali ke Beranda</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include('../layout/foother.php');
?>
