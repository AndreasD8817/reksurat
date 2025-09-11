<?php
// pages/hapus-surat-keluar-dewan.php

// Keamanan: Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error_message'] = "Anda tidak memiliki izin untuk melakukan aksi ini.";
    header('Location: /surat-keluar-dewan');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: /surat-keluar-dewan');
    exit;
}

// Ambil nama file lampiran sebelum menghapus record dari tabel dewan
$stmt = $pdo->prepare("SELECT file_lampiran FROM surat_keluar_dewan WHERE id = ?");
$stmt->execute([$id]);
$surat = $stmt->fetch(PDO::FETCH_ASSOC);

if ($surat && $surat['file_lampiran']) {
    // Arahkan ke folder 'uploads-dewan'
    $filePath = 'uploads-dewan/' . $surat['file_lampiran'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

// Hapus record dari database dewan
$stmt_delete = $pdo->prepare("DELETE FROM surat_keluar_dewan WHERE id = ?");
$stmt_delete->execute([$id]);

$_SESSION['success_message'] = "Surat keluar dewan berhasil dihapus.";
header('Location: /surat-keluar-dewan');
exit;
?>
