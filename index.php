<?php
// ★ セッションを開始（これがないとログイン状態や$_SESSIONが機能しません）
session_start();

// ========================================
//    ヘッダー
// ========================================
require 'header.php';
require_once 'db_connect.php';

// ========================================
// ① データベース接続＆おすすめ車両（3台）の取得
// ========================================


try {
    // ========================================
    // ② データベースへ接続
    // ========================================

    // PDOを使ってMySQLへ接続する
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            // SQLエラーが発生した場合、例外を発生させる
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // データを連想配列として取得する
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // SQLインジェクション対策のため、プリペアドステートメントを使用
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    // ========================================
    // ③ おすすめ車両を3台取得
    // ========================================

    // goodsテーブルから車両情報を取得
    // ORDER BY RAND() → データをランダムな順番にする
    // LIMIT 3 → 3件だけ取得する
    $sql = "SELECT * FROM goods ORDER BY RAND() LIMIT 3";
    // SQLを実行
    $stmt = $pdo->query($sql);
    // 取得したデータをすべて配列に入れる
    $products = $stmt->fetchAll();

    //ハートマーク表示
    $user_favorite_ids = [];
    if (isset($_SESSION['customer']['id'])) {
        $favStmt = $pdo->prepare("SELECT goods_id FROM favorite WHERE user_id = ?");
        $favStmt->execute([$_SESSION['customer']['id']]);
        // ユーザーが登録している goods_id の配列を取得 (例:)
        $user_favorite_ids = $favStmt->fetchAll(PDO::FETCH_COLUMN);
    }

} catch (PDOException $e) {
    // データベース接続やSQLでエラーが発生した場合
    // エラーメッセージを表示して処理を終了する
    exit('データ取得エラー: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <!-- Font Awesomeを使用する場合のCSS
    現在はコメントアウトされている -->
    <!-- https://cloudflare.com -->
    
    <link rel="stylesheet" href="css/main.css">   
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Live motors</title> 
</head> 
<body> 

     
    <!-- ========================================
     ④ メイン画像スライダー
     ======================================== -->
<div class="main-slider"> 
    <!-- 最初に表示する画像
    activeクラスが付いているので最初に表示される -->
    <img src="img/main_slide1.png" class="slide active" alt="店舗外観"> 
    <!-- 2枚目の画像 -->
    <img src="img/main_slide2.png" class="slide" alt="店内"> 
    <!-- 3枚目の画像 -->
    <img src="img/main_slide3.png" class="slide" alt="展示車両"> 
    <!-- スライダーの上に表示する文章 -->
    <div class="main-copy"> 
        <h1>ちょうどいい一台が、<br>きっと見つかる。</h1> 
        <p>街の小さなお店から、あなたのカーライフを応援します。</p> 
    </div> 
</div> 
    <!-- ========================================
    ⑤ 車両カテゴリーのメニュー
    ======================================== -->
    <nav> 
        <ul> 
            <li><a href="products.php?model=軽自動車"><i class="fa-solid fa-car"></i>軽自動車</a></li> 
            <li><a href="products.php?model=コンパクトカー"><i class="fa-solid fa-car"></i>コンパクトカー</a></li> 
            <li><a href="products.php?model=SUV"><i class="fa-solid fa-car"></i>SUV</a></li> 
            <li><a href="products.php?model=セダン"><i class="fa-solid fa-car"></i>セダン</a></li>
            <li><a href="products.php"><i class="fa-solid fa-car"></i>全車両</a></li> 
        </ul> 
    </nav> 
    <!-- ========================================
     ⑥ おすすめ車両
     ======================================== -->
 
    <h2>おすすめの車</h2> 
 
    <!-- ======================================== 
         おすすめの車一覧 
         ======================================== --> 
    <div class="car-list"> 
        <?php if (count($products) === 0): ?> 
            <!-- 車両が1台もない場合に表示する文章 -->
            <p style="grid-column: 1 / -1; text-align: center; color: #666;">現在おすすめの車両はありません。</p> 
        <?php else: ?> 
            <?php foreach ($products as $row): ?>
                <?php
                // 現在の車両がお気に入りに入っているか判定
                $is_fav = in_array($row['goods_id'], $user_favorite_ids);
                ?>
                <!-- ========================================
                ⑦ 車両1台分の表示
                ======================================== -->
                <figure style="position: relative;"> 
                    <!-- 車両情報への詳細リンク --> 
                    <a href="product_detail.php?id=<?= (int)$row['goods_id'] ?>" style="text-decoration: none; color: inherit; display: block;"> 
                         
                        <!-- ① 車両画像 --> 
                        <img src="<?= htmlspecialchars($row['carphoto_main'], ENT_QUOTES, 'UTF-8') ?>" 
                             alt="<?= htmlspecialchars($row['car_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                             onerror="this.onerror=null; this.src='images/no-image.jpg';"> 
 
                        <!-- ② 車両テキスト情報エリア --> 
                        <figcaption> 
                            <!-- 車名（メーカー＋車種＋グレード） --> 
                            <div class="car-name" style="margin-bottom: 8px;"> 
                                <?= htmlspecialchars($row['maker'] ?? '', ENT_QUOTES, 'UTF-8') ?> 
                                <?= htmlspecialchars($row['car_name'] ?? '', ENT_QUOTES, 'UTF-8') ?> 
                                <?= htmlspecialchars($row['car_grade'] ?? '', ENT_QUOTES, 'UTF-8') ?> 
                            </div> 
 
                            <!-- ③ 車両スペック情報（年式、走行距離、車検） --> 
                            <div class="specs-grid" style="display: flex; gap: 10px; font-size: 12px; color: #666; margin-bottom: 8px; font-weight: normal;"> 
                                <span><?= htmlspecialchars($row['model_year'] ?? '', ENT_QUOTES, 'UTF-8') ?>年式</span> 
                                <span>走行距離 <?= number_format($row['milage']) ?>km</span> 
                                <span>車検 <?= htmlspecialchars($row['inspection_date'] ?? '', ENT_QUOTES, 'UTF-8') ?></span> 
                            </div> 
 
                            <!-- ④ 価格 --> 
                            <div class="price-box" style="font-size: 18px; font-weight: bold; color: #d32f2f;"> 
                                <?= number_format($row['price']) ?>円 
                                <span style="font-size: 12px; color: #222; font-weight: normal; margin-left: 4px;">税込</span> 
                            </div> 
                        </figcaption> 
                    </a> 
                    
                    <!-- お気に入りハートボタンをPOST送信のフォームに変更 -->
                    <form action="favorite.php" method="POST" style="position: absolute; top: 10px; right: 10px; z-index: 10;">
                        <?php if ($is_fav): ?>
                            <!-- すでにお気に入りなら、押したときに削除(delete_id)を送る -->
                            <input type="hidden" name="delete_id" value="<?= (int)$row['goods_id'] ?>">
                        <?php else: ?>
                            <!-- 未登録なら、押したときに追加(add_id)を送る -->
                            <input type="hidden" name="add_id" value="<?= (int)$row['goods_id'] ?>">
                        <?php endif; ?>
                        
                        <button type="submit" class="favorite-link <?= $is_fav ? 'active' : '' ?>"
                                style="background: rgba(255,255,255,0.8); border: none; border-radius: 50%; width: 32px; height: 32px; cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 0; color: <?= $is_fav ? '#ff4081' : '#666' ?>;">
                            <i class="<?= $is_fav ? 'fa-solid' : 'fa-regular' ?> fa-heart"></i>
                        </button>
                    </form>

                </figure> 
            <?php endforeach; ?> 
        <?php endif; ?> 
    </div> 
    <!-- ========================================
    フッター
    ======================================== -->
    <?php require 'footer.php'; ?> 

<script src="js/main.js"></script>    
 
</body> 
</html> 
