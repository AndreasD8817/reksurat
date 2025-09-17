<?php
// pages/helpers.php

/**
 * Mencatat aktivitas pengguna ke dalam tabel log_user.
 *
 * @param PDO $pdo Objek koneksi database PDO.
 * @param string $kegiatan Deskripsi kegiatan yang dilakukan.
 * @return void
 */
function log_activity(PDO $pdo, string $kegiatan, ?array $detail = null): void {
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $stmt = $pdo->prepare("INSERT INTO log_user (user_id, kegiatan, detail) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $kegiatan, $detail ? json_encode($detail) : null]);
    }
}

/**
 * Menangani unggahan file dengan aman.
 *
 * @param string $fileInputName Nama dari input file di form.
 * @param string $uploadDir Direktori utama untuk unggahan ('uploads' atau 'uploads-dewan').
 * @param string $subDirectory Sub-direktori spesifik untuk modul.
 * @return string|null Path file yang disimpan atau null jika gagal.
 */
function handle_file_upload(string $fileInputName, string $uploadDir, string $subDirectory): ?string {
    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES[$fileInputName];
        $fileName = time() . '_' . basename($file['name']);
        $mainUploadDir = realpath(dirname(__FILE__) . "/../{$uploadDir}");
        $targetDir = $mainUploadDir . DIRECTORY_SEPARATOR . $subDirectory;
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        $maxSize = 5 * 1024 * 1024; // 5 MB
        if (!in_array($file['type'], $allowedTypes)) { $_SESSION['error_message'] = 'Tipe file tidak valid. Hanya PDF, JPG, dan PNG.'; return null; }
        if ($file['size'] > $maxSize) { $_SESSION['error_message'] = 'Ukuran file terlalu besar. Maksimal 5 MB.'; return null; }
        if (move_uploaded_file($file['tmp_name'], $targetPath)) { return "{$subDirectory}/{$fileName}"; }
    }
    return null;
}