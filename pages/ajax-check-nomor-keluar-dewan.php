<?php
// pages/ajax-check-nomor-keluar-dewan.php

// Keamanan: Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

$nomor_urut = $_POST['nomor_urut'] ?? '';

if (empty($nomor_urut) || !is_numeric($nomor_urut)) {
    header('Content-Type: application/json');
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Nomor urut tidak valid']);
    exit;
}

// Cek ke tabel surat_keluar_dewan
$stmt = $pdo->prepare("SELECT COUNT(id) FROM surat_keluar_dewan WHERE nomor_urut = ?");
$stmt->execute([$nomor_urut]);
$count = $stmt->fetchColumn();

// Siapkan respons JSON
$response = [
    'exists' => $count > 0,
    'nomor' => $nomor_urut
];

header('Content-Type: application/json');
echo json_encode($response);
?>
