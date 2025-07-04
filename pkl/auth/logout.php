<?php
session_start(); // Wajib: Memulai sesi dulu

session_destroy(); // Hancurkan semua data sesi

// Pastikan tidak ada output sebelum header
header("Location: login.php"); // Redirect ke halaman login
exit(); // Opsional, untuk memastikan skrip berhenti
?>

