<?php
// admin/pages/presensi/process_export.php

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

if (!isset($_POST['export_type'])) {
    header("location: export_options.php?pesan=invalid_request");
    exit;
}

$export_type = $_POST['export_type'];
$kelas_filter = $_POST['kelas_filter']; // Bisa 'all' atau nama kelas
$date_string = date('YmdHis'); // Untuk nama file

$query = "";
$title_suffix = "";
$where_clauses = [];

// Base Query
$base_query = "
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
";

if ($export_type === 'harian') {
    $tanggal = $_POST['tanggal'] ?? date('Y-m-d');
    $where_clauses[] = "p.tanggal_masuk = '$tanggal'";
    $title_suffix = "Harian_" . date('Ymd', strtotime($tanggal));
} elseif ($export_type === 'bulanan') {
    $bulan = $_POST['bulan'] ?? date('m');
    $tahun = $_POST['tahun'] ?? date('Y');
    $where_clauses[] = "MONTH(p.tanggal_masuk) = '$bulan'";
    $where_clauses[] = "YEAR(p.tanggal_masuk) = '$tahun'";
    $title_suffix = "Bulanan_" . $bulan . "_" . $tahun;
} elseif ($export_type === 'tahunan') {
    $tahun = $_POST['tahun'] ?? date('Y');
    $where_clauses[] = "YEAR(p.tanggal_masuk) = '$tahun'";
    $title_suffix = "Tahunan_" . $tahun;
}

// Tambahkan filter kelas jika bukan "all"
if ($kelas_filter !== 'all') {
    $where_clauses[] = "s.kelas = '" . mysqli_real_escape_string($conection, $kelas_filter) . "'";
    $title_suffix .= "_" . str_replace(" ", "_", $kelas_filter); // Tambahkan kelas ke nama file
}


if (!empty($where_clauses)) {
    $query = $base_query . " WHERE " . implode(" AND ", $where_clauses);
} else {
    $query = $base_query; // Jika tidak ada filter waktu (seharusnya tidak terjadi)
}

$query .= " ORDER BY s.kelas ASC, s.nis ASC, p.tanggal_masuk ASC";


$result = mysqli_query($conection, $query);
if (!$result) {
    die("Query Error: " . mysqli_error($conection));
}

// Kelompokkan data per kelas (tetap dilakukan agar bisa 1 sheet per kelas)
$dataByClass = [];
if ($result->num_rows > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $kelas = $row['kelas'] ?: 'Tanpa Kelas';
        if (!isset($dataByClass[$kelas])) {
            $dataByClass[$kelas] = [];
        }
        $dataByClass[$kelas][] = $row;
    }
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
    $sheet->setCellValue("A1", "Tidak ada data presensi untuk filter yang dipilih.");
}

// Nama file
$fileName = 'rekap_presensi_' . $title_suffix . '_' . date('YmdHis') . '.xlsx';

// Output
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"$fileName\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;

?>