<?php
// pages/edit-surat-keluar.php

//require_once '../config/database.php';

// Keamanan: Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error_message'] = "Anda tidak memiliki izin untuk mengakses halaman ini.";
    header('Location: /surat-keluar');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: /surat-keluar');
    exit;
}

// Logika untuk UPDATE data saat form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_surat'])) {
    // Ambil semua data dari form
    $kode_klas = $_POST['kode_klasifikasi'];
    $nomor_urut = $_POST['nomor_urut'];
    $tgl_surat = $_POST['tanggal_surat'];
    $tujuan = $_POST['tujuan'];
    $perihal = $_POST['perihal'];
    $hub_surat = $_POST['hub_surat_no'];
    $konseptor = $_POST['konseptor'];
    $keterangan = $_POST['keterangan'];
    
    // Generate nomor surat lengkap baru
    $tahun = date('Y', strtotime($tgl_surat));
    $nomor_lengkap = sprintf("%s/%s/436.5/%s", $kode_klas, $nomor_urut, $tahun);

    // Update data di database
    $stmt = $pdo->prepare("UPDATE surat_keluar SET kode_klasifikasi = ?, nomor_urut = ?, nomor_surat_lengkap = ?, tanggal_surat = ?, tujuan = ?, perihal = ?, hub_surat_no = ?, konseptor = ?, keterangan = ? WHERE id = ?");
    $stmt->execute([$kode_klas, $nomor_urut, $nomor_lengkap, $tgl_surat, $tujuan, $perihal, $hub_surat, $konseptor, $keterangan, $id]);
    
    $_SESSION['success_message'] = "Data surat keluar berhasil diperbarui.";
    header("Location: /surat-keluar");
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

$pageTitle = 'Edit Surat Keluar';
require_once 'templates/header.php';
?>

<div class="bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-xl p-6 animate-fade-in border border-blue-100">
    <h3 class="text-2xl font-bold text-gray-800 mb-6 border-b border-blue-200 pb-3 flex items-center">
        <span class="bg-gradient-to-r from-primary to-secondary text-transparent bg-clip-text">Edit Surat Keluar</span>
        <i class="fas fa-edit ml-3 text-primary"></i>
    </h3>
    <form method="POST" action="/edit-surat-keluar?id=<?php echo $surat['id']; ?>" class="space-y-6">
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