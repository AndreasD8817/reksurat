<?php
// pages/ajax-check-nomor-agenda-dewan.php

// Cegah output error PHP merusak JSON
error_reporting(0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Gunakan require_once agar file tidak dimuat ulang jika sudah ada
require_once __DIR__ . '/../config/database.php';

// Atur header sebagai JSON di awal untuk memastikan output yang benar
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

$nomor_urut = $_POST['nomor_urut'] ?? '';

if (empty($nomor_urut) || !is_numeric($nomor_urut)) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Nomor urut tidak valid']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT COUNT(id) FROM surat_masuk_dewan WHERE agenda_urut = ?");
    $stmt->execute([$nomor_urut]);
    $count = $stmt->fetchColumn();

    $response = [
        'exists' => $count > 0,
        'nomor' => $nomor_urut
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Terjadi masalah pada database.']);
}
?>

