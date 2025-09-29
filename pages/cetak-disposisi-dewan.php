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

// Ambil data dari tabel surat_masuk_dewan
$stmt = $pdo->prepare("SELECT *, DATE_FORMAT(tanggal_surat, '%d %M %Y') as tgl_surat_formatted, DATE_FORMAT(tanggal_diterima, '%d %M %Y') as tgl_diterima_formatted FROM surat_masuk_dewan WHERE id = ?");
$stmt->execute([$id]);
$surat = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$surat) {
    die("Data surat tidak ditemukan.");
}

class PDF extends FPDF {
    // Fungsi untuk menghitung jumlah baris yang dibutuhkan oleh MultiCell
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

$pdf = new PDF('P', 'mm', array(215.9, 330.2)); // Ukuran kertas F4
$pdf->AddPage();
$pdf->SetMargins(5, 15, 5); // Margin Kiri: 5, Atas: 15, Kanan: 5
$bottomMargin = 5;
$pdf->SetAutoPageBreak(true, $bottomMargin);

// --- KOP SURAT ---
$pdf->Image('assets/img/Logo_DPRD.png', 15, 15, 35);
$pdf->SetY(18);
$x_teks_mulai = 65;
$lebar_area_teks = $pdf->GetPageWidth() - $x_teks_mulai - 28;

$pdf->SetX($x_teks_mulai);
$pdf->SetFont('Arial', 'B', 21);
$pdf->Cell($lebar_area_teks, 7, 'DEWAN PERWAKILAN RAKYAT DAERAH', 0, 1, 'C');
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
$pdf->Ln(8);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 8, 'LEMBAR DISPOSISI', 0, 1, 'C');
$pdf->Ln(5);

// --- KONTEN UTAMA ---
$pdf->SetFont('Arial', '', 11);
$x = $pdf->GetX();
$y = $pdf->GetY();

$lebar_total = $pdf->GetPageWidth() - 10;
$lebar_kiri = $lebar_total / 2;
$lebar_kanan = $lebar_total / 2;

$padding_x = 2;
$lebar_label = 28;
$lebar_colon = 3;
$tinggi_baris_label = 4.5; // Jarak per baris agar tidak terpotong

// Hitung semua jumlah baris yang dibutuhkan terlebih dahulu
$lebar_isi_kiri = $lebar_kiri - $lebar_label - $lebar_colon - $padding_x - 1;
$lebar_isi_kanan = $lebar_kanan - $lebar_label - $lebar_colon - $padding_x;
$nb_surat_dari = $pdf->NbLines($lebar_isi_kiri, $surat['asal_surat']);
$nb_tgl_diterima = $pdf->NbLines($lebar_isi_kanan, $surat['tgl_diterima_formatted']);
$nb_nomor_surat = $pdf->NbLines($lebar_isi_kiri, $surat['nomor_surat_lengkap']);
$nb_nomor_agenda = $pdf->NbLines($lebar_isi_kanan, $surat['nomor_agenda_lengkap']);
$nb_tgl_surat = $pdf->NbLines($lebar_isi_kiri, $surat['tgl_surat_formatted']);
$diteruskan_kepada = $surat['diteruskan_kepada'] ?? '-';
$nb_diteruskan = $pdf->NbLines($lebar_isi_kanan, $diteruskan_kepada);

// Hitung tinggi setiap baris berdasarkan konten terpanjang (kiri atau kanan)
$tinggi_baris1 = max(14, (max($nb_surat_dari, $nb_tgl_diterima) * $tinggi_baris_label) + 4);
$tinggi_baris2 = max(14, (max($nb_nomor_surat, $nb_nomor_agenda) * $tinggi_baris_label) + 4);
$tinggi_baris3 = max(14, (max($nb_tgl_surat, $nb_diteruskan) * $tinggi_baris_label) + 4);
$tinggi_total_atas = $tinggi_baris1 + $tinggi_baris2 + $tinggi_baris3;

// Menggambar Kotak dan Garis
$pdf->Rect($x, $y, $lebar_total, $tinggi_total_atas);
$pdf->Line($x + $lebar_kiri, $y, $x + $lebar_kiri, $y + $tinggi_total_atas);
$pdf->Line($x, $y + $tinggi_baris1, $x + $lebar_total, $y + $tinggi_baris1);
$pdf->Line($x, $y + $tinggi_baris1 + $tinggi_baris2, $x + $lebar_total, $y + $tinggi_baris1 + $tinggi_baris2);

// --- Mengisi Konten Teks (Logika Final: Selalu Rata Tengah) ---

// --- BARIS 1 ---
$y_baris1 = $y;
// Hitung posisi tengah untuk konten kiri
$y_pos_kiri1 = $y_baris1 + ($tinggi_baris1 / 2) - (($nb_surat_dari * $tinggi_baris_label) / 2);
// Hitung posisi tengah untuk konten kanan
$y_pos_kanan1 = $y_baris1 + ($tinggi_baris1 / 2) - (($nb_tgl_diterima * $tinggi_baris_label) / 2);

$pdf->SetFont('Arial', '', 12);
$pdf->SetXY($x + $padding_x, $y_pos_kiri1);
$pdf->Cell($lebar_label, $tinggi_baris_label, 'Surat Dari', 0, 0);
$pdf->Cell($lebar_colon, $tinggi_baris_label, ':', 0, 0);
$pdf->SetXY($x + $padding_x + $lebar_label + $lebar_colon, $y_pos_kiri1);
$pdf->MultiCell($lebar_isi_kiri, $tinggi_baris_label, $surat['asal_surat'], 0, 'L');

$y_pos_label_kanan1 = $y_baris1 + ($tinggi_baris1 / 2) - 5; // Posisi tengah untuk label 2 baris
$pdf->SetXY($x + $lebar_kiri + $padding_x, $y_pos_label_kanan1);
$pdf->MultiCell($lebar_label, $tinggi_baris_label, 'Tanggal Diterima', 0, 'L');
$pdf->SetXY($x + $lebar_kiri + $lebar_label, $y_baris1);
$pdf->Cell($lebar_colon, $tinggi_baris1, ':', 0, 0, 'C');
$pdf->SetXY($x + $lebar_kiri + $lebar_label + $lebar_colon, $y_pos_kanan1);
$pdf->MultiCell($lebar_isi_kanan, $tinggi_baris_label, $surat['tgl_diterima_formatted'], 0, 'L');

// --- BARIS 2 ---
$y_baris2 = $y + $tinggi_baris1;
$y_pos_kiri2 = $y_baris2 + ($tinggi_baris2 / 2) - (($nb_nomor_surat * $tinggi_baris_label) / 2);
$y_pos_kanan2 = $y_baris2 + ($tinggi_baris2 / 2) - (($nb_nomor_agenda * $tinggi_baris_label) / 2);

$pdf->SetXY($x + $padding_x, $y_pos_kiri2);
$pdf->Cell($lebar_label, $tinggi_baris_label, 'Nomor Surat', 0, 0);
$pdf->Cell($lebar_colon, $tinggi_baris_label, ':', 0, 0);
$pdf->SetXY($x + $padding_x + $lebar_label + $lebar_colon, $y_pos_kiri2);
$pdf->MultiCell($lebar_isi_kiri, $tinggi_baris_label, $surat['nomor_surat_lengkap'], 0, 'L');

$y_pos_label_kanan2 = $y_baris2 + ($tinggi_baris2 / 2) - 5;
$pdf->SetXY($x + $lebar_kiri + $padding_x, $y_pos_label_kanan2);
$pdf->MultiCell($lebar_label, $tinggi_baris_label, 'Nomor Agenda', 0, 'L');
$pdf->SetXY($x + $lebar_kiri + $lebar_label, $y_baris2);
$pdf->Cell($lebar_colon, $tinggi_baris2, ':', 0, 0, 'C');
$pdf->SetXY($x + $lebar_kiri + $lebar_label + $lebar_colon, $y_pos_kanan2);
$pdf->MultiCell($lebar_isi_kanan, $tinggi_baris_label, $surat['nomor_agenda_lengkap'], 0, 'L');

// --- BARIS 3 ---
$y_baris3 = $y + $tinggi_baris1 + $tinggi_baris2;
$y_pos_kiri3 = $y_baris3 + ($tinggi_baris3 / 2) - (($nb_tgl_surat * $tinggi_baris_label) / 2);
$y_pos_kanan3 = $y_baris3 + ($tinggi_baris3 / 2) - (($nb_diteruskan * $tinggi_baris_label) / 2);

$pdf->SetXY($x + $padding_x, $y_pos_kiri3);
$pdf->Cell($lebar_label, $tinggi_baris_label, 'Tanggal Surat', 0, 0);
$pdf->Cell($lebar_colon, $tinggi_baris_label, ':', 0, 0);
$pdf->SetXY($x + $padding_x + $lebar_label + $lebar_colon, $y_pos_kiri3);
$pdf->MultiCell($lebar_isi_kiri, $tinggi_baris_label, $surat['tgl_surat_formatted'], 0, 'L');

$y_pos_label_kanan3 = $y_baris3 + ($tinggi_baris3 / 2) - 5;
$pdf->SetXY($x + $lebar_kiri + $padding_x, $y_pos_label_kanan3);
$pdf->MultiCell($lebar_label, $tinggi_baris_label, 'Diteruskan Kepada', 0, 'L');
$pdf->SetXY($x + $lebar_kiri + $lebar_label, $y_baris3);
$pdf->Cell($lebar_colon, $tinggi_baris3, ':', 0, 0, 'C');
$pdf->SetXY($x + $lebar_kiri + $lebar_label + $lebar_colon, $y_pos_kanan3);
$pdf->MultiCell($lebar_isi_kanan, $tinggi_baris_label, $diteruskan_kepada, 0, 'L');

// KOTAK PERIHAL
$pdf->SetFont('Arial', '', 12);
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

$pdf->SetY($y_perihal_start + $tinggi_kotak_perihal);

// KOTAK ISI DISPOSISI
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell($lebar_total, 8, 'ISI DISPOSISI', 'LTR', 1, 'C');
$current_y = $pdf->GetY();
$sisa_tinggi = $pdf->GetPageHeight() - $current_y - $bottomMargin;
$pdf->Cell($lebar_total, $sisa_tinggi, '', 'LBR', 1);

// Output PDF
$pdf->Output('I', 'Disposisi_Dewan_' . str_replace('/', '_', $surat['nomor_agenda_lengkap']) . '.pdf');
?>