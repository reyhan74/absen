<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../../auth/siswa/login.php?pesan=belum_login");
    exit;
}

include('../layout/header.php');
include_once('../../config.php');

$selected_location = $_POST['lokasi'] ?? null;

$lokasi_presensi = [];
$lokasi_result = mysqli_query($conection, "SELECT * FROM lokasi_presensi");
while ($row = mysqli_fetch_assoc($lokasi_result)) {
    $lokasi_presensi[] = $row;
}

$latitude_kantor = $longitude_kantor = $radius = $zona_waktu = $nama_lokasi = "";

if ($selected_location) {
    $stmt = mysqli_prepare($conection, "SELECT * FROM lokasi_presensi WHERE nama_lokasi = ?");
    mysqli_stmt_bind_param($stmt, "s", $selected_location);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($lokasi = mysqli_fetch_assoc($result)) {
        $latitude_kantor = $lokasi['latitut'];
        $longitude_kantor = $lokasi['longitude'];
        $radius = $lokasi['radius'];
        $zona_waktu = $lokasi['zona_waktu'];
        $nama_lokasi = $lokasi['nama_lokasi'];
    } else {
        echo "<script>
            Swal.fire({
              icon: 'error',
              title: 'Lokasi tidak ditemukan!',
              text: 'Pastikan Anda memilih lokasi yang benar.'
            });
        </script>";
    }

    switch ($zona_waktu) {
        case 'WIB': date_default_timezone_set('Asia/Jakarta'); break;
        case 'WITA': date_default_timezone_set('Asia/Makassar'); break;
        case 'WIT': date_default_timezone_set('Asia/Jayapura'); break;
        default: date_default_timezone_set('Asia/Jakarta'); break;
    }
}
?>

<!-- SweetAlert pesan session -->
<?php
if (isset($_GET['pesan'])) {
    if ($_GET['pesan'] == "terimakasih_sudah_login") {
        $_SESSION['gagal'] = 'Terimakasih sudah login';
    } elseif ($_GET['pesan'] == "tolak_akses") {
        $_SESSION['gagal'] = 'Akses ke halaman ini ditolak';
    }
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

<?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$selected_location): ?>
<script>
  Swal.fire({
    icon: 'error',
    title: 'Lokasi Belum Dipilih!',
    text: 'Silakan pilih lokasi terlebih dahulu untuk melakukan presensi.',
  });
</script>
<?php endif; ?>

<div class="page-body">
  <div class="container-xl">
    <div class="row">
      <div class="col-md-2"></div>

      <!-- Pilih Lokasi -->
      <div class="col-md-3 mt-2">
        <div class="card text-center">
          <div class="card-header">Pilih Lokasi</div>
          <div class="card-body">
            <form action="" method="POST">
              <select name="lokasi" class="form-control" required>
                <option value="">Pilih Lokasi</option>
                <?php foreach ($lokasi_presensi as $lokasi): ?>
                  <option value="<?= htmlspecialchars($lokasi['nama_lokasi']) ?>" <?= ($selected_location === $lokasi['nama_lokasi']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($lokasi['nama_lokasi']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <br>
              <button type="submit" class="btn btn-success">Pilih</button>
            </form>
          </div>
        </div>
      </div>

      <!-- Presensi Masuk -->
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-header">Presensi Masuk</div>
          <div class="card-body">
            <div class="parent_date">
              <div id="tanggal_masuk"></div><div class="ms-2"></div>
              <div id="bulan_masuk"></div><div class="ms-2"></div>
              <div id="tahun_masuk"></div>
            </div>
            <div class="parent_clock">
              <div id="jam_masuk"></div>:<div id="menit_masuk"></div>:<div id="detik_masuk"></div>
            </div>
            <br>
            <form action="../presensi/presensi_masuk.php" method="POST">
              <input type="hidden" name="nama_lokasi" value="<?= htmlspecialchars($nama_lokasi) ?>">
              <input type="hidden" name="latitude_kantor" value="<?= htmlspecialchars($latitude_kantor) ?>">
              <input type="hidden" name="longitude_kantor" value="<?= htmlspecialchars($longitude_kantor) ?>">
              <input type="hidden" name="radius" value="<?= htmlspecialchars($radius) ?>">
              <input type="hidden" name="zona_waktu" value="<?= htmlspecialchars($zona_waktu) ?>">
              <input type="hidden" name="latitude_pegawai" id="latitude_pegawai_masuk">
              <input type="hidden" name="longitude_pegawai" id="longitude_pegawai_masuk">
              <input type="hidden" name="tanggal_masuk" value="<?= date('Y-m-d') ?>">
              <input type="hidden" name="jam_masuk" value="<?= date('H:i:s') ?>">
              <button type="submit" name="tombol_masuk" class="btn btn-primary" disabled>Masuk</button>
            </form>
          </div>
        </div>
      </div>

      <!-- Presensi Keluar -->
      <div class="col-md-3">
        <div class="card text-center">
          <div class="card-header">Presensi Keluar</div>
          <div class="card-body">
            <div class="parent_date">
              <div id="tanggal_keluar"></div><div class="ms-2"></div>
              <div id="bulan_keluar"></div><div class="ms-2"></div>
              <div id="tahun_keluar"></div>
            </div>
            <div class="parent_clock">
              <div id="jam_keluar"></div>:<div id="menit_keluar"></div>:<div id="detik_keluar"></div>
            </div>
            <br>
            <form action="../presensi/presensi_keluar.php" method="POST">
              <input type="hidden" name="nama_lokasi" value="<?= htmlspecialchars($nama_lokasi) ?>">
              <input type="hidden" name="latitude_kantor" value="<?= htmlspecialchars($latitude_kantor) ?>">
              <input type="hidden" name="longitude_kantor" value="<?= htmlspecialchars($longitude_kantor) ?>">
              <input type="hidden" name="radius" value="<?= htmlspecialchars($radius) ?>">
              <input type="hidden" name="zona_waktu" value="<?= htmlspecialchars($zona_waktu) ?>">
              <input type="hidden" name="latitude_pegawai" id="latitude_pegawai_keluar">
              <input type="hidden" name="longitude_pegawai" id="longitude_pegawai_keluar">
              <input type="hidden" name="tanggal_keluar" value="<?= date('Y-m-d') ?>">
              <input type="hidden" name="jam_keluar" value="<?= date('H:i:s') ?>">
              <button type="submit" name="tombol_keluar" class="btn btn-danger" disabled>Keluar</button>
            </form>
          </div>
        </div>
      </div>

      <div class="col-md-2"></div>
    </div>
  </div>
</div>

<div id="status" style="text-align: center; margin-top: 20px;"></div>

<script>
  const lokasiDipilih = <?= $selected_location ? 'true' : 'false' ?>;

  const namaBulan = ["Januari", "Februari", "Maret", "April", "Mei", "Juni",
    "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

  function updateTime() {
    const waktu = new Date();
    setTimeout(updateTime, 1000);
    const tanggal = waktu.getDate();
    const bulan = namaBulan[waktu.getMonth()];
    const tahun = waktu.getFullYear();
    const jam = waktu.getHours().toString().padStart(2, '0');
    const menit = waktu.getMinutes().toString().padStart(2, '0');
    const detik = waktu.getSeconds().toString().padStart(2, '0');

    document.getElementById("tanggal_masuk").innerHTML = tanggal;
    document.getElementById("bulan_masuk").innerHTML = bulan;
    document.getElementById("tahun_masuk").innerHTML = tahun;
    document.getElementById("jam_masuk").innerHTML = jam;
    document.getElementById("menit_masuk").innerHTML = menit;
    document.getElementById("detik_masuk").innerHTML = detik;

    document.getElementById("tanggal_keluar").innerHTML = tanggal;
    document.getElementById("bulan_keluar").innerHTML = bulan;
    document.getElementById("tahun_keluar").innerHTML = tahun;
    document.getElementById("jam_keluar").innerHTML = jam;
    document.getElementById("menit_keluar").innerHTML = menit;
    document.getElementById("detik_keluar").innerHTML = detik;
  }

  function deg2rad(deg) {
    return deg * (Math.PI / 180);
  }

  function getDistanceFromLatLonInKm(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const dLat = deg2rad(lat2 - lat1);
    const dLon = deg2rad(lon2 - lon1);
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
              Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
              Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c * 1000;
  }

  function getLocation() {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(showPosition, showError);
    } else {
      document.getElementById("status").innerText = "Browser Anda tidak mendukung Geolocation.";
    }
  }

  function showPosition(position) {
    const latPegawai = position.coords.latitude;
    const lonPegawai = position.coords.longitude;

    const latKantor = parseFloat("<?= htmlspecialchars($latitude_kantor) ?>");
    const lonKantor = parseFloat("<?= htmlspecialchars($longitude_kantor) ?>");
    const radius = parseFloat("<?= htmlspecialchars($radius) ?>");

    const jarak = getDistanceFromLatLonInKm(latPegawai, lonPegawai, latKantor, lonKantor);

    if (jarak > radius) {
      document.getElementById("status").innerText = `Diluar radius! Jarak: ${Math.round(jarak)} meter`;
      Swal.fire({
        icon: "error",
        title: "Diluar Radius!",
        text: `Jarak Anda ${Math.round(jarak)} meter di luar jangkauan lokasi.`,
      });
      document.querySelector("button[name='tombol_masuk']").disabled = true;
      document.querySelector("button[name='tombol_keluar']").disabled = true;
    } else {
      document.getElementById("latitude_pegawai_masuk").value = latPegawai;
      document.getElementById("longitude_pegawai_masuk").value = lonPegawai;
      document.getElementById("latitude_pegawai_keluar").value = latPegawai;
      document.getElementById("longitude_pegawai_keluar").value = lonPegawai;
      document.getElementById("status").innerText = "Lokasi berhasil diambil!";
      document.querySelector("button[name='tombol_masuk']").disabled = false;
      document.querySelector("button[name='tombol_keluar']").disabled = false;
    }
  }

  function showError(error) {
    let message = "Terjadi kesalahan saat mengambil lokasi.";
    switch (error.code) {
      case error.PERMISSION_DENIED: message = "Anda menolak permintaan lokasi."; break;
      case error.POSITION_UNAVAILABLE: message = "Informasi lokasi tidak tersedia."; break;
      case error.TIMEOUT: message = "Permintaan lokasi melebihi batas waktu."; break;
    }
    document.getElementById("status").innerText = message;
    Swal.fire({ icon: "error", title: "Lokasi Error", text: message });
  }

  window.onload = function () {
    updateTime();

    if (lokasiDipilih) {
      getLocation();
    } else {
      document.getElementById("status").innerText = "Silakan pilih lokasi terlebih dahulu.";
      Swal.fire({
        icon: "info",
        title: "Lokasi Belum Dipilih",
        text: "Silakan pilih lokasi terlebih dahulu sebelum melakukan presensi.",
      });

      document.querySelector("button[name='tombol_masuk']").disabled = true;
      document.querySelector("button[name='tombol_keluar']").disabled = true;
    }
  };
</script>

<?php include('../layout/foother.php'); ?>
