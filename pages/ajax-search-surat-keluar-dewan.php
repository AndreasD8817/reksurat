<?php
// pages/ajax-search-surat-keluar-dewan.php

// Keamanan: Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

// Pengaturan Pagination & Pencarian
$limit = 10; 
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($page > 0) ? ($page - 1) * $limit : 0;
$search = $_GET['search'] ?? '';

$sql_where = "";
$params = [];
if (!empty($search)) {
    $sql_where = "WHERE nomor_surat_lengkap LIKE ? OR perihal LIKE ? OR tujuan LIKE ?";
    $search_param = "%$search%";
    $params = [$search_param, $search_param, $search_param];
}

// Hitung total data dari tabel surat_keluar_dewan
$stmt_count = $pdo->prepare("SELECT COUNT(id) FROM surat_keluar_dewan " . $sql_where);
$stmt_count->execute($params);
$total_records = $stmt_count->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Ambil data surat dari tabel surat_keluar_dewan
$params_data = $params;
$params_data[] = $limit;
$params_data[] = $offset;

$query = "SELECT *, DATE_FORMAT(tanggal_surat, '%d-%m-%Y') as tgl_formatted FROM surat_keluar_dewan " . $sql_where . " ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt_data = $pdo->prepare($query);

$param_index = 1;
foreach ($params as $param) {
    $stmt_data->bindValue($param_index++, $param, PDO::PARAM_STR);
}
$stmt_data->bindValue($param_index++, $limit, PDO::PARAM_INT);
$stmt_data->bindValue($param_index, $offset, PDO::PARAM_INT);
$stmt_data->execute();
$surat_list = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

// Siapkan data respons
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

