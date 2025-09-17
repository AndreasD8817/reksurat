<?php
// pages/hapus-surat-masuk-dewan.php

require_once 'helpers.php';

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

$stmt = $pdo->prepare("SELECT * FROM surat_masuk_dewan WHERE id = ?");
$stmt->execute([$id]);
$surat = $stmt->fetch(PDO::FETCH_ASSOC);
$nomor_agenda_untuk_log = $surat['nomor_agenda_lengkap'] ?? "ID: {$id}";

if ($surat && !empty($surat['file_lampiran'])) {
    // Arahkan ke folder 'uploads-dewan'
    $filePath = 'uploads-dewan/' . $surat['file_lampiran'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

$stmt_delete = $pdo->prepare("DELETE FROM surat_masuk_dewan WHERE id = ?");
$stmt_delete->execute([$id]);

// Catat aktivitas
log_activity($pdo, "Menghapus Surat Masuk Dewan '{$nomor_agenda_untuk_log}'", ['sebelum' => $surat]);

$_SESSION['success_message'] = "Surat masuk dewan berhasil dihapus.";
header('Location: /surat-masuk-dewan');
exit;
?>
