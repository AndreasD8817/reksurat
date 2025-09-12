<?php
// pages/hapus-disposisi-sekwan.php

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
$stmt = $pdo->prepare("SELECT file_lampiran FROM disposisi_sekwan WHERE id = ?");
$stmt->execute([$id]);
$disposisi = $stmt->fetch(PDO::FETCH_ASSOC);

if ($disposisi && $disposisi['file_lampiran']) {
    $filePath = 'uploads/disposisi_sekwan/' . $disposisi['file_lampiran'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

// Hapus record dari database
$stmt_delete = $pdo->prepare("DELETE FROM disposisi_sekwan WHERE id = ?");
$stmt_delete->execute([$id]);

$_SESSION['success_message'] = "Disposisi berhasil dibatalkan.";
header('Location: /disposisi-sekwan');
exit;
?>
