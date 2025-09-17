<?php
// pages/hapus-surat-keluar.php

require_once 'helpers.php';

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
$stmt = $pdo->prepare("SELECT * FROM surat_keluar WHERE id = ?");
$stmt->execute([$id]);
$surat = $stmt->fetch(PDO::FETCH_ASSOC);
$nomor_surat_untuk_log = $surat['nomor_surat_lengkap'] ?? "ID: {$id}";

if ($surat && !empty($surat['file_lampiran'])) {
    delete_file($surat['file_lampiran'], 'uploads');
}

// Hapus record dari database
$stmt_delete = $pdo->prepare("DELETE FROM surat_keluar WHERE id = ?");
$stmt_delete->execute([$id]);

// Catat aktivitas
log_activity($pdo, "Menghapus Surat Keluar '{$nomor_surat_untuk_log}'", ['sebelum' => $surat]);

$_SESSION['success_message'] = "Surat keluar berhasil dihapus.";
header('Location: /surat-keluar');
exit;
?>