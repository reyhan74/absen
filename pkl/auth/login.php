<?php
session_start();
require_once('../config.php');  // Ensure this file contains the correct database connection

// Check database connection
if (!$conection) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST["login"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Prepared statement to prevent SQL injection
    $stmt = mysqli_prepare($conection, 'SELECT * FROM guru WHERE username = ?');
    
    // Check if the statement was prepared successfully
    if (!$stmt) {
        die("SQL prepare failed: " . mysqli_error($conection));
    }

    // Bind parameters
    mysqli_stmt_bind_param($stmt, 's', $username);
    
    // Execute the query
    mysqli_stmt_execute($stmt);
    
    // Get the result
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        // Check the password with password_verify
        if (password_verify($password, $user['password'])) {
            if ($user['status'] === 'aktif') {
                $_SESSION["login"] = true;
                $_SESSION['id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['lokasi_presensi'] = $user['lokasi_presensi'];
                if ($user['role'] === 'admin') {
                    header("location: ../admin/home/home.php");
                    exit();
                }
                if ($user['role'] === 'wali_murid') {
                    header("location: ../wali_murid/home/");
                    exit();
                } else {
                    header("location: ../pegawai/home/home.php");
                    exit();
                }
            } else {
                $_SESSION["gagal"] = "Akun anda belum aktif";
            }
        } else {
            $_SESSION["gagal"] = "Password Salah Silahkan Coba Lagi";
        }
    } else {
        $_SESSION["gagal"] = "Username Salah Silahkan Coba Lagi";
    }
}
?>

<html>
  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title>Sigin by rhn.id</title>
    <link rel="icon" type="png" href="../assets/img/logo_cb.png">
    <!-- CSS files -->
    <link href="../assets/css/tabler.min.css?1692870487" rel="stylesheet"/>
    <link href="../assets/css/tabler-flags.min.css?1692870487" rel="stylesheet"/>
    <link href="../assets/css/tabler-payments.min.css?1692870487" rel="stylesheet"/>
    <link href="../assets/css/tabler-vendors.min.css?1692870487" rel="stylesheet"/>
    <link href="../assets/css/demo.min.css?1692870487" rel="stylesheet"/>
    <style>
      @import url('https://rsms.me/inter/inter.css');
      :root {
        --tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
      }
      body {
        font-feature-settings: "cv03", "cv04", "cv11";
      }
    </style>
  </head>
  <body class="d-flex flex-column">
    <script src="./dist/js/demo-theme.min.js?1692870487"></script>
    <div class="page page-center">
      <div class="container container-normal py-4">
        <div class="row align-items-center g-4">
          <div class="col-lg">
            <div class="container-tight">
              

            <?php
            if(isset($_GET['pesan'])){
              if($_GET['pesan']== "belum_login"){
                $_SESSION['gagal'] = 'anda belum login';
              } else if ($_GET['pesan'] == "tolak_akses") {
                $_SESSION['gagal'] = 'akses kehalaman ini ditolak';
              }
            }
            ?>
        
                <div class="card card-md">
                <div class="card-body">
                    <div class="text-center mb-4">
                <a href="." class="navbar-brand navbar-brand-autodark"><img src="../assets/img/images-removebg-preview.png" height="100" alt=""></a>
              </div>
                    <h2 class="h2 text-center mb-4">Login Your Account</h2>

                <form action="" method="post" autocomplete="off" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" placeholder="Username" autocomplete="off" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Password</label>
                        <div class="input-group input-group-flat">
                        <input type="password" id="password" class="form-control" name="password" placeholder="Password" autocomplete="off" required>
                        <span class="input-group-text">
                            <a href="#" id="togglePassword" class="link-secondary" title="Show password">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler-eye" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
                            </svg>
                            </a>
                        </span>
                        </div>
                    </div>
                    <div class="form-footer">
                        <button type="submit" name="login" class="btn btn-outline-primary w-100">Sign in</button>
                    </div>
                </form>
                </div>
                <div class="hr-text">or</div>
            </div>
            <div class="text-center text-secondary mt-3">
                Don't have an account yet? <a href="./sign-up.html" tabindex="-1">Sign up</a>
            </div>
            </div>
            </div>
            <div class="col-lg d-none d-lg-block">
            <img src="../assets/img/undraw_secure_login_pdn4.svg" height="300" class="d-block mx-auto" alt="">
            </div>
        </div>
    </div>
    </div>

    <script>
    document.getElementById("togglePassword").addEventListener("click", function (e) {
        e.preventDefault(); // cegah link reload halaman
        const passwordInput = document.getElementById("password");
        const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
        passwordInput.setAttribute("type", type);

        // Opsional: Ganti ikon mata (eye/eye-off)
        this.innerHTML = type === "password"
        ? `<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler-eye" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
            <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
            </svg>`
        : `<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler-eye-off" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M3 3l18 18" />
            <path d="M10.584 10.587a2 2 0 0 0 2.827 2.828" />
            <path d="M9.878 5.878a9 9 0 0 1 10.606 6.122a9 9 0 0 1 -1.57 3.093" />
            <path d="M6.383 6.386a9 9 0 0 0 -2.383 5.614a9 9 0 0 0 9 9" />
            </svg>`;
    });
    </script>


    <!-- Libs JS -->
    <script src="../assets/libs/apexcharts/dist/apexcharts.min.js?1692870487" defer></script>
    <script src="../assets/libs/jsvectormap/dist/js/jsvectormap.min.js?1692870487" defer></script>
    <script src="../assets/libs/jsvectormap/dist/maps/world.js?1692870487" defer></script>
    <script src="../assets/libs/jsvectormap/dist/maps/world-merc.js?1692870487" defer></script>
    <!-- Tabler Core -->
    <script src="../assets/js/tabler.min.js?1692870487" defer></script>
    <script src="../assets/js/demo.min.js?1692870487" defer></script>
    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if (isset($_SESSION['gagal'])) { ?>
    <script>
    Swal.fire({
        icon: "error",
        title: "Oops...",
        text: "<?= htmlspecialchars($_SESSION['gagal']); ?>",  // Proper escaping to avoid XSS
    });
    </script>
    <?php unset($_SESSION['gagal']); ?>
<?php } ?>

    </body>
</html>
