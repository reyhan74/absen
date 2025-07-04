<?php
require_once('../../config.php');

$id = $_GET['id'];

$sql = "DELETE FROM lokasi_presensi WHERE id = $id";
if ($conection->query($sql) === TRUE) {
    header('Location: lokasi_presensi.php');
} else {
    echo "Error deleting record: " . $conection->error;
}
?>