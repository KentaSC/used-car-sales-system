<?php
require_once 'session.php';
ini_set('display_errors', '1');
error_reporting(E_ALL);
require_once 'db_connect.php';
require_once 'price_function.php';
/*
 * URL例：
 * estimate_request.php?goods_id=12
 * URLから受け取った車両IDを整数として取得する。
 */
$goods_id = filter_input(INPUT_GET, 'goods_id', FILTER_VALIDATE_INT);
if (!$goods_id) {
    exit('対象の車両情報が見つかりません。');
}
/*
 * 現在はチーム内でgoodsテーブルのカラム構成が異なるため、
 * SELECT * で取得している。
 * DB構成が統一されたら、必要なカラムだけをSELECTする形に変更する。
 */
$sql = $pdo->prepare(
    'SELECT *
     FROM goods
     WHERE goods_id = ?'
);
$sql->execute([$goods_id]);
$goods = $sql->fetch();
if (!$goods) {
    exit('対象の車両が見つかりません。');
}
/* 支払総額は price_functions.php の共通関数で計算する */
$total_price = calculateTotalPrice((float)$goods['price']);
/* 車検日の表示用 */
$inspection_text = !empty($goods['inspection_date'])
    ? date('Y年n月', strtotime($goods['inspection_date']))
    : '—';
/* 都道府県の選択肢 */
$prefectures = [
    '北海道',
    '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県',
    '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県',
    '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県',
    '岐阜県', '静岡県', '愛知県', '三重県',
    '滋賀県', '京都府', '大阪府', '兵庫県', '奈良県', '和歌山県',
    '鳥取県', '島根県', '岡山県', '広島県', '山口県',
    '徳島県', '香川県', '愛媛県', '高知県',
    '福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'
];
/* DBの値が1の装備だけを表示する */
$equipment_map = [
    'car_navi' => ['fa-solid fa-map', 'ナビ'],
    'backcamera' => ['fa-solid fa-camera', 'バックカメラ'],
    'etc' => ['fa-solid fa-credit-card', 'ETC'],
    'dashcam' => ['fa-solid fa-video', 'ドライブレコーダー'],
    'smartkey' => ['fa-solid fa-key', 'スマートキー'],
    'cruise_control' => ['fa-solid fa-gauge-high', 'クルーズコントロール'],
    'omnidirectional_camera' => ['fa-solid fa-camera-rotate', '全方位カメラ'],
    'led_headlights' => ['fa-solid fa-lightbulb', 'LEDヘッドライト'],
    'siding_door' => ['fa-solid fa-car-side', 'スライドドア'],
    'no_smoking' => ['fa-solid fa-smoking-ban', '禁煙車'],
];
/* HTML特殊文字を安全に表示するための関数 */
function h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>見積・商談受付 | Live motors</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
        }
        body.estimate-page {
            margin: 0;
            color: #18352a;
            background: #f7f9f8;
            font-family: -apple-system, BlinkMacSystemFont, "Hiragino Kaku Gothic ProN",
                "Hiragino Sans", "Yu Gothic", Meiryo, sans-serif;
        }
        .purchase-step {
            max-width: 1100px;
            margin: 0 auto;
            padding: 32px 20px 22px;
            text-align: center;
            background: #fff;
        }
        .purchase-step h2 {
            margin: 0 0 20px;
            font-size: 20px;
        }
        .step-list {
            display: flex;
            align-items: flex-start;
            justify-content: center;
            max-width: 720px;
            margin: 0 auto;
        }
        .step-item {
            width: 130px;
            font-size: 12px;
        }
        .step-item p {
            margin: 7px 0 0;
        }
        .step-circle {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 25px;
            height: 25px;
            margin: 0 auto;
            border-radius: 50%;
            color: #fff;
            background: #c7ccca;
            font-weight: 700;
        }
        .step-circle.active {
            background: #00543d;
        }
        .step-line {
            width: 130px;
            height: 1px;
            margin-top: 12px;
            background: #c7ccca;
        }
        .purchase-guide {
            margin: 20px 0 0;
            font-size: 13px;
        }
        .estimate-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 300px;
            gap: 24px;
            max-width: 1100px;
            margin: 0 auto;
            padding: 24px 20px 50px;
        }
        .form-box,
        .car-summary-box {
            margin-bottom: 18px;
            padding: 20px;
            border: 1px solid #dce5e1;
            border-radius: 6px;
            background: #fff;
        }
        .form-box h3,
        .car-summary-box h3,
        .car-summary-box h4 {
            margin: 0 0 16px;
            color: #00543d;
            font-size: 17px;
        }
        .required {
            display: inline-block;
            margin-left: 5px;
            padding: 2px 5px;
            border-radius: 3px;
            color: #fff;
            background: #00543d;
            font-size: 10px;
            vertical-align: middle;
            white-space: nowrap;
        }
        .form-row {
            display: grid;
            grid-template-columns: 155px minmax(0, 1fr);
            border: 1px solid #e1e7e4;
            border-bottom: 0;
        }
        .form-row:last-child {
            border-bottom: 1px solid #e1e7e4;
        }
        .form-row > label {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 14px 12px;
            background: #f7f9f8;
            font-size: 13px;
            font-weight: 700;
            white-space: nowrap;
        }
        .form-row > input,
        .name-inputs,
        .address-inputs {
            min-width: 0;
            padding: 10px;
        }
        input,
        select,
        textarea {
            width: 100%;
            border: 1px solid #d6dfdb;
            border-radius: 4px;
            padding: 10px 11px;
            color: #18352a;
            background: #fff;
            font: inherit;
            font-size: 13px;
        }
        input:focus,
        select:focus,
        textarea:focus {
            outline: 2px solid #8ec8b4;
            border-color: #00543d;
        }
        .name-inputs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        .address-inputs {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 8px;
        }
        .address-inputs input:last-child {
            grid-column: 1 / -1;
        }
        .choice-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }
        .choice-card {
            display: flex;
            flex-direction: column;
            min-height: 175px;
            padding: 16px;
            border: 1px solid #dce5e1;
            border-radius: 5px;
            cursor: pointer;
        }
        .choice-card:has(input:checked) {
            border: 2px solid #00543d;
            background: #f6fcf9;
        }
        .choice-card input {
            width: auto;
            margin: 0 0 12px;
        }
        .choice-card > i {
            margin-bottom: 10px;
            color: #00543d;
            font-size: 28px;
            text-align: center;
        }
        .choice-card strong {
            margin-bottom: 8px;
            text-align: center;
            font-size: 14px;
        }
        .choice-card span {
            color: #52645d;
            font-size: 11px;
            line-height: 1.6;
        }
        .radio-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 14px;
            border: 1px solid #e1e7e4;
            border-radius: 5px;
        }
        .radio-list label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            cursor: pointer;
        }
        .radio-list input {
            width: auto;
            margin: 0;
        }
        .form-note,
        .character-count {
            margin: 8px 0 0;
            color: #697870;
            font-size: 11px;
        }
        .character-count {
            text-align: right;
        }
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        .form-actions {
            text-align: center;
        }
        .confirm-button {
            width: 100%;
            padding: 16px;
            border: 0;
            border-radius: 5px;
            color: #fff;
            background: #00543d;
            cursor: pointer;
            font-size: 15px;
            font-weight: 700;
        }
        .confirm-button:hover {
            background: #003f2e;
        }
        .confirm-button i {
            margin-left: 12px;
        }
        .back-link {
            display: inline-block;
            margin-top: 18px;
            color: #00543d;
            font-size: 13px;
            text-decoration: none;
        }
        .back-link i {
            margin-right: 6px;
        }
        .car-summary {
            align-self: start;
        }
        .car-summary-image {
            display: block;
            width: 100%;
            aspect-ratio: 16 / 10;
            margin-bottom: 14px;
            border-radius: 4px;
            object-fit: cover;
            background: #edf1ef;
        }
        .car-name {
            margin: 0;
            font-size: 17px;
        }
        .car-sub-info {
            margin: 5px 0 18px;
            color: #607068;
            font-size: 12px;
        }
        .price-label {
            margin: 0 0 4px;
            font-size: 12px;
            font-weight: 700;
        }
        .car-price {
            margin: 0 0 14px;
            color: #e3312d;
            font-size: 26px;
            font-weight: 700;
        }
        .car-price span,
        .total-price span {
            margin-left: 3px;
            font-size: 13px;
        }
        .total-price {
            margin: 0 0 18px;
            font-size: 23px;
            font-weight: 700;
        }
        .car-spec-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        .car-spec-table th,
        .car-spec-table td {
            padding: 8px;
            border: 1px solid #e1e7e4;
            text-align: left;
        }
        .car-spec-table th {
            width: 45%;
            background: #f7f9f8;
            font-weight: 700;
        }
        .equipment-list {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px 10px;
        }
        .equipment-list span {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 13px;
            white-space: nowrap;
        }
        .equipment-list i {
            width: 16px;
            color: #00543d;
            text-align: center;
        }
        .no-equipment {
            margin: 0;
            color: #697870;
            font-size: 12px;
        }
        .contact-box {
            display: flex;
            gap: 10px;
            padding: 18px 14px;
            border: 1px solid #b8d2c8;
            border-radius: 5px;
            background: #f8fcfa;
            color: #173b2e;
        }
        .contact-icon {
            margin-top: 3px;
            color: #00543d;
            font-size: 22px;
        }
        .contact-content {
            flex: 1;
            text-align: center;
        }
        .contact-content strong {
            display: block;
            font-size: 12px;
        }
        .contact-content p {
            margin: 4px 0 10px;
            font-size: 10px;
        }
        .contact-content a {
            display: block;
            color: #00543d;
            font-size: 17px;
            font-weight: 700;
            text-decoration: none;
        }
        .contact-content a i {
            margin-right: 4px;
        }
        .contact-content small {
            display: block;
            margin-top: 4px;
            font-size: 9px;
        }
        @media (max-width: 800px) {
            .estimate-layout {
                grid-template-columns: 1fr;
            }
            .car-summary {
                grid-row: 1;
            }
        }
        @media (max-width: 600px) {
            .purchase-step {
                padding-right: 12px;
                padding-left: 12px;
            }
            .step-item {
                width: 90px;
                font-size: 10px;
            }
            .step-line {
                width: 45px;
            }
            .estimate-layout {
                padding: 16px 12px 35px;
            }
            .form-box,
            .car-summary-box {
                padding: 15px;
            }
            .form-row {
                grid-template-columns: 1fr;
                border-bottom: 1px solid #e1e7e4;
            }
            .form-row > label {
                padding: 10px 12px;
            }
            .name-inputs,
            .address-inputs {
                grid-template-columns: 1fr;
            }
            .address-inputs input:last-child {
                grid-column: auto;
            }
            .choice-cards {
                grid-template-columns: 1fr;
            }
            .choice-card {
                min-height: auto;
            }
        }
    </style>
</head>
<body class="estimate-page">
    <?php require 'header.php'; ?>
    <section class="purchase-step">
        <h2>ご購入・商談の手続き</h2>
        <div class="step-list">
            <div class="step-item">
                <div class="step-circle active">1</div>
                <p>お客様情報入力</p>
            </div>
            <div class="step-line"></div>
            <div class="step-item">
                <div class="step-circle">2</div>
                <p>内容確認</p>
            </div>
            <div class="step-line"></div>
            <div class="step-item">
                <div class="step-circle">3</div>
                <p>受付完了</p>
            </div>
        </div>
        <p class="purchase-guide">
            購入または商談をご希望の方は、以下のフォームにご入力ください。
        </p>
    </section>
    <main class="estimate-layout">
        <form action="purchase_confirm.php" method="post">
            <!-- 確認画面へ対象車両のIDを引き継ぐ -->
            <input type="hidden" name="goods_id" value="<?= h($goods_id) ?>">
            <section class="form-box">
                <h3>お客様情報</h3>
                <div class="form-row">
                    <label>
                        お名前 <span class="required">必須</span>
                    </label>
                    <div class="name-inputs">
                        <input type="text" name="last_name" placeholder="姓" autocomplete="family-name" maxlength="50" required>
                        <input type="text" name="first_name" placeholder="名" autocomplete="given-name" maxlength="50" required>
                    </div>
                </div>
                <div class="form-row">
                    <label>
                        フリガナ <span class="required">必須</span>
                    </label>
                    <div class="name-inputs">
                        <input type="text" name="last_name_kana" placeholder="セイ" maxlength="50" required>
                        <input type="text" name="first_name_kana" placeholder="メイ" maxlength="50" required>
                    </div>
                </div>
                <div class="form-row">
                    <label for="phone">
                        電話番号 <span class="required">必須</span>
                    </label>
                    <input type="tel" id="phone" name="phone" placeholder="例）090-1234-5678" autocomplete="tel" inputmode="tel" maxlength="13" pattern="[0-9-]{10,13}" required>
                </div>
                <div class="form-row">
                    <label for="mail">
                        メールアドレス <span class="required">必須</span>
                    </label>
                    <input type="email" id="mail" name="mail" placeholder="例）example@email.com" autocomplete="email" maxlength="255" required>
                </div>
                <div class="form-row">
                    <label>
                        ご住所 <span class="required">必須</span>
                    </label>
                    <div class="address-inputs">
                        <input type="text" name="postal_code" placeholder="例）880-0001" autocomplete="postal-code" inputmode="tel" maxlength="8" pattern="[0-9]{3}-?[0-9]{4}" required>
                        <select name="prefecture" required>
                            <option value="" selected disabled>都道府県を選択</option>
                            <?php foreach ($prefectures as $prefecture): ?>
                                <option value="<?= h($prefecture) ?>">
                                    <?= h($prefecture) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="address_detail" placeholder="市区町村・番地・建物名など" autocomplete="street-address" maxlength="255" required>
                    </div>
                </div>
            </section>
            <section class="form-box">
                <h3>
                    ご希望の購入・商談方法
                    <span class="required">必須</span>
                </h3>
                <div class="choice-cards">
                    <label class="choice-card">
                        <input type="radio" name="consultation_method" value="store_visit" required>
                        <i class="fa-solid fa-store" aria-hidden="true"></i>
                        <strong>来店して確認</strong>
                        <span>
                            店舗にご来店いただき、実車を確認しながら商談します。
                        </span>
                    </label>
                    <label class="choice-card">
                        <input type="radio" name="consultation_method" value="online">
                        <i class="fa-solid fa-desktop" aria-hidden="true"></i>
                        <strong>オンライン商談</strong>
                        <span>
                            ビデオ通話などで車両の状態を確認し、オンラインで商談します。
                        </span>
                    </label>
                    <label class="choice-card">
                        <input type="radio" name="consultation_method" value="remote_purchase">
                        <i class="fa-solid fa-truck" aria-hidden="true"></i>
                        <strong>遠方購入について相談</strong>
                        <span>
                            陸送や登録など、遠方からの購入方法についてご相談いただけます。
                        </span>
                    </label>
                </div>
            </section>
            <section class="form-box">
                <h3>
                    お支払い方法の希望
                    <span class="required">必須</span>
                </h3>
                <div class="radio-list">
                    <label>
                        <input type="radio" name="payment_method" value="cash" required>
                        現金
                    </label>
                    <label>
                        <input type="radio" name="payment_method" value="auto_loan">
                        オートローン
                    </label>
                    <label>
                        <input type="radio" name="payment_method" value="bank_transfer">
                        銀行振込
                    </label>
                    <label>
                        <input type="radio" name="payment_method" value="other">
                        その他（相談する）
                    </label>
                </div>
                <p class="form-note">
                    ※ローン審査をご希望の方は、後日スタッフよりご案内いたします。
                </p>
            </section>
            <section class="form-box">
                <h3>
                    ご希望の連絡方法
                    <span class="required">必須</span>
                </h3>
                <div class="radio-list">
                    <label>
                        <input type="radio" name="contact_method" value="phone" required>
                        電話
                    </label>
                    <label>
                        <input type="radio" name="contact_method" value="mail">
                        メール
                    </label>
                    <label>
                        <input type="radio" name="contact_method" value="either">
                        どちらでもよい
                    </label>
                </div>
            </section>
            <section class="form-box">
                <h3>お問い合わせ・ご要望など</h3>
                <textarea
                    id="message"
                    name="message"
                    maxlength="500"
                    placeholder="ご質問やご要望がございましたらご記入ください（任意）"
                ></textarea>
                <p class="character-count">
                    <span id="message-count">0</span> / 500
                </p>
            </section>
            <div class="form-actions">
                <button type="submit" class="confirm-button">
                    内容確認へ進む
                    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                </button>
                <a href="javascript:history.back()" class="back-link">
                    <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                    前のページに戻る
                </a>
            </div>
        </form>
        <aside class="car-summary">
            <section class="car-summary-box">
                <h3>ご検討中の車両</h3>
                <img
                    src="<?= h($goods['carphoto_main'] ?? 'img/no-image.jpg') ?>"
                    alt="<?= h($goods['car_name'] ?? '車両画像') ?>"
                    class="car-summary-image"
                    onerror="this.onerror=null; this.src='img/no-image.jpg';"
                >
                <h4 class="car-name"><?= h($goods['car_name'] ?? '車名未登録') ?></h4>
                <p class="car-sub-info">
                    <?= h($goods['model_year'] ?? '—') ?>年式 /
                    <?= !empty($goods['milage']) ? number_format((int)$goods['milage']) : '—' ?>km
                </p>
                <p class="price-label">車両価格</p>
                <p class="car-price">
                    <?= number_format((float)$goods['price']) ?>
                    <span>円</span>
                </p>
                <p class="price-label">支払総額（税込）</p>

                <p class="total-price">
                    <?= number_format($total_price) ?>
                    <span>円</span>
                </p>
                <table class="car-spec-table">
                    <tr>
                        <th>年式</th>
                        <td><?= h($goods['model_year'] ?? '—') ?>年</td>
                    </tr>
                    <tr>
                        <th>走行距離</th>
                        <td>
                            <?= !empty($goods['milage'])
                                ? number_format((int)$goods['milage']) . 'km'
                                : '—'
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>車検</th>
                        <td><?= h($inspection_text) ?></td>
                    </tr>
                    <tr>
                        <th>排気量</th>
                        <td>
                            <?= !empty($goods['engine_cc'])
                                ? number_format((int)$goods['engine_cc']) . 'cc'
                                : '—'
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>ミッション</th>
                        <td><?= h($goods['transmission'] ?? '—') ?></td>
                    </tr>
                    <tr>
                        <th>駆動方式</th>
                        <td><?= h($goods['drivertrain'] ?? '—') ?></td>
                    </tr>
                    <tr>
                        <th>燃料</th>
                        <td><?= h($goods['fuel_type'] ?? '—') ?></td>
                    </tr>
                    <tr>
                        <th>カラー</th>
                        <td><?= h($goods['color'] ?? '—') ?></td>
                    </tr>
                </table>
            </section>
            <section class="car-summary-box equipment-box">
                <h4>装備・特徴</h4>
                <div class="equipment-list">
                    <?php
                    $has_equipment = false;
                    ?>
                    <?php foreach ($equipment_map as $field => [$icon, $label]): ?>
                        <?php if (!empty($goods[$field])): ?>
                            <?php $has_equipment = true; ?>
                            <span>
                                <i class="<?= h($icon) ?>" aria-hidden="true"></i>
                                <?= h($label) ?>
                            </span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php if (!$has_equipment): ?>
                    <p class="no-equipment">装備情報は登録されていません。</p>
                <?php endif; ?>
            </section>
            <section class="contact-box">
                <i class="fa-solid fa-headset contact-icon" aria-hidden="true"></i>
                <div class="contact-content">
                    <strong>ご不明な点はお気軽にご相談ください</strong>
                    <p>専門スタッフが丁寧に対応いたします。</p>
                    <a href="tel:0120123456">
                        <i class="fa-solid fa-phone" aria-hidden="true"></i>
                        0120-123-456
                    </a>
                    <small>受付時間：10:00〜18:00（火曜定休）</small>
                </div>
            </section>
        </aside>
    </main>
    <script>
        const message = document.getElementById('message');
        const messageCount = document.getElementById('message-count');
        message.addEventListener('input', function () {
            messageCount.textContent = message.value.length;
        });
    </script>
    <?php require 'footer.php'; ?>
</body>
</html>