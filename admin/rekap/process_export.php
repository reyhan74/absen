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

// --- 1. Get POST Data and Sanitize ---
$export_type = $_POST['export_type'] ?? '';
$kelas_filter = $_POST['kelas_filter'] ?? 'all';
$tanggal = $_POST['tanggal'] ?? ''; // Only for 'harian'
$bulan = $_POST['bulan'] ?? '';     // For 'bulanan'
$tahun = $_POST['tahun'] ?? '';     // For 'bulanan' or 'tahunan'

// Sanitize inputs
$export_type = mysqli_real_escape_string($conection, $export_type);
$kelas_filter = mysqli_real_escape_string($conection, $kelas_filter);
$tanggal = mysqli_real_escape_string($conection, $tanggal);
$bulan = mysqli_real_escape_string($conection, $bulan);
$tahun = mysqli_real_escape_string($conection, $tahun);

// --- 2. Build the SQL Query based on Filters ---
$sql_filter = "p.tanggal_masuk IS NOT NULL";
$filename_suffix = ""; // To make the exported file name descriptive

switch ($export_type) {
    case 'harian':
        if (!empty($tanggal)) {
            $sql_filter .= " AND DATE(p.tanggal_masuk) = '$tanggal'";
            $filename_suffix = "_Harian_" . date('Ymd', strtotime($tanggal));
        } else {
            // Fallback or error if tanggal is not provided for daily export
            // For now, let's just make it today's date
            $sql_filter .= " AND DATE(p.tanggal_masuk) = CURDATE()";
            $filename_suffix = "_Harian_" . date('Ymd');
        }
        break;
    case 'bulanan':
        if (!empty($bulan) && !empty($tahun)) {
            $sql_filter .= " AND MONTH(p.tanggal_masuk) = '$bulan' AND YEAR(p.tanggal_masuk) = '$tahun'";
            $filename_suffix = "_Bulanan_" . $tahun . $bulan;
        } else {
             // Fallback to current month/year if not provided
            $sql_filter .= " AND MONTH(p.tanggal_masuk) = MONTH(CURDATE()) AND YEAR(p.tanggal_masuk) = YEAR(CURDATE())";
            $filename_suffix = "_Bulanan_" . date('Ym');
        }
        break;
    case 'tahunan':
        if (!empty($tahun)) {
            $sql_filter .= " AND YEAR(p.tanggal_masuk) = '$tahun'";
            $filename_suffix = "_Tahunan_" . $tahun;
        } else {
            // Fallback to current year if not provided
            $sql_filter .= " AND YEAR(p.tanggal_masuk) = YEAR(CURDATE())";
            $filename_suffix = "_Tahunan_" . date('Y');
        }
        break;
    default:
        // No specific export type selected, maybe export all data or redirect with error
        $filename_suffix = "_Semua_Data";
        break;
}

if ($kelas_filter != 'all') {
    $sql_filter .= " AND s.kelas = '$kelas_filter'";
    $filename_suffix .= "_" . $kelas_filter;
}

$query = "
    SELECT
        s.nis,
        s.nama,
        s.kelas,
        p.tanggal_masuk,
        p.jam_masuk,
        p.nama_lokasi,
        p.foto_masuk,
        o.jam_keluar,
        o.foto_keluar
    FROM siswa s
    LEFT JOIN presensi p ON s.id = p.id_siswa
    LEFT JOIN presensi_out o ON s.id = o.id_siswa AND p.tanggal_masuk = o.tanggal_keluar
    WHERE $sql_filter
    ORDER BY p.tanggal_masuk DESC, s.kelas ASC, s.nama ASC
";

$result = mysqli_query($conection, $query);

if (!$result) {
    die("Query Error: " . mysqli_error($conection));
}

// --- 3. Create Excel Spreadsheet using PhpSpreadsheet ---
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Data Presensi Siswa');

// Set headers
$headers = ['No', 'NIS', 'Nama', 'Kelas', 'Tanggal Masuk', 'Jam Masuk', 'Lokasi Masuk', 'Jam Keluar', 'Status Presensi'];
$sheet->fromArray($headers, NULL, 'A1');

// Style headers
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF428BCA']], // Blue color
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
];
$sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray($headerStyle);

// Populate data
$rowNum = 2;
$no = 1;
while ($row = mysqli_fetch_assoc($result)) {
    $status_presensi = "Belum Keluar";
    if (!empty($row['jam_keluar'])) {
        // Calculate attendance duration (optional)
        $jam_masuk_ts = strtotime($row['jam_masuk']);
        $jam_keluar_ts = strtotime($row['jam_keluar']);
        $durasi_detik = $jam_keluar_ts - $jam_masuk_ts;
        $durasi_jam = floor($durasi_detik / 3600);
        $durasi_menit = floor(($durasi_detik % 3600) / 60);
        $status_presensi = "Pulang (" . $durasi_jam . "j " . $durasi_menit . "m)";
    }

    $sheet->setCellValue('A' . $rowNum, $no++);
    $sheet->setCellValue('B' . $rowNum, $row['nis']);
    $sheet->setCellValue('C' . $rowNum, $row['nama']);
    $sheet->setCellValue('D' . $rowNum, $row['kelas']);
    $sheet->setCellValue('E' . $rowNum, $row['tanggal_masuk']);
    $sheet->setCellValue('F' . $rowNum, $row['jam_masuk']);
    $sheet->setCellValue('G' . $rowNum, $row['nama_lokasi']);
    $sheet->setCellValue('H' . $rowNum, $row['jam_keluar'] ?? '-');
    $sheet->setCellValue('I' . $rowNum, $status_presensi); // Add status

    // Optional: Add photo links if needed, but direct images in Excel are complex
    // $sheet->setCellValue('J' . $rowNum, !empty($row['foto_masuk']) ? 'Link Foto Masuk' : '');
    // $sheet->setCellValue('K' . $rowNum, !empty($row['foto_keluar']) ? 'Link Foto Keluar' : '');

    $rowNum++;
}

// Auto-size columns for better readability
foreach (range('A', $sheet->getHighestColumn()) as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// --- 4. Set Headers for Download and Output Excel File ---
$filename = "Rekap_Presensi_Siswa" . $filename_suffix . ".xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

exit;
?>