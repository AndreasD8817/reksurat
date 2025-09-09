<?php
// pages/hapus-surat-keluar.php
// session_start();
// require_once '../config/database.php';

// Keamanan: Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error_message'] = "Anda tidak memiliki izin untuk melakukan aksi ini.";
    header('Location: /surat-keluar');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: /surat-keluar');
    exit;
}

// Ambil nama file lampiran sebelum menghapus record
$stmt = $pdo->prepare("SELECT file_lampiran FROM surat_keluar WHERE id = ?");
$stmt->execute([$id]);
$surat = $stmt->fetch(PDO::FETCH_ASSOC);

if ($surat && $surat['file_lampiran']) {
    // Path lengkap ke file. Menggunakan __DIR__ relatif terhadap index.php sekarang.
    $filePath = 'uploads/' . $surat['file_lampiran'];
    // Hapus file jika ada
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

// Hapus record dari database
$stmt_delete = $pdo->prepare("DELETE FROM surat_keluar WHERE id = ?");
$stmt_delete->execute([$id]);

$_SESSION['success_message'] = "Surat keluar berhasil dihapus.";
header('Location: /surat-keluar');
exit;
?>