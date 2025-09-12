<?php
// pages/ajax-check-nomor-agenda-dewan.php

error_reporting(0);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

$nomor_urut = $_POST['nomor_urut'] ?? '';
$tahun = $_POST['tahun'] ?? date('Y');

if (empty($nomor_urut) || !is_numeric($nomor_urut) || !is_numeric($tahun)) {
    http_response_code(400);
    echo json_encode(['error' => 'Nomor urut atau tahun tidak valid']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT COUNT(id) FROM surat_masuk_dewan WHERE agenda_urut = ? AND YEAR(tanggal_diterima) = ?");
    $stmt->execute([$nomor_urut, $tahun]);
    $count = $stmt->fetchColumn();

    $response = [
        'exists' => $count > 0,
        'nomor' => $nomor_urut,
        'tahun' => $tahun
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Terjadi masalah pada database.']);
}
?>
