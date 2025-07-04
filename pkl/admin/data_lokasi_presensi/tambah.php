<?php
ob_start(); // Start output buffering at the top
session_start();

if (!isset($_SESSION['login'])) {
    header("location: ../../auth/login.php?pesan=belum_login");
    exit(); // Make sure to exit after redirection
} else if ($_SESSION["role"] != 'admin') {
    header("location:../../auth/login.php?pesan=tolak_akses");
    exit(); // Make sure to exit after redirection
}

require_once('../../config.php');

if (isset($_POST['submit'])) {
    $nama_lokasi = htmlspecialchars($_POST['nama_lokasi']);
    $alamat_lokasi = htmlspecialchars($_POST['alamat_lokasi']);
    $latitut = htmlspecialchars($_POST['latitut']);
    $longitude = htmlspecialchars($_POST['longitude']);
    $radius = htmlspecialchars($_POST['radius']);
    $zona_waktu = htmlspecialchars($_POST['zona_waktu']);
    $jam_masuk = htmlspecialchars($_POST['jam_masuk']);
    $jam_pulang = htmlspecialchars($_POST['jam_pulang']);
    
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $pesan_kesalahan = []; // ✅ Ensure it's initialized as an array
    
        if (empty($nama_lokasi)){
            $pesan_kesalahan[] = "Nama lokasi wajib diisi";
        }
        if (empty($alamat_lokasi)){
            $pesan_kesalahan[] = "Alamat lokasi wajib diisi";
        }
        if (empty($latitut)){ 
            $pesan_kesalahan[] = "Latitude wajib diisi";
        }
        if (empty($longitude)){
            $pesan_kesalahan[] = "Longitude wajib diisi";
        }
        if (empty($radius)){
            $pesan_kesalahan[] = "Radius wajib diisi";
        }
        if (empty($zona_waktu)){
            $pesan_kesalahan[] = "Zona waktu wajib diisi";
        }
        if (empty($jam_masuk)){
            $pesan_kesalahan[] = "Jam masuk wajib diisi";
        }
        if (empty($jam_pulang)){
            $pesan_kesalahan[] = "Jam pulang wajib diisi"; // ✅ Fixed here
        }
    
        if (!empty($pesan_kesalahan)) {
            $_SESSION['validasi'] = implode("<br>", $pesan_kesalahan);
        }else{
            $stmt = $conection->prepare("INSERT INTO lokasi_presensi (nama_lokasi, alamat_lokasi, latitut, longitude, radius, zona_waktu, jam_masuk, jam_pulang) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param("ssssssss", $nama_lokasi, $alamat_lokasi, $latitut, $longitude, $radius, $zona_waktu, $jam_masuk, $jam_pulang);

if ($stmt->execute()) {
    $_SESSION['berhasil'] = "Data Berhasil Disimpan";
    header("Location: lokasi_presensi.php");
    exit();
} else {
    $_SESSION['validasi'] = "Terjadi kesalahan dalam menyimpan data.";
}

$stmt->close();

        }
    }
    
}

include('../layout/header.php'); // Include header after all PHP logic
?>

<!-- Your HTML form code below -->

<!-- Page header -->
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    Tambah Lokasi Presensi
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="#" class="btn btn-primary d-sm-none btn-icon" data-bs-toggle="modal" data-bs-target="#modal-report" aria-label="Create new report">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M12 5l0 14"/>
                            <path d="M5 12l14 0"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Page body -->
<div class="page-body">
    <div class="container-xl">
        <div class="card col-md-6">
            <div class="card-body">
                <form action="" method="POST">
                    <div class="mb-3">
                        <label for="">Nama lokasi</label>
                        <input type="text" class="form-control" name="nama_lokasi" 
                        value="<?php if (isset($_POST['nama_lokasi'])) echo $_POST['nama_lokasi']; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="">Alamat Lokasi</label>
                        <input type="text" class="form-control" name="alamat_lokasi" 
                        value="<?php if (isset($_POST['alamat_lokasi'])) echo $_POST['alamat_lokasi']; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="">Latitude</label>
                        <input type="text" class="form-control" name="latitut"
                        value="<?php if (isset($_POST['latitut'])) echo $_POST['latitut']; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="">Longitude</label>
                        <input type="text" class="form-control" name="longitude"
                        value="<?php if (isset($_POST['longitude'])) echo $_POST['longitude']; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="">Radius</label>
                        <input type="number" class="form-control" name="radius"
                        value="<?php if (isset($_POST['radius'])) echo $_POST['radius']; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="">Zona Waktu</label>
                        <select name="zona_waktu" class="form-control">
                            <option value="">--Pilih Zona Waktu--</option>
                            <option <?php if (isset($_POST['zona_waktu']) && $_POST['zona_waktu'] === 'WIB') {echo 'selected';} ?> value="WIB">WIB</option>
                            <option <?php if (isset($_POST['zona_waktu']) && $_POST['zona_waktu'] === 'WITA') {echo 'selected';} ?> value="WITA">WITA</option>
                            <option <?php if (isset($_POST['zona_waktu']) && $_POST['zona_waktu'] === 'WIT') {echo 'selected';} ?> value="WIT">WIT</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="">Jam Masuk</label>
                        <input type="time" class="form-control" name="jam_masuk"
                        value="<?php if (isset($_POST['jam_masuk'])) echo $_POST['jam_masuk']; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="">Jam Pulang</label>
                        <input type="time" class="form-control" name="jam_pulang"
                        value="<?php if (isset($_POST['jam_pulang'])) echo $_POST['jam_pulang']; ?>">
                    </div>

                    <button type="submit" class="btn btn-primary" name="submit">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include('../layout/foother.php'); ?>