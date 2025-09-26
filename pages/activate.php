<?php
// pages/activate.php

require_once __DIR__ . '/../config/secrets.php'; // Memuat secret salt

$message = '';
$currentYear = (int)date('Y');

// Pastikan sesi sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted_code = $_POST['activation_code'] ?? '';

    // Generate expected code based on current year and secret salt
    $expected_code = md5($currentYear . ACTIVATION_SECRET_SALT);

    if ($submitted_code === $expected_code) {
        $_SESSION['app_activated_year'] = $currentYear;

        // --- SET PERSISTENT ACTIVATION COOKIE ---
        $cookie_token = md5($currentYear . ACTIVATION_SECRET_SALT . ACTIVATION_COOKIE_SALT);
        $cookie_expiry = time() + (86400 * 365 * 5); // 5 tahun
        setcookie(ACTIVATION_COOKIE_NAME, $cookie_token, [
            'expires' => $cookie_expiry,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        // --- END OF COOKIE SETTING ---

        $_SESSION['success_message'] = "Aplikasi berhasil diaktifkan untuk tahun {$currentYear}!";
        header('Location: /dashboard'); // Redirect ke dashboard setelah aktivasi
        exit;
    } else {
        $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Kode aktivasi salah. Silakan coba lagi.</div>';
    }
}

// Tampilan halaman aktivasi
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivasi Aplikasi</title>
    <link href="/assets/css/output.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f0f4f8; /* Light blue-gray background */
        }
        .container {
            max-width: 400px;
            margin: 10vh auto;
            padding: 2rem;
            background-color: #ffffff;
            border-radius: 0.75rem; /* rounded-xl */
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); /* shadow-xl */
            text-align: center;
        }
        .logo {
            margin-bottom: 1.5rem;
        }
        .logo img {
            max-width: 120px;
            height: auto;
        }
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db; /* border-gray-300 */
            border-radius: 0.5rem; /* rounded-lg */
            margin-bottom: 1rem;
            font-size: 1rem;
        }
        .btn-submit {
            width: 100%;
            padding: 0.75rem 1rem;
            background-image: linear-gradient(to right, #4f46e5, #7c3aed); /* from-primary to-secondary */
            color: #ffffff;
            border-radius: 0.5rem; /* rounded-lg */
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }
        .btn-submit:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="/assets/img/ArekSurat icon.png" alt="Logo Aplikasi" class="mx-auto">
        </div>
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Aktivasi Aplikasi</h2>
        <p class="text-gray-600 mb-6">Masukkan kode aktivasi untuk tahun <?php echo $currentYear; ?>.</p>

        <?php echo $message; ?>

        <form method="POST" action="/activate">
            <input type="text" name="activation_code" class="form-input" placeholder="Kode Aktivasi" required>
            <button type="submit" class="btn-submit">Aktifkan</button>
        </form>
        <p class="text-gray-600 mt-4 text-sm">
            Butuh bantuan? <a href="https://wa.me/628972440601" target="_blank" class="text-primary hover:underline font-medium"><i class="fab fa-whatsapp mr-1"></i> Hubungi Admin</a>
        </p>
    </div>
</body>
</html>