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
        $sheet->setCellValue('H1', 'Jam Keluar');<?php
// admin/pages/presensi/export_options.php

session_start();
if (!isset($_SESSION['login'])) {
    header("location: ../../auth/login.php?pesan=belum_login");
    exit;
} elseif ($_SESSION["role"] != 'guru') {
    header("location:../../auth/login.php?pesan=tolak_akses");
    exit;
}

include('../../layout/header.php'); // Pastikan path ini benar
require_once('../../config.php'); // Pastikan path ini benar

// Ambil daftar kelas dari database untuk dropdown pilihan
$query_kelas = "SELECT DISTINCT kelas FROM siswa ORDER BY kelas ASC";
$result_kelas = mysqli_query($conection, $query_kelas);
$daftar_kelas = [];
if ($result_kelas) {
    while ($row = mysqli_fetch_assoc($result_kelas)) {
        $daftar_kelas[] = $row['kelas'];
    }
}
?>

<main class="container py-4">
    <h1 class="mb-4 text-center">Opsi Ekspor Data Presensi</h1>

    <div class="card p-4 shadow-sm">
        <form action="process_export.php" method="POST">
            <div class="mb-3">
                <label for="export_type" class="form-label">Pilih Tipe Ekspor:</label>
                <select class="form-select" id="export_type" name="export_type" required>
                    <option value="">-- Pilih Tipe Ekspor --</option>
                    <option value="harian">Harian</option>
                    <option value="bulanan">Bulanan</option>
                    <option value="tahunan">Tahunan</option>
                </select>
            </div>

            <div id="tanggal_section" class="mb-3" style="display: none;">
                <label for="tanggal" class="form-label">Pilih Tanggal:</label>
                <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?php echo date('Y-m-d'); ?>">
            </div>

            <div id="bulan_section" class="mb-3" style="display: none;">
                <label for="bulan" class="form-label">Pilih Bulan:</label>
                <select class="form-select" id="bulan" name="bulan">
                    <?php 
                    $bulan_nama = [
                        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                    ];
                    foreach ($bulan_nama as $num => $name) {
                        $selected = (date('m') == $num) ? 'selected' : '';
                        echo "<option value='{$num}' {$selected}>{$name}</option>";
                    }
                    ?>
                </select>
            </div>

            <div id="tahun_section" class="mb-3" style="display: none;">
                <label for="tahun" class="form-label">Pilih Tahun:</label>
                <select class="form-select" id="tahun" name="tahun">
                    <?php 
                    $current_year = date('Y');
                    for ($y = $current_year - 5; $y <= $current_year + 1; $y++) {
                        $selected = ($current_year == $y) ? 'selected' : '';
                        echo "<option value='{$y}' {$selected}>{$y}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="kelas_filter" class="form-label">Filter Berdasarkan Kelas:</label>
                <select class="form-select" id="kelas_filter" name="kelas_filter">
                    <option value="all">Semua Kelas</option>
                    <?php foreach ($daftar_kelas as $kelas_nama): ?>
                        <option value="<?php echo htmlspecialchars($kelas_nama); ?>"><?php echo htmlspecialchars($kelas_nama); ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">Pilih 'Semua Kelas' untuk ekspor seluruh data yang difilter, atau pilih kelas tertentu.</div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="fas fa-file-excel me-2"></i> Ekspor ke Excel</button>
        </form>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const exportTypeSelect = document.getElementById('export_type');
    const tanggalSection = document.getElementById('tanggal_section');
    const bulanSection = document.getElementById('bulan_section');
    const tahunSection = document.getElementById('tahun_section');
    const kelasFilterSelect = document.getElementById('kelas_filter'); // New: Kelas filter

    function toggleSections() {
        const selectedType = exportTypeSelect.value;

        tanggalSection.style.display = 'none';
        bulanSection.style.display = 'none';
        tahunSection.style.display = 'none';

        if (selectedType === 'harian') {
            tanggalSection.style.display = 'block';
            bulanSection.style.display = 'block'; // Untuk tahun harian
            tahunSection.style.display = 'block'; // Untuk tahun harian
        } else if (selectedType === 'bulanan') {
            bulanSection.style.display = 'block';
            tahunSection.style.display = 'block';
        } else if (selectedType === 'tahunan') {
            tahunSection.style.display = 'block';
        }
    }

    exportTypeSelect.addEventListener('change', toggleSections);

    // Panggil saat halaman dimuat untuk mengatur tampilan awal
    toggleSections(); 
});
</script>

<?php include('../../layout/footer.php'); ?>
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
