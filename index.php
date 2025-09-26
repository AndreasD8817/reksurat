<?php
// reksurat/index.php

// --- KONFIGURASI ERROR LOGGING ---
// 1. Nonaktifkan tampilan error ke pengguna untuk keamanan
ini_set('display_errors', 0);
// 2. Aktifkan logging error ke dalam file
ini_set('log_errors', 1);
// 3. Tentukan nama dan lokasi file log
ini_set('error_log', __DIR__ . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'error_php.log');
// 4. Atur agar semua jenis error (kecuali notice) tercatat
error_reporting(E_ALL & ~E_NOTICE);

// --- PENGATURAN MODE MAINTENANCE ---
// Ubah menjadi 'true' untuk mengaktifkan halaman maintenance.
$maintenance_mode = false;

// --- MEMUAT DEPENDENSI & VARIABEL LINGKUNGAN ---
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


// Mulai session untuk manajemen login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- AKTIVASI APLIKASI TAHUNAN ---
require_once __DIR__ . '/config/secrets.php'; // Memuat semua konstanta aktivasi

$currentYear = (int)date('Y');
$route = $_GET['route'] ?? 'login'; // Ambil route lebih awal

// Cek cookie aktivasi terlebih dahulu
if (isset($_COOKIE[ACTIVATION_COOKIE_NAME])) {
    $expected_token = md5($currentYear . ACTIVATION_SECRET_SALT . ACTIVATION_COOKIE_SALT);
    if (hash_equals($expected_token, $_COOKIE[ACTIVATION_COOKIE_NAME])) {
        // Cookie valid, pulihkan sesi aktivasi
        $_SESSION['app_activated_year'] = $currentYear;
    }
}

// Cek apakah aplikasi sudah diaktifkan untuk tahun ini (setelah pengecekan cookie)
if (!isset($_SESSION['app_activated_year']) || $_SESSION['app_activated_year'] !== $currentYear) {
    // Jika belum diaktifkan dan bukan halaman aktivasi/login, redirect
    if ($route !== 'activate' && $route !== 'login' && $route !== 'logout') {
        header('Location: /activate');
        exit;
    }
}
// --- AKHIR AKTIVASI APLIKASI TAHUNAN ---

// Cek jika mode maintenance aktif
if ($maintenance_mode) {
    require_once 'pages/maintenance.php';
    exit;
}

// Panggil file koneksi database
require_once 'config/database.php';

// Cek apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// --- FUNGSI KEAMANAN CSRF ---
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        // Token valid, hapus token lama agar tidak bisa digunakan lagi (opsional tapi lebih aman)
        unset($_SESSION['csrf_token']);
        return true;
    }
    return false;
}

// Generate token untuk digunakan di form
$csrf_token = generate_csrf_token();

// Ambil halaman yang diminta dari URL, defaultnya 'login'
$route = $_GET['route'] ?? 'login';

// Routing sederhana
if (!isLoggedIn() && $route !== 'login') {
    // Jika belum login dan mencoba akses halaman lain, paksa ke login
    header('Location: /login');
    exit;
}

// Tentukan file halaman yang akan dimuat
$pageFile = "pages/{$route}.php";

// Jika file halaman ada, muat file tersebut. Jika tidak, tampilkan halaman 404.
if (file_exists($pageFile)) {
    require_once $pageFile;
} else {
    // Halaman tidak ditemukan, tampilkan halaman 404 kustom
    http_response_code(404);
    require_once 'pages/404.php';
}
?>