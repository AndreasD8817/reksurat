<?php
// pages/edit-surat-keluar.php

// Keamanan: Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error_message'] = "Anda tidak memiliki izin untuk mengakses halaman ini.";
    header('Location: /surat-keluar');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: /surat-keluar');
    exit;
}

// Ambil data surat yang akan diedit dari database
$stmt = $pdo->prepare("SELECT * FROM surat_keluar WHERE id = ?");
$stmt->execute([$id]);
$surat = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$surat) {
    $_SESSION['error_message'] = "Data surat tidak ditemukan.";
    header('Location: /surat-keluar');
    exit;
}


// --- LOGIKA UPDATE DATA (TERMASUK FILE) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_surat'])) {
    // Verifikasi CSRF Token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Sesi tidak valid atau telah kedaluwarsa. Silakan coba lagi.";
        header("Location: /edit-surat-keluar?id=" . $id);
        exit;
    }

    // 1. Ambil semua data teks dari form
    $kode_klas = $_POST['kode_klasifikasi'];
    $nomor_urut = $_POST['nomor_urut'];
    $tgl_surat = $_POST['tanggal_surat'];
    $tujuan = $_POST['tujuan'];
    $sifat_surat = $_POST['sifat_surat'];
    $perihal = $_POST['perihal'];
    $hub_surat = $_POST['hub_surat_no'];
    $konseptor = $_POST['konseptor'];
    $keterangan = $_POST['keterangan'];
    
    $tahun = date('Y', strtotime($tgl_surat));
    $nomor_lengkap = sprintf("%s/%s/436.5/%s", $kode_klas, $nomor_urut, $tahun);
    
    // 2. Cek apakah ada file baru yang diunggah
    $namaFileBaru = $surat['file_lampiran']; // Defaultnya adalah nama file lama
    if (isset($_FILES['file_lampiran']) && $_FILES['file_lampiran']['error'] === UPLOAD_ERR_OK) {
        // Ada file baru, proses unggah
        $fileBaru = handleFileUpload('file_lampiran', 'surat_keluar');
        if ($fileBaru) {
            // Jika unggah berhasil, hapus file lama (jika ada)
            $pathFileLama = realpath(__DIR__ . '/../uploads/' . $surat['file_lampiran']);
            if ($surat['file_lampiran'] && $pathFileLama && file_exists($pathFileLama)) {
                unlink($pathFileLama);
            }
            $namaFileBaru = $fileBaru; // Gunakan nama file baru
        }
    }

    // 3. Update data di database
    $stmt = $pdo->prepare("UPDATE surat_keluar SET kode_klasifikasi = ?, nomor_urut = ?, nomor_surat_lengkap = ?, tanggal_surat = ?, tujuan = ?, sifat_surat = ?, perihal = ?, hub_surat_no = ?, konseptor = ?, keterangan = ?, file_lampiran = ? WHERE id = ?");
    $stmt->execute([$kode_klas, $nomor_urut, $nomor_lengkap, $tgl_surat, $tujuan, $sifat_surat, $perihal, $hub_surat, $konseptor, $keterangan, $namaFileBaru, $id]);
    
    $_SESSION['success_message'] = "Data surat keluar berhasil diperbarui.";
    header("Location: /surat-keluar");
    exit;
}


$pageTitle = 'Edit Surat Keluar';
require_once 'templates/header.php';
?>

<div class="bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-xl p-6 animate-fade-in border border-blue-100">
    <h3 class="text-2xl font-bold text-gray-800 mb-6 border-b border-blue-200 pb-3 flex items-center">
        <span class="bg-gradient-to-r from-primary to-secondary text-transparent bg-clip-text">Edit Surat Keluar</span>
        <i class="fas fa-edit ml-3 text-primary"></i>
    </h3>
    
    <form method="POST" action="/edit-surat-keluar?id=<?php echo $surat['id']; ?>" class="space-y-6" enctype="multipart/form-data">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kode Klasifikasi & No. Urut</label>
                    <div class="flex items-center space-x-2">
                        <input type="text" name="kode_klasifikasi" value="<?php echo htmlspecialchars($surat['kode_klasifikasi']); ?>" class="flex-1 px-4 py-3 rounded-xl border border-gray-300" required />
                        <span class="text-gray-500 pt-2">/</span>
                        <input type="number" name="nomor_urut" value="<?php echo htmlspecialchars($surat['nomor_urut']); ?>" class="w-full px-4 py-3 rounded-xl border border-gray-300 text-center" required />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Surat</label>
                    <input type="date" name="tanggal_surat" value="<?php echo htmlspecialchars($surat['tanggal_surat']); ?>" class="w-full px-4 py-3 rounded-xl border border-gray-300" required />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tujuan</label>
                    <input type="text" name="tujuan" value="<?php echo htmlspecialchars($surat['tujuan']); ?>" class="w-full px-4 py-3 rounded-xl border border-gray-300" required />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Konseptor</label>
                    <input type="text" name="konseptor" value="<?php echo htmlspecialchars($surat['konseptor']); ?>" class="w-full px-4 py-3 rounded-xl border border-gray-300" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sifat Surat</label>
                    <div class="flex items-center space-x-6 pt-2">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="radio" name="sifat_surat" value="Biasa" class="form-radio h-4 w-4 text-primary" <?php echo ($surat['sifat_surat'] == 'Biasa') ? 'checked' : ''; ?>>
                            <span class="text-gray-700">Biasa</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="radio" name="sifat_surat" value="Penting" class="form-radio h-4 w-4 text-primary" <?php echo ($surat['sifat_surat'] == 'Penting') ? 'checked' : ''; ?>>
                            <span class="text-gray-700">Penting</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="radio" name="sifat_surat" value="Amat Segera" class="form-radio h-4 w-4 text-primary" <?php echo ($surat['sifat_surat'] == 'Amat Segera') ? 'checked' : ''; ?>>
                            <span class="text-gray-700">Amat Segera</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Perihal</label>
                    <textarea name="perihal" class="w-full px-4 py-3 rounded-xl border border-gray-300 h-32" required><?php echo htmlspecialchars($surat['perihal']); ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Hubungan Dgn Surat No</label>
                    <input type="text" name="hub_surat_no" value="<?php echo htmlspecialchars($surat['hub_surat_no']); ?>" class="w-full px-4 py-3 rounded-xl border border-gray-300" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                    <textarea name="keterangan" class="w-full px-4 py-3 rounded-xl border border-gray-300 h-32"><?php echo htmlspecialchars($surat['keterangan']); ?></textarea>
                </div>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">File Lampiran <span class="text-gray-400 font-normal">(Opsional: Ganti jika perlu)</span></label>
                <?php if ($surat['file_lampiran']): ?>
                    <div class="mb-2 text-sm">
                        File saat ini: 
                        <a href="/uploads/<?php echo htmlspecialchars($surat['file_lampiran']); ?>" target="_blank" class="text-primary hover:underline">
                            <i class="fas fa-file-alt mr-1"></i><?php echo basename($surat['file_lampiran']); ?>
                        </a>
                    </div>
                <?php endif; ?>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed border-gray-300 rounded-xl hover:border-primary cursor-pointer relative group">
                    <input id="file-upload-keluar" name="file_lampiran" type="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    <div class="space-y-1 text-center">
                         <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 group-hover:text-primary"></i>
                        <div class="flex text-sm text-gray-600">
                            <span class="relative bg-white rounded-md font-medium text-primary hover:text-secondary">
                                Unggah file baru
                            </span>
                            <p class="pl-1">atau tarik dan lepas</p>
                        </div>
                        <p class="text-xs text-gray-500" id="file-name-keluar">Belum ada file dipilih</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-4">
            <a href="/surat-keluar" class="px-6 py-3 border border-gray-300 rounded-xl hover:bg-gray-50">Batal</a>
            <button type="submit" name="update_surat" class="px-6 py-3 bg-gradient-to-r from-primary to-secondary text-white rounded-xl shadow-md">
                <i class="fas fa-sync-alt mr-2"></i> Update
            </button>
        </div>
    </form>
</div>

<?php require_once 'templates/footer.php'; ?>