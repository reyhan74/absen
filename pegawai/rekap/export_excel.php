<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("location: ../../auth/login.php?pesan=belum_login");
    exit;
} elseif ($_SESSION["role"] != 'guru') {
    header("location:../../auth/login.php?pesan=tolak_akses");
    exit;
}

require_once('../../config.php');
require_once('../../vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Ambil filter bulan dan tahun dari URL, default bulan ini
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

// Query ambil data bulan ini
$query = "
    SELECT 
        s.kelas,
        s.nis,
        s.nama,
        p.tanggal_masuk,
        p.jam_masuk,
        p.nama_lokasi,
        p.foto_masuk,
        o.jam_keluar,
        o.foto_keluar
    FROM siswa s
    LEFT JOIN presensi p ON s.id = p.id_siswa
    LEFT JOIN presensi_out o ON s.id = o.id_siswa 
        AND p.tanggal_masuk = o.tanggal_keluar
    WHERE MONTH(p.tanggal_masuk) = '$bulan'
      AND YEAR(p.tanggal_masuk) = '$tahun'
    ORDER BY s.kelas ASC, s.nis ASC, p.tanggal_masuk ASC
";

$result = mysqli_query($conection, $query);
if (!$result) {
    die("Query Error: " . mysqli_error($conection));
}

// Kelompokkan data per kelas
$dataByClass = [];

while ($row = mysqli_fetch_assoc($result)) {
    $kelas = $row['kelas'] ?: 'Tanpa Kelas';
    if (!isset($dataByClass[$kelas])) {
        $dataByClass[$kelas] = [];
    }
    $dataByClass[$kelas][] = $row;
}

$spreadsheet = new Spreadsheet();
$sheetIndex = 0;

if (!empty($dataByClass)) {
    foreach ($dataByClass as $kelas => $rows) {
        if ($sheetIndex > 0) {
            $spreadsheet->createSheet();
        }
        $spreadsheet->setActiveSheetIndex($sheetIndex);
        $sheet = $spreadsheet->getActiveSheet();

        // Batas nama sheet max 31 karakter
        $title = substr($kelas, 0, 31);
        $sheet->setTitle($title);

        // Header
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'NIS');
        $sheet->setCellValue('C1', 'Nama');
        $sheet->setCellValue('D1', 'Tanggal');
        $sheet->setCellValue('E1', 'Jam Masuk');
        $sheet->setCellValue('F1', 'Lokasi');
        $sheet->setCellValue('G1', 'Foto Masuk');
        $sheet->setCellValue('H1', 'Jam Keluar');
        $sheet->setCellValue('I1', 'Foto Keluar');

        $no = 1;
        $rowExcel = 2;

        foreach ($rows as $row) {
            $sheet->setCellValue("A$rowExcel", $no++);
            $sheet->setCellValue("B$rowExcel", $row['nis']);
            $sheet->setCellValue("C$rowExcel", $row['nama']);
            $sheet->setCellValue("D$rowExcel", $row['tanggal_masuk']);
            $sheet->setCellValue("E$rowExcel", $row['jam_masuk']);
            $sheet->setCellValue("F$rowExcel", $row['nama_lokasi'] ?? '-');
            $sheet->setCellValue("G$rowExcel", $row['foto_masuk'] ?? '-');
            $sheet->setCellValue("H$rowExcel", $row['jam_keluar'] ?? '-');
            $sheet->setCellValue("I$rowExcel", $row['foto_keluar'] ?? '-');

            $rowExcel++;
        }

        // Lebarkan kolom
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheetIndex++;
    }
} else {
    // Jika tidak ada data sama sekali
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle("Data Kosong");
    $sheet->setCellValue("A1", "Tidak ada data presensi untuk bulan $bulan-$tahun.");
}

// Nama file
$fileName = 'rekap_presensi_bulanan_' . $bulan . '_' . $tahun . '_' . date('YmdHis') . '.xlsx';

// Output
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"$fileName\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
