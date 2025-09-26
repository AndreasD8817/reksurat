<?php
// templates/header.php

// Ambil data user dari session
$nama_user = $_SESSION['user_nama'] ?? 'User';
$inisial_user = strtoupper(substr($nama_user, 0, 1));
$user_role = $_SESSION['user_role'] ?? null;

// --- Logika Hak Akses Menu ---
// Hak akses untuk menu Surat Keluar (Setwan & Dewan)
$can_access_surat_keluar = in_array($user_role, ['superadmin', 'admin', 'staff surat keluar']);
// Hak akses untuk menu Surat Masuk (Setwan & Dewan)
$can_access_surat_masuk = in_array($user_role, ['superadmin', 'admin', 'staff surat masuk']);
// Hak akses untuk menu Disposisi (Berdasarkan file disposisi-sekwan.php, staff surat masuk juga bisa akses)
$can_access_disposisi = in_array($user_role, ['superadmin', 'admin', 'staff surat masuk']);
// Hak akses untuk menu Sistem (hanya superadmin)
$can_access_sistem = ($user_role === 'superadmin');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $pageTitle ?? 'Dashboard'; ?> - Reksurat</title>
    <link rel="icon" href="/assets/img/ArekSurat favicon.png" type="image/png">
    <!-- <script src="https://cdn.tailwindcss.com"></script> -->
     <link href="/assets/css/output.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50 min-h-screen flex" data-user-role="<?php echo $_SESSION['user_role'] ?? 'staff'; ?>">
    <div class="overlay" id="overlay"></div>

    <aside class="sidebar bg-white w-64 shadow-lg z-50 h-screen fixed md:fixed transition-transform duration-300 ease-in-out flex flex-col" id="sidebar">
        <div class="px-4 h-20 flex items-center gap-3 border-b border-gray-200 bg-gradient-to-r from-primary to-secondary">
            <img src="/assets/img/ArekSurat icon.png" alt="Logo Aplikasi ArekSurat" class="h-12 w-12">
            <div>
                <h1 class="text-xl font-bold text-white">ArekSurat</h1>
                <p class="text-sm text-indigo-100">Dashboard Penomoran</p>
            </div>
        </div>
        <nav class="mt-6 flex-grow overflow-y-auto">
            
            <a href="/dashboard" class="block py-3 px-5 mx-2 rounded-lg <?php echo ($_GET['route'] ?? '') === 'dashboard' ? 'nav-active shadow-md' : 'text-gray-700 hover:bg-gray-100 hover:text-primary'; ?>">
                <i class="fas fa-tachometer-alt mr-3 w-5 text-center"></i> Dashboard
            </a>

            <!-- Sekretariat Section -->
            <details open class="group">
                <summary class="flex items-center justify-between px-5 mt-4 mb-2 cursor-pointer">
                    <p class="text-gray-500 text-xs uppercase font-bold tracking-wider">Sekretariat</p>
                    <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform duration-200"></i>
                </summary>
                <div class="pb-2">
                    <?php if ($can_access_surat_keluar): ?>
                        <a href="/surat-keluar" class="block py-3 px-5 mx-2 rounded-lg <?php echo ($_GET['route'] ?? '') === 'surat-keluar' ? 'nav-active shadow-md' : 'text-gray-700 hover:bg-gray-100 hover:text-primary'; ?>">
                            <i class="fas fa-paper-plane mr-3 w-5 text-center"></i> Surat Keluar Setwan
                        </a>
                    <?php endif; ?>
                    <?php if ($can_access_surat_masuk): ?>
                        <a href="/surat-masuk" class="block py-3 px-5 mx-2 rounded-lg <?php echo ($_GET['route'] ?? '') === 'surat-masuk' ? 'nav-active shadow-md' : 'text-gray-700 hover:bg-gray-100 hover:text-primary'; ?>">
                            <i class="fas fa-envelope mr-3 w-5 text-center"></i> Surat Masuk Setwan
                        </a>
                    <?php endif; ?>
                    <?php if ($can_access_disposisi): ?>
                        <a href="/disposisi-sekwan" class="block py-3 px-5 mx-2 rounded-lg <?php echo ($_GET['route'] ?? '') === 'disposisi-sekwan' ? 'nav-active shadow-md' : 'text-gray-700 hover:bg-gray-100 hover:text-primary'; ?>">
                            <i class="fas fa-share-square mr-3 w-5 text-center"></i> Disposisi Setwan
                        </a>
                    <?php endif; ?>
                </div>
            </details>

            <!-- Dewan Section -->
            <details open class="group">
                <summary class="flex items-center justify-between px-5 mt-6 mb-2 cursor-pointer">
                    <p class="text-gray-500 text-xs uppercase font-bold tracking-wider">Dewan</p>
                    <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform duration-200"></i>
                </summary>
                <div class="pb-2">
                    <?php if ($can_access_surat_keluar): ?>
                        <a href="/surat-keluar-dewan" class="block py-3 px-5 mx-2 rounded-lg <?php echo ($_GET['route'] ?? '') === 'surat-keluar-dewan' ? 'nav-active shadow-md' : 'text-gray-700 hover:bg-gray-100 hover:text-primary'; ?>">
                            <i class="fas fa-paper-plane mr-3 w-5 text-center"></i> Surat Keluar Dewan
                        </a>
                    <?php endif; ?>
                    <?php if ($can_access_surat_masuk): ?>
                        <a href="/surat-masuk-dewan" class="block py-3 px-5 mx-2 rounded-lg <?php echo ($_GET['route'] ?? '') === 'surat-masuk-dewan' ? 'nav-active shadow-md' : 'text-gray-700 hover:bg-gray-100 hover:text-primary'; ?>">
                            <i class="fas fa-envelope mr-3 w-5 text-center"></i> Surat Masuk Dewan
                        </a>
                    <?php endif; ?>
                    <?php if ($can_access_disposisi): ?>
                        <a href="/disposisi-dewan" class="block py-3 px-5 mx-2 rounded-lg <?php echo ($_GET['route'] ?? '') === 'disposisi-dewan' ? 'nav-active shadow-md' : 'text-gray-700 hover:bg-gray-100 hover:text-primary'; ?>">
                            <i class="fas fa-share-square mr-3 w-5 text-center"></i> Disposisi Dewan
                        </a>
                    <?php endif; ?>
                </div>
            </details>
            
            <?php if ($can_access_sistem): ?>
                <!-- Sistem Section -->
                <details open class="group">
                    <summary class="flex items-center justify-between px-5 mt-8 mb-2 cursor-pointer">
                        <p class="text-gray-500 text-xs uppercase font-bold tracking-wider">Sistem</p>
                        <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform duration-200"></i>
                    </summary>
                    <div class="pb-2">
                        <a href="/users" class="block py-3 px-5 mx-2 rounded-lg <?php echo ($_GET['route'] ?? '') === 'users' ? 'nav-active shadow-md' : 'text-gray-700 hover:bg-gray-100 hover:text-primary'; ?>">
                            <i class="fas fa-users-cog mr-3 w-5 text-center"></i> Manajemen User
                        </a>
                        <a href="/klasifikasi-arsip" class="block py-3 px-5 mx-2 rounded-lg <?php echo ($_GET['route'] ?? '') === 'klasifikasi-arsip' ? 'nav-active shadow-md' : 'text-gray-700 hover:bg-gray-100 hover:text-primary'; ?>">
                            <i class="fas fa-archive mr-3 w-5 text-center"></i> Klasifikasi Arsip
                        </a>
                        <a href="/log-user" class="block py-3 px-5 mx-2 rounded-lg <?php echo ($_GET['route'] ?? '') === 'log-user' ? 'nav-active shadow-md' : 'text-gray-700 hover:bg-gray-100 hover:text-primary'; ?>">
                            <i class="fas fa-history mr-3 w-5 text-center"></i> Log User
                        </a>
                    </div>
                </details>
            <?php endif; ?>
            <a href="/logout" class="block py-3 px-5 mx-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-primary ">
                <i class="fas fa-sign-out-alt mr-3 w-5 text-center"></i> Keluar
            </a>
        </nav>
    </aside>

    <div class="flex-1 flex flex-col md:ml-64 main-content">
        <header class="sticky top-0 z-10 h-20 bg-indigo-100/80 backdrop-blur-lg border-b border-indigo-200/80">
            <div class="px-6 h-full flex items-center justify-between">
                <div class="flex items-center">
                    <button class="md:hidden text-gray-600 bg-gray-100 p-2 rounded-lg mr-4 shadow-sm hover:bg-gray-200 transition-colors" id="menu-toggle">
                        <i class="fas fa-bars text-lg"></i>
                    </button>
                    <h2 class="text-xl font-semibold text-gray-700"><?php echo $pageTitle ?? 'Dashboard'; ?></h2>
                </div>
                <div class="flex items-center cursor-pointer">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-primary to-secondary flex-shrink-0 flex items-center justify-center text-white font-bold shadow-md hover:shadow-lg transition-shadow leading-none">
                        <?php echo htmlspecialchars($inisial_user); ?>
                    </div>
                    <span class="text-gray-700 font-medium text-sm ml-3 hidden md:block">
                        <?php echo htmlspecialchars($nama_user); ?>
                    </span>
                </div>
            </div>
        </header>

        <div class="main-container">
            <main class="p-6">