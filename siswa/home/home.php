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

<div class="page-body">
  <div class="container-xl">
    <div class="row">
      <div class="col-md-2"></div>

      <div class="col-md-3 mt-2">
        <div class="card text-center">
          <div class="card-header">Pilih Lokasi</div>
          <div class="card-body">
            <form id="lokasiForm">
              <select name="lokasi" id="lokasiSelect" class="form-control" required>
                <option value="">Pilih Lokasi</option>
                <?php foreach ($lokasi_presensi as $lokasi): ?>
                  <option value="<?= htmlspecialchars($lokasi['nama_lokasi']) ?>">
                    <?= htmlspecialchars($lokasi['nama_lokasi']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </form>
          </div>
        </div>
      </div>

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
      </div>

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

      <div class="col-md-2"></div>
    </div>
  </div>
</div>

<div id="status" style="text-align: center; margin-top: 20px;"></div>

<script>
  const namaBulan = ["Januari", "Februari", "Maret", "April", "Mei", "Juni",
    "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

  function updateTime() {
    const waktu = new Date();
    const tanggal = waktu.getDate();
    const bulan = namaBulan[waktu.getMonth()];
    const tahun = waktu.getFullYear();
    const jam = waktu.getHours().toString().padStart(2, '0');
    const menit = waktu.getMinutes().toString().padStart(2, '0');
    const detik = waktu.getSeconds().toString().padStart(2, '0');

    const setTime = (prefix) => {
      document.getElementById(`tanggal_${prefix}`).innerHTML = tanggal;
      document.getElementById(`bulan_${prefix}`).innerHTML = bulan;
      document.getElementById(`tahun_${prefix}`).innerHTML = tahun;
      document.getElementById(`jam_${prefix}`).innerHTML = jam;
      document.getElementById(`menit_${prefix}`).innerHTML = menit;
      document.getElementById(`detik_${prefix}`).innerHTML = detik;
    };

    setTime("masuk");
    setTime("keluar");
  }

  setInterval(updateTime, 1000);
  updateTime();

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

  function getLocation(latKantor, lonKantor, radius) {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(function(position) {
        const latPegawai = position.coords.latitude;
        const lonPegawai = position.coords.longitude;
        const jarak = getDistanceFromLatLonInKm(latPegawai, lonPegawai, parseFloat(latKantor), parseFloat(lonKantor));

        if (jarak > radius) {
          document.getElementById("status").innerText = `Diluar radius! Jarak: ${Math.round(jarak)} meter`;
          Swal.fire({
            icon: "error",
            title: "Diluar Radius!",
            text: `Jarak Anda ${Math.round(jarak)} meter di luar jangkauan lokasi.`,
          });
          document.querySelectorAll("button[name='tombol_masuk'], button[name='tombol_keluar']").forEach(btn => btn.disabled = true);
        } else {
          document.getElementById("latitude_pegawai_masuk").value = latPegawai;
          document.getElementById("longitude_pegawai_masuk").value = lonPegawai;
          document.getElementById("latitude_pegawai_keluar").value = latPegawai;
          document.getElementById("longitude_pegawai_keluar").value = lonPegawai;

          document.getElementById("status").innerText = "Lokasi berhasil diambil!";
          document.querySelectorAll("button[name='tombol_masuk'], button[name='tombol_keluar']").forEach(btn => btn.disabled = false);
        }
      }, showError);
    } else {
      document.getElementById("status").innerText = "Browser Anda tidak mendukung Geolocation.";
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

  // Saat lokasi dipilih
  document.getElementById("lokasiSelect").addEventListener("change", function () {
    const lokasi = this.value;
    if (!lokasi) return;

    fetch("../presensi/get_lokasi.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "lokasi=" + encodeURIComponent(lokasi),
    })
    .then(res => res.json())
    .then(data => {
      console.log("RESPON LOKASI:", data);

      if (data && !data.error) {
        document.querySelectorAll("input[name='nama_lokasi']").forEach(el => el.value = data.nama_lokasi);
        document.querySelectorAll("input[name='latitude_kantor']").forEach(el => el.value = data.latitut);
        document.querySelectorAll("input[name='longitude_kantor']").forEach(el => el.value = data.longitude);
        document.querySelectorAll("input[name='radius']").forEach(el => el.value = data.radius);
        document.querySelectorAll("input[name='zona_waktu']").forEach(el => el.value = data.zona_waktu);

        // Konversi dan validasi sebelum dipakai
        const lat = parseFloat(data.latitut);
        const lon = parseFloat(data.longitude);
        const rad = parseFloat(data.radius);
        if (!isNaN(lat) && !isNaN(lon) && !isNaN(rad)) {
          getLocation(lat, lon, rad);
        } else {
          Swal.fire("Data Tidak Valid", "Koordinat atau radius bermasalah.", "error");
        }
      } else {
        Swal.fire({
          icon: "error",
          title: "Gagal Ambil Lokasi",
          text: data.error || "Data lokasi tidak ditemukan.",
        });
      }
    })
    .catch(err => {
      console.error("Fetch Gagal:", err);
      Swal.fire({
        icon: "error",
        title: "Kesalahan",
        text: "Gagal menghubungi server. Coba lagi nanti.",
      });
    });
  });
</script>


<?php include('../layout/foother.php'); ?>
