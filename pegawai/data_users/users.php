<?php
session_start();
if (!isset($_SESSION['login'])) {
  header("location: ../../auth/login.php?pesan=belum_login");
} else if ($_SESSION["role"] != 'guru') {
  header("location:../../auth/login.php?pesan=tolak_akses");
}
include('../layout/header.php'); 
require_once('../../config.php');

$result = mysqli_query($conection, "SELECT * FROM siswa");
?>

<!-- Page header -->
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">Data Siswa</h2>
      </div>
      <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">
          <a href="#" class="btn btn-primary d-sm-none btn-icon" data-bs-toggle="modal" data-bs-target="#modal-report" aria-label="Tambah Siswa">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
              <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
              <path d="M12 5l0 14" />
              <path d="M5 12l14 0" />
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
    <a href="./tambah.php" class="btn btn-primary mb-3">Tambah Siswa</a>

    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered">
            <thead class="text-center">
              <tr>
                <th>NIS</th>
                <th>Absen</th>
                <th>Nama</th>
                <th>Kelas</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($siswa = mysqli_fetch_array($result)) : ?>
              <tr>
                <td><?= $siswa['nis'] ?></td>
                <td><?= $siswa['no_absen'] ?></td>
                <td><?= $siswa['nama'] ?></td>
                <td><?= $siswa['kelas'] ?></td>
                <td><?= $siswa['status'] ?></td>
                <td>
                  <a href="diteil.php?nis=<?= $siswa['nis'] ?>" class="badge badge-pill bg-info text-white text-decoration-none">Detail</a>
                  <a href="edit.php?nis=<?= $siswa['nis'] ?>" class="badge badge-pill bg-primary text-white text-decoration-none">Edit</a>
                  <a href="hapus.php?id=<?= $siswa['id'] ?>" class="badge badge-pill bg-danger text-white text-decoration-none" onclick="return confirm('Yakin ingin menghapus data siswa ini?')">Hapus</a>
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

<!-- CSS responsif tambahan -->
<style>
  @media (max-width: 768px) {
    .table {
      min-width: 600px;
    }
  }
</style>

<?php include('../layout/foother.php'); ?>
