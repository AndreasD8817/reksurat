<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

// Keamanan: Pastikan hanya admin/superadmin yang bisa mengakses
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['superadmin', 'admin'])) {
    $_SESSION['error_message'] = "Anda tidak memiliki izin untuk melakukan aksi ini.";
    header('Location: /disposisi-dewan');
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['error_message'] = "ID Disposisi tidak valid.";
    header('Location: /disposisi-dewan');
    exit;
}

try {
    // Ambil data sebelum dihapus untuk logging dan menghapus file
    $stmt_select = $pdo->prepare("SELECT dd.*, smd.nomor_agenda_lengkap FROM disposisi_dewan dd JOIN surat_masuk_dewan smd ON dd.surat_masuk_id = smd.id WHERE dd.id = ?");
    $stmt_select->execute([$id]);
    $data_sebelum = $stmt_select->fetch(PDO::FETCH_ASSOC);

    if ($data_sebelum) {
        // 1. Hapus file lampiran menggunakan helper
        delete_file($data_sebelum['file_lampiran'], 'uploads-dewan');

        // 2. Hapus data dari database
        $stmt_delete = $pdo->prepare("DELETE FROM disposisi_dewan WHERE id = ?");
        $stmt_delete->execute([$id]);

        // 3. Catat aktivitas menggunakan helper
        $log_detail = array_intersect_key($data_sebelum, array_flip(['nomor_agenda_lengkap', 'nama_pegawai', 'catatan_disposisi']));
        log_activity($pdo, "Membatalkan disposisi untuk surat dewan '{$data_sebelum['nomor_agenda_lengkap']}'", [
            'sebelum' => $log_detail
        ]);

        $_SESSION['success_message'] = "Disposisi berhasil dibatalkan.";
    } else {
        $_SESSION['error_message'] = "Disposisi tidak ditemukan.";
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Gagal membatalkan disposisi: " . $e->getMessage();
}

// Redirect kembali ke halaman daftar
header("Location: /disposisi-dewan");
exit;
?>
