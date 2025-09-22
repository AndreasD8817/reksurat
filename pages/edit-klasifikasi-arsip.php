<?php
// pages/edit-klasifikasi-arsip.php

require_once 'helpers.php';

// Keamanan: Pastikan hanya superadmin yang bisa mengakses
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'superadmin') {
    $_SESSION['error_message'] = "Anda tidak memiliki izin untuk mengakses halaman ini.";
    header('Location: /dashboard');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header('Location: /klasifikasi-arsip');
    exit;
}

// Ambil data yang akan diedit
$stmt = $pdo->prepare("SELECT * FROM klasifikasi_arsip WHERE id = ?");
$stmt->execute([$id]);
$klasifikasi = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$klasifikasi) {
    $_SESSION['error_message'] = "Data klasifikasi tidak ditemukan.";
    header('Location: /klasifikasi-arsip');
    exit;
}

// Logika untuk update data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_klasifikasi'])) {
    $kode = $_POST['kode'];
    $deskripsi = $_POST['deskripsi'];

    if (empty($kode) || empty($deskripsi)) {
        $_SESSION['error_message'] = "Kode dan Deskripsi tidak boleh kosong.";
    } else {
        $stmt = $pdo->prepare("UPDATE klasifikasi_arsip SET kode = ?, deskripsi = ? WHERE id = ?");
        $stmt->execute([$kode, $deskripsi, $id]);
        $_SESSION['success_message'] = "Data klasifikasi berhasil diperbarui.";
        header("Location: /klasifikasi-arsip");
        exit;
    }
    // Redirect untuk menampilkan pesan error jika validasi gagal
    header("Location: /edit-klasifikasi-arsip?id=" . $id);
    exit;
}

$pageTitle = 'Edit Klasifikasi Arsip';
require_once 'templates/header.php';
?>

<div class="pb-10 md:pb-6">
    <div class="bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-xl p-6 border border-blue-100">
        <h3 class="text-2xl font-bold text-gray-800 mb-6 border-b border-blue-200 pb-3 flex items-center">
            <span class="bg-gradient-to-r from-primary to-secondary text-transparent bg-clip-text">Edit Klasifikasi Arsip</span>
            <i class="fas fa-edit ml-3 text-primary"></i>
        </h3>
        
        <form method="POST" action="/edit-klasifikasi-arsip?id=<?php echo $klasifikasi['id']; ?>" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="kode" class="block text-sm font-medium text-gray-700 mb-2">Kode Klasifikasi</label>
                    <input type="text" id="kode" name="kode" value="<?php echo htmlspecialchars($klasifikasi['kode']); ?>" class="w-full px-4 py-3 rounded-xl border border-gray-300" required>
                </div>
                <div class="md:col-span-2">
                    <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                    <input type="text" id="deskripsi" name="deskripsi" value="<?php echo htmlspecialchars($klasifikasi['deskripsi']); ?>" class="w-full px-4 py-3 rounded-xl border border-gray-300" required>
                </div>
            </div>

            <div class="mt-8 flex justify-end space-x-4">
                <a href="/klasifikasi-arsip" class="px-6 py-3 border border-gray-300 rounded-xl hover:bg-gray-50">Batal</a>
                <button type="submit" name="update_klasifikasi" class="px-6 py-3 bg-gradient-to-r from-primary to-secondary text-white rounded-xl shadow-md">
                    <i class="fas fa-sync-alt mr-2"></i> Update
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'templates/footer.php'; ?>