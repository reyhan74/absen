<?php
ob_start();
session_start();

if (!isset($_SESSION['login'])) {
    header("location: ../../auth/login.php?pesan=belum_login");
    exit();
} elseif ($_SESSION["role"] != 'admin') {
    header("location: ../../auth/login.php?pesan=tolak_akses");
    exit();
}

require_once('../../config.php');

$pesan_kesalahan = [];
$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $conection->prepare("SELECT * FROM guru WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        die("User tidak ditemukan!");
    }
} else {
    die("ID tidak valid!");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = htmlspecialchars($_POST['username']);
    $status = htmlspecialchars($_POST['status']);
    $role = htmlspecialchars($_POST['role']);
    $nama = htmlspecialchars($_POST['nama']);
    $jenis_kelamin = htmlspecialchars($_POST['jenis_kelamin']);
    $alamat = htmlspecialchars($_POST['alamat']);
    $no_handphone = htmlspecialchars($_POST['no_handphone']);
    $lokasi_presensi = htmlspecialchars($_POST['lokasi_presensi']);
    $password_baru = $_POST['password'] ?? '';
    $foto_path = $user['foto'];

    if (empty($username)) $pesan_kesalahan[] = "Username wajib diisi";
    if (empty($status)) $pesan_kesalahan[] = "Status wajib diisi";
    if (empty($role)) $pesan_kesalahan[] = "Role wajib diisi";
    if (empty($nama)) $pesan_kesalahan[] = "Nama wajib diisi";
    if (empty($jenis_kelamin)) $pesan_kesalahan[] = "Jenis Kelamin wajib diisi";
    if (empty($alamat)) $pesan_kesalahan[] = "Alamat wajib diisi";
    if (empty($no_handphone)) $pesan_kesalahan[] = "No Handphone wajib diisi";
    if (empty($lokasi_presensi)) $pesan_kesalahan[] = "Lokasi Presensi wajib diisi";

    if (!empty($_FILES['foto']['name'])) {
        $foto = $_FILES['foto']['name'];
        $foto_tmp = $_FILES['foto']['tmp_name'];
        $file_extension = strtolower(pathinfo($foto, PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($file_extension, $allowed_extensions)) {
            $pesan_kesalahan[] = "Format foto tidak valid! Hanya JPG, JPEG, PNG, atau GIF.";
        } else {
            if (!empty($user['foto']) && file_exists($user['foto'])) {
                unlink($user['foto']);
            }
            $new_file_name = time() . "_" . basename($foto);
            $foto_path = 'foto/' . $new_file_name;
            move_uploaded_file($foto_tmp, $foto_path);
        }
    }

    if (empty($pesan_kesalahan)) {
        if (!empty($password_baru)) {
            $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
            $stmt = $conection->prepare("UPDATE guru SET username=?, nama=?, jenis_kelamin=?, alamat=?, no_handphone=?, status=?, role=?, lokasi_presensi=?, foto=?, password=? WHERE id=?");
            $stmt->bind_param("ssssssssssi", $username, $nama, $jenis_kelamin, $alamat, $no_handphone, $status, $role, $lokasi_presensi, $foto_path, $hashed_password, $id);
        } else {
            $stmt = $conection->prepare("UPDATE guru SET username=?, nama=?, jenis_kelamin=?, alamat=?, no_handphone=?, status=?, role=?, lokasi_presensi=?, foto=? WHERE id=?");
            $stmt->bind_param("sssssssssi", $username, $nama, $jenis_kelamin, $alamat, $no_handphone, $status, $role, $lokasi_presensi, $foto_path, $id);
        }

        if ($stmt->execute()) {
            header("Location: users.php");
            exit();
        } else {
            $pesan_kesalahan[] = "Terjadi kesalahan saat menyimpan data.";
        }
    }
}

include('../layout/header.php');
?>

<!-- HTML FORM -->
<div class="page-header d-print-none">
    <div class="container-xl">
        <h2 class="page-title">Edit Guru</h2>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card col-md-8">
            <div class="card-body">
                <?php if (!empty($pesan_kesalahan)) : ?>
                    <div class="alert alert-danger">
                        <?php echo implode("<br>", $pesan_kesalahan); ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Username</label>
                            <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Password Baru (Opsional)</label>
                            <input type="password" class="form-control" name="password" placeholder="Biarkan kosong jika tidak ingin mengubah">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Status</label>
                            <select name="status" class="form-control" required>
                                <option value="aktif" <?= ($user['status'] == "aktif") ? "selected" : "" ?>>Aktif</option>
                                <option value="tidak-aktif" <?= ($user['status'] == "tidak-aktif") ? "selected" : "" ?>>Tidak Aktif</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Role</label>
                            <select name="role" class="form-control" required>
                                <option value="guru" <?= ($user['role'] == "guru") ? "selected" : "" ?>>Guru</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Nama</label>
                            <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-control" required>
                                <option value="laki-laki" <?= ($user['jenis_kelamin'] == "laki-laki") ? "selected" : "" ?>>Laki-laki</option>
                                <option value="perempuan" <?= ($user['jenis_kelamin'] == "perempuan") ? "selected" : "" ?>>Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>No Handphone</label>
                            <input type="text" class="form-control" name="no_handphone" value="<?= htmlspecialchars($user['no_handphone']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Alamat</label>
                            <input type="text" class="form-control" name="alamat" value="<?= htmlspecialchars($user['alamat']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Lokasi Presensi</label>
                            <input type="text" class="form-control" name="lokasi_presensi" value="<?= htmlspecialchars($user['lokasi_presensi']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Foto</label>
                            <input type="file" class="form-control" name="foto">
                            <?php if (!empty($user['foto'])) : ?>
                                <br><img src="<?= $user['foto'] ?>" width="100" alt="Foto User">
                            <?php endif; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="users.php" class="btn btn-secondary">Kembali</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include('../layout/foother.php'); ?>
