<?php
session_start();
include_once('../../config.php');

date_default_timezone_set("Asia/Jakarta");

if (!isset($_SESSION['login'])) {
    header("location: ../../auth/siswa/login.php?pesan=belum_login");
    exit;
}

// Initialize variables for displaying the form
$nama_lokasi = '';
$latitude_kantor = 0;
$longitude_kantor = 0;
$radius = 0;
$tanggal_keluar_form = date('Y-m-d');
$jam_keluar_form = date('H:i:s'); // Not directly used in processing, but good for display

// Haversine formula for distance calculation (PHP version)
function getDistanceHaversine($lat1, $lon1, $lat2, $lon2) {
    $R = 6371e3; // metres
    $phi1 = deg2rad($lat1);
    $phi2 = deg2rad($lat2);
    $deltaPhi = deg2rad($lat2 - $lat1);
    $deltaLambda = deg2rad($lon2 - $lon1);

    $a = sin($deltaPhi / 2) * sin($deltaPhi / 2) +
         cos($phi1) * cos($phi2) *
         sin($deltaLambda / 2) * sin($deltaLambda / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $R * $c; // in metres
}

// --- Main Logic for Processing Attendance Submission (after photo is taken) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['photo'])) {
    $file_foto = $_POST['photo'];
    $foto = str_replace('data:image/jpeg;base64,', '', $file_foto);
    $foto = str_replace(' ', '+', $foto);
    $data = base64_decode($foto);

    if (!is_dir('foto')) {
        mkdir('foto', 0777, true);
    }

    $tanggal_keluar = date('Y-m-d'); // Current server date
    $jam_keluar = date('H:i:s'); // Current server time
    $nama_file = 'foto/keluar_' . date('Y-m-d_H-i-s') . '.png';
    $file_path_for_db = basename($nama_file); // Just the filename for database

    $id_siswa = $_SESSION['id'] ?? null; // Get student ID from session

    // Retrieve ALL necessary data from the POST request (these came from home.php)
    $nama_lokasi = mysqli_real_escape_string($conection, $_POST['nama_lokasi'] ?? 'Tidak diketahui');
    $latitude_pegawai = floatval($_POST['latitude_pegawai'] ?? 0);
    $longitude_pegawai = floatval($_POST['longitude_pegawai'] ?? 0);
    $latitude_kantor = floatval($_POST['latitude_kantor'] ?? 0);
    $longitude_kantor = floatval($_POST['longitude_kantor'] ?? 0);
    $radius = floatval($_POST['radius']); // Use ?? 0 for safety

    // Calculate distance
    $jarak_meter = getDistanceHaversine($latitude_pegawai, $longitude_pegawai, $latitude_kantor, $longitude_kantor);

    if ($jarak_meter > $radius) {
        $_SESSION['gagal'] = "Anda berada di luar radius lokasi yang ditentukan untuk presensi keluar. Jarak Anda: " . round($jarak_meter) . "m, Radius: " . round($radius) . "m.";
        header("Refresh: 3; URL= ../home/home.php");
        exit;
    }

    if ($id_siswa) {
        // --- START: Checks for Separate Tables ---

        // 1. Check if the student has done presensi masuk today in the 'presensi' table
        $cek_masuk_query = "SELECT id FROM presensi WHERE id_siswa = '$id_siswa' AND tanggal_masuk = '$tanggal_keluar'";
        $cek_masuk_result = mysqli_query($conection, $cek_masuk_query);

        if (mysqli_num_rows($cek_masuk_result) == 0) {
            $_SESSION['gagal'] = "Anda belum melakukan presensi masuk hari ini.";
            header("Refresh: 3; URL=../home/home.php");
            exit;
        }

        // 2. Check if the student has already done presensi keluar today in the 'presensi_out' table
        $cek_keluar_query = "SELECT id FROM presensi_out WHERE id_siswa = '$id_siswa' AND tanggal_keluar = '$tanggal_keluar'";
        $cek_keluar_result = mysqli_query($conection, $cek_keluar_query);

        if (mysqli_num_rows($cek_keluar_result) > 0) {
            $_SESSION['gagal'] = "Anda sudah melakukan presensi keluar hari ini.";
            header("Refresh: 3; URL=../home/home.php");
            exit;
        }
        // --- END: Checks for Separate Tables ---

        // If checks pass, proceed to insert into presensi_out
        file_put_contents($nama_file, $data); // Save the photo to the server

        // Insert attendance record into the 'presensi_out' table (for clock-out)
        $query = "INSERT INTO presensi_out (id_siswa, tanggal_keluar, jam_keluar, foto_keluar)
                      VALUES ('$id_pegawai', '$tanggal_keluar', '$jam_keluar', '$file')";
            $result = mysqli_query($conection, $query);

        if ($result) {
            $_SESSION['berhasil'] = "Presensi keluar berhasil.";
            header("Location: terimakasih_keluar.php"); // Assuming this page exists
            exit;
        } else {
            $_SESSION['gagal'] = "Presensi keluar gagal: " . mysqli_error($conection);
        }
    } else {
        $_SESSION['gagal'] = "ID pengguna tidak ditemukan.";
    }
    // Fallback redirect if any logic above didn't exit
    header("Refresh: 3; URL=../home/home.php");
    exit;
}

// --- Display Form for Capturing Photo (initial GET request or POST without photo) ---
include('../layout/header.php');

// If the page is loaded via GET or a POST without photo (i.e., initial load from home.php)
if ($_SERVER['REQUEST_METHOD'] === 'GET' || ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['photo']))) {
    // Collect the data passed from home.php for initial display
    $nama_lokasi = mysqli_real_escape_string($conection, $_POST['nama_lokasi'] ?? 'Tidak diketahui');
    $latitude_kantor = floatval($_POST['latitude_kantor'] ?? 0);
    $longitude_kantor = floatval($_POST['longitude_kantor'] ?? 0);
    $radius = floatval($_POST['radius'] ?? 0);
    $jam_pulang_kantor_config = $_POST['jam_pulang_kantor'] ?? ''; // Pass the configured jam_pulang
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>

<div class="page-body">
    <div class="container-xl">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body" id="map-container">
                        <p class="text-center text-muted">Memuat peta lokasi...</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <div id="my_camera"></div>
                        <div id="my_result"></div>
                        <div id="tanggal-dan-jam" class="mt-2"></div>
                        <form method="POST" id="form-presensi">
                            <input type="hidden" name="photo" id="photo-input">
                            <input type="hidden" name="nama_lokasi" value="<?= htmlspecialchars($nama_lokasi) ?>">
                            <input type="hidden" name="latitude_kantor" value="<?= $latitude_kantor ?>">
                            <input type="hidden" name="longitude_kantor" value="<?= $longitude_kantor ?>">
                            <input type="hidden" name="radius" value="<?= $radius ?>">
                            <input type="hidden" name="jam_pulang_kantor" value="<?= htmlspecialchars($jam_pulang_kantor_config) ?>">
                            <input type="hidden" name="latitude_pegawai" id="latitude_pegawai_input">
                            <input type="hidden" name="longitude_pegawai" id="longitude_pegawai_input">
                            <input type="hidden" name="tanggal_keluar_form" value="<?= $tanggal_keluar_form ?>">
                            <input type="hidden" name="jam_keluar_form" value="<?= $jam_keluar_form ?>">

                            <button class="btn btn-primary mt-2" type="button" id="ambil-foto">Ambil Foto & Presensi Keluar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function updateTanggalDanJam() {
        const now = new Date();
        const tanggal = now.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
        const jam = now.toLocaleTimeString('id-ID');
        document.getElementById('tanggal-dan-jam').innerText = `${tanggal} - ${jam}`;
    }
    updateTanggalDanJam();
    setInterval(updateTanggalDanJam, 1000);

    Webcam.set({
        width: 354,
        height: 472,
        image_format: 'jpeg',
        jpeg_quality: 90
    });
    Webcam.attach('#my_camera');

    function updateMap(lat, long) {
        document.getElementById('map-container').innerHTML = `<iframe src="http://maps.google.com/maps?q=${lat},${long}&hl=id&z=14&output=embed" width="100%" height="400" style="border:0;" allowfullscreen></iframe>`;
    }

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
            const userLat = pos.coords.latitude;
            const userLong = pos.coords.longitude;
            document.getElementById('latitude_pegawai_input').value = userLat;
            document.getElementById('longitude_pegawai_input').value = userLong;
            updateMap(userLat, userLong);
        }, (error) => {
            let errorMessage = 'Gagal mendapatkan lokasi Anda. Pastikan akses lokasi diizinkan.';
            if (error.code === error.PERMISSION_DENIED) {
                errorMessage = 'Akses lokasi ditolak. Harap izinkan akses lokasi di browser Anda untuk menggunakan presensi.';
            } else if (error.code === error.POSITION_UNAVAILABLE) {
                errorMessage = 'Informasi lokasi tidak tersedia.';
            } else if (error.code === error.TIMEOUT) {
                errorMessage = 'Waktu habis saat mencoba mendapatkan lokasi.';
            }
            document.getElementById('map-container').innerHTML = `<p class="text-center text-danger">${errorMessage}</p>`;
            Swal.fire('Gagal Mengakses Lokasi', errorMessage, 'error');
            document.getElementById('ambil-foto').disabled = true; // Disable button if location not available
        });
    } else {
        document.getElementById('map-container').innerHTML = '<p class="text-center text-danger">Browser Anda tidak mendukung geolokasi.</p>';
        Swal.fire('Browser tidak mendukung', 'Perangkat tidak mendukung geolokasi', 'error');
        document.getElementById('ambil-foto').disabled = true; // Disable button if geolocation not supported
    }

    document.getElementById('ambil-foto').addEventListener('click', function () {
        if (!document.getElementById('latitude_pegawai_input').value || !document.getElementById('longitude_pegawai_input').value) {
            Swal.fire('Lokasi Belum Tersedia', 'Mohon tunggu atau pastikan akses lokasi diizinkan.', 'warning');
            return;
        }

        Webcam.snap(function (data_uri) {
            document.getElementById('my_result').innerHTML = '<img src="' + data_uri + '"/>';
            document.getElementById('photo-input').value = data_uri;
            document.getElementById('form-presensi').submit();
        });
    });
</script>

<?php
} // End of conditional display for form
?>

<?php if (isset($_SESSION['gagal'])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        Swal.fire({
            icon: "error",
            title: "Oops...",
            text: "<?= htmlspecialchars($_SESSION['gagal'], ENT_QUOTES); ?>",
        });
    });
</script>
<?php unset($_SESSION['gagal']); ?>
<?php endif; ?>

<?php include('../layout/foother.php'); ?>