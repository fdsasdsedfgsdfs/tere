<?php
session_start();

$error = '';

if ($_POST) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Lütfen kullanıcı adı ve şifrenizi girin.';
    } else {
        // Kullanıcıları yükle
        $users = [];
        if (file_exists('data/users.json')) {
            $users = json_decode(file_get_contents('data/users.json'), true);
        }
        
        // Kullanıcı kontrolü
        $user_found = false;
        foreach ($users as $user) {
            if (($user['username'] === $username || $user['email'] === $username) && 
                password_verify($password, $user['password'])) {
                
                // Giriş başarılı - session başlat
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                header('Location: index.php');
                exit;
            }
        }
        
        $error = 'Kullanıcı adı veya şifre hatalı.';
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - TurkForum</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo h1 {
            font-size: 2rem;
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .logo p {
            color: #666;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            width: 100%;
            padding: 0.75rem;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
        }

        .error {
            background: #fee;
            color: #c33;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #fcc;
        }

        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
        }

        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .back-home {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .back-home:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .demo-info {
            background: rgba(102, 126, 234, 0.1);
            border: 1px solid rgba(102, 126, 234, 0.3);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .demo-info h3 {
            color: #667eea;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .demo-info p {
            color: #666;
            font-size: 0.9rem;
            margin: 0.25rem 0;
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-home">
        <i class="fas fa-arrow-left"></i> Ana Sayfaya Dön
    </a>

    <div class="login-container">
        <div class="logo">
            <h1><i class="fas fa-comments"></i> TurkForum</h1>
            <p>Hesabına giriş yap</p>
        </div>

        <div class="demo-info">
            <h3><i class="fas fa-info-circle"></i> Demo Hesap</h3>
            <p><strong>Kullanıcı:</strong> Admin</p>
            <p><strong>Şifre:</strong> admin123</p>
        </div>

        <?php if ($error): ?>
            <div class="error">
                <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">
                    <i class="fas fa-user"></i> Kullanıcı Adı veya E-posta
                </label>
                <input type="text" id="username" name="username" 
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i> Şifre
                </label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn">
                <i class="fas fa-sign-in-alt"></i> Giriş Yap
            </button>
        </form>

        <div class="register-link">
            Hesabın yok mu? <a href="register.php">Hemen Kayıt Ol</a>
        </div>
    </div>

    <script>
        // Form animasyonları
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('input');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.02)';
                });
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });
            });

            // Demo bilgilerine tıklayınca otomatik doldur
            document.querySelector('.demo-info').addEventListener('click', function() {
                document.getElementById('username').value = 'Admin';
                document.getElementById('password').value = 'admin123';
            });
        });
    </script>
</body>
</html>