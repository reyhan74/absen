<?php
session_start();

// --- IMPORTANT: Disable all error reporting to prevent HTML output in CSV ---
error_reporting(0);
ini_set('display_errors', 0);

// Basic authentication check, similar to rekap.php
if (!isset($_SESSION['login']) || $_SESSION["role"] != 'admin') {
    header("location: ../../auth/login.php?pesan=tolak_akses");
    exit;
}

require_once('../../config.php'); // Ensure this path is correct for your database connection

// --- Filtering and Sorting Logic (Copied from rekap.php to ensure consistency) ---
$orderBy = "p.tanggal_masuk DESC"; // Default sort
if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'nis_asc': $orderBy = "s.nis ASC"; break;
        case 'nis_desc': $orderBy = "s.nis DESC"; break;
        case 'jam_asc': $orderBy = "p.jam_masuk ASC"; break;
        case 'jam_desc': $orderBy = "p.jam_masuk DESC"; break;
        case 'tanggal': $orderBy = "p.tanggal_masuk DESC"; break;
    }
}

$filter = "";
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

if (!empty($startDate) && !empty($endDate)) {
    $startDate = mysqli_real_escape_string($conection, $startDate);
    $endDate = mysqli_real_escape_string($conection, $endDate);
    $filter .= "AND p.tanggal_masuk BETWEEN '$startDate' AND '$endDate'";
} else {
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

$classFilter = "";
if (isset($_GET['kelas']) && $_GET['kelas'] != 'all') {
    $selectedClass = mysqli_real_escape_string($conection, $_GET['kelas']);
    $classFilter = "AND s.kelas = '$selectedClass'";
}

$searchFilter = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = mysqli_real_escape_string($conection, $_GET['search']);
    $searchFilter = "AND (s.nis LIKE '%$searchTerm%' OR s.nama LIKE '%$searchTerm%')";
}

// --- Main Query (Identical to rekap.php) ---
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
    // Log the error instead of dying, as error_reporting(0) will hide it.
    // For debugging, you might temporarily enable error_reporting here.
    error_log("Database Query Error in export_excel.php: " . mysqli_error($conection));
    die("Error generating report. Please check server logs.");
}

// --- Excel Export Headers ---
header('Content-Type: text/csv; charset=utf-8'); // Added charset for better compatibility
header('Content-Disposition: attachment; filename="rekap_presensi_siswa_' . date('Ymd_His') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

// Write the Byte Order Mark (BOM) for UTF-8 compatibility in Excel
// This helps Excel correctly interpret UTF-8 characters.
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));


// Write the column headers (ensure proper parameters for fputcsv)
fputcsv($output, [
    'No',
    'NIS',
    'Nama',
    'Kelas',
    'Tanggal',
    'Jam Masuk',
    'Lokasi',
    'Foto Masuk',
    'Jam Keluar',
    'Foto Keluar'
], ',', '"', '\\'); // Explicitly define delimiter, enclosure, and escape character

// Write the data rows
$no = 1;
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Build the full URL for the photo if needed, otherwise just indicate presence
        $foto_masuk_text = !empty($row['foto_masuk']) ?
                           'http://' . $_SERVER['HTTP_HOST'] . '/aplikasi_presensi_siswa/siswa/presensi/foto/' . $row['foto_masuk'] :
                           'Tidak Ada Foto';
        $foto_keluar_text = !empty($row['foto_keluar']) ?
                            'http://' . $_SERVER['HTTP_HOST'] . '/aplikasi_presensi_siswa/siswa/presensi/foto/' . $row['foto_keluar'] :
                            'Tidak Ada Foto';

        fputcsv($output, [
            $no++,
            $row['nis'],
            $row['nama'],
            $row['kelas'] ?? '-',
            $row['tanggal_masuk'],
            $row['jam_masuk'],
            $row['nama_lokasi'] ?? '-',
            $foto_masuk_text,
            $row['jam_keluar'] ?? '-',
            $foto_keluar_text
        ], ',', '"', '\\'); // Explicitly define delimiter, enclosure, and escape character
    }
} else {
    fputcsv($output, ['Tidak ada data presensi ditemukan.'], ',', '"', '\\');
}

fclose($output);
exit;
?>