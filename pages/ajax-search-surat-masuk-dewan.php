<?php
// pages/ajax-search-surat-masuk-dewan.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

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
if (strtoupper($sort_order) !== 'ASC' && strtoupper($sort_order) !== 'DESC') {
    $sort_order = 'DESC'; // Default jika urutan tidak valid
}
// --- AKHIR BARU ---

$sql_where = "";
$params = [];
$conditions = [];

if (!empty($search)) {
    $conditions[] = "(smd.nomor_agenda_lengkap LIKE ? OR smd.nomor_surat_lengkap LIKE ? OR smd.asal_surat LIKE ? OR smd.perihal LIKE ?)";
    $search_param = "%$search%";
    array_push($params, $search_param, $search_param, $search_param, $search_param);
}

if (!empty($year) && is_numeric($year)) {
    $conditions[] = "YEAR(smd.tanggal_diterima) = ?";
    $params[] = $year;
}

if (!empty($conditions)) {
    $sql_where = "WHERE " . implode(' AND ', $conditions);
}

$count_query = "SELECT COUNT(smd.id) FROM surat_masuk_dewan smd " . $sql_where;
$stmt_count = $pdo->prepare($count_query);
$stmt_count->execute($params);
$total_records = $stmt_count->fetchColumn();
$total_pages = ceil($total_records / $limit);

$query = "SELECT smd.*, DATE_FORMAT(smd.tanggal_diterima, '%d-%m-%Y') as tgl_terima_formatted, dd.id as disposisi_id FROM surat_masuk_dewan smd LEFT JOIN disposisi_dewan dd ON smd.id = dd.surat_masuk_id " . $sql_where . " ORDER BY smd.{$sort_col} {$sort_order}, smd.id DESC LIMIT ? OFFSET ?";
$stmt_data = $pdo->prepare($query);

$param_index = 1;
foreach ($params as $param) {
    $stmt_data->bindValue($param_index++, $param, PDO::PARAM_STR);
}
$stmt_data->bindValue($param_index++, $limit, PDO::PARAM_INT);
$stmt_data->bindValue($param_index++, $offset, PDO::PARAM_INT);
$stmt_data->execute();
$surat_list = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

$response = [
    'surat_list' => $surat_list,
    'pagination' => [
        'current_page' => $page,
        'total_pages' => $total_pages,
        'search_query' => $search
    ]
];

header('Content-Type: application/json');
echo json_encode($response);
?>
