<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("location: ../../auth/login.php?pesan=belum_login");
    exit;
} elseif ($_SESSION["role"] != 'guru') {
    header("location:../../auth/login.php?pesan=tolak_akses");
    exit;
}

include('../layout/header.php');
require_once('../../config.php');

// Sorting
$orderBy = "p.tanggal_masuk DESC";
if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'nis_asc': $orderBy = "s.nis ASC"; break;
        case 'nis_desc': $orderBy = "s.nis DESC"; break;
        case 'jam_asc': $orderBy = "p.jam_masuk ASC"; break;
        case 'jam_desc': $orderBy = "p.jam_masuk DESC"; break;
    }
}

// Filtering
$filter = "";
if (isset($_GET['periode']) && $_GET['periode'] != 'all') {
    switch ($_GET['periode']) {
        case 'hari':
            $filter = "AND DATE(p.tanggal_masuk) = CURDATE()";
            break;
        case 'minggu':
            $filter = "AND YEARWEEK(p.tanggal_masuk, 1) = YEARWEEK(CURDATE(), 1)";
            break;
        case 'bulan':
            $filter = "AND MONTH(p.tanggal_masuk) = MONTH(CURDATE()) AND YEAR(p.tanggal_masuk) = YEAR(CURDATE())";
            break;
    }
}

// Query
$query = "
    SELECT 
        s.nis, s.nama, s.kelas,
        p.tanggal_masuk, p.jam_masuk, p.foto_masuk,
        o.jam_keluar, o.foto_keluar
    FROM siswa s
    LEFT JOIN presensi p ON s.id = p.id_siswa
    LEFT JOIN presensi_out o ON s.id = o.id_siswa AND p.tanggal_masuk = o.tanggal_keluar
    WHERE p.tanggal_masuk IS NOT NULL $filter
    ORDER BY $orderBy
";

$result = mysqli_query($conection, $query);
if (!$result) {
    die("Query Error: " . mysqli_error($conection));
}
?>

<!-- Header -->
<div class="page-header d-print-none">
    <div class="container-xl d-flex justify-content-between align-items-center">
        <h2 class="page-title">Rekap Presensi Siswa</h2>
        <a href="export_excel.php" class="btn btn-success">Ekspor ke Excel</a>
    </div>
</div>

<!-- Filter & Sort Form -->
<div class="container-xl mt-3">
    <form method="GET" class="d-flex gap-2 flex-wrap">
        <select name="periode" class="form-select w-auto" onchange="this.form.submit()">
            <option value="all" <?= (!isset($_GET['periode']) || $_GET['periode'] == 'all') ? 'selected' : '' ?>>Semua</option>
            <option value="hari" <?= ($_GET['periode'] ?? '') == 'hari' ? 'selected' : '' ?>>Hari Ini</option>
            <option value="minggu" <?= ($_GET['periode'] ?? '') == 'minggu' ? 'selected' : '' ?>>Minggu Ini</option>
            <option value="bulan" <?= ($_GET['periode'] ?? '') == 'bulan' ? 'selected' : '' ?>>Bulan Ini</option>
        </select>
        <select name="sort" class="form-select w-auto" onchange="this.form.submit()">
            <option value="tanggal" <?= (!isset($_GET['sort']) || $_GET['sort'] == 'tanggal') ? 'selected' : '' ?>>Sort Tanggal</option>
            <option value="nis_asc" <?= ($_GET['sort'] ?? '') == 'nis_asc' ? 'selected' : '' ?>>NIS Terendah</option>
            <option value="nis_desc" <?= ($_GET['sort'] ?? '') == 'nis_desc' ? 'selected' : '' ?>>NIS Tertinggi</option>
            <option value="jam_asc" <?= ($_GET['sort'] ?? '') == 'jam_asc' ? 'selected' : '' ?>>Jam Masuk Awal</option>
            <option value="jam_desc" <?= ($_GET['sort'] ?? '') == 'jam_desc' ? 'selected' : '' ?>>Jam Masuk Terlambat</option>
        </select>
    </form>
</div>

<!-- Table -->
<div class="page-body mt-3">
    <div class="container-xl">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle text-center">
                        <thead class="table-primary">
                            <tr>
                                <th>No</th>
                                <th>NIS</th>
                                <th>Nama</th>
                                <th>Kelas</th>
                                <th>Tanggal</th>
                                <th>Jam Masuk</th>
                                <th>Foto Masuk</th>
                                <th>Jam Keluar</th>
                                <th>Foto Keluar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($result)) : ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['nis']) ?></td>
                                <td><?= htmlspecialchars($row['nama']) ?></td>
                                <td><?= htmlspecialchars($row['kelas'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['tanggal_masuk']) ?></td>
                                <td><?= htmlspecialchars($row['jam_masuk']) ?></td>
                                <td>
                                    <?php if (!empty($row['foto_masuk'])) : ?>
                                        <img src="../../siswa/presensi/foto/<?= htmlspecialchars($row['foto_masuk']) ?>" alt="Foto Masuk" width="80">
                                    <?php else : ?>
                                        <span class="text-muted">Tidak Ada Foto</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['jam_keluar'] ?? '-') ?></td>
                                <td>
                                    <?php if (!empty($row['foto_keluar'])) : ?>
                                        <img src="../../siswa/presensi/foto/<?= htmlspecialchars($row['foto_keluar']) ?>" alt="Foto Keluar" width="80">
                                    <?php else : ?>
                                        <span class="text-muted">Tidak Ada Foto</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if (mysqli_num_rows($result) === 0): ?>
                                <tr>
                                    <td colspan="9">Tidak ada data presensi ditemukan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('../layout/foother.php'); ?>
