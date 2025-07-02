<?php
session_start();
if(!isset($_SESSION['login'])){
  header("location: ../../auth/login.php?pesan=belum_login");
}
include('./layout/header.php');
require_once('../../config.php');
?>
<main>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">
                    <div class="card card-jadwal">
                        <h2 class="text-center mb-4 text-primary">Jadwal Pelajaran Kelas X RPL</h2>
                        <p class="text-center text-muted">Tahun Ajaran 2024/2025</p>
                        
                        <div class="text-center mb-4">
                            <img src="../../assets/img/download.jpg" class="img-fluid img-thumbnail img-jadwal" alt="Jadwal Pelajaran Kelas X RPL">
                        </div>

                        <div class="alert alert-info text-center" role="alert">
                            <i class="bi bi-info-circle-fill me-2"></i> Jadwal dapat berubah sewaktu-waktu. Mohon periksa pengumuman terbaru.
                        </div>

                        <div class="d-grid gap-2 col-6 mx-auto mt-4">
                            <a href="img/jadwal-pelajaran.jpg" download class="btn btn-success btn-lg">
                                <i class="bi bi-download me-2"></i> Unduh Jadwal
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
<?php include('../layout/foother.php'); ?>