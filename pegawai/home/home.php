<?php
session_start();
if(!isset($_SESSION['login'])){
  header("location: ../../auth/login.php?pesan=belum_login");
} else if ($_SESSION["role"] != 'guru'){
  header("location:../../auth/login.php?pesan=tolak_akses");
}

include('../layout/header.php');
require_once('../../config.php');

// Statistik presensi hari ini
$total_siswa = mysqli_num_rows(mysqli_query($conection, "SELECT id FROM siswa"));
$masuk_today = mysqli_num_rows(mysqli_query($conection, "SELECT id FROM presensi WHERE DATE(tanggal_masuk) = CURDATE()"));
$alfa_today = $total_siswa - $masuk_today;
$terlambat_today = mysqli_num_rows(mysqli_query($conection, "SELECT id FROM presensi WHERE DATE(tanggal_masuk) = CURDATE() AND jam_masuk > '07:00:00'"));
?>

<!-- Page header -->
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">Dashboard</h2>
      </div>
    </div>
  </div>
</div>

<!-- Page body -->
<div class="page-body">
  <div class="container-xl">
    <div class="row row-deck row-cards">
      <div class="col-12">
        <div class="row row-cards">

          <!-- Card: Jumlah Masuk -->
          <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col-auto">
                    <span class="bg-success text-white avatar">
                      <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                           viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                           stroke-linecap="round" stroke-linejoin="round">
                        <path d="M0 0h24v24H0z" fill="none"/>
                        <path d="M9 12h12"/><path d="M15 16l6 -4l-6 -4"/><path d="M4 4v16"/>
                      </svg>
                    </span>
                  </div>
                  <div class="col">
                    <div class="font-weight-medium"><?= $masuk_today ?> Masuk</div>
                    <div class="text-secondary">Hari Ini</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Card: Jumlah Alfa -->
          <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col-auto">
                    <span class="bg-danger text-white avatar">
                      <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                           viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                           stroke-linecap="round" stroke-linejoin="round">
                        <path d="M0 0h24v24H0z" fill="none"/>
                        <path d="M12 3v18"/><path d="M5 12h14"/>
                      </svg>
                    </span>
                  </div>
                  <div class="col">
                    <div class="font-weight-medium"><?= $alfa_today ?> Alfa</div>
                    <div class="text-secondary">Hari Ini</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Card: Jumlah Terlambat -->
          <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col-auto">
                    <span class="bg-warning text-white avatar">
                      <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                           viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                           stroke-linecap="round" stroke-linejoin="round">
                        <path d="M0 0h24v24H0z" fill="none"/>
                        <path d="M12 7v5l3 3"/><circle cx="12" cy="12" r="9"/>
                      </svg>
                    </span>
                  </div>
                  <div class="col">
                    <div class="font-weight-medium"><?= $terlambat_today ?> Terlambat</div>
                    <div class="text-secondary">Hari Ini</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

           <!-- Izin -->
          <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col-auto">
                    <span class="bg-info text-white avatar">
                      <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                           viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                           stroke-linecap="round" stroke-linejoin="round">
                        <path d="M0 0h24v24H0z" fill="none"/>
                        <path d="M12 12v.01"/><path d="M12 3a9 9 0 1 0 9 9a9 9 0 0 0 -9 -9z"/><path d="M12 7v3"/>
                      </svg>
                    </span>
                  </div>
                  <div class="col">
                    <div class="font-weight-medium"> 0 Izin</div>
                    <div class="text-secondary">Hari Ini</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Tambahkan kartu lainnya jika perlu -->

        </div>
      </div>
    </div>
  </div>
</div>

<?php include('../layout/foother.php'); ?>
