<?php
ob_start(); // Start output buffering at the top
session_start();

// Session check for login status and user role
if (!isset($_SESSION['login'])) {
    header("location: ../../auth/login.php?pesan=belum_login");
    exit(); // Exit after redirection to stop further code execution
} else if ($_SESSION["role"] != 'admin') {
    header("location:../../auth/login.php?pesan=tolak_akses");
    exit(); // Exit after redirection
}

require_once('../../config.php');
$id = $_GET['id'];
// Check if ID is provided and is a valid number
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    

    // Fetch the current data from the database for the location
    $result = $conection->query("SELECT * FROM lokasi_presensi WHERE id = $id");
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
    } else {
        echo "Data not found!";
        exit();
    }

    if (isset($_POST['submit'])) {
        // Sanitize input data
        $nama_lokasi = htmlspecialchars($_POST['nama_lokasi']);
        $alamat_lokasi = htmlspecialchars($_POST['alamat_lokasi']);
        $latitut = htmlspecialchars($_POST['latitut']);
        $longitude = htmlspecialchars($_POST['longitude']);
        $radius = htmlspecialchars($_POST['radius']);
        $zona_waktu = htmlspecialchars($_POST['zona_waktu']);
        $jam_masuk = htmlspecialchars($_POST['jam_masuk']);
        $jam_pulang = htmlspecialchars($_POST['jam_pulang']);

        // Update query with prepared statements (to avoid SQL injection)
        $stmt = $conection->prepare("UPDATE lokasi_presensi 
                                        SET nama_lokasi = ?, alamat_lokasi = ?, latitut = ?, longitude = ?, 
                                            radius = ?, zona_waktu = ?, jam_masuk = ?, jam_pulang = ? 
                                        WHERE id = ?");
        $stmt->bind_param("ssssssssi", $nama_lokasi, $alamat_lokasi, $latitut, $longitude, $radius, $zona_waktu, $jam_masuk, $jam_pulang, $id);

        if ($stmt->execute()) {
            header("Location: lokasi_presensi.php");
            exit(); // Exit after redirection
        } else {
            echo "Update failed!";
        }
    }
} else {
    echo "Invalid ID!";
    exit();
}

include('../layout/header.php'); // Include header after all PHP logic
?>

<!-- Page header -->
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    Edit Lokasi Presensi
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
                        <input type="text" class="form-control" name="nama_lokasi" value="<?php echo $data['nama_lokasi']; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="">Alamat Lokasi</label>
                        <input type="text" class="form-control" name="alamat_lokasi" value="<?php echo $data['alamat_lokasi']; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="">Tipe Lokasi</label>
                        <select name="tipe_lokasi" class="form-control">
                            <option value="">--Pilih Tipe Lokasi--</option>
                            <option value="Pusat" <?php echo ($data['tipe_lokasi'] == 'Pusat') ? 'selected' : ''; ?>>Pusat</option>
                            <option value="Cabang" <?php echo ($data['tipe_lokasi'] == 'Cabang') ? 'selected' : ''; ?>>Cabang</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="">Latitude</label>
                        <input type="text" class="form-control" name="latitut" value="<?php echo $data['latitut']; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="">Longitude</label>
                        <input type="text" class="form-control" name="longitude" value="<?php echo $data['longitude']; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="">Radius</label>
                        <input type="number" class="form-control" name="radius" value="<?php echo $data['radius']; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="">Zona Waktu</label>
                        <select name="zona_waktu" class="form-control">
                            <option value="">--Pilih Zona Waktu--</option>
                            <option value="WIB" <?php echo ($data['zona_waktu'] == 'WIB') ? 'selected' : ''; ?>>WIB</option>
                            <option value="WITA" <?php echo ($data['zona_waktu'] == 'WITA') ? 'selected' : ''; ?>>WITA</option>
                            <option value="WIT" <?php echo ($data['zona_waktu'] == 'WIT') ? 'selected' : ''; ?>>WIT</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="">Jam Masuk</label>
                        <input type="time" class="form-control" name="jam_masuk" value="<?php echo $data['jam_masuk']; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="">Jam Pulang</label>
                        <input type="time" class="form-control" name="jam_pulang" value="<?php echo $data['jam_pulang']; ?>">
                    </div>

                    <button type="submit" class="btn btn-primary" name="submit">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>
