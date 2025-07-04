<?php
// admin/pages/presensi/process_export.php

session_start();
if (!isset($_SESSION['login'])) {
    header("location: ../../auth/login.php?pesan=belum_login");
    exit;
} elseif ($_SESSION["role"] != 'admin') {
    header("location:../../auth/login.php?pesan=tolak_akses");
    exit;
}

require_once('../../config.php'); // Your database connection
require '../../vendor/autoload.php'; // Path to Composer's autoloader

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// --- Get export parameters ---
$exportType = $_POST['export_type'] ?? '';

$fileName = "Rekap_Presensi_";
$whereClause = "WHERE p.tanggal_masuk IS NOT NULL ";
$params = [];
$paramTypes = "";

// --- Build SQL Query based on export type ---
switch ($exportType) {
    case 'per_hari':
        $tanggal = $_POST['tanggal'] ?? '';
        if (empty($tanggal)) die("Tanggal tidak boleh kosong untuk ekspor harian.");
        $whereClause .= "AND DATE(p.tanggal_masuk) = ? ";
        $params[] = $tanggal;
        $paramTypes .= "s";
        $fileName .= "Harian_" . $tanggal;
        break;

    case 'per_minggu':
        $tanggal_minggu = $_POST['tanggal_minggu'] ?? '';
        if (empty($tanggal_minggu)) die("Tanggal minggu tidak boleh kosong untuk ekspor mingguan.");
        $whereClause .= "AND YEARWEEK(p.tanggal_masuk, 1) = YEARWEEK(STR_TO_DATE(?, '%Y-%m-%d'), 1) "; // Use STR_TO_DATE for robust comparison
        $params[] = $tanggal_minggu;
        $paramTypes .= "s";
        $fileName .= "Mingguan_" . $tanggal_minggu;
        break;

    case 'per_bulan':
        $bulan = $_POST['bulan'] ?? '';
        $tahun = $_POST['tahun'] ?? '';
        if (empty($bulan) || empty($tahun)) die("Bulan dan Tahun tidak boleh kosong untuk ekspor bulanan.");
        $whereClause .= "AND MONTH(p.tanggal_masuk) = ? AND YEAR(p.tanggal_masuk) = ? ";
        $params[] = $bulan;
        $params[] = $tahun;
        $paramTypes .= "ss";
        $fileName .= "Bulanan_" . $bulan . "_" . $tahun;
        break;

    case 'per_tahun':
        $tahun = $_POST['tahun'] ?? '';
        if (empty($tahun)) die("Tahun tidak boleh kosong untuk ekspor tahunan.");
        $whereClause .= "AND YEAR(p.tanggal_masuk) = ? ";
        $params[] = $tahun;
        $paramTypes .= "s";
        $fileName .= "Tahunan_" . $tahun;
        break;

    case 'per_siswa':
        $idSiswa = $_POST['id_siswa'] ?? '';
        $siswaPeriode = $_POST['siswa_periode'] ?? '';
        if (empty($idSiswa)) die("Siswa tidak boleh kosong untuk ekspor per siswa.");

        // Fetch student's name and class for filename
        $stmt_siswa_info = mysqli_prepare($conection, "SELECT nis, nama, kelas FROM siswa WHERE id = ?");
        mysqli_stmt_bind_param($stmt_siswa_info, "i", $idSiswa);
        mysqli_stmt_execute($stmt_siswa_info);
        $result_siswa_info = mysqli_stmt_get_result($stmt_siswa_info);
        $siswa_info = mysqli_fetch_assoc($result_siswa_info);
        $siswa_nama_file = str_replace(' ', '_', $siswa_info['nama'] ?? 'Siswa');
        mysqli_stmt_close($stmt_siswa_info);

        $whereClause .= "AND s.id = ? ";
        $params[] = $idSiswa;
        $paramTypes .= "i";
        $fileName .= "Siswa_" . $siswa_nama_file;

        if ($siswaPeriode == 'bulanan') {
            $siswaBulan = $_POST['siswa_bulan'] ?? '';
            $siswaTahun = $_POST['siswa_tahun'] ?? '';
            if (empty($siswaBulan) || empty($siswaTahun)) die("Bulan dan Tahun siswa tidak boleh kosong untuk ekspor bulanan siswa.");
            $whereClause .= "AND MONTH(p.tanggal_masuk) = ? AND YEAR(p.tanggal_masuk) = ? ";
            $params[] = $siswaBulan;
            $params[] = $siswaTahun;
            $paramTypes .= "ss";
            $fileName .= "_Bulan_" . $siswaBulan . "_" . $siswaTahun;
        } elseif ($siswaPeriode == 'tahunan') {
            $siswaTahun = $_POST['siswa_tahun'] ?? '';
            if (empty($siswaTahun)) die("Tahun siswa tidak boleh kosong untuk ekspor tahunan siswa.");
            $whereClause .= "AND YEAR(p.tanggal_masuk) = ? ";
            $params[] = $siswaTahun;
            $paramTypes .= "s";
            $fileName .= "_Tahun_" . $siswaTahun;
        } elseif ($siswaPeriode == 'mingguan') {
             $siswaTanggalMinggu = $_POST['siswa_tanggal_minggu'] ?? '';
             if (empty($siswaTanggalMinggu)) die("Tanggal minggu siswa tidak boleh kosong untuk ekspor mingguan siswa.");
             $whereClause .= "AND YEARWEEK(p.tanggal_masuk, 1) = YEARWEEK(STR_TO_DATE(?, '%Y-%m-%d'), 1) ";
             $params[] = $siswaTanggalMinggu;
             $paramTypes .= "s";
             $fileName .= "_Minggu_" . $siswaTanggalMinggu;
        }
        break;

    case 'semua':
        $fileName .= "Semua_Data";
        break;

    default:
        die("Tipe ekspor tidak valid.");
}

// Add Multi-select Class Filter
$selectedClasses = $_POST['kelas_filter'] ?? [];
if (!empty($selectedClasses) && $exportType !== 'per_siswa') {
    $placeholders = implode(',', array_fill(0, count($selectedClasses), '?'));
    $whereClause .= "AND s.kelas IN ($placeholders) ";
    $params = array_merge($params, $selectedClasses);
    $paramTypes .= str_repeat("s", count($selectedClasses)); // 's' for each class

    $fileName .= "_Kelas_" . implode('-', $selectedClasses);
}


$sql = "
    SELECT
        s.nis, s.nama, s.kelas,
        p.tanggal_masuk, p.jam_masuk, p.foto_masuk, p.nama_lokasi,
        o.jam_keluar, o.foto_keluar
    FROM siswa s
    LEFT JOIN presensi p ON s.id = p.id_siswa
    LEFT JOIN presensi_out o ON s.id = o.id_siswa AND p.tanggal_masuk = o.tanggal_keluar
    " . $whereClause . "
    ORDER BY s.kelas ASC, s.nama ASC, p.tanggal_masuk ASC, p.jam_masuk ASC
";

// Prepare and execute the statement
$stmt = mysqli_prepare($conection, $sql);

if ($stmt === false) {
    die("Prepare failed: " . htmlspecialchars(mysqli_error($conection)));
}

if (!empty($params)) {
    // Dynamically bind parameters
    mysqli_stmt_bind_param($stmt, $paramTypes, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    die("Query execution error: " . mysqli_error($conection));
}

// --- Create new Spreadsheet object ---
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Data Presensi Siswa');

// --- Set Header Row ---
$headers = [
    'No', 'NIS', 'Nama', 'Kelas', 'Tanggal', 'Jam Masuk', 'Lokasi Masuk',
    'Foto Masuk (Link)', 'Jam Keluar', 'Foto Keluar (Link)'
];
$sheet->fromArray($headers, NULL, 'A1');

// --- Populate Data ---
$rowNum = 2; // Start data from row 2
while ($row = mysqli_fetch_assoc($result)) {
    $data = [
        ($rowNum - 1), // No
        $row['nis'],
        $row['nama'],
        $row['kelas'] ?? '-',
        $row['tanggal_masuk'],
        $row['jam_masuk'],
        $row['nama_lokasi'] ?? '-',
        !empty($row['foto_masuk']) ? '../../siswa/presensi/foto/' . $row['foto_masuk'] : 'Tidak Ada',
        $row['jam_keluar'] ?? '-',
        !empty($row['foto_keluar']) ? '../../siswa/presensi/foto/' . $row['foto_keluar'] : 'Tidak Ada'
    ];
    $sheet->fromArray($data, NULL, 'A' . $rowNum);
    $rowNum++;
}

// --- Styling ---
// Header row styling
$sheet->getStyle('A1:J1')->applyFromArray([
    'font' => [
        'bold' => true,
        'color' => ['argb' => 'FFFFFFFF'],
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'color' => ['argb' => 'FF4285F4'], // Google Blue
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['argb' => 'FF000000'],
        ],
    ],
]);

// Apply border to all data cells
$sheet->getStyle('A1:J' . ($rowNum - 1))->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['argb' => 'FF000000'],
        ],
    ],
]);

// Auto-size columns
foreach (range('A', $sheet->getHighestDataColumn()) as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true); }

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

// --- Output to Browser ---
$writer = new Xlsx($spreadsheet);
$fileName .= "_" . date('Ymd_His') . ".xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $fileName . '"');
header('Cache-Control: max-age=0');
$writer->save('php://output');

mysqli_stmt_close($stmt);
mysqli_close($conection);
exit;