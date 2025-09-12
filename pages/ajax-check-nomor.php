<?php
// pages/ajax-check-nomor.php
// session_start();
// require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

// Ambil data dari JavaScript
$nomor_urut = $_POST['nomor_urut'] ?? '';
// Ambil tahun dari request, default ke tahun saat ini jika tidak ada
$tahun = $_POST['tahun'] ?? date('Y');

// Validasi sederhana
if (empty($nomor_urut) || !is_numeric($nomor_urut) || !is_numeric($tahun)) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Nomor urut atau tahun tidak valid']);
    exit;
}

// Query sekarang memeriksa 'agenda_urut' DAN tahun dari 'tanggal_diterima'
$stmt = $pdo->prepare("SELECT COUNT(id) FROM surat_masuk WHERE agenda_urut = ? AND YEAR(tanggal_diterima) = ?");
$stmt->execute([$nomor_urut, $tahun]);
$count = $stmt->fetchColumn();

// Siapkan respons JSON
$response = [
    'exists' => $count > 0,
    'nomor' => $nomor_urut,
    'tahun' => $tahun
];

echo json_encode($response);
