<?php
// pages/dashboard.php

// Ambil data statistik untuk ditampilkan di kartu
$total_surat_masuk = $pdo->query("SELECT COUNT(id) FROM surat_masuk")->fetchColumn();
$total_surat_keluar = $pdo->query("SELECT COUNT(id) FROM surat_keluar")->fetchColumn();
$total_surat_masuk_dewan = $pdo->query("SELECT COUNT(id) FROM surat_masuk_dewan")->fetchColumn();
$total_surat_keluar_dewan = $pdo->query("SELECT COUNT(id) FROM surat_keluar_dewan")->fetchColumn();
$total_disposisi = $pdo->query("SELECT COUNT(id) FROM disposisi_sekwan")->fetchColumn();

// BARU: Query untuk menghitung surat masuk setwan yang belum didisposisi
$stmt = $pdo->prepare(
    "SELECT COUNT(sm.id)
     FROM surat_masuk sm
     LEFT JOIN disposisi_sekwan ds ON sm.id = ds.surat_masuk_id
     WHERE ds.id IS NULL"
);
$stmt->execute();
$surat_belum_disposisi = $stmt->fetchColumn();


// Data untuk 6 bulan terakhir (contoh statis)
$line_chart_labels = json_encode(['Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep']);
$line_chart_masuk = json_encode([210, 225, 240, 230, 250, 248]);
$line_chart_keluar = json_encode([180, 175, 190, 185, 195, 192]);


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
        <header class="text-center mb-12">
            <h1 class="text-4xl font-bold mb-4 text-gray-800">DASHBOARD PENOMORAN SURAT</h1>
            <h2 class="text-2xl font-semibold text-gray-600">SEKRETARIAT DPRD KOTA SURABAYA</h2>
            <div class="w-24 h-1 bg-gradient-to-r from-primary to-secondary mx-auto mt-4 rounded-full"></div>
        </header>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-12">
            <div class="card p-6 text-center">
                <h3 class="text-lg font-semibold mb-2 text-gray-600">Surat Masuk Setwan</h3>
                <div class="stats-number"><?php echo $total_surat_masuk; ?></div>
            </div>
            <div class="card p-6 text-center">
                <h3 class="text-lg font-semibold mb-2 text-gray-600">Surat Keluar Setwan</h3>
                <div class="stats-number"><?php echo $total_surat_keluar; ?></div>
            </div>
            <div class="card p-6 text-center">
                <h3 class="text-lg font-semibold mb-2 text-gray-600">Surat Masuk Dewan</h3>
                <div class="stats-number"><?php echo $total_surat_masuk_dewan; ?></div>
            </div>
            <div class="card p-6 text-center">
                <h3 class="text-lg font-semibold mb-2 text-gray-600">Surat Keluar Dewan</h3>
                <div class="stats-number"><?php echo $total_surat_keluar_dewan; ?></div>
            </div>
            <div class="card p-6 text-center">
                <h3 class="text-lg font-semibold mb-2 text-gray-600">Surat Setwan Terdisposisi</h3>
                <div class="stats-number"><?php echo $total_disposisi; ?></div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
            <div class="card p-6 lg:col-span-2">
                <h3 class="text-xl font-semibold mb-4 text-center text-gray-700">Trend Surat 6 Bulan Terakhir</h3>
                <div class="chart-container">
                    <canvas id="lineChart"></canvas>
                </div>
            </div>
            
            <a href="/disposisi-sekwan" class="card p-6 flex flex-col justify-center items-center <?php echo ($surat_belum_disposisi > 0) ? 'blinking-red' : 'blinking-green'; ?>">
                <h3 class="text-xl font-semibold mb-4 text-center text-gray-700">Perlu Tindak Lanjut</h3>
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
                <h3 class="text-xl font-semibold mb-4 text-center text-gray-700">Volume Surat Total</h3>
                <div class="chart-container">
                    <canvas id="barChart"></canvas>
                </div>
            </div>
            <div class="card p-6">
                <h3 class="text-xl font-semibold mb-4 text-center text-gray-700">Distribusi Surat</h3>
                <div class="chart-container">
                    <canvas id="pieChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
                options: { responsive: true, maintainAspectRatio: false }
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
                options: { responsive: true, maintainAspectRatio: false }
            });
            
            // Line Chart
            const lineCtx = document.getElementById('lineChart').getContext('2d');
            new Chart(lineCtx, {
                type: 'line',
                data: {
                    labels: lineChartLabels,
                    datasets: [
                        {
                            label: 'Surat Masuk',
                            data: lineChartMasuk,
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Surat Keluar',
                            data: lineChartKeluar,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        });
    </script>
</body>
</html>

<?php require_once 'templates/footer.php'; ?>