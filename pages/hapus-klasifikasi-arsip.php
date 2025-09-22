<?php
// pages/hapus-klasifikasi-arsip.php

require_once 'helpers.php';

// Keamanan: Pastikan hanya superadmin yang bisa mengakses
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'superadmin') {
    $_SESSION['error_message'] = "Anda tidak memiliki izin untuk melakukan tindakan ini.";
    header('Location: /klasifikasi-arsip');
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    $_SESSION['error_message'] = "ID tidak valid.";
    header('Location: /klasifikasi-arsip');
    exit;
}

// Cek apakah data ada sebelum dihapus
$stmt = $pdo->prepare("SELECT id FROM klasifikasi_arsip WHERE id = ?");
$stmt->execute([$id]);
$klasifikasi = $stmt->fetch();

if ($klasifikasi) {
    $delete_stmt = $pdo->prepare("DELETE FROM klasifikasi_arsip WHERE id = ?");
    $delete_stmt->execute([$id]);
    $_SESSION['success_message'] = "Data klasifikasi berhasil dihapus.";
} else {
    $_SESSION['error_message'] = "Data klasifikasi tidak ditemukan.";
}

header('Location: /klasifikasi-arsip');
exit;
?>
