<?php
// pages/hapus-surat-keluar-dewan.php

require_once 'helpers.php';

// Keamanan: Pastikan hanya superadmin dan admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['superadmin', 'admin'])) {
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
$stmt = $pdo->prepare("SELECT * FROM surat_keluar_dewan WHERE id = ?");
$stmt->execute([$id]);
$surat = $stmt->fetch(PDO::FETCH_ASSOC);
$nomor_surat_untuk_log = $surat['nomor_surat_lengkap'] ?? "ID: {$id}";

if ($surat && !empty($surat['file_lampiran'])) {
    delete_file($surat['file_lampiran'], 'uploads-dewan');
}

// Hapus record dari database dewan
$stmt_delete = $pdo->prepare("DELETE FROM surat_keluar_dewan WHERE id = ?");
$stmt_delete->execute([$id]);

// Catat aktivitas
log_activity($pdo, "Menghapus Surat Keluar Dewan '{$nomor_surat_untuk_log}'", ['sebelum' => $surat]);

$_SESSION['success_message'] = "Surat keluar dewan berhasil dihapus.";
header('Location: /surat-keluar-dewan');
exit;
?>
