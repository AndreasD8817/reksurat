<?php
// pages/ajax-check-nomor.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

// Ambil data dari JavaScript
$nomor_urut = $_POST['nomor_urut'] ?? '';

// Validasi sederhana
if (empty($nomor_urut) || !is_numeric($nomor_urut)) {
    header('Content-Type: application/json');
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Nomor urut tidak valid']);
    exit;
}

// Query sekarang hanya ke kolom 'agenda_urut'
$stmt = $pdo->prepare("SELECT COUNT(id) FROM surat_masuk WHERE agenda_urut = ?");
$stmt->execute([$nomor_urut]);
$count = $stmt->fetchColumn();

// Siapkan respons JSON
$response = [
    'exists' => $count > 0,
    'nomor' => $nomor_urut
];

header('Content-Type: application/json');
echo json_encode($response);