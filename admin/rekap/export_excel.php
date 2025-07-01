<?php
require_once('../../config.php');

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=rekap_presensi.xls");

// Query: urutkan NIS secara numerik
$query = "
    SELECT 
        s.nis, s.nama, s.kelas,
        p.tanggal_masuk, p.jam_masuk, p.foto_masuk,
        o.jam_keluar, o.foto_keluar
    FROM siswa s
    LEFT JOIN presensi p ON s.id = p.id_siswa
    LEFT JOIN presensi_out o ON s.id = o.id_siswa AND p.tanggal_masuk = o.tanggal_keluar
    WHERE p.tanggal_masuk IS NOT NULL
    ORDER BY CAST(s.nis AS UNSIGNED) ASC
";

$result = mysqli_query($conection, $query);

if (!$result) {
    die("Query Error: " . mysqli_error($conection));
}
?>

<table border="1">
    <thead>
        <tr>
            <th>No</th>
            <th>NIS</th>
            <th>Nama</th>
            <th>Kelas</th>
            <th>Tanggal</th>
            <th>Jam Masuk</th>
            <th>Jam Keluar</th>
        </tr>
    </thead>
    <tbody>
    <?php 
    $no = 1; 
    while ($row = mysqli_fetch_assoc($result)) : 
    ?>
        <tr>
            <td><?= $no++; ?></td>
            <td>,<?= htmlspecialchars($row['nis']); ?></td>
            <td><?= htmlspecialchars($row['nama']); ?></td>
            <td><?= htmlspecialchars($row['kelas']); ?></td>
            <td><?= htmlspecialchars($row['tanggal_masuk']); ?></td>
            <td><?= htmlspecialchars($row['jam_masuk']); ?></td>
            <td><?= !empty($row['jam_keluar']) ? htmlspecialchars($row['jam_keluar']) : '-'; ?></td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>
