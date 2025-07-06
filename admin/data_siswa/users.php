<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("location: ../../auth/login.php?pesan=belum_login");
    exit(); // Always call exit after header redirects
} else if ($_SESSION["role"] != 'admin') {
    header("location:../../auth/login.php?pesan=tolak_akses");
    exit(); // Always call exit after header redirects
}
include('../layout/header.php');
require_once('../../config.php'); // Ensure $conection is available

// Initialize search variables
$search_nama = '';
$search_kelas = '';
$where_clauses = [];
$params = [];
$param_types = '';

// Check if search form was submitted
if ($_SERVER["REQUEST_METHOD"] == "GET" && (isset($_GET['search_nama']) || isset($_GET['search_kelas']))) {
    // Sanitize and get search terms
    if (!empty($_GET['search_nama'])) {
        $search_nama = htmlspecialchars($_GET['search_nama']);
        $where_clauses[] = "nama LIKE ?";
        $params[] = '%' . $search_nama . '%';
        $param_types .= 's';
    }
    if (!empty($_GET['search_kelas'])) {
        $search_kelas = htmlspecialchars($_GET['search_kelas']);
        $where_clauses[] = "kelas LIKE ?";
        $params[] = '%' . $search_kelas . '%';
        $param_types .= 's';
    }
}

// Build the SQL query
$sql = "SELECT * FROM siswa";
if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}
$sql .= " ORDER BY nis ASC"; // Added an ORDER BY for consistent results

// Prepare and execute the statement
if (!empty($params)) {
    $stmt = $conection->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conection->error);
    }
    // Dynamically bind parameters using call_user_func_array for mysqli_stmt::bind_param
    // This is necessary because bind_param expects parameters by reference
    $bind_names = array($param_types);
    for ($i = 0; $i < count($params); $i++) {
        $bind_name = 'bind' . $i;
        $$bind_name = $params[$i]; // Create a variable for each parameter
        $bind_names[] = &$$bind_name; // Add reference to the array
    }
    call_user_func_array(array($stmt, 'bind_param'), $bind_names);

    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    // If no search terms, just fetch all
    $result = mysqli_query($conection, $sql);
    if ($result === false) {
        die("Error executing query: " . mysqli_error($conection));
    }
}
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Data Siswa</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="./tambah.php" class="btn btn-primary d-sm-none btn-icon" aria-label="Tambah Siswa">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M12 5l0 14" />
                            <path d="M5 12l14 0" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <?php if (isset($_SESSION['berhasil'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['berhasil']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['berhasil']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['validasi'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['validasi']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['validasi']); ?>
        <?php endif; ?>

        <a href="./tambah.php" class="btn btn-primary mb-3">Tambah Siswa</a>

        <div class="card">
            <div class="card-body">
                <form action="" method="GET" class="mb-4">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label for="search_nama" class="form-label">Cari Nama Siswa:</label>
                            <input type="text" class="form-control" id="search_nama" name="search_nama" value="<?= htmlspecialchars($search_nama); ?>" placeholder="Masukkan nama siswa">
                        </div>
                        <div class="col-md-4">
                            <label for="search_kelas" class="form-label">Cari Kelas:</label>
                            <input type="text" class="form-control" id="search_kelas" name="search_kelas" value="<?= htmlspecialchars($search_kelas); ?>" placeholder="Masukkan nama kelas">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-outline-primary me-2">Cari</button>
                            <a href="users.php" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="text-center">
                            <tr>
                                <th>NIS</th>
                                <th>Absen</th>
                                <th>Username</th>
                                <th>Nama</th>
                                <th>Kelas</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <?php while ($siswa = mysqli_fetch_array($result)) : ?>
                                    <tr>
                                        <td><?= htmlspecialchars($siswa['nis']) ?></td>
                                        <td><?= htmlspecialchars($siswa['no_absen']) ?></td>
                                        <td><?= htmlspecialchars($siswa['username']) ?></td>
                                        <td><?= htmlspecialchars($siswa['nama']) ?></td>
                                        <td><?= htmlspecialchars($siswa['kelas']) ?></td>
                                        <td><?= htmlspecialchars($siswa['status']) ?></td>
                                        <td>
                                            <a href="detail.php?nis=<?= htmlspecialchars($siswa['nis']) ?>" class="badge bg-info text-white text-decoration-none">Detail</a>
                                            <a href="edit.php?nis=<?= htmlspecialchars($siswa['nis']) ?>" class="badge bg-primary text-white text-decoration-none">Edit</a>
                                            <a href="hapus.php?nis=<?= htmlspecialchars($siswa['nis']) ?>" class="badge bg-danger text-white text-decoration-none" onclick="return confirm('Yakin ingin menghapus data siswa ini?')">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada data siswa ditemukan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    @media (max-width: 768px) {
        .table {
            min-width: 600px;
        }
    }
</style>

<?php include('../layout/foother.php'); ?>