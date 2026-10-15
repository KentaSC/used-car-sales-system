<!-- ヘッダー読み込み -->
<?php require 'header.php'; ?>

<!-- 以下お問い合わせ送信・保存用PHP -->
<?php

//データベース接続設定
try {
    require_once __DIR__ . '/db_connect.php';
} catch (PDOException $e) {
    exit('データベースへの接続に失敗しました。');
}
//送信できなかった場合はエラーメッセージを表示させる
$message_sent = false;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    //入力値を取得
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // バリデーション（入力値チェック）

    //未入力チェック（空になってないか）
    if ($name === '' || $email === '' || $message === '') {
        $error_message = 'すべての項目を入力してください。';

    //名前の文字数チェック
    } elseif (mb_strlen($name) > 100) {
        $error_message = 'お名前は100文字以内で入力してください。';

    //名前の使用可能文字チェック
    // 漢字・ひらがな・全角カタカナ・スペースのみ許可
    } elseif (!preg_match('/^[\p{Han}\p{Hiragana}\p{Katakana} 　]+$/u', $name)
            || preg_match('/[\x{FF61}-\x{FF9F}]/u', $name)) {
        $error_message = 'お名前は漢字・ひらがな・全角カタカナで入力してください。';

    //メールアドレスの文字数チェック
    } elseif (mb_strlen($email) > 100) {
        $error_message = 'メールアドレスは100文字以内で入力してください。';
    
    //メールアドレス形式チェック
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = '正しいメールアドレスを入力してください。';

    //お問い合わせ内容の文字数チェック
    } elseif (mb_strlen($message) > 500) {
    $error_message = 'お問い合わせ内容は500文字以内で入力してください。'; 

    //すべてのチェックを通過したら↓
    } else {

        try {

            // データベースに保存
            $sql = "
                INSERT INTO contacts
                (name, email, message)
                VALUES
                (:name, :email, :message)
            ";

            // 上のSQLをプリペアドステートメントとして準備する
            $stmt = $pdo->prepare($sql);

            // 入力された値をSQLへ渡して実行（データベースに追加保存）
            // ※プレースホルダーを使用することで、ユーザーが入力した値をSQL文そのものとして解釈されないように（SQLインジェクション対策）
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':message' => $message
            ]);

            // 送信成功後にフォームの入力欄を空の状態に戻す
            $message_sent = true;

                $name = '';
                $email = '';
                $message = '';


        // データベース保存時のエラー
        } catch (PDOException $e) {

            $error_message = 'お問い合わせの送信に失敗しました。';

        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live motors | お問い合わせ</title>
    <!-- Font Awesome用 -->
    <link rel="stylesheet"href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* ページ全体 */
        .contact-page {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 30px;
            color: #333;
        }

        /* お問い合わせ見出し */
        .contact-title {
            margin: 28px 0 8px;
            color: #12382a;
            font-size: 27px;
            font-weight: 700;
            line-height: 1.4;
            text-align: left;
        }

        /* 手紙アイコン */
        .contact-title i {
            margin-right: 8px;
            font-size: 27px;
        }

        /* 説明文 */
        .contact-description {
            margin: 0 auto 16px;

            color: #555;
            font-size: 14px;
            line-height: 1.7;
        }

        /* フォーム部分全体 */
        .contact-form {
            width: 100%;
            max-width: none;

            margin: 0;

            padding: 16px 15px 25px;

            background-color: #fff;

            border: 1px solid #e8e8e8;
            border-radius: 6px;

            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
        }

        /* フォーム内各項目 */
        .contact-form-group {
            margin-bottom: 17px;
        }
    
        .contact-form-group:last-child {
            margin-bottom: 0;
        }
        
        /* ラベル部分 */
        .contact-label {
            display: inline-flex;
            align-items: center;
            gap: 7px;

            margin-bottom: 8px;

            color: #222;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.4;
        }

        /* 必須部分 */
        .contact-required {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            height: 16px;
            padding: 0 5px;

            background-color: #004831;
            color: #fff;

            border-radius: 3px;

            font-size: 10px;
            font-weight: 700;
            line-height: 1;
        }

        /* 入力欄 */
        .contact-input,
        .contact-textarea {
            display: block;

            width: 100%;

            background-color: #fff;
            color: #333;

            border: 1px solid #e1e1e1;
            border-radius: 6px;

            font-family: inherit;
            font-size: 14px;

            outline: none;

            transition:
                border-color 0.2s,
                box-shadow 0.2s;
        }

        /* テキスト・メール */
        .contact-input {
            height: 40px;
            padding: 0 12px;
        }


        /* お問い合わせ内容 */
        .contact-textarea {
            min-height: 134px;

            padding: 10px 12px;

            line-height: 1.6;

            resize: vertical;
        }


        /* 薄い文字の例の部分 */
        .contact-input::placeholder,
        .contact-textarea::placeholder {
            color: #aaa;
        }


        /* フォーカス時 */
        .contact-input:focus,
        .contact-textarea:focus {
            border-color: #004831;

            box-shadow:
                0 0 0 2px rgba(0, 72, 49, 0.08);
        }

        /* 送信ボタン */
        .contact-submit {
            width: 100%;
            height: 40px;

            display: flex;
            align-items: center;
            justify-content: center;

            border: 1px solid #004831;
            border-radius: 5px;

            background-color: #004831;
            color: #fff;

            font-family: inherit;
            font-size: 14px;
            font-weight: 600;

            cursor: pointer;

            transition:
                background-color 0.2s,
                border-color 0.2s;
        }


        .contact-submit:hover {
            background-color: #003a28;
            border-color: #003a28;
        }

        /* ========================================
        スマートフォン対応
        ======================================== */

        @media (max-width: 600px) {

            .contact-page {
                width: 100%;
                padding: 0 15px 30px;
            }

            .contact-title {
                margin-top: 20px;
                font-size: 19px;
            }

            .contact-description {
                font-size: 11px;
            }

            .contact-form {
                max-width: none;
                padding: 16px 14px 22px;
            }
        }
    </style>
</head>

<body>
    <main class="contact-page">

    <!-- お問い合わせ見出し -->
    <h2 class="contact-title"><i class="fa-regular fa-envelope"></i>お問い合わせ</h2>

    <!-- 説明文 -->
    <p class="contact-description">
        ご不明な点やご質問などがございましたら、<br>
        下記フォームよりお気軽にお問い合わせください。
    </p>

    <!-- 以下フォーム -->
    <!-- action=""なので送信先は現在のページ、method="post"によって入力内容をPOST送信する -->
    <form action="" method="post" class="contact-form">

        <!-- お問い合わせ送信後メッセージ -->
        <!-- データベースへの保存成功してtrueになっていれば、送信成功のメッセージを表示 -->
        <?php if ($message_sent): ?>

        <p style="color: #004831;">
            お問い合わせを受け付けました。ありがとうございます。
        </p>

        <!-- エラーが発生している場合は、ここでエラーメッセージを表示 -->
        <?php elseif ($error_message !== ''): ?>

            <p style="color: #c00;">
                <?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>

        <!-- お名前入力欄 -->
        <div class="contact-form-group">
            <label for="name" class="contact-label">
                お名前<span class="contact-required">必須</span>
            </label>

            <input
                type="text"
                id="name"
                name="name"
                class="contact-input"
                placeholder="例）山田 太郎"
                value="<?= htmlspecialchars($name ?? '', ENT_QUOTES, 'UTF-8') ?>"
                required
            >
            <!-- value="<?= htmlspecialchars($name ?? '', ENT_QUOTES, 'UTF-8') ?>"は
             エラーの後に今まで入力した内容が消えないように再表示する処理 -->

        </div>

        <!-- メールアドレス入力欄 -->
        <div class="contact-form-group">
            <label for="email" class="contact-label">
                メールアドレス<span class="contact-required">必須</span>
            </label>

            <input
                type="email"
                id="email"
                name="email"
                class="contact-input"
                placeholder="例）example@email.com"
                value="<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>"
                required
            >
        </div>

        <!-- お問い合わせ内容入力欄 -->
        <div class="contact-form-group">
            <label for="message" class="contact-label">
                お問い合わせ内容<span class="contact-required">必須</span>
            </label>

            <textarea
                id="message"
                name="message"
                class="contact-textarea"
                placeholder="お問い合わせ内容を500文字以内でご入力ください。"
                required><?= htmlspecialchars($message ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <!-- 送信するボタン -->
        <div class="contact-form-group">
            <button type="submit" class="contact-submit">送信する</button>
        </div>
    </form>
    </main>

    <!-- フッター読み込み -->
    <?php require 'footer.php'; ?>
</body>
</html>