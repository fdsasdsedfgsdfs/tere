<?php
session_start();

// Giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Kullanıcı bilgilerini yükle
$users = json_decode(file_get_contents('data/users.json'), true);
$current_user = null;
foreach ($users as $user) {
    if ($user['id'] == $_SESSION['user_id']) {
        $current_user = $user;
        break;
    }
}

// Konular ve yanıtları yükle
$topics = json_decode(file_get_contents('data/topics.json'), true);
$user_topics = array_filter($topics, function($topic) {
    return $topic['author'] === $_SESSION['username'];
});

$replies = [];
if (file_exists('data/replies.json')) {
    $all_replies = json_decode(file_get_contents('data/replies.json'), true);
    $replies = array_filter($all_replies, function($reply) {
        return $reply['author'] === $_SESSION['username'];
    });
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - <?= htmlspecialchars($_SESSION['username']) ?> - TurkForum</title>
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

        /* Main Content */
        main {
            padding: 2rem 0;
        }

        .profile-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 2px solid #eee;
        }

        .avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
        }

        .profile-info h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(102, 126, 234, 0.1);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }

        .recent-activity {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }

        .activity-item {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            transition: background 0.3s ease;
        }

        .activity-item:hover {
            background: rgba(102, 126, 234, 0.05);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .activity-meta {
            color: #666;
            font-size: 0.9rem;
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

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }

            .profile-header {
                flex-direction: column;
                text-align: center;
            }

            .profile-stats {
                grid-template-columns: repeat(2, 1fr);
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
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-home"></i> Ana Sayfa
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
                <span>Profil</span>
            </div>

            <!-- Profile Info -->
            <div class="profile-container">
                <div class="profile-header">
                    <div class="avatar">
                        <?= $current_user['avatar'] ?>
                    </div>
                    <div class="profile-info">
                        <h1><?= htmlspecialchars($current_user['username']) ?></h1>
                        <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($current_user['email']) ?></p>
                        <p><i class="fas fa-calendar"></i> Üyelik: <?= date('d.m.Y', strtotime($current_user['created_at'])) ?></p>
                        <p><i class="fas fa-crown"></i> <?= $current_user['role'] === 'admin' ? 'Yönetici' : 'Üye' ?></p>
                    </div>
                </div>

                <div class="profile-stats">
                    <div class="stat-card">
                        <div class="stat-number"><?= count($user_topics) ?></div>
                        <div class="stat-label">Açtığı Konu</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?= count($replies) ?></div>
                        <div class="stat-label">Yazdığı Yanıt</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?= array_sum(array_column($user_topics, 'views')) ?></div>
                        <div class="stat-label">Toplam Görüntülenme</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?= date_diff(date_create($current_user['created_at']), date_create())->days ?></div>
                        <div class="stat-label">Gün Önce Katıldı</div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="recent-activity">
                <h2 style="margin-bottom: 1.5rem;">
                    <i class="fas fa-history"></i> Son Aktiviteler
                </h2>

                <?php 
                $recent_topics = array_slice(array_reverse($user_topics), 0, 5);
                $recent_replies = array_slice(array_reverse($replies), 0, 5);
                ?>

                <?php if (!empty($recent_topics)): ?>
                    <h3 style="margin-bottom: 1rem; color: #667eea;">Son Açtığı Konular</h3>
                    <?php foreach ($recent_topics as $topic): ?>
                        <div class="activity-item">
                            <div class="activity-title">
                                <a href="topic.php?id=<?= $topic['id'] ?>" style="color: #333; text-decoration: none;">
                                    <?= htmlspecialchars($topic['title']) ?>
                                </a>
                            </div>
                            <div class="activity-meta">
                                <i class="fas fa-clock"></i> <?= date('d.m.Y H:i', strtotime($topic['created_at'])) ?> - 
                                <i class="fas fa-eye"></i> <?= $topic['views'] ?> görüntülenme - 
                                <i class="fas fa-comments"></i> <?= $topic['replies'] ?> yanıt
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if (!empty($recent_replies)): ?>
                    <h3 style="margin: 2rem 0 1rem; color: #667eea;">Son Yanıtları</h3>
                    <?php foreach ($recent_replies as $reply): ?>
                        <div class="activity-item">
                            <div class="activity-title">
                                Bir konuya yanıt verdi
                            </div>
                            <div class="activity-meta">
                                <i class="fas fa-clock"></i> <?= date('d.m.Y H:i', strtotime($reply['created_at'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if (empty($recent_topics) && empty($recent_replies)): ?>
                    <div style="text-align: center; padding: 2rem; color: #666;">
                        <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; color: #ddd;"></i>
                        <h3>Henüz aktivite yok</h3>
                        <p>Henüz hiç konu açmadınız veya yanıt yazmadınız.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        // Animasyonlar
        document.addEventListener('DOMContentLoaded', function() {
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>