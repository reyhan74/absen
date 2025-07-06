<?php
session_start();

// Ensure consistent login redirection
// If the 'login' session variable is not set, redirect to the login page.
if (!isset($_SESSION['login'])) {
    header("location: ../../auth/siswa/login.php?pesan=belum_login"); // Consistent path to login
    exit; // Stop further script execution after redirect
}

// Include your database configuration file.
include_once('../../config.php');

// Set the default timezone for accurate date/time operations.
date_default_timezone_set("Asia/Jakarta");

// Initialize variables from POST data or set default values.
// The null coalescing operator (??) provides a concise way to do this.
$latitude_pegawai = $_POST['latitude_pegawai'] ?? 0;
$longitude_pegawai = $_POST['longitude_pegawai'] ?? 0;
$latitude_kantor = $_POST['latitude_kantor'] ?? 0;
$longitude_kantor = $_POST['longitude_kantor'] ?? 0;
$radius = $_POST['radius'] ?? 0;
// Sanitize string inputs using mysqli_real_escape_string for database safety.
$nama_lokasi = mysqli_real_escape_string($conection, $_POST['nama_lokasi'] ?? 'Tidak diketahui');
$tanggal_masuk = $_POST['tanggal_masuk_form'] ?? date('Y-m-d'); // Use renamed input for date
$jam_masuk = $_POST['jam_masuk_form'] ?? date('H:i:s');     // Use renamed input for time
$status_masuk = mysqli_real_escape_string($conection, $_POST['status_masuk'] ?? '');


// This block handles the initial page load (GET request) or a POST request without photo data.
// Its purpose is to display the attendance form, map, and webcam feed.
if ($_SERVER['REQUEST_METHOD'] === 'GET' || ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['photo']))) {
    include('../layout/header.php'); // Include the common header for the page display
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
                            <input type="hidden" name="latitude_kantor" value="<?= htmlspecialchars($latitude_kantor) ?>">
                            <input type="hidden" name="longitude_kantor" value="<?= htmlspecialchars($longitude_kantor) ?>">
                            <input type="hidden" name="radius" value="<?= htmlspecialchars($radius) ?>">
                            <input type="hidden" name="tanggal_masuk_form" value="<?= htmlspecialchars($tanggal_masuk) ?>">
                            <input type="hidden" name="jam_masuk_form" value="<?= htmlspecialchars($jam_masuk) ?>">
                            <input type="hidden" name="status_masuk" value="<?= htmlspecialchars($status_masuk) ?>">
                            <button class="btn btn-primary mt-2" type="button" id="ambil-foto">Ambil Foto & Presensi</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Function to update and display the current date and time every second
    function updateTanggalDanJam() {
        const now = new Date();
        const tanggal = now.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
        const jam = now.toLocaleTimeString('id-ID');
        document.getElementById('tanggal-dan-jam').innerText = `${tanggal} - ${jam}`;
    }

    updateTanggalDanJam(); // Call once immediately to display on page load
    setInterval(updateTanggalDanJam, 1000); // Update every 1 second

    // Configure Webcam.js settings
    Webcam.set({
        width: 354,            // Display width
        height: 472,           // Display height
        image_format: 'jpeg',  // Image format for capture (JPEG for efficiency)
        jpeg_quality: 90       // JPEG quality (0-100, 90 is good balance)
    });
    // Attach webcam to the 'my_camera' div
    Webcam.attach('#my_camera');

    // Function to update the Google Maps iframe with given coordinates
    function updateMap(lat, long) {
        // The URL for the Google Maps embed. Note: '0{lat}' in your original code
        // might be a typo; it's corrected here to just use the `lat` variable.
        // For production, consider using Google Maps API with an API key for more robust embeds.
        document.getElementById('map-container').innerHTML = `<iframe src="https://maps.google.com/maps?q=${lat},${long}&hl=id&z=14&output=embed" width="100%" height="400" style="border:0;" allowfullscreen></iframe>`;
    }

    // Attempt to get the user's current geolocation
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
            // Success callback: Get user's latitude and longitude
            const userLat = pos.coords.latitude;
            const userLong = pos.coords.longitude;
            // Populate hidden input fields with current coordinates
            document.getElementById('latitude_pegawai_input').value = userLat;
            document.getElementById('longitude_pegawai_input').value = userLong;
            // Update the map display
            updateMap(userLat, userLong);
        }, (error) => {
            // Error callback: Handle various geolocation errors
            let errorMessage = 'Gagal mendapatkan lokasi Anda. Pastikan akses lokasi diizinkan.';
            if (error.code === error.PERMISSION_DENIED) {
                errorMessage = 'Akses lokasi ditolak. Harap izinkan akses lokasi di browser Anda untuk menggunakan presensi.';
            } else if (error.code === error.POSITION_UNAVAILABLE) {
                errorMessage = 'Informasi lokasi tidak tersedia.';
            } else if (error.code === error.TIMEOUT) {
                errorMessage = 'Waktu habis saat mencoba mendapatkan lokasi.';
            }
            // Display error message in the map container and using SweetAlert
            document.getElementById('map-container').innerHTML = `<p class="text-center text-danger">${errorMessage}</p>`;
            Swal.fire('Gagal Mengakses Lokasi', errorMessage, 'error');
            // Disable the "Ambil Foto & Presensi" button if location cannot be obtained
            document.getElementById('ambil-foto').disabled = true;
        });
    } else {
        // Browser does not support geolocation API
        document.getElementById('map-container').innerHTML = '<p class="text-center text-danger">Browser Anda tidak mendukung geolokasi.</p>';
        Swal.fire('Browser tidak mendukung', 'Perangkat tidak mendukung geolokasi', 'error');
        document.getElementById('ambil-foto').disabled = true; // Disable button
    }

    // Event listener for the "Ambil Foto & Presensi" button click
    document.getElementById('ambil-foto').addEventListener('click', function () {
        // Prevent photo capture if location data isn't available yet
        if (!document.getElementById('latitude_pegawai_input').value || !document.getElementById('longitude_pegawai_input').value) {
            Swal.fire('Lokasi Belum Tersedia', 'Mohon tunggu atau pastikan akses lokasi diizinkan.', 'warning');
            return; // Stop function execution
        }

        // Take a snapshot using Webcam.js
        Webcam.snap(function (data_uri) {
            // Display the captured image preview
            document.getElementById('my_result').innerHTML = '<img src="' + data_uri + '"/>';
            // Set the captured photo data (base64) to the hidden input field
            document.getElementById('photo-input').value = data_uri;
            // Submit the form to the server
            document.getElementById('form-presensi').submit();
        });
    });
</script>

<?php
    include('../layout/foother.php'); // Include the common footer
    exit; // Stop script execution after displaying the form
}

// --- Attendance Logic (This block executes ONLY when photo data is submitted via POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['photo'])) {
    $file_foto = $_POST['photo'];
    // Clean the base64 string: remove data URI prefix and replace spaces with plus signs
    $foto = str_replace('data:image/jpeg;base64,', '', $file_foto);
    $foto = str_replace(' ', '+', $foto);
    $data = base64_decode($foto); // Decode the base64 image data

    // Create the 'foto' directory if it doesn't exist.
    // Permissions '0777' are very permissive; for production, consider '0755' or '0775'.
    if (!is_dir('foto')) {
        mkdir('foto', 0777, true);
    }

    // Generate a unique file name for the captured photo with a .jpeg extension.
    $nama_file = 'foto/masuk_' . date('Y-m-d_H-i-s') . '.jpeg';
    $file = basename($nama_file); // Get just the filename (e.g., 'masuk_2025-07-06_11-30-00.jpeg')

    $id_siswa = $_SESSION['id'] ?? null; // Get the student ID from the session.

    // Retrieve and sanitize all necessary data from POST for database insertion.
    // Use floatval() for numeric values to ensure correct type.
    $latitude_pegawai = floatval($_POST['latitude_pegawai'] ?? 0);
    $longitude_pegawai = floatval($_POST['longitude_pegawai'] ?? 0);
    $latitude_kantor = floatval($_POST['latitude_kantor'] ?? 0);
    $longitude_kantor = floatval($_POST['longitude_kantor'] ?? 0);
    $radius = floatval($_POST['radius'] ?? 0);
    $nama_lokasi = mysqli_real_escape_string($conection, $_POST['nama_lokasi'] ?? 'Tidak diketahui');
    $status_masuk = mysqli_real_escape_string($conection, $_POST['status_masuk'] ?? '');
    $tanggal_masuk = mysqli_real_escape_string($conection, $_POST['tanggal_masuk_form'] ?? date('Y-m-d'));
    $jam_masuk = mysqli_real_escape_string($conection, $_POST['jam_masuk_form'] ?? date('H:i:s'));


    // Haversine formula for calculating the distance between two sets of lat/lon coordinates.
    // This is more accurate than simple Euclidean distance for geographical points.
    function getDistanceHaversine($lat1, $lon1, $lat2, $lon2) {
        $R = 6371e3; // Earth's radius in meters
        $phi1 = deg2rad($lat1); // Convert latitude 1 to radians
        $phi2 = deg2rad($lat2); // Convert latitude 2 to radians
        $deltaPhi = deg2rad($lat2 - $lat1); // Difference in latitudes in radians
        $deltaLambda = deg2rad($lon2 - $lon1); // Difference in longitudes in radians

        // Haversine formula calculation
        $a = sin($deltaPhi / 2) * sin($deltaPhi / 2) +
             cos($phi1) * cos($phi2) *
             sin($deltaLambda / 2) * sin($deltaLambda / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $R * $c; // Distance in meters
    }

    // Calculate the distance between the employee's current location and the office location
    $jarak_meter = getDistanceHaversine($latitude_pegawai, $longitude_pegawai, $latitude_kantor, $longitude_kantor);

    // Check if the employee is outside the allowed radius
    if ($jarak_meter > $radius) {
        $_SESSION['gagal'] = "Anda berada di luar radius lokasi yang ditentukan. Jarak Anda: " . round($jarak_meter) . "m, Radius: " . round($radius) . "m.";
        // Redirect back to home after 3 seconds with a failure message
        header("Refresh: 3; URL= ../home/home.php");
        exit;
    }

    // Proceed if a student ID is available in the session
    if ($id_siswa) {
        // Check if the student has already performed attendance for today
        $cekQuery = "SELECT * FROM presensi WHERE id_siswa = '$id_siswa' AND tanggal_masuk = '$tanggal_masuk'";
        $cekResult = mysqli_query($conection, $cekQuery);

        if (mysqli_num_rows($cekResult) > 0) {
            // If a record exists, attendance has already been done for today
            $_SESSION['gagal'] = "Anda sudah melakukan presensi masuk hari ini.";
            header("Refresh: 3; URL=../home/home.php");
            exit; // Crucial to exit after a header redirect
        } else {
            // If no attendance record for today, save the photo and insert into the database
            if (file_put_contents($nama_file, $data)) {
                // Construct the SQL query to insert attendance data
                $query = "INSERT INTO presensi (id_siswa, nama_lokasi, tanggal_masuk, jam_masuk, foto_masuk)
                          VALUES ('$id_siswa', '$nama_lokasi', '$tanggal_masuk', '$jam_masuk', '$file')";
                $result = mysqli_query($conection, $query);

                if ($result) {
                    // If insertion is successful, set a success message and redirect
                    $_SESSION['berhasil'] = "Presensi masuk berhasil.";
                    header("Location: terimakasih.php"); // Redirect to a dedicated success page
                    exit;
                } else {
                    // If insertion fails, set an error message
                    $_SESSION['gagal'] = "Presensi gagal: " . mysqli_error($conection);
                }
            } else {
                // If photo saving fails, set an error message
                $_SESSION['gagal'] = "Gagal menyimpan foto.";
            }
        }
    } else {
        // If student ID is not found in the session
        $_SESSION['gagal'] = "ID pengguna tidak ditemukan.";
    }

    // Fallback redirect: If any attendance logic fails (and not redirected earlier),
    // redirect to home with a failure message after 3 seconds.
    header("Refresh: 3; URL=../home/home.php");
    exit;
}
?>