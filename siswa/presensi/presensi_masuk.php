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
// These are primarily for the *initial form display*
// and will be re-read from POST when the form is submitted with a photo.
$latitude_pegawai = $_POST['latitude_pegawai'] ?? 0;
$longitude_pegawai = $_POST['longitude_pegawai'] ?? 0;
// Fetch office location and radius from the database
// Assuming 'lokasi_presensi' is a table or a setting in 'config.php'
// For this example, I'll hardcode some values as you haven't provided the fetching logic for them.
// You should replace these with actual values from your database.
$query_lokasi = "SELECT latitude, longitude, radius, nama_lokasi FROM lokasi_presensi WHERE id = 1 LIMIT 1"; // Adjust ID as needed
$result_lokasi = mysqli_query($conection, $query_lokasi);
$data_lokasi = mysqli_fetch_assoc($result_lokasi);

$latitude_kantor = $data_lokasi['latitude'] ?? -6.2088; // Default to Jakarta if not found
$longitude_kantor = $data_lokasi['longitude'] ?? 106.8456; // Default to Jakarta if not found
$radius = $data_lokasi['radius'] ?? 50; // Default radius in meters
$nama_lokasi = mysqli_real_escape_string($conection, $data_lokasi['nama_lokasi'] ?? 'Lokasi Default');

$tanggal_masuk = date('Y-m-d');
$jam_masuk = date('H:i:s');
$status_masuk = 'Hadir'; // Default status for entry


// Handle the initial page load to get and display user's current location on the map
// This runs when the page is first accessed (GET request or initial POST with no photo)
if ($_SERVER['REQUEST_METHOD'] === 'GET' || ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['photo']))) {
    include('../layout/header.php'); // Include header here for initial display
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <div class="page-body">
    <div class="container-xl">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Peta Lokasi Anda</h3>
                    </div>
                    <div class="card-body">
                        <div id="map-container" style="width: 100%; height: 400px; border:0;">
                            <p class="text-center text-muted">Memuat peta lokasi...</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-header">
                        <h3 class="card-title">Ambil Foto Presensi</h3>
                    </div>
                    <div class="card-body">
                        <div id="my_camera" class="mb-3 mx-auto" style="width: 320px; height: 240px; overflow: hidden; border: 1px solid #ccc;"></div>
                        <div id="my_result" class="mb-3 mx-auto" style="max-width: 320px;"></div>
                        <div id="tanggal-dan-jam" class="mt-2 fw-bold text-muted"></div>
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
                            <button class="btn btn-primary mt-3" type="button" id="ambil-foto">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 7h1a2 2 0 0 0 2 -2a1 1 0 0 1 1 -1h3a1 1 0 0 1 1 1a2 2 0 0 0 2 2h1a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-9a2 2 0 0 1 2 -2" /><path d="M12 11m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /></svg>
                                Ambil Foto & Presensi
                            </button>
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
        width: 320, // Adjusted width for better performance/storage
        height: 240, // Adjusted height
        image_format: 'jpeg', // Request JPEG from Webcam.js
        jpeg_quality: 90, // Quality can be adjusted, higher is better but larger file
        flip_horiz: true // Often useful for selfies
    });
    Webcam.attach('#my_camera');

    function updateMap(lat, long) {
        document.getElementById('map-container').innerHTML = `<iframe src="https://maps.google.com/maps?q=${lat},${long}&hl=id&z=14&output=embed" width="100%" height="400" style="border:0;" allowfullscreen></iframe>`;
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
        }, {
            enableHighAccuracy: true,
            timeout: 10000, // 10 seconds
            maximumAge: 0 // No cached position
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
            document.getElementById('my_result').innerHTML = '<img src="' + data_uri + '" class="img-fluid rounded"/>';
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
    $file_foto_base64 = $_POST['photo'];
    $id_siswa = $_SESSION['id'] ?? null;

    // Retrieve and sanitize all necessary data from POST
    $latitude_pegawai = floatval($_POST['latitude_pegawai'] ?? 0);
    $longitude_pegawai = floatval($_POST['longitude_pegawai'] ?? 0);
    $latitude_kantor = floatval($_POST['latitude_kantor'] ?? 0);
    $longitude_kantor = floatval($_POST['longitude_kantor'] ?? 0);
    $radius = floatval($_POST['radius'] ?? 0); // Ensure radius is also float
    $nama_lokasi = mysqli_real_escape_string($conection, $_POST['nama_lokasi'] ?? 'Tidak diketahui');
    $status_masuk = mysqli_real_escape_string($conection, $_POST['status_masuk'] ?? 'Hadir'); // Default to Hadir
    $tanggal_masuk = mysqli_real_escape_string($conection, $_POST['tanggal_masuk_form'] ?? date('Y-m-d'));
    $jam_masuk = mysqli_real_escape_string($conection, $_POST['jam_masuk_form'] ?? date('H:i:s'));


    // Haversine formula for distance calculation (more accurate)
    function getDistanceHaversine($lat1, $lon1, $lat2, $lon2) {
        $R = 6371e3; // metres (Earth's radius)
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

    // Process and save the image
    $img_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $file_foto_base64));

    if ($img_data === false) {
        $_SESSION['gagal'] = "Gagal memproses gambar: Data base64 tidak valid.";
        header("Refresh: 3; URL=../home/home.php");
        exit;
    }

    // Create image from string
    $img = imagecreatefromstring($img_data);

    if ($img === false) {
        $_SESSION['gagal'] = "Gagal membuat gambar dari data. Format gambar mungkin tidak didukung.";
        header("Refresh: 3; URL=../home/home.php");
        exit;
    }

    // Set desired quality (0-100, 100 is best quality)
    $quality = 75; // Adjust this value for desired compression (e.g., 75 is good balance)

    // Ensure 'foto' directory exists
    if (!is_dir('foto')) {
        mkdir('foto', 0777, true);
    }

    $nama_file_jpg = 'foto/masuk_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.jpg'; // Add uniqid for uniqueness
    $file_for_db = basename($nama_file_jpg); // Store only filename in DB

    // Save image as JPG with compression
    $save_success = imagejpeg($img, $nama_file_jpg, $quality);

    // Free up memory
    imagedestroy($img);

    if (!$save_success) {
        $_SESSION['gagal'] = "Gagal menyimpan file foto.";
        header("Refresh: 3; URL=../home/home.php");
        exit;
    }

    if ($id_siswa) {
        $cekQuery = "SELECT * FROM presensi WHERE id_siswa = ? AND tanggal_masuk = ?";
        $stmt_cek = mysqli_prepare($conection, $cekQuery);
        mysqli_stmt_bind_param($stmt_cek, 'ss', $id_siswa, $tanggal_masuk);
        mysqli_stmt_execute($stmt_cek);
        $cekResult = mysqli_stmt_get_result($stmt_cek);

        if (mysqli_num_rows($cekResult) > 0) {
            $_SESSION['gagal'] = "Anda sudah melakukan presensi masuk hari ini.";
            header("Refresh: 3; URL=../home/home.php");
            exit; // Important to exit after header redirect
        } else {
            $query = "INSERT INTO presensi (id_siswa, nama_lokasi, tanggal_masuk, jam_masuk, foto_masuk, latitude, longitude)
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt_insert = mysqli_prepare($conection, $query);
            // Bind parameters, note 'd' for double (float) types
            mysqli_stmt_bind_param($stmt_insert, 'issssid', 
                                    $id_siswa, 
                                    $nama_lokasi, 
                                    $tanggal_masuk, 
                                    $jam_masuk, 
                                    $file_for_db,
                                    $latitude_pegawai,
                                    $longitude_pegawai);
            $result = mysqli_stmt_execute($stmt_insert);

            if ($result) {
                $_SESSION['berhasil'] = "Presensi masuk berhasil.";
                header("Location: terimakasih.php"); // Assuming terimakasih.php handles success message
                exit;
            } else {
                $_SESSION['gagal'] = "Presensi gagal: " . mysqli_error($conection);
            }
        }
        mysqli_stmt_close($stmt_cek); // Close statement for check query
        if (isset($stmt_insert)) {
            mysqli_stmt_close($stmt_insert); // Close statement for insert query
        }
    } else {
        $_SESSION['gagal'] = "ID pengguna tidak ditemukan.";
    }
    // Redirect back to home with message if attendance failed
    header("Refresh: 3; URL=../home/home.php");
    exit;
}
?>