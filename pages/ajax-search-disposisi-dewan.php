<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['superadmin', 'admin'])) {
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

// Ambil parameter dari GET request
$searchTerm = $_GET['search'] ?? '';
$filter_tahun = $_GET['filter_tahun'] ?? '';
$limit = 10;
$page_num = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
$offset = ($page_num - 1) * $limit;

// Pengurutan
$sort_col = $_GET['sort_col'] ?? 'tanggal_disposisi';
$sort_order = $_GET['sort_order'] ?? 'DESC';

// Whitelist untuk kolom yang bisa diurutkan
$allowed_cols = ['nomor_agenda_lengkap', 'perihal', 'nama_pegawai', 'tanggal_disposisi'];
$sort_col_validated = in_array($sort_col, $allowed_cols) ? $sort_col : 'tanggal_disposisi';
$sort_order_validated = (strtoupper($sort_order) === 'ASC') ? 'ASC' : 'DESC';

// Tentukan prefix tabel untuk kolom ambigu
$col_prefix = ($sort_col_validated === 'perihal' || $sort_col_validated === 'nomor_agenda_lengkap') ? 'smd.' : 'dd.';

$params = [];
$where_clauses = [];

if ($searchTerm) {
    $where_clauses[] = "(smd.nomor_agenda_lengkap LIKE ? OR smd.perihal LIKE ? OR dd.nama_pegawai LIKE ?)";
    $params[] = "%{$searchTerm}%";
    $params[] = "%{$searchTerm}%";
    $params[] = "%{$searchTerm}%";
}

if ($filter_tahun) {
    $where_clauses[] = "YEAR(dd.tanggal_disposisi) = ?";
    $params[] = $filter_tahun;
}

$where_sql = "";
if (!empty($where_clauses)) {
    $where_sql = " WHERE " . implode(" AND ", $where_clauses);
}

// Bangun klausa ORDER BY yang dinamis
$order_by_clause = "ORDER BY {$col_prefix}{$sort_col_validated} {$sort_order_validated}, dd.id DESC";

// Query untuk mengambil data disposisi dewan
$sql = "SELECT dd.id, dd.nama_pegawai, dd.file_lampiran, 
               DATE_FORMAT(dd.tanggal_disposisi, '%d-%m-%Y %H:%i') as tgl_disposisi_formatted, 
               smd.nomor_agenda_lengkap, smd.perihal
        FROM disposisi_dewan dd
        JOIN surat_masuk_dewan smd ON dd.surat_masuk_id = smd.id
        $where_sql
        $order_by_clause
        LIMIT $limit OFFSET $offset";

$count_sql = "SELECT COUNT(dd.id) FROM disposisi_dewan dd JOIN surat_masuk_dewan smd ON dd.surat_masuk_id = smd.id $where_sql";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$disposisi_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Generate HTML untuk baris tabel
$html = '';
if (empty($disposisi_list)) {
    $html = '<tr><td colspan="5" class="text-center py-10 text-gray-500"><i class="fas fa-folder-open fa-3x mb-3"></i><p>Tidak ada data untuk ditampilkan.</p></td></tr>';
} else {
    foreach ($disposisi_list as $disposisi) {
        $nomor_agenda_html = htmlspecialchars($disposisi['nomor_agenda_lengkap']);
        if (!empty($disposisi['file_lampiran'])) {
            $nomor_agenda_html = '<a href="#" class="text-primary hover:underline pdf-modal-trigger" data-pdf-src="/uploads-dewan/' . htmlspecialchars($disposisi['file_lampiran']) . '" data-agenda-no="' . htmlspecialchars($disposisi['nomor_agenda_lengkap']) . '">' . $nomor_agenda_html . '</a>';
        }

        $html .= '<tr class="hover:bg-blue-50 transition-colors duration-200">';
        $html .= '<td class="px-4 md:px-6 py-3 md:py-4 font-medium">';
        $html .= $nomor_agenda_html;
        $html .= '<div class="md:hidden text-sm text-gray-600 mt-1">';
        $html .= '<div class="truncate">Perihal: ' . htmlspecialchars($disposisi['perihal']) . '</div>';
        $html .= '<div>Tujuan: ' . htmlspecialchars($disposisi['nama_pegawai']) . '</div>';
        $html .= '<div>Tgl: ' . htmlspecialchars($disposisi['tgl_disposisi_formatted']) . '</div>';
        $html .= '</div>';
        $html .= '</td>';
        $html .= '<td class="px-4 md:px-6 py-3 md:py-4 text-gray-600 hidden md:table-cell">' . htmlspecialchars($disposisi['perihal']) . '</td>';
        $html .= '<td class="px-4 md:px-6 py-3 md:py-4 text-gray-600 hidden md:table-cell">' . htmlspecialchars($disposisi['nama_pegawai']) . '</td>';
        $html .= '<td class="px-4 md:px-6 py-3 md:py-4 text-gray-600 hidden md:table-cell text-sm">' . htmlspecialchars($disposisi['tgl_disposisi_formatted']) . '</td>';
        $html .= '<td class="px-4 md:px-6 py-3 md:py-4 text-sm font-medium"><div class="flex space-x-2">';
        $html .= '<a href="/edit-disposisi-dewan?id=' . $disposisi['id'] . '" class="text-blue-500 hover:text-blue-700" title="Edit Disposisi"><i class="fas fa-edit"></i></a>';
        if (in_array($_SESSION['user_role'], ['superadmin', 'admin'])) {
            $html .= '<button onclick="confirmDelete(\'disposisi-dewan\', ' . $disposisi['id'] . ')" class="text-red-500 hover:text-red-700" title="Batalkan Disposisi"><i class="fas fa-trash-alt"></i></button>';
        }
        $html .= '</div></td>';
        $html .= '</tr>';
    }
}

// Generate HTML untuk pagination
$pagination = '';
if ($total_pages > 1) {
    $pagination .= '<nav aria-label="Page navigation"><ul class="inline-flex items-center -space-x-px">';
    for ($i = 1; $i <= $total_pages; $i++) {
        $active_class = $i === $page_num ? 'text-blue-600 bg-blue-50 border-blue-300' : 'text-gray-500 bg-white border-gray-300';
        $pagination .= '<li><a href="?page_num=' . $i . '&search=' . urlencode($searchTerm) . '&filter_tahun=' . $filter_tahun . '" class="px-3 py-2 leading-tight ' . $active_class . ' hover:bg-gray-100 hover:text-gray-700">' . $i . '</a></li>';
    }
    $pagination .= '</ul></nav>';
}

header('Content-Type: application/json');
echo json_encode(['html' => $html, 'pagination' => $pagination]);
