
<?php 
session_start();
if(!isset($_SESSION['login'])){
  header("location: ../../auth/login.php?pesan=belum_login");
} else if ($_SESSION["role"] != 'admin'){
  header("location:../../auth/login.php?pesan=tolak_akses");
}
include('../layout/header.php'); 
require_once('../../config.php');

$result = mysqli_query($conection, "SELECT * FROM jabatan ORDER BY id DESC");
?>

               <!-- Page header -->
               <div class="page-header d-print-none">
                    <div class="container-xl">
                      <div class="row g-2 align-items-center">
                        <div class="col">
                          <!-- Page pre-title -->
                          
                          <h2 class="page-title">
                            Data jabatan
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
            <a href="<?= base_url('admin/data_jabatan/tambah.php')?>" class="btn btn_primary">Tambah Data</a>
            <div class="row row-deck row-cards mt-2">
            <table class='table table-bordered'>
              <tr class="text-center">
                <th>No.</th>
                <th>Nama Jabatan</th>
                <th>Aksi</th>
              </tr>
              <?php if(mysqli_num_rows ($result)) : ?>
              <?php 
                $no = 1; 
                while ($jabatan = mysqli_fetch_array($result)) : ?> <!-- Fixed typo: mysqli_fecth_array to mysqli_fetch_array -->
                    <tr class="text-center">
                        <td><?= $no++ ?></td>
                        <td><?= $jabatan['jabatan'] ?></td> <!-- Fixed: Added echo to display jabatan -->
                        <td>
                        <a href="<?= base_url('admin/data_jabatan/edit.php?id=' . $jabatan['id']) ?>" class= "badge bg-primary">Edit</a>
                        <a href="<?= base_url('admin/data_jabatan/hapus.php?id=' . $jabatan['id']) ?>" class= "badge bg-primary">HAPUS</a>
                        </td>
                    </tr>
                <?php endwhile; // Properly close the while loop ?>
               <?php endif; ?>
            </table>
            </div>
          </div>
        </div>

        <?php include('../layout/foother.php'); ?>