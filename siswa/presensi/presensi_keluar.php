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
    // Remove the data URI prefix and replace spaces with plus signs for base64 decoding
    $foto = str_replace('data:image/jpeg;base64,', '', $file_foto);
    $foto = str_replace(' ', '+', $foto);
    $data = base64_decode($foto); // Decode the base64 image data

    // Create 'foto' directory if it doesn't exist.
    // Permissions '0777' are very permissive; consider '0755' or '0775' for production.
    if (!is_dir('foto')) {
        mkdir('foto', 0777, true);
    }

    $tanggal_keluar = date('Y-m-d'); // Current server date
    $jam_keluar = date('H:i:s'); // Current server time
    // Ensure the filename always has a .jpeg extension
    $nama_file = 'foto/keluar_' . date('Y-m-d_H-i-s') . '.jpeg';
    $file_path_for_db = basename($nama_file); // Just the filename for database

    $id_siswa = $_SESSION['id'] ?? null; // Get student ID from session (assuming 'id' is for siswa)

    // Retrieve ALL necessary data from the POST request (these came from home.php)
    // These variables are still needed for the radius check, even if not inserted into presensi_out
    $nama_lokasi = mysqli_real_escape_string($conection, $_POST['nama_lokasi'] ?? 'Tidak diketahui');
    $latitude_pegawai = floatval($_POST['latitude_pegawai'] ?? 0);
    $longitude_pegawai = floatval($_POST['longitude_pegawai'] ?? 0);
    $latitude_kantor = floatval($_POST['latitude_kantor'] ?? 0);
    $longitude_kantor = floatval($_POST['longitude_kantor'] ?? 0);
    $radius = floatval($_POST['radius'] ?? 0);

    // Calculate distance
    $jarak_meter = getDistanceHaversine($latitude_pegawai, $longitude_pegawai, $latitude_kantor, $longitude_kantor);

    // --- Radius check for presensi keluar (STILL PERFORMED) ---
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

        // If checks pass, proceed to save the photo and insert into presensi_out
        // Save the raw decoded image data first
        if (file_put_contents($nama_file, $data)) {
            // Optional: Re-process the image using GD for compression if needed
            // This ensures a specific quality regardless of client-side setting, or can reduce file size further.
            if (function_exists('imagecreatefromjpeg') && function_exists('imagejpeg')) {
                $source_image = @imagecreatefromjpeg($nama_file); // Use @ to suppress warnings if file is corrupted
                if ($source_image) {
                    $compression_quality = 80; // Adjust this value (0-100) for more/less compression
                    imagejpeg($source_image, $nama_file, $compression_quality);
                    imagedestroy($source_image); // Free up memory
                    // echo "Image re-compressed to quality: " . $compression_quality . ".\n"; // Debugging
                } else {
                    error_log("Failed to create JPEG image from " . $nama_file . " for re-compression.");
                    // Optionally, delete the file if it couldn't be processed
                    // unlink($nama_file);
                }
            } else {
                error_log("GD library functions for JPEG not available for re-compression.");
            }

            // --- MODIFIED INSERT QUERY (EXCLUDING LAT/LONG/NAMA_LOKASI) ---
            $query = "INSERT INTO presensi_out (id_siswa, tanggal_keluar, jam_keluar, foto_keluar)
                      VALUES ('$id_siswa', '$tanggal_keluar', '$jam_keluar', '$file_path_for_db')";
            $result = mysqli_query($conection, $query);

            if ($result) {
                $_SESSION['berhasil'] = "Presensi keluar berhasil.";
                header("Location: terimakasih_keluar.php"); // Assuming this page exists
                exit;
            } else {
                $_SESSION['gagal'] = "Presensi keluar gagal: " . mysqli_error($conection);
            }
        } else {
             $_SESSION['gagal'] = "Gagal menyimpan foto ke server.";
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
// Include SweetAlert2 library for better user notifications
echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';

// If the page is loaded via GET or a POST without photo (i.e., initial load from home.php)
if ($_SERVER['REQUEST_METHOD'] === 'GET' || ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['photo']))) {
    // Collect the data passed from home.php for initial display
    $nama_lokasi = mysqli_real_escape_string($conection, $_POST['nama_lokasi'] ?? 'Tidak diketahui');
    $latitude_kantor = floatval($_POST['latitude_kantor'] ?? 0);
    $longitude_kantor = floatval($_POST['longitude_kantor'] ?? 0);
    $radius = floatval($_POST['radius'] ?? 0);
    $jam_pulang_kantor_config = $_POST['jam_pulang_kantor'] ?? ''; // Pass the configured jam_pulang
?>
<head>
    <title>Presensi Keluar</title>
    <style>
        #my_result {
            margin-top: 10px; /* Added margin for spacing below webcam feed */
        }

        #my_result img {
            max-width: 100%; /* Ensure image doesn't overflow its container */
            height: auto;    /* Maintain aspect ratio */
            display: block;  /* Remove extra space below the image */
            border: 1px solid #ddd; /* Subtle border for the image */
            border-radius: 4px; /* Slightly rounded corners for aesthetics */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Soft shadow */
        }

        /* Optional: Add some spacing below the camera feed */
        #my_camera {
            margin-bottom: 15px;
        }
    </style>
</head>
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
                            <input type="hidden" name="latitude_kantor" value="<?= htmlspecialchars($latitude_kantor) ?>">
                            <input type="hidden" name="longitude_kantor" value="<?= htmlspecialchars($longitude_kantor) ?>">
                            <input type="hidden" name="radius" value="<?= htmlspecialchars($radius) ?>">
                            <input type="hidden" name="jam_pulang_kantor" value="<?= htmlspecialchars($jam_pulang_kantor_config) ?>">
                            <input type="hidden" name="latitude_pegawai" id="latitude_pegawai_input">
                            <input type="hidden" name="longitude_pegawai" id="longitude_pegawai_input">
                            <input type="hidden" name="tanggal_keluar_form" value="<?= htmlspecialchars($tanggal_keluar_form) ?>">
                            <input type="hidden" name="jam_keluar_form" value="<?= htmlspecialchars($jam_keluar_form) ?>">

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
        image_format: 'jpeg', // Already set to JPEG
        jpeg_quality: 90 // Already set to 90% quality
    });
    Webcam.attach('#my_camera');

    function updateMap(lat, long) {
        // The Google Maps embed URL corrected to use the variables directly
        document.getElementById('map-container').innerHTML = `
            <iframe
                src="https://maps.google.com/maps?q=${lat},${long}&hl=id&z=14&output=embed"
                width="100%"
                height="400"
                style="border:0;"
                allowfullscreen>
            </iframe>
        `;
    }

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
            const userLat = pos.coords.latitude;
            const userLong = pos.coords.longitude;
            document.getElementById('latitude_pegawai_input').value = userLat;
            document.getElementById('longitude_pegawai_input').value = userLong;
            updateMap(userLat, userLong);
            console.log("Geolocation Success! Latitude:", userLat, "Longitude:", userLong); // Debugging log
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
            console.error("Geolocation Error:", error.code, error.message); // Debugging error log
            // Specific check for secure context error (common in local dev)
            if (error.code === 0 && error.message.includes("Only secure origins are allowed")) {
                console.error("Hint: This might be due to running on HTTP. Try enabling HTTPS or Chrome's insecure origins flag.");
            }
        }, {
            enableHighAccuracy: true, // Request high accuracy
            timeout: 10000,           // Set a timeout of 10 seconds
            maximumAge: 0             // Don't use a cached position, get a fresh one
        });
    } else {
        document.getElementById('map-container').innerHTML = '<p class="text-center text-danger">Browser Anda tidak mendukung geolokasi.</p>';
        Swal.fire('Browser tidak mendukung', 'Perangkat tidak mendukung geolokasi', 'error');
        document.getElementById('ambil-foto').disabled = true; // Disable button if geolocation not supported
        console.error("Browser does not support Geolocation."); // Debugging log
    }

    document.getElementById('ambil-foto').addEventListener('click', function () {
        if (!document.getElementById('latitude_pegawai_input').value || !document.getElementById('longitude_pegawai_input').value) {
            Swal.fire('Lokasi Belum Tersedia', 'Mohon tunggu atau pastikan akses lokasi diizinkan.', 'warning');
            return;
        }

        Webcam.snap(function (data_uri) {
            // Display the captured image preview with specific styling for better display
            const resultDiv = document.getElementById('my_result');
            resultDiv.innerHTML = `
                <img src="${data_uri}"
                     alt="Foto Presensi"
                     style="width: 100%; height: auto; display: block; border: 1px solid #ccc; border-radius: 5px; margin-top: 10px;"/>
            `;
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
<?php unset($_SESSION['gagal']); // Clear the session variable after displaying ?>
<?php endif; ?>

<?php
// Include the common footer for the page
include('../layout/foother.php');
?>