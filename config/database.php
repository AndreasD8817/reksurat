<?php
// config/database.php

// Ambil konfigurasi dari variabel lingkungan (.env)
// Operator '??' memberikan nilai default jika variabel tidak ditemukan.
$host = $_ENV['DB_HOST'] ?? 'localhost';
$dbname = $_ENV['DB_NAME'] ?? 'reksurat';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';

// Opsi koneksi PDO yang direkomendasikan untuk keamanan dan efisiensi
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Buat koneksi menggunakan PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, $options);
} catch (PDOException $e) {
    // Di lingkungan produksi, catat error ke log (sudah diatur di index.php)
    error_log("Koneksi database gagal: " . $e->getMessage());
    // Tampilkan pesan yang ramah ke pengguna tanpa membocorkan detail teknis.
    // Muat halaman error khusus dan hentikan eksekusi skrip.
    require_once __DIR__ . '/../pages/db_error.php';
    exit;
}
?>