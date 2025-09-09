<?php
// pages/edit-surat-masuk.php
// session_start();
// require_once '../config/database.php';

// Keamanan: Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error_message'] = "Anda tidak memiliki izin untuk mengakses halaman ini.";
    header('Location: /surat-masuk');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: /surat-masuk');
    exit;
}

// Logika untuk UPDATE data saat form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_surat_masuk'])) {
    $agenda_klas = $_POST['agenda_klasifikasi'];
    $agenda_urut = $_POST['agenda_urut'];
    $nomor_surat_lengkap = $_POST['nomor_surat_lengkap'];
    $asal_surat = $_POST['asal_surat'];
    $perihal = $_POST['perihal'];
    $keterangan = $_POST['keterangan'];
    $tgl_surat = $_POST['tanggal_surat'];
    $tgl_diterima = $_POST['tanggal_diterima'];

    $tahun = date('Y', strtotime($tgl_diterima));
    $nomor_agenda_lengkap = sprintf("%s/%s/436.5/%s", $agenda_klas, $agenda_urut, $tahun);

    $stmt = $pdo->prepare(
        "UPDATE surat_masuk SET agenda_klasifikasi = ?, agenda_urut = ?, nomor_agenda_lengkap = ?, nomor_surat_lengkap = ?, tanggal_surat = ?, tanggal_diterima = ?, asal_surat = ?, perihal = ?, keterangan = ? WHERE id = ?"
    );
    $stmt->execute([$agenda_klas, $agenda_urut, $nomor_agenda_lengkap, $nomor_surat_lengkap, $tgl_surat, $tgl_diterima, $asal_surat, $perihal, $keterangan, $id]);
    
    $_SESSION['success_message'] = "Data surat masuk berhasil diperbarui.";
    header("Location: /surat-masuk");
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

$pageTitle = 'Edit Surat Masuk';
require_once 'templates/header.php';
?>

<div class="bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-xl p-6 animate-fade-in border border-blue-100">
    <h3 class="text-2xl font-bold text-gray-800 mb-6 border-b border-blue-200 pb-3 flex items-center">
        <span class="bg-gradient-to-r from-primary to-secondary text-transparent bg-clip-text">Edit Surat Masuk</span>
        <i class="fas fa-edit ml-3 text-primary"></i>
    </h3>
    <form method="POST" action="/edit-surat-masuk?id=<?php echo $surat['id']; ?>" class="space-y-6">
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
                <label class="block text-sm font-medium text-gray-700 mb-2">Perihal</label>
                <textarea name="perihal" class="w-full px-4 py-3 rounded-xl border border-gray-300 h-32" required><?php echo htmlspecialchars($surat['perihal']); ?></textarea>
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
                <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                <textarea name="keterangan" class="w-full px-4 py-3 rounded-xl border border-gray-300 h-32"><?php echo htmlspecialchars($surat['keterangan']); ?></textarea>
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