<?php
// pages/surat-keluar.php

// Fungsi untuk menangani unggahan file
function handleFileUpload($fileInputName, $subDirectory) {
    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES[$fileInputName];
        $fileName = time() . '_' . basename($file['name']);
        $mainUploadDir = realpath(dirname(__FILE__) . '/../uploads');
        $targetDir = $mainUploadDir . DIRECTORY_SEPARATOR . $subDirectory;
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
        $allowedTypes = ['application/pdf', 'image/jpeg'];
        $maxSize = 5 * 1024 * 1024;
        if (!in_array($file['type'], $allowedTypes)) {
            $_SESSION['error_message'] = 'Tipe file tidak valid. Hanya PDF dan JPG.';
            return null;
        }
        if ($file['size'] > $maxSize) {
            $_SESSION['error_message'] = 'Ukuran file terlalu besar. Maksimal 5 MB.';
            return null;
        }
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $subDirectory . '/' . $fileName; 
        }
    }
    return null;
}

// Logika untuk menyimpan surat baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_surat'])) {
    $nomor_urut = $_POST['nomor_urut'];
    // Ambil tahun dari dropdown baru
    $tahun = $_POST['tahun_penomoran']; 

    // Cek duplikasi nomor urut berdasarkan tahun
    $stmt_check = $pdo->prepare("SELECT COUNT(id) FROM surat_keluar WHERE nomor_urut = ? AND YEAR(tanggal_surat) = ?");
    $stmt_check->execute([$nomor_urut, $tahun]);
    $is_exists = $stmt_check->fetchColumn() > 0;

    if ($is_exists) {
        $_SESSION['error_message'] = "Nomor Urut '{$nomor_urut}' untuk tahun {$tahun} sudah terdaftar.";
    } else {
        $fileLampiran = handleFileUpload('file_lampiran', 'surat_keluar');
        if (!isset($_SESSION['error_message'])) {
            $kode_klas = $_POST['kode_klasifikasi'];
            $tgl_surat = $_POST['tanggal_surat'];
            $tujuan = $_POST['tujuan'];
            $sifat_surat = $_POST['sifat_surat'] ?? 'Biasa';
            $perihal = $_POST['perihal'];
            $hub_surat = $_POST['hub_surat_no'];
            $konseptor = $_POST['konseptor'];
            $keterangan = $_POST['keterangan'];

            // Gunakan tahun dari dropdown untuk membuat nomor lengkap
            $nomor_lengkap = sprintf("%s/%s/436.5/%s", $kode_klas, $nomor_urut, $tahun);
            
            $stmt = $pdo->prepare("INSERT INTO surat_keluar (kode_klasifikasi, nomor_urut, nomor_surat_lengkap, tanggal_surat, tujuan, sifat_surat, perihal, hub_surat_no, konseptor, keterangan, file_lampiran) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$kode_klas, $nomor_urut, $nomor_lengkap, $tgl_surat, $tujuan, $sifat_surat, $perihal, $hub_surat, $konseptor, $keterangan, $fileLampiran]);
            
            $_SESSION['success_message'] = "Surat keluar berhasil disimpan.";
        }
    }
    
    header("Location: /surat-keluar");
    exit;
}

// Logika untuk menampilkan data di tabel (tidak berubah)
$limit = 10;
$stmt_data = $pdo->prepare("SELECT *, DATE_FORMAT(tanggal_surat, '%d-%m-%Y') as tgl_formatted FROM surat_keluar ORDER BY id DESC LIMIT ?");
$stmt_data->bindValue(1, $limit, PDO::PARAM_INT);
$stmt_data->execute();
$surat_keluar_list = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

$stmt_count = $pdo->query("SELECT COUNT(id) FROM surat_keluar");
$total_records = $stmt_count->fetchColumn();
$total_pages = ceil($total_records / $limit);

$pageTitle = 'Surat Keluar';
require_once 'templates/header.php';
?>

<div id="form-keluar-container" class="bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-xl p-6 animate-fade-in border border-blue-100 transition-all duration-500">
    <div class="flex justify-between items-center mb-6 border-b border-blue-200 pb-3">
        <h3 class="text-2xl font-bold text-gray-800 flex items-center">
            <span class="bg-gradient-to-r from-primary to-secondary text-transparent bg-clip-text">Form Penomoran Surat Keluar</span>
            <i class="fas fa-paper-plane ml-3 text-primary"></i>
        </h3>
        <button id="toggle-form-btn" class="text-primary hover:text-secondary text-xl p-2">
            <i class="fas fa-chevron-up"></i>
        </button>
    </div>
    
    <form id="form-keluar-body" method="POST" action="/surat-keluar" enctype="multipart/form-data" class="space-y-6 transition-all duration-500">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-5">
                <div>
                    <!-- MODIFIKASI: Mengubah label dan struktur input nomor -->
                    <label class="block text-sm font-medium text-gray-700 mb-2">Klasifikasi / Nomor Urut / Tahun</label>
                    <div class="flex items-center space-x-2">
                        <input type="text" name="kode_klasifikasi" class="flex-1 px-4 py-3 rounded-xl border border-gray-300" placeholder="Klasifikasi" required />
                        <span class="text-gray-500 pt-2">/</span>
                        <input type="number" id="nomor_urut_keluar" name="nomor_urut" class="w-24 px-4 py-3 rounded-xl border border-gray-300 text-center" placeholder="No. Urut" required />
                        <span class="text-gray-500 pt-2">/</span>
                        <!-- Dropdown Tahun -->
                        <select name="tahun_penomoran" class="w-28 px-4 py-3 rounded-xl border border-gray-300 bg-white">
                            <option value="2025" <?php echo (date('Y') == '2025') ? 'selected' : ''; ?>>2025</option>
                            <option value="2026" <?php echo (date('Y') == '2026') ? 'selected' : ''; ?>>2026</option>
                        </select>
                        <button type="button" id="checkNomorKeluarBtn" class="px-4 py-3 bg-indigo-100 text-indigo-600 rounded-xl hover:bg-indigo-200" title="Cek ketersediaan No. Urut">
                            <i class="fas fa-check"></i>
                        </button>
                    </div>
                </div>
                 <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Surat</label>
                    <input type="date" name="tanggal_surat" class="w-full px-4 py-3 rounded-xl border border-gray-300" value="<?php echo date('Y-m-d'); ?>" required />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tujuan</label>
                    <input type="text" name="tujuan" class="w-full px-4 py-3 rounded-xl border border-gray-300" required />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Konseptor</label>
                    <input type="text" name="konseptor" class="w-full px-4 py-3 rounded-xl border border-gray-300" />
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

            </div>
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Perihal</label>
                    <textarea name="perihal" class="w-full px-4 py-3 rounded-xl border border-gray-300 h-32" required></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Hubungan Dgn Surat No</label>
                    <input type="text" name="hub_surat_no" class="w-full px-4 py-3 rounded-xl border border-gray-300" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                    <textarea name="keterangan" class="w-full px-4 py-3 rounded-xl border border-gray-300 h-32"></textarea>
                </div>
                
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">File Lampiran <span class="text-gray-400 font-normal">(PDF/JPG, maks 5MB)</span></label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed border-gray-300 rounded-xl hover:border-primary cursor-pointer relative group">
                    <input id="file-upload-keluar" name="file_lampiran" type="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    <div class="space-y-1 text-center">
                         <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 group-hover:text-primary"></i>
                        <div class="flex text-sm text-gray-600">
                            <span class="relative bg-white rounded-md font-medium text-primary hover:text-secondary">
                                Unggah file
                            </span>
                            <p class="pl-1">atau tarik dan lepas</p>
                        </div>
                        <p class="text-xs text-gray-500" id="file-name-keluar">Belum ada file dipilih</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-8 flex justify-end space-x-4">
            <button type="reset" class="px-6 py-3 border border-gray-300 rounded-xl hover:bg-gray-50">Reset</button>
            <button type="submit" name="simpan_surat" class="px-6 py-3 bg-gradient-to-r from-primary to-secondary text-white rounded-xl shadow-md hover:shadow-lg">
                <i class="fas fa-save mr-2"></i> Simpan
            </button>
        </div>
    </form>
</div>

<!-- Bagian tabel daftar surat (tidak ada perubahan) -->
<div id="list-keluar-container" class="mt-8 bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-xl p-6 animate-fade-in border border-blue-100 transition-all duration-500">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <h3 class="text-xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-list-alt text-primary mr-2"></i> 
            <span class="bg-gradient-to-r from-primary to-secondary text-transparent bg-clip-text">Daftar Surat Keluar</span>
        </h3>
        <form id="searchFormKeluar" class="w-full md:w-96 relative">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-search"></i></div>
                <input type="text" id="searchInputKeluar" name="search" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl" placeholder="Ketik untuk mencari surat...">
            </div>
        </form>
    </div>
    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-primary to-secondary">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">No. Surat</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">Perihal</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">Tujuan</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">Lampiran</th>
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody id="tableBodyKeluar" class="bg-white divide-y divide-gray-200">
                <?php foreach ($surat_keluar_list as $surat): ?>
                     <tr class="hover:bg-blue-50 transition-colors duration-200">
                        <td class="px-6 py-4 font-medium">
                            <a href="#" class="text-primary hover:underline detail-link-keluar" data-id="<?php echo $surat['id']; ?>">
                                <?php echo htmlspecialchars($surat['nomor_surat_lengkap']); ?>
                            </a>
                        </td>
                        <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($surat['tgl_formatted']); ?></td>
                        <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($surat['perihal']); ?></td>
                        <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($surat['tujuan']); ?></td>
                        <td class="px-6 py-4">
                            <?php if ($surat['file_lampiran']): ?>
                                <a href="/uploads/<?php echo htmlspecialchars($surat['file_lampiran']); ?>" target="_blank" class="inline-flex items-center text-primary hover:text-secondary">
                                    <i class="fas fa-file-alt mr-1"></i> Lihat
                                </a>
                            <?php else: ?>
                                <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <a href="/edit-surat-keluar?id=<?php echo $surat['id']; ?>" class="text-blue-500 hover:text-blue-700" title="Edit"><i class="fas fa-edit"></i></a>
                                    <button onclick="confirmDelete('keluar', <?php echo $surat['id']; ?>)" class="text-red-500 hover:text-red-700" title="Hapus"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div id="paginationContainerKeluar" class="mt-6">
         <?php
         if ($total_pages > 1) {
             echo '<div class="flex items-center justify-between">';
             echo '<div class="text-sm text-gray-600">Halaman 1 dari ' . $total_pages . '</div>';
             echo '<div><button onclick="fetchData(2)" class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-primary hover:bg-gray-50">Selanjutnya <i class="fas fa-arrow-right ml-1"></i></button></div>';
             echo '</div>';
         }
         ?>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>

