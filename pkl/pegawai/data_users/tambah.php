<?php
ob_start();
session_start();

if (!isset($_SESSION['login'])) {
    header("location: ../../auth/login.php?pesan=belum_login");
    exit();
} elseif ($_SESSION["role"] != 'guru') {
    header("location: ../../auth/login.php?pesan=tolak_akses");
    exit();
}

require_once('../../config.php');

// Get the last NIS from the database
$result = $conection->query("SELECT nis FROM siswa ORDER BY nis DESC LIMIT 1");
$last_nis = $result->fetch_assoc();
$next_nis = $last_nis ? str_pad((int)$last_nis['nis'] + 1, 6, '0', STR_PAD_LEFT) : '000001'; // Start from 000001 if no records exist

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit'])) {
    $nis = $next_nis; // Use the automatically generated NIS
    $no_absen = htmlspecialchars($_POST['no_absen']);
    $nama = htmlspecialchars($_POST['nama']);
    $jenis_kelamin = htmlspecialchars($_POST['jenis_kelamin']);
    $alamat = htmlspecialchars($_POST['alamat']);
    $kelas = htmlspecialchars($_POST['kelas']);
    $no_handphone = htmlspecialchars($_POST['no_handphone']);
    $lokasi_presensi = htmlspecialchars($_POST['lokasi_presensi']);
    $status = htmlspecialchars($_POST['status']);
    $foto = $_FILES['foto']['name'];
    $foto_tmp = $_FILES['foto']['tmp_name'];

    $errors = [];

    if (empty($no_absen))$errors[] = "Nomor Absen wajib diisi";
    if (empty($nama)) $errors[] = "Nama wajib diisi";
    if (empty($jenis_kelamin)) $errors[] = "Jenis Kelamin wajib diisi";
    if (empty($alamat)) $errors[] = "Alamat wajib diisi";
    if (empty($no_handphone)) $errors[] = "No Handphone wajib diisi";
    if (empty($lokasi_presensi)) $errors[] = "Lokasi Presensi wajib diisi";
    if (empty($status)) $errors[] = "Status wajib diisi";
    if (empty($foto)) $errors[] = "Foto wajib diisi";

    if (!empty($errors)) {
    $_SESSION['validasi'] = implode("<br>", $errors);
    } else {
        // Tangani upload foto jika ada
        if (!empty($foto)) {
            $foto_path = '../../assets/img/profile_siswa/' . basename($foto);
            if (!move_uploaded_file($foto_tmp, $foto_path)) {
                $_SESSION['validasi'] = "Gagal mengunggah foto.";
                header("Location: tambah_siswa.php");
                exit();
            }
        } else {
            $foto_path = ''; // atau bisa diset ke foto default
        }

        // Siapkan query INSERT
        $stmt = $conection->prepare("INSERT INTO siswa (nis, nama, no_absen, kelas, jenis_kelamin, alamat, no_handphone, lokasi_presensi, foto, status) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssss", $nis, $nama, $no_absen, $kelas, $jenis_kelamin, $alamat, $no_handphone, $lokasi_presensi, $foto_path, $status);

        // Eksekusi
        if ($stmt->execute()) {
            $_SESSION['berhasil'] = "Data Siswa Berhasil Ditambahkan";
            header("Location: users.php");
            exit();
        } else {
            $_SESSION['validasi'] = "Terjadi kesalahan dalam menyimpan data.";
            header("Location: tambah_siswa.php");
            exit();
        }

        $stmt->close();
    }

}

include('../layout/header.php');
?>

<!-- Page header -->
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">Tambah Data Siswa</h2>
            </div>
        </div>
    </div>
</div>

<!-- Page body -->
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
                            <label for="">NIS</label>
                            <input type="text" class="form-control" name="nis" value="<?= $next_nis; ?>" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="">Nama</label>
                            <input type="text" class="form-control" name="nama" value="<?= $_POST['nama'] ?? '' ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="">Nomer Absen</label>
                            <select name="no_absen" class="form-control" required>
                                <option value="">--Pilih Nomor Absen--</option>
                                <option value="1" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '1') ? 'selected' : '' ?>>1</option>
                                <option value="2" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '2') ? 'selected' : '' ?>>2</option>
                                <option value="3" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '3') ? 'selected' : '' ?>>3</option>
                                <option value="4" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '4') ? 'selected' : '' ?>>4</option>
                                <option value="5" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '5') ? 'selected' : '' ?>>5</option>
                                <option value="6" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '6') ? 'selected' : '' ?>>6</option>
                                <option value="7" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '7') ? 'selected' : '' ?>>7</option>
                                <option value="8" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '8') ? 'selected' : '' ?>>8</option>
                                <option value="9" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '9') ? 'selected' : '' ?>>9</option>
                                <option value="10" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '10') ? 'selected' : '' ?>>10</option>
                                <option value="11" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '11') ? 'selected' : '' ?>>11</option>
                                <option value="12" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '12') ? 'selected' : '' ?>>12</option>
                                <option value="13" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '13') ? 'selected' : '' ?>>13</option>
                                <option value="14" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '14') ? 'selected' : '' ?>>14</option>
                                <option value="15" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '15') ? 'selected' : '' ?>>15</option>
                                <option value="16" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '16') ? 'selected' : '' ?>>16</option>
                                <option value="17" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '17') ? 'selected' : '' ?>>17</option>
                                <option value="18" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '18') ? 'selected' : '' ?>>18</option>
                                <option value="19" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '19') ? 'selected' : '' ?>>19</option>
                                <option value="20" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '20') ? 'selected' : '' ?>>20</option>
                                <option value="21" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '21') ? 'selected' : '' ?>>21</option>
                                <option value="22" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '22') ? 'selected' : '' ?>>22</option>
                                <option value="23" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '23') ? 'selected' : '' ?>>23</option>
                                <option value="24" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '24') ? 'selected' : '' ?>>24</option>
                                <option value="25" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '25') ? 'selected' : '' ?>>25</option>
                                <option value="26" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '26') ? 'selected' : '' ?>>26</option>
                                <option value="27" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '27') ? 'selected' : '' ?>>27</option>
                                <option value="28" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '28') ? 'selected' : '' ?>>28</option>
                                <option value="29" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '29') ? 'selected' : '' ?>>29</option>
                                <option value="30" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '30') ? 'selected' : '' ?>>30</option>
                                <option value="31" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '31') ? 'selected' : '' ?>>31</option>
                                <option value="32" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '32') ? 'selected' : '' ?>>32</option>
                                <option value="33" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '33') ? 'selected' : '' ?>>33</option>
                                <option value="34" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '34') ? 'selected' : '' ?>>34</option>
                                <option value="35" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '35') ? 'selected' : '' ?>>35</option>
                                <option value="36" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '36') ? 'selected' : '' ?>>36</option>
                                <option value="37" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '37') ? 'selected' : '' ?>>37</option>
                                <option value="38" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '38') ? 'selected' : '' ?>>38</option>
                                <option value="39" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '39') ? 'selected' : '' ?>>39</option>
                                <option value="40" <?= (isset($_POST['no_absen']) && $_POST['no_absen'] === '40') ? 'selected' : '' ?>>40</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="">Pilih Kelas</label>
                            <select name="kelas" class="form-control">
                                <option value="">--Pilih Kelas--</option>
                                <option value="X TKJ 1" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'X TKJ 1') ? 'selected' : '' ?>>X TKJ 1</option>
                                <option value="X TKJ 2" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'X TKJ 2') ? 'selected' : '' ?>>X TKJ 2</option>
                                <option value="X TKJ 3" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'X TKJ 3') ? 'selected' : '' ?>>X TKJ 3</option>
                                <option value="X TPM 1" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'X TPM 1') ? 'selected' : '' ?>>X TPM 1</option>
                                <option value="X TPM 2" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'X TPM 2') ? 'selected' : '' ?>>X TPM 2</option>
                                <option value="X TPM 3" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'X TPM 3') ? 'selected' : '' ?>>X TPM 3</option>
                                <option value="X TPM 4" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'X TPM 4') ? 'selected' : '' ?>>X TPM 4</option>
                                <option value="X TPM 5" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'X TPM 5') ? 'selected' : '' ?>>X TPM 5</option>
                                <option value="X TKR 1" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'X TKR 1') ? 'selected' : '' ?>>X TKR 1</option>
                                <option value="X TKR 2" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'X TKR 2') ? 'selected' : '' ?>>X TKR 2</option>
                                <option value="X TKR 3" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'X TKR 3') ? 'selected' : '' ?>>X TKR 3</option>
                                <option value="X TITL 1" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'X TITL 1') ? 'selected' : '' ?>>X TITL 1</option>
                                <option value="X TITL 2" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'X TITL 2') ? 'selected' : '' ?>>X TITL 2</option>
                                <option value="X TITL 3" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'X TITL 3') ? 'selected' : '' ?>>X TITL 3</option>
                                <option value="X DPIB 1" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'X DPIB 1') ? 'selected' : '' ?>>X DPIB 1</option>
                                <option value="X DPIB 2" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'X DPIB 2') ? 'selected' : '' ?>>X DPIB 1</option>
                                <option value="X TOI 1" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'X TOI 1') ? 'selected' : '' ?>>X TOI 1</option>
                                <option value="X TOI 2" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'X TOI 2') ? 'selected' : '' ?>>X TOI 2</option>
                                <option value="XI TKJ 1" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XI TKJ 1') ? 'selected' : '' ?>>XI TKJ 1</option>
                                <option value="XI TKJ 2" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XI TKJ 2') ? 'selected' : '' ?>>XI TKJ 2</option>
                                <option value="XI TKJ 3" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XI TKJ 3') ? 'selected' : '' ?>>XI TKJ 3</option>
                                <option value="XI TPM 1" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XI TPM 1') ? 'selected' : '' ?>>XI TPM 1</option>
                                <option value="XI TPM 2" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XI TPM 2') ? 'selected' : '' ?>>XI TPM 2</option>
                                <option value="XI TPM 3" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XI TPM 3') ? 'selected' : '' ?>>XI TPM 3</option>
                                <option value="XI TPM 4" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XI TPM 4') ? 'selected' : '' ?>>XI TPM 4</option>
                                <option value="XI TPM 5" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XI TPM 5') ? 'selected' : '' ?>>XI TPM 5</option>
                                <option value="XI TKR 1" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XI TKR 1') ? 'selected' : '' ?>>XI TKR 1</option>
                                <option value="XI TKR 2" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XI TKR 2') ? 'selected' : '' ?>>XI TKR 2</option>
                                <option value="XI TKR 3" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XI TKR 3') ? 'selected' : '' ?>>XI TKR 3</option>
                                <option value="XI TITL 1" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XI TITL 1') ? 'selected' : '' ?>>XI TITL 1</option>
                                <option value="XI TITL 2" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XI TITL 2') ? 'selected' : '' ?>>XI TITL 2</option>
                                <option value="XI TITL 3" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XI TITL 3') ? 'selected' : '' ?>>XI TITL 3</option>
                                <option value="XI DPIB 1" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XI DPIB 1') ? 'selected' : '' ?>>XI DPIB 1</option>
                                <option value="XI DPIB 2" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XI DPIB 2') ? 'selected' : '' ?>>XI DPIB 1</option>
                                <option value="XI TOI 1" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XI TOI 1') ? 'selected' : '' ?>>XI TOI 1</option>
                                <option value="XI TOI 2" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XI TOI 2') ? 'selected' : '' ?>>XI TOI 2</option>
                                <option value="XII TKJ 1" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XII TKJ 1') ? 'selected' : '' ?>>XII TKJ 1</option>
                                <option value="XII TKJ 2" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XII TKJ 2') ? 'selected' : '' ?>>XII TKJ 2</option>
                                <option value="XII TKJ 3" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XII TKJ 3') ? 'selected' : '' ?>>XII TKJ 3</option>
                                <option value="XII TPM 1" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XII TPM 1') ? 'selected' : '' ?>>XII TPM 1</option>
                                <option value="XII TPM 2" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XII TPM 2') ? 'selected' : '' ?>>XII TPM 2</option>
                                <option value="XII TPM 3" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XII TPM 3') ? 'selected' : '' ?>>XII TPM 3</option>
                                <option value="XII TPM 4" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XII TPM 4') ? 'selected' : '' ?>>XII TPM 4</option>
                                <option value="XII TPM 5" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XII TPM 5') ? 'selected' : '' ?>>XII TPM 5</option>
                                <option value="XII TKR 1" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XII TKR 1') ? 'selected' : '' ?>>XII TKR 1</option>
                                <option value="XII TKR 2" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XII TKR 2') ? 'selected' : '' ?>>XII TKR 2</option>
                                <option value="XII TKR 3" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XII TKR 3') ? 'selected' : '' ?>>XII TKR 3</option>
                                <option value="XII TITL 1" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XII TITL 1') ? 'selected' : '' ?>>XII TITL 1</option>
                                <option value="XII TITL 2" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XII TITL 2') ? 'selected' : '' ?>>XII TITL 2</option>
                                <option value="XII TITL 3" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XII TITL 3') ? 'selected' : '' ?>>XII TITL 3</option>
                                <option value="XII DPIB 1" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XII DPIB 1') ? 'selected' : '' ?>>XII DPIB 1</option>
                                <option value="XII DPIB 2" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XII DPIB 2') ? 'selected' : '' ?>>XII DPIB 2</option>
                                <option value="XII TOI 1" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XII TOI 1') ? 'selected' : '' ?>>XII TOI 1</option>
                                <option value="XII TOI 2" <?= (isset($_POST['kelas']) && $_POST['kelas'] === 'XII TOI 2') ? 'selected' : '' ?>>XII TOI 2</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-control">
                                <option value="">--Pilih Jenis Kelamin--</option>
                                <option value="Laki-Laki" <?= (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin'] === 'Laki-Laki') ? 'selected' : '' ?>>Laki-Laki</option>
                                <option value="Perempuan" <?= (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin'] === 'Perempuan') ? 'selected' : '' ?>>Perempuan</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="">Alamat</label>
                            <input type="text" class="form-control" name="alamat" value="<?= $_POST['alamat'] ?? '' ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="">No Handphone</label>
                            <input type="text" class="form-control" name="no_handphone" value="<?= $_POST['no_handphone'] ?? '' ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="">Lokasi Presensi</label>
                            <select name="lokasi_presensi" class="form-control">
                                <option value="">--Pilih Lokasi Presensi--</option>
                                <option value="Kampus 1" <?= (isset($_POST['lokasi_presensi']) && $_POST['lokasi_presensi'] === 'Kampus 1') ? 'selected' : '' ?>>Kampus 1</option>
                                <option value="Kampus 2" <?= (isset($_POST['lokasi_presensi']) && $_POST['lokasi_presensi'] === 'Kampus 2') ? 'selected' : '' ?>>Kampus 2</option>
                                <option value="Kampus 3" <?= (isset($_POST['lokasi_presensi']) && $_POST['lokasi_presensi'] === 'Kampus 3') ? 'selected' : '' ?>>Kampus 3</option>
                                <option value="Kampus 4" <?= (isset($_POST['lokasi_presensi']) && $_POST['lokasi_presensi'] === 'Kampus 4') ? 'selected' : '' ?>>Kampus 4</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="">Status</label>
                            <select name="status" class="form-control">
                                <option value="">--Pilih Status--</option>
                                <option value="aktif" <?= (isset($_POST['status']) && $_POST['status'] === 'aktif') ? 'selected' : '' ?>>Aktif</option>
                                <option value="tidak aktif" <?= (isset($_POST['status']) && $_POST['status'] === 'tidak aktif') ? 'selected' : '' ?>>Tidak Aktif</option>
                            </select>
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