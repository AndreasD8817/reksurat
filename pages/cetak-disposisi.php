<?php
// Halaman: pages/cetak-disposisi.php

// Memuat library FPDF yang diperlukan untuk membuat file PDF.
require_once __DIR__ . '/../libs/fpdf/fpdf.php';

// --- Keamanan dan Validasi ---
if (!isset($_SESSION['user_id'])) {
    die("Akses ditolak. Silakan login terlebih dahulu.");
}

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    die("ID Surat tidak valid.");
}

// --- Pengambilan Data dari Database ---
$stmt = $pdo->prepare("SELECT *, DATE_FORMAT(tanggal_surat, '%d %M %Y') as tgl_surat_formatted, DATE_FORMAT(tanggal_diterima, '%d %M %Y') as tgl_diterima_formatted FROM surat_masuk WHERE id = ?");
$stmt->execute([$id]);
$surat = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$surat) {
    die("Data surat tidak ditemukan.");
}

// --- Kustomisasi Class PDF ---
class PDF extends FPDF
{
    // Class bisa dikembangkan lebih lanjut (misal: header/footer otomatis)
}

// --- Inisialisasi Dokumen PDF ---
$pdf = new PDF('P', 'mm', array(215.9, 330.2));
$pdf->AddPage();
$pdf->SetMargins(20, 15, 20);

// --- KOP SURAT ---
// Path ke gambar logo, posisi X, Y, dan Ukuran (lebar)
$pdf->Image('assets/img/Logo_Pemkot.png', 20, 15, 25); 

// 1. PEMERINTAH KOTA SURABAYA (Arial 14 non bold)
$pdf->SetFont('Arial', '', 14); 
$pdf->Cell(0, 7, 'PEMERINTAH KOTA SURABAYA', 0, 1, 'C');

// 2. SEKRETARIAT DPRD (Arial 25 bold)
$pdf->SetFont('Arial', 'B', 25); 
$pdf->Cell(0, 12, 'SEKRETARIAT DPRD', 0, 1, 'C');

// 3. Alamat, Telp, dan Laman (Arial 12 non bold)
$pdf->SetFont('Arial', '', 12); 
$pdf->Cell(0, 6, 'Jalan Yos Sudarso Nomor 18 - 22 Surabaya 60272', 0, 1, 'C');
$pdf->Cell(0, 6, 'Telp. (031) 5463551', 0, 1, 'C');
$pdf->Cell(0, 6, 'Laman : dprd.surabaya.go.id, Pos-el : sekwan@surabaya.go.id', 0, 1, 'C');

// Garis bawah kop surat
// $pdf->Line($pdf->GetX(), $pdf->GetY()+2, $pdf->GetX() + 175.9, $pdf->GetY()+2);

// Spasi setelah kop surat
$pdf->Ln(8);

// --- JUDUL LEMBAR ---
$pdf->SetFont('Arial', 'BU', 14);
$pdf->Cell(0, 8, 'LEMBAR DISPOSISI', 0, 1, 'C');
$pdf->Ln(5);

// ==================================================================
// --- KONTEN UTAMA (VERSI PERBAIKAN DENGAN KOTAK) ---
// ==================================================================

// Mengatur font default untuk konten.
$pdf->SetFont('Arial', '', 10);

// --- Definisi Variabel Layout ---
$x = $pdf->GetX();
$y = $pdf->GetY();
$lebar_total = 175.9;
$lebar_kiri = 90;
$lebar_kanan = $lebar_total - $lebar_kiri;
$tinggi_baris1 = 10;
$tinggi_baris2 = 10;
$tinggi_baris3 = 10;
$tinggi_total_atas = $tinggi_baris1 + $tinggi_baris2 + $tinggi_baris3;

// --- 1. Menggambar Semua Kotak dan Garis ---
// Kotak luar utama
$pdf->Rect($x, $y, $lebar_total, $tinggi_total_atas);
// Garis vertikal pemisah kolom
$pdf->Line($x + $lebar_kiri, $y, $x + $lebar_kiri, $y + $tinggi_total_atas);
// Garis horizontal pemisah baris
$pdf->Line($x, $y + $tinggi_baris1, $x + $lebar_total, $y + $tinggi_baris1);
$pdf->Line($x, $y + $tinggi_baris1 + $tinggi_baris2, $x + $lebar_total, $y + $tinggi_baris1 + $tinggi_baris2);
// Kotak Perihal di bawahnya
$pdf->Rect($x, $y + $tinggi_total_atas, $lebar_total, 14);


// --- 2. Mengisi Konten Teks ---
$padding_x = 2; // Jarak dari tepi kiri setiap kolom
$padding_y = 3; // Jarak dari tepi atas setiap baris
$lebar_label = 28;
$lebar_colon = 3;
$tinggi_baris_label = 5;

// -- BARIS 1 --
// Kolom Kiri: Surat Dari
$pdf->SetXY($x + $padding_x, $y + $padding_y);
$pdf->Cell($lebar_label, 5, 'Surat Dari', 0, 0);
$pdf->Cell($lebar_colon, 5, ':', 0, 0);
$pdf->MultiCell($lebar_kiri - $lebar_label - $lebar_colon - $padding_x - 1, 5, $surat['asal_surat'], 0, 'L');
// Kolom Kanan: Tanggal Diterima
$pdf->SetXY($x + $lebar_kiri + $padding_x, $y + 1);
$pdf->MultiCell($lebar_label, 4.5, "Tanggal\nDiterima", 0, 'L');
$pdf->SetXY($x + $lebar_kiri + $lebar_label, $y);
$pdf->Cell($lebar_colon, $tinggi_baris1, ':', 0, 0, 'C');
$pdf->SetXY($x + $lebar_kiri + $lebar_label + $lebar_colon, $y + $padding_y);
$pdf->MultiCell($lebar_kanan - $lebar_label - $lebar_colon - $padding_x, 5, $surat['tgl_diterima_formatted'], 0, 'L');

// -- BARIS 2 --
$y_baris2 = $y + $tinggi_baris1;
// Kolom Kiri: Nomor Surat
$pdf->SetXY($x + $padding_x, $y_baris2 + $padding_y);
$pdf->Cell($lebar_label, 5, 'Nomor Surat', 0, 0);
$pdf->Cell($lebar_colon, 5, ':', 0, 0);
$pdf->MultiCell($lebar_kiri - $lebar_label - $lebar_colon - $padding_x - 1, 5, $surat['nomor_surat_lengkap'], 0, 'L');
// Kolom Kanan: Nomor Agenda
$pdf->SetXY($x + $lebar_kiri + $padding_x, $y_baris2 + 1);
$pdf->MultiCell($lebar_label, 4.5, "Nomor\nAgenda", 0, 'L');
$pdf->SetXY($x + $lebar_kiri + $lebar_label, $y_baris2);
$pdf->Cell($lebar_colon, $tinggi_baris2, ':', 0, 0, 'C');
$pdf->SetXY($x + $lebar_kiri + $lebar_label + $lebar_colon, $y_baris2 + $padding_y);
$pdf->MultiCell($lebar_kanan - $lebar_label - $lebar_colon - $padding_x, 5, $surat['nomor_agenda_lengkap'], 0, 'L');

// -- BARIS 3 --
$y_baris3 = $y + $tinggi_baris1 + $tinggi_baris2;
// Kolom Kiri: Tanggal Surat (kode ini tetap sama)
$pdf->SetXY($x + $padding_x, $y_baris3 + $padding_y);
$pdf->Cell($lebar_label, 5, 'Tanggal Surat', 0, 0);
$pdf->Cell($lebar_colon, 5, ':', 0, 0);
$pdf->MultiCell($lebar_kiri - $lebar_label - $lebar_colon - $padding_x - 1, 5, $surat['tgl_surat_formatted'], 0, 'L');

// Kolom Kanan: Sifat
// PERBAIKAN 1: Atur posisi X titik dua (:) agar lurus dengan yang di atas
$pdf->SetXY($x + $lebar_kiri + $lebar_label, $y_baris3 + 1); 
$pdf->Cell($lebar_colon, 4.5, ':', 0, 0);
// Cetak label "Sifat" di sebelah kirinya
$pdf->SetXY($x + $lebar_kiri + $padding_x, $y_baris3 + 1);
$pdf->Cell($lebar_label, 4.5, 'Sifat', 0, 0);

// --- Pilihan Sifat Surat di bawahnya ---
$sifat = $surat['sifat_surat'];
$pdf->SetY($y_baris3 + 5.5);
$x_pilihan_awal = $x + $lebar_kiri + $padding_x;
$pdf->SetX($x_pilihan_awal);
$pdf->SetFont('Arial', '', 10);

// PERBAIKAN 2: Atur ulang lebar setiap pilihan agar tidak mepet
// Pilihan "Biasa"
$pdf->Cell(4, 4, '', 1, 0); // Kotak
if ($sifat == 'Biasa') { $pdf->SetFont('ZapfDingbats','',10); $pdf->Text($pdf->GetX() - 3.5, $pdf->GetY() + 3.2, '4'); $pdf->SetFont('Arial','',10); }
$pdf->Cell(20, 5, ' Biasa', 0, 0);

// Pilihan "Amat Segera"
$pdf->Cell(4, 4, '', 1, 0); // Kotak
if ($sifat == 'Amat Segera') { $pdf->SetFont('ZapfDingbats','',10); $pdf->Text($pdf->GetX() - 3.5, $pdf->GetY() + 3.2, '4'); $pdf->SetFont('Arial','',10); }
$pdf->Cell(28, 5, ' Amat Segera', 0, 0);

// Pilihan "Penting"
$pdf->Cell(4, 4, '', 1, 0); // Kotak
if ($sifat == 'Penting') { $pdf->SetFont('ZapfDingbats','',10); $pdf->Text($pdf->GetX() - 3.5, $pdf->GetY() + 3.2, '4'); $pdf->SetFont('Arial','',10); }
$pdf->Cell(20, 5, ' Penting', 0, 1);

$pdf->SetFont('Arial', '', 11); // Kembalikan ukuran font ke standar

// --- Mengisi Baris Perihal ---
$pdf->SetXY($x + $padding_x, $y + $tinggi_total_atas + 4);
$pdf->Cell($lebar_label, 5, 'Perihal', 0, 0);
$pdf->Cell($lebar_colon, 5, ':', 0, 0);
$pdf->MultiCell($lebar_total - $lebar_label - $lebar_colon - $padding_x -1, 5, $surat['perihal'], 0, 'L');

// --- Bagian Disposisi ---
// **PERBAIKAN UTAMA DI SINI**
// Menggunakan variabel $y dan $tinggi_total_atas yang sudah didefinisikan di atas.
$pdf->SetY($y + $tinggi_total_atas + 14 + 5);
$pdf->Cell($lebar_total, 7, 'Diteruskan Kepada :', 'LTR', 1);
$pdf->Cell($lebar_total, 7, '1. Kepala Bagian Umum', 'LR', 1);
$pdf->Cell($lebar_total, 7, '2. Kepala Bagian Rapat dan Perundang-Undangan', 'LR', 1);
$pdf->Cell($lebar_total, 7, '3. Kepala Bagian Informasi dan Protokol', 'LBR', 1);
$pdf->Ln(2);
$pdf->Cell(35, 7, 'Isi Disposisi :', 'LT', 0);
$pdf->Cell($lebar_total - 35, 7, '', 'TR', 1);
$pdf->Cell($lebar_total, 153, '', 'LBR', 1);

// --- Output PDF ---
$pdf->Output('I', 'Disposisi_' . str_replace('/', '_', $surat['nomor_agenda_lengkap']) . '.pdf');

?>