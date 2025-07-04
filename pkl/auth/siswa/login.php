<?php
session_start();
require_once('../../config.php'); // Ensure this file exists and contains the database connection

if (isset($_POST["login"])) {
    $nis = $_POST["nis"];
    
    // Ensure the database connection variable is properly defined
    if (!$conection) {
        die("Database connection failed: " . mysqli_connect_error());
    }
    
    // Prepared statement to prevent SQL injection
    $stmt = mysqli_prepare($conection, 'SELECT * FROM siswa WHERE nis = ?');
    if ($stmt) {
        // Bind parameters: "s" means the parameter is a string (username)
        mysqli_stmt_bind_param($stmt, 's', $nis);
        
        // Execute the query
        mysqli_stmt_execute($stmt);
        
        // Get the result
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            
            if ($user['status'] === 'aktif') {
                $_SESSION["login"] = true;
                $_SESSION['id'] = $user['id'];
                $_SESSION['jenis_kelamin'] = $user['jenis_kelamin'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['alamat'] = $user['alamat'];
                $_SESSION['no_handphone'] = $user['no_handphone'];
                $_SESSION['foto'] = $user['foto'];
                $_SESSION['lokasi_presensi'] = $user['lokasi_presensi'];
                header("Location: ../../siswa/home/home.php");
                exit();
            } else {
                $_SESSION["gagal"] = "Akun anda belum aktif";
            }
        } else {
            $_SESSION["gagal"] = "NIS tidak ditemukan";
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION["gagal"] = "Terjadi kesalahan dalam query";
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Login Siswa</title>
    <link rel="icon" type="png" href="../../assets/img/logo_cb.png">
    <link href="../../assets/css/tabler.min.css" rel="stylesheet"/>
    <style>
        body { font-family: 'Inter Var', sans-serif; }
    </style>
</head>
<body class="d-flex flex-column">
    <div class="page page-center">
        <div class="container py-4">
            <div class="row align-items-center g-4">
                <div class="col-lg">
                    <div class="container-tight">
                        <div class="text-center mb-4">
                            <a href="."><img src="./static/logo.svg" height="36" alt=""></a>
                        </div>
                        <?php
                            if(isset($_GET['pesan'])){
                            if($_GET['pesan']== "belum_login"){
                                $_SESSION['gagal'] = 'anda belum login';
                            } else if ($_GET['pesan'] == "tolak_akses") {
                                $_SESSION['gagal'] = 'akses kehalaman ini ditolak';
                            }
                            }
                            ?>
                                        <?php if(isset($_SESSION['gagal'])): ?>
                                        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                                        <script>
                                            Swal.fire({
                                                icon: "error",
                                                title: "Oops...",
                                                text: "<?= htmlspecialchars($_SESSION['gagal']); ?>",
                                            });
                                        </script>
                                        <?php unset($_SESSION['gagal']); endif; ?>
                                        <div class="card card-md">
                                            <div class="card-body">
                                                <div class="text-center mb-4">
                                <a href="." class="navbar-brand navbar-brand-autodark"><img src="../../assets/img/images-removebg-preview.png" height="100" alt=""></a>
                            </div>
                                <h2 class="h2 text-center mb-4">Login Your Account</h2>
                                <form action="" method="post" autocomplete="off">
                                    <div class="mb-3">
                                        <label class="form-label">NIS</label>
                                        <input type="text" class="form-control" name="nis" placeholder="NIS" required>
                                    </div>
                                    <div class="form-footer d-flex justify-content-between gap-2">
                                        <button type="submit" name="login" class="btn btn-outline-primary w-50">Login</button>
                                        <a href="../../siswa/cari_nis/cari_nis.php" class="btn btn-outline-secondary w-50">Cari NIS</a>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg d-none d-lg-block">
                    <img src="../../assets/img/undraw_secure_login_pdn4.svg" height="300" class="d-block mx-auto" alt="">
                </div>
            </div>
        </div>
    </div>
</body>
</html>
