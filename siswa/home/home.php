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
?>

<?php if (isset($_SESSION['gagal'])): ?>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    Swal.fire({
      icon: "error",
      title: "Oops...",
      text: "<?= htmlspecialchars($_SESSION['gagal'], ENT_QUOTES); ?>",
    });
  });
</script>
<?php unset($_SESSION['gagal']); endif; ?>

<style>
  .parent_date {
    display: grid;
    grid-template-columns: auto auto auto auto auto;
    font-size: 20px;
    text-align: center;
    justify-content: center;
  }
  .parent_clock {
    display: flex;
    justify-content: center;
    font-size: 24px;
    font-weight: bold;
    gap: 10px;
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

            <!-- Pilih Lokasi -->
            <form id="lokasiForm" class="mb-4 text-center">
              <label for="lokasiSelect" class="form-label fw-bold">Pilih Lokasi</label>
              <select name="lokasi" id="lokasiSelect" class="form-control w-50 mx-auto" required>
                <option value="">-- Pilih Lokasi --</option>
                <?php foreach ($lokasi_presensi as $lokasi): ?>
                  <option value='<?= json_encode($lokasi) ?>'>
                    <?= htmlspecialchars($lokasi['nama_lokasi']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </form>

            <div class="row">
              <!-- Presensi Masuk -->
              <div class="col-md-6 border-end" id="masukSection" style="display: none;">
                <div class="text-center">
                  <h6>Presensi Masuk</h6>
                  <div class="parent_date mb-1">
                    <div id="tanggal_masuk"></div><div class="ms-1"></div>
                    <div id="bulan_masuk"></div><div class="ms-1"></div>
                    <div id="tahun_masuk"></div>
                  </div>
                  <div class="parent_clock mb-2">
                    <div id="jam_masuk"></div>:<div id="menit_masuk"></div>:<div id="detik_masuk"></div>
                  </div>
                  <form action="../presensi/presensi_masuk.php" method="POST">
                    <input type="hidden" name="nama_lokasi">
                    <input type="hidden" name="latitude_kantor">
                    <input type="hidden" name="longitude_kantor">
                    <input type="hidden" name="radius">
                    <input type="hidden" name="zona_waktu">
                    <input type="hidden" name="latitude_pegawai" id="latitude_pegawai_masuk">
                    <input type="hidden" name="longitude_pegawai" id="longitude_pegawai_masuk">
                    <input type="hidden" name="tanggal_masuk" value="<?= date('Y-m-d') ?>">
                    <input type="hidden" name="jam_masuk" value="<?= date('H:i:s') ?>">
                    <input type="hidden" name="status_masuk" id="status_masuk" value="">
                    <button type="submit" name="tombol_masuk" class="btn btn-primary" disabled>Masuk</button>
                  </form>
                </div>
              </div>

              <!-- Presensi Keluar -->
              <div class="col-md-6" id="keluarSection" style="display: none;">
                <div class="text-center">
                  <h6>Presensi Keluar</h6>
                  <div class="parent_date mb-1">
                    <div id="tanggal_keluar"></div><div class="ms-1"></div>
                    <div id="bulan_keluar"></div><div class="ms-1"></div>
                    <div id="tahun_keluar"></div>
                  </div>
                  <div class="parent_clock mb-2">
                    <div id="jam_keluar"></div>:<div id="menit_keluar"></div>:<div id="detik_keluar"></div>
                  </div>
                  <form action="../presensi/presensi_keluar.php" method="POST">
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
  const namaBulan = ["Januari", "Februari", "Maret", "April", "Mei", "Juni",
    "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

  let jamMasukDB = null;
  let jamPulangDB = null;

  function updateTime() {
    const waktu = new Date();
    const jam = waktu.getHours().toString().padStart(2, '0');
    const menit = waktu.getMinutes().toString().padStart(2, '0');
    const jamMenit = `${jam}:${menit}`;

    const tanggal = waktu.getDate();
    const bulan = namaBulan[waktu.getMonth()];
    const tahun = waktu.getFullYear();
    const detik = waktu.getSeconds().toString().padStart(2, '0');

    ["masuk", "keluar"].forEach(prefix => {
      document.getElementById(`tanggal_${prefix}`).innerHTML = tanggal;
      document.getElementById(`bulan_${prefix}`).innerHTML = bulan;
      document.getElementById(`tahun_${prefix}`).innerHTML = tahun;
      document.getElementById(`jam_${prefix}`).innerHTML = jam;
      document.getElementById(`menit_${prefix}`).innerHTML = menit;
      document.getElementById(`detik_${prefix}`).innerHTML = detik;
    });

    if (jamMasukDB && jamPulangDB) {
      if (jamMenit >= jamMasukDB && jamMenit < jamPulangDB) {
        document.getElementById("masukSection").style.display = "block";
        document.getElementById("keluarSection").style.display = "none";
        document.getElementById("status_masuk").value = (jamMenit > jamMasukDB) ? "Terlambat" : "Tepat Waktu";
      } else if (jamMenit >= jamPulangDB) {
        document.getElementById("masukSection").style.display = "none";
        document.getElementById("keluarSection").style.display = "block";
      } else {
        document.getElementById("masukSection").style.display = "none";
        document.getElementById("keluarSection").style.display = "none";
      }
    }
  }

  setInterval(updateTime, 1000);
  updateTime();

  document.getElementById("lokasiSelect").addEventListener("change", function () {
    const selected = JSON.parse(this.value);
    jamMasukDB = selected.jam_masuk?.substring(0,5);
    jamPulangDB = selected.jam_pulang?.substring(0,5);
    updateTime();
  });
</script>

<?php include('../layout/foother.php'); ?>
