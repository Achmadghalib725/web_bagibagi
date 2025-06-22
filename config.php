
<?php
// config/config.php

// Mulai session di setiap halaman yang membutuhkan login
// Cek dulu apakah sesi sudah aktif sebelum memulai yang baru
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Detail Koneksi Database
$db_host = 'localhost';
$db_user = 'root'; // Ganti dengan username database Anda
$db_pass = '';     // Ganti dengan password database Anda
$db_name = 'manajementugas_db'; // Nama database Anda

// Membuat koneksi ke database
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Cek koneksi
if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

?>