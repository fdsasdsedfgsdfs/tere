<?php
session_start();

$topic_id = $_GET['id'] ?? 1;

// Verileri yükle
$topics = json_decode(file_get_contents('data/topics.json'), true);
$categories = json_decode(file_get_contents('data/categories.json'), true);

// Konuyu bul
$current_topic = null;
$topic_index = null;
foreach ($topics as $index => $topic) {
    if ($topic['id'] == $topic_id) {
        $current_topic = $topic;
        $topic_index = $index;
        break;
    }
}

if (!$current_topic) {
    header('Location: index.php');
    exit;
}

// Kategoriyi bul
$current_category = null;
foreach ($categories as $category) {
    if ($category['id'] == $current_topic['category_id']) {
        $current_category = $category;
        break;
    }
}

// Görüntülenme sayısını artır
$topics[$topic_index]['views']++;
file_put_contents('data/topics.json', json_encode($topics, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
$current_topic['views']++;

// Yanıtları yükle
$replies = [];
if (file_exists('data/replies.json')) {
    $all_replies = json_decode(file_get_contents('data/replies.json'), true);
    $replies = array_filter($all_replies, function($reply) use ($topic_id) {
        return $reply['topic_id'] == $topic_id;
    });
    // Tarihe göre sırala
    usort($replies, function($a, $b) {
        return strtotime($a['created_at']) - strtotime($b['created_at']);
    });
} else {
    file_put_contents('data/replies.json', json_encode([], JSON_PRETTY_PRINT));
}

// Yanıt gönderme
$error = '';
$success = '';
if ($_POST && isset($_SESSION['user_id'])) {
    $reply_content = trim($_POST['content']);
    
    if (empty($reply_content)) {
        $error = 'Yanıt içeriği boş olamaz.';
    } else {
        // Yeni yanıt oluştur
        $new_reply = [
            'id' => time() . rand(1000, 9999),
            'topic_id' => $topic_id,
            'content' => $reply_content,
            'author' => $_SESSION['username'],
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $replies[] = $new_reply;
        
        // Tüm yanıtları yükle ve yeni yanıtı ekle
        $all_replies = [];
        if (file_exists('data/replies.json')) {
            $all_replies = json_decode(file_get_contents('data/replies.json'), true);
        }
        $all_replies[] = $new_reply;
        
        // Yanıtları kaydet
        file_put_contents('data/replies.json', json_encode($all_replies, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        // Konu yanıt sayısını güncelle
        $topics[$topic_index]['replies'] = count($replies);
        file_put_contents('data/topics.json', json_encode($topics, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        $success = 'Yanıtınız başarıyla gönderildi!';
        
        // Sayfayı yenile
        header('Location: topic.php?id=' . $topic_id . '#reply-' . $new_reply['id']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($current_topic['title']) ?> - TurkForum</title>
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
            max-width: 1000px;
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

        .topic-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }

        .topic-header {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #eee;
        }

        .topic-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .topic-info {
            flex: 1;
        }

        .topic-title {
            font-size: 1.8rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .topic-meta {
            display: flex;
            gap: 1.5rem;
            color: #666;
            font-size: 0.9rem;
        }

        .topic-content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #444;
            margin-bottom: 1.5rem;
        }

        .topic-stats {
            display: flex;
            gap: 2rem;
            padding: 1rem;
            background: rgba(102, 126, 234, 0.05);
            border-radius: 10px;
            justify-content: center;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 1.2rem;
            font-weight: bold;
            color: #667eea;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #666;
        }

        .replies-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }

        .replies-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #eee;
        }

        .reply-item {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
        }

        .reply-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        }

        .reply-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .reply-author {
            font-weight: bold;
            color: #667eea;
        }

        .reply-date {
            color: #666;
            font-size: 0.9rem;
        }

        .reply-content {
            line-height: 1.6;
            color: #444;
        }

        .reply-form {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
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

        textarea {
            width: 100%;
            min-height: 120px;
            padding: 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            resize: vertical;
            transition: border-color 0.3s ease;
        }

        textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .error {
            background: #fee;
            color: #c33;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #fcc;
        }

        .success {
            background: #efe;
            color: #363;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #cfc;
        }

        .pinned-badge {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: 1rem;
        }

        .login-prompt {
            text-align: center;
            padding: 2rem;
            color: #666;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }

            .topic-header {
                flex-direction: column;
                text-align: center;
            }

            .topic-meta {
                justify-content: center;
                flex-wrap: wrap;
            }

            .topic-stats {
                flex-direction: column;
                gap: 1rem;
            }

            .replies-header {
                flex-direction: column;
                gap: 1rem;
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
                <a href="category.php?id=<?= $current_category['id'] ?>"><?= htmlspecialchars($current_category['name']) ?></a> › 
                <span><?= htmlspecialchars($current_topic['title']) ?></span>
            </div>

            <!-- Topic -->
            <div class="topic-container">
                <div class="topic-header">
                    <div class="topic-icon">
                        <?php if ($current_topic['pinned']): ?>
                            <i class="fas fa-thumbtack"></i>
                        <?php else: ?>
                            <i class="fas fa-comment"></i>
                        <?php endif; ?>
                    </div>
                    <div class="topic-info">
                        <h1 class="topic-title">
                            <?= htmlspecialchars($current_topic['title']) ?>
                            <?php if ($current_topic['pinned']): ?>
                                <span class="pinned-badge">📌 Sabitlenmiş</span>
                            <?php endif; ?>
                        </h1>
                        <div class="topic-meta">
                            <span>
                                <i class="fas fa-user"></i> <?= htmlspecialchars($current_topic['author']) ?>
                            </span>
                            <span>
                                <i class="fas fa-clock"></i> <?= date('d.m.Y H:i', strtotime($current_topic['created_at'])) ?>
                            </span>
                            <span>
                                <i class="fas fa-folder"></i> <?= htmlspecialchars($current_category['name']) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="topic-content">
                    <?= nl2br(htmlspecialchars($current_topic['content'])) ?>
                </div>

                <div class="topic-stats">
                    <div class="stat-item">
                        <div class="stat-number"><?= count($replies) ?></div>
                        <div class="stat-label">Yanıt</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?= $current_topic['views'] ?></div>
                        <div class="stat-label">Görüntülenme</div>
                    </div>
                </div>
            </div>

            <!-- Replies -->
            <?php if (!empty($replies)): ?>
                <div class="replies-section">
                    <div class="replies-header">
                        <h2><i class="fas fa-comments"></i> Yanıtlar (<?= count($replies) ?>)</h2>
                    </div>

                    <?php foreach ($replies as $reply): ?>
                        <div class="reply-item" id="reply-<?= $reply['id'] ?>">
                            <div class="reply-header">
                                <span class="reply-author">
                                    <i class="fas fa-user"></i> <?= htmlspecialchars($reply['author']) ?>
                                </span>
                                <span class="reply-date">
                                    <i class="fas fa-clock"></i> <?= date('d.m.Y H:i', strtotime($reply['created_at'])) ?>
                                </span>
                            </div>
                            <div class="reply-content">
                                <?= nl2br(htmlspecialchars($reply['content'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Reply Form -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="reply-form">
                    <h3><i class="fas fa-reply"></i> Yanıt Yaz</h3>
                    
                    <?php if ($error): ?>
                        <div class="error">
                            <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="success">
                            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label for="content">Yanıtınız:</label>
                            <textarea id="content" name="content" 
                                      placeholder="Yanıtınızı buraya yazın..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Yanıt Gönder
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="login-prompt">
                    <i class="fas fa-lock" style="font-size: 2rem; margin-bottom: 1rem; color: #ddd;"></i>
                    <h3>Yanıt yazmak için giriş yapın</h3>
                    <p>Bu konuya yanıt yazabilmek için önce hesabınıza giriş yapmanız gerekiyor.</p>
                    <br>
                    <a href="login.php" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Giriş Yap
                    </a>
                    <a href="register.php" class="btn btn-secondary">
                        <i class="fas fa-user-plus"></i> Kayıt Ol
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        // Animasyonlar
        document.addEventListener('DOMContentLoaded', function() {
            // Reply items animasyonu
            const replyItems = document.querySelectorAll('.reply-item');
            replyItems.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    item.style.transition = 'all 0.5s ease';
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Textarea auto-resize
            const textarea = document.getElementById('content');
            if (textarea) {
                textarea.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = this.scrollHeight + 'px';
                });
            }
        });
    </script>
</body>
</html>