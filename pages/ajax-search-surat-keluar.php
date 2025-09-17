<?php
// pages/ajax-search-surat-keluar.php

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

// Siapkan query
$sql_where = "";
$params = [];
$conditions = [];

if (!empty($search)) {
    $conditions[] = "(nomor_surat_lengkap LIKE ? OR perihal LIKE ? OR tujuan LIKE ?)";
    $search_param = "%$search%";
    array_push($params, $search_param, $search_param, $search_param);
}

if (!empty($year) && is_numeric($year)) {
    $conditions[] = "YEAR(tanggal_surat) = ?";
    $params[] = $year;
}

if (!empty($conditions)) {
    $sql_where = "WHERE " . implode(' AND ', $conditions);
}

// Hitung total data untuk pagination
$stmt_count = $pdo->prepare("SELECT COUNT(id) FROM surat_keluar " . $sql_where);
$stmt_count->execute($params);
$total_records = $stmt_count->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Ambil data surat
$stmt_data = $pdo->prepare("SELECT *, DATE_FORMAT(tanggal_surat, '%d-%m-%Y') as tgl_formatted FROM surat_keluar " . $sql_where . " ORDER BY id DESC LIMIT ? OFFSET ?");
$param_index = 1;
foreach ($params as $param) {
    $stmt_data->bindValue($param_index++, $param, PDO::PARAM_STR);
}
$stmt_data->bindValue($param_index++, $limit, PDO::PARAM_INT);
$stmt_data->bindValue($param_index++, $offset, PDO::PARAM_INT);
$stmt_data->execute();
$surat_keluar_list = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

// Siapkan data respons dalam bentuk array
$response = [
    'surat_list' => $surat_keluar_list,
    'pagination' => [
        'current_page' => $page,
        'total_pages' => $total_pages,
        'search_query' => $search
    ]
];

// Set header sebagai JSON dan kirim data
header('Content-Type: application/json');
echo json_encode($response);
