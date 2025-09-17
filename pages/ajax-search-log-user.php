<?php
// pages/ajax-search-log-user.php

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

$limit = 15;
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($page > 0) ? ($page - 1) * $limit : 0;
$search = $_GET['search'] ?? '';

$sql_where = "";
$params = [];

if (!empty($search)) {
    $sql_where = "WHERE u.nama LIKE ? OR l.kegiatan LIKE ?";
    $search_param = "%$search%";
    array_push($params, $search_param, $search_param);
}

$query_base = "FROM log_user l JOIN users u ON l.user_id = u.id " . $sql_where;

// Hitung total data
$stmt_count = $pdo->prepare("SELECT COUNT(l.id) " . $query_base);
$stmt_count->execute($params);
$total_records = $stmt_count->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Ambil data log
$query_data = "SELECT l.id, u.nama as user_nama, l.kegiatan, l.detail,
                      DATE_FORMAT(l.waktu, '%d-%m-%Y') as tanggal,
                      DATE_FORMAT(l.waktu, '%H:%i:%s') as jam "
              . $query_base . " ORDER BY l.id DESC LIMIT ? OFFSET ?";

$stmt_data = $pdo->prepare($query_data);

$param_index = 1;
foreach ($params as $param) {
    $stmt_data->bindValue($param_index++, $param, PDO::PARAM_STR);
}
$stmt_data->bindValue($param_index++, $limit, PDO::PARAM_INT);
$stmt_data->bindValue($param_index++, $offset, PDO::PARAM_INT);
$stmt_data->execute();
$logs = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

$response = [
    'logs' => $logs,
    'pagination' => [
        'current_page' => $page,
        'total_pages' => $total_pages,
        'search_query' => $search
    ]
];

header('Content-Type: application/json');
echo json_encode($response);
?>