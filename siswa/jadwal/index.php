<?php
session_start();
if (!isset($_SESSION['login']) ) { // Pastikan role juga sesuai jika ada
    header("location: ../../auth/siswa/login.php?pesan=belum_login");
    exit;
}

include('../layout/header.php');
require_once('../../config.php'); // Mengandung $conection untuk koneksi database

// 1. Ambil ID siswa dari sesi
$siswa_id = $_SESSION['id']; 

// 2. Ambil data kelas siswa dari tabel_siswa
$siswa_kelas = '';
$siswa_nama = '';
$sql_siswa = "SELECT nama, kelas FROM siswa WHERE id = ?";
$stmt_siswa = $conection->prepare($sql_siswa);
$stmt_siswa->bind_param("i", $siswa_id);
$stmt_siswa->execute();
$result_siswa = $stmt_siswa->get_result();

if ($result_siswa->num_rows > 0) {
    $row_siswa = $result_siswa->fetch_assoc();
    $siswa_nama = $row_siswa['nama'];
    $siswa_kelas = $row_siswa['kelas']; // Ini adalah nilai VARCHAR dari kolom 'kelas'
}
$stmt_siswa->close();

// 3. Ambil data jadwal berdasarkan kelas siswa dan tahun ajaran saat ini
$current_year = date('Y');
$next_year = date('Y') + 1;
$tahun_ajaran_sekarang = $current_year . '/' . $next_year; // Contoh: "2024/2025"

$jadwal_ditemukan = null;
if (!empty($siswa_kelas)) {
    $sql_jadwal = "SELECT nama_kelas, tahun_ajaran, foto_jadwal, keterangan FROM kelas_jadwal WHERE nama_kelas = ?";
    $stmt_jadwal = $conection->prepare($sql_jadwal);
    $stmt_jadwal->bind_param("ss", $siswa_kelas);
    $stmt_jadwal->execute();
    $result_jadwal = $stmt_jadwal->get_result();

    if ($result_jadwal->num_rows > 0) {
        $jadwal_ditemukan = $result_jadwal->fetch_assoc();
    }
    $stmt_jadwal->close();
}

// Tidak perlu menutup koneksi di sini, biarkan header/footer yang handle jika mereka juga perlu koneksi,
// atau tutup di akhir footer.php.
// $conection->close(); 
?>

<main class="container container-main">
    <h1 class="text-center mb-4 text-primary">Jadwal Pelajaran Anda</h1>
    <p class="text-center text-muted">Selamat datang, <?php echo htmlspecialchars($siswa_nama); ?> dari Kelas <?php echo htmlspecialchars($siswa_kelas); ?>!</p>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card card-jadwal">
                <?php if ($jadwal_ditemukan): ?>
                    <h2 class="text-center mb-4 text-primary">Jadwal Kelas <?php echo htmlspecialchars($jadwal_ditemukan['nama_kelas']); ?></h2>
                    <p class="text-center text-muted">Tahun Ajaran: <?php echo htmlspecialchars($jadwal_ditemukan['tahun_ajaran']); ?></p>
                    
                    <?php 
                    // Path relatif dari lokasi file ini (misal: pages/siswa/jadwal_saya.php)
                    // ke folder uploads/jadwal/ yang berada di root project
                    $image_base_path = '../../uploads/jadwal/'; 
                    ?>

                    <?php if (!empty($jadwal_ditemukan['foto_jadwal'])): ?>
                        <div class="text-center mb-4">
                            <img src="<?php echo $image_base_path . htmlspecialchars($jadwal_ditemukan['foto_jadwal']); ?>" class="img-fluid img-jadwal" alt="Jadwal Pelajaran Kelas <?php echo htmlspecialchars($jadwal_ditemukan['nama_kelas']); ?>">
                        </div>
                        <div class="alert alert-info text-center info-alert" role="alert">
                            <i class="fas fa-info-circle me-2"></i> Jadwal dapat berubah sewaktu-waktu. Mohon periksa pengumuman terbaru.
                        </div>
                        <div class="d-grid gap-2 col-6 mx-auto mt-4">
                            <a href="<?php echo $image_base_path . htmlspecialchars($jadwal_ditemukan['foto_jadwal']); ?>" download="<?php echo htmlspecialchars('Jadwal_' . $jadwal_ditemukan['nama_kelas'] . '_' . $jadwal_ditemukan['tahun_ajaran'] . '.jpg'); ?>" class="btn btn-success btn-lg">
                                <i class="fas fa-download me-2"></i> Unduh Jadwal
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning text-center" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i> Foto jadwal belum tersedia untuk kelas Anda.
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($jadwal_ditemukan['keterangan'])): ?>
                        <div class="alert alert-secondary info-alert mt-3" role="alert">
                            <i class="fas fa-sticky-note me-2"></i> Catatan Admin: <?php echo htmlspecialchars($jadwal_ditemukan['keterangan']); ?>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="alert alert-danger text-center" role="alert">
                        <i class="fas fa-times-circle me-2"></i> Maaf, jadwal pelajaran untuk kelas Anda (<?php echo htmlspecialchars($siswa_kelas); ?>) pada tahun ajaran ini belum ditemukan. Silakan hubungi admin sekolah.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include('../layout/foother.php'); ?>