<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../../auth/siswa/login.php?pesan=belum_login");
    exit;
}

include_once('../../config.php');
$id_siswa = $_SESSION['id'] ?? null;
if (!$id_siswa) die("ID siswa tidak ditemukan. Silakan login ulang.");

// Tambah Catatan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_catatan'])) {
    $tanggal = $_POST['tanggal'] ?? date('Y-m-d');
    $catatan = trim($_POST['catatan']);
    $nama_file = null;

    if (!empty($_FILES['foto']['name'])) {
        $upload_dir = '../../assets/img/foto_catatan/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $nama_file = time() . '_' . basename($_FILES['foto']['name']);
        $target_path = $upload_dir . $nama_file;
        $ext = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));

        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
            move_uploaded_file($_FILES['foto']['tmp_name'], $target_path);
        } else {
            $_SESSION['gagal'] = 'Format foto harus jpg/jpeg/png.';
            header("Location: catatan.php");
            exit;
        }
    }

    if (!empty($catatan)) {
        $stmt = mysqli_prepare($conection, "INSERT INTO catatan_presensi (id_siswa, tanggal, catatan, foto) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'isss', $id_siswa, $tanggal, $catatan, $nama_file);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $_SESSION['sukses'] = 'Catatan berhasil disimpan.';
    } else {
        $_SESSION['gagal'] = 'Isi catatan tidak boleh kosong.';
    }
    header("Location: catatan.php");
    exit;
}

// Edit Catatan + Ganti Foto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_catatan'])) {
    $id_catatan = $_POST['id_catatan'];
    $catatan_baru = trim($_POST['catatan_edit']);
    $foto_baru = null;
    $foto_lama = null;

    $q = mysqli_query($conection, "SELECT foto FROM catatan_presensi WHERE id = $id_catatan AND id_siswa = $id_siswa");
    $data = mysqli_fetch_assoc($q);
    $foto_lama = $data['foto'] ?? null;

    if (!empty($_FILES['foto_edit']['name'])) {
        $upload_dir = '../../assets/img/foto_catatan/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $foto_baru = time() . '_' . basename($_FILES['foto_edit']['name']);
        $target_path = $upload_dir . $foto_baru;
        $ext = strtolower(pathinfo($foto_baru, PATHINFO_EXTENSION));

        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
            move_uploaded_file($_FILES['foto_edit']['tmp_name'], $target_path);
            if (!empty($foto_lama) && file_exists($upload_dir . $foto_lama)) {
                unlink($upload_dir . $foto_lama);
            }
        } else {
            $_SESSION['gagal'] = 'Format foto harus jpg/jpeg/png.';
            header("Location: catatan.php");
            exit;
        }
    }

    if (!empty($catatan_baru)) {
        if ($foto_baru) {
            $stmt = mysqli_prepare($conection, "UPDATE catatan_presensi SET catatan = ?, foto = ? WHERE id = ? AND id_siswa = ?");
            mysqli_stmt_bind_param($stmt, 'ssii', $catatan_baru, $foto_baru, $id_catatan, $id_siswa);
        } else {
            $stmt = mysqli_prepare($conection, "UPDATE catatan_presensi SET catatan = ? WHERE id = ? AND id_siswa = ?");
            mysqli_stmt_bind_param($stmt, 'sii', $catatan_baru, $id_catatan, $id_siswa);
        }
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $_SESSION['sukses'] = 'Catatan berhasil diubah.';
    } else {
        $_SESSION['gagal'] = 'Isi catatan tidak boleh kosong.';
    }
    header("Location: catatan.php");
    exit;
}

// Hapus Catatan
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    $q = mysqli_query($conection, "SELECT foto FROM catatan_presensi WHERE id = $id_hapus AND id_siswa = $id_siswa");
    $row = mysqli_fetch_assoc($q);
    if ($row && !empty($row['foto'])) {
        $foto_path = '../../assets/img/foto_catatan/' . $row['foto'];
        if (file_exists($foto_path)) unlink($foto_path);
    }

    $stmt = mysqli_prepare($conection, "DELETE FROM catatan_presensi WHERE id = ? AND id_siswa = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $id_hapus, $id_siswa);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $_SESSION['sukses'] = 'Catatan berhasil dihapus.';
    header("Location: catatan.php");
    exit;
}

// Ambil Data
$data_catatan = [];
$stmt = mysqli_prepare($conection, "SELECT * FROM catatan_presensi WHERE id_siswa = ? ORDER BY tanggal DESC");
mysqli_stmt_bind_param($stmt, 'i', $id_siswa);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $data_catatan[] = $row;
}
mysqli_stmt_close($stmt);
?>

<?php include('../layout/header.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (isset($_SESSION['sukses'])): ?>
<script>Swal.fire({ icon: 'success', title: 'Berhasil', text: '<?= htmlspecialchars($_SESSION['sukses']) ?>' });</script>
<?php unset($_SESSION['sukses']); endif; ?>

<?php if (isset($_SESSION['gagal'])): ?>
<script>Swal.fire({ icon: 'error', title: 'Gagal', text: '<?= htmlspecialchars($_SESSION['gagal']) ?>' });</script>
<?php unset($_SESSION['gagal']); endif; ?>

<div class="container mt-4">
    <div class="row">
        <div class="table-responsive">
        <div class="col-md-5">
            <h4>Tambah Catatan</h4>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group mb-2">
                    <label for="tanggal">Tanggal</label>
                    <input type="date" name="tanggal" id="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group mb-2">
                    <label for="catatan">Isi Catatan</label>
                    <textarea name="catatan" id="catatan" rows="5" class="form-control" required></textarea>
                </div>
                <div class="form-group mb-2">
                    <label for="foto">Upload Foto (opsional)</label>
                    <input type="file" name="foto" accept="image/*" class="form-control">
                </div>
                <button type="submit" name="simpan_catatan" class="btn btn-primary">Simpan</button>
            </form>
        </div>
        <br>
        <div class="col-md-7">
            <h4>Daftar Catatan Presensi</h4>
            <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Catatan</th>
                        <th>Foto</th>
                        <th>Waktu Input</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($data_catatan): foreach ($data_catatan as $catatan): ?>
                        <tr>
                            <td><?= htmlspecialchars($catatan['tanggal']) ?></td>
                            <td><?= nl2br(htmlspecialchars($catatan['catatan'])) ?></td>
                            <td>
                                <?php if (!empty($catatan['foto'])): ?>
                                    <img src="../../assets/img/foto_catatan/<?= htmlspecialchars($catatan['foto']) ?>" width="100" class="img-thumbnail">
                                    <br>
                                    <a href="../../assets/img/foto_catatan/<?= htmlspecialchars($catatan['foto']) ?>" download class="btn btn-sm btn-outline-primary mt-1">Download</a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($catatan['created_at']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-secondary" onclick="editCatatan(<?= $catatan['id'] ?>, `<?= htmlspecialchars($catatan['catatan'], ENT_QUOTES) ?>`)">Edit</button>
                                <button class="btn btn-sm btn-danger" onclick="hapusCatatan(<?= $catatan['id'] ?>)">Hapus</button>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="5" class="text-center">Belum ada catatan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Catatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_catatan" id="id_catatan_edit">
                <div class="form-group">
                    <label for="catatan_edit">Catatan Baru</label>
                    <textarea name="catatan_edit" id="catatan_edit" class="form-control" rows="5" required></textarea>
                </div>
                <div class="form-group mt-2">
                    <label for="foto_edit">Ganti Foto (opsional)</label>
                    <input type="file" name="foto_edit" id="foto_edit" accept="image/*" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="edit_catatan" class="btn btn-success">Simpan Perubahan</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
function editCatatan(id, isi) {
    document.getElementById('id_catatan_edit').value = id;
    document.getElementById('catatan_edit').value = isi;
    new bootstrap.Modal(document.getElementById('modalEdit')).show();
}

function hapusCatatan(id) {
    Swal.fire({
        title: 'Yakin ingin menghapus catatan ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location = `?hapus=${id}`;
        }
    });
}
</script>

<?php include('../layout/foother.php'); ?>
