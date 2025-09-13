<?php
// pages/disposisi-sekwan.php

// Fungsi untuk menangani unggahan file
function handleDisposisiFileUpload($fileInputName) {
    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES[$fileInputName];
        $fileName = time() . '_' . basename($file['name']);
        
        $targetDir = realpath(dirname(__FILE__) . '/../uploads/disposisi_sekwan');
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        $maxSize = 5 * 1024 * 1024; // 5 MB

        if (!in_array($file['type'], $allowedTypes)) {
            $_SESSION['error_message'] = 'Tipe file tidak valid. Hanya PDF, JPG, dan PNG yang diizinkan.';
            return null;
        }
        if ($file['size'] > $maxSize) {
            $_SESSION['error_message'] = 'Ukuran file terlalu besar. Maksimal 5 MB.';
            return null;
        }

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $fileName;
        }
    }
    return null;
}

// Logika untuk menyimpan data disposisi baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_disposisi'])) {
    $surat_masuk_id = $_POST['surat_masuk_id'];
    $nama_pegawai = $_POST['nama_pegawai'];
    $catatan = $_POST['catatan_disposisi'];

    $fileLampiran = handleDisposisiFileUpload('file_lampiran');
    
    if (!isset($_SESSION['error_message'])) {
        $stmt = $pdo->prepare(
            "INSERT INTO disposisi_sekwan (surat_masuk_id, nama_pegawai, catatan_disposisi, file_lampiran) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$surat_masuk_id, $nama_pegawai, $catatan, $fileLampiran]);
        
        $_SESSION['success_message'] = "Disposisi berhasil disimpan.";
    }

    header("Location: /disposisi-sekwan");
    exit;
}

// Ambil daftar surat masuk yang BELUM didisposisikan untuk dropdown
$surat_untuk_disposisi = $pdo->query(
    "SELECT sm.id, sm.nomor_agenda_lengkap 
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
            sm.nomor_agenda_lengkap, sm.perihal
     FROM disposisi_sekwan ds
     JOIN surat_masuk sm ON ds.surat_masuk_id = sm.id
     ORDER BY ds.id DESC LIMIT ?"
);
$stmt_data->bindValue(1, $limit, PDO::PARAM_INT);
$stmt_data->execute();
$disposisi_list = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

$stmt_count = $pdo->query("SELECT COUNT(id) FROM disposisi_sekwan");
$total_records = $stmt_count->fetchColumn();
$total_pages = ceil($total_records / $limit);

$pageTitle = 'Disposisi Surat Masuk';
require_once 'templates/header.php';
?>

<!-- Form Disposisi (tidak ada perubahan) -->
<div id="form-disposisi-container" class="bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-xl p-6 animate-fade-in border border-blue-100 transition-all duration-500">
    <div class="flex justify-between items-center mb-6 border-b border-blue-200 pb-3">
        <h3 class="text-2xl font-bold text-gray-800 flex items-center">
            <span class="bg-gradient-to-r from-primary to-secondary text-transparent bg-clip-text">Form Disposisi Surat (Sekwan)</span>
            <i class="fas fa-share-square ml-3 text-primary"></i>
        </h3>
        <button id="toggle-form-disposisi-btn" class="text-primary hover:text-secondary text-xl p-2">
            <i class="fas fa-chevron-up"></i>
        </button>
    </div>
    
    <form id="form-disposisi-body" method="POST" action="/disposisi-sekwan" class="space-y-6 transition-all duration-500" enctype="multipart/form-data">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="surat_masuk_id" class="block text-sm font-medium text-gray-700 mb-2">Nomor Agenda Surat</label>
                <select id="surat_masuk_id" name="surat_masuk_id" class="w-full px-4 py-3 rounded-xl border border-gray-300" required>
                    <option value="" disabled selected>-- Pilih Nomor Agenda --</option>
                    <?php foreach ($surat_untuk_disposisi as $surat): ?>
                        <option value="<?php echo $surat['id']; ?>">
                            <?php echo htmlspecialchars($surat['nomor_agenda_lengkap']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="nama_pegawai" class="block text-sm font-medium text-gray-700 mb-2">Nama Pegawai</label>
                <input type="text" id="nama_pegawai" name="nama_pegawai" class="w-full px-4 py-3 rounded-xl border border-gray-300" placeholder="Masukkan nama pegawai yang dituju" required>
            </div>

            <div class="md:col-span-2">
                <label for="catatan_disposisi" class="block text-sm font-medium text-gray-700 mb-2">Isi/Catatan Disposisi</label>
                <textarea id="catatan_disposisi" name="catatan_disposisi" rows="4" class="w-full px-4 py-3 rounded-xl border border-gray-300" placeholder="Contoh: Mohon segera ditindaklanjuti..."></textarea>
            </div>
            
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">File Lampiran <span class="text-gray-400 font-normal">(Opsional: PDF/JPG/PNG, maks 5MB)</span></label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed border-gray-300 rounded-xl hover:border-primary cursor-pointer relative group">
                    <input id="file-upload-disposisi" name="file_lampiran" type="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    <div class="space-y-1 text-center">
                         <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 group-hover:text-primary"></i>
                        <div class="flex text-sm text-gray-600">
                            <span class="relative bg-white rounded-md font-medium text-primary hover:text-secondary">
                                Unggah file
                            </span>
                            <p class="pl-1">atau tarik dan lepas</p>
                        </div>
                        <p class="text-xs text-gray-500" id="file-name-disposisi">Belum ada file dipilih</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end">
            <button type="submit" name="simpan_disposisi" class="px-6 py-3 bg-gradient-to-r from-primary to-secondary text-white rounded-xl shadow-md hover:shadow-lg">
                <i class="fas fa-save mr-2"></i> Simpan Disposisi
            </button>
        </div>
    </form>
</div>

<!-- Daftar Disposisi -->
<div id="list-disposisi-container" class="mt-8 bg-white rounded-2xl shadow-xl p-6">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
         <h3 class="text-xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-list-alt text-primary mr-2"></i> 
            <span class="bg-gradient-to-r from-primary to-secondary text-transparent bg-clip-text">Daftar Disposisi</span>
        </h3>
        <form id="searchFormDisposisi" class="w-full md:w-80">
            <div class="relative">
                <input type="text" id="searchInputDisposisi" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl" placeholder="Cari perihal, no agenda...">
                <i class="fas fa-search absolute left-3 top-4 text-gray-400"></i>
            </div>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Agenda</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Perihal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pegawai Tertuju</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Disposisi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lampiran</th>
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody id="tableBodyDisposisi" class="bg-white divide-y divide-gray-200">
                <?php foreach ($disposisi_list as $disposisi): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap font-medium text-primary"><?php echo htmlspecialchars($disposisi['nomor_agenda_lengkap']); ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($disposisi['perihal']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($disposisi['nama_pegawai']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($disposisi['tgl_disposisi_formatted']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($disposisi['file_lampiran']): ?>
                                <a href="/uploads/disposisi_sekwan/<?php echo htmlspecialchars($disposisi['file_lampiran']); ?>" target="_blank" class="text-indigo-600 hover:text-indigo-900">Lihat File</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                         <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <!-- MODIFIKASI: Menambahkan tombol Edit dan mengubah tombol Batalkan menjadi ikon -->
                                <div class="flex space-x-3">
                                    <a href="/edit-disposisi-sekwan?id=<?php echo $disposisi['id']; ?>" class="text-blue-500 hover:text-blue-700" title="Edit Disposisi">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="confirmDelete('disposisi-sekwan', <?php echo $disposisi['id']; ?>)" class="text-red-500 hover:text-red-700" title="Batalkan Disposisi">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div id="paginationContainerDisposisi" class="mt-4">
        <!-- Pagination akan dimuat oleh JavaScript -->
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>
