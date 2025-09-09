<?php
// pages/surat-masuk.php

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_surat_masuk'])) {
    $agenda_urut = $_POST['agenda_urut'];
    
    $stmt_check = $pdo->prepare("SELECT COUNT(id) FROM surat_masuk WHERE agenda_urut = ?");
    $stmt_check->execute([$agenda_urut]);
    $is_exists = $stmt_check->fetchColumn() > 0;

    if ($is_exists) {
        $_SESSION['error_message'] = "Nomor Urut Agenda '{$agenda_urut}' sudah terdaftar.";
    } else {
        $$fileLampiran = handleFileUpload('file_lampiran', 'surat_masuk'); 
        
        // Lanjutkan hanya jika tidak ada error dari upload file
        if (!isset($_SESSION['error_message'])) {
            $agenda_klas = $_POST['agenda_klasifikasi'];
            $nomor_surat_lengkap = $_POST['nomor_surat_lengkap'];
            // ... (variabel lainnya tetap sama)
            $asal_surat = $_POST['asal_surat'];
            $perihal = $_POST['perihal'];
            $keterangan = $_POST['keterangan'];
            $tgl_surat = $_POST['tanggal_surat'];
            $tgl_diterima = $_POST['tanggal_diterima'];
            
            $tahun = date('Y', strtotime($tgl_diterima));
            $nomor_agenda_lengkap = sprintf("%s/%s/436.5/%s", $agenda_klas, $agenda_urut, $tahun);
    
            $stmt = $pdo->prepare(
                "INSERT INTO surat_masuk (agenda_klasifikasi, agenda_urut, nomor_agenda_lengkap, nomor_surat_lengkap, tanggal_surat, tanggal_diterima, asal_surat, perihal, keterangan, file_lampiran) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$agenda_klas, $agenda_urut, $nomor_agenda_lengkap, $nomor_surat_lengkap, $tgl_surat, $tgl_diterima, $asal_surat, $perihal, $keterangan, $fileLampiran]);
            
            $_SESSION['success_message'] = "Surat masuk berhasil disimpan.";
        }
    }
    
    header("Location: /surat-masuk");
    exit;
}

// ... (sisa kode PHP untuk memuat data awal tidak berubah)
$limit = 10;
$stmt_data = $pdo->prepare("SELECT *, DATE_FORMAT(tanggal_diterima, '%d-%m-%Y') as tgl_terima_formatted FROM surat_masuk ORDER BY id DESC LIMIT ?");
$stmt_data->bindValue(1, $limit, PDO::PARAM_INT);
$stmt_data->execute();
$surat_masuk_list = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

$stmt_count = $pdo->query("SELECT COUNT(id) FROM surat_masuk");
$total_records = $stmt_count->fetchColumn();
$total_pages = ceil($total_records / $limit);

$pageTitle = 'Surat Masuk';
require_once 'templates/header.php';
?>

<div class="bg-white rounded-2xl shadow-lg p-6 animate-fade-in">
    <h3 class="text-xl font-semibold text-gray-800 mb-6 border-b pb-3">Form Pencatatan Surat Masuk</h3>
    <form method="POST" action="/surat-masuk" enctype="multipart/form-data">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Agenda</label>
                <div class="flex items-center space-x-2">
                    <input type="text" id="agenda_klasifikasi" name="agenda_klasifikasi" class="w-full px-4 py-3 rounded-xl border" placeholder="Klasifikasi">
                    <span class="text-gray-500 pt-2">/</span>
                    <input type="text" id="agenda_urut" name="agenda_urut" class="w-full px-4 py-3 rounded-xl border" placeholder="No. Urut">
                    <button type="button" id="checkAgendaBtn" class="px-4 py-3 bg-indigo-100 text-indigo-600 rounded-xl hover:bg-indigo-200" title="Cek ketersediaan No. Urut">
                        <i class="fas fa-check"></i>
                    </button>
                </div>
            </div>
            <div><label class="block text-sm font-medium text-gray-700 mb-2">Nomor Surat</label><input type="text" name="nomor_surat_lengkap" class="w-full px-4 py-3 rounded-xl border" required></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-2">Asal Surat</label><input type="text" name="asal_surat" class="w-full px-4 py-3 rounded-xl border" required /></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-2">Perihal</label><textarea name="perihal" class="w-full px-4 py-3 rounded-xl border h-32" required></textarea></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Surat</label><input type="date" name="tanggal_surat" class="w-full px-4 py-3 rounded-xl border" value="<?php echo date('Y-m-d'); ?>" required /></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Diterima</label><input type="date" name="tanggal_diterima" class="w-full px-4 py-3 rounded-xl border" value="<?php echo date('Y-m-d'); ?>" required /></div>
            
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">File Lampiran <span class="text-gray-400 font-normal">(PDF/JPG, maks 5MB)</span></label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                    <div class="space-y-1 text-center">
                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-400"></i>
                        <div class="flex text-sm text-gray-600">
                            <label for="file-upload-masuk" class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-secondary focus-within:outline-none">
                                <span>Unggah file</span>
                                <input id="file-upload-masuk" name="file_lampiran" type="file" class="sr-only">
                            </label>
                            <p class="pl-1">atau tarik dan lepas</p>
                        </div>
                        <p class="text-xs text-gray-500" id="file-name-masuk">Belum ada file dipilih</p>
                    </div>
                </div>
            </div>

            <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label><textarea name="keterangan" class="w-full px-4 py-3 rounded-xl border h-32"></textarea></div>
        </div>
        <div class="mt-8 flex justify-end">
            <button type="submit" name="simpan_surat_masuk" class="px-6 py-3 bg-primary text-white rounded-xl shadow-md">
                <i class="fas fa-save mr-2"></i> Simpan Surat
            </button>
        </div>
    </form>
</div>

<div class="mt-8 bg-white rounded-2xl shadow-lg p-6 animate-fade-in">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <h3 class="text-lg font-semibold text-gray-800">Daftar Surat Masuk</h3>
        <form id="searchFormMasuk" class="w-full md:w-96">
            <div class="relative">
                <input type="text" id="searchInputMasuk" name="search" class="w-full pl-10 pr-4 py-3 border rounded-xl" placeholder="Cari...">
                <i class="fas fa-search absolute left-3 top-3.5 text-gray-400"></i>
            </div>
        </form>
    </div>
    <div class="overflow-x-auto rounded-xl border">
        <table class="min-w-full divide-y">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">No. Agenda</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Asal Surat</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Perihal</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Tgl Diterima</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Lampiran</th>
                </tr>
            </thead>
            <tbody id="tableBodyMasuk" class="bg-white divide-y">
                <?php foreach ($surat_masuk_list as $surat): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-semibold text-gray-800"><?php echo htmlspecialchars($surat['nomor_agenda_lengkap']); ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($surat['asal_surat']); ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($surat['perihal']); ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($surat['tgl_terima_formatted']); ?></td>
                        <td class="px-6 py-4">
                            <?php if ($surat['file_lampiran']): ?>
                                <a href="/uploads/<?php echo $surat['file_lampiran']; ?>" target="_blank" class="text-primary hover:underline">
                                    <i class="fas fa-file-alt"></i> Lihat
                                </a>
                            <?php else: ?>
                                <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div id="paginationContainerMasuk" class="mt-6">
        </div>
</div>

<?php require_once 'templates/footer.php'; ?>