<?php
session_start();
if(!isset($_SESSION['login'])){
  header("location: ../../auth/login.php?pesan=belum_login");
} else if ($_SESSION["role"] != 'admin'){
  header("location:../../auth/login.php?pesan=tolak_akses");
}
include('../layout/header.php'); 
require_once('../../config.php');
$result = mysqli_query($conection, "SELECT * FROM guru");
?>

<!-- Page header -->
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">Data Guru</h2>
      </div>
    </div>
  </div>
</div>

<!-- Page body -->
<div class="page-body">
  <div class="container-xl">
    <a href="tambah.php" class="btn btn-primary mb-3">Tambah Guru</a>

    <div class="card">
      <div class="card-body">
        <div id="table-default" class="table-responsive">
          <table class="table table-bordered">
            <thead class="text-center">
              <tr>
                <th><button class="table-sort" data-sort="sort-no">No</button></th>
                <th><button class="table-sort" data-sort="sort-nama">Nama</button></th>
                <th><button class="table-sort" data-sort="sort-username">Username</button></th>
                <th><button class="table-sort" data-sort="sort-role">Role</button></th>
                <th><button class="table-sort" data-sort="sort-status">Status</button></th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody class="table-tbody text-center">
              <?php 
              $no = 1;
              while ($pegawai = mysqli_fetch_array($result)) : ?>
              <tr>
                <td class="sort-no"><?= $no++ ?></td>
                <td class="sort-nama"><?= $pegawai['nama'] ?></td>
                <td class="sort-username"><?= $pegawai['username'] ?></td>
                <td class="sort-role"><?= $pegawai['role'] ?></td>
                <td class="sort-status"><?= $pegawai['status'] ?></td>
                <td>
                 <!-- <a href="diteil.php?nis=<?= $pegawai['id'] ?>" class="badge badge-pill bg-info text-white text-decoration-none">Detail</a>-->
                  <a href="edit.php?id=<?= $pegawai['id'] ?>" class="badge badge-pill bg-primary text-white text-decoration-none">Edit</a>
                  <a href="hapus.php?id=<?= $pegawai['id'] ?>" class="badge badge-pill bg-danger text-white text-decoration-none" onclick="return confirm('Yakin ingin menghapus user ini?')">Hapus</a>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Tambahan CSS untuk mobile scroll jika belum ada -->
<style>
  @media (max-width: 768px) {
    .table {
      min-width: 600px;
    }
  }
</style>

<?php include('../layout/foother.php'); ?>
