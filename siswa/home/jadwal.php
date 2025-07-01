<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../../auth/siswa/login.php?pesan=belum_login");
    exit;
}

include('../layout/header.php');
include_once('../../config.php');

// Ambil semua data dari tabel jadwal_pelajaran
$result = mysqli_query($conection, "SELECT * FROM jadwal_pelajaran ORDER BY id DESC");
?>

<!-- SweetAlert pesan session -->
<?php
if (isset($_GET['pesan'])) {
    if ($_GET['pesan'] == "tambah_berhasil") {
        $_SESSION['sukses'] = 'Data berhasil ditambahkan.';
    } elseif ($_GET['pesan'] == "hapus_berhasil") {
        $_SESSION['sukses'] = 'Data berhasil dihapus.';
    } elseif ($_GET['pesan'] == "edit_berhasil") {
        $_SESSION['sukses'] = 'Data berhasil diubah.';
    }
}
?>

<?php if (isset($_SESSION['sukses'])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        Swal.fire({
            icon: "success",
            title: "Berhasil!",
            text: "<?= htmlspecialchars($_SESSION['sukses'], ENT_QUOTES); ?>",
        });
    });
</script>
<?php unset($_SESSION['sukses']); endif; ?>

<div class="page-body">
    <div class="container-xl">
        <div class="row">
            <div class="col-12">
                <h3>Jadwal Kelas</h3>
                <a href="jadwal_kelas_tambah.php" class="btn btn-success mb-3">
                    <i class="fa fa-plus"></i> Tambah Jadwal
                </a>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Kelas</th>
                                        <th>Hari</th>
                                        <th>Jam Mulai</th>
                                        <th>Jam Selesai</th>
                                        <th>Mata Pelajaran</th>
                                        <th>Guru</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $no = 1;
                                        while($row = mysqli_fetch_assoc($result)) {
                                        ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= htmlspecialchars($row['kelas']); ?></td>
                                            <td><?= htmlspecialchars($row['hari']); ?></td>
                                            <td><?= htmlspecialchars($row['jam_mulai']); ?></td>
                                            <td><?= htmlspecialchars($row['jam_selesai']); ?></td>
                                            <td><?= htmlspecialchars($row['mata_pelajaran']); ?></td>
                                            <td><?= htmlspecialchars($row['guru']); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function hapusJadwal(id) {
    Swal.fire({
        title: 'Yakin ingin menghapus data?',
        text: "Data yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'jadwal_kelas_hapus.php?id=' + id;
        }
    });
}
</script>

<?php include('../layout/foother.php'); ?>
