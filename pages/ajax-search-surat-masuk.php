<?php
// pages/ajax-search-surat-masuk.php

// Memulai session dan memuat koneksi database
// session_start();
// require_once '../config/database.php';

// Pastikan hanya user yang sudah login yang bisa akses
if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

// Pengaturan Pagination & Pencarian
$limit = 10;
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($page > 0) ? ($page - 1) * $limit : 0;
$search = $_GET['search'] ?? '';
$year = $_GET['year'] ?? '';

// --- BARU: Pengaturan Pengurutan ---
$sort_col = $_GET['sort_col'] ?? 'tanggal_diterima';
$sort_order = $_GET['sort_order'] ?? 'DESC';

// Whitelist untuk kolom yang bisa diurutkan demi keamanan
$allowed_cols = ['nomor_agenda_lengkap', 'asal_surat', 'perihal', 'tanggal_diterima', 'tanggal_surat'];
if (!in_array($sort_col, $allowed_cols)) {
    $sort_col = 'tanggal_diterima'; // Default jika kolom tidak valid
}
// Pastikan urutan hanya ASC atau DESC
if (strtoupper($sort_order) !== 'ASC' && strtoupper($sort_order) !== 'DESC') {
    $sort_order = 'DESC'; // Default jika urutan tidak valid
}
// --- AKHIR BARU ---

// Siapkan query pencarian
$sql_where = "";
$params = [];
$conditions = [];

if (!empty($search)) {
    $conditions[] = "(sm.nomor_agenda_lengkap LIKE ? OR sm.nomor_surat_lengkap LIKE ? OR sm.asal_surat LIKE ? OR sm.perihal LIKE ?)";
    $search_param = "%$search%";
    array_push($params, $search_param, $search_param, $search_param, $search_param);
}

if (!empty($year) && is_numeric($year)) {
    $conditions[] = "YEAR(tanggal_diterima) = ?";
    $params[] = $year;
}

if (!empty($conditions)) {
    $sql_where = "WHERE " . implode(' AND ', $conditions);
}

$query_base = "FROM surat_masuk sm LEFT JOIN disposisi_sekwan ds ON sm.id = ds.surat_masuk_id " . $sql_where;

// Hitung total data untuk pagination
$stmt_count = $pdo->prepare("SELECT COUNT(sm.id) " . $query_base);
$stmt_count->execute($params);
$total_records = $stmt_count->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Ambil data surat
$stmt_data = $pdo->prepare(
    "SELECT sm.*, 
            DATE_FORMAT(sm.tanggal_surat, '%d-%m-%Y') as tgl_surat_formatted, 
            DATE_FORMAT(sm.tanggal_diterima, '%d-%m-%Y') as tgl_terima_formatted,
            ds.id as disposisi_id
     " . $query_base . " 
     ORDER BY sm.{$sort_col} {$sort_order}, sm.id DESC LIMIT ? OFFSET ?"
);

// Bind parameters
$param_index = 1;
foreach ($params as $param) {
    $stmt_data->bindValue($param_index++, $param, PDO::PARAM_STR);
}
$stmt_data->bindValue($param_index++, $limit, PDO::PARAM_INT);
$stmt_data->bindValue($param_index++, $offset, PDO::PARAM_INT);
$stmt_data->execute();
$surat_masuk_list = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

// Siapkan data respons dalam bentuk array
$response = [
    'surat_list' => $surat_masuk_list,
    'pagination' => [
        'current_page' => $page,
        'total_pages' => $total_pages,
        'search_query' => $search
    ]
];

// Set header sebagai JSON dan kirim data
header('Content-Type: application/json');
echo json_encode($response);