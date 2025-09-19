<?php
// pages/edit-user.php

// Keamanan: Pastikan hanya admin yang bisa mengakses halaman ini
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error_message'] = "Anda tidak memiliki izin untuk mengakses halaman ini.";
    header('Location: /dashboard');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header('Location: /users');
    exit;
}

// Ambil data user yang akan di-edit
$stmt = $pdo->prepare("SELECT id, nama, username, email, role FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['error_message'] = "User tidak ditemukan.";
    header('Location: /users');
    exit;
}

// Logika untuk UPDATE user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['error_message'] = "Sesi tidak valid. Silakan coba lagi.";
        header('Location: /edit-user?id=' . $id);
        exit;
    }

    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Cek apakah username atau email sudah digunakan oleh user lain
    $stmt_check = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt_check->execute([$username, $email, $id]);
    if ($stmt_check->fetch()) {
        $_SESSION['error_message'] = "Username atau email sudah digunakan oleh pengguna lain.";
    } else {
        // Bangun query secara dinamis
        $sql = "UPDATE users SET nama = ?, username = ?, email = ?, role = ?";
        $params = [$nama, $username, $email, $role];

        if (!empty($password)) {
            $sql .= ", password = ?";
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $params[] = $hashed_password;
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $_SESSION['success_message'] = "Data user berhasil diperbarui.";
        header('Location: /users');
        exit;
    }
    // Redirect kembali ke halaman edit jika ada error
    header('Location: /edit-user?id=' . $id);
    exit;
}


$pageTitle = 'Edit User';
require_once 'templates/header.php';
?>

<div class="bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-xl p-6 animate-fade-in border border-blue-100">
    <h3 class="text-2xl font-bold text-gray-800 mb-6 border-b border-blue-200 pb-3 flex items-center">
        <span class="bg-gradient-to-r from-primary to-secondary text-transparent bg-clip-text">Edit Data User</span>
        <i class="fas fa-user-edit ml-3 text-primary"></i>
    </h3>
    
    <form method="POST" action="/edit-user?id=<?php echo $user['id']; ?>" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($user['nama']); ?>" class="w-full px-4 py-3 rounded-xl border border-gray-300" required>
            </div>
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" class="w-full px-4 py-3 rounded-xl border border-gray-300" required>
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="w-full px-4 py-3 rounded-xl border border-gray-300" required>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password Baru (Opsional)</label>
                <div class="relative">
                    <input type="password" id="password" name="password" class="w-full px-4 py-3 rounded-xl border border-gray-300 pr-10" placeholder="Isi untuk mengganti password">
                    <button type="button" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-primary focus:outline-none toggle-password" aria-label="Tampilkan password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                <select id="role" name="role" class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white" required>
                    <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                    <option value="staff surat masuk" <?php echo ($user['role'] == 'staff surat masuk') ? 'selected' : ''; ?>>Staff Surat Masuk</option>
                    <option value="staff surat keluar" <?php echo ($user['role'] == 'staff surat keluar') ? 'selected' : ''; ?>>Staff Surat Keluar</option>
                </select>
            </div>
        </div>

        <div class="mt-8 flex justify-end space-x-4">
            <a href="/users" class="px-6 py-3 border border-gray-300 rounded-xl hover:bg-gray-50">Batal</a>
            <button type="submit" name="update_user" class="px-6 py-3 bg-gradient-to-r from-primary to-secondary text-white rounded-xl shadow-md">
                <i class="fas fa-save mr-2"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<?php require_once 'templates/footer.php'; ?>
