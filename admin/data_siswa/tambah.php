<?php
ob_start(); // Start output buffering
session_start();

// Check if the user is logged in and has 'admin' role
if (!isset($_SESSION['login'])) {
    header("location: ../../auth/login.php?pesan=belum_login");
    exit();
} else if ($_SESSION["role"] != 'admin') {
    header("location:../../auth/login.php?pesan=tolak_akses");
    exit();
}

require_once('../../config.php'); // Pastikan $conection tersedia dari sini

// Initialize variables for form values to prevent undefined variable notices
$username = '';
$password = ''; // Added for consistency, though not displayed directly
$confirm_password = ''; // Added for consistency
$nis = '';
$no_absen = '';
$nama = '';
$kelas = '';
$jenis_kelamin = '';
$alamat = '';
$no_handphone = '';
$lokasi_presensi = '';
$status = '';
// foto is handled separately

if (isset($_POST['submit'])) {
    // Retrieve and sanitize form data
    $username = htmlspecialchars($_POST['username']);
    $password = $_POST['password']; // Jangan langsung htmlspecialchars, akan di-hash
    $confirm_password = $_POST['confirm_password'];
    $nis = htmlspecialchars($_POST['nis']);
    $no_absen = htmlspecialchars($_POST['no_absen']);
    $nama = htmlspecialchars($_POST['nama']);
    $kelas = htmlspecialchars($_POST['kelas']);
    $jenis_kelamin = htmlspecialchars($_POST['jenis_kelamin']);
    $alamat = htmlspecialchars($_POST['alamat']);
    $no_handphone = htmlspecialchars($_POST['no_handphone']);
    $lokasi_presensi = htmlspecialchars($_POST['lokasi_presensi']);
    $status = htmlspecialchars($_POST['status']);
    
    // Handle file upload
    $foto_name = $_FILES['foto']['name'];
    $foto_tmp_name = $_FILES['foto']['tmp_tmp_name']; // Corrected to foto_tmp_name
    $foto_error = $_FILES['foto']['error'];
    $foto_size = $_FILES['foto']['size'];
    $foto_type = $_FILES['foto']['type'];

    // Initialize an array for error messages
    $pesan_kesalahan = [];

    // --- Input Validations ---
    if (empty($username)) {
        $pesan_kesalahan[] = "Username wajib diisi.";
    }
    if (empty($password)) {
        $pesan_kesalahan[] = "Password wajib diisi.";
    }
    if ($password !== $confirm_password) {
        $pesan_kesalahan[] = "Password dan konfirmasi password tidak cocok.";
    }
    // New validations for added fields
    if (empty($nis)) {
        $pesan_kesalahan[] = "NIS wajib diisi.";
    }
    if (empty($no_absen)) {
        $pesan_kesalahan[] = "Nomor Absen wajib diisi.";
    }
    if (empty($nama)) {
        $pesan_kesalahan[] = "Nama wajib diisi.";
    }
    if (empty($kelas)) {
        $pesan_kesalahan[] = "Kelas wajib diisi.";
    }
    if (empty($jenis_kelamin)) {
        $pesan_kesalahan[] = "Jenis Kelamin wajib diisi.";
    }
    if (empty($alamat)) {
        $pesan_kesalahan[] = "Alamat wajib diisi.";
    }
    if (empty($no_handphone)) {
        $pesan_kesalahan[] = "No Handphone wajib diisi."; 
    }
    if (empty($lokasi_presensi)) {
        $pesan_kesalahan[] = "Lokasi Presensi wajib diisi.";
    }
    if (empty($status)) {
        $pesan_kesalahan[] = "Status wajib diisi.";
    }
    
    // --- Foto Validations ---
    if ($foto_error === UPLOAD_ERR_NO_FILE) {
        $pesan_kesalahan[] = "Foto wajib diunggah."; 
    } elseif ($foto_error !== UPLOAD_ERR_OK) {
        $pesan_kesalahan[] = "Terjadi kesalahan saat mengunggah foto. Error code: " . $foto_error;
    } else {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($foto_type, $allowed_types)) {
            $pesan_kesalahan[] = "Format foto tidak valid. Harap unggah file JPG, PNG, atau GIF.";
        }
        // Optional: Limit file size
        $max_file_size = 2 * 1024 * 1024; // 2MB
        if ($foto_size > $max_file_size) {
            $pesan_kesalahan[] = "Ukuran foto terlalu besar. Maksimal 2MB.";
        }
    }

    // If there are validation errors, store them in the session
    if (!empty($pesan_kesalahan)) {
        $_SESSION['validasi'] = implode("<br>", $pesan_kesalahan);
        // Redirect back to the form to display errors
        header("Location: tambah.php"); 
        exit();
    } else {
        // --- Process Photo Upload ---
        // Create unique file name to avoid overwriting
        $file_ext = strtolower(pathinfo($foto_name, PATHINFO_EXTENSION));
        $unique_file_name = uniqid('profile_', true) . '.' . $file_ext; // profile_randomstring.jpg

        // Determine upload directory based on role (though 'role' isn't in 'absen' table, it's used for directory)
        // Path disesuaikan agar relatif terhadap root folder 'absen'
        $base_upload_dir = '../../assets/img/profile_siswa'; 
        
        $final_upload_dir = $base_upload_dir;

        // Ensure the directory exists and is writable
        if (!is_dir($final_upload_dir)) {
            // Create directory recursively with write permissions for owner/group
            mkdir($final_upload_dir, 0775, true); 
        }

        $foto_target_path = $final_upload_dir . $unique_file_name; // Full path to save

        if (move_uploaded_file($foto_tmp_name, $foto_target_path)) {
            // Hash the password only after successful validation and file upload
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // --- Save data to the database (absen table based on new screenshot) ---
            // The table in the screenshot is 'absen', and it contains all the necessary fields.
            $stmt = $conection->prepare("INSERT INTO absen (username, password, nis, no_absen, nama, kelas, jenis_kelamin, alamat, no_handphone, lokasi_presensi, foto, status) 
                                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssssss", 
                                $username, $hashedPassword, $nis, $no_absen, $nama, $kelas, 
                                $jenis_kelamin, $alamat, $no_handphone, $lokasi_presensi, $foto_target_path, $status); 
            
            if ($stmt && $stmt->execute()) {
                $_SESSION['pesan_sukses'] = "Data pengguna berhasil ditambahkan!";
                header("Location: users.php"); // Redirect to the user list page (or wherever you display 'absen' data)
                exit();
            } else {
                $_SESSION['validasi'] = "Terjadi kesalahan saat menyimpan data ke database: " . ($stmt ? $stmt->error : "Statement error.");
                // Redirect back to the form to display errors
                header("Location: tambah.php"); 
                exit();
            }
            if ($stmt) $stmt->close();

        } else {
            $_SESSION['validasi'] = "Terjadi kesalahan saat mengupload foto. Pastikan folder tujuan ada dan memiliki izin tulis (CHMOD 775 atau 777).";
            // Redirect back to the form to display errors
            header("Location: tambah.php"); 
            exit();
        }
    }
}

include('../layout/header.php');
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Tambah Data Absen/Pengguna</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card col-md-8">
            <div class="card-body">
                <?php
                // Display validation errors if any
                if (isset($_SESSION['validasi'])) {
                    echo '<div class="alert alert-danger">' . $_SESSION['validasi'] . '</div>';
                    unset($_SESSION['validasi']); // Clear the session variable
                }
                // Display success message
                if (isset($_SESSION['pesan_sukses'])) {
                    echo '<div class="alert alert-success">' . $_SESSION['pesan_sukses'] . '</div>';
                    unset($_SESSION['pesan_sukses']); // Clear the session variable
                }
                ?>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">USERNAME</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">PASSWORD</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">KONFIRMASI PASSWORD</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="nis" class="form-label">NIS</label>
                            <input type="text" class="form-control" id="nis" name="nis" value="<?php echo htmlspecialchars($nis); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="no_absen" class="form-label">NO ABSEN</label>
                            <input type="text" class="form-control" id="no_absen" name="no_absen" value="<?php echo htmlspecialchars($no_absen); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="nama" class="form-label">NAMA</label>
                            <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($nama); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="kelas" class="form-label">KELAS</label>
                            <input type="text" class="form-control" id="kelas" name="kelas" value="<?php echo htmlspecialchars($kelas); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="jenis_kelamin" class="form-label">JENIS KELAMIN</label>
                            <select name="jenis_kelamin" id="jenis_kelamin" class="form-control" required>
                                <option value="" disabled selected>Pilih Jenis Kelamin</option>
                                <option value="laki-laki" <?php echo ($jenis_kelamin == "laki-laki") ? "selected" : ""; ?>>Laki-laki</option>
                                <option value="perempuan" <?php echo ($jenis_kelamin == "perempuan") ? "selected" : ""; ?>>Perempuan</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="alamat" class="form-label">ALAMAT</label>
                            <input type="text" class="form-control" id="alamat" name="alamat" value="<?php echo htmlspecialchars($alamat); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="no_handphone" class="form-label">NO HANDPHONE</label>
                            <input type="text" class="form-control" id="no_handphone" name="no_handphone" value="<?php echo htmlspecialchars($no_handphone); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="lokasi_presensi" class="form-label">LOKASI PRESENSI</label>
                            <input type="text" class="form-control" id="lokasi_presensi" name="lokasi_presensi" value="<?php echo htmlspecialchars($lokasi_presensi); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">STATUS</label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="aktif" <?php echo ($status == "aktif") ? "selected" : ""; ?>>Aktif</option>
                                <option value="tidak-aktif" <?php echo ($status == "tidak-aktif") ? "selected" : ""; ?>>Tidak Aktif</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="foto" class="form-label">FOTO</label>
                            <input type="file" class="form-control" id="foto" name="foto" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" name="submit">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include('../layout/foother.php'); ?>
<?php ob_end_flush(); // End output buffering and send output ?>