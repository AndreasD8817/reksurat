<?php
// pages/db_error.php
// Halaman ini dirancang untuk menjadi mandiri tanpa dependensi eksternal
// yang mungkin gagal jika koneksi database terputus.

// Set kode status HTTP yang sesuai untuk masalah server
http_response_code(503); // 503 Service Unavailable
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masalah Koneksi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .gradient-bg {
            background: linear-gradient(-45deg, #6b7280, #374151, #1f2937, #111827);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4 text-white">
    <div class="w-full max-w-lg text-center">
        <div class="bg-white/10 backdrop-blur-lg rounded-2xl shadow-2xl p-8 md:p-12">
            <div class="mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 mx-auto text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h1 class="text-3xl md:text-4xl font-bold mb-3">
                Terjadi Masalah Koneksi
            </h1>
            <p class="text-gray-300 text-lg mb-6">
                Kami sedang mengalami kendala teknis pada sistem. Tim kami sedang menanganinya.
            </p>
            <p class="text-gray-400">
                Silakan coba lagi dalam beberapa saat.
            </p>
        </div>
        <p class="mt-6 text-sm text-gray-400">ArekSurat &copy; <?php echo date('Y'); ?></p>
    </div>
</body>
</html>