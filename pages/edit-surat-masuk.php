<?php
// pages/edit-surat-masuk.php

// Keamanan: Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error_message'] = "Anda tidak memiliki izin untuk mengakses halaman ini.";
    header('Location: /surat-masuk');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header('Location: /surat-masuk');
    exit;
}

// Ambil data surat yang akan diedit
$stmt = $pdo->prepare("SELECT * FROM surat_masuk WHERE id = ?");
$stmt->execute([$id]);
$surat = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$surat) {
    $_SESSION['error_message'] = "Data surat tidak ditemukan.";
    header('Location: /surat-masuk');
    exit;
}


// --- LOGIKA UPDATE DATA (TERMASUK FILE) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_surat_masuk'])) {
    // 1. Ambil data teks dari form
    $agenda_klas = $_POST['agenda_klasifikasi'];
    $agenda_urut = $_POST['agenda_urut'];
    $nomor_surat_lengkap = $_POST['nomor_surat_lengkap'];
    $asal_surat = $_POST['asal_surat'];
    $sifat_surat = $_POST['sifat_surat'];
    $perihal = $_POST['perihal'];
    $keterangan = $_POST['keterangan'];
    $tgl_surat = $_POST['tanggal_surat'];
    $tgl_diterima = $_POST['tanggal_diterima'];
    $tahun = $_POST['tahun_penomoran'];

    $nomor_agenda_lengkap = sprintf("%s/%s/436.5/%s", $agenda_klas, $agenda_urut, $tahun);

    // 2. Cek apakah ada file baru yang diunggah
    $namaFileBaru = $surat['file_lampiran']; // Defaultnya adalah nama file lama
    if (isset($_FILES['file_lampiran']) && $_FILES['file_lampiran']['error'] === UPLOAD_ERR_OK) {
        // Asumsi fungsi handleFileUpload ada dari file surat-masuk.php atau di-include terpisah
        // Jika belum, pindahkan fungsinya ke sini juga
        $fileBaru = handleFileUpload('file_lampiran', 'surat_masuk');
        if ($fileBaru) {
            $pathFileLama = realpath(__DIR__ . '/../uploads/' . $surat['file_lampiran']);
            if ($surat['file_lampiran'] && $pathFileLama && file_exists($pathFileLama)) {
                unlink($pathFileLama);
            }
            $namaFileBaru = $fileBaru;
        }
    }

    // 3. Update data di database
    $stmt = $pdo->prepare(
        "UPDATE surat_masuk SET agenda_klasifikasi = ?, agenda_urut = ?, nomor_agenda_lengkap = ?, nomor_surat_lengkap = ?, tanggal_surat = ?, tanggal_diterima = ?, asal_surat = ?, sifat_surat = ?, perihal = ?, keterangan = ?, file_lampiran = ? WHERE id = ?"
    );
    $stmt->execute([$agenda_klas, $agenda_urut, $nomor_agenda_lengkap, $nomor_surat_lengkap, $tgl_surat, $tgl_diterima, $asal_surat, $sifat_surat, $perihal, $keterangan, $namaFileBaru, $id]);
    
    $_SESSION['success_message'] = "Data surat masuk berhasil diperbarui.";
    header("Location: /surat-masuk");
    exit;
}

$tahun_surat = date('Y', strtotime($surat['tanggal_diterima']));
$pageTitle = 'Edit Surat Masuk';
require_once 'templates/header.php';
?>

<div class="bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-xl p-6 border border-blue-100">
    <h3 class="text-2xl font-bold text-gray-800 mb-6 border-b border-blue-200 pb-3 flex items-center">
        <span class="bg-gradient-to-r from-primary to-secondary text-transparent bg-clip-text">Edit Surat Masuk</span>
        <i class="fas fa-edit ml-3 text-primary"></i>
    </h3>
    
    <form method="POST" action="/edit-surat-masuk?id=<?php echo $surat['id']; ?>" class="space-y-6" enctype="multipart/form-data">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Klasifikasi / No. Urut Agenda / Tahun</label>
                <div class="flex items-center space-x-2">
                    <input type="text" name="agenda_klasifikasi" value="<?php echo htmlspecialchars($surat['agenda_klasifikasi']); ?>" class="flex-1 px-4 py-3 rounded-xl border border-gray-300" placeholder="Klasifikasi">
                    <span class="text-gray-500 pt-2">/</span>
                    <input type="text" id="agenda_urut_edit" name="agenda_urut" value="<?php echo htmlspecialchars($surat['agenda_urut']); ?>" class="w-24 px-4 py-3 rounded-xl border border-gray-300 text-center" placeholder="No. Urut">
                    <span class="text-gray-500 pt-2">/</span>
                    <select id="tahun_penomoran_edit_masuk" name="tahun_penomoran" class="w-28 px-4 py-3 rounded-xl border border-gray-300 bg-white">
                        <?php
                        $tahun_sekarang = date('Y');
                        for ($i = $tahun_sekarang + 1; $i >= $tahun_sekarang - 5; $i--) {
                            $selected = ($i == $tahun_surat) ? 'selected' : '';
                            echo "<option value='$i' $selected>$i</option>";
                        }
                        ?>
                    </select>
                    <button type="button" id="checkAgendaBtnEdit" class="px-4 py-3 bg-indigo-100 text-indigo-600 rounded-xl hover:bg-indigo-200" title="Cek ketersediaan No. Urut Agenda">
                        <i class="fas fa-check"></i>
                    </button>
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
                        <a href="/uploads/<?php echo htmlspecialchars($surat['file_lampiran']); ?>" target="_blank" class="text-primary hover:underline">
                           <i class="fas fa-file-alt mr-1"></i><?php echo basename($surat['file_lampiran']); ?>
                        </a>
                    </div>
                <?php endif; ?>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed border-gray-300 rounded-xl hover:border-primary cursor-pointer relative group">
                    <input id="file-upload-masuk" name="file_lampiran" type="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    <div class="space-y-1 text-center">
                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 group-hover:text-primary"></i>
                        <div class="flex text-sm text-gray-600">
                            <span class="relative bg-white rounded-md font-medium text-primary hover:text-secondary">
                                Unggah file baru
                            </span>
                            <p class="pl-1">atau tarik dan lepas</p>
                        </div>
                        <p class="text-xs text-gray-500" id="file-name-masuk">Belum ada file dipilih</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-8 flex justify-end space-x-4">
             <a href="/surat-masuk" class="px-6 py-3 border border-gray-300 rounded-xl hover:bg-gray-50">Batal</a>
            <button type="submit" name="update_surat_masuk" class="px-6 py-3 bg-gradient-to-r from-primary to-secondary text-white rounded-xl shadow-md">
                <i class="fas fa-sync-alt mr-2"></i> Update Surat
            </button>
        </div>
    </form>
</div>

<?php require_once 'templates/footer.php'; ?>
