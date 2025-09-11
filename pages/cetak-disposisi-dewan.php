<?php
// pages/cetak-disposisi-dewan.php

require_once __DIR__ . '/../libs/fpdf/fpdf.php';

if (!isset($_SESSION['user_id'])) {
    die("Akses ditolak. Silakan login terlebih dahulu.");
}

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    die("ID Surat tidak valid.");
}

// UBAH: Ambil data dari tabel surat_masuk_dewan
$stmt = $pdo->prepare("SELECT *, DATE_FORMAT(tanggal_surat, '%d %M %Y') as tgl_surat_formatted, DATE_FORMAT(tanggal_diterima, '%d %M %Y') as tgl_diterima_formatted FROM surat_masuk_dewan WHERE id = ?");
$stmt->execute([$id]);
$surat = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$surat) {
    die("Data surat tidak ditemukan.");
}

class PDF extends FPDF {
    function NbLines($w, $txt) {
        $cw = &$this->CurrentFont['cw'];
        if($w == 0) $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if($nb > 0 && $s[$nb - 1] == "\n") $nb--;
        $sep = -1; $i = 0; $j = 0; $l = 0; $nl = 1;
        while($i < $nb) {
            $c = $s[$i];
            if($c == "\n") {
                $i++; $sep = -1; $j = $i; $l = 0; $nl++;
                continue;
            }
            if($c == ' ') $sep = $i;
            $l += $cw[$c];
            if($l > $wmax) {
                if($sep == -1) {
                    if($i == $j) $i++;
                } else
                    $i = $sep + 1;
                $sep = -1; $j = $i; $l = 0; $nl++;
            } else
                $i++;
        }
        return $nl;
    }
}

$pdf = new PDF('P', 'mm', array(215.9, 330.2));
$pdf->AddPage();
$pdf->SetMargins(20, 15, 20);
$bottomMargin = 20;
$pdf->SetAutoPageBreak(true, $bottomMargin);

// --- KOP SURAT (Revisi Final) ---

// 1. Tempatkan Logo di sebelah kiri halaman.
// Parameter: path, posisi X, posisi Y, lebar
$pdf->Image('assets/img/Logo_DPRD.png', 20, 18, 32);

// 2. Atur posisi kursor Y agar sejajar dengan bagian atas area teks
$pdf->SetY(18);

// 3. Tentukan area khusus untuk blok teks (di sebelah kanan logo)
$x_teks_mulai = 65; // Titik X (horizontal) dimulainya teks
// Hitung sisa lebar halaman untuk area teks
$lebar_area_teks = $pdf->GetPageWidth() - $x_teks_mulai - 28; // (Lebar Halaman - Posisi Awal Teks - Margin Kanan)

// 4. Cetak setiap baris teks.
// Perhatikan bahwa lebar Cell sekarang diisi dengan $lebar_area_teks, bukan 0.
// Ini membuat teks menjadi center HANYA di dalam area tersebut.
$pdf->SetX($x_teks_mulai);
$pdf->SetFont('Arial', 'B', 21);
$pdf->Cell($lebar_area_teks, 7, 'DEWAN PERWAKILAN RAKYAT DAERAH', 0, 1, 'C');

// Pindahkan kursor X kembali ke awal blok teks untuk baris berikutnya
$pdf->SetX($x_teks_mulai);
$pdf->SetFont('Arial', 'B', 19);
$pdf->Cell($lebar_area_teks, 9, 'KOTA SURABAYA', 0, 1, 'C');

$pdf->SetX($x_teks_mulai);
$pdf->SetFont('Arial', '', 13);
$pdf->Cell($lebar_area_teks, 5, 'Jalan Yos Sudarso Nomor 18 - 22 Surabaya - 60272', 0, 1, 'C');

$pdf->SetX($x_teks_mulai);
$pdf->Cell($lebar_area_teks, 5, 'Telp. (031) 5463551', 0, 1, 'C');

$pdf->SetX($x_teks_mulai);
$pdf->Cell($lebar_area_teks, 5, 'Laman : dprd.surabaya.go.id, Pos-el : dprd_surabaya@surabaya.go.id', 0, 1, 'C');



// Beri spasi sebelum konten utama
$pdf->Ln(8);

$pdf->SetFont('Arial', 'BU', 14);
$pdf->Cell(0, 8, 'LEMBAR DISPOSISI', 0, 1, 'C');
$pdf->Ln(5);

// pages/cetak-disposisi-dewan.php

// KONTEN UTAMA (MODIFIKASI FINAL)
$pdf->SetFont('Arial', '', 11);
$x = $pdf->GetX(); $y = $pdf->GetY();
$lebar_total = 175.9; $lebar_kiri = 90; $lebar_kanan = $lebar_total - $lebar_kiri;
$padding_x = 2; $lebar_label = 28; $lebar_colon = 3; $tinggi_baris_label = 5;

// Kalkulasi dinamis tinggi baris berdasarkan konten
$lebar_isi_kiri = $lebar_kiri - $lebar_label - $lebar_colon - $padding_x - 1;
$lebar_isi_kanan = $lebar_kanan - $lebar_label - $lebar_colon - $padding_x;
$nb_surat_dari = $pdf->NbLines($lebar_isi_kiri, $surat['asal_surat']);
$tinggi_baris1 = max(12, ($nb_surat_dari * $tinggi_baris_label) + 4);
$nb_nomor_surat = $pdf->NbLines($lebar_isi_kiri, $surat['nomor_surat_lengkap']);
$nb_nomor_agenda = $pdf->NbLines($lebar_isi_kanan, $surat['nomor_agenda_lengkap']);
$tinggi_baris2 = max(12, (max($nb_nomor_surat, $nb_nomor_agenda) * $tinggi_baris_label) + 4);
$tinggi_baris3 = 12; // Tinggi baris ketiga kita buat statis
$tinggi_total_atas = $tinggi_baris1 + $tinggi_baris2 + $tinggi_baris3;

// --- Menggambar Semua Kotak dan Garis ---
$pdf->Rect($x, $y, $lebar_total, $tinggi_total_atas);
$pdf->Line($x + $lebar_kiri, $y, $x + $lebar_kiri, $y + $tinggi_total_atas);
$pdf->Line($x, $y + $tinggi_baris1, $x + $lebar_total, $y + $tinggi_baris1);
$pdf->Line($x, $y + $tinggi_baris1 + $tinggi_baris2, $x + $lebar_total, $y + $tinggi_baris1 + $tinggi_baris2);

// --- Mengisi Konten Teks ---
// -- BARIS 1 (Surat Dari & Tgl Diterima) --
$y_baris1 = $y;
$y_pos_konten_kiri1 = $y_baris1 + ($tinggi_baris1 / 2) - (($nb_surat_dari * $tinggi_baris_label) / 2);
$pdf->SetXY($x + $padding_x, $y_pos_konten_kiri1);
$pdf->Cell($lebar_label, $tinggi_baris_label, 'Surat Dari', 0, 0);
$pdf->Cell($lebar_colon, $tinggi_baris_label, ':', 0, 0);
$pdf->SetXY($x + $padding_x + $lebar_label + $lebar_colon, $y_pos_konten_kiri1);
$pdf->MultiCell($lebar_isi_kiri, $tinggi_baris_label, $surat['asal_surat'], 0, 'L');
$pdf->SetXY($x + $lebar_kiri + $padding_x, $y_baris1 + ($tinggi_baris1 / 2) - 5);
$pdf->MultiCell($lebar_label, $tinggi_baris_label, "Tanggal\nDiterima", 0, 'L');
$pdf->SetXY($x + $lebar_kiri + $lebar_label, $y_baris1);
$pdf->Cell($lebar_colon, $tinggi_baris1, ':', 0, 0, 'C');
$pdf->SetXY($x + $lebar_kiri + $lebar_label + $lebar_colon, $y_baris1 + ($tinggi_baris1/2) - ($tinggi_baris_label/2));
$pdf->MultiCell($lebar_isi_kanan, $tinggi_baris_label, $surat['tgl_diterima_formatted'], 0, 'L');

// -- BARIS 2 (Nomor Surat & Nomor Agenda) --
$y_baris2 = $y + $tinggi_baris1;
$y_pos_konten_kiri2 = $y_baris2 + ($tinggi_baris2 / 2) - (($nb_nomor_surat * $tinggi_baris_label) / 2);
$pdf->SetXY($x + $padding_x, $y_pos_konten_kiri2);
$pdf->Cell($lebar_label, $tinggi_baris_label, 'Nomor Surat', 0, 0);
$pdf->Cell($lebar_colon, $tinggi_baris_label, ':', 0, 0);
$pdf->SetXY($x + $padding_x + $lebar_label + $lebar_colon, $y_pos_konten_kiri2);
$pdf->MultiCell($lebar_isi_kiri, $tinggi_baris_label, $surat['nomor_surat_lengkap'], 0, 'L');
$y_pos_konten_kanan2 = $y_baris2 + ($tinggi_baris2 / 2) - (($nb_nomor_agenda * $tinggi_baris_label) / 2);
$pdf->SetXY($x + $lebar_kiri + $padding_x, $y_baris2 + ($tinggi_baris2/2) - 5);
$pdf->MultiCell($lebar_label, $tinggi_baris_label, "Nomor\nAgenda", 0, 'L');
$pdf->SetXY($x + $lebar_kiri + $lebar_label, $y_baris2);
$pdf->Cell($lebar_colon, $tinggi_baris2, ':', 0, 0, 'C');
$pdf->SetXY($x + $lebar_kiri + $lebar_label + $lebar_colon, $y_pos_konten_kanan2);
$pdf->MultiCell($lebar_isi_kanan, $tinggi_baris_label, $surat['nomor_agenda_lengkap'], 0, 'L');

// -- BARIS 3 (Tanggal Surat & Diteruskan Kepada) --
$y_baris3 = $y + $tinggi_baris1 + $tinggi_baris2;
// SISI KIRI: Tanggal Surat
$pdf->SetXY($x + $padding_x, $y_baris3 + ($tinggi_baris3 / 2) - ($tinggi_baris_label / 2));
$pdf->Cell($lebar_label, $tinggi_baris_label, 'Tanggal Surat', 0, 0);
$pdf->Cell($lebar_colon, $tinggi_baris_label, ':', 0, 0);
$pdf->MultiCell($lebar_isi_kiri, $tinggi_baris_label, $surat['tgl_surat_formatted'], 0, 'L');
// SISI KANAN: Diteruskan Kepada (PENGGANTI SIFAT SURAT)
$pdf->SetXY($x + $lebar_kiri + $padding_x, $y_baris3 + 1);
$pdf->MultiCell($lebar_label, 5, "Diteruskan\nKepada", 0, 'L');
$pdf->SetXY($x + $lebar_kiri + $lebar_label, $y_baris3);
$pdf->Cell($lebar_colon, $tinggi_baris3, ':', 0, 0, 'C');
$diteruskan_kepada = $surat['diteruskan_kepada'] ?? '-';
$nb_diteruskan = $pdf->NbLines($lebar_isi_kanan, $diteruskan_kepada);
$y_pos_diteruskan = $y_baris3 + ($tinggi_baris3 / 2) - (($nb_diteruskan * $tinggi_baris_label) / 2);
$pdf->SetXY($x + $lebar_kiri + $lebar_label + $lebar_colon, $y_pos_diteruskan);
$pdf->MultiCell($lebar_isi_kanan, $tinggi_baris_label, $diteruskan_kepada, 0, 'L');

$pdf->SetFont('Arial', '', 11); // Mengembalikan font ke ukuran normal
$lebar_teks_perihal = $lebar_total - $lebar_label - $lebar_colon - ($padding_x * 2);
$jumlah_baris = $pdf->NbLines($lebar_teks_perihal, $surat['perihal']);
$tinggi_konten_perihal = $jumlah_baris * $tinggi_baris_label;
$tinggi_kotak_perihal = max(14, $tinggi_konten_perihal + 4);
$y_perihal_start = $y + $tinggi_total_atas;
$pdf->Rect($x, $y_perihal_start, $lebar_total, $tinggi_kotak_perihal);
$y_content_start = $y_perihal_start + ($tinggi_kotak_perihal - $tinggi_konten_perihal) / 2;
$pdf->SetXY($x + $padding_x, $y_content_start);
$pdf->Cell($lebar_label, $tinggi_baris_label, 'Perihal', 0, 0);
$pdf->Cell($lebar_colon, $tinggi_baris_label, ':', 0, 0);
$pdf->SetXY($x + $padding_x + $lebar_label + $lebar_colon, $y_content_start);
$pdf->MultiCell($lebar_teks_perihal, $tinggi_baris_label, $surat['perihal'], 0, 'L');
$pdf->SetY($y_perihal_start + $tinggi_kotak_perihal + 5);
// $pdf->Cell($lebar_total, 7, 'Diteruskan Kepada :', 'LTR', 1);
// $pdf->Cell($lebar_total, 7, '1. Kepala Bagian Umum', 'LR', 1);
// $pdf->Cell($lebar_total, 7, '2. Kepala Bagian Rapat dan Perundang-Undangan', 'LR', 1);
// $pdf->Cell($lebar_total, 7, '3. Kepala Bagian Informasi dan Protokol', 'LBR', 1);
// $pdf->Ln(2);
$pdf->Cell(35, 7, 'Isi Disposisi :', 'LT', 0);
$pdf->Cell($lebar_total - 35, 7, '', 'TR', 1);
$current_y = $pdf->GetY();
$sisa_tinggi = $pdf->GetPageHeight() - $current_y - $bottomMargin;
$pdf->Cell($lebar_total, $sisa_tinggi, '', 'LBR', 1);

// Output PDF
$pdf->Output('I', 'Disposisi_Dewan_' . str_replace('/', '_', $surat['nomor_agenda_lengkap']) . '.pdf');
?>
