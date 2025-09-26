<?php
// pages/edit-disposisi-dewan.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

// Keamanan: Pastikan hanya admin/superadmin yang bisa mengakses
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'superadmin'])) {
    $_SESSION['error_message'] = "Anda tidak memiliki izin untuk mengakses halaman ini.";
    header('Location: /disposisi-dewan');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: /disposisi-dewan');
    exit;
}

// Ambil data disposisi yang akan diedit
$stmt = $pdo->prepare(
    "SELECT dd.*, smd.nomor_agenda_lengkap, smd.perihal 
     FROM disposisi_dewan dd
     JOIN surat_masuk_dewan smd ON dd.surat_masuk_id = smd.id
     WHERE dd.id = ?"
);
$stmt->execute([$id]);
$disposisi = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$disposisi) {
    $_SESSION['error_message'] = "Data disposisi dewan tidak ditemukan.";
    header('Location: /disposisi-dewan');
    exit;
}

// Logika untuk memproses update data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_disposisi'])) {
    $nama_pegawai = $_POST['nama_pegawai'];
    $catatan = $_POST['catatan_disposisi'];

    $namaFileBaru = $disposisi['file_lampiran']; // Defaultnya adalah nama file lama
    if (isset($_FILES['file_lampiran']) && $_FILES['file_lampiran']['error'] === UPLOAD_ERR_OK) {
        // Ada file baru, proses unggah
        $fileBaru = handle_file_upload('file_lampiran', 'uploads-dewan', 'disposisi_dewan');
        if ($fileBaru) {
            // Jika unggah berhasil, hapus file lama (jika ada)
            delete_file($disposisi['file_lampiran'], 'uploads-dewan');
            $namaFileBaru = $fileBaru; // Gunakan nama file baru
        }
    }

    // Siapkan data baru untuk log
    $data_baru = [
        'nama_pegawai' => $nama_pegawai,
        'catatan_disposisi' => $catatan,
        'file_lampiran' => $namaFileBaru
    ];

    // Ambil data lama sebelum diupdate untuk perbandingan log
    $data_lama = array_intersect_key($disposisi, $data_baru);
    $perubahan = array_diff_assoc($data_baru, $data_lama);

    // Update data di database
    $stmt = $pdo->prepare(
        "UPDATE disposisi_dewan SET nama_pegawai = ?, catatan_disposisi = ?, file_lampiran = ? WHERE id = ?"
    );
    $stmt->execute([$nama_pegawai, $catatan, $namaFileBaru, $id]);
    
    // Catat aktivitas jika ada perubahan
    if (!empty($perubahan)) {
        log_activity($pdo, "Mengedit disposisi untuk surat dewan '{$disposisi['nomor_agenda_lengkap']}'", ['sebelum' => $data_lama, 'sesudah' => $data_baru, 'perubahan' => $perubahan]);
    }

    $_SESSION['success_message'] = "Data disposisi dewan berhasil diperbarui.";
    header("Location: /disposisi-dewan");
    exit;
}

$pageTitle = 'Edit Disposisi Surat Dewan';
require_once __DIR__ . '/../templates/header.php';
?>

<div class="bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-xl p-6 border border-blue-100">
    <h3 class="text-2xl font-bold text-gray-800 mb-6 border-b border-blue-200 pb-3 flex items-center">
        <span class="bg-gradient-to-r from-primary to-secondary text-transparent bg-clip-text">Edit Disposisi Dewan</span>
        <i class="fas fa-edit ml-3 text-primary"></i>
    </h3>
    
    <form method="POST" action="/edit-disposisi-dewan?id=<?= $disposisi['id']; ?>" class="space-y-6" enctype="multipart/form-data">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Agenda Surat</label>
                <input type="text" value="<?= htmlspecialchars($disposisi['nomor_agenda_lengkap']); ?>" class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-100" readonly>
            </div>
            <div>
                <label for="nama_pegawai" class="block text-sm font-medium text-gray-700 mb-2">Diteruskan Kepada</label>
                <input type="text" id="nama_pegawai" name="nama_pegawai" value="<?= htmlspecialchars($disposisi['nama_pegawai']); ?>" class="w-full px-4 py-3 rounded-xl border border-gray-300" required>
            </div>
            <div class="md:col-span-2">
                <label for="catatan_disposisi" class="block text-sm font-medium text-gray-700 mb-2">Isi/Catatan Disposisi</label>
                <textarea id="catatan_disposisi" name="catatan_disposisi" rows="4" class="w-full px-4 py-3 rounded-xl border border-gray-300"><?= htmlspecialchars($disposisi['catatan_disposisi']); ?></textarea>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">File Lampiran <span class="text-gray-400 font-normal">(Opsional: Ganti jika perlu)</span></label>
                <?php if ($disposisi['file_lampiran']): ?>
                    <div class="mb-2 text-sm">
                        File saat ini: 
                        <a href="/uploads-dewan/<?= htmlspecialchars($disposisi['file_lampiran']); ?>" target="_blank" class="text-primary hover:underline">
                           <i class="fas fa-file-alt mr-1"></i><?= basename($disposisi['file_lampiran']); ?>
                        </a>
                    </div>
                <?php endif; ?>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed border-gray-300 rounded-xl hover:border-primary cursor-pointer relative group">
                    <input id="file-upload-disposisi" name="file_lampiran" type="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    <div class="space-y-1 text-center">
                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 group-hover:text-primary"></i>
                        <div class="flex text-sm text-gray-600">
                            <span class="relative bg-white rounded-md font-medium text-primary hover:text-secondary">Unggah file baru</span>
                            <p class="pl-1">atau tarik dan lepas</p>
                        </div>
                        <p class="text-xs text-gray-500" id="file-name-disposisi">Belum ada file dipilih</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 flex flex-col md:flex-row justify-between items-center">

            <div class="flex space-x-4 mt-4 md:mt-0">
                <a href="/disposisi-dewan" class="px-6 py-3 border border-gray-300 rounded-xl hover:bg-gray-50">Batal</a>
                <button type="submit" name="update_disposisi" class="px-6 py-3 bg-gradient-to-r from-primary to-secondary text-white rounded-xl shadow-md">
                    <i class="fas fa-sync-alt mr-2"></i> Update Disposisi
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('file-upload-disposisi');
    const fileNameDisplay = document.getElementById('file-name-disposisi');
    fileInput.addEventListener('change', function() {
        fileNameDisplay.textContent = this.files[0] ? this.files[0].name : 'Belum ada file dipilih';
    });
});
</script>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>