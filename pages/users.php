<?php
// pages/users.php

// Keamanan: Pastikan hanya admin yang bisa mengakses halaman ini
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
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
                    <input type="password" id="password" name="password" class="w-full px-4 py-3 rounded-xl border border-gray-300 pr-10" placeholder="Minimal 8 karakter" required>
                    <button type="button" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-primary focus:outline-none toggle-password" aria-label="Tampilkan password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                <select id="role" name="role" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white" required>
                    <option value="" disabled selected>-- Pilih Role --</option>
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
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody id="tableBodyUsers" class="bg-white divide-y divide-gray-200">
                <?php foreach ($users as $user): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900"><?php echo htmlspecialchars($user['id']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($user['nama']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($user['username']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                                $role = htmlspecialchars($user['role']);
                                $badge_class = 'bg-blue-100 text-blue-800';
                                if ($role === 'admin') {
                                    $badge_class = 'bg-green-100 text-green-800';
                                }
                            ?>
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $badge_class; ?>">
                                <?php echo ucfirst($role); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-3">
                                <a href="/edit-user?id=<?php echo $user['id']; ?>" class="text-indigo-600 hover:text-indigo-900" title="Edit"><i class="fas fa-edit"></i></a>
                                <?php if ($user['id'] != $_SESSION['user_id']): // Admin tidak bisa menghapus diri sendiri ?>
                                <button onclick="confirmDelete('user', <?php echo $user['id']; ?>)" class="text-red-600 hover:text-red-900" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                 <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            <p>Belum ada data pengguna.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>
