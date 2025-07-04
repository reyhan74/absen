<?php
ob_start();
session_start();

if (!isset($_SESSION['login'])) {
    header("location: ../../auth/login.php?pesan=belum_login");
    exit();
} elseif ($_SESSION["role"] != 'guru') {
    header("location: ../../auth/login.php?pesan=tolak_akses");
    exit();
}

require_once('../../config.php');

// Get the NIS from the URL
$nis = $_GET['nis'] ?? null;

if (!$nis) {
    die("NIS tidak valid!");
}

// Fetch the existing student data
$stmt = $conection->prepare("SELECT * FROM siswa WHERE nis = ?");
$stmt->bind_param("s", $nis);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    die("Data siswa tidak ditemukan!");
}

include('../layout/header.php');
?>

<!-- Page header -->
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Detail Siswa</h2>
            </div>
        </div>
    </div>
</div>

<!-- Page body -->
<div class="page-body">
    <div class="container-xl">
        <div class="card col-md-8">
            <div class="card-body">
                <h5>Informasi Siswa</h5>
                <table class="table">
                    <tr>
                        <th>NIS</th>
                        <td><?= htmlspecialchars($student['nis']); ?></td>
                    </tr>
                    <tr>
                        <th>Nomor absen</th>
                        <td><?= htmlspecialchars($student['no_absen']); ?></td>
                    </tr>
                    <tr>
                        <th>Nama</th>
                        <td><?= htmlspecialchars($student['nama']); ?></td>
                    </tr>
                    <tr>
                        <th>Jenis Kelamin</th>
                        <td><?= htmlspecialchars($student['jenis_kelamin']); ?></td>
                    </tr>
                    <tr>
                        <th>Alamat</th>
                        <td><?= htmlspecialchars($student['alamat']); ?></td>
                    </tr>
                    <tr>
                        <th>No Handphone</th>
                        <td><?= htmlspecialchars($student['no_handphone']); ?></td>
                    </tr>
                    <tr>
                        <th>Lokasi Presensi</th>
                        <td><?= htmlspecialchars($student['lokasi_presensi']); ?></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td><?= htmlspecialchars($student['status']); ?></td>
                    </tr>
                    <tr>
                        <th>Foto</th>
                        <td>
                            <?php if (!empty($student['foto'])): ?>
                                <img src="<?= htmlspecialchars($student['foto']); ?>" width="100" alt="Foto Siswa">
                            <?php else: ?>
                                Tidak ada foto.
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                <a href="users.php" class="btn btn-secondary">Kembali</a>
            </div>
        </div>
    </div>
</div>