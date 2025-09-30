<?php
// pages/disposisi-sekwan.php

require_once 'helpers.php';

if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['superadmin', 'admin', 'staff surat masuk'])) {
    $_SESSION['error_message'] = "Anda tidak memiliki izin untuk mengakses halaman Disposisi.";
    header('Location: /dashboard');
    exit;
}

// Logika untuk menyimpan data disposisi baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_disposisi'])) {
    $surat_masuk_id = $_POST['surat_masuk_id'];
    $nama_pegawai = $_POST['nama_pegawai'];
    $catatan = $_POST['catatan_disposisi'];

    $fileLampiran = handle_file_upload('file_lampiran', 'uploads', 'disposisi_sekwan');
    
    if (!isset($_SESSION['error_message'])) {
        $data_baru = [
            'surat_masuk_id' => $surat_masuk_id,
            'nama_pegawai' => $nama_pegawai,
            'catatan_disposisi' => $catatan,
            'file_lampiran' => $fileLampiran
        ];

        $stmt = $pdo->prepare(
            "INSERT INTO disposisi_sekwan (surat_masuk_id, nama_pegawai, catatan_disposisi, file_lampiran) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute(array_values($data_baru));

        // Ambil nomor agenda untuk log
        $stmt_get_no = $pdo->prepare("SELECT nomor_agenda_lengkap FROM surat_masuk WHERE id = ?");
        $stmt_get_no->execute([$surat_masuk_id]);
        $no_agenda = $stmt_get_no->fetchColumn();
        log_activity($pdo, "Membuat disposisi untuk surat '{$no_agenda}'", ['sesudah' => $data_baru]);
        
        $_SESSION['success_message'] = "Disposisi berhasil disimpan.";
    }

    header("Location: /disposisi-sekwan");
    exit;
}

// Ambil daftar surat masuk yang BELUM didisposisikan untuk dropdown
$surat_untuk_disposisi = $pdo->query(
    "SELECT sm.id, sm.nomor_agenda_lengkap, sm.perihal 
     FROM surat_masuk sm
     LEFT JOIN disposisi_sekwan ds ON sm.id = ds.surat_masuk_id
     WHERE ds.id IS NULL
     ORDER BY sm.id DESC"
)->fetchAll(PDO::FETCH_ASSOC);

// Logika untuk menampilkan data awal di tabel
$limit = 10;
$stmt_data = $pdo->prepare(
    "SELECT ds.id, ds.nama_pegawai, ds.file_lampiran,
            DATE_FORMAT(ds.tanggal_disposisi, '%d-%m-%Y %H:%i') as tgl_disposisi_formatted, 
            sm.nomor_agenda_lengkap, sm.perihal, sm.file_lampiran as surat_masuk_file
     FROM disposisi_sekwan ds
     JOIN surat_masuk sm ON ds.surat_masuk_id = sm.id
     ORDER BY ds.tanggal_disposisi DESC, ds.id DESC LIMIT ?"
);
$stmt_data->bindValue(1, $limit, PDO::PARAM_INT);
$stmt_data->execute();
$disposisi_list = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

$stmt_count = $pdo->query("SELECT COUNT(id) FROM disposisi_sekwan");
$total_records = $stmt_count->fetchColumn();
$total_pages = ceil($total_records / $limit);

// --- LOGIKA UNTUK DROPDOWN TAHUN DINAMIS (FILTER) ---
// 1. Ambil tahun-tahun yang sudah ada dari database
$stmt_years = $pdo->query("SELECT DISTINCT YEAR(tanggal_disposisi) as year FROM disposisi_sekwan WHERE YEAR(tanggal_disposisi) IS NOT NULL ORDER BY year ASC");
$db_years = $stmt_years->fetchAll(PDO::FETCH_COLUMN);

// 2. Dapatkan tahun saat ini dan tahun depan
$current_year = date('Y');

// 3. Gabungkan semua tahun, buat unik, dan urutkan dari terbaru ke terlama
$all_years = array_unique(array_merge($db_years, [$current_year, $current_year + 1]));
rsort($all_years); // Mengurutkan dari besar ke kecil (descending)

$pageTitle = 'Disposisi Surat Masuk';
require_once 'templates/header.php';
?>

<div class="pb-10 md:pb-6">
    <div id="form-disposisi-container" class="bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-xl p-4 md:p-6 animate-fade-in border border-blue-100 mx-2 md:mx-0 mt-4 md:mt-0">
        <div class="flex justify-between items-center mb-4 md:mb-6 border-b border-blue-200 pb-2 md:pb-3">
            <h3 class="text-xl md:text-2xl font-bold text-gray-800 flex items-center">
                <span class="bg-gradient-to-r from-primary to-secondary text-transparent bg-clip-text">Form Disposisi Surat (Sekwan)</span>
                <i class="fas fa-share-square ml-2 md:ml-3 text-primary"></i>
            </h3>
            <button id="toggle-form-disposisi-btn" class="text-primary hover:text-secondary text-lg md:text-xl p-1 md:p-2">
                <i class="fas fa-chevron-up"></i>
            </button>
        </div>
        
        <form id="form-disposisi-body" method="POST" action="/disposisi-sekwan" class="space-y-4 md:space-y-6 transition-all duration-500" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                <div>
                    <label for="surat_masuk_id" class="block text-sm font-medium text-gray-700 mb-2">Nomor Agenda Surat</label>
                    <select id="surat_masuk_id" name="surat_masuk_id" class="w-full px-3 md:px-4 py-2 md:py-3 rounded-xl border border-gray-300" required>
                        <option value="" disabled selected>-- Pilih Nomor Agenda --</option>
                        <?php foreach ($surat_untuk_disposisi as $surat): ?>
                            <option value="<?php echo $surat['id']; ?>">
                                <?php
                                $perihal = $surat['perihal'];
                                $perihal_pendek = (strlen($perihal) > 50) ? substr($perihal, 0, 50) . '...' : $perihal;
                                echo htmlspecialchars($surat['nomor_agenda_lengkap']) . ' - ' . htmlspecialchars($perihal_pendek);
                                ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="nama_pegawai" class="block text-sm font-medium text-gray-700 mb-2">Nama Pegawai</label>
                    <input type="text" id="nama_pegawai" name="nama_pegawai" class="w-full px-3 md:px-4 py-2 md:py-3 rounded-xl border border-gray-300" placeholder="Masukkan nama pegawai yang dituju" required>
                </div>

                <div class="md:col-span-2">
                    <label for="catatan_disposisi" class="block text-sm font-medium text-gray-700 mb-2">Isi/Catatan Disposisi</label>
                    <textarea id="catatan_disposisi" name="catatan_disposisi" rows="4" class="w-full px-3 md:px-4 py-2 md:py-3 rounded-xl border border-gray-300" placeholder="Contoh: Mohon segera ditindaklanjuti..."></textarea>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">File Lampiran <span class="text-gray-400 font-normal">(Opsional: PDF/JPG/PNG, maks 5MB)</span></label>
                    <div class="mt-1 flex justify-center px-4 md:px-6 pt-4 md:pt-5 pb-4 md:pb-6 border-2 border-dashed border-gray-300 rounded-xl hover:border-primary cursor-pointer relative group">
                        <input id="file-upload-disposisi" name="file_lampiran" type="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <div class="space-y-1 text-center">
                             <i class="fas fa-cloud-upload-alt text-3xl md:text-4xl text-gray-400 group-hover:text-primary"></i>
                            <div class="flex flex-col md:flex-row text-sm text-gray-600">
                                <span class="relative bg-white rounded-md font-medium text-primary hover:text-secondary">
                                    Unggah file
                                </span>
                                <p class="md:pl-1">atau tarik dan lepas</p>
                            </div>
                            <p class="text-xs text-gray-500" id="file-name-disposisi">Belum ada file dipilih</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-col-reverse md:flex-row justify-end space-y-reverse space-y-4 md:space-y-0 md:space-x-4">
                <button type="reset" class="px-4 md:px-6 py-2 md:py-3 border border-gray-300 rounded-xl hover:bg-gray-50 mt-4 md:mt-0">Reset</button>
                <button type="submit" name="simpan_disposisi" class="px-4 md:px-6 py-2 md:py-3 bg-gradient-to-r from-primary to-secondary text-white rounded-xl shadow-md hover:shadow-lg">
                    <i class="fas fa-save mr-2"></i> Simpan Disposisi
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Daftar Disposisi -->
<div id="list-disposisi-container" class="mt-6 md:mt-8 bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-xl p-4 md:p-6 animate-fade-in border border-blue-100 mx-2 md:mx-0">
    <div class="flex flex-col gap-4 mb-4 md:mb-6">
        <h3 class="text-xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-list-alt text-primary mr-2"></i> 
            <span class="bg-gradient-to-r from-primary to-secondary text-transparent bg-clip-text">Daftar Disposisi</span>
        </h3>
        <form id="searchFormDisposisi" class="w-full flex flex-col md:flex-row items-stretch md:items-center gap-3">
             <!-- Filter Tahun -->
            <select id="filterTahunDisposisi" name="filter_tahun" class="w-full md:w-44 px-3 md:px-4 py-2 md:py-3 rounded-xl border border-gray-300 bg-white focus:ring-2 focus:ring-indigo-300 focus:border-indigo-500 transition duration-200">
                <option value="">Semua Tahun</option>
                <?php foreach ($all_years as $year): ?>
                    <option value="<?php echo $year; ?>">
                        <?php echo $year; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <!-- Kolom Pencarian -->
            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <i class="fas fa-search"></i>
                </div>
                <input type="text" id="searchInputDisposisi" name="search" class="w-full pl-10 pr-4 py-2 md:py-3 border border-gray-300 rounded-xl" placeholder="Cari perihal, no agenda...">
            </div>
        </form>
    </div>
    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-primary to-secondary">
                <tr>
                    <th class="px-4 md:px-6 py-2 md:py-4 text-left text-xs font-semibold text-white uppercase tracking-wider sortable-col cursor-pointer" data-sort-col="nomor_agenda_lengkap" data-sort-order="asc">No. Agenda <span class="sort-icon"></span></th>
                    <th class="px-4 md:px-6 py-2 md:py-4 text-left text-xs font-semibold text-white uppercase tracking-wider hidden md:table-cell sortable-col cursor-pointer" data-sort-col="perihal" data-sort-order="asc">Perihal <span class="sort-icon"></span></th>
                    <th class="px-4 md:px-6 py-2 md:py-4 text-left text-xs font-semibold text-white uppercase tracking-wider hidden md:table-cell sortable-col cursor-pointer" data-sort-col="nama_pegawai" data-sort-order="asc">Pegawai Tertuju <span class="sort-icon"></span></th>
                    <th class="px-4 md:px-6 py-2 md:py-4 text-left text-xs font-semibold text-white uppercase tracking-wider hidden md:table-cell sortable-col cursor-pointer" data-sort-col="tanggal_disposisi" data-sort-order="desc">Tanggal Disposisi <span class="sort-icon"><i class="fas fa-sort-down"></i></span></th>
                    <?php if (in_array($_SESSION['user_role'], ['superadmin', 'admin', 'staff surat masuk'])): ?>
                        <th class="px-4 md:px-6 py-2 md:py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody id="tableBodyDisposisi" class="bg-white divide-y divide-gray-200">
                <?php foreach ($disposisi_list as $disposisi): ?>
                    <tr class="hover:bg-blue-50 transition-colors duration-200">
                        <td class="px-4 md:px-6 py-3 md:py-4 font-medium">
                            <?php if (!empty($disposisi['file_lampiran'])): ?>
                                <a href="#" class="text-primary hover:underline pdf-modal-trigger" data-pdf-src="/uploads/<?php echo htmlspecialchars($disposisi['file_lampiran']); ?>" data-agenda-no="<?php echo htmlspecialchars($disposisi['nomor_agenda_lengkap']); ?>">
                                    <?php echo htmlspecialchars($disposisi['nomor_agenda_lengkap']); ?>
                                </a>
                            <?php else: ?>
                                <span class="text-gray-800"><?php echo htmlspecialchars($disposisi['nomor_agenda_lengkap']); ?></span>
                            <?php endif; ?>
                            <div class="md:hidden text-sm text-gray-600 mt-1">
                                <div class="truncate">Perihal: <?php echo htmlspecialchars($disposisi['perihal']); ?></div>
                                <div>Tujuan: <?php echo htmlspecialchars($disposisi['nama_pegawai']); ?></div>
                                <div>Tgl: <?php echo htmlspecialchars($disposisi['tgl_disposisi_formatted']); ?></div>
                            </div>
                        </td>
                        <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600 hidden md:table-cell"><?php echo htmlspecialchars($disposisi['perihal']); ?></td>
                        <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600 hidden md:table-cell"><?php echo htmlspecialchars($disposisi['nama_pegawai']); ?></td>
                        <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600 hidden md:table-cell text-sm"><?php echo htmlspecialchars($disposisi['tgl_disposisi_formatted']); ?></td>
                        
                        <?php if (in_array($_SESSION['user_role'], ['superadmin', 'admin', 'staff surat masuk'])): ?>
                            <td class="px-4 md:px-6 py-3 md:py-4 text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="/edit-disposisi-sekwan?id=<?php echo $disposisi['id']; ?>" class="text-blue-500 hover:text-blue-700" title="Edit Disposisi">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if (in_array($_SESSION['user_role'], ['superadmin', 'admin'])): ?>
                                        <button onclick="confirmDelete('disposisi-sekwan', <?php echo $disposisi['id']; ?>)" class="text-red-500 hover:text-red-700" title="Batalkan Disposisi">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div id="paginationContainerDisposisi" class="mt-4 md:mt-6">
        <!-- Pagination akan dimuat oleh JavaScript -->
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>
