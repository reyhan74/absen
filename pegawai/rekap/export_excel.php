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
require_once('../../vendor/autoload.php'); // pastikan PHPSpreadsheet sudah di-install

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Ambil filter dari URL
$periode = $_GET['periode'] ?? 'all';

// Buat filter SQL
$where = "";
switch ($periode) {
    case 'hari':
        $where = "AND DATE(p.tanggal_masuk) = CURDATE()";
        break;
    case 'minggu':
        $where = "AND YEARWEEK(p.tanggal_masuk, 1) = YEARWEEK(CURDATE(), 1)";
        break;
    case 'bulan':
        $where = "AND MONTH(p.tanggal_masuk) = MONTH(CURDATE()) AND YEAR(p.tanggal_masuk) = YEAR(CURDATE())";
        break;
}

// Query ambil data
$query = "
    SELECT 
        s.nis, s.nama, s.kelas,
        p.tanggal_masuk, p.jam_masuk, p.nama_lokasi, p.foto_masuk,
        o.jam_keluar, o.foto_keluar
    FROM siswa s
    LEFT JOIN presensi p ON s.id = p.id_siswa
    LEFT JOIN presensi_out o ON s.id = o.id_siswa AND p.tanggal_masuk = o.tanggal_keluar
    WHERE p.tanggal_masuk IS NOT NULL $where
    ORDER BY p.tanggal_masuk ASC, s.nis ASC
";

$result = mysqli_query($conection, $query);
if (!$result) {
    die("Query Error: " . mysqli_error($conection));
}

// Kelompokkan data per tanggal
$dataByDate = [];

while ($row = mysqli_fetch_assoc($result)) {
    $tgl = $row['tanggal_masuk'];
    if (!isset($dataByDate[$tgl])) {
        $dataByDate[$tgl] = [];
    }
    $dataByDate[$tgl][] = $row;
}

$spreadsheet = new Spreadsheet();
$sheetIndex = 0;

if (!empty($dataByDate)) {
    foreach ($dataByDate as $tanggal => $rows) {
        if ($sheetIndex > 0) {
            $spreadsheet->createSheet();
        }
        $spreadsheet->setActiveSheetIndex($sheetIndex);
        $sheet = $spreadsheet->getActiveSheet();

        // Batas nama sheet 31 karakter
        $title = date('d-m-Y', strtotime($tanggal));
        $title = substr($title, 0, 31);
        $sheet->setTitle($title);

        // Header kolom
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'NIS');
        $sheet->setCellValue('C1', 'Nama');
        $sheet->setCellValue('D1', 'Kelas');
        $sheet->setCellValue('E1', 'Tanggal');
        $sheet->setCellValue('F1', 'Jam Masuk');
        $sheet->setCellValue('G1', 'Lokasi');
        $sheet->setCellValue('H1', 'Foto Masuk');
        $sheet->setCellValue('I1', 'Jam Keluar');
        $sheet->setCellValue('J1', 'Foto Keluar');

        $no = 1;
        $rowExcel = 2;

        foreach ($rows as $row) {
            $sheet->setCellValue("A$rowExcel", $no++);
            $sheet->setCellValue("B$rowExcel", $row['nis']);
            $sheet->setCellValue("C$rowExcel", $row['nama']);
            $sheet->setCellValue("D$rowExcel", $row['kelas'] ?? '-');
            $sheet->setCellValue("E$rowExcel", $row['tanggal_masuk']);
            $sheet->setCellValue("F$rowExcel", $row['jam_masuk']);
            $sheet->setCellValue("G$rowExcel", $row['nama_lokasi'] ?? '-');
            $sheet->setCellValue("H$rowExcel", $row['foto_masuk'] ?? '-');
            $sheet->setCellValue("I$rowExcel", $row['jam_keluar'] ?? '-');
            $sheet->setCellValue("J$rowExcel", $row['foto_keluar'] ?? '-');

            $rowExcel++;
        }

        // Auto width kolom
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheetIndex++;
    }
} else {
    // Jika data kosong
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle("Data Kosong");
    $sheet->setCellValue("A1", "Tidak ada data presensi.");
}

// Nama file download
$fileName = 'rekap_presensi_' . $periode . '_' . date('YmdHis') . '.xlsx';

// Output
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"$fileName\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
