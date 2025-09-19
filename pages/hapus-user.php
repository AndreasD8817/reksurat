<?php
// pages/hapus-user.php

// Keamanan: Pastikan hanya superadmin yang bisa melakukan aksi ini
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'superadmin') {
    $_SESSION['error_message'] = "Anda tidak memiliki izin untuk melakukan aksi ini.";
    header('Location: /users');
    exit;
}

$id = $_GET['id'] ?? null;

// Validasi ID
if (!$id || !is_numeric($id)) {
    $_SESSION['error_message'] = "ID user tidak valid.";
    header('Location: /users');
    exit;
}

// Pencegahan admin menghapus diri sendiri
if ($id == $_SESSION['user_id']) {
    $_SESSION['error_message'] = "Anda tidak dapat menghapus akun Anda sendiri.";
    header('Location: /users');
    exit;
}

// Hapus user dari database
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$id]);

$_SESSION['success_message'] = "User berhasil dihapus.";
header('Location: /users');
exit;
?>
