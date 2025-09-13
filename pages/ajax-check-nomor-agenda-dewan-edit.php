<?php
// pages/ajax-check-nomor-agenda-dewan-edit.php

// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }
// require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

$nomor_urut = $_POST['nomor_urut'] ?? '';
$tahun = $_POST['tahun'] ?? date('Y');
$id_surat = $_POST['id'] ?? '';

if (empty($nomor_urut) || !is_numeric($nomor_urut) || !is_numeric($tahun) || empty($id_surat) || !is_numeric($id_surat)) {
    http_response_code(400);
    echo json_encode(['error' => 'Input tidak valid']);
    exit;
}

$stmt = $pdo->prepare("SELECT COUNT(id) FROM surat_masuk_dewan WHERE agenda_urut = ? AND YEAR(tanggal_diterima) = ? AND id != ?");
$stmt->execute([$nomor_urut, $tahun, $id_surat]);
$count = $stmt->fetchColumn();

$response = [
    'exists' => $count > 0,
    'nomor' => $nomor_urut,
    'tahun' => $tahun
];

echo json_encode($response);
?>
