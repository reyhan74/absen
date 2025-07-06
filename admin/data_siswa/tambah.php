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

// Get the last NIS from the database
$result = $conection->query("SELECT nis FROM siswa ORDER BY nis DESC LIMIT 1");
$last_nis = $result->fetch_assoc();
$next_nis = $last_nis ? str_pad((int)$last_nis['nis'] + 1, 6, '0', STR_PAD_LEFT) : '000001'; // Start from 000001 if no records exist

// Initialize variables for form values to prevent undefined variable notices when form is not yet submitted
$nis = $next_nis;
$no_absen = '';
$nama = '';
$jenis_kelamin = '';
$alamat = '';
$kelas = '';
$no_handphone = '';
$status = '';
$username = ''; // Initialize username
$password = ''; // Not used for direct display, but good for clarity
$confirm_password = ''; // Initialize confirm_password

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit'])) {
    // Retrieve and sanitize form data
    $nis = $next_nis; // Use the automatically generated NIS
    $no_absen = htmlspecialchars($_POST['no_absen']);
    $nama = htmlspecialchars($_POST['nama']);
    $jenis_kelamin = htmlspecialchars($_POST['jenis_kelamin']);
    $alamat = htmlspecialchars($_POST['alamat']);
    $kelas = htmlspecialchars($_POST['kelas']);
    $no_handphone = htmlspecialchars($_POST['no_handphone']);
    $status = htmlspecialchars($_POST['status']);
    $username = htmlspecialchars($_POST['username']); // Get username
    $password = $_POST['password']; // Get password (will be hashed)
    $confirm_password = $_POST['confirm_password']; // Get confirm password

    // Handle file upload
    $foto_name = $_FILES['foto']['name'];
    $foto_tmp = $_FILES['foto']['tmp_name'];
    $foto_error = $_FILES['foto']['error'];
    $foto_size = $_FILES['foto']['size'];
    $foto_type = $_FILES['foto']['type'];

    $errors = [];

    // --- Input Validations ---
    if (empty($no_absen)) $errors[] = "Nomor Absen wajib diisi";
    if (empty($nama)) $errors[] = "Nama wajib diisi";
    if (empty($jenis_kelamin)) $errors[] = "Jenis Kelamin wajib diisi";
    if (empty($alamat)) $errors[] = "Alamat wajib diisi";
    if (empty($kelas)) $errors[] = "Kelas wajib diisi"; // Added validation for kelas
    if (empty($no_handphone)) $errors[] = "No Handphone wajib diisi";
    if (empty($status)) $errors[] = "Status wajib diisi";
    if (empty($username)) $errors[] = "Username wajib diisi."; // Username validation
    if (empty($password)) $errors[] = "Password wajib diisi."; // Password validation
    if ($password !== $confirm_password) $errors[] = "Password dan konfirmasi password tidak cocok."; // Password match validation

    // --- Foto Validations ---
    if ($foto_error === UPLOAD_ERR_NO_FILE) {
        $errors[] = "Foto wajib diunggah.";
    } elseif ($foto_error !== UPLOAD_ERR_OK) {
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
        header("Location: tambah_siswa.php");
        exit();
    } else {
        // --- Process Photo Upload ---
        $file_ext = strtolower(pathinfo($foto_name, PATHINFO_EXTENSION));
        $unique_file_name = uniqid('siswa_profile_', true) . '.' . $file_ext;
        $foto_path = '../../assets/img/profile_siswa/' . $unique_file_name;

        // Ensure the directory exists and is writable
        if (!is_dir('../../assets/img/profile_siswa/')) {
            mkdir('../../assets/img/profile_siswa/', 0775, true);
        }

        if (move_uploaded_file($foto_tmp, $foto_path)) {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Prepare the INSERT query with new username and password columns
            // You MUST add 'username' and 'password' columns to your 'siswa' table in the database
            $stmt = $conection->prepare("INSERT INTO siswa (username, password, nis, nama, no_absen, kelas, jenis_kelamin, alamat, no_handphone, foto, status)
                                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssissssss", $nis, $username, $hashedPassword, $nis, $nama, $no_absen, $kelas, $jenis_kelamin, $alamat, $no_handphone, $foto_path, $status);

            if ($stmt->execute()) {
                $_SESSION['berhasil'] = "Data Siswa Berhasil Ditambahkan";
                // Redirect to a user list page, perhaps users.php or a dedicated siswa.php list
                header("Location: users.php");
                exit();
            } else {
                $_SESSION['validasi'] = "Terjadi kesalahan dalam menyimpan data ke database: " . $stmt->error;
                header("Location: tambah_siswa.php");
                exit();
            }
            $stmt->close();
        } else {
            $_SESSION['validasi'] = "Gagal mengunggah foto. Pastikan folder tujuan ada dan memiliki izin tulis (CHMOD 775 atau 777).";
            header("Location: tambah_siswa.php");
            exit();
        }
    }
}

// Restore form data after validation error
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']); // Clear after use

// Populate variables with submitted data or default empty values
$nama = $form_data['nama'] ?? '';
$no_absen = $form_data['no_absen'] ?? '';
$kelas = $form_data['kelas'] ?? '';
$jenis_kelamin = $form_data['jenis_kelamin'] ?? '';
$alamat = $form_data['alamat'] ?? '';
$no_handphone = $form_data['no_handphone'] ?? '';
$status = $form_data['status'] ?? '';
$username = $form_data['username'] ?? '';

include('../layout/header.php');
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Tambah Data Siswa</h2>
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
                            <input type="text" class="form-control" id="nis" name="nis" value="<?= $next_nis; ?>" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($username); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
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
                                    <option value="<?= $i; ?>" <?= (isset($no_absen) && (int)$no_absen === $i) ? 'selected' : '' ?>><?= $i; ?></option>
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
                                    echo '<option value="' . htmlspecialchars($c) . '" ' . ((isset($kelas) && $kelas === $c) ? 'selected' : '') . '>' . htmlspecialchars($c) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="jenis_kelamin">Jenis Kelamin</label>
                            <select name="jenis_kelamin" id="jenis_kelamin" class="form-control" required>
                                <option value="">--Pilih Jenis Kelamin--</option>
                                <option value="Laki-Laki" <?= (isset($jenis_kelamin) && $jenis_kelamin === 'Laki-Laki') ? 'selected' : '' ?>>Laki-Laki</option>
                                <option value="Perempuan" <?= (isset($jenis_kelamin) && $jenis_kelamin === 'Perempuan') ? 'selected' : '' ?>>Perempuan</option>
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
                                <option value="aktif" <?= (isset($status) && $status === 'aktif') ? 'selected' : '' ?>>Aktif</option>
                                <option value="tidak aktif" <?= (isset($status) && $status === 'tidak aktif') ? 'selected' : '' ?>>Tidak Aktif</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="foto">Foto</label>
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
<?php ob_end_flush(); ?>