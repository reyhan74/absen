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
$status = '';
$role = '';
$nama = '';
$jenis_kelamin = '';
$alamat = '';
$no_handphone = '';

if (isset($_POST['submit'])) {
    // Retrieve and sanitize form data
    $username = htmlspecialchars($_POST['username']);
    $password = $_POST['password']; // Jangan langsung htmlspecialchars, akan di-hash
    $confirm_password = $_POST['confirm_password'];
    $status = htmlspecialchars($_POST['status']);
    $role = htmlspecialchars($_POST['role']);
    $nama = htmlspecialchars($_POST['nama']);
    $jenis_kelamin = htmlspecialchars($_POST['jenis_kelamin']);
    $alamat = htmlspecialchars($_POST['alamat']);
    $no_handphone = htmlspecialchars($_POST['no_handphone']);
    
    // Handle file upload
    $foto_name = $_FILES['foto']['name'];
    $foto_tmp_name = $_FILES['foto']['tmp_name'];
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
    if (empty($status)) {
        $pesan_kesalahan[] = "Status wajib diisi.";
    }
    if (empty($role)) {
        $pesan_kesalahan[] = "Role wajib diisi.";
    }
    if (empty($nama)) {
        $pesan_kesalahan[] = "Nama wajib diisi.";
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

        // Determine upload directory based on role
        // Path disesuaikan agar relatif terhadap root folder 'absen'
        $base_upload_dir = '../../assets/img/'; 
        $upload_dir_role = '';

        if ($role == 'guru') {
            $upload_dir_role = 'profile_guru/'; 
        } elseif ($role == 'wali_murid') {
            $upload_dir_role = 'profile_wali/'; 
        } else {
            // Default directory if role is not guru or wali_murid (e.g., for 'admin' if you add that option)
            $upload_dir_role = 'profiles/'; 
        }
        
        $final_upload_dir = $base_upload_dir . $upload_dir_role;

        // Ensure the directory exists and is writable
        if (!is_dir($final_upload_dir)) {
            // Create directory recursively with write permissions for owner/group
            mkdir($final_upload_dir, 0775, true); 
        }

        $foto_target_path = $final_upload_dir . $unique_file_name; // Full path to save

        if (move_uploaded_file($foto_tmp_name, $foto_target_path)) {
            // Hash the password only after successful validation and file upload
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // --- Save data to the database based on role ---
            $stmt = null;
            if ($role == 'guru') {
                // Based on your guru table structure (Screenshot 319.jpg), it has 'alamat' field.
                $stmt = $conection->prepare("INSERT INTO guru (username, password, nama, jenis_kelamin, alamat, no_handphone, status, role, foto) 
                                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssssss", $username, $hashedPassword, $nama, $jenis_kelamin, $alamat, $no_handphone, $status, $role, $foto_target_path); 
            } elseif ($role == 'wali_murid') {
                // Based on previous suggestion for wali_murid table:
                // email, password, nama_wali, jenis_kelamin, alamat, telepon, status, foto
                // Please ensure your 'wali_murid' table has 'jenis_kelamin' and 'status' columns if you use them.
                // 'username' from form is mapped to 'email' in wali_murid, 'no_handphone' to 'telepon'.
                $stmt = $conection->prepare("INSERT INTO guru (username, password, nama, jenis_kelamin, alamat, no_handphone, status, role, foto) 
                                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssssss", $username, $hashedPassword, $nama, $jenis_kelamin, $alamat, $no_handphone, $status, $role, $foto_target_path); 
            }
            
            if ($stmt && $stmt->execute()) {
                $_SESSION['pesan_sukses'] = "Data pengguna berhasil ditambahkan!";
                header("Location: users.php"); // Redirect to the user list page
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
                <h2 class="page-title">Tambah User</h2>
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
                            <label for="status" class="form-label">STATUS</label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="aktif" <?php echo ($status == "aktif") ? "selected" : ""; ?>>Aktif</option>
                                <option value="tidak-aktif" <?php echo ($status == "tidak-aktif") ? "selected" : ""; ?>>Tidak Aktif</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="role" class="form-label">ROLE</label>
                            <select name="role" id="role" class="form-control" required>
                                <option value="guru" <?php echo ($role == "guru") ? "selected" : ""; ?>>Guru</option>
                                <option value="wali_murid" <?php echo ($role == "wali_murid") ? "selected" : ""; ?>>Wali Murid</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="nama" class="form-label">NAMA</label>
                            <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($nama); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="jenis_kelamin" class="form-label">JENIS KELAMIN</label>
                            <select name="jenis_kelamin" id="jenis_kelamin" class="form-control" required>
                                <option value="laki-laki" <?php echo ($jenis_kelamin == "laki-laki") ? "selected" : ""; ?>>Laki-laki</option>
                                <option value="perempuan" <?php echo ($jenis_kelamin == "perempuan") ? "selected" : ""; ?>>Perempuan</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="no_handphone" class="form-label">NO Handphone</label>
                            <input type="text" class="form-control" id="no_handphone" name="no_handphone" value="<?php echo htmlspecialchars($no_handphone); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <input type="text" class="form-control" id="alamat" name="alamat" value="<?php echo htmlspecialchars($alamat); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="foto" class="form-label">Foto</label>
                            <input type="file" class="form-control" id="foto" name="foto" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" name="submit">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include('../layout/foother.php'); // Perhatikan bahwa saya mengubah ini menjadi 'foother.php' berdasarkan potensi typo di include Anda. Jika nama file Anda 'footer.php', ubah kembali. ?>
<?php ob_end_flush(); // End output buffering and send output ?>