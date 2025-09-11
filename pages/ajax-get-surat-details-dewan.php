<?php
// pages/ajax-get-surat-details-dewan.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';

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

$stmt = $pdo->prepare(
    "SELECT *,
            DATE_FORMAT(tanggal_surat, '%d %M %Y') as tgl_surat_formatted,
            DATE_FORMAT(tanggal_diterima, '%d %M %Y') as tgl_diterima_formatted,
            DATE_FORMAT(created_at, '%d %M %Y %H:%i') as tgl_input_formatted
     FROM surat_masuk_dewan WHERE id = ?"
);
$stmt->execute([$id]);
$surat = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$surat) {
    http_response_code(404);
    echo json_encode(['error' => 'Data surat tidak ditemukan']);
    exit;
}

header('Content-Type: application/json');
echo json_encode($surat);
?>
