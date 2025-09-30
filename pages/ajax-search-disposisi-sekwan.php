<?php
// pages/ajax-search-disposisi-sekwan.php

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

// Pengaturan Pagination, Pencarian, dan Pengurutan
$limit = 10;
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$offset = ($page > 0) ? ($page - 1) * $limit : 0;
$search = $_GET['search'] ?? '';
$year = $_GET['year'] ?? '';

// Pengurutan
$sort_col = $_GET['sort_col'] ?? 'tanggal_disposisi';
$sort_order = $_GET['sort_order'] ?? 'DESC';

// Whitelist untuk kolom yang bisa diurutkan
$allowed_cols = ['nomor_agenda_lengkap', 'perihal', 'nama_pegawai', 'tanggal_disposisi'];
$sort_col_validated = in_array($sort_col, $allowed_cols) ? $sort_col : 'tanggal_disposisi';

// Tentukan prefix tabel untuk kolom ambigu
$col_prefix = ($sort_col_validated === 'perihal' || $sort_col_validated === 'nomor_agenda_lengkap') ? 'sm.' : 'ds.';

$sort_order_validated = (strtoupper($sort_order) === 'ASC') ? 'ASC' : 'DESC';

// Siapkan query pencarian
$sql_where = "";
$params = [];
$conditions = [];

if (!empty($search)) {
    $conditions[] = "(sm.nomor_agenda_lengkap LIKE ? OR sm.perihal LIKE ? OR ds.nama_pegawai LIKE ?)";
    $search_param = "%$search%";
    array_push($params, $search_param, $search_param, $search_param);
}

if (!empty($year) && is_numeric($year)) {
    $conditions[] = "YEAR(ds.tanggal_disposisi) = ?";
    $params[] = $year;
}

if (!empty($conditions)) {
    $sql_where = "WHERE " . implode(' AND ', $conditions);
}

// Hitung total data untuk pagination
$stmt_count = $pdo->prepare(
    "SELECT COUNT(ds.id) 
     FROM disposisi_sekwan ds 
     JOIN surat_masuk sm ON ds.surat_masuk_id = sm.id " . $sql_where
);
$stmt_count->execute($params);
$total_records = $stmt_count->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Bangun klausa ORDER BY yang dinamis
$order_by_clause = "ORDER BY {$col_prefix}{$sort_col_validated} {$sort_order_validated}, ds.id DESC";

// Ambil data disposisi yang sudah digabung dengan surat masuk
$query = "SELECT ds.id, ds.nama_pegawai, ds.file_lampiran,
                 DATE_FORMAT(ds.tanggal_disposisi, '%d-%m-%Y %H:%i') as tgl_disposisi_formatted,
                 sm.nomor_agenda_lengkap, sm.perihal, sm.file_lampiran as surat_masuk_file
          FROM disposisi_sekwan ds
          JOIN surat_masuk sm ON ds.surat_masuk_id = sm.id "
          . $sql_where . " " . $order_by_clause . " LIMIT ? OFFSET ?";
          
$stmt_data = $pdo->prepare($query);

// Bind parameter secara dinamis
$param_index = 1;
foreach ($params as $param) {
    $stmt_data->bindValue($param_index++, $param, PDO::PARAM_STR);
}
$stmt_data->bindValue($param_index++, $limit, PDO::PARAM_INT);
$stmt_data->bindValue($param_index++, $offset, PDO::PARAM_INT);
$stmt_data->execute();
$disposisi_list = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

// Siapkan respons JSON
$response = [
    'disposisi_list' => $disposisi_list,
    'pagination' => [
        'current_page' => $page,
        'total_pages' => $total_pages,
        'search_query' => $search,
        'sort_col' => $sort_col,
        'sort_order' => $sort_order
    ]
];

header('Content-Type: application/json');
echo json_encode($response);
?>
