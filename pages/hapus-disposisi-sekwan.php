<?php
// pages/hapus-disposisi-sekwan.php

require_once 'helpers.php';

// Keamanan: Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error_message'] = "Anda tidak memiliki izin untuk melakukan aksi ini.";
    header('Location: /disposisi-sekwan');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: /disposisi-sekwan');
    exit;
}

// Ambil nama file lampiran sebelum menghapus record
$stmt = $pdo->prepare(
    "SELECT ds.*, sm.nomor_agenda_lengkap 
     FROM disposisi_sekwan ds 
     JOIN surat_masuk sm ON ds.surat_masuk_id = sm.id 
     WHERE ds.id = ?"
);
$stmt->execute([$id]);
$disposisi = $stmt->fetch(PDO::FETCH_ASSOC);
$nomor_agenda_untuk_log = $disposisi['nomor_agenda_lengkap'] ?? "ID Disposisi: {$id}";


if ($disposisi && !empty($disposisi['file_lampiran'])) {
    $filePath = 'uploads/disposisi_sekwan/' . $disposisi['file_lampiran'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

// Hapus record dari database
$stmt_delete = $pdo->prepare("DELETE FROM disposisi_sekwan WHERE id = ?");
$stmt_delete->execute([$id]);

// Catat aktivitas
log_activity($pdo, "Membatalkan disposisi untuk surat '{$nomor_agenda_untuk_log}'", ['sebelum' => $disposisi]);

$_SESSION['success_message'] = "Disposisi berhasil dibatalkan.";
header('Location: /disposisi-sekwan');
exit;
?>
