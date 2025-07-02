<?php
ob_start(); // Start output buffering
session_start();

// Check if the user is logged in
if (!isset($_SESSION['login'])) {
    header("location: ../../auth/login.php?pesan=belum_login");
    exit();
} else if ($_SESSION["role"] != 'admin') {
    header("location:../../auth/login.php?pesan=tolak_akses");
    exit();
}

require_once('../../config.php');

if (isset($_POST['submit'])) {
    // Retrieve and sanitize form data
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);
    $confirm_password = htmlspecialchars($_POST['confirm_password']);
    $status = htmlspecialchars($_POST['status']);
    $role = htmlspecialchars($_POST['role']);
    $nama = htmlspecialchars($_POST['nama']);
    $jenis_kelamin = htmlspecialchars($_POST['jenis_kelamin']);
    $alamat = htmlspecialchars($_POST['alamat']);
    $no_handphone = htmlspecialchars($_POST['no_handphone']);
    $lokasi_presensi = htmlspecialchars($_POST['lokasi_presensi']);
    
    // Handle file upload
    $foto = $_FILES['foto']['name'];
    $foto_tmp = $_FILES['foto']['tmp_name'];
    $foto_path = '../../siswa/home/profile' . basename($foto); // Set the path to save the file

    // Initialize an array for error messages
    $pesan_kesalahan = [];

    // Validate input fields
    if (empty($username)) {
        $pesan_kesalahan[] = "Username wajib diisi";
    }
    if ($password !== $confirm_password) {
        $pesan_kesalahan[] = "Password dan konfirmasi password tidak cocok";
    }
    if (empty($status)) {
        $pesan_kesalahan[] = "Status wajib diisi";
    }
    if (empty($role)) {
        $pesan_kesalahan[] = "Role wajib diisi";
    }
    if (empty($nama)) {
        $pesan_kesalahan[] = "Nama wajib diisi";
    }
    if (empty($jenis_kelamin)) {
        $pesan_kesalahan[] = "Jenis Kelamin wajib diisi";
    }
    if (empty($alamat)) {
        $pesan_kesalahan[] = "Alamat wajib diisi";
    }
    if (empty($no_handphone)) {
        $pesan_kesalahan[] = "No Handphone wajib diisi"; 
    }
    if (empty($lokasi_presensi)) {
        $pesan_kesalahan[] = "Lokasi Presensi wajib diisi"; 
    }
    if (empty($foto)) {
        $pesan_kesalahan[] = "Foto wajib diisi"; 
    } else {
        // Validate file upload
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['foto']['type'], $allowed_types)) {
            $pesan_kesalahan[] = "Format foto tidak valid. Harap unggah file JPG, PNG, atau GIF.";
        }
    }

    // If there are validation errors, store them in the session
    if (!empty($pesan_kesalahan)) {
        $_SESSION['validasi'] = implode("<br>", $pesan_kesalahan);
    } else {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Move the uploaded file to the server
        if (move_uploaded_file($foto_tmp, $foto_path)) {
            // If validation is successful, save data to the database
            $stmt = $conection->prepare("INSERT INTO guru (username, password, nama, jenis_kelamin, alamat, no_handphone, status, role, foto) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssss", $username, $hashedPassword, $nama, $jenis_kelamin, $alamat, $no_handphone, $status, $role, $foto_path);

            if ($stmt->execute()) {
                header("Location: users.php");
                exit();
            } else {
                $_SESSION['validasi'] = "Terjadi kesalahan saat menyimpan data.";
            }
        } else {
            $_SESSION['validasi'] = "Terjadi kesalahan saat mengupload foto.";
        }
    }
}

include('../layout/header.php');
?>

<!-- HTML Form -->
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
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="">USERNAME</label>
                            <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="">PASSWORD</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="">KONFIRMASI PASSWORD</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="">STATUS</label>
                            <select name="status" class="form-control" required>
                                <option value="aktif" <?php echo (isset($status) && $status == "aktif") ? "selected" : ""; ?>>Aktif</option>
                                <option value="tidak-aktif" <?php echo (isset($status) && $status == "tidak-aktif") ? "selected" : ""; ?>>Tidak Aktif</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="">ROLE</label>
                            <select name="role" class="form-control" required>
                                <option value="guru" <?php echo (isset($role) && $role == "guru") ? "selected" : ""; ?>>Guru</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="">NAMA</label>
                            <input type="text" class="form-control" name="nama" value="<?php echo htmlspecialchars($nama ?? ''); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="">JENIS KELAMIN</label>
                            <select name="jenis_kelamin" class="form-control" required>
                                <option value="laki-laki" <?php echo (isset($jenis_kelamin) && $jenis_kelamin == "laki-laki") ? "selected" : ""; ?>>Laki-laki</option>
                                <option value="perempuan" <?php echo (isset($jenis_kelamin) && $jenis_kelamin == "perempuan") ? "selected" : ""; ?>>Perempuan</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="">NO Handphone</label>
                            <input type="text" class="form-control" name="no_handphone" value="<?php echo htmlspecialchars($no_handphone ?? ''); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="">Alamat</label>
                            <input type="text" class="form-control" name="alamat" value="<?php echo htmlspecialchars($alamat ?? ''); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="">Lokasi Presensi</label>
                            <input type="text" class="form-control" name="lokasi_presensi" value="<?php echo htmlspecialchars($lokasi_presensi ?? ''); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="">Foto</label>
                            <input type="file" class="form-control" name="foto" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" name="submit">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>
