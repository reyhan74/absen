<?php
require_once('../../config.php');

$id = $_GET['id'];

$sql = "DELETE FROM guru WHERE id = $id";
if ($conection->query($sql) === TRUE) {
    header('Location: users.php');
} else {
    echo "Error deleting record: " . $conection->error;
}
?>