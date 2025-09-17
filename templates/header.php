<?php
// templates/header.php

// Ambil nama user dari session
$nama_user = $_SESSION['user_nama'] ?? 'User';
$inisial_user = strtoupper(substr($nama_user, 0, 1));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $pageTitle ?? 'Dashboard'; ?> - Reksurat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4f46e5',
                        secondary: '#6366f1',
                        dark: '#1f2937'
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-out',
                        'slide-in': 'slideIn 0.3s ease-out',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideIn: {
                            '0%': { transform: 'translateX(-100%)' },
                            '100%': { transform: 'translateX(0)' },
                        }
                    }
                }
            }
        }
    </script>
    <style type="text/css">
        .sidebar {
            transition: all 0.3s ease;
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed;
                z-index: 50;
                height: 100vh;
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 40;
            }
            .overlay.open {
                display: block;
            }
        }
        .nav-active {
            background-color: #4f46e5;
            color: white;
        }
        .nav-active:hover {
            background-color: #4338ca;
        }
        
        /* Fix untuk layout yang benar */
        body {
            overflow: hidden;
        }
        .main-container {
            height: calc(100vh - 80px); /* 80px adalah tinggi header */
            overflow-y: auto;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex" data-user-role="<?php echo $_SESSION['user_role'] ?? 'staff'; ?>">
    <div class="overlay" id="overlay"></div>

    <aside class="sidebar bg-white w-64 shadow-lg z-20 h-screen fixed transition-transform duration-300 ease-in-out" id="sidebar">
        <div class="px-5 h-20 flex flex-col justify-center border-b border-gray-200 bg-gradient-to-r from-primary to-secondary">
            <h1 class="text-xl font-bold text-white">Reksurat</h1>
            <p class="text-sm text-indigo-100">Dashboard Penomoran</p>
        </div>
        <nav class="mt-6">
            
            <a href="/dashboard" class="block py-3 px-5 mx-2 rounded-lg  <?php echo ($_GET['page'] ?? '') === 'dashboard' ? 'nav-active shadow-md' : 'text-gray-700 hover:bg-gray-100 hover:text-primary'; ?>">
                <i class="fas fa-tachometer-alt mr-3 w-5 text-center"></i> Dashboard
            </a>
            <div class="px-5 mt-4 mb-2">
                <p class="text-gray-500 text-xs uppercase font-bold tracking-wider">Sekretariat</p>
            </div>
            <?php if (in_array($_SESSION['user_role'], ['admin', 'staff surat keluar'])): ?>
                <a href="/surat-keluar" class="block py-3 px-5 mx-2 rounded-lg  <?php echo ($_GET['page'] ?? '') === 'surat-keluar' ? 'nav-active shadow-md' : 'text-gray-700 hover:bg-gray-100 hover:text-primary'; ?>">
                    <i class="fas fa-paper-plane mr-3 w-5 text-center"></i> Surat Keluar Setwan
                </a>
            <?php endif; ?>
            <?php if (in_array($_SESSION['user_role'], ['admin', 'staff surat masuk'])): ?>
                <a href="/surat-masuk" class="block py-3 px-5 mx-2 rounded-lg  <?php echo ($_GET['page'] ?? '') === 'surat-masuk' ? 'nav-active shadow-md' : 'text-gray-700 hover:bg-gray-100 hover:text-primary'; ?>">
                    <i class="fas fa-envelope mr-3 w-5 text-center"></i> Surat Masuk Setwan
                </a>
                <a href="/disposisi-sekwan" class="block py-3 px-5 mx-2 rounded-lg  <?php echo ($_GET['page'] ?? '') === 'disposisi-sekwan' ? 'nav-active shadow-md' : 'text-gray-700 hover:bg-gray-100 hover:text-primary'; ?>">
                    <i class="fas fa-share-square mr-3 w-5 text-center"></i> Disposisi Setwan
                </a>
            <?php endif; ?>

            <div class="px-5 mt-6 mb-2">
                <p class="text-gray-500 text-xs uppercase font-bold tracking-wider">Dewan</p>
            </div>
            <?php if (in_array($_SESSION['user_role'], ['admin', 'staff surat keluar'])): ?>
                <a href="/surat-keluar-dewan" class="block py-3 px-5 mx-2 rounded-lg  <?php echo ($_GET['page'] ?? '') === 'surat-keluar-dewan' ? 'nav-active shadow-md' : 'text-gray-700 hover:bg-gray-100 hover:text-primary'; ?>">
                    <i class="fas fa-paper-plane mr-3 w-5 text-center"></i> Surat Keluar Dewan
                </a>
            <?php endif; ?>
            <?php if (in_array($_SESSION['user_role'], ['admin', 'staff surat masuk'])): ?>
                <a href="/surat-masuk-dewan" class="block py-3 px-5 mx-2 rounded-lg  <?php echo ($_GET['page'] ?? '') === 'surat-masuk-dewan' ? 'nav-active shadow-md' : 'text-gray-700 hover:bg-gray-100 hover:text-primary'; ?>">
                    <i class="fas fa-envelope mr-3 w-5 text-center"></i> Surat Masuk Dewan
                </a>
            <?php endif; ?>
            
            <div class="px-5 mt-8 mb-2">
                <p class="text-gray-500 text-xs uppercase font-bold tracking-wider">Sistem</p>
            </div>
             <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <a href="/users" class="block py-3 px-5 mx-2 rounded-lg  <?php echo ($_GET['page'] ?? '') === 'users' ? 'nav-active shadow-md' : 'text-gray-700 hover:bg-gray-100 hover:text-primary'; ?>">
                    <i class="fas fa-users-cog mr-3 w-5 text-center"></i> Manajemen User
                </a>
                <a href="/log-user" class="block py-3 px-5 mx-2 rounded-lg  <?php echo ($_GET['page'] ?? '') === 'log-user' ? 'nav-active shadow-md' : 'text-gray-700 hover:bg-gray-100 hover:text-primary'; ?>">
                    <i class="fas fa-history mr-3 w-5 text-center"></i> Log User
                </a>
            <?php endif; ?>
            <a href="/logout" class="block py-3 px-5 mx-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-primary ">
                <i class="fas fa-sign-out-alt mr-3 w-5 text-center"></i> Keluar
            </a>
        </nav>
    </aside>

    <div class="flex-1 flex flex-col md:ml-64">
        <header class="sticky top-0 z-10 h-20 bg-indigo-100/80 backdrop-blur-lg border-b border-indigo-200/80">
            <div class="px-6 h-full flex items-center justify-between">
                <div class="flex items-center">
                    <button class="md:hidden text-gray-600 bg-gray-100 p-2 rounded-lg mr-4 shadow-sm hover:bg-gray-200 transition-colors" id="menu-toggle">
                        <i class="fas fa-bars text-lg"></i>
                    </button>
                    <h2 class="text-xl font-semibold text-gray-700 "><?php echo $pageTitle ?? 'Dashboard'; ?></h2>
                </div>
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-primary to-secondary flex items-center justify-center text-white font-bold shadow-md hover:shadow-lg transition-shadow cursor-pointer">
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
