<?php
// config/database.php

$host = 'localhost';
$dbname = 'reksurat';
$user = 'root'; // User default Laragon
$pass = '';     // Password default Laragon kosong

try {
    // Buat koneksi menggunakan PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    
    // Set error mode ke exception untuk penanganan error yang lebih baik
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    // Tampilkan pesan error jika koneksi gagal
    die("Koneksi database gagal: " . $e->getMessage());
}
?>