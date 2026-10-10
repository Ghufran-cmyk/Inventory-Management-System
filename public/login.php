<?php
session_start();

// Jika sudah login, langsung arahkan ke Dashboard
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

require_once '../config/db.php';

// [FITUR PINTAR]: Buat tabel users otomatis jika belum ada di database
$pdo->exec("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'admin'
)");

// [FITUR PINTAR]: Jika tabel users masih kosong, buatkan 1 akun default secara otomatis
$userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
if ($userCount == 0) {
    // Kata sandi 'admin123' langsung diacak (di-hash) demi keamanan
    $default_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT INTO users (username, password, role) VALUES ('admin', '$default_hash', 'admin')");
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = 'Username dan Password wajib diisi!';
    } else {
        // Cari data pengguna di database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // --- INI ADALAH INTI DARI PASSWORD HASHING ---
            // password_verify() akan mencocokkan teks yang diketik dengan kode acak di database
            if (password_verify($password, $user['password'])) {
                
                // Jika cocok, buat sesi login
                $_SESSION['user'] = [
                    'username' => $user['username'],
                    'role'     => $user['role']
                ];
                
                header("Location: index.php");
                exit;
            
            // [Masa Transisi]: Jika password di database masih teks biasa (belum di-hash dari tugas sebelumnya)
            } elseif ($password === $user['password']) {
                // Langsung acak password-nya dan perbarui di database secara diam-diam!
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $update_stmt = $pdo->prepare("UPDATE users SET password = :hash WHERE id = :id");
                $update_stmt->execute(['hash' => $hashed_password, 'id' => $user['id']]);
                
                $_SESSION['user'] = ['username' => $user['username'], 'role' => $user['role']];
                header("Location: index.php");
                exit;
            } else {
                $error = 'Kata sandi salah!';
            }
        } else {
            $error = 'Username tidak ditemukan!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Multivision Gear</title>
    <!-- Font Kekinian dari Google (Sama dengan Dashboard) -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-light: #64748b;
            --primary: #3b82f6;
            --border: #e2e8f0;
            --danger: #ef4444;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-color);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-card {
            background: var(--card-bg);
            padding: 40px 35px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.04);
            width: 100%;
            max-width: 360px;
            text-align: center;
            border: 1px solid var(--border);
        }

        /* Styling Logo di atas judul */
        .logo {
            height: 65px;
            margin-bottom: 15px;
            border-radius: 10px;
        }

        .login-card h2 {
            margin: 0 0 35px 0; /* Jarak yang lebih lega karena teks bawahnya dihapus */
            font-size: 22px;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: 0.5px;
        }

        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: 12px;
            font-family: inherit;
            font-size: 14px;
            outline: none;
            background: #f8fafc;
            box-sizing: border-box;
            transition: 0.3s;
        }

        .form-control:focus {
            border-color: var(--text-main);
            background: white;
            box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: var(--text-main);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: #1e293b;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        }

        .alert {
            background: #fee2e2;
            color: var(--danger);
            padding: 12px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
            border: 1px solid #fca5a5;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <!-- Logo ditambahkan di sini -->
        <img src="assets/logo.png" alt="Logo Multivision Gear" class="logo">
        
        <!-- Judul -->
        <h2>MULTIVISION GEAR</h2>
        
        <!-- Area untuk menampilkan pesan error (jika password salah) -->
        <?php if (isset($error) && $error): ?>
            <div class="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Form Login -->
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username..." required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-login">Login Sekarang</button>
        </form>
    </div>
</body>
</html>