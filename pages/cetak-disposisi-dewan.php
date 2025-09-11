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

// KOP SURAT (Sama persis)
$pdf->Image('assets/img/Logo_Pemkot.png', 20, 15, 25);
$pdf->SetFont('Arial', '', 14);
$pdf->Cell(0, 7, 'PEMERINTAH KOTA SURABAYA', 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 25);
$pdf->Cell(0, 12, 'SEKRETARIAT DPRD', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 6, 'Jalan Yos Sudarso Nomor 18 - 22 Surabaya 60272', 0, 1, 'C');
$pdf->Cell(0, 6, 'Telp. (031) 5463551', 0, 1, 'C');
$pdf->Cell(0, 6, 'Laman : dprd.surabaya.go.id, Pos-el : sekwan@surabaya.go.id', 0, 1, 'C');
$pdf->Ln(8);

$pdf->SetFont('Arial', 'BU', 14);
$pdf->Cell(0, 8, 'LEMBAR DISPOSISI', 0, 1, 'C');
$pdf->Ln(5);

// KONTEN UTAMA (Sama persis)
$pdf->SetFont('Arial', '', 11);
$x = $pdf->GetX(); $y = $pdf->GetY();
$lebar_total = 175.9; $lebar_kiri = 90; $lebar_kanan = $lebar_total - $lebar_kiri;
$padding_x = 2; $lebar_label = 28; $lebar_colon = 3; $tinggi_baris_label = 5;
$lebar_isi_kiri = $lebar_kiri - $lebar_label - $lebar_colon - $padding_x - 1;
$lebar_isi_kanan = $lebar_kanan - $lebar_label - $lebar_colon - $padding_x;
$nb_surat_dari = $pdf->NbLines($lebar_isi_kiri, $surat['asal_surat']);
$tinggi_baris1 = max(12, ($nb_surat_dari * $tinggi_baris_label) + 4);
$nb_nomor_surat = $pdf->NbLines($lebar_isi_kiri, $surat['nomor_surat_lengkap']);
$nb_nomor_agenda = $pdf->NbLines($lebar_isi_kanan, $surat['nomor_agenda_lengkap']);
$tinggi_baris2 = max(12, (max($nb_nomor_surat, $nb_nomor_agenda) * $tinggi_baris_label) + 4);
$tinggi_baris3 = 12;
$tinggi_total_atas = $tinggi_baris1 + $tinggi_baris2 + $tinggi_baris3;
$pdf->Rect($x, $y, $lebar_total, $tinggi_total_atas);
$pdf->Line($x + $lebar_kiri, $y, $x + $lebar_kiri, $y + $tinggi_total_atas);
$pdf->Line($x, $y + $tinggi_baris1, $x + $lebar_total, $y + $tinggi_baris1);
$pdf->Line($x, $y + $tinggi_baris1 + $tinggi_baris2, $x + $lebar_total, $y + $tinggi_baris1 + $tinggi_baris2);
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
$y_baris3 = $y + $tinggi_baris1 + $tinggi_baris2;
$pdf->SetXY($x + $padding_x, $y_baris3 + 3.5);
$pdf->Cell($lebar_label, $tinggi_baris_label, 'Tanggal Surat', 0, 0);
$pdf->Cell($lebar_colon, $tinggi_baris_label, ':', 0, 0);
$pdf->MultiCell($lebar_isi_kiri, $tinggi_baris_label, $surat['tgl_surat_formatted'], 0, 'L');
$pdf->SetXY($x + $lebar_kiri + $lebar_label, $y_baris3 + 1);
$pdf->Cell($lebar_colon, 4.5, ':', 0, 0);
$pdf->SetXY($x + $lebar_kiri + $padding_x, $y_baris3 + 1);
$pdf->Cell($lebar_label, 4.5, 'Sifat', 0, 0);
$sifat = $surat['sifat_surat'];
$pdf->SetY($y_baris3 + 6);
$x_pilihan_awal = $x + $lebar_kiri + $padding_x;
$pdf->SetX($x_pilihan_awal);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(5, 5, '', 1, 0);
if ($sifat == 'Biasa') { $pdf->SetFont('ZapfDingbats','',10); $pdf->Text($pdf->GetX() - 4.2, $pdf->GetY() + 3.8, '4'); $pdf->SetFont('Arial','',10); }
$pdf->Cell(20, 5, ' Biasa', 0, 0);
$pdf->Cell(5, 5, '', 1, 0);
if ($sifat == 'Amat Segera') { $pdf->SetFont('ZapfDingbats','',10); $pdf->Text($pdf->GetX() - 4.2, $pdf->GetY() + 3.8, '4'); $pdf->SetFont('Arial','',10); }
$pdf->Cell(28, 5, ' Amat Segera', 0, 0);
$pdf->Cell(5, 5, '', 1, 0);
if ($sifat == 'Penting') { $pdf->SetFont('ZapfDingbats','',10); $pdf->Text($pdf->GetX() - 4.2, $pdf->GetY() + 3.8, '4'); $pdf->SetFont('Arial','',10); }
$pdf->Cell(20, 5, ' Penting', 0, 1);
$pdf->SetFont('Arial', '', 11);
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
$pdf->Cell($lebar_total, 7, 'Diteruskan Kepada :', 'LTR', 1);
$pdf->Cell($lebar_total, 7, '1. Kepala Bagian Umum', 'LR', 1);
$pdf->Cell($lebar_total, 7, '2. Kepala Bagian Rapat dan Perundang-Undangan', 'LR', 1);
$pdf->Cell($lebar_total, 7, '3. Kepala Bagian Informasi dan Protokol', 'LBR', 1);
$pdf->Ln(2);
$pdf->Cell(35, 7, 'Isi Disposisi :', 'LT', 0);
$pdf->Cell($lebar_total - 35, 7, '', 'TR', 1);
$current_y = $pdf->GetY();
$sisa_tinggi = $pdf->GetPageHeight() - $current_y - $bottomMargin;
$pdf->Cell($lebar_total, $sisa_tinggi, '', 'LBR', 1);

// Output PDF
$pdf->Output('I', 'Disposisi_Dewan_' . str_replace('/', '_', $surat['nomor_agenda_lengkap']) . '.pdf');
?>
