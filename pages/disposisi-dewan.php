<?php
// pages/disposisi-dewan.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

// Cek jika pengguna tidak login atau bukan superadmin/admin, redirect ke halaman login
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['superadmin', 'admin'])) {
    $_SESSION['error_message'] = "Anda tidak memiliki izin untuk mengakses halaman Disposisi Dewan.";
    header("Location: /dashboard");
    exit;
}

// Logika untuk menyimpan data disposisi baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_disposisi'])) {
    $surat_masuk_id = $_POST['surat_masuk_id'];
    $nama_pegawai = $_POST['nama_pegawai'];
    $catatan = $_POST['catatan_disposisi'];

    // Cek apakah surat sudah pernah didisposisikan
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM disposisi_dewan WHERE surat_masuk_id = ?");
    $checkStmt->execute([$surat_masuk_id]);
    if ($checkStmt->fetchColumn() > 0) {
        $_SESSION['error_message'] = "Surat ini sudah pernah didisposisikan sebelumnya.";
    } else {
        $fileLampiran = handle_file_upload('file_lampiran', 'uploads-dewan', 'disposisi_dewan');
        
        if (!isset($_SESSION['error_message'])) {
            $data_baru = [
                'surat_masuk_id' => $surat_masuk_id,
                'nama_pegawai' => $nama_pegawai,
                'catatan_disposisi' => $catatan,
                'file_lampiran' => $fileLampiran
            ];

            $stmt = $pdo->prepare(
                "INSERT INTO disposisi_dewan (surat_masuk_id, nama_pegawai, catatan_disposisi, file_lampiran) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute(array_values($data_baru));

            // Ambil nomor agenda untuk log
            $stmt_get_no = $pdo->prepare("SELECT nomor_agenda_lengkap FROM surat_masuk_dewan WHERE id = ?");
            $stmt_get_no->execute([$surat_masuk_id]);
            $no_agenda = $stmt_get_no->fetchColumn();
            log_activity($pdo, "Membuat disposisi untuk surat dewan '{$no_agenda}'", ['sesudah' => $data_baru]);
            
            $_SESSION['success_message'] = "Disposisi dewan berhasil disimpan.";
        }
    }

    header("Location: /disposisi-dewan");
    exit;
}

// Ambil daftar surat masuk dewan yang BELUM didisposisikan untuk dropdown
$surat_untuk_disposisi = $pdo->query(
    "SELECT sm.id, sm.nomor_agenda_lengkap, sm.perihal 
     FROM surat_masuk_dewan sm
     LEFT JOIN disposisi_dewan dd ON sm.id = dd.surat_masuk_id
     WHERE dd.id IS NULL
     ORDER BY sm.id DESC"
)->fetchAll(PDO::FETCH_ASSOC);

// Logika untuk menampilkan data awal di tabel
$limit = 10;
$stmt_data = $pdo->prepare(
    "SELECT dd.id, dd.nama_pegawai, dd.file_lampiran,
            DATE_FORMAT(dd.tanggal_disposisi, '%d-%m-%Y %H:%i') as tgl_disposisi_formatted, 
            smd.nomor_agenda_lengkap, smd.perihal, smd.file_lampiran as surat_masuk_file
     FROM disposisi_dewan dd
     JOIN surat_masuk_dewan smd ON dd.surat_masuk_id = smd.id
     ORDER BY dd.tanggal_disposisi DESC, dd.id DESC LIMIT ?"
);
$stmt_data->bindValue(1, $limit, PDO::PARAM_INT);
$stmt_data->execute();
$disposisi_list = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

$stmt_count = $pdo->query("SELECT COUNT(id) FROM disposisi_dewan");
$total_records = $stmt_count->fetchColumn();
$total_pages = ceil($total_records / $limit);

// --- LOGIKA UNTUK DROPDOWN TAHUN DINAMIS (FILTER) ---
$stmt_years = $pdo->query("SELECT DISTINCT YEAR(tanggal_disposisi) as year FROM disposisi_dewan WHERE YEAR(tanggal_disposisi) IS NOT NULL ORDER BY year ASC");
$db_years = $stmt_years->fetchAll(PDO::FETCH_COLUMN);
$current_year = date('Y');
$all_years = array_unique(array_merge($db_years, [$current_year, $current_year + 1]));
rsort($all_years);

$pageTitle = 'Disposisi Surat Dewan';
require_once __DIR__ . '/../templates/header.php';
?>

<div id="disposisi-dewan-page" class="pb-10 md:pb-6">
    <div id="form-disposisi-container" class="bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-xl p-4 md:p-6 animate-fade-in border border-blue-100 mx-2 md:mx-0 mt-4 md:mt-0">
        <div class="flex justify-between items-center mb-4 md:mb-6 border-b border-blue-200 pb-2 md:pb-3">
            <h3 class="text-xl md:text-2xl font-bold text-gray-800 flex items-center">
                <span class="bg-gradient-to-r from-primary to-secondary text-transparent bg-clip-text">Form Disposisi Surat (Dewan)</span>
                <i class="fas fa-share-square ml-2 md:ml-3 text-primary"></i>
            </h3>
            <button id="toggle-form-disposisi-btn" class="text-primary hover:text-secondary text-lg md:text-xl p-1 md:p-2">
                <i class="fas fa-chevron-up"></i>
            </button>
        </div>
        
        <form id="form-disposisi-body" method="POST" action="/disposisi-dewan" class="space-y-4 md:space-y-6 transition-all duration-500" enctype="multipart/form-data">
            <input type="hidden" name="simpan_disposisi" value="1">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                <div>
                    <label for="surat_masuk_id" class="block text-sm font-medium text-gray-700 mb-2">Nomor Agenda Surat</label>
                    <select id="surat_masuk_id" name="surat_masuk_id" class="w-full px-3 md:px-4 py-2 md:py-3 rounded-xl border border-gray-300" required>
                        <option value="" disabled selected>-- Pilih Nomor Agenda --</option>
                        <?php foreach ($surat_untuk_disposisi as $surat): ?>
                            <option value="<?= htmlspecialchars($surat['id']) ?>">
                                <?php
                                $perihal = $surat['perihal'];
                                $perihal_pendek = (strlen($perihal) > 50) ? substr($perihal, 0, 50) . '...' : $perihal;
                                ?>
                                <?= htmlspecialchars($surat['nomor_agenda_lengkap']) ?> - <?= htmlspecialchars($perihal_pendek) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="nama_pegawai" class="block text-sm font-medium text-gray-700 mb-2">Diteruskan Kepada</label>
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
            <span class="bg-gradient-to-r from-primary to-secondary text-transparent bg-clip-text">Daftar Disposisi Dewan</span>
        </h3>
        <form id="searchFormDisposisiDewan" class="w-full flex flex-col md:flex-row items-stretch md:items-center gap-3">
             <!-- Filter Tahun -->
            <select id="filterTahunDisposisi" name="filter_tahun" class="w-full md:w-44 px-3 md:px-4 py-2 md:py-3 rounded-xl border border-gray-300 bg-white focus:ring-2 focus:ring-indigo-300 focus:border-indigo-500 transition duration-200">
                <option value="">Semua Tahun</option>
                <?php foreach ($all_years as $year): ?>
                    <option value="<?= $year; ?>">
                        <?= $year; ?>
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
                    <th class="px-4 md:px-6 py-2 md:py-4 text-left text-xs font-semibold text-white uppercase tracking-wider hidden md:table-cell sortable-col cursor-pointer" data-sort-col="nama_pegawai" data-sort-order="asc">Kepada Tertuju <span class="sort-icon"></span></th>
                    <th class="px-4 md:px-6 py-2 md:py-4 text-left text-xs font-semibold text-white uppercase tracking-wider hidden md:table-cell sortable-col cursor-pointer" data-sort-col="tanggal_disposisi" data-sort-order="desc">Tanggal Disposisi <span class="sort-icon"><i class="fas fa-sort-down"></i></span></th>
                    <th class="px-4 md:px-6 py-2 md:py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody id="tableBodyDisposisi" class="bg-white divide-y divide-gray-200">
                <?php if (empty($disposisi_list)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-10 text-gray-500">
                            <i class="fas fa-folder-open fa-3x mb-3"></i>
                            <p>Tidak ada data untuk ditampilkan.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($disposisi_list as $disposisi): ?>
                        <tr class="hover:bg-blue-50 transition-colors duration-200">
                            <td class="px-4 md:px-6 py-3 md:py-4 font-medium">
                                <?php if (!empty($disposisi['file_lampiran'])): ?>
                                    <a href="#" class="text-primary hover:underline pdf-modal-trigger" data-pdf-src="/uploads-dewan/<?= htmlspecialchars($disposisi['file_lampiran']); ?>" data-agenda-no="<?= htmlspecialchars($disposisi['nomor_agenda_lengkap']); ?>">
                                        <?= htmlspecialchars($disposisi['nomor_agenda_lengkap']); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-800"><?= htmlspecialchars($disposisi['nomor_agenda_lengkap']); ?></span>
                                <?php endif; ?>
                                <div class="md:hidden text-sm text-gray-600 mt-1">
                                    <div class="truncate">Perihal: <?= htmlspecialchars($disposisi['perihal']); ?></div>
                                    <div>Tujuan: <?= htmlspecialchars($disposisi['nama_pegawai']); ?></div>
                                    <div>Tgl: <?= htmlspecialchars($disposisi['tgl_disposisi_formatted']); ?></div>
                                </div>
                            </td>
                            <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600 hidden md:table-cell"><?= htmlspecialchars($disposisi['perihal']); ?></td>
                            <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600 hidden md:table-cell"><?= htmlspecialchars($disposisi['nama_pegawai']); ?></td>
                            <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600 hidden md:table-cell text-sm"><?= htmlspecialchars($disposisi['tgl_disposisi_formatted']); ?></td>
                            
                            <td class="px-4 md:px-6 py-3 md:py-4 text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="/edit-disposisi-dewan?id=<?= $disposisi['id']; ?>" class="text-blue-500 hover:text-blue-700" title="Edit Disposisi">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if (in_array($_SESSION['user_role'], ['superadmin', 'admin'])): ?>
                                        <button onclick="confirmDelete('disposisi-dewan', <?= $disposisi['id']; ?>)" class="text-red-500 hover:text-red-700" title="Batalkan Disposisi">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    <?php endif; ?>
                                    
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div id="paginationContainerDisposisi" class="mt-4 md:mt-6">
        <!-- Pagination akan dimuat oleh JavaScript -->
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const formContainer = document.getElementById('form-disposisi-container');
    const toggleBtn = document.getElementById('toggle-form-disposisi-btn');
    const formBody = document.getElementById('form-disposisi-body');
    const icon = toggleBtn.querySelector('i');

    // Cek status minimize dari localStorage
    const isMinimized = localStorage.getItem('disposisiDewanFormMinimized') === 'true';

    function applyMinimizeState(minimized) {
        if (minimized) {
            formBody.classList.add('hidden');
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
        } else {
            formBody.classList.remove('hidden');
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-up');
        }
    }

    // Terapkan state saat halaman dimuat
    applyMinimizeState(isMinimized);

    // Event listener untuk tombol toggle
    toggleBtn.addEventListener('click', () => {
        const currentlyHidden = formBody.classList.toggle('hidden');
        localStorage.setItem('disposisiDewanFormMinimized', currentlyHidden);
        applyMinimizeState(currentlyHidden); // Perbarui ikon
    });

    // ... sisa skrip AJAX ...
    const searchInput = document.getElementById('searchInputDisposisi');
    const filterTahun = document.getElementById('filterTahunDisposisi');
    const tableBody = document.getElementById('tableBodyDisposisi');
    const paginationContainer = document.getElementById('paginationContainerDisposisi');
    const tableHeader = document.querySelector('#list-disposisi-container thead');
    let searchTimeout;

    let currentSort = {
        col: 'tanggal_disposisi',
        order: 'desc'
    };

    function fetchDisposisi(page = 1) {
        const searchTerm = searchInput.value;
        const year = filterTahun.value;
        const { col, order } = currentSort;
        
        const url = `/ajax-search-disposisi-dewan?search=${encodeURIComponent(searchTerm)}&page_num=${page}&filter_tahun=${year}&sort_col=${col}&sort_order=${order}`;

        tableBody.innerHTML = '<tr><td colspan="5" class="text-center py-10"><i class="fas fa-spinner fa-spin text-primary text-3xl"></i></td></tr>';

        fetch(url)
            .then(response => response.json())
            .then(data => {
                tableBody.innerHTML = data.html;
                paginationContainer.innerHTML = data.pagination;
                updateSortIcons();
            })
            .catch(error => {
                console.error('Error fetching disposisi:', error);
                tableBody.innerHTML = '<tr><td colspan="5" class="text-center py-10 text-red-500">Gagal memuat data. Silakan coba lagi.</td></tr>';
            });
    }

    function updateSortIcons() {
        tableHeader.querySelectorAll('.sortable-col').forEach(th => {
            const sortCol = th.dataset.sortCol;
            const sortIcon = th.querySelector('.sort-icon');
            
            if (sortCol === currentSort.col) {
                sortIcon.innerHTML = currentSort.order === 'asc' ? '<i class="fas fa-sort-up"></i>' : '<i class="fas fa-sort-down"></i>';
            } else {
                sortIcon.innerHTML = '';
            }
        });
    }

    tableHeader.addEventListener('click', function(event) {
        const target = event.target.closest('.sortable-col');
        if (target) {
            const sortCol = target.dataset.sortCol;
            if (currentSort.col === sortCol) {
                currentSort.order = currentSort.order === 'asc' ? 'desc' : 'asc';
            } else {
                currentSort.col = sortCol;
                currentSort.order = 'asc';
            }
            fetchDisposisi(1);
        }
    });

    searchInput.addEventListener('keyup', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => fetchDisposisi(1), 300);
    });

    filterTahun.addEventListener('change', () => {
        fetchDisposisi(1);
    });

    document.getElementById('list-disposisi-container').addEventListener('click', function(event) {
        const target = event.target.closest('#paginationContainerDisposisi a');
        if (target) {
            event.preventDefault();
            const url = new URL(target.href);
            const page = url.searchParams.get('page_num') || 1;
            fetchDisposisi(page);
        }
    });

    // Initial UI setup
    updateSortIcons();
});
</script>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>
