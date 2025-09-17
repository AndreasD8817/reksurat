<?php
// pages/ajax-get-next-nomor.php

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

$type = $_GET['type'] ?? '';
$tahun = $_GET['tahun'] ?? date('Y');

if (empty($type) || empty($tahun) || !is_numeric($tahun)) {
    http_response_code(400);
    echo json_encode(['error' => 'Input tidak valid']);
    exit;
}

$table_map = [
    'keluar' => ['table' => 'surat_keluar', 'nomor_col' => 'nomor_urut', 'tgl_col' => 'tanggal_surat'],
    'masuk' => ['table' => 'surat_masuk', 'nomor_col' => 'agenda_urut', 'tgl_col' => 'tanggal_diterima'],
    'keluar-dewan' => ['table' => 'surat_keluar_dewan', 'nomor_col' => 'nomor_urut', 'tgl_col' => 'tanggal_surat'],
    'masuk-dewan' => ['table' => 'surat_masuk_dewan', 'nomor_col' => 'agenda_urut', 'tgl_col' => 'tanggal_diterima'],
];

if (!array_key_exists($type, $table_map)) {
    http_response_code(400);
    echo json_encode(['error' => 'Tipe surat tidak valid']);
    exit;
}

$config = $table_map[$type];
$table = $config['table'];
$nomor_col = $config['nomor_col'];
$tgl_col = $config['tgl_col'];

try {
    // Mengambil nomor urut maksimum sebagai integer
    $stmt = $pdo->prepare("SELECT MAX(CAST($nomor_col AS UNSIGNED)) FROM $table WHERE YEAR($tgl_col) = ?");
    $stmt->execute([$tahun]);
    $max_nomor = $stmt->fetchColumn();
    
    // Jika tidak ada nomor untuk tahun itu, mulai dari 1. Jika ada, tambahkan 1.
    $next_nomor = ($max_nomor) ? (int)$max_nomor + 1 : 1;

    echo json_encode(['next_nomor' => $next_nomor]);

} catch (PDOException $e) {
    // Log error untuk debugging, jangan tampilkan detail ke user
    error_log("AJAX Get Next Nomor Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Terjadi masalah pada server.']);
}
?>