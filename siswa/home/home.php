<?php
session_start();
// Ensure consistent login redirection
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
                    <div class="card-body text-center">

                        <form id="lokasiForm" class="mb-4">
                            <label for="lokasiSelect" class="form-label fw-bold">Pilih Lokasi</label>
                            <select name="lokasi" id="lokasiSelect" class="form-control w-50 mx-auto" required>
                                <option value="">-- Pilih Lokasi --</option>
                                <?php foreach ($lokasi_presensi as $lokasi): ?>
                                    <?php if (!empty($lokasi['jam_masuk']) && !empty($lokasi['jam_pulang'])): ?>
                                    <option value='<?= json_encode($lokasi) ?>'>
                                        <?= htmlspecialchars($lokasi['nama_lokasi']) ?>
                                    </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </form>

                        <div class="row justify-content-center">
                            <div class="col-md-6" id="masukSection" style="display: none;">
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
                                    <form id="formMasuk" action="../presensi/presensi_masuk.php" method="POST">
                                        <input type="hidden" name="nama_lokasi" id="masuk_nama_lokasi">
                                        <input type="hidden" name="latitude_kantor" id="masuk_latitude_kantor">
                                        <input type="hidden" name="longitude_kantor" id="masuk_longitude_kantor">
                                        <input type="hidden" name="radius" id="masuk_radius">
                                        <input type="hidden" name="zona_waktu" id="masuk_zona_waktu">
                                        <input type="hidden" name="latitude_pegawai" id="latitude_pegawai_masuk">
                                        <input type="hidden" name="longitude_pegawai" id="longitude_pegawai_masuk">
                                        <input type="hidden" name="tanggal_masuk_form" value="<?= date('Y-m-d') ?>"> <input type="hidden" name="jam_masuk_form" value="<?= date('H:i:s') ?>"> <input type="hidden" name="status_masuk" id="status_masuk" value="">
                                        <button type="submit" name="tombol_masuk" id="btnMasuk" class="btn btn-primary" disabled>Masuk</button>
                                    </form>
                                </div>
                            </div>

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
                                    <form id="formKeluar" action="../presensi/presensi_keluar.php" method="POST">
                                        <input type="hidden" name="nama_lokasi" id="keluar_nama_lokasi">
                                        <input type="hidden" name="latitude_kantor" id="keluar_latitude_kantor">
                                        <input type="hidden" name="longitude_kantor" id="keluar_longitude_kantor">
                                        <input type="hidden" name="radius" id="keluar_radius">
                                        <input type="hidden" name="zona_waktu" id="keluar_zona_waktu">
                                        <input type="hidden" name="latitude_pegawai" id="latitude_pegawai_keluar">
                                        <input type="hidden" name="longitude_pegawai" id="longitude_pegawai_keluar">
                                        <input type="hidden" name="tanggal_keluar_form" value="<?= date('Y-m-d') ?>"> <input type="hidden" name="jam_keluar_form" value="<?= date('H:i:s') ?>"> <button type="submit" name="tombol_keluar" id="btnKeluar" class="btn btn-danger" disabled>Keluar</button>
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
    let lokasiDipilih = null;
    let radius = 0;
    let kantorLat = 0;
    let kantorLong = 0;
    let lokasiValid = false;
    let userLatitude = 0; // To store user's latitude
    let userLongitude = 0; // To store user's longitude

    function getDistance(lat1, lon1, lat2, lon2) {
        const R = 6371e3; // metres
        const φ1 = lat1 * Math.PI / 180; // φ, λ in radians
        const φ2 = lat2 * Math.PI / 180;
        const Δφ = (lat2 - lat1) * Math.PI / 180;
        const Δλ = (lon2 - lon1) * Math.PI / 180;

        const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
                  Math.cos(φ1) * Math.cos(φ2) *
                  Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

        return R * c; // in metres
    }

    function checkLocationPermission() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(pos => {
                userLatitude = pos.coords.latitude;
                userLongitude = pos.coords.longitude;
                const jarak = getDistance(kantorLat, kantorLong, userLatitude, userLongitude);

                // Update hidden inputs for user's location
                document.getElementById("latitude_pegawai_masuk").value = userLatitude;
                document.getElementById("longitude_pegawai_masuk").value = userLongitude;
                document.getElementById("latitude_pegawai_keluar").value = userLatitude;
                document.getElementById("longitude_pegawai_keluar").value = userLongitude;


                if (jarak > radius) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lokasi Tidak Sesuai',
                        text: 'Anda berada di luar radius yang ditentukan!'
                    });
                    lokasiValid = false;
                    document.getElementById("btnMasuk").disabled = true;
                    document.getElementById("btnKeluar").disabled = true;
                    document.getElementById("status").innerText = "Anda berada di luar radius lokasi yang dipilih.";
                    return;
                } else {
                    lokasiValid = true;
                    document.getElementById("btnMasuk").disabled = false;
                    document.getElementById("btnKeluar").disabled = false;
                    document.getElementById("status").innerText = "Lokasi Anda berada dalam radius yang ditentukan.";
                }

                updateTime(); // Update time and visibility after location check
            }, (error) => {
                let errorMessage = 'Izinkan akses lokasi di browser Anda';
                if (error.code === error.PERMISSION_DENIED) {
                    errorMessage = 'Akses lokasi ditolak. Harap izinkan akses lokasi untuk presensi.';
                } else if (error.code === error.POSITION_UNAVAILABLE) {
                    errorMessage = 'Informasi lokasi tidak tersedia.';
                } else if (error.code === error.TIMEOUT) {
                    errorMessage = 'Waktu habis saat mencoba mendapatkan lokasi.';
                }
                Swal.fire('Gagal Mengakses Lokasi', errorMessage, 'error');
                lokasiValid = false;
                document.getElementById("btnMasuk").disabled = true;
                document.getElementById("btnKeluar").disabled = true;
                document.getElementById("status").innerText = "Gagal mendapatkan lokasi Anda. Izinkan akses lokasi.";
            });
        } else {
            Swal.fire('Browser tidak mendukung', 'Perangkat tidak mendukung geolokasi', 'error');
            lokasiValid = false;
            document.getElementById("btnMasuk").disabled = true;
            document.getElementById("btnKeluar").disabled = true;
            document.getElementById("status").innerText = "Browser Anda tidak mendukung geolokasi.";
        }
    }

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

        // Update hidden date/time fields with current client-side time
        document.querySelector('#formMasuk input[name="tanggal_masuk_form"]').value = `${tahun}-${(waktu.getMonth() + 1).toString().padStart(2, '0')}-${tanggal.toString().padStart(2, '0')}`;
        document.querySelector('#formMasuk input[name="jam_masuk_form"]').value = `${jam}:${menit}:${detik}`;
        document.querySelector('#formKeluar input[name="tanggal_keluar_form"]').value = `${tahun}-${(waktu.getMonth() + 1).toString().padStart(2, '0')}-${tanggal.toString().padStart(2, '0')}`;
        document.querySelector('#formKeluar input[name="jam_keluar_form"]').value = `${jam}:${menit}:${detik}`;


        if (jamMasukDB && jamPulangDB && lokasiValid) {
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
                document.getElementById("status").innerText = "Waktu presensi belum dimulai atau sudah lewat.";
            }
        }
    }

    setInterval(updateTime, 1000);

    document.getElementById("lokasiSelect").addEventListener("change", function () {
        const selectedValue = this.value;
        if (!selectedValue) {
            // Handle case where "-- Pilih Lokasi --" is selected
            Swal.fire({
                icon: 'info',
                title: 'Pilih Lokasi',
                text: 'Silakan pilih lokasi presensi dari daftar.'
            });
            document.getElementById("masukSection").style.display = "none";
            document.getElementById("keluarSection").style.display = "none";
            document.getElementById("btnMasuk").disabled = true;
            document.getElementById("btnKeluar").disabled = true;
            document.getElementById("status").innerText = "";
            lokasiDipilih = null;
            return;
        }

        const selected = JSON.parse(selectedValue);
        if (!selected || !selected.jam_masuk || !selected.jam_pulang || !selected.radius || !selected.latitut || !selected.longitude) {
            Swal.fire({
                icon: 'error',
                title: 'Data Lokasi Tidak Lengkap',
                text: 'Lokasi yang dipilih tidak memiliki semua data yang diperlukan (jam masuk/pulang, radius, lat/long).'
            });
            document.getElementById("masukSection").style.display = "none";
            document.getElementById("keluarSection").style.display = "none";
            document.getElementById("btnMasuk").disabled = true;
            document.getElementById("btnKeluar").disabled = true;
            document.getElementById("status").innerText = "Data lokasi tidak lengkap.";
            lokasiDipilih = null;
            return;
        }

        jamMasukDB = selected.jam_masuk?.substring(0,5);
        jamPulangDB = selected.jam_pulang?.substring(0,5);
        radius = parseFloat(selected.radius);
        kantorLat = parseFloat(selected.latitut);
        kantorLong = parseFloat(selected.longitude);
        lokasiDipilih = selected;

        // Populate hidden inputs for BOTH forms (masuk and keluar)
        document.getElementById("masuk_nama_lokasi").value = selected.nama_lokasi;
        document.getElementById("masuk_latitude_kantor").value = kantorLat;
        document.getElementById("masuk_longitude_kantor").value = kantorLong;
        document.getElementById("masuk_radius").value = radius;
        document.getElementById("masuk_zona_waktu").value = selected.zona_waktu; // Assuming 'zona_waktu' exists

        document.getElementById("keluar_nama_lokasi").value = selected.nama_lokasi;
        document.getElementById("keluar_latitude_kantor").value = kantorLat;
        document.getElementById("keluar_longitude_kantor").value = kantorLong;
        document.getElementById("keluar_radius").value = radius;
        document.getElementById("keluar_zona_waktu").value = selected.zona_waktu; // Assuming 'zona_waktu' exists

        checkLocationPermission(); // Re-check location when a new location is selected
    });

    // Initial check for geolocation permission and update time display
    // No need to call checkLocationPermission directly here, as it's called on select change.
    // However, if there's no initial selection or if the user doesn't interact,
    // the clock won't show. Consider updating time display regardless of selection.
    updateTime();

</script>

<?php include('../layout/foother.php'); ?>