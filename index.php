<?php
// reksurat/index.php

// Mulai session untuk manajemen login
session_start();

// Panggil file koneksi database
require_once 'config/database.php';

// Cek apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Ambil halaman yang diminta dari URL, defaultnya 'login'
// Contoh: http://reksurat.test/?page=surat-keluar
$page = $_GET['page'] ?? 'login';

// Routing sederhana
if (!isLoggedIn() && $page !== 'login') {
    // Jika belum login dan mencoba akses halaman lain, paksa ke login
    header('Location: /?page=login');
    exit;
}

// Tentukan file halaman yang akan dimuat
$pageFile = "pages/{$page}.php";

// Jika file halaman ada, muat file tersebut. Jika tidak, tampilkan error 404.
if (file_exists($pageFile)) {
    require_once $pageFile;
} else {
    // Halaman tidak ditemukan
    http_response_code(404);
    echo "<h1>404 - Halaman Tidak Ditemukan</h1>";
    echo "Maaf, halaman yang Anda cari tidak ada.";
}
?>