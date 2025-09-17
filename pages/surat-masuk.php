<?php
// pages/surat-masuk.php

require_once 'helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_surat_masuk'])) {
    $agenda_urut = $_POST['agenda_urut'];
    // Ambil tahun dari dropdown
    $tahun = $_POST['tahun_penomoran'];
    
    // Cek duplikasi nomor agenda berdasarkan tahun
    $stmt_check = $pdo->prepare("SELECT COUNT(id) FROM surat_masuk WHERE agenda_urut = ? AND YEAR(tanggal_diterima) = ?");
    $stmt_check->execute([$agenda_urut, $tahun]);
    $is_exists = $stmt_check->fetchColumn() > 0;

    if ($is_exists) {
        $_SESSION['error_message'] = "Nomor Urut Agenda '{$agenda_urut}' untuk tahun {$tahun} sudah terdaftar.";
    } else {
        $fileLampiran = handle_file_upload('file_lampiran', 'uploads', 'surat_masuk'); 
        
        if (!isset($_SESSION['error_message'])) {
            $agenda_klas = $_POST['agenda_klasifikasi'];
            $nomor_surat_lengkap = $_POST['nomor_surat_lengkap'];
            $asal_surat = $_POST['asal_surat'];
            $sifat_surat = $_POST['sifat_surat'] ?? 'Biasa'; 
            $perihal = $_POST['perihal'];
            $keterangan = $_POST['keterangan'];
            $tgl_surat = $_POST['tanggal_surat'];
            $tgl_diterima = $_POST['tanggal_diterima'];
            
            // Gunakan tahun dari dropdown untuk nomor agenda
            $nomor_agenda_lengkap = sprintf("%s/%s/436.5/%s", $agenda_klas, $agenda_urut, $tahun);

            $data_baru = [
                'agenda_klasifikasi' => $agenda_klas, 'agenda_urut' => $agenda_urut, 'nomor_agenda_lengkap' => $nomor_agenda_lengkap,
                'nomor_surat_lengkap' => $nomor_surat_lengkap, 'tanggal_surat' => $tgl_surat, 'tanggal_diterima' => $tgl_diterima,
                'asal_surat' => $asal_surat, 'sifat_surat' => $sifat_surat, 'perihal' => $perihal,
                'keterangan' => $keterangan, 'file_lampiran' => $fileLampiran
            ];
    
            $stmt = $pdo->prepare(
                "INSERT INTO surat_masuk (agenda_klasifikasi, agenda_urut, nomor_agenda_lengkap, nomor_surat_lengkap, tanggal_surat, tanggal_diterima, asal_surat, sifat_surat, perihal, keterangan, file_lampiran) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute(array_values($data_baru));
            
            $_SESSION['success_message'] = "Surat masuk berhasil disimpan.";

            // Catat aktivitas
            log_activity($pdo, "Menambah Surat Masuk '{$nomor_agenda_lengkap}'", ['sesudah' => $data_baru]);
        }
    }
    
    header("Location: /surat-masuk");
    exit;
}

// Logika memuat data awal tidak berubah
$limit = 10;
$stmt_data = $pdo->prepare("SELECT *, DATE_FORMAT(tanggal_diterima, '%d-%m-%Y') as tgl_terima_formatted FROM surat_masuk ORDER BY id DESC LIMIT ?");
$stmt_data->bindValue(1, $limit, PDO::PARAM_INT);
$stmt_data->execute();
$surat_masuk_list = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

$stmt_count = $pdo->query("SELECT COUNT(id) FROM surat_masuk");
$total_records = $stmt_count->fetchColumn();
$total_pages = ceil($total_records / $limit);

// --- LOGIKA UNTUK DROPDOWN TAHUN DINAMIS ---
// 1. Ambil tahun-tahun yang sudah ada dari database
$stmt_years = $pdo->query("SELECT DISTINCT YEAR(tanggal_diterima) as year FROM surat_masuk WHERE YEAR(tanggal_diterima) IS NOT NULL ORDER BY year ASC");
$db_years = $stmt_years->fetchAll(PDO::FETCH_COLUMN);

// 2. Dapatkan tahun saat ini dan tahun depan
$current_year = date('Y');

// 3. Gabungkan semua tahun, buat unik, dan urutkan dari terbaru ke terlama
$all_years = array_unique(array_merge($db_years, [$current_year, $current_year + 1]));
rsort($all_years); // Mengurutkan dari besar ke kecil (descending)

$pageTitle = 'Surat Masuk';
require_once 'templates/header.php';
?>

<div class="bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-xl p-6 animate-fade-in border border-blue-100">
    <div class="flex justify-between items-center mb-6 border-b border-blue-200 pb-3">
        <h3 class="text-2xl font-bold text-gray-800 flex items-center">
            <span class="bg-gradient-to-r from-primary to-secondary text-transparent bg-clip-text">Form Pencatatan Surat Masuk</span>
            <i class="fas fa-envelope ml-3 text-primary"></i>
        </h3>
        <button id="toggle-form-masuk-btn" class="text-primary hover:text-secondary text-xl p-2">
            <i class="fas fa-chevron-up"></i>
        </button>
    </div>
    
    <form id="form-masuk-body" method="POST" action="/surat-masuk" enctype="multipart/form-data" class="space-y-6 transition-all duration-500">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
            <div>
                <!-- MODIFIKASI: Mengubah label dan struktur input nomor -->
                <label class="block text-sm font-medium text-gray-700 mb-2">Klasifikasi / No. Urut Agenda / Tahun</label>
                <div class="flex items-center space-x-2">
                    <input type="text" id="agenda_klasifikasi" name="agenda_klasifikasi" class="flex-1 px-4 py-3 rounded-xl border border-gray-300" placeholder="Klasifikasi">
                    <span class="text-gray-500 pt-2">/</span>
                    <input type="text" id="agenda_urut" name="agenda_urut" class="w-24 px-4 py-3 rounded-xl border border-gray-300 text-center" placeholder="No. Urut">
                    <span class="text-gray-500 pt-2">/</span>
                    <!-- Dropdown Tahun -->
                    <select name="tahun_penomoran" class="w-28 px-4 py-3 rounded-xl border border-gray-300 bg-white" required>
                        <?php foreach ($all_years as $year): ?>
                            <option value="<?php echo $year; ?>" <?php echo ($year == $current_year) ? 'selected' : ''; ?>>
                                <?php echo $year; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" id="checkAgendaBtn" class="px-4 py-3 bg-indigo-100 text-indigo-600 rounded-xl hover:bg-indigo-200" title="Cek ketersediaan No. Urut Agenda">
                        <i class="fas fa-check"></i>
                    </button>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Surat</label>
                <input type="text" name="nomor_surat_lengkap" class="w-full px-4 py-3 rounded-xl border border-gray-300" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Asal Surat</label>
                <input type="text" name="asal_surat" class="w-full px-4 py-3 rounded-xl border border-gray-300" required />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sifat Surat</label>
                <div class="flex items-center space-x-6 pt-2">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="radio" name="sifat_surat" value="Biasa" class="form-radio h-4 w-4 text-primary" checked>
                        <span class="text-gray-700">Biasa</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="radio" name="sifat_surat" value="Penting" class="form-radio h-4 w-4 text-primary">
                        <span class="text-gray-700">Penting</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="radio" name="sifat_surat" value="Amat Segera" class="form-radio h-4 w-4 text-primary">
                        <span class="text-gray-700">Amat Segera</span>
                    </label>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Surat</label>
                <input type="date" name="tanggal_surat" class="w-full px-4 py-3 rounded-xl border border-gray-300" value="<?php echo date('Y-m-d'); ?>" required />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Diterima</label>
                <input type="date" name="tanggal_diterima" class="w-full px-4 py-3 rounded-xl border border-gray-300" value="<?php echo date('Y-m-d'); ?>" required />
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Perihal</label>
                <textarea name="perihal" class="w-full px-4 py-3 rounded-xl border border-gray-300 h-24" required></textarea>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">File Lampiran <span class="text-gray-400 font-normal">(PDF/JPG, maks 5MB)</span></label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed border-gray-300 rounded-xl hover:border-primary cursor-pointer relative group">
                    <input id="file-upload-masuk" name="file_lampiran" type="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    <div class="space-y-1 text-center">
                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 group-hover:text-primary"></i>
                        <div class="flex text-sm text-gray-600">
                            <span class="relative bg-white rounded-md font-medium text-primary hover:text-secondary">
                                Unggah file
                            </span>
                            <p class="pl-1">atau tarik dan lepas</p>
                        </div>
                        <p class="text-xs text-gray-500" id="file-name-masuk">Belum ada file dipilih</p>
                    </div>
                </div>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                <textarea name="keterangan" class="w-full px-4 py-3 rounded-xl border border-gray-300 h-24"></textarea>
            </div>
        </div>
        <div class="mt-8 flex justify-end">
            <button type="submit" name="simpan_surat_masuk" class="px-6 py-3 bg-gradient-to-r from-primary to-secondary text-white rounded-xl shadow-md hover:shadow-lg">
                <i class="fas fa-save mr-2"></i> Simpan Surat
            </button>
        </div>
    </form>
</div>

<!-- Bagian tabel daftar surat (tidak ada perubahan) -->
<div id="list-masuk-container" class="mt-8 bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-xl p-6 animate-fade-in border border-blue-100">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <h3 class="text-xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-list-alt text-primary mr-2"></i>
            <span class="bg-gradient-to-r from-primary to-secondary text-transparent bg-clip-text">Daftar Surat Masuk</span>
        </h3>
        <form id="searchFormMasuk" class="w-full md:w-auto flex items-center gap-4">
            <!-- Filter Tahun -->
            <select id="filterTahunMasuk" name="filter_tahun" class="w-44 px-4 py-3 rounded-xl border border-gray-300 bg-white focus:ring-2 focus:ring-indigo-300 focus:border-indigo-500 transition duration-200">
                <option value="">Semua Tahun</option>
                <?php foreach ($all_years as $year): ?>
                    <option value="<?php echo $year; ?>">
                        <?php echo $year; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <!-- Kolom Pencarian -->
            <div class="relative w-full md:w-80">
                <input type="text" id="searchInputMasuk" name="search" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl" placeholder="Cari perihal, asal surat...">
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
                    <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">No. Agenda</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">Asal Surat</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">Perihal</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">Tgl Diterima</th>
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody id="tableBodyMasuk" class="bg-white divide-y divide-gray-200">
                <?php foreach ($surat_masuk_list as $surat): ?>
                    <tr class="hover:bg-blue-50 transition-colors duration-200">
                        <td class="px-6 py-4 font-semibold">
                            <a href="#" class="text-primary hover:underline detail-link" data-id="<?php echo $surat['id']; ?>">
                                <?php echo htmlspecialchars($surat['nomor_agenda_lengkap']); ?>
                            </a>
                        </td>
                        <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($surat['asal_surat']); ?></td>
                        <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($surat['perihal']); ?></td>
                        <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($surat['tgl_terima_formatted']); ?></td>
                        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <a href="/edit-surat-masuk?id=<?php echo $surat['id']; ?>" class="text-blue-500 hover:text-blue-700" title="Edit"><i class="fas fa-edit"></i></a>
                                    <button onclick="confirmDelete('masuk', <?php echo $surat['id']; ?>)" class="text-red-500 hover:text-red-700" title="Hapus"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div id="paginationContainerMasuk" class="mt-6">
        <?php
        if ($total_pages > 1) {
            echo '<div class="flex items-center justify-between">';
            echo '<div class="text-sm text-gray-600">Halaman 1 dari ' . $total_pages . '</div>';
            echo '<div><button onclick="fetchDataMasuk(2)" class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-primary hover:bg-gray-50">Selanjutnya <i class="fas fa-arrow-right ml-1"></i></button></div>';
        }
        ?>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
