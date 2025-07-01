<?php
require_once('../../config.php');

$id = $_GET['id'];

$sql = "DELETE FROM siswa WHERE id = $id";
if ($conection->query($sql) === TRUE) {
    header('Location: users.php');
} else {
    echo "Error deleting record: " . $conection->error;
}
?>