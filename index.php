<?php
session_start();

// JSON dosyalarını oluştur
if (!file_exists('data')) {
    mkdir('data', 0777, true);
}

// Kategoriler JSON dosyası
if (!file_exists('data/categories.json')) {
    $categories = [
        ['id' => 1, 'name' => 'Genel Sohbet', 'description' => 'Günlük konuşmalar ve genel sohbet', 'icon' => '💬'],
        ['id' => 2, 'name' => 'Teknoloji', 'description' => 'Teknoloji haberleri ve tartışmaları', 'icon' => '💻'],
        ['id' => 3, 'name' => 'Oyunlar', 'description' => 'Oyun tartışmaları ve incelemeler', 'icon' => '🎮'],
        ['id' => 4, 'name' => 'Yardım', 'description' => 'Yardım ve destek konuları', 'icon' => '❓']
    ];
    file_put_contents('data/categories.json', json_encode($categories, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Konular JSON dosyası
if (!file_exists('data/topics.json')) {
    $topics = [
        [
            'id' => 1,
            'category_id' => 1,
            'title' => 'Foruma Hoş Geldiniz!',
            'content' => 'Bu forum sitesine hoş geldiniz. Buradan yeni konular açabilir ve tartışmalara katılabilirsiniz.',
            'author' => 'Admin',
            'created_at' => date('Y-m-d H:i:s'),
            'replies' => 0,
            'views' => 0,
            'pinned' => true
        ]
    ];
    file_put_contents('data/topics.json', json_encode($topics, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Kullanıcılar JSON dosyası
if (!file_exists('data/users.json')) {
    $users = [
        [
            'id' => 1,
            'username' => 'Admin',
            'email' => 'admin@forum.com',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'role' => 'admin',
            'created_at' => date('Y-m-d H:i:s'),
            'avatar' => '👑'
        ]
    ];
    file_put_contents('data/users.json', json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Verileri yükle
$categories = json_decode(file_get_contents('data/categories.json'), true);
$topics = json_decode(file_get_contents('data/topics.json'), true);

// İstatistikleri hesapla
$total_topics = count($topics);
$total_users = count(json_decode(file_get_contents('data/users.json'), true));
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TurkForum - Türkiye'nin En Büyük Forum Sitesi</title>
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
            max-width: 1200px;
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

        /* Main Content */
        main {
            padding: 2rem 0;
        }

        .welcome-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }

        .welcome-section h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stats {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 1.5rem;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }

        /* Categories Grid */
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .category-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 16px 48px rgba(0,0,0,0.15);
        }

        .category-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .category-icon {
            font-size: 2rem;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .category-title {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
        }

        .category-description {
            color: #666;
            margin-bottom: 1rem;
            line-height: 1.5;
        }

        .category-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }

        .recent-topics {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: #333;
        }

        .topic-list {
            list-style: none;
        }

        .topic-item {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            transition: background 0.3s ease;
        }

        .topic-item:hover {
            background: rgba(102, 126, 234, 0.05);
        }

        .topic-item:last-child {
            border-bottom: none;
        }

        .topic-title {
            font-weight: bold;
            color: #333;
            text-decoration: none;
            margin-bottom: 0.5rem;
            display: block;
        }

        .topic-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
            color: #666;
        }

        .pinned {
            color: #f39c12 !important;
        }

        .pinned::before {
            content: "📌 ";
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }

            .welcome-section h1 {
                font-size: 2rem;
            }

            .stats {
                flex-direction: column;
                gap: 1rem;
            }

            .categories-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Online Users */
        .online-users {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 1.5rem;
            margin-top: 1.5rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }

        .online-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #27ae60;
            border-radius: 50%;
            margin-right: 0.5rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
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
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <span>Hoş geldin, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
                        <a href="profile.php" class="btn btn-secondary">
                            <i class="fas fa-user"></i> Profil
                        </a>
                        <a href="logout.php" class="btn btn-primary">
                            <i class="fas fa-sign-out-alt"></i> Çıkış
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-secondary">
                            <i class="fas fa-sign-in-alt"></i> Giriş Yap
                        </a>
                        <a href="register.php" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Kayıt Ol
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <!-- Welcome Section -->
            <div class="welcome-section">
                <h1>Türkiye'nin En Büyük Forum Topluluğu</h1>
                <p>Binlerce üye ile sohbet et, fikirlerini paylaş ve yeni arkadaşlıklar kur!</p>
                <div class="stats">
                    <div class="stat-item">
                        <div class="stat-number"><?= number_format($total_topics) ?></div>
                        <div class="stat-label">Konu</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?= number_format($total_users) ?></div>
                        <div class="stat-label">Üye</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Aktif</div>
                    </div>
                </div>
            </div>

            <!-- Categories -->
            <div class="categories-grid">
                <?php foreach ($categories as $category): ?>
                    <?php 
                    $category_topics = array_filter($topics, function($topic) use ($category) {
                        return $topic['category_id'] == $category['id'];
                    });
                    $topic_count = count($category_topics);
                    ?>
                    <a href="category.php?id=<?= $category['id'] ?>" class="category-card">
                        <div class="category-header">
                            <div class="category-icon">
                                <?= $category['icon'] ?>
                            </div>
                            <div>
                                <div class="category-title"><?= htmlspecialchars($category['name']) ?></div>
                            </div>
                        </div>
                        <div class="category-description">
                            <?= htmlspecialchars($category['description']) ?>
                        </div>
                        <div class="category-stats">
                            <span><i class="fas fa-comments"></i> <?= $topic_count ?> Konu</span>
                            <span><i class="fas fa-eye"></i> <?= rand(100, 1000) ?> Görüntülenme</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Recent Topics -->
            <div class="recent-topics">
                <h2 class="section-title">
                    <i class="fas fa-fire"></i> Son Konular
                </h2>
                <ul class="topic-list">
                    <?php 
                    $recent_topics = array_slice(array_reverse($topics), 0, 5);
                    foreach ($recent_topics as $topic): 
                    ?>
                        <li class="topic-item">
                            <a href="topic.php?id=<?= $topic['id'] ?>" 
                               class="topic-title <?= $topic['pinned'] ? 'pinned' : '' ?>">
                                <?= htmlspecialchars($topic['title']) ?>
                            </a>
                            <div class="topic-meta">
                                <span>
                                    <i class="fas fa-user"></i> <?= htmlspecialchars($topic['author']) ?>
                                </span>
                                <span>
                                    <i class="fas fa-clock"></i> <?= date('d.m.Y H:i', strtotime($topic['created_at'])) ?>
                                </span>
                                <span>
                                    <i class="fas fa-comments"></i> <?= $topic['replies'] ?> Yanıt
                                </span>
                                <span>
                                    <i class="fas fa-eye"></i> <?= $topic['views'] ?>
                                </span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Online Users -->
            <div class="online-users">
                <h2 class="section-title">
                    <span class="online-indicator"></span> Çevrimiçi Kullanıcılar (<?= rand(15, 45) ?>)
                </h2>
                <p>Şu anda <?= rand(15, 45) ?> kullanıcı forumda aktif.</p>
            </div>
        </div>
    </main>

    <script>
        // Basit animasyonlar
        document.addEventListener('DOMContentLoaded', function() {
            // Kartlara hover efekti
            const cards = document.querySelectorAll('.category-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px) scale(1.02)';
                });
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });

            // Sayaç animasyonu
            const statNumbers = document.querySelectorAll('.stat-number');
            statNumbers.forEach(stat => {
                const finalValue = parseInt(stat.textContent.replace(/,/g, ''));
                let currentValue = 0;
                const increment = finalValue / 50;
                
                const timer = setInterval(() => {
                    currentValue += increment;
                    if (currentValue >= finalValue) {
                        currentValue = finalValue;
                        clearInterval(timer);
                    }
                    stat.textContent = Math.floor(currentValue).toLocaleString();
                }, 20);
            });
        });
    </script>
</body>
</html>
