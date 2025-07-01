<?php
session_start();
if(!isset($_SESSION['login'])){
  header("location: ../../auth/login.php?pesan=belum_login");
} else if ($_SESSION["role"] != 'admin'){
  header("location:../../auth/login.php?pesan=tolak_akses");
}
include('../layout/header.php'); 
require_once('../../config.php');

$result = mysqli_query($conection, "SELECT * FROM lokasi_presensi ORDER BY id DESC"); 
?>
        <!-- Page header -->
        <div class="page-header d-print-none">
          <div class="container-xl">
            <div class="row g-2 align-items-center">
              <div class="col">
                <!-- Page pre-title -->
                
                <h2 class="page-title">
                  Lokasi Presensi
                </h2>
              </div>
              
            </div>
          </div>
        </div>
        <!-- Page body -->
      <div class="page-body">
        <div class="container-xl">
          <a href="tambah.php" class="btn btn-primary mb-3">Tambah Lokasi Presensi</a>
        <div class="card">
          <div class="card-body">
            <div id="table-default" class="table-responsive">
              <table class="table table-bordered">
                <thead class="text-center">
                  <tr class="text-center">
                      <th>No</th>
                      <th>Nama Lokasi</th>
                      <th>Tipe Lokasi</th>
                      <th>Latitude/Lagitude</th>
                      <th>Radius</th>
                      <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                    $no = 1;
                    while ($lokasi = mysqli_fetch_array($result)) : ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= $lokasi [ 'nama_lokasi'] ?></td>
                        <td><?= $lokasi ['alamat_lokasi'] ?></td>
                        <td><?= isset($lokasi['latitut']) && isset($lokasi['longitude']) ? $lokasi['latitut'] . '/' . $lokasi['longitude'] : 'Data Not Available' ?></td>

                        <td><?= $lokasi ['radius'] ?></td>
                        <td>
                          <a href="detail.php?id=<?= $lokasi['id'] ?>" class="badge badege-pill bg-primary">Detail</a>
                          <a href="edit.php?id=<?= $lokasi['id'] ?>" class="badge badege-pill bg-primary">Edit</a>
                          <a href="hapus.php?id=<?= $lokasi['id'] ?>" class="badge badege-pill bg-danger">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; // Properly close the while loop ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        </div>
      </div>
    </div>
</div>
<?php include('../layout/foother.php'); ?>