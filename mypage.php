<?php

// ========================================
// 1. セッション管理・ログイン処理の初期化
// ========================================
require_once 'Modallogin.php'; 

// 未ログイン状態であれば、トップページ（main.php）にリダイレクトして終了
if (!isset($_SESSION['customer'])) {
    header('Location: index.php');
    exit();
}

// ========================================
// 2. ユーザー情報と商談情報の取得準備
// ========================================
$customer = $_SESSION['customer'] ?? null;

// 複数台を格納するために配列として初期化
$negotiations = [];

// ========================================
// 3. データベースからの商談中データ取得処理
// ========================================
if ($customer && !empty($customer['userid'])) {
    try {
        // 「受付済」「商談中」の双方に対応し、複数件取得するため LIMIT 1 を除外
        $stmt = $pdo->prepare("
            SELECT h.purchaseid,
                   h.applicationdate, 
                   h.status AS nego_status,
                   g.goods_id, 
                   g.car_name, 
                   g.model_year, 
                   g.milage, 
                   g.color,
                   g.carphoto_main
            FROM history h
            JOIN goods g ON h.productid = g.goods_id
            WHERE h.user_id = ? 
              AND (h.status = '商談中' OR h.status = '受付済')
            ORDER BY h.applicationdate DESC
        ");
        
        $stmt->execute([$customer['userid']]);
        
        // fetchAll() で該当する全件を取得
        $negotiations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        $negotiations = [];
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>マイページ - Live motors</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- カスタムCSS -->
    <link rel="stylesheet" href="css/style.css">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: sans-serif;
        }
        .mypage-container {
            max-width: 1160px;
            margin: 30px auto;
            display: flex;
            gap: 30px;
            padding: 0 15px;
        }
        .mypage-sidebar {
            width: 260px;
            flex-shrink: 0;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            padding: 20px;
            height: fit-content;
        }
        .sidebar-title {
            font-size: 1.1rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            color: #333;
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.95rem;
            transition: background 0.2s;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: #eaf2ed;
            color: #198754;
            font-weight: bold;
        }
        .sidebar-menu i {
            font-size: 1.1rem;
        }
        .mypage-main {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .profile-card {
            background: #ffffff;
            border-radius: 8px;
            padding: 20px 25px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .profile-avatar {
            width: 60px;
            height: 60px;
            background-color: #ced4da;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 2rem;
        }
        .profile-info h2 {
            font-size: 1.25rem;
            margin: 0 0 5px 0;
            font-weight: bold;
        }
        .profile-info p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        .section-box {
            background: #ffffff;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .section-box h3 {
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }
        .negotiation-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            display: flex;
            gap: 20px;
            align-items: center;
            margin-bottom: 20px;
        }
        .negotiation-img {
            width: 240px;
            height: 160px;
            object-fit: cover;
            border-radius: 6px;
        }
        .negotiation-details {
            flex-grow: 1;
        }
        .negotiation-details h4 {
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .spec-row {
            display: grid;
            grid-template-columns: 120px 1fr;
            font-size: 0.9rem;
            margin-bottom: 8px;
            color: #555;
        }
        .status-badge {
            color: #d32f2f;
            font-weight: bold;
        }
        .btn-detail {
            display: block;
            width: 100%;
            text-align: center;
            background: #ffffff;
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 6px;
            color: #333;
            text-decoration: none;
            font-weight: bold;
            margin-top: 15px;
        }
        .btn-detail:hover {
            background: #f8f9fa;
        }
    </style>
</head>
<body>

    <!-- 共通ヘッダー -->
    <?php require 'header.php'; ?>

    <div class="mypage-container">

        <!-- 左側：サイドメニュー -->
        <aside class="mypage-sidebar">
            <div class="sidebar-title">マイページ</div>
            <ul class="sidebar-menu">
                <li><a href="mypage.php" class="active"><i class="bi bi-house-door"></i> ホーム</a></li>
                <li><a href="favorite.php"><i class="bi bi-heart"></i> お気に入り</a></li>
                
                <?php if ($customer): ?>
                    <li><a href="#" data-bs-toggle="modal" data-bs-target="#customerModal"><i class="bi bi-person"></i> お客様情報の確認・変更</a></li>
                <?php else: ?>
                    <li><a href="#" data-bs-toggle="modal" data-bs-target="#customerModal"><i class="bi bi-person-plus"></i> 新規会員登録</a></li>
                <?php endif; ?>

                <li><a href="contact.php"><i class="bi bi-envelope"></i> お問い合わせ</a></li>
                
                <?php if ($customer): ?>
                    <li><a href="?logout=1"><i class="bi bi-box-arrow-right"></i> ログアウト</a></li>
                <?php endif; ?>
            </ul>
        </aside>

        <!-- 右側：メインコンテンツ -->
        <main class="mypage-main">
            <!-- プロフィールヘッダー -->
            <div class="profile-card">
                <div class="profile-avatar">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div class="profile-info">
                    <h2>
                        <?= htmlspecialchars($customer['last_name'] ?? '', ENT_QUOTES, 'UTF-8') ?> 
                        <?= htmlspecialchars($customer['first_name'] ?? '', ENT_QUOTES, 'UTF-8') ?> 様
                    </h2>
                    <p>いつもご利用いただきありがとうございます。</p>
                </div>
            </div>

            <!-- 商談中の車一覧 -->
            <div class="section-box">
                <h3>商談中の車</h3>

                <?php if (!empty($negotiations)): ?>
                    <?php foreach ($negotiations as $negotiation): ?>
                        <div class="negotiation-card">
                            <img src="<?= htmlspecialchars($negotiation['carphoto_main'] ?? 'images/no-image.jpg', ENT_QUOTES, 'UTF-8') ?>" alt="" class="negotiation-img">
                        
                            <div class="negotiation-details">
                                <h4><?= htmlspecialchars($negotiation['car_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></h4>

                                <div class="spec-row">
                                    <span class="label"><i class="bi bi-tag"></i> 商談番号</span>
                                    <span>: <?= htmlspecialchars($negotiation['purchaseid'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                                </div>

                                <div class="spec-row">
                                    <span class="label"><i class="bi bi-calendar"></i> 申込日</span>
                                    <span>: <?= htmlspecialchars($negotiation['applicationdate'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                                </div>

                                <div class="spec-row">
                                    <span class="label"><i class="bi bi-car-front"></i> 年式 / 走行距離</span>
                                    <span>: <?= htmlspecialchars($negotiation['model_year'] ?? '', ENT_QUOTES, 'UTF-8') ?>年 / <?= number_format($negotiation['milage'] ?? 0) ?>km</span>
                                </div>

                                <div class="spec-row">
                                    <span class="label"><i class="bi bi-palette"></i> カラー</span>
                                    <span>: <?= htmlspecialchars($negotiation['color'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                                </div>

                                <div class="spec-row">
                                    <span class="label"><i class="bi bi-info-circle"></i> ステータス</span>
                                    <span class="status-badge">: <?= htmlspecialchars($negotiation['nego_status'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                                </div>

                                <a href="product_detail.php?id=<?= htmlspecialchars($negotiation['goods_id'], ENT_QUOTES, 'UTF-8') ?>" class="btn-detail">詳細を見る</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted mb-0">現在、商談中の車はありません。</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- 共通フッター -->
    <?php require 'footer.php'; ?>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>