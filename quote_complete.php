<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // POST以外の直接アクセスを弾く
    header('Location: request_estimate.php');
    exit;
}

// 1. POSTデータを受け取る（purchase_confirm.php のフォームから送られてくる値）
$goods_id            = $_POST['goods_id'] ?? null;
$consultation_method = $_POST['consultation_method'] ?? '';
$payment_method      = $_POST['payment_method'] ?? '';
$contact_method      = $_POST['contact_method'] ?? '';
$message             = $_POST['message'] ?? '';

// セッションからログインユーザーIDを取得（ログイン機能がある場合）
$user_id = $_SESSION['customer']['userid'] ?? null; 
if (!$user_id) {
    header('Location: index.php'); // ログイン画面のURLへ変更してください
    exit;
}
// DB接続設定
require_once __DIR__ . '/db_config.php';

$apply_data = [];
$car_data   = [];

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

    // ★ 2. 画像のテーブル構造（カラム名）に合わせて INSERT
    $sql = "INSERT INTO history (
                user_id,
                productid,
                applicationdate,
                consultation_method,
                status,
                request,
                paymentmethod,
                contact,
                createdate
            ) VALUES (
                :user_id,
                :productid,
                NOW(),
                :consultation_method,
                '受付済',
                :request,
                :paymentmethod,
                :contact,
                NOW()
            )";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':productid', $goods_id, PDO::PARAM_INT);
    $stmt->bindValue(':consultation_method', $consultation_method, PDO::PARAM_STR);
    $stmt->bindValue(':request', $message, PDO::PARAM_STR);
    $stmt->bindValue(':paymentmethod', $payment_method, PDO::PARAM_STR);
    $stmt->bindValue(':contact', $contact_method, PDO::PARAM_STR);
    $stmt->execute();

    // 挿入したレコードの purchaseid を取得
    $purchase_id = $pdo->lastInsertId();

    // ★ 3. 登録したばかりのデータを取得して完了画面に表示する
    if ($purchase_id) {
        $stmt = $pdo->prepare("SELECT * FROM history WHERE purchaseid = :id");
        $stmt->bindValue(':id', $purchase_id, PDO::PARAM_INT);
        $stmt->execute();
        $apply_data = $stmt->fetch() ?: [];

        if (!empty($apply_data['productid'])) {
            $stmt_car = $pdo->prepare("SELECT * FROM goods WHERE goods_id = :goods_id");
            $stmt_car->bindValue(':goods_id', $apply_data['productid'], PDO::PARAM_INT);
            $stmt_car->execute();
            $car_data = $stmt_car->fetch() ?: [];
        }
    }

} catch (PDOException $e) {
    exit('データ登録エラー: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>お申し込み完了 - Live motors</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: sans-serif;
            color: #333;
        }
        .complete-container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 15px;
        }
        .page-title {
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 30px;
        }
        .step-bar {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 40px;
            position: relative;
        }
        .step-bar::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 25%;
            right: 25%;
            height: 2px;
            background-color: #ccc;
            z-index: 1;
            transform: translateY(-50%);
        }
        .step-item {
            position: relative;
            z-index: 2;
            background: #f8f9fa;
            padding: 0 15px;
            text-align: center;
            font-size: 0.85rem;
            color: #666;
        }
        .step-circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #ccc;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 5px;
            font-weight: bold;
            font-size: 0.9rem;
        }
        .step-item.active .step-circle {
            background-color: #198754;
        }
        .step-item.active {
            color: #198754;
            font-weight: bold;
        }
        .complete-message-box {
            text-align: center;
            margin-bottom: 40px;
        }
        .complete-icon {
            font-size: 4rem;
            color: #198754;
            margin-bottom: 10px;
            line-height: 1;
        }
        .complete-message-box h2 {
            font-size: 1.6rem;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .complete-message-box p {
            color: #555;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 5px;
        }
        .content-card {
            background: #ffffff;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }
        .card-heading {
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }
        .info-table {
            width: 100%;
            font-size: 0.95rem;
        }
        .info-table th {
            width: 35%;
            padding: 12px 10px;
            color: #555;
            font-weight: normal;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        .info-table td {
            width: 65%;
            padding: 12px 10px;
            color: #333;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        .info-table tr:last-child th,
        .info-table tr:last-child td {
            border-bottom: none;
        }
        .car-img-wrapper {
            position: relative;
            margin-bottom: 15px;
        }
        .car-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 6px;
        }
        .car-title {
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .car-sub {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 15px;
        }
        .price-row {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .price-label {
            font-size: 0.85rem;
            color: #666;
        }
        .price-value-main {
            font-size: 1.25rem;
            font-weight: bold;
            color: #dc3545;
        }
        .price-value-sub {
            font-size: 1.1rem;
            font-weight: bold;
            color: #333;
        }
        .spec-table {
            width: 100%;
            font-size: 0.85rem;
        }
        .spec-table th {
            color: #666;
            font-weight: normal;
            padding: 6px 0;
            width: 40%;
        }
        .spec-table td {
            color: #333;
            padding: 6px 0;
            width: 60%;
        }
        .notice-box {
            background-color: #f4f6f5;
            border-left: 4px solid #198754;
            padding: 20px;
            border-radius: 4px;
            margin-bottom: 30px;
            font-size: 0.9rem;
        }
        .notice-box-title {
            font-weight: bold;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .notice-box ul {
            margin: 0;
            padding-left: 20px;
            color: #555;
        }
        .notice-box li {
            margin-bottom: 5px;
        }
        .action-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-bottom: 60px;
        }
        .btn-custom-outline {
            background: #fff;
            border: 1px solid #ccc;
            color: #333;
            padding: 12px 30px;
            border-radius: 6px;
            font-weight: bold;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s;
        }
        .btn-custom-outline:hover {
            background: #f8f9fa;
            color: #333;
        }
        .btn-custom-primary {
            background: #123d26;
            border: 1px solid #123d26;
            color: #fff;
            padding: 12px 30px;
            border-radius: 6px;
            font-weight: bold;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s;
        }
        .btn-custom-primary:hover {
            background: #198754;
            color: #fff;
        }
    </style>
</head>
<body>

    <div class="complete-container">
        <h1 class="page-title">ご購入・商談の手続き</h1>

        <!-- ステップインジケーター -->
        <div class="step-bar">
            <div class="step-item">
                <div class="step-circle"><i class="bi bi-check"></i></div>
                お客様情報入力
            </div>
            <div class="step-item">
                <div class="step-circle"><i class="bi bi-check"></i></div>
                内容確認
            </div>
            <div class="step-item active">
                <div class="step-circle">3</div>
                受付完了
            </div>
        </div>

        <!-- 完了メッセージ -->
        <div class="complete-message-box">
            <div class="complete-icon">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <h2>お申し込みを受け付けました！</h2>
            <p>この度は、Live motorsをご利用いただき、誠にありがとうございます。</p>
            <p>担当スタッフが内容を確認後、改めてご連絡いたします。</p>
            <p>ご連絡をさせていただきますので、今しばらくお待ちください。<br>通常、1〜2営業日以内にご連絡いたします。</p>
            <p>メールでも受付内容の確認をお送りいたしましたので、あわせてご確認ください。</p>
        </div>

        <!-- メインコンテンツ（2カラム） -->
        <div class="row">
            <!-- 左側：受付内容 -->
            <div class="col-lg-8">
                <div class="content-card">
                    <h3 class="card-heading">受付内容</h3>
                    <table class="info-table">
                        <tr>
                            <th>受付番号</th>
                            <td>
                                <?php if (!empty($apply_data['purchaseid'])): ?>
                                    LM-<?= date('Ymd', strtotime($apply_data['applicationdate'] ?? 'now')) ?>-<?= sprintf('%04d', $apply_data['purchaseid']) ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>受付日時</th>
                            <td>
                                <?= !empty($apply_data['applicationdate']) ? htmlspecialchars(date('Y年n月j日 (D) H:i', strtotime($apply_data['applicationdate'])), ENT_QUOTES, 'UTF-8') : '-' ?>
                            </td>
                        </tr>
                        <tr>
                            <th>決定した商談日時</th>
                            <td>
                                <?= !empty($apply_data['negotiationdate']) && $apply_data['negotiationdate'] !== '0000-00-00 00:00:00' 
                                    ? htmlspecialchars(date('Y年n月j日 (D) H:i〜', strtotime($apply_data['negotiationdate'])), ENT_QUOTES, 'UTF-8') 
                                    : '調整中' ?>
                            </td>
                        </tr>
                        <tr>
                            <th>ご検討中の車両</th>
                            <td>
                                <?php if (!empty($car_data)): ?>
                                    <?= htmlspecialchars(($car_data['maker'] ?? '') . ' ' . ($car_data['car_name'] ?? '') . ' ' . ($car_data['car_grade'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                    （<?= htmlspecialchars($car_data['model_year'] ?? '', ENT_QUOTES, 'UTF-8') ?>年式 / <?= number_format($car_data['milage'] ?? 0) ?>km）
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <tr>
                        <th>ご希望の購入・商談方法</th>
                            <td>
                                <?php
                                    // DBの値が数値（0, 1, 2...）または文字列（'store_visit'など）どちらにも対応できるよう定義
                                    $consultation_labels = [
                                        0 => '来店して確認',
                                        1 => 'オンライン商談',
                                        2 => '遠方購入について相談',
                                        'store_visit' => '来店して確認',
                                        'online' => 'オンライン商談',
                                        'remote_purchase' => '遠方購入について相談'
                                        ];
                                    $c_method = $apply_data['consultation_method'] ?? '';
                                    echo htmlspecialchars($consultation_labels[$c_method] ?? $c_method, ENT_QUOTES, 'UTF-8');
                                ?>
                            </td>
                        </tr>
                    <tr>
    <th>お支払い方法の希望</th>
    <td>
        <?php
        // DBの数値IDと日本語の対応表（※番号と名称の組み合わせはDBの定義に合わせて変更してください）
        $payment_labels = [
            0 => 'オートローン',
            1 => '現金',
            2 => '銀行振込',
            3 => 'その他（相談する）',
            'cash' => '現金',
            'auto_loan' => 'オートローン',
            'bank_transfer' => '銀行振込',
            'other' => 'その他（相談する）'
        ];
        $p_method = $apply_data['paymentmethod'] ?? '';
        echo htmlspecialchars($payment_labels[$p_method] ?? $p_method, ENT_QUOTES, 'UTF-8');
        ?>
    </td>
</tr>
    <tr>
<th>ご希望の連絡方法</th>
<td>
    <?php
    // DBの数値（0, 1, 2...）と表示名称の対応表
    // ※番号の意味に合わせて文字列を変更してください
    $contact_labels = [
        0 => '電話',
        1 => 'メール',
        2 => 'どちらでもよい'
    ];

    $contact_val = $apply_data['contact'] ?? '';
    echo htmlspecialchars($contact_labels[$contact_val] ?? $contact_val, ENT_QUOTES, 'UTF-8');
    ?>
</td>
            </tr>
                        <tr>
                            <th>お問い合わせ・ご要望など</th>
                            <td><?= !empty($apply_data['request']) ? nl2br(htmlspecialchars($apply_data['request'], ENT_QUOTES, 'UTF-8')) : 'なし' ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- 右側：ご検討中の車両 -->
<div class="col-lg-4">
    <div class="content-card">
        <h3 class="card-heading">ご検討中の車両</h3>
        <div class="car-img-wrapper">
            <img src="<?= !empty($car_data['carphoto_main']) ? htmlspecialchars($car_data['carphoto_main'], ENT_QUOTES, 'UTF-8') : 'images/no-image.jpg' ?>" 
                 alt="車両画像" class="car-img" onerror="this.onerror=null; this.src='images/no-image.jpg';">
        </div>
        <div class="car-title">
            <?= !empty($car_data) ? htmlspecialchars(($car_data['maker'] ?? '') . ' ' . ($car_data['car_name'] ?? '') . ' ' . ($car_data['car_grade'] ?? ''), ENT_QUOTES, 'UTF-8') : '車両情報なし' ?>
        </div>
        <div class="car-sub">
            <?= !empty($car_data) ? htmlspecialchars(($car_data['model_year'] ?? '') . '年式 / ' . number_format($car_data['milage'] ?? 0) . 'km', ENT_QUOTES, 'UTF-8') : '' ?>
        </div>
        
        <div class="price-row">
        <!-- 車両本体価格（税抜） -->
        <div class="price-label">車両価格（税抜）</div>
        <div class="price-value-sub mb-2">
            <?= !empty($car_data['price']) ? number_format($car_data['price'] / 10000) . '万円' : '-' ?>
        </div>

        <!-- 支払総額（税込） -->
        <div class="price-label">支払総額（税込）</div>
        <div class="price-value-main">
            <?= !empty($car_data['price']) ? number_format(($car_data['price'] * 1.05) / 10000) . '万円' : '-' ?>
            </div>
        </div>

        <!-- 実際のカラム名に合わせて表示 -->
        <table class="spec-table">
            <tr>
                <th>年式</th>
                <td><?= !empty($car_data['model_year']) ? htmlspecialchars($car_data['model_year'], ENT_QUOTES, 'UTF-8') . '年' : '-' ?></td>
            </tr>
            <tr>
                <th>走行距離</th>
                <td><?= isset($car_data['milage']) ? number_format($car_data['milage']) . 'km' : '-' ?></td>
            </tr>
            <tr>
                <th>車検</th>
                <td>
                    <?= !empty($car_data['inspection_date']) ? htmlspecialchars(date('Y年n月', strtotime($car_data['inspection_date'])), ENT_QUOTES, 'UTF-8') : '-' ?>
                </td>
            </tr>
            <tr>
                <th>排気量</th>
                <td><?= !empty($car_data['engine_cc']) ? htmlspecialchars(number_format($car_data['engine_cc']), ENT_QUOTES, 'UTF-8') . 'cc' : '-' ?></td>
            </tr>
            <tr>
                <th>ミッション</th>
                <td><?= htmlspecialchars($car_data['transmission'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
                <th>駆動方式</th>
                <td><?= htmlspecialchars($car_data['drivertrain'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
                <th>燃料</th>
                <td><?= htmlspecialchars($car_data['fuel_type'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <tr>
                <th>カラー</th>
                <td><?= htmlspecialchars($car_data['color'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
        </table>
    </div>
</div>

        <!-- ご確認ください ボックス -->
        <div class="notice-box">
            <div class="notice-box-title">
                <i class="bi bi-info-circle-fill text-success"></i> ご確認ください
            </div>
            <ul>
                <li>担当スタッフよりご連絡させていただく際に、追加の書類や情報をご依頼する場合がございます。</li>
                <li>次回メールが届かない方は「@livemotors.jp」からのメールを受信できるようご設定ください。</li>
                <li>お急ぎの場合は、下記のお問い合わせ先までご連絡ください。</li>
            </ul>
        </div>

        <!-- アクションボタン -->
        <div class="action-buttons">
            <a href="main.php" class="btn-custom-outline">
                <i class="bi bi-house-door"></i> トップページに戻る
            </a>
            <a href="mypage.php" class="btn-custom-primary">
                <i class="bi bi-person"></i> マイページで申し込み内容を確認する
            </a>
        </div>
    </div>

    <!-- 共通フッターの呼び出し -->
    <?php require 'footer.php'; ?>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>