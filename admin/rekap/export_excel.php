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

// Get list of students (NIS, Nama, Kelas, ID) for the student dropdown
$query_siswa = "SELECT id, nis, nama, kelas FROM siswa ORDER BY nama ASC";
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
                    <option value="per_minggu">Per Minggu</option>
                    <option value="per_bulan">Per Bulan</option>
                    <option value="per_tahun">Per Tahun</option>
                    <option value="per_siswa">Per Siswa</option>
                    <option value="semua">Semua Data</option>
                </select>
            </div>

            <div id="global_date_filters" style="display: none;">
                <div id="tanggal_section" class="mb-3" style="display: none;">
                    <label for="tanggal" class="form-label">Pilih Tanggal:</label>
                    <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?php echo date('Y-m-d'); ?>">
                </div>

                <div id="minggu_section" class="mb-3" style="display: none;">
                    <label for="tanggal_minggu" class="form-label">Pilih Tanggal di Minggu Ini:</label>
                    <input type="date" class="form-control" id="tanggal_minggu" name="tanggal_minggu" value="<?php echo date('Y-m-d'); ?>">
                    <div class="form-text">Pilih tanggal mana saja dalam minggu yang ingin diekspor.</div>
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
            </div>

            <div id="siswa_section" class="mb-3" style="display: none;">
                <label for="id_siswa" class="form-label">Pilih Siswa:</label>
                <select class="form-select" id="id_siswa" name="id_siswa">
                    <option value="">-- Pilih Siswa --</option>
                    <?php foreach ($daftar_siswa as $siswa): ?>
                        <option value="<?php echo htmlspecialchars($siswa['id']); ?>"
                                data-kelas="<?php echo htmlspecialchars($siswa['kelas']); ?>"
                                data-nama="<?php echo htmlspecialchars($siswa['nama']); ?>">
                            <?php echo htmlspecialchars($siswa['nis'] . ' - ' . $siswa['nama'] . ' (' . $siswa['kelas'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="mt-2" id="siswa_info"></div>

                <div class="mb-3 mt-3">
                    <label for="siswa_periode" class="form-label">Rekap Periode Siswa:</label>
                    <select class="form-select" id="siswa_periode" name="siswa_periode">
                        <option value="bulanan">Bulanan</option>
                        <option value="mingguan">Mingguan</option>
                        <option value="tahunan">Tahunan</option>
                    </select>
                </div>

                <div id="siswa_bulan_section" class="mb-3">
                    <label for="siswa_bulan" class="form-label">Pilih Bulan (Siswa):</label>
                    <select class="form-select" id="siswa_bulan" name="siswa_bulan">
                        <?php
                        foreach ($bulan_nama as $num => $name) {
                            $selected = (date('m') == $num) ? 'selected' : '';
                            echo "<option value='{$num}' {$selected}>{$name}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div id="siswa_tahun_section" class="mb-3">
                    <label for="siswa_tahun" class="form-label">Pilih Tahun (Siswa):</label>
                    <select class="form-select" id="siswa_tahun" name="siswa_tahun">
                        <?php
                        for ($y = $current_year - 5; $y <= $current_year + 1; $y++) {
                            $selected = ($current_year == $y) ? 'selected' : '';
                            echo "<option value='{$y}' {$selected}>{$y}</option>";
                        }
                        ?>
                    </select>
                </div>
                 <div id="siswa_minggu_section" class="mb-3" style="display: none;">
                    <label for="siswa_tanggal_minggu" class="form-label">Pilih Tanggal di Minggu Ini (Siswa):</label>
                    <input type="date" class="form-control" id="siswa_tanggal_minggu" name="siswa_tanggal_minggu" value="<?php echo date('Y-m-d'); ?>">
                    <div class="form-text">Pilih tanggal mana saja dalam minggu yang ingin diekspor.</div>
                </div>
            </div>

            <div id="kelas_filter_section" class="mb-3" style="display: none;">
                <label for="kelas_filter" class="form-label">Filter Berdasarkan Kelas (Pilih Satu atau Lebih):</label>
                <select class="form-select" id="kelas_filter" name="kelas_filter[]" multiple style="min-height: 120px;">
                    <?php foreach ($daftar_kelas as $kelas_nama): ?>
                        <option value="<?php echo htmlspecialchars($kelas_nama); ?>"><?php echo htmlspecialchars($kelas_nama); ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">Tekan CTRL (Windows) atau Command (Mac) untuk memilih lebih dari satu kelas.</div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="fas fa-file-excel me-2"></i> Ekspor ke Excel</button>
        </form>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const exportTypeSelect = document.getElementById('export_type');
    const globalDateFilters = document.getElementById('global_date_filters');
    const tanggalSection = document.getElementById('tanggal_section');
    const mingguSection = document.getElementById('minggu_section'); // New
    const bulanSection = document.getElementById('bulan_section');
    const tahunSection = document.getElementById('tahun_section');

    const siswaSection = document.getElementById('siswa_section');
    const idSiswaSelect = document.getElementById('id_siswa');
    const siswaInfoDiv = document.getElementById('siswa_info');
    const siswaPeriodeSelect = document.getElementById('siswa_periode');
    const siswaBulanSection = document.getElementById('siswa_bulan_section');
    const siswaTahunSection = document.getElementById('siswa_tahun_section');
    const siswaMingguSection = document.getElementById('siswa_minggu_section'); // New

    const kelasFilterSection = document.getElementById('kelas_filter_section');
    const kelasFilterSelect = document.getElementById('kelas_filter');

    // Function to reset all inputs (except export type)
    function resetFormInputs() {
        // Reset global date filters
        document.getElementById('tanggal').value = '<?php echo date('Y-m-d'); ?>';
        document.getElementById('tanggal_minggu').value = '<?php echo date('Y-m-d'); ?>';
        document.getElementById('bulan').value = '<?php echo date('m'); ?>';
        document.getElementById('tahun').value = '<?php echo date('Y'); ?>';

        // Reset siswa filters
        idSiswaSelect.value = '';
        siswaInfoDiv.innerHTML = '';
        siswaPeriodeSelect.value = 'bulanan'; // Default for siswa
        document.getElementById('siswa_bulan').value = '<?php echo date('m'); ?>';
        document.getElementById('siswa_tahun').value = '<?php echo date('Y'); ?>';
        document.getElementById('siswa_tanggal_minggu').value = '<?php echo date('Y-m-d'); ?>';

        // Reset multi-select class filter
        Array.from(kelasFilterSelect.options).forEach(option => {
            option.selected = false;
        });
    }

    function toggleSections() {
        const selectedType = exportTypeSelect.value;

        // Hide all sections initially
        globalDateFilters.style.display = 'none';
        tanggalSection.style.display = 'none';
        mingguSection.style.display = 'none';
        bulanSection.style.display = 'none';
        tahunSection.style.display = 'none';

        siswaSection.style.display = 'none';
        siswaBulanSection.style.display = 'none';
        siswaTahunSection.style.display = 'none';
        siswaMingguSection.style.display = 'none';

        kelasFilterSection.style.display = 'none'; // Default hidden, shown based on type

        resetFormInputs(); // Always reset when export type changes

        switch (selectedType) {
            case 'per_hari':
                globalDateFilters.style.display = 'block';
                tanggalSection.style.display = 'block';
                kelasFilterSection.style.display = 'block';
                break;
            case 'per_minggu':
                globalDateFilters.style.display = 'block';
                mingguSection.style.display = 'block';
                kelasFilterSection.style.display = 'block';
                break;
            case 'per_bulan':
                globalDateFilters.style.display = 'block';
                bulanSection.style.display = 'block';
                tahunSection.style.display = 'block';
                kelasFilterSection.style.display = 'block';
                break;
            case 'per_tahun':
                globalDateFilters.style.display = 'block';
                tahunSection.style.display = 'block';
                kelasFilterSection.style.display = 'block';
                break;
            case 'per_siswa':
                siswaSection.style.display = 'block';
                // Initially show monthly for student
                siswaBulanSection.style.display = 'block';
                siswaTahunSection.style.display = 'block';
                // kelasFilterSection is hidden for per_siswa
                break;
            case 'semua':
                kelasFilterSection.style.display = 'block';
                break;
        }
        // Call nested toggle for siswa_periode if per_siswa is active
        if (selectedType === 'per_siswa') {
            toggleSiswaPeriodSections();
        }
    }

    function toggleSiswaPeriodSections() {
        const selectedSiswaPeriod = siswaPeriodeSelect.value;
        siswaBulanSection.style.display = 'none';
        siswaTahunSection.style.display = 'none';
        siswaMingguSection.style.display = 'none'; // Hide weekly too

        if (selectedSiswaPeriod === 'bulanan') {
            siswaBulanSection.style.display = 'block';
            siswaTahunSection.style.display = 'block';
        } else if (selectedSiswaPeriod === 'tahunan') {
            siswaTahunSection.style.display = 'block';
        } else if (selectedSiswaPeriod === 'mingguan') { // New case for weekly
            siswaMingguSection.style.display = 'block';
        }
    }

    idSiswaSelect.addEventListener('change', function() {
        const selectedOption = idSiswaSelect.options[idSiswaSelect.selectedIndex];
        if (selectedOption.value) {
            const studentName = selectedOption.dataset.nama;
            const studentClass = selectedOption.dataset.kelas;
            siswaInfoDiv.innerHTML = `<strong>Nama:</strong> ${studentName}<br><strong>Kelas:</strong> ${studentClass}`;
        } else {
            siswaInfoDiv.innerHTML = '';
        }
    });

    siswaPeriodeSelect.addEventListener('change', toggleSiswaPeriodSections);
    exportTypeSelect.addEventListener('change', toggleSections);

    // Initial call to set up the form state
    toggleSections();
});
</script>

<?php include('../layout/foother.php'); ?>