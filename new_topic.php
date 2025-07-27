<?php
session_start();

// Giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$category_id = $_GET['category'] ?? 1;

// Verileri yükle
$categories = json_decode(file_get_contents('data/categories.json'), true);

// Kategoriyi bul
$current_category = null;
foreach ($categories as $category) {
    if ($category['id'] == $category_id) {
        $current_category = $category;
        break;
    }
}

if (!$current_category) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_POST) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    
    if (empty($title) || empty($content)) {
        $error = 'Lütfen başlık ve içerik alanlarını doldurun.';
    } elseif (strlen($title) < 5) {
        $error = 'Başlık en az 5 karakter olmalıdır.';
    } elseif (strlen($content) < 10) {
        $error = 'İçerik en az 10 karakter olmalıdır.';
    } else {
        // Mevcut konuları yükle
        $topics = [];
        if (file_exists('data/topics.json')) {
            $topics = json_decode(file_get_contents('data/topics.json'), true);
        }
        
        // Yeni konu oluştur
        $new_topic = [
            'id' => time() . rand(1000, 9999),
            'category_id' => $category_id,
            'title' => $title,
            'content' => $content,
            'author' => $_SESSION['username'],
            'created_at' => date('Y-m-d H:i:s'),
            'replies' => 0,
            'views' => 0,
            'pinned' => false
        ];
        
        $topics[] = $new_topic;
        
        // Kaydet
        file_put_contents('data/topics.json', json_encode($topics, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        // Yeni konuya yönlendir
        header('Location: topic.php?id=' . $new_topic['id']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Konu Aç - <?= htmlspecialchars($current_category['name']) ?> - TurkForum</title>
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
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: #667eea;
            text-decoration: none;
        }

        .user-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-secondary:hover {
            background: #667eea;
            color: white;
        }

        .btn-success {
            background: #27ae60;
            color: white;
            font-size: 1.1rem;
            padding: 0.75rem 1.5rem;
        }

        .btn-success:hover {
            background: #229954;
            transform: translateY(-2px);
        }

        /* Main Content */
        main {
            padding: 2rem 0;
        }

        .breadcrumb {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        }

        .breadcrumb a {
            color: #667eea;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #eee;
        }

        .form-header h1 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .category-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 2rem;
        }

        label {
            display: block;
            margin-bottom: 0.75rem;
            color: #333;
            font-weight: 600;
            font-size: 1.1rem;
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        textarea {
            min-height: 200px;
            resize: vertical;
        }

        .char-counter {
            text-align: right;
            color: #666;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .error {
            background: #fee;
            color: #c33;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border: 1px solid #fcc;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #eee;
        }

        .tips {
            background: rgba(102, 126, 234, 0.05);
            border: 1px solid rgba(102, 126, 234, 0.2);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .tips h3 {
            color: #667eea;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .tips ul {
            color: #666;
            padding-left: 1.5rem;
        }

        .tips li {
            margin-bottom: 0.5rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">
                    <i class="fas fa-comments"></i> TurkForum
                </a>
                <div class="user-actions">
                    <span>Hoş geldin, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
                    <a href="profile.php" class="btn btn-secondary">
                        <i class="fas fa-user"></i> Profil
                    </a>
                    <a href="logout.php" class="btn btn-primary">
                        <i class="fas fa-sign-out-alt"></i> Çıkış
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <i class="fas fa-home"></i>
                <a href="index.php">Ana Sayfa</a> › 
                <a href="category.php?id=<?= $category_id ?>"><?= htmlspecialchars($current_category['name']) ?></a> › 
                <span>Yeni Konu</span>
            </div>

            <!-- Form -->
            <div class="form-container">
                <div class="form-header">
                    <h1><i class="fas fa-plus-circle"></i> Yeni Konu Aç</h1>
                    <div class="category-badge">
                        <?= $current_category['icon'] ?> <?= htmlspecialchars($current_category['name']) ?>
                    </div>
                </div>

                <div class="tips">
                    <h3><i class="fas fa-lightbulb"></i> İpuçları</h3>
                    <ul>
                        <li>Başlığınızı açık ve anlaşılır yazın</li>
                        <li>Konunuzu detaylı bir şekilde açıklayın</li>
                        <li>Saygılı ve kibar bir dil kullanın</li>
                        <li>Arama yaparak benzer konuların daha önce açılıp açılmadığını kontrol edin</li>
                    </ul>
                </div>

                <?php if ($error): ?>
                    <div class="error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" id="topicForm">
                    <div class="form-group">
                        <label for="title">
                            <i class="fas fa-heading"></i> Konu Başlığı
                        </label>
                        <input type="text" id="title" name="title" 
                               value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" 
                               placeholder="Konunuzun başlığını buraya yazın..." 
                               maxlength="200" required>
                        <div class="char-counter">
                            <span id="titleCounter">0</span>/200 karakter
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="content">
                            <i class="fas fa-edit"></i> Konu İçeriği
                        </label>
                        <textarea id="content" name="content" 
                                  placeholder="Konunuzu detaylı bir şekilde buraya yazın..." 
                                  maxlength="5000" required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                        <div class="char-counter">
                            <span id="contentCounter">0</span>/5000 karakter
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="category.php?id=<?= $category_id ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Geri Dön
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-paper-plane"></i> Konuyu Oluştur
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const titleInput = document.getElementById('title');
            const contentTextarea = document.getElementById('content');
            const titleCounter = document.getElementById('titleCounter');
            const contentCounter = document.getElementById('contentCounter');

            // Karakter sayacı
            function updateCounters() {
                titleCounter.textContent = titleInput.value.length;
                contentCounter.textContent = contentTextarea.value.length;
                
                // Renk kodlaması
                if (titleInput.value.length > 180) {
                    titleCounter.style.color = '#e74c3c';
                } else if (titleInput.value.length > 150) {
                    titleCounter.style.color = '#f39c12';
                } else {
                    titleCounter.style.color = '#666';
                }
                
                if (contentTextarea.value.length > 4500) {
                    contentCounter.style.color = '#e74c3c';
                } else if (contentTextarea.value.length > 4000) {
                    contentCounter.style.color = '#f39c12';
                } else {
                    contentCounter.style.color = '#666';
                }
            }

            titleInput.addEventListener('input', updateCounters);
            contentTextarea.addEventListener('input', updateCounters);

            // Sayfa yüklendiğinde sayaçları güncelle
            updateCounters();

            // Textarea auto-resize
            contentTextarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = this.scrollHeight + 'px';
            });

            // Form validasyonu
            document.getElementById('topicForm').addEventListener('submit', function(e) {
                if (titleInput.value.trim().length < 5) {
                    e.preventDefault();
                    alert('Başlık en az 5 karakter olmalıdır.');
                    titleInput.focus();
                    return;
                }
                
                if (contentTextarea.value.trim().length < 10) {
                    e.preventDefault();
                    alert('İçerik en az 10 karakter olmalıdır.');
                    contentTextarea.focus();
                    return;
                }
            });

            // Animasyonlar
            const formGroups = document.querySelectorAll('.form-group');
            formGroups.forEach((group, index) => {
                group.style.opacity = '0';
                group.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    group.style.transition = 'all 0.5s ease';
                    group.style.opacity = '1';
                    group.style.transform = 'translateY(0)';
                }, index * 200);
            });
        });
    </script>
</body>
</html>