<?php
// pages/ajax-check-nomor-keluar-edit.php
// File ini khusus untuk menangani pengecekan nomor urut di halaman edit

// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }
// require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

// Ambil nomor urut, tahun, dan ID surat yang sedang diedit
$nomor_urut = $_POST['nomor_urut'] ?? '';
$tahun = $_POST['tahun'] ?? date('Y');
$id_surat = $_POST['id'] ?? '';

// Validasi input
if (empty($nomor_urut) || !is_numeric($nomor_urut) || !is_numeric($tahun) || empty($id_surat) || !is_numeric($id_surat)) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Input tidak valid']);
    exit;
}

// Cek ke database, KECUALI untuk ID surat yang sedang diedit
$stmt = $pdo->prepare("SELECT COUNT(id) FROM surat_keluar WHERE nomor_urut = ? AND YEAR(tanggal_surat) = ? AND id != ?");
$stmt->execute([$nomor_urut, $tahun, $id_surat]);
$count = $stmt->fetchColumn();

// Siapkan respons JSON
$response = [
    'exists' => $count > 0,
    'nomor' => $nomor_urut,
    'tahun' => $tahun
];

echo json_encode($response);
?>
