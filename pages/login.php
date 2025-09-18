<?php
// pages/login.php

// Jika sudah login, langsung arahkan ke dashboard
if (isLoggedIn()) {
    header('Location: /dashboard');
    exit;
}

$error = '';
// Cek jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifikasi CSRF Token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        // Jangan berikan pesan error yang terlalu spesifik
        $error = 'Terjadi kesalahan. Silakan coba lagi.';
        // Regenerate token untuk form
        $csrf_token = generate_csrf_token();
    } else {
    // ---- PERUBAHAN 1: Mengambil 'username' dari POST ----
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // ---- PERUBAHAN 2: Mencari user berdasarkan 'username' ----
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verifikasi password (logika ini tidak berubah)
    if ($user && password_verify($password, $user['password'])) {
        // Jika berhasil, simpan data user ke session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nama'] = $user['nama'];
        $_SESSION['user_role'] = $user['role'];
        header('Location: /dashboard'); // Arahkan ke halaman surat keluar
        exit;
    } else {
        // ---- PERUBAHAN 3: Memperbarui pesan error ----
        $error = 'Username atau kata sandi salah!';
    }
    }
}

// Generate CSRF token jika belum ada
if (!isset($csrf_token)) {
    $csrf_token = generate_csrf_token();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - Reksurat</title>
    <link rel="icon" href="/assets/img/ArekSurat favicon.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .floating {
            animation: float 6s ease-in-out infinite;
        }
        
        .gradient-bg {
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }
        
        .input-focus-effect:focus {
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
        }
        
        .btn-hover-effect {
            transition: all 0.3s ease;
            transform: translateY(0);
        }
        
        .btn-hover-effect:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
        }
        
        .particle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            animation: float 8s infinite ease-in-out;
        }
        
        .card-entrance {
            animation: cardEntrance 0.8s ease-out forwards;
            opacity: 0;
            transform: translateY(20px);
        }
        
        @keyframes cardEntrance {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 overflow-hidden">
    <div class="fixed inset-0 gradient-bg z-0"></div>
    
    <div id="particles-container" class="fixed inset-0 z-1"></div>
    
    <div class="relative w-full max-w-md z-10 card-entrance">
        <div class="bg-white rounded-2xl shadow-2xl p-8 backdrop-blur-sm bg-opacity-95 transform transition-all duration-300 hover:shadow-xl">
            <div class="text-center mb-8 floating">
                <img src="assets/img/ArekSurat icon.png" alt="Logo" class="mx-auto mb-4" style="width: 250px; height: auto;">
                <p class="text-gray-500 mt-2">Masuk untuk mengakses dashboard</p>
            </div>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 animate-pulse" role="alert">
                    <span class="block sm:inline"><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <form class="space-y-6" method="POST" action="/login">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="transform transition-all duration-300 hover:scale-[1.01]">
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i> 
                        </div>
                        <input type="text" name="username" id="username" class="pl-10 w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-300 outline-none input-focus-effect" placeholder="admin" required />
                    </div>
                </div>

                <div class="transform transition-all duration-300 hover:scale-[1.01]">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" name="password" id="password" class="pl-10 pr-10 w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-300 outline-none input-focus-effect" placeholder="password123" required />
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" id="togglePassword">
                            <i class="fas fa-eye text-gray-400"></i>
                        </div>
                    </div>
                </div>
                <div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-300 btn-hover-effect">
                        Masuk
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Kode untuk partikel (tidak diubah)
            const container = document.getElementById('particles-container');
            const particleCount = 15;
            for (let i = 0; i < particleCount; i++) {
                createParticle(container);
            }
            function createParticle(container) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                const size = Math.random() * 15 + 5;
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                const posX = Math.random() * 100;
                const posY = Math.random() * 100;
                particle.style.left = `${posX}%`;
                particle.style.top = `${posY}%`;
                particle.style.animationDelay = `${Math.random() * 5}s`;
                container.appendChild(particle);
            }
            
            // Kode untuk animasi input (tidak diubah)
            const inputs = document.querySelectorAll('input');
            inputs.forEach(input => {
                input.addEventListener('focus', () => {
                    input.parentElement.parentElement.classList.add('scale-105');
                });
                
                input.addEventListener('blur', () => {
                    input.parentElement.parentElement.classList.remove('scale-105');
                });
            });

            // --- KODE BARU UNTUK FUNGSI LIHAT PASSWORD ---
            const togglePassword = document.querySelector('#togglePassword');
            const passwordInput = document.querySelector('#password');

            togglePassword.addEventListener('click', function() {
                // Ganti tipe input dari 'password' ke 'text' atau sebaliknya
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Ganti ikon mata dari 'fa-eye' ke 'fa-eye-slash' atau sebaliknya
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
            // --- AKHIR DARI KODE BARU ---
        });
    </script>
</body>
</html>