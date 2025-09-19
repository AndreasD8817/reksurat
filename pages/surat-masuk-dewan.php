<?php
// pages/surat-masuk-dewan.php

require_once 'helpers.php';

// Memastikan pengguna memiliki izin untuk mengakses halaman ini
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'staff surat masuk'])) {
    $_SESSION['error_message'] = "Anda tidak memiliki izin untuk mengakses halaman Surat Masuk Dewan.";
    header('Location: /dashboard');
    exit;
}

// Menangani penambahan data surat baru saat form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_surat_masuk_dewan'])) {
    $agenda_urut = $_POST['agenda_urut'];
    $tahun = $_POST['tahun_penomoran']; // Ambil tahun dari dropdown

    // Cek duplikasi nomor agenda pada tahun yang sama
    $stmt_check = $pdo->prepare("SELECT COUNT(id) FROM surat_masuk_dewan WHERE agenda_urut = ? AND YEAR(tanggal_diterima) = ?");
    $stmt_check->execute([$agenda_urut, $tahun]);
    $is_exists = $stmt_check->fetchColumn() > 0;

    if ($is_exists) {
        $_SESSION['error_message'] = "Nomor Urut Agenda '{$agenda_urut}' untuk tahun {$tahun} sudah terdaftar.";
    } else {
        // Handle upload file lampiran
        $fileLampiran = handle_file_upload('file_lampiran', 'uploads-dewan', 'surat_masuk_dewan');

        if (!isset($_SESSION['error_message'])) {
            $agenda_klas = $_POST['agenda_klasifikasi'];
            $nomor_surat_lengkap = $_POST['nomor_surat_lengkap'];
            $asal_surat = $_POST['asal_surat'];
            $sifat_surat = $_POST['sifat_surat'] ?? 'Biasa';
            $perihal = $_POST['perihal'];
            $diteruskan_kepada = $_POST['diteruskan_kepada'];
            $keterangan = $_POST['keterangan'];
            $tgl_surat = $_POST['tanggal_surat'];
            $tgl_diterima = $_POST['tanggal_diterima'];

            // Membuat nomor agenda lengkap
            $nomor_agenda_lengkap = sprintf("%s/%s/436.5/%s", $agenda_klas, $agenda_urut, $tahun);

            $data_baru = [
                'agenda_klasifikasi' => $agenda_klas, 'agenda_urut' => $agenda_urut, 'nomor_agenda_lengkap' => $nomor_agenda_lengkap,
                'nomor_surat_lengkap' => $nomor_surat_lengkap, 'tanggal_surat' => $tgl_surat, 'tanggal_diterima' => $tgl_diterima,
                'asal_surat' => $asal_surat, 'sifat_surat' => $sifat_surat, 'perihal' => $perihal,
                'diteruskan_kepada' => $diteruskan_kepada, 'keterangan' => $keterangan, 'file_lampiran' => $fileLampiran
            ];

            // Menyimpan data ke database
            $stmt = $pdo->prepare(
                "INSERT INTO surat_masuk_dewan (agenda_klasifikasi, agenda_urut, nomor_agenda_lengkap, nomor_surat_lengkap, tanggal_surat, tanggal_diterima, asal_surat, sifat_surat, perihal, diteruskan_kepada, keterangan, file_lampiran) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute(array_values($data_baru));

            $_SESSION['success_message'] = "Surat masuk dewan berhasil disimpan.";

            // Mencatat aktivitas pengguna
            log_activity($pdo, "Menambah Surat Masuk Dewan '{$nomor_agenda_lengkap}'", ['sesudah' => $data_baru]);
        }
    }

    header("Location: /surat-masuk-dewan");
    exit;
}

// Logika untuk memuat data awal untuk tabel
$limit = 10;
$stmt_data = $pdo->prepare("SELECT *, DATE_FORMAT(tanggal_diterima, '%d-%m-%Y') as tgl_terima_formatted FROM surat_masuk_dewan ORDER BY id DESC LIMIT ?");
$stmt_data->bindValue(1, $limit, PDO::PARAM_INT);
$stmt_data->execute();
$surat_masuk_list = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

$stmt_count = $pdo->query("SELECT COUNT(id) FROM surat_masuk_dewan");
$total_records = $stmt_count->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Logika untuk dropdown tahun dinamis
$stmt_years = $pdo->query("SELECT DISTINCT YEAR(tanggal_diterima) as year FROM surat_masuk_dewan WHERE YEAR(tanggal_diterima) IS NOT NULL ORDER BY year ASC");
$db_years = $stmt_years->fetchAll(PDO::FETCH_COLUMN);
$current_year = date('Y');
$all_years = array_unique(array_merge($db_years, [$current_year, $current_year + 1]));
rsort($all_years); // Mengurutkan dari tahun terbaru ke terlama

// Logika untuk nomor urut otomatis berdasarkan tahun sekarang
$stmt_next_num = $pdo->prepare("SELECT MAX(CAST(agenda_urut AS UNSIGNED)) FROM surat_masuk_dewan WHERE YEAR(tanggal_diterima) = ?");
$stmt_next_num->execute([$current_year]);
$max_num = $stmt_next_num->fetchColumn();
$next_nomor_urut = $max_num ? (int)$max_num + 1 : 1;

$pageTitle = 'Surat Masuk Dewan';
require_once 'templates/header.php';
?>

<!-- FORM PENCATATAN SURAT -->
<div class="pb-10 md:pb-6">
    <div id="form-masuk-dewan-container" class="bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-xl p-4 md:p-6 animate-fade-in border border-blue-100 mx-2 md:mx-0 mt-4 md:mt-0">
        <div class="flex justify-between items-center mb-4 md:mb-6 border-b border-blue-200 pb-2 md:pb-3">
            <h3 class="text-xl md:text-2xl font-bold text-gray-800 flex items-center">
            <span class="bg-gradient-to-r from-primary to-secondary text-transparent bg-clip-text">Form Pencatatan Surat Masuk Dewan</span>
                <i class="fas fa-user-tie ml-2 md:ml-3 text-primary"></i>
            </h3>
            <button id="toggle-form-masuk-dewan-btn" class="text-primary hover:text-secondary text-lg md:text-xl p-1 md:p-2">
                <i class="fas fa-chevron-up"></i>
            </button>
        </div>
        
        <form id="form-masuk-dewan-body" method="POST" action="/surat-masuk-dewan" enctype="multipart/form-data" class="space-y-4 md:space-y-6 transition-all duration-500">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                <!-- Kolom Kiri Form -->
                <div class="space-y-4 md:space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Klasifikasi / No. Urut Agenda / Tahun</label>
                        <div class="flex flex-col md:flex-row md:items-center space-y-2 md:space-y-0 md:space-x-2">
                            <input type="text" id="agenda_klasifikasi_dewan" name="agenda_klasifikasi" class="flex-1 px-3 md:px-4 py-2 md:py-3 rounded-xl border border-gray-300" placeholder="Klasifikasi">
                            <span class="hidden md:block text-gray-500 pt-2">/</span>
                            <input type="text" id="agenda_urut_dewan" name="agenda_urut" value="<?php echo $next_nomor_urut; ?>" class="w-full md:w-24 px-3 md:px-4 py-2 md:py-3 rounded-xl border border-gray-300 text-center" placeholder="No. Urut">
                            <span class="hidden md:block text-gray-500 pt-2">/</span>
                            <select id="tahun_penomoran_masuk_dewan" name="tahun_penomoran" class="w-full md:w-28 px-3 md:px-4 py-2 md:py-3 rounded-xl border border-gray-300 bg-white" required>
                                <?php foreach ($all_years as $year): ?>
                                    <option value="<?php echo $year; ?>" <?php echo ($year == $current_year) ? 'selected' : ''; ?>>
                                        <?php echo $year; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" id="checkAgendaDewanBtn" class="px-3 md:px-4 py-2 md:py-3 bg-indigo-100 text-indigo-600 rounded-xl hover:bg-indigo-200 mt-2 md:mt-0" title="Cek ketersediaan No. Urut Agenda">
                                <i class="fas fa-check"></i> <span class="md:hidden">Cek Nomor</span>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Surat</label>
                        <input type="text" name="nomor_surat_lengkap" class="w-full px-3 md:px-4 py-2 md:py-3 rounded-xl border border-gray-300" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Asal Surat</label>
                        <input type="text" name="asal_surat" class="w-full px-3 md:px-4 py-2 md:py-3 rounded-xl border border-gray-300" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sifat Surat</label>
                        <div class="flex flex-col md:flex-row md:items-center space-y-2 md:space-y-0 md:space-x-6 pt-2">
                            <label class="flex items-center space-x-2 cursor-pointer"><input type="radio" name="sifat_surat" value="Biasa" class="form-radio h-4 w-4 text-primary" checked><span class="text-gray-700">Biasa</span></label>
                            <label class="flex items-center space-x-2 cursor-pointer"><input type="radio" name="sifat_surat" value="Penting" class="form-radio h-4 w-4 text-primary"><span class="text-gray-700">Penting</span></label>
                            <label class="flex items-center space-x-2 cursor-pointer"><input type="radio" name="sifat_surat" value="Amat Segera" class="form-radio h-4 w-4 text-primary"><span class="text-gray-700">Amat Segera</span></label>
                        </div>
                    </div>
                </div>
                <!-- Kolom Kanan Form -->
                <div class="space-y-4 md:space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Surat</label>
                        <input type="date" name="tanggal_surat" class="w-full px-3 md:px-4 py-2 md:py-3 rounded-xl border border-gray-300" value="<?php echo date('Y-m-d'); ?>" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Diterima</label>
                        <input type="date" name="tanggal_diterima" class="w-full px-3 md:px-4 py-2 md:py-3 rounded-xl border border-gray-300" value="<?php echo date('Y-m-d'); ?>" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Perihal</label>
                        <textarea name="perihal" class="w-full px-3 md:px-4 py-2 md:py-3 rounded-xl border border-gray-300 h-24" required></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Diteruskan Kepada</label>
                        <input type="text" name="diteruskan_kepada" class="w-full px-3 md:px-4 py-2 md:py-3 rounded-xl border border-gray-300" placeholder="Contoh: Ketua Komisi A, Fraksi PDI Perjuangan, dll.">
                    </div>
                </div>
                <!-- Input field yang full-width -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                    <textarea name="keterangan" class="w-full px-3 md:px-4 py-2 md:py-3 rounded-xl border border-gray-300 h-24"></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">File Lampiran <span class="text-gray-400 font-normal">(PDF/JPG, maks 5MB)</span></label>
                    <div class="mt-1 flex justify-center px-4 md:px-6 pt-4 md:pt-5 pb-4 md:pb-6 border-2 border-dashed border-gray-300 rounded-xl hover:border-primary cursor-pointer relative group">
                        <input id="file-upload-masuk-dewan" name="file_lampiran" type="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <div class="space-y-1 text-center">
                            <i class="fas fa-cloud-upload-alt text-3xl md:text-4xl text-gray-400 group-hover:text-primary"></i>
                            <div class="flex flex-col md:flex-row text-sm text-gray-600">
                                <span class="relative bg-white rounded-md font-medium text-primary hover:text-secondary">Unggah file</span>
                                <p class="md:pl-1">atau tarik dan lepas</p>
                            </div>
                            <p class="text-xs text-gray-500" id="file-name-masuk-dewan">Belum ada file dipilih</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Tombol Aksi Form -->
            <div class="mt-6 flex flex-col-reverse md:flex-row justify-end space-y-reverse space-y-4 md:space-y-0 md:space-x-4">
                <button type="reset" class="px-4 md:px-6 py-2 md:py-3 border border-gray-300 rounded-xl hover:bg-gray-50 mt-4 md:mt-0">Reset</button>
                <button type="submit" name="simpan_surat_masuk_dewan" class="px-4 md:px-6 py-2 md:py-3 bg-gradient-to-r from-primary to-secondary text-white rounded-xl shadow-md hover:shadow-lg">
                    <i class="fas fa-save mr-2"></i> Simpan Surat
                </button>
            </div>
        </form>
    </div>
</div>

<!-- DAFTAR SURAT (TABEL) -->
<div id="list-masuk-dewan-container" class="mt-6 md:mt-8 bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-xl p-4 md:p-6 animate-fade-in border border-blue-100 mx-2 md:mx-0">
    <div class="flex flex-col gap-4 mb-4 md:mb-6">
        <h3 class="text-xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-list-alt text-primary mr-2"></i>
            <span class="bg-gradient-to-r from-primary to-secondary text-transparent bg-clip-text">Daftar Surat Masuk Dewan</span>
        </h3>
        <!-- Form Filter dan Pencarian -->
        <form id="searchFormMasukDewan" class="w-full flex flex-col md:flex-row items-stretch md:items-center gap-3">
            <select id="filterTahunMasukDewan" name="filter_tahun" class="w-full md:w-44 px-3 md:px-4 py-2 md:py-3 rounded-xl border border-gray-300 bg-white focus:ring-2 focus:ring-indigo-300 focus:border-indigo-500 transition duration-200">
                <option value="">Semua Tahun</option>
                <?php foreach ($all_years as $year): ?>
                    <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                <?php endforeach; ?>
            </select>
            <div class="relative w-full">
                <input type="text" id="searchInputMasukDewan" name="search" class="w-full pl-10 pr-4 py-2 md:py-3 border border-gray-300 rounded-xl" placeholder="Cari perihal, asal surat...">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <i class="fas fa-search"></i>
                </div>
            </div>
        </form>
    </div>

    <!-- Pembungkus tabel agar bisa scroll horizontal jika diperlukan -->
    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-primary to-secondary">
                <tr>
                    <th class="px-4 md:px-6 py-2 md:py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">No. Agenda</th>
                    <th class="px-4 md:px-6 py-2 md:py-4 text-left text-xs font-semibold text-white uppercase tracking-wider hidden md:table-cell">Asal Surat</th>
                    <th class="px-4 md:px-6 py-2 md:py-4 text-left text-xs font-semibold text-white uppercase tracking-wider hidden md:table-cell">Perihal</th>
                    <th class="px-4 md:px-6 py-2 md:py-4 text-left text-xs font-semibold text-white uppercase tracking-wider hidden md:table-cell">Tgl Diterima</th>
                    <?php if (in_array($_SESSION['user_role'], ['admin', 'staff surat masuk'])): ?>
                        <!-- [PERBAIKAN] Sembunyikan kolom 'Aksi' di mobile -->
                        <th class="px-4 md:px-6 py-2 md:py-4 text-left text-xs font-semibold text-white uppercase tracking-wider hidden md:table-cell">Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody id="tableBodyMasukDewan" class="bg-white divide-y divide-gray-200">
                <?php foreach ($surat_masuk_list as $surat): ?>
                    <!-- [PERBAIKAN] Tambah class 'relative' untuk positioning tombol aksi mobile -->
                    <tr class="hover:bg-blue-50 transition-colors duration-200 relative">
                        
                        <!-- [PERBAIKAN] Kolom utama ini akan terlihat di semua ukuran layar. Padding kanan (pr-12) ditambah untuk memberi ruang bagi tombol aksi di mobile. -->
                        <td class="px-4 pr-12 md:px-6 py-3 md:py-4 font-semibold">
                            
                            <!-- [PERBAIKAN] Tombol Aksi KHUSUS MOBILE -->
                            <?php if (in_array($_SESSION['user_role'], ['admin', 'staff surat masuk'])): ?>
                            <div class="md:hidden absolute top-4 right-4 flex space-x-3">
                                <a href="/edit-surat-masuk-dewan?id=<?php echo $surat['id']; ?>" class="text-blue-500 hover:text-blue-700" title="Edit"><i class="fas fa-edit"></i></a>
                                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                    <button onclick="window.confirmDelete('surat-masuk-dewan', <?php echo $surat['id']; ?>)" class="text-red-500 hover:text-red-700" title="Hapus"><i class="fas fa-trash"></i></button>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>

                            <!-- Konten Utama (Nomor Agenda & Detail) -->
                            <a href="#" class="text-primary hover:underline detail-link-masuk-dewan" data-id="<?php echo $surat['id']; ?>">
                                <?php echo htmlspecialchars($surat['nomor_agenda_lengkap']); ?>
                            </a>
                            <!-- Detail surat yang hanya tampil di mobile -->
                            <div class="md:hidden text-sm text-gray-600 mt-1 space-y-1">
                                <div>Dari: <?php echo htmlspecialchars($surat['asal_surat']); ?></div>
                                <div class="truncate"><?php echo htmlspecialchars($surat['perihal']); ?></div>
                                <div>Diterima: <?php echo htmlspecialchars($surat['tgl_terima_formatted']); ?></div>
                            </div>
                        </td>

                        <!-- [PERBAIKAN] Kolom-kolom ini hanya akan muncul di DESKTOP (md:table-cell) -->
                        <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600 hidden md:table-cell"><?php echo htmlspecialchars($surat['asal_surat']); ?></td>
                        <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600 hidden md:table-cell"><?php echo htmlspecialchars($surat['perihal']); ?></td>
                        <td class="px-4 md:px-6 py-3 md:py-4 text-gray-600 hidden md:table-cell"><?php echo htmlspecialchars($surat['tgl_terima_formatted']); ?></td>
                        
                        <!-- [PERBAIKAN] Kolom Aksi KHUSUS DESKTOP -->
                        <?php if (in_array($_SESSION['user_role'], ['admin', 'staff surat masuk'])): ?>
                            <td class="px-4 md:px-6 py-3 md:py-4 hidden md:table-cell">
                                <div class="flex space-x-2">
                                    <a href="/edit-surat-masuk-dewan?id=<?php echo $surat['id']; ?>" class="text-blue-500 hover:text-blue-700" title="Edit"><i class="fas fa-edit"></i></a>
                                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                        <button onclick="window.confirmDelete('surat-masuk-dewan', <?php echo $surat['id']; ?>)" class="text-red-500 hover:text-red-700" title="Hapus"><i class="fas fa-trash"></i></button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        <?php endif; ?>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Kontainer untuk navigasi halaman (pagination) -->
    <div id="paginationContainerMasukDewan" class="mt-4 md:mt-6">
        <?php
        if ($total_pages > 1) {
            echo '<div class="flex flex-col md:flex-row items-center justify-between gap-3 md:gap-0">';
            echo '<div class="text-sm text-gray-600">Halaman 1 dari ' . $total_pages . '</div>';
            echo '<div><button onclick="document.getElementById(\'searchFormMasukDewan\').fetchData(2)" class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-primary hover:bg-gray-50 w-full md:w-auto">Selanjutnya <i class="fas fa-arrow-right ml-1"></i></button></div>';
            echo '</div>';
        }
        ?>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>
