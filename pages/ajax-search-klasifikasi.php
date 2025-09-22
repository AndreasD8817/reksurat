<?php
// pages/ajax-search-klasifikasi.php

// 1. Memuat file koneksi database Anda.
// Pastikan path ini benar dari lokasi file ini.
require_once '../config/database.php';

// 2. Mengatur header respons menjadi JSON.
// Ini memberitahu browser bahwa data yang dikirim kembali adalah format JSON.
header('Content-Type: application/json');

// 3. Menyiapkan struktur respons default.
$response = ['status' => 'error', 'data' => []];

// 4. Mengambil kata kunci pencarian (term) dari URL yang dikirim oleh JavaScript.
// '??' adalah null coalescing operator, cara aman untuk menangani variabel yang mungkin tidak ada.
$searchTerm = $_GET['term'] ?? '';

// 5. Hanya menjalankan pencarian jika ada kata kunci (minimal 1 karakter).
if (!empty($searchTerm)) {
    try {
        // Menggunakan variabel $pdo yang sudah tersedia dari database.php
        
        // 7. Menyiapkan query SQL dengan placeholder (?) untuk mencegah SQL Injection.
        // Query ini mencari 'searchTerm' di kolom 'kode' ATAU 'deskripsi'.
        // Dibatasi 20 hasil untuk performa.
        $stmt = $pdo->prepare("SELECT id, kode, deskripsi FROM klasifikasi_arsip WHERE kode LIKE ? OR deskripsi LIKE ? LIMIT 20");
        
        // 8. Menambahkan wildcard '%' di awal dan akhir kata kunci.
        // Ini agar pencarian bisa menemukan kata di tengah kalimat (contoh: cari 'Dinas' akan menemukan 'Perjalanan Dinas').
        $likeTerm = "%{$searchTerm}%";

        // 9. Menjalankan query dengan aman, memasukkan $likeTerm untuk setiap placeholder (?).
        $stmt->execute([$likeTerm, $likeTerm]);
        
        // 10. Mengambil semua baris hasil query sebagai array.
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 11. Jika query berhasil, ubah status respons menjadi 'success' dan isi dengan data.
        $response['status'] = 'success';
        $response['data'] = $data;

    } catch (PDOException $e) {
        // 12. Jika terjadi error saat koneksi atau query database.
        $response['message'] = 'Terjadi kesalahan pada server.';
        // Sebaiknya catat error detail di log server, jangan tampilkan ke pengguna.
        error_log("AJAX Search Klasifikasi Error: " . $e->getMessage());
    }
} else {
    // Jika tidak ada kata kunci yang dikirim, kembalikan array data kosong.
    $response['status'] = 'success';
    $response['data'] = [];
}

// 13. Mengubah array PHP ($response) menjadi string format JSON dan menampilkannya.
// Inilah yang akan diterima oleh JavaScript di sisi frontend.
echo json_encode($response);
