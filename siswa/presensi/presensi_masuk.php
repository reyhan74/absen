<?php
session_start();
// Ensure consistent login redirection
if (!isset($_SESSION['login'])) {
    header("location: ../../auth/siswa/login.php?pesan=belum_login"); // Consistent path
    exit;
}

include_once('../../config.php');

// Set default timezone once at the top
date_default_timezone_set("Asia/Jakarta");

// Initialize variables with default or empty values
$latitude_pegawai = $_POST['latitude_pegawai'] ?? 0;
$longitude_pegawai = $_POST['longitude_pegawai'] ?? 0;
$latitude_kantor = $_POST['latitude_kantor'] ?? 0;
$longitude_kantor = $_POST['longitude_kantor'] ?? 0;
$radius = $_POST['radius'] ?? 0;
$nama_lokasi = mysqli_real_escape_string($conection, $_POST['nama_lokasi'] ?? 'Tidak diketahui');
$tanggal_masuk = $_POST['tanggal_masuk_form'] ?? date('Y-m-d'); // Use renamed input
$jam_masuk = $_POST['jam_masuk_form'] ?? date('H:i:s'); // Use renamed input
$status_masuk = mysqli_real_escape_string($conection, $_POST['status_masuk'] ?? '');


// Handle the initial page load to get and display user's current location on the map
// This runs when the page is first accessed (GET request or initial POST with no photo)
if ($_SERVER['REQUEST_METHOD'] === 'GET' || ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['photo']))) {
    include('../layout/header.php'); // Include header here for initial display
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>

<div class="page-body">
    <div class="container-xl">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div id="map-container" style="width: 100%; height: 400px; border:0;">
                            <p class="text-center text-muted">Memuat peta lokasi...</p>
                        </div>
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
                            <input type="hidden" name="latitude_pegawai" id="latitude_pegawai_input">
                            <input type="hidden" name="longitude_pegawai" id="longitude_pegawai_input">
                            <input type="hidden" name="latitude_kantor" value="<?= $latitude_kantor ?>">
                            <input type="hidden" name="longitude_kantor" value="<?= $longitude_kantor ?>">
                            <input type="hidden" name="radius" value="<?= $radius ?>">
                            <input type="hidden" name="tanggal_masuk_form" value="<?= $tanggal_masuk ?>">
                            <input type="hidden" name="jam_masuk_form" value="<?= $jam_masuk ?>">
                            <input type="hidden" name="status_masuk" value="<?= $status_masuk ?>">
                            <button class="btn btn-primary mt-2" type="button" id="ambil-foto">Ambil Foto & Presensi</button>
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

    // Function to update the map
    function updateMap(lat, long) {
        document.getElementById('map-container').innerHTML = `<iframe src="https://maps.google.com/maps?q=${lat},${long}&hl=id&z=14&output=embed" width="100%" height="400" style="border:0;" allowfullscreen></iframe>`;
    }

    // Get user's location and update map/hidden inputs
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
        // Ensure user coordinates are available before snapping
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
    include('../layout/foother.php');
    exit; // Stop execution after displaying the form
}

// Only proceed with attendance logic if photo data is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['photo'])) {
    $file_foto = $_POST['photo'];
    $foto = str_replace('data:image/jpeg;base64,', '', $file_foto);
    $foto = str_replace(' ', '+', $foto);
    $data = base64_decode($foto);

    if (!is_dir('foto')) {
        mkdir('foto', 0777, true);
    }

    $nama_file = 'foto/masuk_' . date('Y-m-d_H-i-s') . '.png';
    $file = basename($nama_file);
    $id_siswa = $_SESSION['id'] ?? null;

    // Retrieve and sanitize all necessary data from POST
    $latitude_pegawai = floatval($_POST['latitude_pegawai'] ?? 0);
    $longitude_pegawai = floatval($_POST['longitude_pegawai'] ?? 0);
    $latitude_kantor = floatval($_POST['latitude_kantor'] ?? 0);
    $longitude_kantor = floatval($_POST['longitude_kantor'] ?? 0);
    $radius = floatval($_POST['radius'] ?? 0);
    $nama_lokasi = mysqli_real_escape_string($conection, $_POST['nama_lokasi'] ?? 'Tidak diketahui');
    $status_masuk = mysqli_real_escape_string($conection, $_POST['status_masuk'] ?? '');
    $tanggal_masuk = mysqli_real_escape_string($conection, $_POST['tanggal_masuk_form'] ?? date('Y-m-d'));
    $jam_masuk = mysqli_real_escape_string($conection, $_POST['jam_masuk_form'] ?? date('H:i:s'));


    // Haversine formula for distance calculation (more accurate)
    // Converted from JS to PHP. This is more robust.
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

    $jarak_meter = getDistanceHaversine($latitude_pegawai, $longitude_pegawai, $latitude_kantor, $longitude_kantor);

    if ($jarak_meter > $radius) {
        $_SESSION['gagal'] = "Anda berada di luar radius lokasi yang ditentukan. Jarak Anda: " . round($jarak_meter) . "m, Radius: " . round($radius) . "m.";
        header("Refresh: 3; URL= ../home/home.php");
        exit;
    }

    if ($id_siswa) {
        $cekQuery = "SELECT * FROM presensi WHERE id_siswa = '$id_siswa' AND tanggal_masuk = '$tanggal_masuk'";
        $cekResult = mysqli_query($conection, $cekQuery);

        if (mysqli_num_rows($cekResult) > 0) {
            $_SESSION['gagal'] = "Anda sudah melakukan presensi masuk hari ini.";
            header("Refresh: 3; URL=../home/home.php");
            exit; // Important to exit after header redirect
        } else {
            file_put_contents($nama_file, $data);
            $query = "INSERT INTO presensi (id_siswa, nama_lokasi, tanggal_masuk, jam_masuk, foto_masuk)
                      VALUES ('$id_siswa', '$nama_lokasi', '$tanggal_masuk', '$jam_masuk', '$file')";
            $result = mysqli_query($conection, $query);

            if ($result) {
                $_SESSION['berhasil'] = "Presensi masuk berhasil.";
                header("Location: terimakasih.php"); // Assuming terimakasih.php handles success message
                exit;
            } else {
                $_SESSION['gagal'] = "Presensi gagal: " . mysqli_error($conection);
            }
        }
    } else {
        $_SESSION['gagal'] = "ID pengguna tidak ditemukan.";
    }
    // Redirect back to home with message if attendance failed
    header("Refresh: 3; URL=../home/home.php");
    exit;
}
?>