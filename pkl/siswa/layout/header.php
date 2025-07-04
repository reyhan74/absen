<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Konfigurasi judul halaman dan nama user dari sesi
$page_title = $page_title ?? 'Dashboard';
$nama = $_SESSION['nama'] ?? 'User ';
$foto = $_SESSION['foto'] ?? '';

// Simpan data presensi dalam session (jika diperlukan)
$_SESSION['data_presensi'] = [
    'nama' => $nama,
    'foto' => $foto,
];

// Fungsi untuk ambil path foto
function getProfilePhoto($foto) {
    $path = __DIR__ . "/" . $foto;
    if (!empty($foto) && file_exists($path)) {
        return htmlspecialchars($foto);
    } else {
        return '../../assets/img/download.jpg';
    }
}

$foto_path = getProfilePhoto($foto);

// Load konfigurasi global
require_once('../../config.php');
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title>Absensi Siswa</title>
    <link rel="icon" type="png" href="../../assets/img/logo_cb.png">
    <!-- CSS files -->
    <link href="../../assets/css/tabler.min.css?1692870487" rel="stylesheet"/>
    <link href="../../assets/css/tabler-flags.min.css?1692870487" rel="stylesheet"/>
    <link href="../../assets/css/tabler-payments.min.css?1692870487" rel="stylesheet"/>
    <link href="../../assets/css/tabler-vendors.min.css?1692870487" rel="stylesheet"/>
    <link href="../../assets/css/demo.min.css?1692870487" rel="stylesheet"/>
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
<body>
    <script src="./dist/js/demo-theme.min.js?1692870487"></script>
    <div class="page">
        <!-- Navbar -->
        <nav class="sticky-top">
            <header class="navbar navbar-expand-md d-print-none">
                <div class="container-xl">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
                        <a href="#">
                            <h1 class="navbar-brand-image">Absensi Siswa</h1>
                        </a>
                    </h1>
                    <div class="navbar-nav flex-row order-md-last">
                        <div class="nav-item dropdown">
                            <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown">
                                <span class="avatar" style="background-image: url('<?= $foto_path ?>')"></span>
                                <div class="d-none d-xl-block ps-2">
                                    <div><?= htmlspecialchars($nama) ?></div>
                                    <div class="mt-1 small text-secondary">SMK CB PARE</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </header>
            <header class="navbar-expand-md">
                <div class="collapse navbar-collapse" id="navbar-menu">
                    <div class="navbar">
                        <div class="container-xl">
                            <ul class="navbar-nav">
                                <li class="nav-item">
                                    <a class="nav-link" href="../../siswa/home/home.php">
                                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <path d="M5 12l-2 0l9 -9l9 9l-2 0" />
                                                <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" />
                                                <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" />
                                            </svg>
                                        </span>
                                        <span class="nav-link-title">Beranda</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="../catatan/catatan.php">
                                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-clipboard-check">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" />
                                                <path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" />
                                                <path d="M9 14l2 2l4 -4" />
                                            </svg>
                                        </span>
                                        <span class="nav-link-title">Catatan</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="../../auth/siswa/logout.php">
                                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-logout">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" />
                                                <path d="M9 12h12l-3 -3" />
                                                <path d="M18 15l3 -3" />
                                            </svg>
                                        </span>
                                        <span class="nav-link-title">keluar</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </header>
        </nav>
        <div class="page-wrapper">
