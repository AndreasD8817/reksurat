<?php
// pages/cetak-disposisi.php

require_once __DIR__ . '/../libs/fpdf/fpdf.php';

// Keamanan: Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    die("Akses ditolak. Silakan login terlebih dahulu.");
}

$id = $_GET['id'] ?? null;
if (!$id) {
    die("ID Surat tidak valid.");
}

// Ambil data surat dari database
$stmt = $pdo->prepare("SELECT *, DATE_FORMAT(tanggal_surat, '%d %M %Y') as tgl_surat_formatted, DATE_FORMAT(tanggal_diterima, '%d %M %Y') as tgl_diterima_formatted FROM surat_masuk WHERE id = ?");
$stmt->execute([$id]);
$surat = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$surat) {
    die("Data surat tidak ditemukan.");
}

class PDF extends FPDF
{
    // Class kustom untuk FPDF
}

// Inisialisasi PDF dengan ukuran F4 (215.9mm x 330.2mm)
$pdf = new PDF('P', 'mm', array(215.9, 330.2));
$pdf->AddPage();
$pdf->SetMargins(20, 15, 20);

// --- KOP SURAT ---
$pdf->Image('assets/img/Logo_Pemkot.png', 20, 15, 25); 
$pdf->SetFont('Times', 'B', 14);
$pdf->Cell(0, 7, 'PEMERINTAH KOTA SURABAYA', 0, 1, 'C');
$pdf->SetFont('Times', 'B', 18);
$pdf->Cell(0, 9, 'SEKRETARIAT DPRD', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Jalan Yos Sudarso Nomor 18 - 22 Surabaya 60272', 0, 1, 'C');
$pdf->Cell(0, 6, 'Telp. (031) 5463551', 0, 1, 'C');
$pdf->Cell(0, 6, 'Laman : dprd.surabaya.go.id, Pos-el : sekwan@surabaya.go.id', 0, 1, 'C');
// $pdf->SetLineWidth(1);
// $pdf->Line(20, 52, 195.9, 52);
// $pdf->SetLineWidth(0.2);
// $pdf->Line(20, 53, 195.9, 53);
$pdf->Ln(10);

// --- JUDUL LEMBAR ---
$pdf->SetFont('Arial', 'BU', 14);
$pdf->Cell(0, 8, 'LEMBAR DISPOSISI', 0, 1, 'C');
$pdf->Ln(5);

// ==================================================================
// --- KONTEN UTAMA (PERBAIKAN FINAL PADA LAYOUT) ---
// ==================================================================

$pdf->SetFont('Arial', '', 11);

// Simpan posisi Y awal dan X awal
$y_awal = $pdf->GetY();
$x_awal = $pdf->GetX();
$tinggi_baris = 7;
$tinggi_kotak_atas = $tinggi_baris * 4; // 28mm

// Definisikan lebar kolom
$lebar_kolom_kiri = 90;
$lebar_kolom_kanan = 175.9 - $lebar_kolom_kiri - 20;

// --- Menggambar Kotak-Kotak Layout ---
$pdf->Rect($x_awal, $y_awal, $lebar_kolom_kiri, $tinggi_kotak_atas);
$pdf->Rect($x_awal + $lebar_kolom_kiri, $y_awal, $lebar_kolom_kanan, $tinggi_kotak_atas);
$pdf->Rect($x_awal, $y_awal + $tinggi_kotak_atas, 175.9 - 20, 14);

// --- Mengisi Kolom Kiri ---
$pdf->SetXY($x_awal + 2, $y_awal + 1);
$pdf->Cell(30, $tinggi_baris, 'Surat Dari', 0, 0);
$pdf->Cell(5, $tinggi_baris, ':', 0, 0);
$pdf->MultiCell($lebar_kolom_kiri - 40, 6, $surat['asal_surat'], 0, 'L');

$pdf->SetXY($x_awal + 2, $y_awal + 8);
$pdf->Cell(30, $tinggi_baris, 'Nomor Surat', 0, 0);
$pdf->Cell(5, $tinggi_baris, ':', 0, 0);
$pdf->MultiCell($lebar_kolom_kiri - 40, 6, $surat['nomor_surat_lengkap'], 0, 'L');

$pdf->SetXY($x_awal + 2, $y_awal + 20);
$pdf->Cell(30, $tinggi_baris, 'Tanggal Surat', 0, 0);
$pdf->Cell(5, $tinggi_baris, ':', 0, 0);
$pdf->Cell(40, $tinggi_baris, $surat['tgl_surat_formatted'], 0, 1);

// --- Mengisi Kolom Kanan ---
$pdf->SetXY($x_awal + $lebar_kolom_kiri + 2, $y_awal + 1);
$pdf->Cell(35, $tinggi_baris, 'Tanggal Diterima', 0, 0);
$pdf->Cell(5, $tinggi_baris, ':', 0, 0);
$pdf->Cell(40, $tinggi_baris, $surat['tgl_diterima_formatted'], 0, 1);

$pdf->SetXY($x_awal + $lebar_kolom_kiri + 2, $y_awal + 8);
$pdf->Cell(35, $tinggi_baris, 'Nomor Agenda', 0, 0);
$pdf->Cell(5, $tinggi_baris, ':', 0, 0);
$pdf->MultiCell($lebar_kolom_kanan - 45, 6, $surat['nomor_agenda_lengkap'], 0, 'L');

$pdf->SetXY($x_awal + $lebar_kolom_kiri + 2, $y_awal + 20);
$pdf->Cell(35, $tinggi_baris, 'Sifat', 0, 0);
$pdf->Cell(5, $tinggi_baris, ':', 0, 0);
$sifat = $surat['sifat_surat'];
$x_sifat_start = $pdf->GetX();

$pdf->SetFont('ZapfDingbats', '', 12);
$pdf->Cell(5, 6, ($sifat == 'Biasa') ? '4' : '', 1, 0, 'C');
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(18, 6, ' Biasa', 0, 0);

$pdf->SetX($x_sifat_start + 25);
$pdf->SetFont('ZapfDingbats', '', 12);
$pdf->Cell(5, 6, ($sifat == 'Amat Segera') ? '4' : '', 1, 0, 'C');
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(28, 6, ' Amat Segera', 0, 0);

$pdf->SetX($x_sifat_start + 63);
$pdf->SetFont('ZapfDingbats', '', 12);
$pdf->Cell(5, 6, ($sifat == 'Penting') ? '4' : '', 1, 0, 'C');
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(18, 6, ' Penting', 0, 1);

// --- Mengisi Baris Perihal ---
$pdf->SetXY($x_awal, $y_awal + $tinggi_kotak_atas);
$pdf->Cell(32, 14, 'Perihal', 0, 0, 'L');
$pdf->SetXY($x_awal + 32, $y_awal + $tinggi_kotak_atas);
$pdf->Cell(5, 14, ':', 0, 0, 'C');
$pdf->SetXY($x_awal + 37, $y_awal + $tinggi_kotak_atas + 1);
$pdf->MultiCell($lebar_kolom_kiri + $lebar_kolom_kanan - 40, 6, $surat['perihal'], 0, 'L');

// ==================================================================
// --- AKHIR BAGIAN YANG DIPERBAIKI ---
// ==================================================================

// Bagian Disposisi
$pdf->SetY($y_awal + $tinggi_kotak_atas + 14 + 5); // Set Posisi Y di bawah kotak perihal
$pdf->Cell(0, 7, 'Diteruskan Kepada :', 'LTR', 1);
$pdf->Cell(0, 7, '1. Kepala Bagian Umum', 'LR', 1);
$pdf->Cell(0, 7, '2. Kepala Bagian Rapat dan Perundang-Undangan', 'LR', 1);
$pdf->Cell(0, 7, '3. Kepala Bagian Informasi dan Protokol', 'LBR', 1);
$pdf->Ln(2);
$pdf->Cell(35, 7, 'Isi Disposisi :', 'LT', 0);
$pdf->Cell(0, 7, '', 'TR', 1);
$pdf->Cell(0, 100, '', 'LBR', 1);

// Output PDF
$pdf->Output('I', 'Disposisi_' . str_replace('/', '_', $surat['nomor_agenda_lengkap']) . '.pdf');
?>