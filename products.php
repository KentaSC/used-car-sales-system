<?php
session_start();
// ========================================
// ① データベース接続
// ========================================
/* データベース接続設定を読み込む */
require_once __DIR__ . '/db_config.php';

try {

    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    // ハートマーク表示用のお気に入りID取得
    $user_favorite_ids = [];
    if (isset($_SESSION['customer']['id'])) {
        $favStmt = $pdo->prepare("SELECT goods_id FROM favorite WHERE user_id = ?");
        $favStmt->execute([$_SESSION['customer']['id']]);
        $user_favorite_ids = $favStmt->fetchAll(PDO::FETCH_COLUMN);
    }

} catch (PDOException $e) {

    // 接続できない場合に原因を確認しやすくする
    $errorMessage = $e->getMessage();

    echo '<!DOCTYPE html>';
    echo '<html lang="ja">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>データベース接続エラー</title>';

    echo '<style>
        body {
            margin: 0;
            padding: 40px 20px;
            background: #f7f8f7;
            color: #222;
            font-family: Arial, "Yu Gothic", sans-serif;
        }

        .error-box {
            max-width: 700px;
            margin: 40px auto;
            padding: 30px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,.08);
        }

        h1 {
            margin-top: 0;
            color: #b42318;
            font-size: 24px;
        }

        .message {
            margin: 20px 0;
            padding: 15px;
            background: #fff3f2;
            border-radius: 8px;
            line-height: 1.7;
            word-break: break-word;
        }

        .check-list {
            padding-left: 20px;
            line-height: 2;
        }

        .code {
            padding: 3px 7px;
            background: #f1f1f1;
            border-radius: 4px;
            font-family: Consolas, monospace;
        }
    </style>';

    echo '</head>';
    echo '<body>';

    echo '<div class="error-box">';
    echo '<h1>データベースに接続できません</h1>';

    echo '<div class="message">';
    echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8');
    echo '</div>';

    echo '<h2>確認してください</h2>';
    echo '<ol class="check-list">';
    echo '<li>XAMPPの <strong>MySQL</strong> が起動しているか</li>';
    echo '<li>phpMyAdminに <span class="code">test</span> データベースが存在するか</li>';
    echo '<li>MySQLのユーザー名が <span class="code">root</span> になっているか</li>';
    echo '<li>rootにパスワードを設定している場合は、<span class="code">$dbPass</span> を変更する</li>';
    echo '</ol>';

    echo '</div>';
    echo '</body>';
    echo '</html>';

    exit;
}

// ========================================
// ② 検索条件を取得
// ========================================

$sort = filter_input(INPUT_GET, 'sort') ?? 'new';
$maker = filter_input(INPUT_GET, 'maker') ?? '';
$model = filter_input(INPUT_GET, 'model') ?? '';
$price_min = filter_input(INPUT_GET, 'price_min') ?? '';
$price_max = filter_input(INPUT_GET, 'price_max') ?? '';

// ========================================
// ③ メーカーをDBから取得
// ========================================

$makerSql = "
    SELECT DISTINCT maker
    FROM goods
    WHERE maker IS NOT NULL
    AND maker != ''
    ORDER BY maker
";

$makerStmt = $pdo->query($makerSql);
$makers = $makerStmt->fetchAll();

// ========================================
// ④ 車種をDBから取得
// ========================================

if ($maker !== '') {

    $modelSql = "
        SELECT DISTINCT car_model
        FROM goods
        WHERE maker = :maker
        AND car_model IS NOT NULL
        AND car_model != ''
        ORDER BY car_name
    ";

    $modelStmt = $pdo->prepare($modelSql);
    $modelStmt->execute([
        ':maker' => $maker
    ]);

    

} else {

    $modelSql = "
        SELECT DISTINCT car_model
        FROM goods
        WHERE car_model IS NOT NULL
        AND car_model != ''
        ORDER BY car_model
    ";

    $modelStmt = $pdo->query($modelSql);
}

$models = $modelStmt->fetchAll();

// ========================================
// ⑤ 価格選択肢を作成
// ========================================

// 価格を保存するための空の配列を作ります。
$prices = [];


// ========================================
// 50万円～100万円
// 10万円刻み
// ========================================

// 500,000円から1,000,000円まで
// 100,000円ずつ増やします。
for ($price = 500000; $price <= 1000000; $price += 100000) {

    // 価格を配列に追加します。
    $prices[] = $price;
}


// ========================================
// 100万円～200万円
// 20万円刻み
// ========================================

// 100万円は上のfor文ですでに追加されているため、
// 120万円から開始します。
for ($price = 1200000; $price <= 2000000; $price += 200000) {

    // 価格を配列に追加します。
    $prices[] = $price;
}


// ========================================
// 200万円～500万円
// 50万円刻み
// ========================================

// 200万円は上のfor文ですでに追加されているため、
// 250万円から開始します。
for ($price = 2500000; $price <= 10000000; $price += 500000) {

    // 価格を配列に追加します。
    $prices[] = $price;
}

// ========================================
// ⑥ 車両検索SQL
// ========================================

$sql = "
    SELECT *
    FROM goods
    WHERE 1=1
";

$params = [];

// メーカー
if ($maker !== '') {

    $sql .= " AND maker = :maker";

    $params[':maker'] = $maker;
}

// 車種
if ($model !== '' && $model !== '全車両') {

    $sql .= " AND car_model = :model";

    $params[':model'] = $model;
}

// 最低価格
if ($price_min !== '') {

    $sql .= " AND price >= :price_min";

    $params[':price_min'] = (int)$price_min;
}

// 最高価格
if ($price_max !== '') {

    $sql .= " AND price <= :price_max";

    $params[':price_max'] = (int)$price_max;
}

// ========================================
// ⑦ 並び替え
// ========================================

if ($sort === 'price_asc') {

    $sql .= " ORDER BY price ASC";

} elseif ($sort === 'price_desc') {

    $sql .= " ORDER BY price DESC";

} else {

    // 新着順
    $sql .= " ORDER BY date_time DESC";
}

// ========================================
// ⑧ SQL実行
// ========================================

try {

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

} catch (PDOException $e) {

    echo '<div style="
        max-width:700px;
        margin:40px auto;
        padding:25px;
        background:#fff3f2;
        border:1px solid #f0b8b3;
        border-radius:10px;
        color:#7a1f18;
        font-family:Arial, \"Yu Gothic\", sans-serif;
        line-height:1.7;
    ">';

    echo '<strong>車両データの取得に失敗しました。</strong><br>';
    echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');

    echo '</div>';

    exit;
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >
    <title>Live motors | 中古車検索</title>
    <link
        rel="stylesheet"
        href="css/products.css"
    >
</head>
<body>
<?php require 'header.php'; ?>


<div class="container">

    <!-- ========================================
         検索フォーム
    ======================================== -->
    <form method="GET" action="" class="search-panel">

        <!-- 並び替え -->
        <div class="form-group">
            <label>並び替え</label>
            <select name="sort">
                <option
                    value="new"
                    <?= $sort === 'new' ? 'selected' : '' ?>
                >
                    新着順
                </option>
                <option
                    value="price_asc"
                    <?= $sort === 'price_asc' ? 'selected' : '' ?>
                >
                    価格の安い順
                </option>
                <option
                    value="price_desc"
                    <?= $sort === 'price_desc' ? 'selected' : '' ?>
                >
                    価格の高い順
                </option>
            </select>
        </div>

        <!-- メーカー -->
        <div class="form-group">
            <label>メーカー</label>
            <select
                name="maker"
            >
                <option value="">
                    すべてのメーカー
                </option>

                <?php foreach ($makers as $makerRow): ?>

                    <option
                        value="<?= htmlspecialchars(
                            $makerRow['maker'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        <?= $maker === $makerRow['maker']
                            ? 'selected'
                            : '' ?>
                    >
                        <?= htmlspecialchars(
                            $makerRow['maker'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </option>

                <?php endforeach; ?>

            </select>
        </div>

        <div class="form-group">
            <label>車種</label>
            <select name="model">
                <option value="">
                    すべての車種
                </option>

                <?php foreach ($models as $modelRow): ?>
                    <?php 
                    $currentModel = $modelRow['car_model'] ?? ''; 
                    ?>
                    <option
                        value="<?= htmlspecialchars($currentModel, ENT_QUOTES, 'UTF-8') ?>"
                        <?= (isset($model) && $model === $currentModel) ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($currentModel, ENT_QUOTES, 'UTF-8') ?>
                    </option>

                <?php endforeach; ?>

            </select>
        </div>


        <!-- 価格 -->
        <div class="form-group">
            <label>価格</label>

            <div class="price-range">

                <!-- ================================ -->
                <!-- 価格の下限 -->
                <!-- ================================ -->
                <select name="price_min">

                    <!-- 下限を指定しない -->
                    <option value="">下限なし</option>

                    <?php foreach ($prices as $price): ?>

                        <option
                            value="<?= $price ?>"
                            <?= $price_min == $price ? 'selected' : '' ?>
                        >
                            <?= number_format($price) ?>円～
                        </option>

                    <?php endforeach; ?>

                </select>


                <span>～</span>


                <!-- ================================ -->
                <!-- 価格の上限 -->
                <!-- ================================ -->
                <select name="price_max">

                    <!-- 上限を指定しない -->
                    <option value="">上限なし</option>

                    <?php foreach ($prices as $price): ?>

                        <option
                            value="<?= $price ?>"
                            <?= $price_max == $price ? 'selected' : '' ?>
                        >
                            ～<?= number_format($price) ?>円
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>
        </div>

        <!-- 検索ボタン -->
        <button
            type="submit"
            class="submit-btn">
            検索
        </button>
    </form>

    <!-- ========================================
         検索結果件数
    ======================================== -->

    <div class="result-bar">
        検索結果 :
        <?= count($products) ?>
        台
    </div>

    <!-- ========================================
         車両一覧
    ======================================== -->
<div class="car-grid">

    <?php if (count($products) === 0): ?>

        <div class="no-result">
            条件に一致する車両がありません。
        </div>

        <?php else: ?>
        <?php foreach ($products as $row): ?>
            <?php
            // ★ 現在の車両がお気に入りに入っているか判定
            $is_fav = in_array($row['goods_id'], $user_favorite_ids);
            ?>
            <div class="car-card" style="position: relative;"> <!-- 親要素にrelativeを指定して配置を安定させます -->
                <!-- 車両情報へのリンク -->
                <a href="product_detail.php?id=<?= (int)$row['goods_id'] ?>" 
                class="car-link"
                >
                    <!-- 車両画像 -->
                    <div class="car-img-box">
                        <img
                            src="<?= htmlspecialchars(
                                $row['carphoto_main'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            alt="<?= htmlspecialchars(
                                $row['car_name'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            class="car-photo"
                            onerror="this.onerror=null; this.src='imag/no-image.jpg';"
                        >
                    </div>

                    <!-- 車名 -->
                    <div class="car-title-row">
                        <div class="car-name">

                            <?= htmlspecialchars(
                                $row['maker'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                            <?= htmlspecialchars(
                                $row['car_name'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                            <?= htmlspecialchars(
                                $row['car_grade'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </div>
                    </div>

                    <!-- 車両情報 -->
                    <div class="specs-grid">

                        <span class="spec-item">
                            <?= htmlspecialchars(
                                $row['model_year'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>年式
                        </span>

                        <span class="spec-item">
                            走行距離
                            <?= number_format($row['milage']) ?>km
                        </span>

                        <span class="spec-item">
                            車検
                            <?= htmlspecialchars(
                                $row['inspection_date'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </span>
                    </div>

                    <!-- 価格 -->
                    <div class="price-box">
                        <?= number_format($row['price']) ?>円
                        <span>税込</span>
                    </div>

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

            </div>

        <?php endforeach; ?>
    <?php endif; ?>

</div>
<?php require 'footer.php'; ?>

<script src="js/products.js"></script>

</body>
</html>