<?php
session_start();

$category_id = $_GET['id'] ?? 1;

// Verileri yükle
$categories = json_decode(file_get_contents('data/categories.json'), true);
$topics = json_decode(file_get_contents('data/topics.json'), true);

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

// Bu kategorideki konuları filtrele
$category_topics = array_filter($topics, function($topic) use ($category_id) {
    return $topic['category_id'] == $category_id;
});

// Konuları tarihe göre sırala (yeniden eskiye)
usort($category_topics, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($current_category['name']) ?> - TurkForum</title>
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

        .category-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            text-align: center;
        }

        .category-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .category-title {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .category-description {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }

        .new-topic-btn {
            background: #27ae60;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .new-topic-btn:hover {
            background: #229954;
            transform: translateY(-2px);
        }

        .topics-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }

        .topics-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #eee;
        }

        .topics-count {
            color: #666;
            font-size: 0.9rem;
        }

        .topic-list {
            list-style: none;
        }

        .topic-item {
            display: flex;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .topic-item:hover {
            background: rgba(102, 126, 234, 0.05);
            transform: translateX(5px);
        }

        .topic-item:last-child {
            border-bottom: none;
        }

        .topic-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            color: white;
            font-size: 1.2rem;
        }

        .topic-content {
            flex: 1;
        }

        .topic-title {
            font-size: 1.1rem;
            font-weight: bold;
            color: #333;
            text-decoration: none;
            margin-bottom: 0.5rem;
            display: block;
        }

        .topic-title:hover {
            color: #667eea;
        }

        .topic-meta {
            display: flex;
            gap: 1rem;
            color: #666;
            font-size: 0.9rem;
        }

        .topic-stats {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            min-width: 80px;
        }

        .stat-number {
            font-weight: bold;
            color: #667eea;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #666;
        }

        .pinned {
            background: linear-gradient(135deg, #f39c12, #e67e22) !important;
        }

        .pinned .topic-title::before {
            content: "📌 ";
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #666;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #ddd;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }

            .topic-item {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .topic-stats {
                flex-direction: row;
                min-width: auto;
            }

            .topics-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
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
            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <i class="fas fa-home"></i>
                <a href="index.php">Ana Sayfa</a> › 
                <span><?= htmlspecialchars($current_category['name']) ?></span>
            </div>

            <!-- Category Header -->
            <div class="category-header">
                <div class="category-icon">
                    <?= $current_category['icon'] ?>
                </div>
                <h1 class="category-title"><?= htmlspecialchars($current_category['name']) ?></h1>
                <p class="category-description"><?= htmlspecialchars($current_category['description']) ?></p>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="new_topic.php?category=<?= $category_id ?>" class="new-topic-btn">
                        <i class="fas fa-plus"></i> Yeni Konu Aç
                    </a>
                <?php else: ?>
                    <a href="login.php" class="new-topic-btn">
                        <i class="fas fa-sign-in-alt"></i> Konu Açmak İçin Giriş Yap
                    </a>
                <?php endif; ?>
            </div>

            <!-- Topics List -->
            <div class="topics-container">
                <div class="topics-header">
                    <h2><i class="fas fa-list"></i> Konular</h2>
                    <span class="topics-count">
                        Toplam <?= count($category_topics) ?> konu
                    </span>
                </div>

                <?php if (empty($category_topics)): ?>
                    <div class="empty-state">
                        <i class="fas fa-comments"></i>
                        <h3>Henüz konu yok</h3>
                        <p>Bu kategoride henüz hiç konu açılmamış.</p>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <p>İlk konuyu sen aç!</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <ul class="topic-list">
                        <?php foreach ($category_topics as $topic): ?>
                            <li class="topic-item" onclick="location.href='topic.php?id=<?= $topic['id'] ?>'">
                                <div class="topic-icon <?= $topic['pinned'] ? 'pinned' : '' ?>">
                                    <?php if ($topic['pinned']): ?>
                                        <i class="fas fa-thumbtack"></i>
                                    <?php else: ?>
                                        <i class="fas fa-comment"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="topic-content">
                                    <a href="topic.php?id=<?= $topic['id'] ?>" class="topic-title">
                                        <?= htmlspecialchars($topic['title']) ?>
                                    </a>
                                    <div class="topic-meta">
                                        <span>
                                            <i class="fas fa-user"></i> <?= htmlspecialchars($topic['author']) ?>
                                        </span>
                                        <span>
                                            <i class="fas fa-clock"></i> <?= date('d.m.Y H:i', strtotime($topic['created_at'])) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="topic-stats">
                                    <div>
                                        <div class="stat-number"><?= $topic['replies'] ?></div>
                                        <div class="stat-label">Yanıt</div>
                                    </div>
                                </div>
                                <div class="topic-stats">
                                    <div>
                                        <div class="stat-number"><?= $topic['views'] ?></div>
                                        <div class="stat-label">Görüntülenme</div>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        // Animasyonlar
        document.addEventListener('DOMContentLoaded', function() {
            // Topic items animasyonu
            const topicItems = document.querySelectorAll('.topic-item');
            topicItems.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    item.style.transition = 'all 0.5s ease';
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>