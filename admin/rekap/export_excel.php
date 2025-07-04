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

include('../layout/header.php'); // Ensure this path is correct
require_once('../../config.php'); // Ensure this path is correct

// Get list of classes from the database for the dropdown
$query_kelas = "SELECT DISTINCT kelas FROM siswa WHERE kelas IS NOT NULL AND kelas != '' ORDER BY kelas ASC";
$result_kelas = mysqli_query($conection, $query_kelas);
$daftar_kelas = [];
if ($result_kelas) {
    while ($row = mysqli_fetch_assoc($result_kelas)) {
        $daftar_kelas[] = $row['kelas'];
    }
}

// Get list of students (NIS and Nama) for the student dropdown
$query_siswa = "SELECT id, nis, nama FROM siswa ORDER BY nama ASC";
$result_siswa = mysqli_query($conection, $query_siswa);
$daftar_siswa = [];
if ($result_siswa) {
    while ($row = mysqli_fetch_assoc($result_siswa)) {
        $daftar_siswa[] = $row;
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
                    <option value="per_hari">Per Hari</option>
                    <option value="per_bulan">Per Bulan</option>
                    <option value="per_siswa">Per Siswa</option>
                    <option value="per_tahun">Per Tahun</option>
                    <option value="semua">Semua Data</option>
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

            <div id="siswa_section" class="mb-3" style="display: none;">
                <label for="id_siswa" class="form-label">Pilih Siswa:</label>
                <select class="form-select" id="id_siswa" name="id_siswa">
                    <option value="">-- Pilih Siswa --</option>
                    <?php foreach ($daftar_siswa as $siswa): ?>
                        <option value="<?php echo htmlspecialchars($siswa['id']); ?>">
                            <?php echo htmlspecialchars($siswa['nis'] . ' - ' . $siswa['nama']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="kelas_filter_section" class="mb-3">
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
    const siswaSection = document.getElementById('siswa_section');
    const kelasFilterSection = document.getElementById('kelas_filter_section');

    function toggleSections() {
        const selectedType = exportTypeSelect.value;

        // Hide all sections initially
        tanggalSection.style.display = 'none';
        bulanSection.style.display = 'none';
        tahunSection.style.display = 'none';
        siswaSection.style.display = 'none';
        kelasFilterSection.style.display = 'block'; // Default to visible for most exports

        // Reset values when hidden
        document.getElementById('tanggal').value = '';
        document.getElementById('bulan').value = '<?php echo date('m'); ?>';
        document.getElementById('tahun').value = '<?php echo date('Y'); ?>';
        document.getElementById('id_siswa').value = '';
        document.getElementById('kelas_filter').value = 'all';

        switch (selectedType) {
            case 'per_hari':
                tanggalSection.style.display = 'block';
                // kelasFilterSection.style.display = 'block'; // already default
                break;
            case 'per_bulan':
                bulanSection.style.display = 'block';
                tahunSection.style.display = 'block';
                // kelasFilterSection.style.display = 'block'; // already default
                break;
            case 'per_siswa':
                siswaSection.style.display = 'block';
                bulanSection.style.display = 'block'; // Often useful to filter student attendance by month
                tahunSection.style.display = 'block';
                kelasFilterSection.style.display = 'none'; // Class filter not needed when selecting specific student
                break;
            case 'per_tahun':
                tahunSection.style.display = 'block';
                // kelasFilterSection.style.display = 'block'; // already default
                break;
            case 'semua':
                // kelasFilterSection.style.display = 'block'; // already default
                break;
        }
    }

    exportTypeSelect.addEventListener('change', toggleSections);

    // Call on page load to set initial visibility based on default selected option or no option
    toggleSections();
});
</script>

<?php include('../layout/foother.php'); ?>