<?php
// pages/log-user.php

// Keamanan: Pastikan hanya superadmin yang bisa mengakses
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'superadmin') {
    $_SESSION['error_message'] = "Anda tidak memiliki izin untuk mengakses halaman ini.";
    header('Location: /dashboard');
    exit;
}

// Logika untuk memuat data awal
$limit = 15; // Tampilkan lebih banyak log per halaman
$stmt_data = $pdo->prepare(
    "SELECT l.id, u.nama as user_nama, l.kegiatan, l.detail,
            DATE_FORMAT(l.waktu, '%d-%m-%Y') as tanggal,
            DATE_FORMAT(l.waktu, '%H:%i:%s') as jam
     FROM log_user l
     JOIN users u ON l.user_id = u.id
     ORDER BY l.id DESC LIMIT ?"
);
$stmt_data->bindValue(1, $limit, PDO::PARAM_INT);
$stmt_data->execute();
$logs = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

$stmt_count = $pdo->query("SELECT COUNT(id) FROM log_user");
$total_records = $stmt_count->fetchColumn();
$total_pages = ceil($total_records / $limit);

$pageTitle = 'Log Aktivitas User';
require_once 'templates/header.php';
?>

<style>
    /* Styling untuk tampilan mobile */
    @media (max-width: 767px) {
        .responsive-table thead {
            display: none; /* Sembunyikan header tabel di mobile */
        }
        .responsive-table tbody {
            display: block;
            width: 100%;
        }
        .responsive-table tr {
            display: block;
            margin-bottom: 1rem;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb; /* border-gray-200 */
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            background-color: #ffffff;
            padding: 1rem;
        }
        .responsive-table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0.5rem;
            border-bottom: 1px solid #f3f4f6; /* border-gray-100 */
            text-align: right; /* Data di sebelah kanan */
        }
        .responsive-table td:last-child {
            border-bottom: none;
        }
        .responsive-table td::before {
            content: attr(data-label);
            font-weight: 600;
            text-align: left; /* Label di sebelah kiri */
            color: #4b5563; /* text-gray-600 */
        }
        .responsive-table td.text-center {
            justify-content: flex-end; /* Pastikan tombol tetap di kanan */
        }
        .responsive-table td.text-center::before {
            flex-grow: 1; /* Dorong tombol ke kanan */
        }
    }
</style>

<div class="bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-xl p-6 animate-fade-in border border-blue-100">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <h3 class="text-2xl font-bold text-gray-800 flex items-center">
            <span class="bg-gradient-to-r from-primary to-secondary text-transparent bg-clip-text">Log Aktivitas Pengguna</span>
            <i class="fas fa-history ml-3 text-primary"></i>
        </h3>
        <form id="searchFormLog" class="w-full md:w-auto flex flex-col md:flex-row items-center gap-4">
            <div class="flex items-center gap-2">
                <input type="date" id="startDateLog" name="start_date" class="px-4 py-3 border border-gray-300 rounded-xl text-sm" title="Tanggal Mulai">
                <span class="text-gray-500">-</span>
                <input type="date" id="endDateLog" name="end_date" class="px-4 py-3 border border-gray-300 rounded-xl text-sm" title="Tanggal Selesai">
            </div>
            <div class="relative w-full md:w-auto">
                <input type="text" id="searchInputLog" name="search" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl" placeholder="Cari kegiatan atau user...">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <i class="fas fa-search"></i>
                </div>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
        <table class="min-w-full responsive-table">
            <thead class="bg-gradient-to-r from-primary to-secondary">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">No</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">User</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">Kegiatan</th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">Detail</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">Jam</th>
                </tr>
            </thead>
            <tbody id="tableBodyLog" class="bg-white md:divide-y md:divide-gray-200">
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-10 text-gray-500">Belum ada aktivitas yang tercatat.</td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($logs as $log): ?>
                        <tr class="md:hover:bg-blue-50 transition-colors duration-200">
                            <td data-label="No" class="px-6 py-4 font-medium text-gray-700"><?php echo $no++; ?></td>
                            <td data-label="User" class="px-6 py-4 font-semibold text-gray-800"><?php echo htmlspecialchars($log['user_nama']); ?></td>
                            <td data-label="Kegiatan" class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($log['kegiatan']); ?></td>
                            <td data-label="Detail" class="px-6 py-4 text-center">
                                <?php if ($log['detail']): ?>
                                    <button class="detail-log-btn text-primary hover:underline text-sm" data-detail='<?php echo htmlspecialchars($log['detail']); ?>'>Lihat</button>
                                <?php endif; ?>
                            </td>
                            <td data-label="Tanggal" class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($log['tanggal']); ?></td>
                            <td data-label="Jam" class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($log['jam']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div id="paginationContainerLog" class="mt-6">
        <?php
        if ($total_pages > 1) {
            echo '<div class="flex items-center justify-between">';
            echo '<div class="text-sm text-gray-600">Halaman 1 dari ' . $total_pages . '</div>';
            echo '<div><button onclick="document.getElementById(\'searchFormLog\').fetchData(2)" class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-primary hover:bg-gray-50">Selanjutnya <i class="fas fa-arrow-right ml-1"></i></button></div>';
            echo '</div>';
        }
        ?>
    </div>
</div>

<script type="module">
    import { setupPageFunctionality } from '/assets/js/modules/page.js';
    import { updateTableLog } from '/assets/js/modules/ui.js';

    setupPageFunctionality({
        searchFormId: 'searchFormLog', // ID form pencarian
        searchInputId: 'searchInputLog', // ID input pencarian
        tableBodyId: 'tableBodyLog', // ID <tbody> tabel
        paginationContainerId: 'paginationContainerLog', // ID kontainer pagination
        startDateId: 'startDateLog', // ID input tanggal mulai
        endDateId: 'endDateLog', // ID input tanggal selesai
        searchUrl: '/ajax-search-log-user',
        updateTable: updateTableLog,
    });
</script>
<?php require_once 'templates/footer.php'; ?>
