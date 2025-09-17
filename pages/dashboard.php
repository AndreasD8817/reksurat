<?php
// pages/dashboard.php

// --- 1. PENGATURAN TAHUN ---
$selected_year = $_GET['tahun'] ?? date('Y');

// --- 2. FUNGSI HELPER & PENGAMBILAN DATA ---

/**
 * Mengambil total surat dari tabel tertentu untuk tahun yang dipilih.
 * @param PDO $pdo Koneksi PDO.
 * @param string $table Nama tabel.
 * @param string $date_column Nama kolom tanggal untuk filter tahun.
 * @param int $year Tahun yang dipilih.
 * @return int Jumlah total.
 */
function get_total_surat_by_year(PDO $pdo, string $table, string $date_column, int $year): int {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(id) FROM $table WHERE YEAR($date_column) = ?");
        $stmt->execute([$year]);
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error getting total surat from $table: " . $e->getMessage());
        return 0;
    }
}

// Ambil data statistik untuk kartu berdasarkan tahun yang dipilih
$total_surat_masuk = get_total_surat_by_year($pdo, 'surat_masuk', 'tanggal_diterima', $selected_year);
$total_surat_keluar = get_total_surat_by_year($pdo, 'surat_keluar', 'tanggal_surat', $selected_year);
$total_surat_masuk_dewan = get_total_surat_by_year($pdo, 'surat_masuk_dewan', 'tanggal_diterima', $selected_year);
$total_surat_keluar_dewan = get_total_surat_by_year($pdo, 'surat_keluar_dewan', 'tanggal_surat', $selected_year);
$total_disposisi = get_total_surat_by_year($pdo, 'disposisi_sekwan', 'tanggal_disposisi', $selected_year);

// Query untuk menghitung surat masuk setwan yang belum didisposisi untuk tahun yang dipilih
$stmt = $pdo->prepare(
    "SELECT COUNT(sm.id)
     FROM surat_masuk sm
     LEFT JOIN disposisi_sekwan ds ON sm.id = ds.surat_masuk_id 
     WHERE ds.id IS NULL AND YEAR(sm.tanggal_diterima) = ?"
);
$stmt->execute([$selected_year]);
$surat_belum_disposisi = $stmt->fetchColumn();

// Ambil data untuk line chart (12 bulan dalam tahun yang dipilih)
$line_chart_labels = [];
$line_chart_masuk = [];
$line_chart_keluar = [];

for ($month = 1; $month <= 12; $month++) {
    $line_chart_labels[] = date('M', mktime(0, 0, 0, $month, 1));

    // Query untuk surat masuk
    $stmt = $pdo->prepare("SELECT COUNT(id) FROM surat_masuk WHERE MONTH(tanggal_diterima) = ? AND YEAR(tanggal_diterima) = ?");
    $stmt->execute([$month, $selected_year]);
    $line_chart_masuk[] = (int)$stmt->fetchColumn();

    // Query untuk surat keluar
    $stmt = $pdo->prepare("SELECT COUNT(id) FROM surat_keluar WHERE MONTH(tanggal_surat) = ? AND YEAR(tanggal_surat) = ?");
    $stmt->execute([$month, $selected_year]);
    $line_chart_keluar[] = (int)$stmt->fetchColumn();
}

// Encode ke JSON
$line_chart_labels = json_encode($line_chart_labels);
$line_chart_masuk = json_encode($line_chart_masuk);
$line_chart_keluar = json_encode($line_chart_keluar);

// --- 3. LOGIKA UNTUK DROPDOWN TAHUN ---
$stmt_years = $pdo->query(
    "(SELECT DISTINCT YEAR(tanggal_surat) as y FROM surat_keluar) UNION " .
    "(SELECT DISTINCT YEAR(tanggal_diterima) as y FROM surat_masuk) UNION " .
    "(SELECT DISTINCT YEAR(tanggal_surat) as y FROM surat_keluar_dewan) UNION " .
    "(SELECT DISTINCT YEAR(tanggal_diterima) as y FROM surat_masuk_dewan)"
);
$db_years = $stmt_years->fetchAll(PDO::FETCH_COLUMN);
$all_years = array_unique(array_merge($db_years, [date('Y'), date('Y') + 1]));
rsort($all_years); // Urutkan dari terbaru ke terlama

$pageTitle = 'Dashboard';
require_once 'templates/header.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penomoran Surat - Sekretariat DPRD Kota Surabaya</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 16px;
            border: 1px solid rgba(229, 231, 235, 1);
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); 
        }
        
        .chart-container {
            position: relative; 
            height: 300px;
            padding: 1rem;
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(to right, #4f46e5, #6366f1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* CSS untuk notifikasi berkedip */
        @keyframes blink-red-glow {
            50% {
                box-shadow: 0 0 20px rgba(239, 68, 68, 0.6);
                border-color: rgba(239, 68, 68, 0.7);
            }
        }
        .blinking-red {
            animation: blink-red-glow 1.5s linear infinite;
        }

        @keyframes blink-green-glow {
             50% {
                box-shadow: 0 0 20px rgba(16, 185, 129, 0.6);
                border-color: rgba(16, 185, 129, 0.7);
            }
        }
        .blinking-green {
            animation: blink-green-glow 2s linear infinite;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col md:flex-row justify-between items-center mb-10">
            <header class="text-center md:text-left">
                <h1 class="text-4xl font-bold text-gray-800">Dashboard Penomoran Surat</h1>
                <h2 class="text-2xl font-semibold text-gray-600">Sekretariat DPRD Kota Surabaya</h2>
                <div class="w-24 h-1 bg-gradient-to-r from-primary to-secondary mt-4 rounded-full mx-auto md:mx-0"></div>
            </header>
            
            <!-- Filter Tahun -->
            <div class="mt-6 md:mt-0">
                <form method="GET" action="/dashboard" class="flex items-center gap-3 bg-white p-2 rounded-xl shadow-sm border">
                    <label for="tahun" class="text-sm font-medium text-gray-600">Tampilkan Data Tahun:</label>
                    <select name="tahun" id="tahun" class="w-32 px-3 py-2 rounded-lg border-gray-300 bg-gray-50 focus:ring-2 focus:ring-indigo-300" onchange="this.form.submit()">
                        <?php foreach ($all_years as $year): ?>
                            <option value="<?php echo $year; ?>" <?php echo ($year == $selected_year) ? 'selected' : ''; ?>>
                                <?php echo $year; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-12">
            <div class="card p-6 text-center">
                <h3 class="text-lg font-semibold mb-2 text-gray-600">Surat Masuk Setwan</h3> 
                <div class="stats-number" title="Total untuk tahun <?php echo $selected_year; ?>"><?php echo $total_surat_masuk; ?></div>
            </div>
            <div class="card p-6 text-center">
                <h3 class="text-lg font-semibold mb-2 text-gray-600">Surat Keluar Setwan</h3> 
                <div class="stats-number" title="Total untuk tahun <?php echo $selected_year; ?>"><?php echo $total_surat_keluar; ?></div>
            </div>
            <div class="card p-6 text-center">
                <h3 class="text-lg font-semibold mb-2 text-gray-600">Surat Masuk Dewan</h3> 
                <div class="stats-number" title="Total untuk tahun <?php echo $selected_year; ?>"><?php echo $total_surat_masuk_dewan; ?></div>
            </div>
            <div class="card p-6 text-center">
                <h3 class="text-lg font-semibold mb-2 text-gray-600">Surat Keluar Dewan</h3> 
                <div class="stats-number" title="Total untuk tahun <?php echo $selected_year; ?>"><?php echo $total_surat_keluar_dewan; ?></div>
            </div>
            <div class="card p-6 text-center">
                <h3 class="text-lg font-semibold mb-2 text-gray-600">Surat Setwan Terdisposisi</h3>
                <div class="stats-number" title="Total untuk tahun <?php echo $selected_year; ?>"><?php echo $total_disposisi; ?></div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
            <div class="card p-6 lg:col-span-2">
                <h3 class="text-xl font-semibold mb-4 text-center text-gray-700">Grafik Surat Setwan Tahun <?php echo $selected_year; ?></h3>
                <div class="chart-container">
                    <canvas id="lineChart"></canvas>
                </div>
            </div>
            
            <a href="/disposisi-sekwan" class="card p-6 flex flex-col justify-center items-center <?php echo ($surat_belum_disposisi > 0) ? 'blinking-red' : 'blinking-green'; ?>">
                <h3 class="text-xl font-semibold mb-4 text-center text-gray-700">Perlu Disposisi (<?php echo $selected_year; ?>)</h3>
                <div class="text-6xl font-bold text-gray-800 my-4">
                    <?php echo $surat_belum_disposisi; ?>
                </div>
                <p class="text-center text-gray-600 font-medium">
                    <?php
                    if ($surat_belum_disposisi > 0) {
                        echo "Surat Masuk Setwan<br>menunggu untuk didisposisi.";
                    } else {
                        echo "Semua Surat Masuk Setwan<br>telah berhasil didisposisi.";
                    }
                    ?>
                </p>
                <span class="mt-4 text-sm font-semibold text-primary group-hover:underline">Lihat Detail &rarr;</span>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="card p-6">
                <h3 class="text-xl font-semibold mb-4 text-center text-gray-700">Volume Surat Tahun <?php echo $selected_year; ?></h3>
                <div class="chart-container">
                    <canvas id="barChart"></canvas>
                </div>
            </div>
            <div class="card p-6">
                <h3 class="text-xl font-semibold mb-4 text-center text-gray-700">Distribusi Surat Tahun <?php echo $selected_year; ?></h3>
                <div class="chart-container">
                    <canvas id="pieChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectedYear = '<?php echo $selected_year; ?>';
            // Data dari PHP
            const totalSuratMasuk = <?php echo $total_surat_masuk; ?>;
            const totalSuratKeluar = <?php echo $total_surat_keluar; ?>;
            const totalSuratMasukDewan = <?php echo $total_surat_masuk_dewan; ?>;
            const totalSuratKeluarDewan = <?php echo $total_surat_keluar_dewan; ?>;
            const totalDisposisi = <?php echo $total_disposisi; ?>;
            const lineChartLabels = <?php echo $line_chart_labels; ?>;
            const lineChartMasuk = <?php echo $line_chart_masuk; ?>;
            const lineChartKeluar = <?php echo $line_chart_keluar; ?>;

            Chart.defaults.font.family = "'Poppins', sans-serif";

            // Bar Chart
            const barCtx = document.getElementById('barChart').getContext('2d');
            new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: ['Surat Masuk Setwan', 'Surat Keluar Setwan', 'Surat Masuk Dewan', 'Surat Keluar Dewan'],
                    datasets: [{
                        label: 'Jumlah Surat',
                        data: [totalSuratMasuk, totalSuratKeluar, totalSuratMasukDewan, totalSuratKeluarDewan],
                        backgroundColor: ['#4f46e5', '#6366f1', '#f59e0b', '#10b981'],
                        borderRadius: 5 
                    }]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } } // Sembunyikan legend karena sudah jelas dari label
                }
            });
            
            // Pie Chart
            const pieCtx = document.getElementById('pieChart').getContext('2d');
            new Chart(pieCtx, {
                type: 'doughnut', // doughnut lebih modern
                data: {
                    labels: ['Surat Masuk Setwan', 'Surat Keluar Setwan', 'Surat Masuk Dewan', 'Surat Keluar Dewan'],
                    datasets: [{
                        data: [totalSuratMasuk, totalSuratKeluar, totalSuratMasukDewan, totalSuratKeluarDewan],
                        backgroundColor: ['#4f46e5', '#6366f1', '#f59e0b', '#10b981'] 
                    }]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'top' } }
                }
            });
            
            // Line Chart
            const lineCtx = document.getElementById('lineChart').getContext('2d');
            new Chart(lineCtx, {
                type: 'line',
                data: {
                    labels: lineChartLabels,
                    datasets: [
                        {
                            label: 'Surat Masuk Setwan',
                            data: lineChartMasuk,
                            borderColor: '#ef4444', 
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Surat Keluar Setwan',
                            data: lineChartKeluar, 
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } } }
            });
        });
    </script>
</body>
</html>

<?php require_once 'templates/footer.php'; ?>