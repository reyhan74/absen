<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../../auth/siswa/login.php?pesan=belum_login");
    exit;
}

include('../layout/header.php');
include_once('../../config.php');

$lokasi_presensi = [];
$lokasi_result = mysqli_query($conection, "SELECT * FROM lokasi_presensi");
while ($row = mysqli_fetch_assoc($lokasi_result)) {
    $lokasi_presensi[] = $row;
}

if (isset($_GET['pesan'])) {
    if ($_GET['pesan'] == "terimakasih_sudah_login") {
        $_SESSION['gagal'] = 'Terimakasih sudah login';
    } elseif ($_GET['pesan'] == "tolak_akses") {
        $_SESSION['gagal'] = 'Akses ke halaman ini ditolak';
    }
}

if (isset($_SESSION['gagal'])) {
    echo "<script>
        document.addEventListener(\"DOMContentLoaded\", function () {
            Swal.fire({ icon: 'error', title: 'Oops...', text: '" . htmlspecialchars($_SESSION['gagal'], ENT_QUOTES) . "' });
        });
    </script>";
    unset($_SESSION['gagal']);
}
?>

<style>
  .parent_date {
    display: flex;
    justify-content: center;
    gap: 5px;
    font-size: 20px;
  }
  .parent_clock {
    display: flex;
    justify-content: center;
    gap: 10px;
    font-size: 24px;
    font-weight: bold;
  }
</style>

<div class="page-body">
  <div class="container-xl">
    <div class="row justify-content-center mt-4">
      <div class="col-md-10">
        <div class="card">
          <div class="card-header text-center">
            <h5>Pilih Lokasi & Presensi</h5>
          </div>
          <div class="card-body">

            <form id="lokasiForm" class="mb-4 text-center">
              <label for="lokasiSelect" class="form-label fw-bold">Pilih Lokasi</label>
              <select name="lokasi" id="lokasiSelect" class="form-control w-50 mx-auto" required>
                <option value="">-- Pilih Lokasi --</option>
                <?php foreach ($lokasi_presensi as $lokasi): ?>
                  <option value="<?= htmlspecialchars($lokasi['nama_lokasi']) ?>">
                    <?= htmlspecialchars($lokasi['nama_lokasi']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </form>

            <div class="row">
              <!-- Presensi Masuk -->
              <div class="col-md-6 border-end">
                <div class="text-center">
                  <h6>Presensi Masuk</h6>
                  <div class="parent_date mb-1">
                    <div id="tanggal_masuk"></div><div id="bulan_masuk"></div><div id="tahun_masuk"></div>
                  </div>
                  <div class="parent_clock mb-2">
                    <div id="jam_masuk"></div>:<div id="menit_masuk"></div>:<div id="detik_masuk"></div>
                  </div>
                  <form action="../presensi/presensi_masuk.php" method="POST" class="form-masuk d-none">
                    <input type="hidden" name="nama_lokasi">
                    <input type="hidden" name="latitude_kantor">
                    <input type="hidden" name="longitude_kantor">
                    <input type="hidden" name="radius">
                    <input type="hidden" name="zona_waktu">
                    <input type="hidden" name="latitude_pegawai" id="latitude_pegawai_masuk">
                    <input type="hidden" name="longitude_pegawai" id="longitude_pegawai_masuk">
                    <input type="hidden" name="tanggal_masuk" value="<?= date('Y-m-d') ?>">
                    <input type="hidden" name="jam_masuk" value="<?= date('H:i:s') ?>">
                    <button type="submit" name="tombol_masuk" class="btn btn-primary" disabled>Masuk</button>
                  </form>
                </div>
              </div>

              <!-- Presensi Keluar -->
              <div class="col-md-6">
                <div class="text-center">
                  <h6>Presensi Keluar</h6>
                  <div class="parent_date mb-1">
                    <div id="tanggal_keluar"></div><div id="bulan_keluar"></div><div id="tahun_keluar"></div>
                  </div>
                  <div class="parent_clock mb-2">
                    <div id="jam_keluar"></div>:<div id="menit_keluar"></div>:<div id="detik_keluar"></div>
                  </div>
                  <form action="../presensi/presensi_keluar.php" method="POST" class="form-keluar d-none">
                    <input type="hidden" name="nama_lokasi">
                    <input type="hidden" name="latitude_kantor">
                    <input type="hidden" name="longitude_kantor">
                    <input type="hidden" name="radius">
                    <input type="hidden" name="zona_waktu">
                    <input type="hidden" name="latitude_pegawai" id="latitude_pegawai_keluar">
                    <input type="hidden" name="longitude_pegawai" id="longitude_pegawai_keluar">
                    <input type="hidden" name="tanggal_keluar" value="<?= date('Y-m-d') ?>">
                    <input type="hidden" name="jam_keluar" value="<?= date('H:i:s') ?>">
                    <button type="submit" name="tombol_keluar" class="btn btn-danger" disabled>Keluar</button>
                  </form>
                </div>
              </div>
            </div>
            <div id="status" class="mt-3 text-center text-muted fw-semibold"></div>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const namaBulan = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

function updateTime() {
  const now = new Date();
  const tanggal = now.getDate();
  const bulan = namaBulan[now.getMonth()];
  const tahun = now.getFullYear();
  const jam = now.getHours().toString().padStart(2, '0');
  const menit = now.getMinutes().toString().padStart(2, '0');
  const detik = now.getSeconds().toString().padStart(2, '0');

  const setTime = prefix => {
    document.getElementById(`tanggal_${prefix}`).innerText = tanggal;
    document.getElementById(`bulan_${prefix}`).innerText = bulan;
    document.getElementById(`tahun_${prefix}`).innerText = tahun;
    document.getElementById(`jam_${prefix}`).innerText = jam;
    document.getElementById(`menit_${prefix}`).innerText = menit;
    document.getElementById(`detik_${prefix}`).innerText = detik;
  };

  setTime("masuk");
  setTime("keluar");

  const jamSekarang = parseInt(jam);
  const formMasuk = document.querySelector(".form-masuk");
  const formKeluar = document.querySelector(".form-keluar");

  if (jamSekarang >= 6 && jamSekarang < 12) {
    formMasuk.classList.remove("d-none");
    formKeluar.classList.add("d-none");
  } else if (jamSekarang >= 12 && jamSekarang < 18) {
    formMasuk.classList.add("d-none");
    formKeluar.classList.remove("d-none");
  } else {
    formMasuk.classList.add("d-none");
    formKeluar.classList.add("d-none");
  }
}

setInterval(updateTime, 1000);
updateTime();
</script>

<?php include('../layout/foother.php'); ?>
