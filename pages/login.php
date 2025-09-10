<?php
// pages/login.php

// Jika sudah login, langsung arahkan ke dashboard
if (isLoggedIn()) {
    header('Location: /surat-keluar');
    exit;
}

$error = '';
// Cek jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        header('Location: /surat-keluar'); // Arahkan ke halaman surat keluar
        exit;
    } else {
        // ---- PERUBAHAN 3: Memperbarui pesan error ----
        $error = 'Username atau kata sandi salah!';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - Reksurat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-b-[50%]"></div>
    <div class="relative w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-2xl p-8 backdrop-blur-sm bg-opacity-95">
            <div class="text-center mb-8">
                <i class="fas fa-envelope-open-text text-indigo-600 text-5xl mx-auto mb-4"></i>
                <h1 class="text-3xl font-bold text-gray-800">Reksurat</h1>
                <p class="text-gray-500 mt-2">Masuk untuk mengakses dashboard</p>
            </div>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <form class="space-y-6" method="POST" action="/login">
                
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i> </div>
                        <input type="text" name="username" id="username" class="pl-10 w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-300 outline-none" placeholder="admin" required />
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" name="password" id="password" class="pl-10 w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-300 outline-none" placeholder="password123" required />
                    </div>
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-300">
                        Masuk
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>