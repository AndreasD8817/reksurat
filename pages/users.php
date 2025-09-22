<?php
// pages/users.php

// Keamanan: Pastikan hanya superadmin yang bisa mengakses halaman ini
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'superadmin') {
    $_SESSION['error_message'] = "Anda tidak memiliki izin untuk mengakses halaman Manajemen User.";
    header('Location: /dashboard');
    exit;
}

// Logika untuk MENAMBAH user baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_user'])) {
    // Verifikasi CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Terjadi kesalahan validasi. Silakan coba lagi.";
        header('Location: /users');
        exit;
    }

    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Validasi dasar
    if (empty($nama) || empty($username) || empty($email) || empty($password) || empty($role)) {
        $_SESSION['error_message'] = "Semua kolom wajib diisi.";
    } else {
        // Cek apakah username atau email sudah ada
        $stmt_check = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt_check->execute([$username, $email]);
        if ($stmt_check->fetch()) {
            $_SESSION['error_message'] = "Username atau email sudah digunakan.";
        } else {
            // Hash password untuk keamanan
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Simpan ke database
            $stmt = $pdo->prepare("INSERT INTO users (nama, username, email, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nama, $username, $email, $hashed_password, $role]);

            $_SESSION['success_message'] = "User baru berhasil ditambahkan.";
        }
    }
    header('Location: /users');
    exit;
}

// Logika untuk MENAMPILKAN daftar user
$stmt = $pdo->query("SELECT id, nama, username, email, role FROM users ORDER BY id ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Manajemen User';
require_once 'templates/header.php';
?>

<!-- Form Tambah User -->
<div class="bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-xl p-6 animate-fade-in border border-blue-100">
    <h3 class="text-2xl font-bold text-gray-800 mb-6 border-b border-blue-200 pb-3 flex items-center">
        <span class="bg-gradient-to-r from-primary to-secondary text-transparent bg-clip-text">Tambah User Baru</span>
        <i class="fas fa-user-plus ml-3 text-primary"></i>
    </h3>
    
    <form method="POST" action="/users" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div>
                <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" class="w-full px-4 py-3 rounded-xl border border-gray-300" placeholder="Masukkan nama lengkap" required>
            </div>
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                <input type="text" id="username" name="username" class="w-full px-4 py-3 rounded-xl border border-gray-300" placeholder="Username untuk login" required>
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" id="email" name="email" class="w-full px-4 py-3 rounded-xl border border-gray-300" placeholder="contoh@email.com" required>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <div class="relative">
                    <input type="password" id="password" name="password" class="w-full px-4 py-3 rounded-xl border border-gray-300 pr-10" placeholder="Minimal 8 karakter" required autocomplete="new-password">
                    <button type="button" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-primary focus:outline-none toggle-password" aria-label="Tampilkan password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                <select id="role" name="role" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white" required>
                    <option value="" disabled selected>-- Pilih Role --</option>
                    <option value="superadmin">Superadmin</option>
                    <option value="admin">Admin</option>
                    <option value="staff surat masuk">Staff Surat Masuk</option>
                    <option value="staff surat keluar">Staff Surat Keluar</option>
                </select>
            </div>
        </div>
        <div class="mt-8 flex justify-end">
            <button type="submit" name="tambah_user" class="px-6 py-3 bg-gradient-to-r from-primary to-secondary text-white rounded-xl shadow-md hover:shadow-lg">
                <i class="fas fa-plus mr-2"></i> Tambah User
            </button>
        </div>
    </form>
</div>


<!-- Daftar User -->
<div class="mt-8 bg-white rounded-2xl shadow-xl p-6">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
         <h3 class="text-xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-users text-primary mr-2"></i> 
            <span class="bg-gradient-to-r from-primary to-secondary text-transparent bg-clip-text">Daftar Pengguna</span>
        </h3>
    </div>

    <!-- Grid container for users -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (empty($users)): ?>
            <div class="col-span-full text-center py-8 text-gray-500">
                <p>Belum ada data pengguna.</p>
            </div>
        <?php else: ?>
            <?php foreach ($users as $user): ?>
                <div class="bg-white border border-gray-200 rounded-lg p-4 flex flex-col hover:shadow-lg transition-shadow duration-200">
                    <div class="flex-grow">
                        <div class="flex items-center mb-3">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-r from-primary to-secondary flex items-center justify-center text-white font-bold text-xl">
                                <?php echo strtoupper(substr($user['nama'], 0, 1)); ?>
                            </div>
                            <div class="ml-4">
                                <p class="font-bold text-gray-800"><?php echo htmlspecialchars($user['nama']); ?></p>
                                <p class="text-sm text-gray-500">@<?php echo htmlspecialchars($user['username']); ?></p>
                            </div>
                        </div>
                        
                        <p class="text-sm text-gray-600 mb-2"><i class="fas fa-envelope w-5 mr-2 text-gray-400"></i><?php echo htmlspecialchars($user['email']); ?></p>
                        
                        <div class="flex items-center">
                            <i class="fas fa-user-shield w-5 mr-2 text-gray-400"></i>
                            <?php
                                $role = htmlspecialchars($user['role']);
                                $badge_class = 'bg-blue-100 text-blue-800';
                                if ($role === 'superadmin') {
                                    $badge_class = 'bg-red-100 text-red-800';
                                }
                                if ($role === 'admin') {
                                    $badge_class = 'bg-green-100 text-green-800';
                                }
                            ?>
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $badge_class; ?>">
                                <?php echo ucfirst($role); ?>
                            </span>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 mt-4 pt-4 flex justify-end space-x-3">
                        <a href="/edit-user?id=<?php echo $user['id']; ?>" class="text-indigo-600 hover:text-indigo-900" title="Edit"><i class="fas fa-edit"></i> Edit</a>
                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                        <button onclick="confirmDelete('user', <?php echo $user['id']; ?>)" class="text-red-600 hover:text-red-900" title="Hapus"><i class="fas fa-trash-alt"></i> Hapus</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>
