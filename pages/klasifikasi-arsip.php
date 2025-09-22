<?php
// pages/klasifikasi-arsip.php

require_once 'helpers.php';

// Keamanan: Pastikan hanya superadmin yang bisa mengakses
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'superadmin') {
    $_SESSION['error_message'] = "Anda tidak memiliki izin untuk mengakses halaman ini.";
    header('Location: /dashboard');
    exit;
}

// Logika untuk menyimpan data baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_klasifikasi'])) {
    $kode = $_POST['kode'];
    $deskripsi = $_POST['deskripsi'];

    if (empty($kode) || empty($deskripsi)) {
        $_SESSION['error_message'] = "Kode dan Deskripsi tidak boleh kosong.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO klasifikasi_arsip (kode, deskripsi) VALUES (?, ?)");
        $stmt->execute([$kode, $deskripsi]);
        $_SESSION['success_message'] = "Data klasifikasi berhasil ditambahkan.";
    }
    header("Location: /klasifikasi-arsip");
    exit;
}

// --- Logika Pencarian dan Pagination (Final Corrected Version) ---
$limit = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;
$search_term = $_GET['search'] ?? '';

// 1. Build base queries
$sql_count = "SELECT COUNT(id) FROM klasifikasi_arsip";
$sql_data = "SELECT * FROM klasifikasi_arsip";

// 2. Build parameters and WHERE clause if a search is performed
$params = [];
if (!empty($search_term)) {
    $sql_count .= " WHERE kode LIKE ? OR deskripsi LIKE ?";
    $sql_data .= " WHERE kode LIKE ? OR deskripsi LIKE ?";
    $like_term = '%' . $search_term . '%';
    $params[] = $like_term;
    $params[] = $like_term;
}

// 3. Execute the COUNT query
$stmt_count = $pdo->prepare($sql_count);
$stmt_count->execute($params);
$total_records = $stmt_count->fetchColumn();
$total_pages = ceil($total_records / $limit);

// 4. Build the final DATA query with ordering and limits
$sql_data .= " ORDER BY kode ASC LIMIT ? OFFSET ?";

// 5. Add limit and offset to the parameters array for the data query
$params[] = $limit;
$params[] = $offset;

// 6. Execute the DATA query
$stmt_data = $pdo->prepare($sql_data);
$stmt_data->execute($params);
$klasifikasi_list = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Kelola Klasifikasi Arsip';
require_once 'templates/header.php';
?>

<div class="pb-10 md:pb-6">
    <!-- Form Tambah Data -->
    <div id="form-container" class="bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-xl p-4 md:p-6 animate-fade-in border border-blue-100 mx-2 md:mx-0 mt-4 md:mt-0">
        <div class="flex justify-between items-center mb-4 md:mb-6 border-b border-blue-200 pb-3">
            <h3 class="text-xl md:text-2xl font-bold text-gray-800 flex items-center">
                <span class="bg-gradient-to-r from-primary to-secondary text-transparent bg-clip-text">Form Klasifikasi Arsip</span>
                <i class="fas fa-archive ml-3 text-primary"></i>
            </h3>
            <button id="toggle-form-btn" class="text-primary hover:text-secondary text-lg md:text-xl p-2">
                <i class="fas fa-chevron-up"></i>
            </button>
        </div>
        
        <form id="form-body" method="POST" action="/klasifikasi-arsip" class="space-y-4 transition-all duration-500">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="kode" class="block text-sm font-medium text-gray-700 mb-2">Kode Klasifikasi</label>
                    <input type="text" id="kode" name="kode" class="w-full px-4 py-3 rounded-xl border border-gray-300" placeholder="Contoh: 001.1" required>
                </div>
                <div class="md:col-span-2">
                    <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                    <input type="text" id="deskripsi" name="deskripsi" class="w-full px-4 py-3 rounded-xl border border-gray-300" placeholder="Contoh: Tata Naskah Dinas" required>
                </div>
            </div>
            <div class="mt-6 flex justify-end">
                <button type="submit" name="simpan_klasifikasi" class="px-6 py-3 bg-gradient-to-r from-primary to-secondary text-white rounded-xl shadow-md hover:shadow-lg">
                    <i class="fas fa-save mr-2"></i> Simpan
                </button>
            </div>
        </form>
    </div>

    <!-- Tabel Data -->
    <div class="mt-8 bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-xl p-4 md:p-6 animate-fade-in border border-blue-100 mx-2 md:mx-0">
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <h3 class="text-xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-list-alt text-primary mr-2"></i>
                <span class="bg-gradient-to-r from-primary to-secondary text-transparent bg-clip-text">Daftar Klasifikasi Arsip</span>
            </h3>
            <!-- Form Pencarian -->
            <form method="GET" action="/klasifikasi-arsip" class="w-full md:w-1/3">
                <div class="relative">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search_term); ?>" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-xl" placeholder="Cari kode atau deskripsi...">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
            </form>
        </div>
        <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-primary to-secondary">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">Kode</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">Deskripsi</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($klasifikasi_list)): ?>
                        <tr>
                            <td colspan="3" class="text-center py-10 text-gray-500">
                                <?php if (!empty($search_term)): ?>
                                    Pencarian untuk "<?php echo htmlspecialchars($search_term); ?>" tidak ditemukan.
                                <?php else: ?>
                                    Data tidak ditemukan.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($klasifikasi_list as $klas): ?>
                            <tr class="hover:bg-blue-50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                    <?php echo htmlspecialchars($klas['kode']); ?>
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    <?php echo htmlspecialchars($klas['deskripsi']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-3">
                                        <a href="/edit-klasifikasi-arsip?id=<?php echo $klas['id']; ?>" class="text-blue-500 hover:text-blue-700" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="/hapus-klasifikasi-arsip?id=<?php echo $klas['id']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');" class="text-red-500 hover:text-red-700" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div class="mt-6">
            <?php generate_pagination($total_pages, $page, '/klasifikasi-arsip', ['search' => $search_term]); ?>
        </div>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
