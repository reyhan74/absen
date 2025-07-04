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
$tanggal = $_POST['tanggal'] ?? '';
$bulan = $_POST['bulan'] ?? '';
$tahun = $_POST['tahun'] ?? '';
$idSiswa = $_POST['id_siswa'] ?? '';
$kelasFilter = $_POST['kelas_filter'] ?? 'all';

$fileName = "Rekap_Presensi_";
$whereClause = "WHERE p.tanggal_masuk IS NOT NULL ";
$params = [];
$paramTypes = "";

// --- Build SQL Query based on export type ---
switch ($exportType) {
    case 'per_hari':
        if (!empty($tanggal)) {
            $whereClause .= "AND DATE(p.tanggal_masuk) = ? ";
            $params[] = $tanggal;
            $paramTypes .= "s";
            $fileName .= "Harian_" . $tanggal;
        } else {
            die("Tanggal tidak boleh kosong untuk ekspor harian.");
        }
        break;
    case 'per_bulan':
        if (!empty($bulan) && !empty($tahun)) {
            $whereClause .= "AND MONTH(p.tanggal_masuk) = ? AND YEAR(p.tanggal_masuk) = ? ";
            $params[] = $bulan;
            $params[] = $tahun;
            $paramTypes .= "ss";
            $fileName .= "Bulanan_" . $bulan . "_" . $tahun;
        } else {
            die("Bulan dan Tahun tidak boleh kosong untuk ekspor bulanan.");
        }
        break;
    case 'per_siswa':
        if (!empty($idSiswa)) {
            $whereClause .= "AND s.id = ? ";
            $params[] = $idSiswa;
            $paramTypes .= "i";

            // Optional: Filter by month/year for specific student's attendance
            if (!empty($bulan) && !empty($tahun)) {
                $whereClause .= "AND MONTH(p.tanggal_masuk) = ? AND YEAR(p.tanggal_masuk) = ? ";
                $params[] = $bulan;
                $params[] = $tahun;
                $paramTypes .= "ss";
                $fileName .= "Siswa_" . $idSiswa . "_" . $bulan . "_" . $tahun;
            } else {
                 $fileName .= "Siswa_" . $idSiswa . "_Semua";
            }
        } else {
            die("Siswa tidak boleh kosong untuk ekspor per siswa.");
        }
        break;
    case 'per_tahun':
        if (!empty($tahun)) {
            $whereClause .= "AND YEAR(p.tanggal_masuk) = ? ";
            $params[] = $tahun;
            $paramTypes .= "s";
            $fileName .= "Tahunan_" . $tahun;
        } else {
            die("Tahun tidak boleh kosong untuk ekspor tahunan.");
        }
        break;
    case 'semua':
        $fileName .= "Semua_Data";
        break;
    default:
        die("Tipe ekspor tidak valid.");
}

// Add Class Filter if not 'all' and not 'per_siswa' (per_siswa handles student ID directly)
if ($kelasFilter != 'all' && $exportType !== 'per_siswa') {
    $whereClause .= "AND s.kelas = ? ";
    $params[] = $kelasFilter;
    $paramTypes .= "s";
    $fileName .= "_Kelas_" . $kelasFilter;
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
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

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
?>