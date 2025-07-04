<?php
require_once('../../config.php');

$id = $_GET['id'];

$sql = "DELETE FROM jabatan WHERE id = $id";
if ($conection->query($sql) === TRUE) {
    header('Location: jabatan.php');
} else {
    echo "Error deleting record: " . $conection->error;
}
?>