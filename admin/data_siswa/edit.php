<?php
ob_start(); // Start output buffering
session_start();

// Check if the user is logged in and has 'admin' role
if (!isset($_SESSION['login'])) {
    header("location: ../../auth/login.php?pesan=belum_login");
    exit();
} elseif ($_SESSION["role"] != 'admin') {
    header("location: ../../auth/login.php?pesan=tolak_akses");
    exit();
}

require_once('../../config.php'); // Make sure $conection is available from here

// Get NIS from URL (GET parameter)
$nis_to_edit = $_GET['nis'] ?? null;

// Redirect if NIS is not provided
if (!$nis_to_edit) {
    $_SESSION['validasi'] = "NIS siswa tidak ditemukan.";
    header("Location: users.php"); // Or wherever your student list is
    exit();
}

// Fetch existing student data
$stmt_fetch = $conection->prepare("SELECT nis, no_absen, nama, jenis_kelamin, alamat, kelas, no_handphone, status, username, foto FROM siswa WHERE nis = ?");
$stmt_fetch->bind_param("s", $nis_to_edit);
$stmt_fetch->execute();
$result_fetch = $stmt_fetch->get_result();
$siswa_data = $result_fetch->fetch_assoc();
$stmt_fetch->close();

// Redirect if student not found
if (!$siswa_data) {
    $_SESSION['validasi'] = "Data siswa dengan NIS " . htmlspecialchars($nis_to_edit) . " tidak ditemukan.";
    header("Location: users.php");
    exit();
}

// Initialize variables with existing data for form pre-filling
$nis = $siswa_data['nis'];
$no_absen = $siswa_data['no_absen'];
$nama = $siswa_data['nama'];
$jenis_kelamin = $siswa_data['jenis_kelamin'];
$alamat = $siswa_data['alamat'];
$kelas = $siswa_data['kelas'];
$no_handphone = $siswa_data['no_handphone'];
$status = $siswa_data['status'];
$username = $siswa_data['username'];
$current_foto_path = $siswa_data['foto']; // Store current photo path

// Password fields are usually left blank for security, users fill only if they want to change
$password = '';
$confirm_password = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit'])) {
    // Retrieve and sanitize form data from POST
    $no_absen = htmlspecialchars($_POST['no_absen']);
    $nama = htmlspecialchars($_POST['nama']);
    $jenis_kelamin = htmlspecialchars($_POST['jenis_kelamin']);
    $alamat = htmlspecialchars($_POST['alamat']);
    $kelas = htmlspecialchars($_POST['kelas']);
    $no_handphone = htmlspecialchars($_POST['no_handphone']);
    $status = htmlspecialchars($_POST['status']);
    $username = htmlspecialchars($_POST['username']);
    $password = $_POST['password']; // New password (if provided)
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    // --- Input Validations ---
    if (empty($no_absen)) $errors[] = "Nomor Absen wajib diisi";
    if (empty($nama)) $errors[] = "Nama wajib diisi";
    if (empty($jenis_kelamin)) $errors[] = "Jenis Kelamin wajib diisi";
    if (empty($alamat)) $errors[] = "Alamat wajib diisi";
    if (empty($kelas)) $errors[] = "Kelas wajib diisi";
    if (empty($no_handphone)) $errors[] = "No Handphone wajib diisi";
    if (empty($status)) $errors[] = "Status wajib diisi";
    if (empty($username)) $errors[] = "Username wajib diisi.";

    // Password validation only if new password fields are filled
    if (!empty($password) || !empty($confirm_password)) {
        if ($password !== $confirm_password) {
            $errors[] = "Password dan konfirmasi password tidak cocok.";
        }
    }

    // --- Photo Handling ---
    $new_foto_uploaded = false;
    $new_foto_path = $current_foto_path; // Default to current photo path

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
        $foto_name = $_FILES['foto']['name'];
        $foto_tmp = $_FILES['foto']['tmp_name'];
        $foto_error = $_FILES['foto']['error'];
        $foto_size = $_FILES['foto']['size'];
        $foto_type = $_FILES['foto']['type'];

        if ($foto_error !== UPLOAD_ERR_OK) {
            $errors[] = "Terjadi kesalahan saat mengunggah foto. Error code: " . $foto_error;
        } else {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($foto_type, $allowed_types)) {
                $errors[] = "Format foto tidak valid. Harap unggah file JPG, PNG, atau GIF.";
            }
            $max_file_size = 2 * 1024 * 1024; // 2MB
            if ($foto_size > $max_file_size) {
                $errors[] = "Ukuran foto terlalu besar. Maksimal 2MB.";
            }
            if (empty($errors)) {
                $new_foto_uploaded = true;
                $file_ext = strtolower(pathinfo($foto_name, PATHINFO_EXTENSION));
                $unique_file_name = uniqid('siswa_profile_', true) . '.' . $file_ext;
                $new_foto_path = '../../assets/img/profile_siswa/' . $unique_file_name;
            }
        }
    }

    if (!empty($errors)) {
        $_SESSION['validasi'] = implode("<br>", $errors);
        // Persist submitted values for user convenience (except passwords)
        $_SESSION['form_data'] = [
            'nama' => $_POST['nama'] ?? '',
            'no_absen' => $_POST['no_absen'] ?? '',
            'kelas' => $_POST['kelas'] ?? '',
            'jenis_kelamin' => $_POST['jenis_kelamin'] ?? '',
            'alamat' => $_POST['alamat'] ?? '',
            'no_handphone' => $_POST['no_handphone'] ?? '',
            'status' => $_POST['status'] ?? '',
            'username' => $_POST['username'] ?? ''
        ];
        header("Location: edit.php?nis=" . urlencode($nis_to_edit));
        exit();
    } else {
        // Prepare for database update
        $hashedPassword = $siswa_data['password']; // Keep existing password if not updated
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        }

        // Handle photo move only if a new photo was uploaded
        if ($new_foto_uploaded) {
            // Ensure the directory exists and is writable
            if (!is_dir('../../assets/img/profile_siswa/')) {
                mkdir('../../assets/img/profile_siswa/', 0775, true);
            }

            if (move_uploaded_file($foto_tmp, $new_foto_path)) {
                // If old photo exists and is different, delete it
                if ($current_foto_path && file_exists($current_foto_path) && $current_foto_path !== $new_foto_path) {
                    unlink($current_foto_path);
                }
            } else {
                $_SESSION['validasi'] = "Gagal mengunggah foto baru. Pastikan folder tujuan ada dan memiliki izin tulis (CHMOD 775 atau 777).";
                header("Location: edit.php?nis=" . urlencode($nis_to_edit));
                exit();
            }
        }

        // Prepare the UPDATE query
        // Using `nis_to_edit` in WHERE clause to ensure we update the correct record
        // The NIS itself should generally not be changed once set.
        $query = "UPDATE siswa SET username = ?, password = ?, nama = ?, no_absen = ?, kelas = ?, jenis_kelamin = ?, alamat = ?, no_handphone = ?, foto = ?, status = ? WHERE nis = ?";
        $stmt = $conection->prepare($query);
        $stmt->bind_param("sssisssssss",
            $username,
            $hashedPassword,
            $nama,
            $no_absen,
            $kelas,
            $jenis_kelamin,
            $alamat,
            $no_handphone,
            $new_foto_path, // Use new photo path or old one
            $status,
            $nis_to_edit // This is the original NIS from the URL
        );

        if ($stmt->execute()) {
            $_SESSION['berhasil'] = "Data Siswa Berhasil Diperbarui";
            header("Location: users.php"); // Redirect to the student list
            exit();
        } else {
            $_SESSION['validasi'] = "Terjadi kesalahan dalam memperbarui data siswa: " . $stmt->error;
            header("Location: edit.php?nis=" . urlencode($nis_to_edit));
            exit();
        }
        $stmt->close();
    }
}

// Restore form data after validation error (if form_data exists in session)
if (isset($_SESSION['form_data'])) {
    $form_data = $_SESSION['form_data'];
    $nama = $form_data['nama'] ?? $nama;
    $no_absen = $form_data['no_absen'] ?? $no_absen;
    $kelas = $form_data['kelas'] ?? $kelas;
    $jenis_kelamin = $form_data['jenis_kelamin'] ?? $jenis_kelamin;
    $alamat = $form_data['alamat'] ?? $alamat;
    $no_handphone = $form_data['no_handphone'] ?? $no_handphone;
    $status = $form_data['status'] ?? $status;
    $username = $form_data['username'] ?? $username;
    unset($_SESSION['form_data']); // Clear after use
}


include('../layout/header.php');
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Edit Data Siswa</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card col-md-8">
            <div class="card-body">
                <?php if (isset($_SESSION['validasi'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['validasi']; unset($_SESSION['validasi']); ?></div>
                <?php endif; ?>

                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nis">NIS</label>
                            <input type="text" class="form-control" id="nis" name="nis" value="<?= htmlspecialchars($nis); ?>" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($username); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password (Biarkan kosong jika tidak ingin mengubah)</label>
                            <input type="password" class="form-control" id="password" name="password">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Konfirmasi Password (Biarkan kosong jika tidak ingin mengubah)</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="nama">Nama</label>
                            <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($nama); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="no_absen">Nomor Absen</label>
                            <select name="no_absen" id="no_absen" class="form-control" required>
                                <option value="">--Pilih Nomor Absen--</option>
                                <?php for ($i = 1; $i <= 40; $i++): ?>
                                    <option value="<?= $i; ?>" <?= ((int)$no_absen === $i) ? 'selected' : '' ?>><?= $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="kelas">Pilih Kelas</label>
                            <select name="kelas" id="kelas" class="form-control" required>
                                <option value="">--Pilih Kelas--</option>
                                <?php
                                $classes = [
                                    "X TKJ 1", "X TKJ 2", "X TKJ 3",
                                    "X TPM 1", "X TPM 2", "X TPM 3", "X TPM 4", "X TPM 5",
                                    "X TKR 1", "X TKR 2", "X TKR 3",
                                    "X TITL 1", "X TITL 2", "X TITL 3",
                                    "X DPIB 1", "X DPIB 2",
                                    "X TOI 1", "X TOI 2",
                                    "XI TKJ 1", "XI TKJ 2", "XI TKJ 3",
                                    "XI TPM 1", "XI TPM 2", "XI TPM 3", "XI TPM 4", "XI TPM 5",
                                    "XI TKR 1", "XI TKR 2", "XI TKR 3",
                                    "XI TITL 1", "XI TITL 2", "XI TITL 3",
                                    "XI DPIB 1", "XI DPIB 2",
                                    "XI TOI 1", "XI TOI 2",
                                    "XII TKJ 1", "XII TKJ 2", "XII TKJ 3",
                                    "XII TPM 1", "XII TPM 2", "XII TPM 3", "XII TPM 4", "XII TPM 5",
                                    "XII TKR 1", "XII TKR 2", "XII TKR 3",
                                    "XII TITL 1", "XII TITL 2", "XII TITL 3",
                                    "XII DPIB 1", "XII DPIB 2",
                                    "XII TOI 1", "XII TOI 2"
                                ];
                                foreach ($classes as $c) {
                                    echo '<option value="' . htmlspecialchars($c) . '" ' . (($kelas === $c) ? 'selected' : '') . '>' . htmlspecialchars($c) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="jenis_kelamin">Jenis Kelamin</label>
                            <select name="jenis_kelamin" id="jenis_kelamin" class="form-control" required>
                                <option value="">--Pilih Jenis Kelamin--</option>
                                <option value="Laki-Laki" <?= ($jenis_kelamin === 'Laki-Laki') ? 'selected' : '' ?>>Laki-Laki</option>
                                <option value="Perempuan" <?= ($jenis_kelamin === 'Perempuan') ? 'selected' : '' ?>>Perempuan</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="alamat">Alamat</label>
                            <input type="text" class="form-control" id="alamat" name="alamat" value="<?= htmlspecialchars($alamat); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="no_handphone">No Handphone</label>
                            <input type="text" class="form-control" id="no_handphone" name="no_handphone" value="<?= htmlspecialchars($no_handphone); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="">--Pilih Status--</option>
                                <option value="aktif" <?= ($status === 'aktif') ? 'selected' : '' ?>>Aktif</option>
                                <option value="tidak aktif" <?= ($status === 'tidak aktif') ? 'selected' : '' ?>>Tidak Aktif</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="foto">Foto (Biarkan kosong jika tidak ingin mengubah)</label>
                            <input type="file" class="form-control" id="foto" name="foto">
                            <?php if ($current_foto_path): ?>
                                <small class="form-text text-muted">Foto saat ini: <a href="<?= htmlspecialchars($current_foto_path); ?>" target="_blank">Lihat Foto</a></small><br>
                                <img src="<?= htmlspecialchars($current_foto_path); ?>" alt="Current Photo" style="max-width: 100px; margin-top: 10px;">
                            <?php endif; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" name="submit">Update</button>
                    <a href="users.php" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include('../layout/foother.php'); ?>
<?php ob_end_flush(); ?>