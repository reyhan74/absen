<?php

$db_host = "100.91.139.68";
$db_user = "absen";
$db_pass = "881f18347f62e";
$db_name = "absen";

$conection = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if(!$conection) {
    echo "koneksi gagal" . mysqli_connect_error();
}


function base_url($url = null)
{
    $base_url = 'http://127.0.0.1/absen';
    if ($url != null) {
        // Correct string concatenation and variable reference
        return $base_url . '/' . $url;
    } else {
        return $base_url;
    }
}

?>