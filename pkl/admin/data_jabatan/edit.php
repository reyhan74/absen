
<?php
require_once('../../config.php');
session_start();
ob_start();

if(!isset($_SESSION['login'])){
  header("location: ../../auth/login.php?pesan=belum_login");
} else if ($_SESSION["role"] != 'admin'){
  header("location:../../auth/login.php?pesan=tolak_akses");
}

include('../layout/header.php');

$id = $_GET['id'];
$sql = "SELECT * FROM jabatan WHERE id = $id";
$result = $conection->query($sql);
$row = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jabatan = $_POST['jabatan']; // Add the missing semicolon

    // Fix SQL query to use $jabatan correctly
    $sql = "UPDATE jabatan SET jabatan = '$jabatan' WHERE id = $id"; // No single quotes around $jabatan

    if ($conection->query($sql) === TRUE) {
        header('location: jabatan.php'); // Add missing parentheses and semicolon
    } else {
        echo "Error: " . $conection->error; // Add error handling for failure
    }
}
?>


        <!-- Page header -->
        <div class="page-header d-print-none">
          <div class="container-xl">
            <div class="row g-2 align-items-center">
              <div class="col">
                <!-- Page pre-title -->
                
                <h2 class="page-title">
                  Edit Data
                </h2>
              </div>
              <!-- Page title actions -->
              <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                  <a href="#" class="btn btn-primary d-sm-none btn-icon" data-bs-toggle="modal" data-bs-target="#modal-report" aria-label="Create new report">
                    <!-- Download SVG icon from http://tabler-icons.io/i/plus -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Page body -->
        <div class="page-body">
          <div class="container-xl">
            <div class="card col md-6">
              <div class="card-body">
                <form action="" method="POST">

                  <div class="mb-3">
                    <label for="">Nama Jabatan</label>
                    <input type="text" class="form-control" name="jabatan" value=<?=$row ['jabatan'] ?>>
                  </div>
                  <button type="submit" name="submit" class="btn btn-primary">Ubah</button>
                </form>
              </div>
            </div>
          </div>
        </div>
        <?php include('../layout/foother.php'); ?>