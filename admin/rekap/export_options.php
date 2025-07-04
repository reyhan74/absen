<?php
// admin/pages/presensi/get_students.php
require_once('../../config.php'); // Your database connection

header('Content-Type: application/json');

$searchTerm = $_GET['search'] ?? '';
$students = [];

if (!empty($searchTerm) && strlen($searchTerm) >= 2) {
    // Sanitize input
    $searchTerm = mysqli_real_escape_string($conection, $searchTerm);
    $searchWildcard = '%' . $searchTerm . '%';

    $query = "SELECT id, nis, nama, kelas FROM siswa WHERE nis LIKE ? OR nama LIKE ? LIMIT 10";
    $stmt = mysqli_prepare($conection, $query);
    mysqli_stmt_bind_param($stmt, "ss", $searchWildcard, $searchWildcard);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $students[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($conection);
echo json_encode($students);
?>