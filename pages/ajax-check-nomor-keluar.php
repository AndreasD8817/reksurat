<?php
// pages/ajax-check-nomor-keluar.php
// session_start();
// require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

// Ambil nomor urut dan tahun yang akan dicek
$nomor_urut = $_POST['nomor_urut'] ?? '';
$tahun = $_POST['tahun'] ?? date('Y');

if (empty($nomor_urut) || !is_numeric($nomor_urut) || !is_numeric($tahun)) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Nomor urut atau tahun tidak valid']);
    exit;
}

// Cek ke tabel surat_keluar berdasarkan nomor urut DAN tahun
$stmt = $pdo->prepare("SELECT COUNT(id) FROM surat_keluar WHERE nomor_urut = ? AND YEAR(tanggal_surat) = ?");
$stmt->execute([$nomor_urut, $tahun]);
$count = $stmt->fetchColumn();

// Siapkan respons JSON
$response = [
    'exists' => $count > 0,
    'nomor' => $nomor_urut,
    'tahun' => $tahun
];

echo json_encode($response);
