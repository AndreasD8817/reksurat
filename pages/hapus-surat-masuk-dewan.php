<?php
// pages/hapus-surat-masuk-dewan.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error_message'] = "Anda tidak memiliki izin untuk melakukan aksi ini.";
    header('Location: /surat-masuk-dewan');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: /surat-masuk-dewan');
    exit;
}

$stmt = $pdo->prepare("SELECT file_lampiran FROM surat_masuk_dewan WHERE id = ?");
$stmt->execute([$id]);
$surat = $stmt->fetch(PDO::FETCH_ASSOC);

if ($surat && $surat['file_lampiran']) {
    // Arahkan ke folder 'uploads-dewan'
    $filePath = 'uploads-dewan/' . $surat['file_lampiran'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

$stmt_delete = $pdo->prepare("DELETE FROM surat_masuk_dewan WHERE id = ?");
$stmt_delete->execute([$id]);

$_SESSION['success_message'] = "Surat masuk dewan berhasil dihapus.";
header('Location: /surat-masuk-dewan');
exit;
?>
