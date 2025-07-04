<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("location: ../../auth/login.php?pesan=belum_login");
    exit;
} elseif ($_SESSION["role"] != 'admin') {
    header("location:../../auth/login.php?pesan=tolak_akses");
    exit;
}

include('../layout/header.php');
require_once('../../config.php'); // Ensure this path is correct for your database connection

// --- Sorting Logic ---
$orderBy = "p.tanggal_masuk DESC"; // Default sort
if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'nis_asc': $orderBy = "s.nis ASC"; break;
        case 'nis_desc': $orderBy = "s.nis DESC"; break;
        case 'jam_asc': $orderBy = "p.jam_masuk ASC"; break;
        case 'jam_desc': $orderBy = "p.jam_masuk DESC"; break;
        case 'tanggal': $orderBy = "p.tanggal_masuk DESC"; break; // Added explicitly for clarity
    }
}

// --- Filtering Logic ---
$filter = "";
// Custom Date Range Filter (takes precedence over 'periode')
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

if (!empty($startDate) && !empty($endDate)) {
    $startDate = mysqli_real_escape_string($conection, $startDate);
    $endDate = mysqli_real_escape_string($conection, $endDate);
    $filter .= "AND p.tanggal_masuk BETWEEN '$startDate' AND '$endDate'";
} else {
    // Period Filter (only applies if no custom date range is set)
    if (isset($_GET['periode']) && $_GET['periode'] != 'all') {
        switch ($_GET['periode']) {
            case 'hari':
                $filter .= "AND DATE(p.tanggal_masuk) = CURDATE()";
                break;
            case 'minggu':
                $filter .= "AND YEARWEEK(p.tanggal_masuk, 1) = YEARWEEK(CURDATE(), 1)";
                break;
            case 'bulan':
                $filter .= "AND MONTH(p.tanggal_masuk) = MONTH(CURDATE()) AND YEAR(p.tanggal_masuk) = YEAR(CURDATE())";
                break;
        }
    }
}


// Class Filter
$classFilter = "";
if (isset($_GET['kelas']) && $_GET['kelas'] != 'all') {
    $selectedClass = mysqli_real_escape_string($conection, $_GET['kelas']);
    $classFilter = "AND s.kelas = '$selectedClass'";
}

// Search Filter (by NIS or Nama)
$searchFilter = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = mysqli_real_escape_string($conection, $_GET['search']);
    $searchFilter = "AND (s.nis LIKE '%$searchTerm%' OR s.nama LIKE '%$searchTerm%')";
}

// --- Fetching Classes for Dropdown ---
$kelasQuery = "SELECT DISTINCT kelas FROM siswa WHERE kelas IS NOT NULL AND kelas != '' ORDER BY kelas ASC";
$kelasResult = mysqli_query($conection, $kelasQuery);
$classes = [];
if ($kelasResult) {
    while ($row = mysqli_fetch_assoc($kelasResult)) {
        $classes[] = $row['kelas'];
    }
}

// --- Main Query ---
$query = "
    SELECT
        s.nis, s.nama, s.kelas,
        p.tanggal_masuk, p.jam_masuk, p.foto_masuk, p.nama_lokasi,
        o.jam_keluar, o.foto_keluar
    FROM siswa s
    LEFT JOIN presensi p ON s.id = p.id_siswa
    LEFT JOIN presensi_out o ON s.id = o.id_siswa AND p.tanggal_masuk = o.tanggal_keluar
    WHERE p.tanggal_masuk IS NOT NULL
    $filter
    $classFilter
    $searchFilter
    ORDER BY $orderBy
";

$result = mysqli_query($conection, $query);
if (!$result) {
    die("Query Error: " . mysqli_error($conection));
}
?>

<div class="page-header d-print-none">
    <div class="container-xl d-flex justify-content-between align-items-center">
        <h2 class="page-title">Rekap Presensi Siswa</h2>
        <a href="export_excel.php?<?= http_build_query($_GET) ?>" class="btn btn-success">Export Data Saat Ini</a>
    </div>
</div>

---

<div class="container-xl mt-3">
    <form method="GET" class="d-flex gap-2 flex-wrap align-items-end">
        <div class="mb-3 me-2">
            <label for="search" class="form-label">Cari NIS atau Nama</label>
            <input type="text" name="search" id="search" class="form-control w-auto" placeholder="Cari NIS atau Nama" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </div>

        <div class="mb-3 me-2">
            <label for="kelas" class="form-label">Filter Kelas</label>
            <select name="kelas" id="kelas" class="form-select w-auto">
                <option value="all" <?= (!isset($_GET['kelas']) || $_GET['kelas'] == 'all') ? 'selected' : '' ?>>Semua Kelas</option>
                <?php foreach ($classes as $classOption) : ?>
                    <option value="<?= htmlspecialchars($classOption) ?>" <?= (isset($_GET['kelas']) && $_GET['kelas'] == $classOption) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($classOption) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3 me-2">
            <label for="periode" class="form-label">Filter Periode</label>
            <select name="periode" id="periode" class="form-select w-auto">
                <option value="all" <?= (!isset($_GET['periode']) || $_GET['periode'] == 'all') ? 'selected' : '' ?>>Semua</option>
                <option value="hari" <?= ($_GET['periode'] ?? '') == 'hari' ? 'selected' : '' ?>>Hari Ini</option>
                <option value="minggu" <?= ($_GET['periode'] ?? '') == 'minggu' ? 'selected' : '' ?>>Minggu Ini</option>
                <option value="bulan" <?= ($_GET['periode'] ?? '') == 'bulan' ? 'selected' : '' ?>>Bulan Ini</option>
            </select>
        </div>

        <div class="mb-3 me-2">
            <label for="start_date" class="form-label">Dari Tanggal</label>
            <input type="date" name="start_date" id="start_date" class="form-control w-auto" value="<?= htmlspecialchars($startDate) ?>">
        </div>
        <div class="mb-3 me-2">
            <label for="end_date" class="form-label">Sampai Tanggal</label>
            <input type="date" name="end_date" id="end_date" class="form-control w-auto" value="<?= htmlspecialchars($endDate) ?>">
        </div>

        <div class="mb-3 me-2">
            <label for="sort" class="form-label">Urutkan Berdasarkan</label>
            <select name="sort" id="sort" class="form-select w-auto">
                <option value="tanggal" <?= (!isset($_GET['sort']) || $_GET['sort'] == 'tanggal') ? 'selected' : '' ?>>Tanggal (Terbaru)</option>
                <option value="nis_asc" <?= ($_GET['sort'] ?? '') == 'nis_asc' ? 'selected' : '' ?>>NIS (Terendah)</option>
                <option value="nis_desc" <?= ($_GET['sort'] ?? '') == 'nis_desc' ? 'selected' : '' ?>>NIS (Tertinggi)</option>
                <option value="jam_asc" <?= ($_GET['sort'] ?? '') == 'jam_asc' ? 'selected' : '' ?>>Jam Masuk (Awal)</option>
                <option value="jam_desc" <?= ($_GET['sort'] ?? '') == 'jam_desc' ? 'selected' : '' ?>>Jam Masuk (Terlambat)</option>
            </select>
        </div>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Terapkan Filter</button>
            <a href="rekap.php" class="btn btn-secondary">Reset Filter</a>
        </div>
    </form>
</div>

---

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
                                <th>Lokasi</th>
                                <th>Foto Masuk</th>
                                <th>Jam Keluar</th>
                                <th>Foto Keluar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            if (mysqli_num_rows($result) > 0) :
                                while ($row = mysqli_fetch_assoc($result)) : ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['nis']) ?></td>
                                        <td><?= htmlspecialchars($row['nama']) ?></td>
                                        <td><?= htmlspecialchars($row['kelas'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($row['tanggal_masuk']) ?></td>
                                        <td><?= htmlspecialchars($row['jam_masuk']) ?></td>
                                        <td><?= htmlspecialchars($row['nama_lokasi'] ?? '-') ?></td>
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
                                <?php endwhile;
                            else : ?>
                                <tr>
                                    <td colspan="10">Tidak ada data presensi ditemukan.</td>
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