<?php

$db_host = "127.0.0.1";
$db_user = "root";
$db_pass = "";
$db_name = "presensi";

$conection = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if(!$conection) {
    echo "koneksi gagal" . mysqli_connect_error();
}


?>
