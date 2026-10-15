<?php
require_once 'db_connect.php';
// --- データベース接続とデータ取得の例 ---
// 実際の環境に合わせて接続情報（ユーザー名、パスワード、DB名など）を変更してください。
try {
    // 本来はURLのクエリパラメータ（例: ?goods_id=1）などから取得します
    // ここではテストとして goods_id = 1 を指定しています
    $goods_id = isset($_GET['goods_id']) ? (int)$_GET['goods_id'] : 1;

    $stmt = $pdo->prepare('SELECT * FROM goods WHERE goods_id = :goods_id');
    $stmt->bindValue(':goods_id', $goods_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        // 車両データが見つからない場合のデフォルト処理
        echo "指定された車両が見つかりません。";
        exit;
    }
} catch (PDOException $e) {
    echo "接続失敗: " . $e->getMessage();
    exit;
}

// フォーム送信時の処理
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 送信時の処理
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ご購入・商談の手続き</title>
    <link rel="stylesheet" href="css/quote.css">
</head>
<body>

<main class="estimate-page">

    <!-- ヘッダー・ステップエリア -->
    <header class="estimate-header">
        <h1>ご購入・商談の手続き</h1>
        
        <div class="estimate-steps">
            <div class="estimate-step active">
                <span class="step-number">1</span>
                1. お客様情報入力
            </div>
            <div class="step-line"></div>
            <div class="estimate-step">
                <span class="step-number">2</span>
                2. 内容確認
            </div>
            <div class="step-line"></div>
            <div class="estimate-step">
                <span class="step-number">3</span>
                3. 受付完了
            </div>
        </div>

        <p class="estimate-description">購入または商談をご希望の方は、以下のフォームにご入力ください。</p>
    </header>

    <!-- エラーメッセージ（検証用） -->
    <?php if (!empty($errors)): ?>
    <div class="estimate-error">
        <strong>入力内容に誤りがあります。</strong>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- メインレイアウト -->
    <form action="" method="POST" class="estimate-layout">
        
        <!-- 左側フォームエリア -->
        <div class="form-side">
            
            <!-- お客様情報セクション -->
            <section class="estimate-section">
                <h2>お客様情報</h2>
                
                <div class="customer-table">
                    <!-- お名前 -->
                    <div class="customer-label">お名前<span class="required">必須</span></div>
                    <div class="customer-inputs">
                        <input type="text" name="sei" placeholder="姓" required>
                        <input type="text" name="mei" placeholder="名" required>
                    </div>

                    <!-- フリガナ -->
                    <div class="customer-label">フリガナ<span class="required">必須</span></div>
                    <div class="customer-inputs">
                        <input type="text" name="sei_kana" placeholder="セイ" required>
                        <input type="text" name="mei_kana" placeholder="メイ" required>
                    </div>

                    <!-- 電話番号 -->
                    <div class="customer-label">電話番号<span class="required">必須</span></div>
                    <div class="customer-input-full">
                        <input type="tel" name="tel" placeholder="例) 090-1234-5678" required>
                    </div>

                    <!-- メールアドレス -->
                    <div class="customer-label">メールアドレス<span class="required">必須</span></div>
                    <div class="customer-input-full">
                        <input type="email" name="email" placeholder="例) example@email.com" required>
                    </div>

                    <!-- ご住所 -->
                    <div class="customer-label">ご住所<span class="required">必須</span></div>
                    <div class="address-area">
                        <div class="address-top">

                            <!-- 郵便番号 -->
                            <input
                                type="text"
                                name="zipcode"
                                id="zipcode"
                                placeholder="郵便番号（例：880-0001）"
                                maxlength="8"
                                required
                            >

                            <!-- 住所検索ボタン -->
                            <button
                                type="button"
                                class="address-search"
                                id="address-search"
                            >
                                住所検索
                            </button>

                            <!-- 都道府県 -->
                            <select name="pref" id="pref" required>
                                <option value="">都道府県</option>
                                <option value="北海道">北海道</option>
                                <option value="青森県">青森県</option>
                                <option value="岩手県">岩手県</option>
                                <option value="宮城県">宮城県</option>
                                <option value="秋田県">秋田県</option>
                                <option value="山形県">山形県</option>
                                <option value="福島県">福島県</option>
                                <option value="茨城県">茨城県</option>
                                <option value="栃木県">栃木県</option>
                                <option value="群馬県">群馬県</option>
                                <option value="埼玉県">埼玉県</option>
                                <option value="千葉県">千葉県</option>
                                <option value="東京都">東京都</option>
                                <option value="神奈川県">神奈川県</option>
                                <option value="新潟県">新潟県</option>
                                <option value="富山県">富山県</option>
                                <option value="石川県">石川県</option>
                                <option value="福井県">福井県</option>
                                <option value="山梨県">山梨県</option>
                                <option value="長野県">長野県</option>
                                <option value="岐阜県">岐阜県</option>
                                <option value="静岡県">静岡県</option>
                                <option value="愛知県">愛知県</option>
                                <option value="三重県">三重県</option>
                                <option value="滋賀県">滋賀県</option>
                                <option value="京都府">京都府</option>
                                <option value="大阪府">大阪府</option>
                                <option value="兵庫県">兵庫県</option>
                                <option value="奈良県">奈良県</option>
                                <option value="和歌山県">和歌山県</option>
                                <option value="鳥取県">鳥取県</option>
                                <option value="島根県">島根県</option>
                                <option value="岡山県">岡山県</option>
                                <option value="広島県">広島県</option>
                                <option value="山口県">山口県</option>
                                <option value="徳島県">徳島県</option>
                                <option value="香川県">香川県</option>
                                <option value="愛媛県">愛媛県</option>
                                <option value="高知県">高知県</option>
                                <option value="福岡県">福岡県</option>
                                <option value="佐賀県">佐賀県</option>
                                <option value="長崎県">長崎県</option> 
                                <option value="熊本県">熊本県</option>
                                <option value="大分県">大分県</option>
                                <option value="宮崎県">宮崎県</option>
                                <option value="鹿児島県">鹿児島県</option>
                                <option value="沖縄県">沖縄県</option>
                                
                            </select>
                        </div>
                        <input type="text" name="address" id="address" placeholder="市区町村・番地・建物名など" required>
                    </div>                       
                </div>
            </section>

            <!-- ご希望の購入・商談方法 -->
            <section class="estimate-section">
                <h2>ご希望の購入・商談方法<span class="required">必須</span></h2>
                <div class="choice-grid">
                    <label class="choice-card">
                        <input type="radio" name="method" value="store" checked>
                        <div class="choice-icon">🏬</div>
                        <strong>来店して確認</strong>
                        <p>店舗にご来店いただき、実車を確認しながら商談します。</p>
                    </label>
                    <label class="choice-card">
                        <input type="radio" name="method" value="online">
                        <div class="choice-icon">💻</div>
                        <strong>オンライン商談</strong>
                        <p>ビデオ通話などで車両の状態を確認し、オンラインで商談します。</p>
                    </label>
                    <label class="choice-card">
                        <input type="radio" name="method" value="remote">
                        <div class="choice-icon">🏠</div>
                        <strong>遠方購入について相談</strong>
                        <p>陸送や登録など、遠方購入に関するご相談を承ります。</p>
                    </label>
                </div>
            </section>

            <!-- お支払い方法の希望 -->
            <section class="estimate-section">
                <h2>お支払い方法の希望<span class="required">必須</span></h2>
                <div class="radio-box">
                    <label><input type="radio" name="payment" value="cash" checked>現金</label>
                    <label><input type="radio" name="payment" value="loan">オートローン</label>
                    <label><input type="radio" name="payment" value="bank">銀行振込</label>
                    <label><input type="radio" name="payment" value="other">その他（相談する）</label>
                </div>
                <p class="note">※ローン審査をご希望の方は、後日スタッフよりご案内いたします。</p>
            </section>

            <!-- ご希望の連絡方法 -->
            <section class="estimate-section">
                <h2>ご希望の連絡方法<span class="required">必須</span></h2>
                <div class="radio-box">
                    <label><input type="radio" name="contact" value="tel" checked>電話</label>
                    <label><input type="radio" name="contact" value="email">メール</label>
                    <label><input type="radio" name="contact" value="either">どちらでもよい</label>
                </div>
            </section>

            <!-- お問い合わせ・ご要望など -->
            <section class="estimate-section">
                <h2>お問い合わせ・ご要望など</h2>
                <textarea name="requests" placeholder="ご質問やご要望がございましたらご記入ください（任意）" maxlength="500"></textarea>
                <div class="character-count">0 / 500</div>
            </section>

            <!-- ボタンエリア -->
            <button type="submit" class="estimate-submit">内容確認へ進む <span>&gt;</span></button>
            <a href="#" class="estimate-back">&lt; 前のページに戻る</a>

        </div>

        <!-- 右側：サイドバー（車両情報） -->
        <div class="sidebar-side">
            <div class="car-info-card">
                <h2>ご検討中の車両</h2>

                <!-- 車両画像 -->
                <div class="car-img-box">
                    <img
                        src="<?= htmlspecialchars($row['carphoto_main'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        alt="<?= htmlspecialchars($row['car_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        class="car-photo"
                        onerror="this.onerror=null; this.src='images/no-image.jpg';"
                    >
                </div>
                
                <!-- 車名・グレード -->
                <div class="car-title-row">
                    <div class="car-name">
                        <span class="maker-label"><?= htmlspecialchars($row['maker'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                        <strong><?= htmlspecialchars($row['car_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                        <span class="grade-label"><?= htmlspecialchars($row['car_grade'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </div>

                <!-- 価格表示エリア -->
                <div class="price-container">
                    <div class="price-row">
                        <span class="price-label">車両価格</span>
                        <span class="price-value"><strong><?= number_format($row['price']) ?></strong>円</span>
                    </div>
                    <div class="price-row total">
                        <span class="price-label">支払総額（税込）</span>
                        <!-- 支払総額の算出ロジックがないため、仮で車両価格+7万円、または実体に合わせて調整してください -->
                        <span class="price-value"><strong><?= number_format($row['price'] + 70000) ?></strong>円</span>
                    </div>
                </div>
                
                <!-- 車両詳細スペック表 -->
                <table class="specs-table">
                    <tr>
                        <th>年式</th>
                        <td><?= htmlspecialchars($row['model_year'] ?? '', ENT_QUOTES, 'UTF-8') ?>年</td>
                    </tr>
                    <tr>
                        <th>走行距離</th>

                    <?= number_format($row['milage'] ?? 0) ?>km
                    車検    

                    <?= htmlspecialchars($row['inspection_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>

                    <script src="js/address.js"></script>