<?php
session_start();
include_once('../../config.php');

if (!isset($_SESSION['login'])) {
    header("location: ../../auth/login.php?pesan=belum_login");
    exit;
}

// Proses upload foto presensi keluar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['photo'])) {
    $file_foto = $_POST['photo'];
    $foto = str_replace('data:image/jpeg;base64,', '', $file_foto);
    $foto = str_replace(' ', '+', $foto);
    $data = base64_decode($foto);

    if (!is_dir('foto')) {
        mkdir('foto', 0777, true);
    }

    date_default_timezone_set("Asia/Jakarta");
    $tanggal_keluar = date('Y-m-d');
    $jam_keluar = date('H:i:s');
    $nama_file = 'foto/keluar_' . date('Y-m-d_H-i-s') . '.png';
    $file = basename($nama_file);
    $id_pegawai = $_SESSION['id'] ?? null;

    if ($id_pegawai) {
        $cekQuery = "SELECT * FROM presensi_out WHERE id_siswa = '$id_pegawai' AND tanggal_keluar = '$tanggal_keluar'";
        $cekResult = mysqli_query($conection, $cekQuery);

        if (mysqli_num_rows($cekResult) > 0) {
            $_SESSION['gagal'] = "Anda sudah melakukan presensi keluar hari ini.";
            header("Refresh: 3; URL=../home/home.php");
        } else {
            file_put_contents($nama_file, $data);
            $query = "INSERT INTO presensi_out (id_siswa, tanggal_keluar, jam_keluar, foto_keluar)
                      VALUES ('$id_pegawai', '$tanggal_keluar', '$jam_keluar', '$file')";
            $result = mysqli_query($conection, $query);

            if ($result) {
                $_SESSION['berhasil'] = "Presensi keluar berhasil.";
                header("Location: terimakasih_keluar.php");
                exit;
            } else {
                $_SESSION['gagal'] = "Presensi keluar gagal: " . mysqli_error($conection);
            }
        }
    } else {
        $_SESSION['gagal'] = "ID pengguna tidak ditemukan.";
    }
}

// Tampilkan halaman kamera jika tombol keluar ditekan dan jarak valid
include('../layout/header.php');

if (isset($_POST['tombol_keluar'])) {
    $latitude_pegawai = $_POST['latitude_pegawai'];
    $longitude_pegawai = $_POST['longitude_pegawai'];
    $latitude_kantor = $_POST['latitude_kantor'];
    $longitude_kantor = $_POST['longitude_kantor'];
    $radius = $_POST['radius'];

    // Hitung jarak antara lokasi pengguna dan kantor
    $theta = $longitude_pegawai - $longitude_kantor;
    $jarak = sin(deg2rad($latitude_pegawai)) * sin(deg2rad($latitude_kantor)) +
             cos(deg2rad($latitude_pegawai)) * cos(deg2rad($latitude_kantor)) * cos(deg2rad($theta));
    $jarak = acos($jarak);
    $jarak = rad2deg($jarak);
    $mil = $jarak * 60 * 1.1515;
    $jarak_km = $mil * 1.609344;
    $jarak_meter = $jarak_km * 1000;

    if ($jarak_meter > $radius) {
        $_SESSION['gagal'] = "Anda berada di luar radius sekolah.";
        header("Refresh: 3; URL= ../home/home.php");
        exit;
    }
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>

<div class="page-body">
    <div class="container-xl">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <iframe src="https://www.google.com/maps?q=<?= $latitude_pegawai ?>,<?= $longitude_pegawai ?>&hl=es;z=14&output=embed"
                                width="100%" height="400" style="border:0;" allowfullscreen></iframe>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <div id="my_camera"></div>
                        <div id="my_result"></div>
                        <div id="tanggal-dan-jam" class="mt-2"></div>
                        <form method="POST" id="form-presensi">
                            <input type="hidden" name="photo" id="photo-input">
                            <button class="btn btn-primary mt-2" type="button" id="ambil-foto">Ambil Foto & Presensi Keluar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function updateTanggalDanJam() {
        const now = new Date();
        const tanggal = now.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
        const jam = now.toLocaleTimeString('id-ID');
        document.getElementById('tanggal-dan-jam').innerText = `${tanggal} - ${jam}`;
    }
    updateTanggalDanJam();
    setInterval(updateTanggalDanJam, 1000);

    Webcam.set({
        width: 354,
        height: 472,
        image_format: 'jpeg',
        jpeg_quality: 90
    });
    Webcam.attach('#my_camera');

    document.getElementById('ambil-foto').addEventListener('click', function () {
        Webcam.snap(function (data_uri) {
            document.getElementById('my_result').innerHTML = '<img src="' + data_uri + '"/>';
            document.getElementById('photo-input').value = data_uri;
            document.getElementById('form-presensi').submit();
        });
    });
</script>

<?php } ?>

<?php if (isset($_SESSION['gagal'])): ?>
<script>
    Swal.fire({
        icon: "error",
        title: "Oops...",
        text: "<?= htmlspecialchars($_SESSION['gagal'], ENT_QUOTES); ?>",
    });
</script>
<?php unset($_SESSION['gagal']); ?>
<?php endif; ?>

<?php include('../layout/foother.php'); ?>
