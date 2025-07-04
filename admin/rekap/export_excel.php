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

// Get list of distinct classes from the database for the checkboxes
$query_kelas = "SELECT DISTINCT kelas FROM siswa WHERE kelas IS NOT NULL AND kelas != '' ORDER BY kelas ASC";
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
                    <option value="per_hari">Per Hari</option>
                    <option value="per_bulan">Per Bulan</option>
                    <option value="per_tahun">Per Tahun</option>
                    <option value="per_siswa">Per Siswa</option>
                    <option value="semua">Semua Data</option>
                </select>
            </div>

            <div id="tanggal_section" class="mb-3" style="display: none;">
                <label for="tanggal" class="form-label">Pilih Tanggal:</label>
                <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?php echo date('Y-m-d'); ?>">
            </div>

            <div id="periode_siswa_section" class="mb-3" style="display: none;">
                <label for="periode_siswa" class="form-label">Periode Laporan Siswa:</label>
                <select class="form-select" id="periode_siswa" name="periode_siswa">
                    <option value="bulanan">Bulanan</option>
                    <option value="mingguan">Mingguan</option>
                    <option value="tahunan">Tahunan</option>
                    <option value="semua_waktu">Semua Waktu</option>
                </select>
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

            <div id="mingguan_section" class="mb-3" style="display: none;">
                <label for="minggu" class="form-label">Pilih Minggu (dalam Tahun):</label>
                <input type="week" class="form-control" id="minggu" name="minggu" value="<?php echo date('Y-\WW'); ?>">
            </div>


            <div id="siswa_search_section" class="mb-3" style="display: none;">
                <label for="search_siswa_input" class="form-label">Cari & Pilih Siswa (Ketik Nama/NIS):</label>
                <input type="text" class="form-control mb-2" id="search_siswa_input" placeholder="Cari Siswa...">
                <select class="form-select" id="id_siswa" name="id_siswa" size="5" required>
                    </select>
                <small class="form-text text-muted">Pilih siswa dari daftar setelah mengetik.</small>
            </div>


            <div id="kelas_filter_section" class="mb-3" style="display: none;">
                <label class="form-label">Filter Berdasarkan Kelas:</label>
                <div class="form-check-group">
                    <?php foreach ($daftar_kelas as $kelas_nama): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="kelas_filter[]" value="<?php echo htmlspecialchars($kelas_nama); ?>" id="kelas_<?php echo htmlspecialchars($kelas_nama); ?>">
                            <label class="form-check-label" for="kelas_<?php echo htmlspecialchars($kelas_nama); ?>">
                                <?php echo htmlspecialchars($kelas_nama); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="form-text">Pilih satu atau lebih kelas. Jika tidak ada yang dipilih, semua kelas akan disertakan (kecuali untuk ekspor 'Per Siswa').</div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="fas fa-file-excel me-2"></i> Ekspor ke Excel</button>
        </form>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const exportTypeSelect = document.getElementById('export_type');
    const tanggalSection = document.getElementById('tanggal_section');
    const periodeSiswaSection = document.getElementById('periode_siswa_section');
    const periodeSiswaSelect = document.getElementById('periode_siswa');
    const bulanSection = document.getElementById('bulan_section');
    const tahunSection = document.getElementById('tahun_section');
    const mingguanSection = document.getElementById('mingguan_section');
    const siswaSearchSection = document.getElementById('siswa_search_section');
    const searchSiswaInput = document.getElementById('search_siswa_input');
    const idSiswaSelect = document.getElementById('id_siswa');
    const kelasFilterSection = document.getElementById('kelas_filter_section');
    const kelasCheckboxes = document.querySelectorAll('input[name="kelas_filter[]"]');

    function resetFormFields() {
        document.getElementById('tanggal').value = '<?php echo date('Y-m-d'); ?>';
        document.getElementById('bulan').value = '<?php echo date('m'); ?>';
        document.getElementById('tahun').value = '<?php echo date('Y'); ?>';
        document.getElementById('minggu').value = '<?php echo date('Y-\WW'); ?>';
        idSiswaSelect.innerHTML = ''; // Clear student options
        searchSiswaInput.value = ''; // Clear search input

        // Uncheck all class checkboxes
        kelasCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
    }

    function toggleSections() {
        const selectedType = exportTypeSelect.value;

        // Hide all sections initially
        tanggalSection.style.display = 'none';
        periodeSiswaSection.style.display = 'none';
        bulanSection.style.display = 'none';
        tahunSection.style.display = 'none';
        mingguanSection.style.display = 'none';
        siswaSearchSection.style.display = 'none';
        kelasFilterSection.style.display = 'none'; // Default to hidden, show based on type

        resetFormFields(); // Reset fields when type changes

        switch (selectedType) {
            case 'per_hari':
                tanggalSection.style.display = 'block';
                kelasFilterSection.style.display = 'block';
                break;
            case 'per_bulan':
                bulanSection.style.display = 'block';
                tahunSection.style.display = 'block';
                kelasFilterSection.style.display = 'block';
                break;
            case 'per_tahun':
                tahunSection.style.display = 'block';
                kelasFilterSection.style.display = 'block';
                break;
            case 'per_siswa':
                siswaSearchSection.style.display = 'block';
                periodeSiswaSection.style.display = 'block';
                // Further toggling based on periodeSiswaSelect will be handled by its change event
                break;
            case 'semua':
                kelasFilterSection.style.display = 'block';
                break;
        }
        togglePeriodeSiswaSections(); // Call this to set initial state for per_siswa
    }

    function togglePeriodeSiswaSections() {
        const selectedPeriodeSiswa = periodeSiswaSelect.value;
        const exportType = exportTypeSelect.value;

        // Only toggle if exportType is 'per_siswa'
        if (exportType === 'per_siswa') {
            bulanSection.style.display = 'none';
            tahunSection.style.display = 'none';
            mingguanSection.style.display = 'none';

            switch (selectedPeriodeSiswa) {
                case 'bulanan':
                    bulanSection.style.display = 'block';
                    tahunSection.style.display = 'block';
                    break;
                case 'mingguan':
                    mingguanSection.style.display = 'block';
                    tahunSection.style.display = 'block'; // Week is also within a year
                    break;
                case 'tahunan':
                    tahunSection.style.display = 'block';
                    break;
                case 'semua_waktu':
                    // No specific date/time sections needed
                    break;
            }
        }
    }

    // Event Listeners
    exportTypeSelect.addEventListener('change', toggleSections);
    periodeSiswaSelect.addEventListener('change', togglePeriodeSiswaSections);

    // Initial call to set up the form state on page load
    toggleSections();

    // AJAX for Student Search
    let searchTimeout;
    searchSiswaInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = this.value;

        searchTimeout = setTimeout(() => {
            if (searchTerm.length >= 2) { // Only search if at least 2 characters are typed
                fetch('get_students.php?search=' + encodeURIComponent(searchTerm))
                    .then(response => response.json())
                    .then(data => {
                        idSiswaSelect.innerHTML = ''; // Clear previous options
                        if (data.length > 0) {
                            data.forEach(siswa => {
                                const option = document.createElement('option');
                                option.value = siswa.id;
                                option.textContent = `${siswa.nis} - ${siswa.nama} (${siswa.kelas})`;
                                idSiswaSelect.appendChild(option);
                            });
                        } else {
                            const option = document.createElement('option');
                            option.value = '';
                            option.textContent = 'Tidak ada siswa ditemukan';
                            idSiswaSelect.appendChild(option);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching students:', error);
                        idSiswaSelect.innerHTML = '<option value="">Error memuat siswa</option>';
                    });
            } else if (searchTerm.length === 0) {
                idSiswaSelect.innerHTML = ''; // Clear if input is empty
            }
        }, 300); // Debounce search to prevent too many requests
    });

    // Ensure at least one class checkbox is checked if the section is visible
    // and the export type is not 'semua' or 'per_siswa'
    document.querySelector('form').addEventListener('submit', function(event) {
        const selectedType = exportTypeSelect.value;
        if (kelasFilterSection.style.display === 'block' && selectedType !== 'semua') {
            let oneChecked = false;
            kelasCheckboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    oneChecked = true;
                }
            });
            if (!oneChecked) {
                // If no checkboxes are selected, assume all classes
                // The backend process_export.php will handle this by checking if kelas_filter is an empty array
                // For now, we'll allow it to submit and let the backend decide.
                // console.warn("Tidak ada kelas yang dipilih. Akan mengekspor semua kelas.");
            }
        }

        // For 'per_siswa' type, ensure a student is selected
        if (selectedType === 'per_siswa' && idSiswaSelect.value === '') {
            alert('Silakan pilih siswa terlebih dahulu.');
            event.preventDefault(); // Prevent form submission
        }
    });

});
</script>

<?php include('../layout/foother.php'); ?>