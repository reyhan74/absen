<?php
// admin/pages/presensi/export_options.php

session_start();
if (!isset($_SESSION['login'])) {
    header("location: ../../auth/login.php?pesan=belum_login");
    exit;
} elseif ($_SESSION["role"] != 'admin') {
    header("location:../../auth/login.php?pesan=tolak_akses");
    exit;
}

include('../layout/header.php'); // Pastikan path ini benar
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

<?php include('../layout/foother.php'); ?>