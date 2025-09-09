<?php
// pages/hapus-surat-masuk.php
// session_start();
// require_once '../config/database.php';

// Keamanan: Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error_message'] = "Anda tidak memiliki izin untuk melakukan aksi ini.";
    header('Location: /surat-masuk');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: /surat-masuk');
    exit;
}

// Ambil nama file lampiran sebelum menghapus record
$stmt = $pdo->prepare("SELECT file_lampiran FROM surat_masuk WHERE id = ?");
$stmt->execute([$id]);
$surat = $stmt->fetch(PDO::FETCH_ASSOC);

if ($surat && $surat['file_lampiran']) {
    // Path lengkap ke file
    $filePath = 'uploads/' . $surat['file_lampiran'];
    // Hapus file jika ada
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}


// Hapus record dari database
$stmt_delete = $pdo->prepare("DELETE FROM surat_masuk WHERE id = ?");
$stmt_delete->execute([$id]);

$_SESSION['success_message'] = "Surat masuk berhasil dihapus.";
header('Location: /surat-masuk');
exit;
?>