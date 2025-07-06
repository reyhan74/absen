<?php
// admin/pages/wali_murid/dashboard.php

session_start();
if (!isset($_SESSION['login'])) {
    header("location: ../../auth/login.php?pesan=belum_login");
    exit;
} elseif ($_SESSION["role"] != 'wali_murid') { // Pastikan hanya wali yang bisa akses
    header("location:../../auth/login.php?pesan=tolak_akses");
    exit;
}

include('../layout/header.php'); // Path ke header
require_once('../../config.php'); // Path ke config

$wali_murid_id = $_SESSION['id']; // ID wali murid yang sedang login

// Ambil data siswa yang terkait dengan wali murid ini
$query_siswa = "SELECT id, nis, nama, kelas FROM tabel_siswa WHERE wali_murid_id = ?";
$stmt_siswa = $conection->prepare($query_siswa);
$stmt_siswa->bind_param("i", $wali_murid_id);
$stmt_siswa->execute();
$result_siswa = $stmt_siswa->get_result();
$siswa_terkait = [];
if ($result_siswa->num_rows > 0) {
    while ($row_siswa = $result_siswa->fetch_assoc()) {
        $siswa_terkait[] = $row_siswa;
    }
}
$stmt_siswa->close();

// Filter kehadiran (bulan dan tahun)
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

// Array untuk menyimpan data presensi per siswa
$data_presensi_siswa = [];

if (!empty($siswa_terkait)) {
    foreach ($siswa_terkait as $siswa) {
        $siswa_id = $siswa['id'];
        $siswa_nama = $siswa['nama'];
        $siswa_kelas = $siswa['kelas'];

        // Query untuk mengambil presensi siswa per bulan/tahun
        $query_presensi = "
            SELECT 
                p.tanggal_masuk,
                p.jam_masuk,
                p.nama_lokasi,
                p.foto_masuk,
                o.jam_keluar,
                o.foto_keluar
            FROM presensi p
            LEFT JOIN presensi_out o ON p.id_siswa = o.id_siswa 
                AND p.tanggal_masuk = o.tanggal_keluar
            WHERE p.id_siswa = ? 
              AND MONTH(p.tanggal_masuk) = ? 
              AND YEAR(p.tanggal_masuk) = ?
            ORDER BY p.tanggal_masuk ASC
        ";
        $stmt_presensi = $conection->prepare($query_presensi);
        $stmt_presensi->bind_param("iis", $siswa_id, $bulan, $tahun); // 'iis' -> integer, integer, string
        $stmt_presensi->execute();
        $result_presensi = $stmt_presensi->get_result();

        $presensi_bulanan = [];
        if ($result_presensi->num_rows > 0) {
            while ($row_presensi = $result_presensi->fetch_assoc()) {
                $presensi_bulanan[] = $row_presensi;
            }
        }
        $stmt_presensi->close();
        
        $data_presensi_siswa[] = [
            'id' => $siswa_id,
            'nama' => $siswa_nama,
            'kelas' => $siswa_kelas,
            'presensi' => $presensi_bulanan
        ];
    }
}

// Tutup koneksi database jika tidak ada operasi lain setelah ini
// $conection->close(); 

?>

<main class="container py-4">
    <h1 class="mb-4 text-center text-primary">Rekap Kehadiran Putra/Putri Anda</h1>
    <p class="text-center text-muted">Selamat datang, Wali Murid!</p>

    <div class="card p-4 mb-4 shadow-sm">
        <form method="GET" action="">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="bulan" class="form-label">Pilih Bulan:</label>
                    <select class="form-select" id="bulan" name="bulan">
                        <?php 
                        $bulan_nama = [
                            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                        ];
                        foreach ($bulan_nama as $num => $name) {
                            $selected = ($bulan == $num) ? 'selected' : '';
                            echo "<option value='{$num}' {$selected}>{$name}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="tahun" class="form-label">Pilih Tahun:</label>
                    <select class="form-select" id="tahun" name="tahun">
                        <?php 
                        $current_year = date('Y');
                        for ($y = $current_year - 3; $y <= $current_year + 1; $y++) {
                            $selected = ($tahun == $y) ? 'selected' : '';
                            echo "<option value='{$y}' {$selected}>{$y}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">Tampilkan Rekap</button>
                </div>
            </div>
        </form>
    </div>

    <?php if (empty($siswa_terkait)): ?>
        <div class="alert alert-warning text-center" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> Belum ada data putra/putri yang terhubung dengan akun Anda. Silakan hubungi admin sekolah.
        </div>
    <?php else: ?>
        <?php foreach ($data_presensi_siswa as $data): ?>
            <div class="card p-4 mb-4 shadow-sm">
                <h3 class="mb-3 text-success">Siswa: <?php echo htmlspecialchars($data['nama']); ?> (NIS: <?php echo htmlspecialchars($data['nis']); ?>) - Kelas: <?php echo htmlspecialchars($data['kelas']); ?></h3>
                <h5 class="mb-3">Rekap Kehadiran Bulan <?php echo $bulan_nama[$bulan]; ?> Tahun <?php echo $tahun; ?></h5>

                <?php if (!empty($data['presensi'])): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-primary">
                                <tr>
                                    <th>No.</th>
                                    <th>Tanggal</th>
                                    <th>Jam Masuk</th>
                                    <th>Lokasi Masuk</th>
                                    <th>Foto Masuk</th>
                                    <th>Jam Keluar</th>
                                    <th>Foto Keluar</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; ?>
                                <?php foreach ($data['presensi'] as $presensi): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($presensi['tanggal_masuk']); ?></td>
                                        <td><?php echo htmlspecialchars($presensi['jam_masuk'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($presensi['nama_lokasi'] ?? '-'); ?></td>
                                        <td>
                                            <?php if (!empty($presensi['foto_masuk'])): ?>
                                                <a href="../../uploads/presensi_masuk/<?php echo htmlspecialchars($presensi['foto_masuk']); ?>" target="_blank" class="btn btn-sm btn-info text-white">Lihat Foto</a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($presensi['jam_keluar'] ?? '-'); ?></td>
                                        <td>
                                            <?php if (!empty($presensi['foto_keluar'])): ?>
                                                <a href="../../uploads/presensi_keluar/<?php echo htmlspecialchars($presensi['foto_keluar']); ?>" target="_blank" class="btn btn-sm btn-info text-white">Lihat Foto</a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            // Contoh sederhana status, Anda bisa kembangkan
                                            if (!empty($presensi['jam_masuk']) && !empty($presensi['jam_keluar'])) {
                                                echo '<span class="badge bg-success">Hadir Penuh</span>';
                                            } elseif (!empty($presensi['jam_masuk'])) {
                                                echo '<span class="badge bg-warning text-dark">Hanya Masuk</span>';
                                            } else {
                                                echo '<span class="badge bg-danger">Tidak Hadir</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center" role="alert">
                        <i class="fas fa-info-circle me-2"></i> Belum ada data presensi untuk siswa ini pada bulan <?php echo $bulan_nama[$bulan]; ?> tahun <?php echo $tahun; ?>.
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
</main>

<?php include('../layout/foother.php'); ?>