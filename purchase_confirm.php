<?php
require_once 'session.php';
/* request_estimate.php からPOST送信された場合だけ表示する */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('見積受付画面から操作してください。');
}
/* 商談日時は運営側で一律に設定する */
$meeting_date = '2026年10月1日（木）';
$meeting_time = '14:00';
$request_last_name = trim($_POST['last_name'] ?? '');
$request_first_name = trim($_POST['first_name'] ?? '');
$request_last_name_kana = trim($_POST['last_name_kana'] ?? '');
$request_first_name_kana = trim($_POST['first_name_kana'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$mail = trim($_POST['mail'] ?? '');
$postal_code = trim($_POST['postal_code'] ?? '');
$prefecture = trim($_POST['prefecture'] ?? '');
$address_detail = trim($_POST['address_detail'] ?? '');
/* 商談日は、入力画面に項目を追加した後に受け取る */
$desired_date = trim($_POST['desired_date'] ?? '');
$desired_time = trim($_POST['desired_time'] ?? '');
if (
    $request_last_name === '' ||
    $request_first_name === '' ||
    $request_last_name_kana === '' ||
    $request_first_name_kana === '' ||
    $phone === '' ||
    $mail === '' ||
    $postal_code === '' ||
    $prefecture === '' ||
    $address_detail === ''
) {
    exit('入力内容が不足しています。見積受付画面から入力し直してください。');
}
/* HTMLへ安全に表示するための関数 */
function h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
/* 購入・商談方法をPOSTで受け取る */
$consultation_method = $_POST['consultation_method'] ?? '';
/* valueを画面表示用の日本語・アイコン・説明へ変換する */
$consultation_options = [
    'store_visit' => [
        'label' => '来店して確認',
        'icon' => 'fa-solid fa-store',
        'description' => '店舗にご来店いただき、実車を確認しながら商談します。',
    ],
    'online' => [
        'label' => 'オンライン商談',
        'icon' => 'fa-solid fa-desktop',
        'description' => 'ビデオ通話などで車両の状態を確認し、オンラインで商談します。',
    ],
    'remote_purchase' => [
        'label' => '遠方購入について相談',
        'icon' => 'fa-solid fa-truck',
        'description' => '陸送や登録など、遠方からの購入方法についてご相談いただけます。',
    ],
];
$selected_consultation = $consultation_options[$consultation_method] ?? null;
if (!$selected_consultation) {
    exit('購入・商談方法が正しく選択されていません。');
}
/* 支払い方法をPOSTで受け取る */
$payment_method = $_POST['payment_method'] ?? '';
/* valueを画面表示用の日本語へ変換する */
$payment_labels = [
    'cash' => '現金',
    'auto_loan' => 'オートローン',
    'bank_transfer' => '銀行振込',
    'other' => 'その他（相談する）',
];
$selected_payment = $payment_labels[$payment_method] ?? null;
if (!$selected_payment) {
    exit('お支払い方法が正しく選択されていません。');
}
/* 連絡方法をPOSTで受け取る */
$contact_method = $_POST['contact_method'] ?? '';
/* valueを画面表示用の日本語へ変換する */
$contact_labels = [
    'phone' => '電話',
    'mail' => 'メール',
    'either' => 'どちらでもよい',
];
$selected_contact = $contact_labels[$contact_method] ?? null;
if (!$selected_contact) {
    exit('連絡方法が正しく選択されていません。');
}
/* お問い合わせ内容をPOSTで受け取る */
$message = trim($_POST['message'] ?? '');
/* 500文字を超える場合は送信を止める */
if (mb_strlen($message) > 500) {
    exit('お問い合わせ・ご要望は500文字以内で入力してください。');
}
$goods_id = filter_input(INPUT_POST, 'goods_id', FILTER_VALIDATE_INT);
if (!$goods_id) {
    exit('対象の車両情報が見つかりません。');
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>購入・商談内容確認 | Live motors</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
    * {
        box-sizing: border-box;
    }
    body {
        margin: 0;
        color: #18352a;
        background: #f7f9f8;
        font-family: -apple-system, BlinkMacSystemFont,
            "Hiragino Kaku Gothic ProN", "Hiragino Sans",
            "Yu Gothic", Meiryo, sans-serif;
    }
    .purchase-step {
        padding: 32px 20px 26px;
        background: #ffffff;
        text-align: center;
    }
    .purchase-step h2 {
        margin: 0 0 20px;
        font-size: 20px;
    }
    .purchase-step h3 {
        margin: 24px 0 8px;
        font-size: 18px;
    }
    .purchase-step > p {
        margin: 0;
        font-size: 13px;
        line-height: 1.7;
    }
    .step-list {
        display: flex;
        justify-content: center;
        align-items: flex-start;
        max-width: 680px;
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
        color: #ffffff;
        background: #c5cbc8;
        font-size: 12px;
        font-weight: 700;
    }
    .step-circle.completed,
    .step-circle.active {
        background: #00543d;
    }
    .step-line {
        width: 115px;
        height: 1px;
        margin-top: 12px;
        background: #c5cbc8;
    }
    .confirm-layout {
        max-width: 800px;
        margin: 0 auto;
        padding: 28px 20px 50px;
    }
    .confirm-main {
        width: 100%;
    }
    .confirm-box {
        margin-bottom: 18px;
        padding: 20px;
        border: 1px solid #dce5e1;
        border-radius: 6px;
        background: #ffffff;
    }
    .confirm-box h3 {
        margin: 0 0 15px;
        color: #00543d;
        font-size: 17px;
    }
    .confirm-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .confirm-table th,
    .confirm-table td {
        padding: 12px 14px;
        border: 1px solid #e1e7e4;
        text-align: left;
        vertical-align: middle;
    }
    .confirm-table th {
        width: 30%;
        background: #f7f9f8;
        font-weight: 700;
        white-space: nowrap;
    }
    .confirm-table td {
        line-height: 1.7;
    }
    .business-hours {
        margin: 5px 0 0;
        color: #697870;
        font-size: 11px;
    }
    .method-confirm-card {
        display: flex;
        align-items: center;
        gap: 18px;
        padding: 18px;
        border: 1px solid #dce5e1;
        border-radius: 5px;
        background: #f9fcfa;
    }
    .method-confirm-card > i {
        width: 40px;
        color: #00543d;
        font-size: 31px;
        text-align: center;
    }
    .method-confirm-card strong {
        display: block;
        margin-bottom: 5px;
        color: #00543d;
        font-size: 15px;
    }
    .method-confirm-card p {
        margin: 0;
        color: #52645d;
        font-size: 12px;
        line-height: 1.7;
    }
    .form-note {
        margin: 10px 0 0;
        color: #697870;
        font-size: 11px;
        line-height: 1.6;
    }
    .confirm-actions {
        margin-top: 24px;
    }
    .confirm-button {
        width: 100%;
        padding: 16px;
        border: 0;
        border-radius: 5px;
        color: #ffffff;
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
        display: block;
        width: fit-content;
        margin: 18px auto 0;
        color: #00543d;
        font-size: 13px;
        text-decoration: none;
    }
    .back-link:hover {
        text-decoration: underline;
    }
    .back-link i {
        margin-right: 6px;
    }
    @media (max-width: 600px) {
        .purchase-step {
            padding: 25px 12px 20px;
        }
        .purchase-step h2 {
            font-size: 17px;
        }
        .step-item {
            width: 88px;
            font-size: 10px;
        }
        .step-line {
            width: 38px;
        }
        .confirm-layout {
            padding: 16px 12px 35px;
        }
        .confirm-box {
            padding: 15px;
        }
        .confirm-table th,
        .confirm-table td {
            padding: 10px;
        }
        .confirm-table th {
            width: 36%;
            font-size: 12px;
        }
        .method-confirm-card {
            align-items: flex-start;
            gap: 12px;
            padding: 14px;
        }
        .method-confirm-card > i {
            width: 28px;
            font-size: 25px;
        }
    }
</style>
</head>
<body>
    <?php require 'header.php'; ?>
    <!-- 以下は共通で使用できる。cssを使用して見た目を変えるだけでok -->
    <section class="purchase-step">
        <h2>ご購入・商談の手続き</h2>
        <div class="step-list">
            <div class="step-item">
                <div class="step-circle completed">1</div>
                <p>お客様情報入力</p>
            </div>
            <div class="step-line"></div>
            <div class="step-item">
                <div class="step-circle active">2</div>
                <p>内容確認</p>
            </div>
            <div class="step-line"></div> <!-- div という箱をCSSで調整することで、線として見せる -->
            <div class="step-item">
                <div class="step-circle">3</div>
                <p>受付完了</p>
            </div>
        </div>
        <h3>入力内容のご確認</h3>
        <p>
        下記の内容でよろしければ「送信する」ボタンを押してください。<br>
        修正する場合は、各項目の「編集」ボタンから変更してください。
        </p>
    </section>
    <main class="confirm-layout">
    <div class="confirm-main">
        <section class="confirm-box">
            <h3>お客様情報</h3>
            <table class="confirm-table">
            
                <tr>
                    <th>お名前</th>
                    <td><?= h($request_last_name . ' ' . $request_first_name) ?></td>
                </tr>
                <tr>
                    <th>フリガナ</th>
                    <td><?= h($request_last_name_kana . ' ' . $request_first_name_kana) ?></td>
                </tr>
                <tr>
                    <th>電話番号</th>
                    <td><?= h($phone) ?></td>
                </tr>
                <tr>
                    <th>メールアドレス</th>
                    <td><?= h($mail) ?></td>
                </tr>
                <tr>
                    <th>ご住所</th>
                    <td>
                        〒<?= h($postal_code) ?><br>
                        <?= h($prefecture) ?><?= h($address_detail) ?>
                    </td>
                </tr>
            </table>
        </section>
        <section class="confirm-box">
            <h3>商談日時</h3>
            <table class="confirm-table">
                <tr>
                    <th>商談日時</th>
                    <td>
                        <?= h($meeting_date) ?> <?= h($meeting_time) ?>〜
                        <p class="business-hours">
                            ※営業時間：10:00〜18:00（火曜定休）
                        </p>
                    </td>
                </tr>
            </table>
        </section>
        <section class="confirm-box">
        <h3>ご希望の購入・商談方法</h3>
        <div class="method-confirm-card">
            <i class="<?= h($selected_consultation['icon']) ?>" aria-hidden="true"></i>
            <div>
                <strong><?= h($selected_consultation['label']) ?></strong>
                <p><?= h($selected_consultation['description']) ?></p>
            </div>
        </div>
        </section>
        <section class="confirm-box">
            <h3>お支払い方法の希望</h3>
            <table class="confirm-table">
                <tr>
                    <th>お支払い方法</th>
                    <td><?= h($selected_payment) ?></td>
                </tr>
            </table>
            <?php if ($payment_method === 'auto_loan'): ?>
                <p class="form-note">
                    ※ローン審査をご希望の方は、後日スタッフよりご案内いたします。
                </p>
            <?php endif; ?>
        </section>
        <section class="confirm-box">
            <h3>ご希望の連絡方法</h3>
            <table class="confirm-table">
            <tr>
                <th>連絡方法</th>
                <td><?= h($selected_contact) ?></td>
            </tr>
            </table>
        </section>
        <section class="confirm-box">
            <h3>お問い合わせ・ご要望など</h3>
            <table class="confirm-table">
                <tr>
                    <th>お問い合わせ内容</th>
                    <td>
                        <?php if ($message !== ''): ?>
                            <?= nl2br(h($message)) ?>
                        <?php else: ?>
                            なし
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </section>
       <form action="quote_complete.php" method="post" class="confirm-actions">
    <!-- historyへ保存する値を完了画面へ引き継ぐ -->
    <input type="hidden" name="goods_id" value="<?= h($goods_id) ?>">
    <input
        type="hidden"
        name="consultation_method"
        value="<?= h($consultation_method) ?>"
    >
    <input
        type="hidden"
        name="payment_method"
        value="<?= h($payment_method) ?>"
    >
    <input
        type="hidden"
        name="contact_method"
        value="<?= h($contact_method) ?>"
    >
    <!-- 改行を含む可能性があるためtextareaで引き継ぐ -->
    <textarea name="message" hidden><?= h($message) ?></textarea>
    <button type="submit" class="confirm-button">
        上記の内容で送信する
        <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
    </button>
</form>
<a href="javascript:history.back()" class="back-link">
    <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
    入力画面に戻って修正する
</a>
    </div>
</main>
<?php require 'footer.php'; ?>
</body>
</html>