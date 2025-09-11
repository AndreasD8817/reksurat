<?php
// pages/edit-surat-masuk-dewan.php

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error_message'] = "Anda tidak memiliki izin untuk mengakses halaman ini.";
    header('Location: /surat-masuk-dewan');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: /surat-masuk-dewan');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM surat_masuk_dewan WHERE id = ?");
$stmt->execute([$id]);
$surat = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$surat) {
    $_SESSION['error_message'] = "Data surat tidak ditemukan.";
    header('Location: /surat-masuk-dewan');
    exit;
}

function handleFileUpload($fileInputName, $subDirectory) {
    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES[$fileInputName];
        $fileName = time() . '_' . basename($file['name']);
        $mainUploadDir = realpath(dirname(__FILE__) . '/../uploads-dewan');
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_surat_masuk_dewan'])) {
    $agenda_klas = $_POST['agenda_klasifikasi'];
    $agenda_urut = $_POST['agenda_urut'];
    $nomor_surat_lengkap = $_POST['nomor_surat_lengkap'];
    $asal_surat = $_POST['asal_surat'];
    $sifat_surat = $_POST['sifat_surat'];
    $perihal = $_POST['perihal'];
    $keterangan = $_POST['keterangan'];
    $tgl_surat = $_POST['tanggal_surat'];
    $tgl_diterima = $_POST['tanggal_diterima'];

    $tahun = date('Y', strtotime($tgl_diterima));
    $nomor_agenda_lengkap = sprintf("%s/%s/436.5/%s", $agenda_klas, $agenda_urut, $tahun);

    $namaFileBaru = $surat['file_lampiran'];
    if (isset($_FILES['file_lampiran']) && $_FILES['file_lampiran']['error'] === UPLOAD_ERR_OK) {
        $fileBaru = handleFileUpload('file_lampiran', 'surat_masuk_dewan');
        if ($fileBaru) {
            if ($surat['file_lampiran'] && file_exists('uploads-dewan/' . $surat['file_lampiran'])) {
                unlink('uploads-dewan/' . $surat['file_lampiran']);
            }
            $namaFileBaru = $fileBaru;
        }
    }

    $stmt = $pdo->prepare(
        "UPDATE surat_masuk_dewan SET agenda_klasifikasi = ?, agenda_urut = ?, nomor_agenda_lengkap = ?, nomor_surat_lengkap = ?, tanggal_surat = ?, tanggal_diterima = ?, asal_surat = ?, sifat_surat = ?, perihal = ?, keterangan = ?, file_lampiran = ? WHERE id = ?"
    );
    $stmt->execute([$agenda_klas, $agenda_urut, $nomor_agenda_lengkap, $nomor_surat_lengkap, $tgl_surat, $tgl_diterima, $asal_surat, $sifat_surat, $perihal, $keterangan, $namaFileBaru, $id]);
    
    $_SESSION['success_message'] = "Data surat masuk dewan berhasil diperbarui.";
    header("Location: /surat-masuk-dewan");
    exit;
}

$pageTitle = 'Edit Surat Masuk Dewan';
require_once 'templates/header.php';
?>

<div class="bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-xl p-6 animate-fade-in border border-blue-100">
    <h3 class="text-2xl font-bold text-gray-800 mb-6 border-b border-blue-200 pb-3 flex items-center">
        <span class="bg-gradient-to-r from-primary to-secondary text-transparent bg-clip-text">Edit Surat Masuk Dewan</span>
        <i class="fas fa-edit ml-3 text-primary"></i>
    </h3>
    
    <form method="POST" action="/edit-surat-masuk-dewan?id=<?php echo $surat['id']; ?>" class="space-y-6" enctype="multipart/form-data">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Agenda</label>
                <div class="flex items-center space-x-2">
                    <input type="text" name="agenda_klasifikasi" value="<?php echo htmlspecialchars($surat['agenda_klasifikasi']); ?>" class="w-full px-4 py-3 rounded-xl border border-gray-300">
                    <span class="text-gray-500 pt-2">/</span>
                    <input type="text" name="agenda_urut" value="<?php echo htmlspecialchars($surat['agenda_urut']); ?>" class="w-full px-4 py-3 rounded-xl border border-gray-300">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Surat</label>
                <input type="text" name="nomor_surat_lengkap" value="<?php echo htmlspecialchars($surat['nomor_surat_lengkap']); ?>" class="w-full px-4 py-3 rounded-xl border border-gray-300" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Asal Surat</label>
                <input type="text" name="asal_surat" value="<?php echo htmlspecialchars($surat['asal_surat']); ?>" class="w-full px-4 py-3 rounded-xl border border-gray-300" required />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sifat Surat</label>
                <div class="flex items-center space-x-6 pt-2">
                    <label class="flex items-center space-x-2 cursor-pointer"><input type="radio" name="sifat_surat" value="Biasa" class="form-radio h-4 w-4 text-primary" <?php echo ($surat['sifat_surat'] == 'Biasa') ? 'checked' : ''; ?>><span class="text-gray-700">Biasa</span></label>
                    <label class="flex items-center space-x-2 cursor-pointer"><input type="radio" name="sifat_surat" value="Penting" class="form-radio h-4 w-4 text-primary" <?php echo ($surat['sifat_surat'] == 'Penting') ? 'checked' : ''; ?>><span class="text-gray-700">Penting</span></label>
                    <label class="flex items-center space-x-2 cursor-pointer"><input type="radio" name="sifat_surat" value="Amat Segera" class="form-radio h-4 w-4 text-primary" <?php echo ($surat['sifat_surat'] == 'Amat Segera') ? 'checked' : ''; ?>><span class="text-gray-700">Amat Segera</span></label>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Surat</label>
                <input type="date" name="tanggal_surat" value="<?php echo htmlspecialchars($surat['tanggal_surat']); ?>" class="w-full px-4 py-3 rounded-xl border border-gray-300" required />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Diterima</label>
                <input type="date" name="tanggal_diterima" value="<?php echo htmlspecialchars($surat['tanggal_diterima']); ?>" class="w-full px-4 py-3 rounded-xl border border-gray-300" required />
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Perihal</label>
                <textarea name="perihal" class="w-full px-4 py-3 rounded-xl border border-gray-300 h-24" required><?php echo htmlspecialchars($surat['perihal']); ?></textarea>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                <textarea name="keterangan" class="w-full px-4 py-3 rounded-xl border border-gray-300 h-24"><?php echo htmlspecialchars($surat['keterangan']); ?></textarea>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">File Lampiran <span class="text-gray-400 font-normal">(Opsional: Ganti jika perlu)</span></label>
                <?php if ($surat['file_lampiran']): ?>
                    <div class="mb-2 text-sm">
                        File saat ini: 
                        <a href="/uploads-dewan/<?php echo htmlspecialchars($surat['file_lampiran']); ?>" target="_blank" class="text-primary hover:underline">
                           <i class="fas fa-file-alt mr-1"></i><?php echo basename($surat['file_lampiran']); ?>
                        </a>
                    </div>
                <?php endif; ?>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed border-gray-300 rounded-xl hover:border-primary cursor-pointer relative group">
                    <input id="file-upload-masuk-dewan" name="file_lampiran" type="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    <div class="space-y-1 text-center">
                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 group-hover:text-primary"></i>
                        <div class="flex text-sm text-gray-600">
                            <span class="relative bg-white rounded-md font-medium text-primary hover:text-secondary">Unggah file baru</span>
                            <p class="pl-1">atau tarik dan lepas</p>
                        </div>
                        <p class="text-xs text-gray-500" id="file-name-masuk-dewan">Belum ada file dipilih</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-8 flex justify-end space-x-4">
             <a href="/surat-masuk-dewan" class="px-6 py-3 border border-gray-300 rounded-xl hover:bg-gray-50">Batal</a>
            <button type="submit" name="update_surat_masuk_dewan" class="px-6 py-3 bg-gradient-to-r from-primary to-secondary text-white rounded-xl shadow-md">
                <i class="fas fa-sync-alt mr-2"></i> Update Surat
            </button>
        </div>
    </form>
</div>

<?php require_once 'templates/footer.php'; ?>
