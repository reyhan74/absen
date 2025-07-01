<?php
session_start();
if(!isset($_SESSION['login'])){
  header("location: ../../auth/login.php?pesan=belum_login");
} else if ($_SESSION["role"] != 'admin'){
  header("location:../../auth/login.php?pesan=tolak_akses");
}
include('../layout/header.php'); 
require_once('../../config.php');
$id = $_GET['id'];
$result = mysqli_query($conection, "SELECT * FROM lokasi_presensi WHERE id=$id"); 
?>

<?php while($Lokasi = mysqli_fetch_array($result)) : ?>
        <!-- Page header -->
        <div class="page-header d-print-none">
          <div class="container-xl">
            <div class="row g-2 align-items-center">
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
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <table class="table">
                                    <tr>
                                        <td>Nama Lokasi</td>
                                        <td>: <?= $Lokasi['nama_lokasi'] ?></td>
                                    </tr>
                                    <tr>
                                        <td>Alamat Lokasi</td>
                                        <td>: <?= $Lokasi['alamat_lokasi'] ?></td>
                                    </tr>
                                    <tr>
                                        <td>Latitut</td>
                                        <td>: <?= $Lokasi['latitut'] ?></td>
                                    </tr>
                                    <tr>
                                        <td>longitude</td>
                                        <td>: <?= $Lokasi['longitude'] ?></td>
                                    </tr>
                                    <tr>
                                        <td>Radius</td>
                                        <td>: <?= $Lokasi['radius'] ?></td>
                                    </tr>
                                    <tr>
                                        <td>Zona Waktu</td>
                                        <td>: <?= $Lokasi['zona_waktu'] ?></td>
                                    </tr>
                                    <tr>
                                        <td>Jam Masuk</td>
                                        <td>: <?= $Lokasi['jam_masuk'] ?></td>
                                    </tr>
                                    <tr>
                                        <td>Jam Pulang</td>
                                        <td>: <?= $Lokasi['jam_pulang'] ?></td>
                                    </tr>
                                </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d403.1534170602218!2d<?= $Lokasi['longitude']?>!3d<?= $Lokasi['latitut']?>!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sid!2sid!4v1739350789695!5m2!1sid!2sid" width="500" height="350" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?php endwhile; ?>
