<?php
session_start();
include('../layout/header.php'); 
require_once('../../config.php');
?>

<div class="page-body">
  <div class="container-xl">
    <h2 class="page-title mb-4">Cari NIS Siswa</h2>

    <form method="GET" class="search-form mb-4">
      <div class="search-grid">
        <input type="text" name="nama" placeholder="Nama Siswa (HURUF KAPITAL)" value="<?= isset($_GET['nama']) ? htmlspecialchars($_GET['nama']) : '' ?>">

        <select name="kelas">
          <option value="">-- Pilih Kelas --</option>
          <!-- X -->
          <option value="X TKJ 1">X TKJ 1</option>
          <option value="X TKJ 2">X TKJ 2</option>
          <option value="X TKJ 3">X TKJ 3</option>
          <option value="X TPM 1">X TPM 1</option>
          <option value="X TPM 2">X TPM 2</option>
          <option value="X TPM 3">X TPM 3</option>
          <option value="X TPM 4">X TPM 4</option>
          <option value="X TPM 5">X TPM 5</option>
          <option value="X TKR 1">X TKR 1</option>
          <option value="X TKR 2">X TKR 2</option>
          <option value="X TKR 3">X TKR 3</option>
          <option value="X TITL 1">X TITL 1</option>
          <option value="X TITL 2">X TITL 2</option>
          <option value="X TITL 3">X TITL 3</option>
          <option value="X DPIB 1">X DPIB 1</option>
          <option value="X DPIB 2">X DPIB 2</option>
          <option value="X TOI 1">X TOI 1</option>
          <option value="X TOI 2">X TOI 2</option>
          <!-- XI -->
          <option value="XI TKJ 1">XI TKJ 1</option>
          <option value="XI TKJ 2">XI TKJ 2</option>
          <option value="XI TKJ 3">XI TKJ 3</option>
          <option value="XI TPM 1">XI TPM 1</option>
          <option value="XI TPM 2">XI TPM 2</option>
          <option value="XI TPM 3">XI TPM 3</option>
          <option value="XI TPM 4">XI TPM 4</option>
          <option value="XI TPM 5">XI TPM 5</option>
          <option value="XI TKR 1">XI TKR 1</option>
          <option value="XI TKR 2">XI TKR 2</option>
          <option value="XI TKR 3">XI TKR 3</option>
          <option value="XI TITL 1">XI TITL 1</option>
          <option value="XI TITL 2">XI TITL 2</option>
          <option value="XI TITL 3">XI TITL 3</option>
          <option value="XI DPIB 1">XI DPIB 1</option>
          <option value="XI DPIB 2">XI DPIB 2</option>
          <option value="XI TOI 1">XI TOI 1</option>
          <option value="XI TOI 2">XI TOI 2</option>
          <!-- XII -->
          <option value="XII TKJ 1">XII TKJ 1</option>
          <option value="XII TKJ 2">XII TKJ 2</option>
          <option value="XII TKJ 3">XII TKJ 3</option>
          <option value="XII TPM 1">XII TPM 1</option>
          <option value="XII TPM 2">XII TPM 2</option>
          <option value="XII TPM 3">XII TPM 3</option>
          <option value="XII TPM 4">XII TPM 4</option>
          <option value="XII TPM 5">XII TPM 5</option>
          <option value="XII TKR 1">XII TKR 1</option>
          <option value="XII TKR 2">XII TKR 2</option>
          <option value="XII TKR 3">XII TKR 3</option>
          <option value="XII TITL 1">XII TITL 1</option>
          <option value="XII TITL 2">XII TITL 2</option>
          <option value="XII TITL 3">XII TITL 3</option>
          <option value="XII DPIB 1">XII DPIB 1</option>
          <option value="XII DPIB 2">XII DPIB 2</option>
          <option value="XII TOI 1">XII TOI 1</option>
          <option value="XII TOI 2">XII TOI 2</option>
        </select>

        <select name="no_absen">
          <option value="">-- No Absen --</option>
          <?php
          for ($i = 1; $i <= 40; $i++) {
            $selected = (isset($_GET['no_absen']) && $_GET['no_absen'] == $i) ? 'selected' : '';
            echo "<option value=\"$i\" $selected>$i</option>";
          }
          ?>
        </select>
      </div>

      <div style="margin-top: 12px;">
        <button type="submit" class="btn-cari">Cari</button>
      </div>

      <?php if (!isset($_SESSION['user'])): ?>
        <div style="margin-top: 10px;">
          <a href="/" class="btn-login">Login</a>
        </div>
      <?php endif; ?>
    </form>

    <?php
    if (isset($_GET['nama']) || isset($_GET['kelas']) || isset($_GET['no_absen'])) {
      $nama = mysqli_real_escape_string($conection, $_GET['nama']);
      $kelas = mysqli_real_escape_string($conection, $_GET['kelas']);
      $no_absen = mysqli_real_escape_string($conection, $_GET['no_absen']);

      $query = "SELECT * FROM siswa WHERE 1=1";
      if (!empty($nama)) $query .= " AND nama LIKE '%$nama%'";
      if (!empty($kelas)) $query .= " AND kelas = '$kelas'";
      if (!empty($no_absen)) $query .= " AND no_absen = '$no_absen'";

      $result = mysqli_query($conection, $query);

      if (mysqli_num_rows($result) > 0) {
        echo "<div class='card'><div class='card-body'><div class='table-responsive'><table class='table table-bordered'><thead><tr><th>NIS</th><th>Nama</th><th>Kelas</th><th>No Absen</th></tr></thead><tbody>";
        while ($siswa = mysqli_fetch_array($result)) {
          echo "<tr>
                  <td>{$siswa['nis']}</td>
                  <td>{$siswa['nama']}</td>
                  <td>{$siswa['kelas']}</td>
                  <td>{$siswa['no_absen']}</td>
                </tr>";
        }
        echo "</tbody></table></div></div></div>";
      } else {
        echo "<p>Tidak ada hasil ditemukan.</p>";
      }
    }
    ?>
  </div>
</div>

<!-- CSS tambahan -->
<style>
  .search-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
  }
  .search-grid input,
  .search-grid select {
    padding: 8px;
    border-radius: 6px;
    border: 1px solid #ccc;
    flex: 1 1 200px;
  }
  .btn-cari {
    padding: 8px 16px;
    background-color: #206bc4;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
  }
  .btn-cari:hover {
    background-color: #1a5bb8;
  }
  .btn-login {
    padding: 8px 16px;
    background-color: #0ca678;
    color: white;
    border: none;
    border-radius: 6px;
    text-decoration: none;
    font-weight: bold;
  }
  .btn-login:hover {
    background-color: #099268;
  }
</style>

<?php include('../layout/foother.php'); ?>
