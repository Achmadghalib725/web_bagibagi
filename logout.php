
<?php
// logout.php

// Langkah 1: Selalu mulai sesi di awal untuk bisa mengaksesnya.
session_start();

// Langkah 2: Kosongkan semua variabel sesi.
// Ini seperti membersihkan semua data dari laci meja.
$_SESSION = array();

// Langkah 3: Hancurkan sesi secara permanen.
// Ini adalah langkah final yang menghapus 'kunci kamar' (session ID) dari server.
session_destroy();

// Langkah 4: Arahkan pengguna kembali ke halaman login.
// Kita juga bisa menambahkan parameter di URL untuk menampilkan pesan.
header("Location: login.php?status=loggedout");
exit; // Pastikan untuk keluar dari skrip setelah redirect.


?>