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

/**
 * Menghapus file lampiran dengan aman dari direktori yang ditentukan.
 *
 * @param string|null $filePath Path file relatif dari dalam folder uploads (misal: 'surat_masuk/namafile.pdf').
 * @param string $uploadDir Direktori utama unggahan ('uploads' atau 'uploads-dewan').
 * @return bool True jika berhasil dihapus atau file tidak ada, false jika gagal.
 */
function delete_file(?string $filePath, string $uploadDir): bool
{
    if (empty($filePath)) {
        return true; // Tidak ada file untuk dihapus, anggap berhasil.
    }

    $absolutePath = realpath(dirname(__FILE__) . "/../{$uploadDir}/" . $filePath);

    // Cek jika path valid dan file ada, lalu hapus.
    return ($absolutePath && file_exists($absolutePath)) ? unlink($absolutePath) : true;
}

/**
 * Membuat komponen pagination dengan gaya Tailwind CSS.
 *
 * @param int $total_pages Jumlah total halaman.
 * @param int $current_page Halaman saat ini.
 * @param string $base_url URL dasar untuk link pagination.
 * @param array $query_params Parameter query tambahan untuk disertakan dalam link.
 * @return void
 */
function generate_pagination(int $total_pages, int $current_page, string $base_url, array $query_params = []): void
{
    if ($total_pages <= 1) {
        return;
    }

    // Hapus parameter 'page' yang mungkin ada untuk menghindari duplikasi
    unset($query_params['page']);

    // Bangun query string dari parameter yang ada
    $query_string = http_build_query($query_params);

    echo '<nav class="flex items-center justify-between" aria-label="Pagination">';

    // Tombol Previous
    $prev_page = $current_page - 1;
    $prev_link = $base_url . '?page=' . $prev_page . ($query_string ? '&' . $query_string : '');
    $prev_disabled = $current_page <= 1 ? 'disabled' : '';
    echo '<a href="' . ($current_page > 1 ? $prev_link : '#') . '" class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 ' . ($prev_disabled ? 'opacity-50 cursor-not-allowed' : '') . '" ' . $prev_disabled . '>Sebelumnya</a>';

    // Info Halaman
    echo '<div class="hidden md:block">';
    echo '<p class="text-sm text-gray-700">Halaman <span class="font-medium">' . $current_page . '</span> dari <span class="font-medium">' . $total_pages . '</span></p>';
    echo '</div>';

    // Tombol Next
    $next_page = $current_page + 1;
    $next_link = $base_url . '?page=' . $next_page . ($query_string ? '&' . $query_string : '');
    $next_disabled = $current_page >= $total_pages ? 'disabled' : '';
    echo '<a href="' . ($current_page < $total_pages ? $next_link : '#') . '" class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 ' . ($next_disabled ? 'opacity-50 cursor-not-allowed' : '') . '" ' . $next_disabled . '>Selanjutnya</a>';

    echo '</nav>';
}