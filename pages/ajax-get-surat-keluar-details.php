<?php
// pages/ajax-get-surat-keluar-details.php

// File ini khusus untuk mengambil detail satu surat keluar via AJAX

// session_start(); // Tidak perlu, sudah ada di index.php
// require_once '../config/database.php'; // Tidak perlu, sudah ada di index.php

// Keamanan: Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    http_response_code(400);
    echo json_encode(['error' => 'ID Surat tidak valid']);
    exit;
}

// Ambil data detail surat dari database
// Kita format tanggalnya agar lebih mudah dibaca
$stmt = $pdo->prepare(
    "SELECT *,
            DATE_FORMAT(tanggal_surat, '%d %M %Y') as tgl_surat_formatted,
            DATE_FORMAT(created_at, '%d %M %Y %H:%i') as tgl_input_formatted
     FROM surat_keluar WHERE id = ?"
);
$stmt->execute([$id]);
$surat = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$surat) {
    http_response_code(404);
    echo json_encode(['error' => 'Data surat tidak ditemukan']);
    exit;
}

// Kirim data sebagai JSON
header('Content-Type: application/json');
echo json_encode($surat);
?>