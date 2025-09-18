<?php
// pages/cetak-laporan-tahunan.php

require_once __DIR__ . '/../libs/fpdf/fpdf.php';

if (!isset($_SESSION['user_id'])) {
    die("Akses ditolak. Silakan login terlebih dahulu.");
}

$tipe = $_GET['tipe'] ?? null;
$tahun = $_GET['tahun'] ?? null;

if (!$tipe || !$tahun || !is_numeric($tahun)) {
    die("Parameter tidak valid.");
}

// Konfigurasi untuk setiap jenis laporan
$report_configs = [
    'surat-masuk' => [
        'title' => 'Laporan Tahunan Surat Masuk Sekretariat',
        'table' => 'surat_masuk',
        'date_col' => 'tanggal_diterima',
        'logo' => 'Logo_Pemkot.png',
        'kop1' => 'PEMERINTAH KOTA SURABAYA',
        'kop2' => 'SEKRETARIAT DPRD',
        'headers' => ['No. Agenda', 'Asal Surat', 'Perihal', 'Tgl Diterima'],
        'columns' => ['nomor_agenda_lengkap', 'asal_surat', 'perihal', 'tgl_diterima_formatted'],
        'col_widths' => [40, 45, 70, 25]
    ],
    'surat-keluar' => [
        'title' => 'Laporan Tahunan Surat Keluar Sekretariat',
        'table' => 'surat_keluar',
        'date_col' => 'tanggal_surat',
        'logo' => 'Logo_Pemkot.png',
        'kop1' => 'PEMERINTAH KOTA SURABAYA',
        'kop2' => 'SEKRETARIAT DPRD',
        'headers' => ['No. Surat', 'Tujuan', 'Perihal', 'Tgl Surat'],
        'columns' => ['nomor_surat_lengkap', 'tujuan', 'perihal', 'tgl_surat_formatted'],
        'col_widths' => [40, 45, 70, 25]
    ],
    'surat-masuk-dewan' => [
        'title' => 'Laporan Tahunan Surat Masuk Dewan',
        'table' => 'surat_masuk_dewan',
        'date_col' => 'tanggal_diterima',
        'logo' => 'Logo_DPRD.png',
        'kop1' => 'DEWAN PERWAKILAN RAKYAT DAERAH',
        'kop2' => 'KOTA SURABAYA',
        'headers' => ['No. Agenda', 'Asal Surat', 'Perihal', 'Tgl Diterima'],
        'columns' => ['nomor_agenda_lengkap', 'asal_surat', 'perihal', 'tgl_diterima_formatted'],
        'col_widths' => [40, 45, 70, 25]
    ],
    'surat-keluar-dewan' => [
        'title' => 'Laporan Tahunan Surat Keluar Dewan',
        'table' => 'surat_keluar_dewan',
        'date_col' => 'tanggal_surat',
        'logo' => 'Logo_DPRD.png',
        'kop1' => 'DEWAN PERWAKILAN RAKYAT DAERAH',
        'kop2' => 'KOTA SURABAYA',
        'headers' => ['No. Surat', 'Tujuan', 'Perihal', 'Tgl Surat'],
        'columns' => ['nomor_surat_lengkap', 'tujuan', 'perihal', 'tgl_surat_formatted'],
        'col_widths' => [40, 45, 70, 25]
    ],
    'disposisi-sekwan' => [
        'title' => 'Laporan Tahunan Disposisi Sekretaris Dewan',
        'table' => 'disposisi_sekwan ds JOIN surat_masuk sm ON ds.surat_masuk_id = sm.id',
        'date_col' => 'ds.tanggal_disposisi',
        'logo' => 'Logo_Pemkot.png',
        'kop1' => 'PEMERINTAH KOTA SURABAYA',
        'kop2' => 'SEKRETARIAT DPRD',
        'headers' => ['No. Agenda', 'Perihal Surat', 'Pegawai Tertuju', 'Tgl Disposisi'],
        'columns' => ['nomor_agenda_lengkap', 'perihal', 'nama_pegawai', 'tgl_disposisi_formatted'],
        'col_widths' => [40, 70, 45, 25]
    ]
];

if (!array_key_exists($tipe, $report_configs)) {
    die("Jenis laporan tidak valid.");
}

$config = $report_configs[$tipe];

// Ambil data dari database
$date_col_formatted = str_replace('ds.', '', $config['date_col']); // Hapus prefix jika ada
$sql = "SELECT *, DATE_FORMAT({$config['date_col']}, '%d-%m-%Y') as tgl_diterima_formatted, DATE_FORMAT({$config['date_col']}, '%d-%m-%Y') as tgl_surat_formatted, DATE_FORMAT({$config['date_col']}, '%d-%m-%Y') as tgl_disposisi_formatted FROM {$config['table']} WHERE YEAR({$config['date_col']}) = ? ORDER BY {$config['date_col']} ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$tahun]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

class PDF extends FPDF {
    private $reportConfig;
    private $reportYear;
    // Properti untuk tabel multi-baris
    var $widths;
    var $aligns;

    function setReportConfig($config, $year) {
        $this->reportConfig = $config;
        $this->reportYear = $year;
    }

    function Header() {
        $logoPath = 'assets/img/' . $this->reportConfig['logo'];
        $this->Image($logoPath, 10, 8, 25);

        $this->SetFont('Arial', 'B', 15);
        $this->Cell(80);
        $this->Cell(30, 7, $this->reportConfig['kop1'], 0, 1, 'C');
        $this->SetFont('Arial', 'B', 18);
        $this->Cell(80);
        $this->Cell(30, 9, $this->reportConfig['kop2'], 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(80);
        $this->Cell(30, 5, 'Jalan Yos Sudarso Nomor 18 - 22 Surabaya - 60272', 0, 1, 'C');
        $this->Ln(5);
        // $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(5);

        $this->SetFont('Arial', 'BU', 14);
        $this->Cell(0, 8, strtoupper($this->reportConfig['title']), 0, 1, 'C');
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 6, 'TAHUN ' . $this->reportYear, 0, 1, 'C');
        $this->Ln(5);

        // Table Header
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(230, 230, 230);
        $this->SetTextColor(0);
        $this->SetDrawColor(128, 128, 128);
        $this->Cell(10, 7, 'No', 1, 0, 'C', true);
        for ($i = 0; $i < count($this->reportConfig['headers']); $i++) {
            $this->Cell($this->reportConfig['col_widths'][$i], 7, $this->reportConfig['headers'][$i], 1, 0, 'C', true);
        }
        $this->Ln();
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    // --- FUNGSI HELPER UNTUK TABEL MULTI-BARIS ---

    function SetWidths($w) {
        // Set array lebar kolom
        $this->widths = $w;
    }

    function SetAligns($a) {
        // Set array perataan kolom
        $this->aligns = $a;
    }

    function Row($data) {
        // Hitung tinggi baris
        $nb = 0;
        for ($i = 0; $i < count($data); $i++) {
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        }
        $h = 5 * $nb;
        // Buat page break jika perlu
        $this->CheckPageBreak($h);
        // Gambar sel-sel dalam baris
        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            // Simpan posisi saat ini
            $x = $this->GetX();
            $y = $this->GetY();
            // Gambar border
            $this->Rect($x, $y, $w, $h);
            // Cetak teks
            $this->MultiCell($w, 5, $data[$i], 0, $a);
            // Kembalikan posisi ke sebelah kanan sel
            $this->SetXY($x + $w, $y);
        }
        // Pindah ke baris baru
        $this->Ln($h);
    }

    function CheckPageBreak($h) {
        // Jika tinggi baris akan melewati batas halaman, tambahkan halaman baru
        if ($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
        }
    }

    function NbLines($w, $txt) {
        // Hitung jumlah baris yang akan digunakan oleh sebuah MultiCell
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] == "\n") $nb--;
        $sep = -1; $i = 0; $j = 0; $l = 0; $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") { $i++; $sep = -1; $j = $i; $l = 0; $nl++; continue; }
            if ($c == ' ') $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) { if ($i == $j) $i++; } 
                else $i = $sep + 1;
                $sep = -1; $j = $i; $l = 0; $nl++;
            } else $i++;
        }
        return $nl;
    }

    // Fungsi untuk menangani teks yang panjang dan memotongnya jika perlu
    function SafeCell($w, $h, $txt, $border, $ln, $align, $fill) {
        $this->Cell($w, $h, $this->TruncateText($txt, $w), $border, $ln, $align, $fill);
    }

    private function TruncateText($text, $width) {
        if ($this->GetStringWidth($text) <= $width - 2) { // -2 for margin
            return $text;
        }
        while ($this->GetStringWidth($text . '...') > $width - 2) {
            $text = substr($text, 0, -1);
        }
        return $text . '...';
    }
}

$pdf = new PDF('P', 'mm', 'A4');
$pdf->setReportConfig($config, $tahun);
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 9);

$no = 1;
if (empty($data)) {
    $pdf->Cell(0, 10, 'Tidak ada data untuk ditampilkan.', 1, 1, 'C');
} else {
    // Atur lebar dan perataan kolom untuk data
    $pdf->SetWidths(array_merge([10], $config['col_widths']));
    $pdf->SetAligns(array_merge(['C'], array_fill(0, count($config['headers']), 'L')));

    foreach ($data as $row) {
        $rowData = [$no++];
        foreach ($config['columns'] as $colName) {
            // Bersihkan data dari tag HTML dan decode entitas
            $cellData = html_entity_decode(strip_tags($row[$colName] ?? ''));
            $rowData[] = $cellData;
        }
        // Gunakan fungsi Row yang baru ditambahkan
        $pdf->Row($rowData);
    }
}

$pdf->Output('I', 'Laporan_' . str_replace('-', '_', $tipe) . '_' . $tahun . '.pdf');

?>