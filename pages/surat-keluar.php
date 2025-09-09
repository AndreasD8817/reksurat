<?php
// pages/surat-keluar.php

/**
 * Menangani unggahan file, memvalidasi, dan menyimpannya ke subdirektori tertentu.
 *
 * @param string $fileInputName Nama dari input file di form HTML (misal: 'file_lampiran').
 * @param string $subDirectory  Nama subdirektori di dalam 'uploads' (misal: 'surat_masuk').
 * @return string|null          Mengembalikan 'subDirectory/namaFile.ext' jika berhasil, atau null jika gagal.
 */
function handleFileUpload($fileInputName, $subDirectory) {
    // Cek jika ada file yang dikirim dan tidak ada error
    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES[$fileInputName];
        $fileName = time() . '_' . basename($file['name']);

        // --- INI BAGIAN KUNCI YANG DIPERBARUI ---
        // Menentukan path direktori utama 'uploads'
        $mainUploadDir = realpath(dirname(__FILE__) . '/../uploads');
        
        // Membuat path lengkap ke subdirektori (misal: C:\laragon\www\reksurat\uploads\surat_masuk)
        $targetDir = $mainUploadDir . DIRECTORY_SEPARATOR . $subDirectory;

        // Cek jika subdirektori belum ada, maka buat folder tersebut
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true); // 0777 memberikan izin penuh, 'true' untuk membuat folder rekursif
        }

        // Membuat path tujuan file yang lengkap dan andal
        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
        // -----------------------------------------

        // Validasi tipe & ukuran file
        $allowedTypes = ['application/pdf', 'image/jpeg'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowedTypes)) {
            $_SESSION['error_message'] = 'Tipe file tidak valid. Hanya PDF dan JPG.';
            return null;
        }
        if ($file['size'] > $maxSize) {
            $_SESSION['error_message'] = 'Ukuran file terlalu besar. Maksimal 5 MB.';
            return null;
        }

        // Pindahkan file dan kembalikan path relatif jika berhasil
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Mengembalikan path dengan subdirektorinya untuk disimpan di database
            return $subDirectory . '/' . $fileName; 
        }
    }
    // Jika tidak ada file yang diunggah, kembalikan null
    return null;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_surat'])) {
    $nomor_urut = $_POST['nomor_urut'];
    $stmt_check = $pdo->prepare("SELECT COUNT(id) FROM surat_keluar WHERE nomor_urut = ?");
    $stmt_check->execute([$nomor_urut]);
    $is_exists = $stmt_check->fetchColumn() > 0;

    if ($is_exists) {
        $_SESSION['error_message'] = "Nomor Urut '{$nomor_urut}' sudah terdaftar.";
    } else {
        $fileLampiran = handleFileUpload('file_lampiran', 'surat_keluar');
        if (!isset($_SESSION['error_message'])) {
            $kode_klas = $_POST['kode_klasifikasi'];
            $tgl_surat = $_POST['tanggal_surat'];
            $tujuan = $_POST['tujuan'];
            $perihal = $_POST['perihal'];
            $hub_surat = $_POST['hub_surat_no'];
            $konseptor = $_POST['konseptor'];
            $keterangan = $_POST['keterangan'];

            $tahun = date('Y', strtotime($tgl_surat));
            $nomor_lengkap = sprintf("%s/%s/436.5/%s", $kode_klas, $nomor_urut, $tahun);
            
            $stmt = $pdo->prepare("INSERT INTO surat_keluar (kode_klasifikasi, nomor_urut, nomor_surat_lengkap, tanggal_surat, tujuan, perihal, hub_surat_no, konseptor, keterangan, file_lampiran) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$kode_klas, $nomor_urut, $nomor_lengkap, $tgl_surat, $tujuan, $perihal, $hub_surat, $konseptor, $keterangan, $fileLampiran]);
            
            $_SESSION['success_message'] = "Surat keluar berhasil disimpan.";
        }
    }
    
    header("Location: /surat-keluar");
    exit;
}

// Logika untuk memuat data awal
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

<div class="bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-xl p-6 animate-fade-in border border-blue-100">
    <h3 class="text-2xl font-bold text-gray-800 mb-6 border-b border-blue-200 pb-3 flex items-center">
        <span class="bg-gradient-to-r from-primary to-secondary text-transparent bg-clip-text">Form Penomoran Surat Keluar</span>
        <i class="fas fa-paper-plane ml-3 text-primary"></i>
    </h3>
    <form method="POST" action="/surat-keluar" enctype="multipart/form-data" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kode Klasifikasi & No. Urut</label>
                    <div class="flex items-center space-x-2">
                        <input type="text" name="kode_klasifikasi" class="flex-1 px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-300" placeholder="Klasifikasi" required />
                        <span class="text-gray-500 pt-2">/</span>
                        <input type="number" id="nomor_urut_keluar" name="nomor_urut" class="w-full px-4 py-3 rounded-xl border border-gray-300 text-center focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-300" placeholder="No. Urut" required />
                        <button type="button" id="checkNomorKeluarBtn" class="px-4 py-3 bg-indigo-100 text-indigo-600 rounded-xl hover:bg-indigo-200 transition-all duration-300 transform hover:scale-105" title="Cek ketersediaan No. Urut">
                            <i class="fas fa-check"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Surat</label>
                    <input type="date" name="tanggal_surat" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-300" value="<?php echo date('Y-m-d'); ?>" required />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tujuan</label>
                    <input type="text" name="tujuan" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-300" required />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Konseptor</label>
                    <input type="text" name="konseptor" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-300" />
                </div>
            </div>
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Perihal</label>
                    <textarea name="perihal" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-300 h-32" required></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Hubungan Dgn Surat No</label>
                    <input type="text" name="hub_surat_no" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-300" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                    <textarea name="keterangan" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-300 h-32"></textarea>
                </div>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">File Lampiran <span class="text-gray-400 font-normal">(PDF/JPG, maks 5MB)</span></label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed border-gray-300 rounded-xl hover:border-primary transition-all duration-300 cursor-pointer relative overflow-hidden group">
                    <input id="file-upload-keluar" name="file_lampiran" type="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    <div class="space-y-1 text-center">
                         <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 group-hover:text-primary transition-colors duration-300"></i>
                        <div class="flex text-sm text-gray-600">
                            <span class="relative bg-white rounded-md font-medium text-primary hover:text-secondary focus-within:outline-none">
                                <span class="text-primary">Unggah file</span>
                            </span>
                            <p class="pl-1">atau tarik dan lepas</p>
                        </div>
                        <p class="text-xs text-gray-500" id="file-name-keluar">Belum ada file dipilih</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-8 flex justify-end space-x-4">
            <button type="reset" class="px-6 py-3 border border-gray-300 rounded-xl hover:bg-gray-50 transition-all duration-300 transform hover:-translate-y-1">Reset</button>
            <button type="submit" name="simpan_surat" class="px-6 py-3 bg-gradient-to-r from-primary to-secondary text-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 hover:scale-105">
                <i class="fas fa-save mr-2"></i> Simpan
            </button>
        </div>
    </form>
</div>

<div class="mt-8 bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-xl p-6 animate-fade-in border border-blue-100">
    
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <h3 class="text-xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-list-alt text-primary mr-2"></i> 
            <span class="bg-gradient-to-r from-primary to-secondary text-transparent bg-clip-text">Daftar Surat Keluar</span>
        </h3>
        <form id="searchFormKeluar" class="w-full md:w-96 relative">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-search"></i></div>
                <input type="text" id="searchInputKeluar" name="search" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-300" placeholder="Ketik untuk mencari surat...">
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
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody id="tableBodyKeluar" class="bg-white divide-y divide-gray-200">
                <?php foreach ($surat_keluar_list as $surat): ?>
                    <tr class="hover:bg-blue-50 transition-colors duration-200">
                        <td class="px-6 py-4 font-medium text-primary"><?php echo htmlspecialchars($surat['nomor_surat_lengkap']); ?></td>
                        <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($surat['tgl_formatted']); ?></td>
                        <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($surat['perihal']); ?></td>
                        <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($surat['tujuan']); ?></td>
                        <td class="px-6 py-4">
                            <?php if ($surat['file_lampiran']): ?>
                                <a href="/uploads/<?php echo htmlspecialchars($surat['file_lampiran']); ?>" target="_blank" class="inline-flex items-center text-primary hover:text-secondary transition-colors duration-200">
                                    <i class="fas fa-file-alt mr-1"></i> Lihat
                                </a>
                            <?php else: ?>
                                <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
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
         // Logika untuk menampilkan pagination awal
         if ($total_pages > 1) {
             echo '<div class="flex items-center justify-between">';
             echo '<div class="text-sm text-gray-600">Halaman 1 dari ' . $total_pages . '</div>';
             echo '<div><button onclick="fetchData(2)" class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-primary hover:bg-gray-50 transition-all duration-300 transform hover:-translate-y-1">Selanjutnya <i class="fas fa-arrow-right ml-1"></i></button></div>';
             echo '</div>';
         }
         ?>
    </div>
</div>

<script>
    // Script untuk menampilkan nama file yang dipilih
    document.getElementById('file-upload-keluar').addEventListener('change', function(e) {
        var fileName = e.target.files[0] ? e.target.files[0].name : 'Belum ada file dipilih';
        document.getElementById('file-name-keluar').textContent = fileName;
    });
</script>

<?php require_once 'templates/footer.php'; ?>