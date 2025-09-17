<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Halaman Tidak Ditemukan</title>
    <!-- Menggunakan Tailwind CSS agar konsisten dengan proyek -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        /* Style untuk animasi sederhana */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.8s ease-out forwards;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="text-center p-8">
        <div class="mb-4">
             
        </div>
        <h1 class="text-6xl md:text-9xl font-bold text-indigo-600">404</h1>
        <p class="text-2xl md:text-3xl font-light text-gray-800 mt-4">
            Halaman Tidak Ditemukan
        </p>
        <p class="text-gray-500 mt-4 mb-8">
            Maaf, halaman yang Anda cari mungkin telah dipindahkan atau tidak ada lagi.
        </p>
        <a href="/dashboard" class="px-8 py-3 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-transform transform hover:scale-105 duration-300">
            Kembali ke Dashboard
        </a>
    </div>
</body>
</html>
