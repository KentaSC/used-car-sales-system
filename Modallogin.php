<?php
/* ==========================================================
 * 1. セッションおよび変数の初期化
 * ========================================================== */
// セッションを開始または再開し、ユーザーのログイン状態（$_SESSION['customer']等）を管理できるようにします[cite: 4]
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// エラーメッセージ、成功メッセージ、モーダル制御用の処理モード（$action_mode）を初期化します[cite: 4]
$error = '';
$success_message = '';
$action_mode = '';

// フォームで入力された値を保持し、入力エラー時に再表示（リレー）するための変数を初期化します[cite: 4]
$mail_input = '';
$last_name = '';
$first_name = '';
$last_name_kana = '';
$first_name_kana = '';


/* ==========================================================
 * 2. データベース接続
 * ========================================================== */
require_once __DIR__ . '/db_config.php';
try {
    // PDOを使用したMySQLデータベースへの接続設定[cite: 4]
$pdo = new PDO(
    "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
    $dbUser,
    $dbPass,
    [
        // SQLエラー時に例外を発生させる
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        // 取得結果を連想配列として扱う
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // プリペアドステートメントを使用する
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
    );
    
} catch (PDOException $e) {
    // DB接続失敗時はログにのみ詳細を出力し、ユーザーには詳細なエラー理由を隠蔽して画面を安全に停止します[cite: 4]
    error_log('Database Connection Error: ' . $e->getMessage());
    http_response_code(500);
    die('システムエラーが発生しました。しばらく経ってから再度お試しください。');
}

/* ==========================================================
 * 3. フォーム送信（POSTリクエスト）時の処理分岐
 * ========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ------------------------------------------------------
    // 3-1. ログイン処理 ('login')
    // ------------------------------------------------------
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        // 再ログイン処理の前に一旦セッションの会員情報をクリア[cite: 4]
        unset($_SESSION['customer']);

        // 入力値の前後空白を除去して取得[cite: 4]
        $mail_input = trim($_POST['mail'] ?? '');
        $password_input = $_POST['password'] ?? '';

        // 入力されたメールアドレスでユーザーレコードを検索[cite: 4]
        $sql = $pdo->prepare('SELECT * FROM users WHERE mail = ?');
        $sql->execute([$mail_input]);
        $row = $sql->fetch();

        // ユーザーが存在し、かつパスワードハッシュの一致検証が成功した場合[cite: 4]
        if ($row && password_verify($password_input, $row['password'])) {
            // セッション固定化攻撃（Session Fixation）を防止するため、ログイン成功時にセッションIDを新しく更新します[cite: 4]
            session_regenerate_id(true);

            // セッションにログインユーザーの基本情報を保持[cite: 4]
            $_SESSION['customer'] = [
                'id'         => $row['userid'],
                'userid'     => $row['userid'],
                'last_name'  => $row['last_name'],
                'first_name' => $row['first_name'],
                'mail'       => $row['mail']
            ];
        } else {
            // 認証失敗時：エラーメッセージをセットし、ログインモーダルを再開するように指示[cite: 4]
            $error = 'メールアドレスまたはパスワードが違います。';
            $action_mode = 'login';
        }
    }

    // ------------------------------------------------------
    // 3-2. 新規登録・会員情報変更処理 ('register_or_update')
    // ------------------------------------------------------
    if (isset($_POST['action']) && $_POST['action'] === 'register_or_update') {

        // 各入力項目を取得（前後空白を除去）[cite: 4]
        $mail_input      = trim($_POST['mail'] ?? '');
        $last_name       = trim($_POST['last_name'] ?? '');
        $first_name      = trim($_POST['first_name'] ?? '');
        $last_name_kana  = trim($_POST['last_name_kana'] ?? '');
        $first_name_kana = trim($_POST['first_name_kana'] ?? '');

        // ------------------------------------------------------
        // お名前・フリガナの文字種バリデーション（共通チェック）
        // ------------------------------------------------------
        // 漢字・ひらがなチェック（表外漢字や々なども許容）
$kanji_hiragana_pattern = '/^[一-龠々〆ヵヶぁ-ん]+$/u';
        // 全角カタカナチェック（長音「ー」やー表記も許容）
        $katakana_pattern = '/^[ァ-ヶー]+$/u';

        if (mb_strlen($mail_input, 'UTF-8') > 100) {
            $error = 'メールアドレスは100文字以内で入力してください。';
            $action_mode = 'register';
        } elseif (mb_strlen($last_name, 'UTF-8') > 10 || mb_strlen($first_name, 'UTF-8') > 10) {
            $error = 'お名前（姓・名）はそれぞれ10文字以内で入力してください。';
            $action_mode = 'register';
        } elseif (mb_strlen($last_name_kana, 'UTF-8') > 15 || mb_strlen($first_name_kana, 'UTF-8') > 15) {
            $error = 'フリガナ（セイ・メイ）はそれぞれ15文字以内で入力してください。';
            $action_mode = 'register';
        } elseif (!preg_match($kanji_hiragana_pattern, $last_name) || !preg_match($kanji_hiragana_pattern, $first_name)) {
            $error = 'お名前（姓・名）は漢字および「ひらがな」のみで入力してください。';
            $action_mode = 'register';
        } elseif (!preg_match($katakana_pattern, $last_name_kana) || !preg_match($katakana_pattern, $first_name_kana)) {
            $error = 'フリガナ（セイ・メイ）は「全角カタカナ」のみで入力してください。';
            $action_mode = 'register';
        }

        if (isset($_SESSION['customer'])) {
            // 【会員情報変更処理のバリデーション】[cite: 4]
            $current_password     = $_POST['current_password'] ?? '';
            $new_password         = $_POST['new_password'] ?? '';
            $new_password_confirm = $_POST['new_password_confirm'] ?? '';

            // 現在ログイン中のユーザーの現在のパスワードをDBから取得[cite: 4]
            $current_userid = $_SESSION['customer']['userid'];
            $sql = $pdo->prepare('SELECT * FROM users WHERE userid = ?');
            $sql->execute([$current_userid]);
            $current_user_row = $sql->fetch();

            // 現在のパスワードが未入力、またはハッシュ値が不一致の場合は変更不可[cite: 4]
            if (empty($current_password) || !password_verify($current_password, $current_user_row['password'])) {
                $error = '現在のパスワードが正しくありません。';
                $action_mode = 'register';
            } elseif (!empty($new_password)) {
                // 新しいパスワードが入力されている場合のバリデーション[cite: 4]
                if ($new_password !== $new_password_confirm) {
                    $error = '新しいパスワードと確認用パスワードが一致しません。';
                    $action_mode = 'register';
                } elseif (strlen($new_password) < 8 || !preg_match('/^[a-zA-Z0-9]+$/', $new_password)) {
                    $error = 'パスワードは8文字以上の半角英数字で入力してください。';
                    $action_mode = 'register';
                }
            }
        } else {
            // ------------------------------------------------------
            // 【新規登録処理のバリデーション】
            // ------------------------------------------------------
            // フォームから送信された「パスワード」と「確認用パスワード」を取得（未入力時は空文字）
            $password_input   = $_POST['password'] ?? '';
            $password_confirm = $_POST['password_confirm'] ?? '';

            // 1. パスワードの一致チェック
            if ($password_input !== $password_confirm) {
                // 入力した2つのパスワードが一致しない場合
                $error = 'パスワードと確認用パスワードが一致しません。';
                $action_mode = 'register'; // 新規登録画面（モーダル/ページ）にとどめる

            // 2. パスワードの文字数・形式ルールチェック
            } elseif (strlen($password_input) < 8 || !preg_match('/^[a-zA-Z0-9]+$/', $password_input)) {
                // 8文字未満、または半角英数字以外の文字が含まれている場合
                $error = 'パスワードは8文字以上の半角英数字で入力してください。';
                $action_mode = 'register'; // 新規登録画面（モーダル/ページ）にとどめる
            }
        }

        // バリデーションエラーがなければDBの登録・更新処理へ進みます[cite: 4]
        if (empty($error)) {
            try {
                // アトミック（不可分）なDB処理を担保するためトランザクションを開始[cite: 4]
                $pdo->beginTransaction();

                if (isset($_SESSION['customer'])) {
                    // --- 会員情報更新（UPDATE） ---[cite: 4]
                    $current_userid = $_SESSION['customer']['userid'];

                    if (!empty($new_password)) {
                        // 新しいパスワードが指定されている場合はパスワードも含めてUPDATE[cite: 4]
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $sql = $pdo->prepare(
                            'UPDATE users SET password=?, mail=?, last_name=?, first_name=?, last_name_kana=?, first_name_kana=? WHERE userid=?'
                        );
                        $sql->execute([$hashed_password, $mail_input, $last_name, $first_name, $last_name_kana, $first_name_kana, $current_userid]);
                    } else {
                        // パスワード変更なしでプロフィール情報のみUPDATE[cite: 4]
                        $sql = $pdo->prepare(
                            'UPDATE users SET mail=?, last_name=?, first_name=?, last_name_kana=?, first_name_kana=? WHERE userid=?'
                        );
                        $sql->execute([$mail_input, $last_name, $first_name, $last_name_kana, $first_name_kana, $current_userid]);
                    }

                    // セッション内の情報を最新の状態に同期[cite: 4]
                    $_SESSION['customer']['mail'] = $mail_input;
                    $_SESSION['customer']['last_name'] = $last_name;
                    $_SESSION['customer']['first_name'] = $first_name;

                    $success_message = 'お客様情報を更新しました。';
                    $action_mode = 'success';

                } else {
                    // --- 新規会員登録（INSERT） ---[cite: 4]
                    // 1. メールアドレスの重復チェック[cite: 4]
                    $sql = $pdo->prepare('SELECT userid FROM users WHERE mail=?');
                    $sql->execute([$mail_input]);

                    if ($sql->fetch()) {
                        $error = 'このメールアドレスはすでに使用されています。';
                        $action_mode = 'register';
                        throw new Exception($error);
                    } else {
                        // 2. パスワードのハッシュ化[cite: 4]
                        $hashed_password = password_hash($password_input, PASSWORD_DEFAULT);

                        // 3. 連番のユーザーID（例: U001, U002...）を採番[cite: 4]
                        // 排他制御（FOR UPDATE）で同時アクセス時のID重複を防ぎます[cite: 4]
                        $stmt_max = $pdo->query('SELECT userid FROM users ORDER BY CAST(SUBSTRING(userid, 2) AS UNSIGNED) DESC LIMIT 1 FOR UPDATE');
                        $last_row = $stmt_max->fetch();
                        if ($last_row && preg_match('/^U(\d+)$/', $last_row['userid'], $matches)) {
                            $next_num = intval($matches[1]) + 1;
                        } else {
                            $next_num = 1;
                        }

                        // 数値を3桁ゼロパディング（U001形式）にする[cite: 4]
                        $userid_to_register = sprintf('U%03d', $next_num);

                        // 4. DBへ新規ユーザー挿入[cite: 4]
                        $sql = $pdo->prepare(
                            'INSERT INTO users (userid, password, mail, last_name, first_name, last_name_kana, first_name_kana, entrydate, deleteflg)
                             VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE(), 0)'
                        );
                        $sql->execute([
                            $userid_to_register,
                            $hashed_password,
                            $mail_input,
                            $last_name,
                            $first_name,
                            $last_name_kana,
                            $first_name_kana
                        ]);
                        $success_message = 'お客様の登録が完了しました。';
                        $action_mode = 'success';
                    }
                }

                // すべての処理が問題なく完了したらコミットして確定[cite: 4]
                $pdo->commit();
            } catch (Exception $e) {
                // エラー時はロールバック
                // して変更を取り消し[cite: 4]
                $pdo->rollBack();
                if (empty($error)) {
                    $error = '処理中にエラーが発生しました。もう一度お試しください。';
                }
                $action_mode = 'register';
            }
        } else {
            $action_mode = 'register';
        }
    }

    // ------------------------------------------------------
    // 3-3. パスワード忘れ処理 ('forgot_password')
    // ------------------------------------------------------
    if (isset($_POST['action']) && $_POST['action'] === 'forgot_password') {
        $mail_input = trim($_POST['forgot_mail'] ?? '');
        
        // 入力されたメールアドレスのユーザーが存在するか確認[cite: 4]
        $sql = $pdo->prepare('SELECT userid FROM users WHERE mail = ?');
        $sql->execute([$mail_input]);
        $user = $sql->fetch();

        if ($user) {
            // 暗号学的に安全なランダムトークン（64文字の16進数）と、30分後の有効期限を生成[cite: 4]
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+30 minutes'));

            // DBに再設定トークンと有効期限を一時保存[cite: 4]
            $update_sql = $pdo->prepare('UPDATE users SET reset_token = ?, reset_expires = ? WHERE mail = ?');
            $update_sql->execute([$token, $expires, $mail_input]);

            // 次の検証ステップで使用するためにセッションに保持[cite: 4]
            $_SESSION['reset_mail'] = $mail_input;

            // PHPMailerライブラリをロードして案内メールを送信[cite: 4]
            require __DIR__ . '/../vendor/autoload.php';
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = '127.0.0.1'; // テスト用SMTPサーバー（Mailpit / Mailhog 等）[cite: 4]
                $mail->SMTPAuth   = false;
                $mail->Port       = 1025;
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom('noreply@example.com', 'スーパー管理者 山田');
                $mail->addAddress($mail_input);
                $mail->Subject = '【ライブモーター】パスワード再設定のご案内';
                
                // HTML形式メール[cite: 4]
                $mail->isHTML(true);
                $mail->Body = "<p>パスワード再設定の要求を受け付けました。</p>"
                            . "<p>以下の確認コードをパスワード再設定画面に入力してください。</p>"
                            . "<p><strong>確認コード: " . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . "</strong></p>"
                            . "<p><small>※このコードの有効期限は30分です。</small></p>";

                // プレーンテキスト代替本文（HTML非対応クライアント用）[cite: 4]
                $mail->AltBody = "パスワード再設定の要求を受け付けました。\n\n"
                               . "以下の確認コードをパスワード再設定画面に入力してください。\n\n"
                               . "確認コード: " . $token . "\n\n"
                               . "※このコードの有効期限は30分です。";
                $mail->send();
            } catch (Exception $e) {
                error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            }
        }

        // セキュリティ対策（メールアドレス存在の有無を漏洩させないため、対象ユーザーの有無に関わらず同じ成功メッセージを表示）[cite: 4]
        $success_message = 'ご登録がある場合、パスワード再設定用の案内を送信しました。メールに記載された確認コードを入力してください。';
        $action_mode = 'forgot_sent';
    }
    

// ------------------------------------------------------
    // 3-3-2. 確認コード（トークン）の検証前処理 ('verify_token')
    // ------------------------------------------------------
    if (isset($_POST['action']) && $_POST['action'] === 'verify_token') {
        // フォームから入力された確認コードを取得（前後の不要な空白を削除）
        $token_input = trim($_POST['reset_token'] ?? '');

        // 対象のメールアドレスを取得（セッションの値を優先し、無ければPOST値から取得）
        $mail_input = $_SESSION['reset_mail'] ?? ($_POST['reset_mail'] ?? '');

        // メールアドレスが取得できない場合（セッション切れ・不正な遷移時）のチェック
        if (empty($mail_input)) {
            // エラーメッセージを設定
            $error = 'セッションの有効期限が切れました。最初からやり直してください。';
            // 処理モードを「パスワード忘れ（メールアドレス入力画面）」に変更してやり直させる
            $action_mode = 'forgot_password'; 
        } else {
            // DBから保存済みのトークンと期限を取得[cite: 4]
            $sql = $pdo->prepare('SELECT reset_token, reset_expires FROM users WHERE mail = ?');
            $sql->execute([$mail_input]);
            $user_data = $sql->fetch();

            $now = date('Y-m-d H:i:s');

            // タイミング攻撃（Timing Attack）を防止するため hash_equals でトークン文字列を安全に比較比較します[cite: 4]
            if (!$user_data || empty($user_data['reset_token']) || !is_string($user_data['reset_token']) || hash_equals($user_data['reset_token'], $token_input) === false) {
                $error = '確認コードが正しくありません。';
                $action_mode = 'input_token_modal';
            } elseif ($user_data['reset_expires'] < $now) {
                // 有効期限切れの判定[cite: 4]
                $error = '確認コードの有効期限が切れています。最初からやり直してください。';
                $action_mode = 'forgot_password';
            } else {
                // コード検証に成功したためフラグをセッションにセットし、パスワード再入力画面へ移ります[cite: 4]
                $_SESSION['reset_mail'] = $mail_input;
                $_SESSION['token_verified'] = true;
                $action_mode = 'reset_password_modal';
            }
        }
    }

    // ------------------------------------------------------
    // 3-4. パスワード再登録処理 ('reset_password')
    // ------------------------------------------------------
    if (isset($_POST['action']) && $_POST['action'] === 'reset_password') {
        // 対象ユーザーのメールアドレスを取得（セッションの値を優先し、無ければPOST値から取得）
        $mail_input       = $_SESSION['reset_mail'] ?? ($_POST['reset_mail'] ?? ''); 

        // 確認コードの検証が済んでいるかどうかのフラグ判定（true / false）
        $is_verified = !empty($_SESSION['token_verified']);

        // フォームから送信された新しいパスワードと確認用パスワードを取得
        $new_pass        = $_POST['new_password'] ?? '';
        $new_pass_confirm = $_POST['new_password_confirm'] ?? '';

        // 有効期限のチェックに使用するため、現在日時を取得（フォーマット: YYYY-MM-DD HH:MM:SS）
        $now = date('Y-m-d H:i:s');

        // 改めてDBからトークンと有効期限の両方を取得して正当性を再検証[cite: 4]
        $sql = $pdo->prepare('SELECT reset_token, reset_expires FROM users WHERE mail = ?');
        $sql->execute([$mail_input]);
        $user_data = $sql->fetch();

        // 不正アクセスまたはセッション切れの判定[cite: 4]
        // --- 新しいパスワードの設定前バリデーション（入力・権限・有効期限チェック） ---

        // 1. セッション切れ・不正アクセス・トークン不在のチェック
        if (empty($mail_input) || !$is_verified || empty($user_data['reset_token'])) {
            // メールアドレス未設定、コード認証未完了、またはトークンがDBに存在しない場合
            $error = 'セッションが無効か、有効期限が切れています。最初からやり直してください。';
            $action_mode = 'forgot_password'; // 最初（メールアドレス入力画面）に戻す

        // 2. 確認コードの有効期限チェック
        } elseif ($user_data && $user_data['reset_expires'] < $now) {
            // DB内の有効期限（reset_expires）が現在時刻（$now）を過ぎている場合
            $error = '確認コードの有効期限が切れています。最初からやり直してください。';
            $action_mode = 'forgot_password'; // 最初（メールアドレス入力画面）に戻す

        // 3. パスワードの一致チェック
        } elseif ($new_pass !== $new_pass_confirm) {
            // 新しいパスワードと確認用パスワードが一致しない場合
            $error = '新しいパスワードと確認用パスワードが一致しません。';
            $action_mode = 'reset_password_modal'; // パスワード再設定画面にとどめる

        // 4. パスワードの文字数・形式（仕様ルール）チェック
        } elseif (strlen($new_pass) < 8 || !preg_match('/^[a-zA-Z0-9]+$/', $new_pass)) {
            // 8文字未満、または半角英数字以外の文字が含まれている場合
            $error = 'パスワードは8文字以上の半角英数字で入力してください。';
            $action_mode = 'reset_password_modal'; // パスワード再設定画面にとどめる
        } else {
            try {
                $pdo->beginTransaction();
                $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);

                // 新しいパスワードを保存し、同時に再設定用トークンと有効期限を破棄（NULL化）して再利用を防止[cite: 4]
                $sql = $pdo->prepare('UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE mail = ?');
                $sql->execute([$hashed_password, $mail_input]);
                
                $pdo->commit();

                // 使用済みの再設定セッション変数をクリア[cite: 4]
                unset($_SESSION['reset_mail']);
                unset($_SESSION['token_verified']);
                
                $success_message = 'パスワードを再設定しました。新しいパスワードでログインしてください。';
                $action_mode = 'success';
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'パスワードの更新に失敗しました。';
                $action_mode = 'reset_password_modal';
            }
        }
    }
}

/* ==========================================================
 * 4. ログアウト処理
 * ========================================================== */
// if (isset($_GET['logout'])) {
//     // セッション内の会員情報を削除[cite: 4]
//     unset($_SESSION['customer']);
//     $_SESSION['logged_out'] = true; // ログアウト完了モーダル表示用のフラグ[cite: 4]
//     session_regenerate_id(true);    // セッションIDを刷新[cite: 4]

//     // URLクエリパラメータ（?logout=1）を除去してリダイレクト（二重実行を防止）[cite: 4]
//     header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
//     exit();
// }

if (isset($_GET['logout'])) {
    unset($_SESSION['customer']);
    header('Location: index.php?logged_out=1');
    exit();
}
/* ==========================================================
 * 5. ログイン中のユーザー情報取得
 * ========================================================== */
if (isset($_SESSION['customer'])) {
    $last_name  = $_SESSION['customer']['last_name'] ?? '';
    $first_name = $_SESSION['customer']['first_name'] ?? '';
    $mail_input = $_SESSION['customer']['mail'] ?? '';

    // POST送信時以外でフリガナデータが未設定の場合、DBから最新のユーザー情報を取得してフォーム入力欄を補完[cite: 4]
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' && (empty($last_name_kana) || empty($first_name_kana))) {
        $sql = $pdo->prepare('SELECT * FROM users WHERE userid = ?');
        $sql->execute([$_SESSION['customer']['userid']]);
        $current_user = $sql->fetch();
        
        if ($current_user) {
            $mail_input      = $current_user['mail'];
            $last_name       = $current_user['last_name'];
            $first_name      = $current_user['first_name'];
            $last_name_kana  = $current_user['last_name_kana'];
            $first_name_kana = $current_user['first_name_kana'];
            
            // セッション情報も最新情報に同期[cite: 4]
            $_SESSION['customer']['last_name'] = $last_name;
            $_SESSION['customer']['first_name'] = $first_name;
            $_SESSION['customer']['mail'] = $mail_input;
        }
    }
}
?>

<!-- カスタムスタイル（モーダルの見た目調整用）[cite: 4] -->
<style>
.custom-modal .modal-content {
    background-color: #fcfdfd; 
    border: 2px solid #198754;
    border-radius: 15px;
}
</style>

<!-- ==========================================================
 * 7-1. ログイン用モーダル
 * ========================================================== -->
<!-- ====================================================== -->
<!-- ログイン用 モダールウィンドウ -->
<!-- ====================================================== -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog custom-modal">
        <div class="modal-content">
            <!-- ログインフォーム送信設定（POST送信） -->
            <form action="index.php" method="POST">
                <!-- サーバー側で処理を識別するための隠しパラメータ -->
                <input type="hidden" name="action" value="login">
                
                <!-- モダールヘッダー -->
                <div class="modal-header">
                    <h5 class="modal-title">ログイン</h5>
                    <!-- 閉じるボタン（×） -->
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
                </div>
                
                <!-- モダール本体 -->
                <div class="modal-body">
                    <!-- サイトロゴ表示 -->
                    <div class="text-center mb-4">
                        <img src="img/logo_small.png" alt="サイトロゴ" class="img-fluid" style="max-height: 200px;">
                    </div>
                    
                    <!-- ログインエラー時のメッセージ表示（PHP処理） -->
                    <?php if (!empty($error) && $action_mode === 'login'): ?>
                        <div class="alert alert-danger" role="alert">
                            <!-- XSS対策のため HTML エスケープ処理を実施 -->
                            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>

                    <!-- メールアドレス入力欄 -->
                    <div class="mb-3">
                        <label for="mail" class="form-label">メールアドレス</label>
                        <!-- 入力値保持のため value に前回の入力値を自動補完 -->
                        <input type="email" class="form-control" id="mail" name="mail" value="<?php echo htmlspecialchars($mail_input, ENT_QUOTES, 'UTF-8'); ?>" placeholder="example@email.com" required>
                    </div>

                    <!-- パスワード入力欄 -->
                    <div class="mb-3">
                        <label for="password" class="form-label">パスワード</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" required>
                            <!-- パスワード表示/非表示切り替え用ボタン -->
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">🫣</button>
                        </div>
                    </div>
                    
                    <!-- 画面遷移リンク（新規登録・パスワード再設定） -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <!-- ログインモダールを閉じ、新規登録モダール(#customerModal)を開く -->
                        <a href="#" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#customerModal" class="text-decoration-none small text-primary">初めての方（新規会員登録）</a>
                        <!-- パスワード再設定画面起動用リンク -->
                        <a href="#" id="openForgotPassword" class="text-decoration-none small text-success">パスワードをお忘れの方はこちら</a>
                    </div>
                </div>
                
                <!-- モダールフッター（送信・キャンセルボタン） -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="submit" class="btn btn-primary">ログインする</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==========================================================
 * 7-2. 新規登録・会員情報変更用モーダル
 * ========================================================== -->
<div class="modal fade" id="customerModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog custom-modal">
        <div class="modal-content">
            <!-- 送信フォーム（POST送信） -->
            <form action="" method="POST">
                <!-- サーバー側で新規登録か情報変更かを判定するためのアクション識別値 -->
                <input type="hidden" name="action" value="register_or_update">
                
                <!-- モダールヘッダー -->
                <div class="modal-header">
                    <h5 class="modal-title">
                        <!-- ログイン状態に応じてタイトルを動的に変更 -->
                        <?php echo isset($_SESSION['customer']) ? 'お客様情報の変更' : '新規会員登録（無料登録など）'; ?>
                    </h5>
                    <!-- 閉じるボタン（×） -->
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
                </div>
                
                <!-- モダール本体 -->
                <div class="modal-body">
                    <!-- ロゴおよび未ログイン時用案内文 -->
                    <div class="text-center mb-3">
                        <img src="img/logo_small.png" alt="サイトロゴ" class="img-fluid" style="max-height: 200px;">
                        <?php if (!isset($_SESSION['customer'])): ?>
                            <p class="text-muted small mt-2">会員登録をして、便利な機能をご利用いただけます。</p>
                        <?php endif; ?>
                    </div>

                    <!-- バリデーションエラーメッセージの表示 -->
                    <?php if (!empty($error) && $action_mode === 'register'): ?>
                        <div class="alert alert-danger" role="alert">
                            <!-- XSS対策のため HTML エスケープ処理を実施 -->
                            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['customer'])): ?>
                        <!-- ====================================================== -->
                        <!-- ログイン時：会員情報変更フォーム -->
                        <!-- ====================================================== -->
                        <div class="mb-3">
                            <label class="form-label">メールアドレス <span class="badge bg-danger">必須</span></label>
                            <input type="email" class="form-control" name="mail" placeholder="example@email.com" value="<?php echo htmlspecialchars($mail_input, ENT_QUOTES, 'UTF-8'); ?>" maxlength="100" required>
                        </div>

                    <!-- お名前（姓・名） -->
                    <div class="mb-3">
                        <label class="form-label">お名前 <span class="badge bg-danger">必須</span></label>
                        <div class="row g-2">
                                <div class="col">
                                    <input type="text" class="form-control" name="last_name" placeholder="姓" value="<?php echo htmlspecialchars($last_name, ENT_QUOTES, 'UTF-8'); ?>" pattern="^[一-龠々〆ヵヶぁ-ん]+$" title="漢字またはひらがなで入力してください"maxlength="10" required>
                            </div>
                            <div class="col">
                                <input type="text" class="form-control" name="first_name" placeholder="名" value="<?php echo htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8'); ?>" pattern="^[一-龠々〆ヵヶぁ-ん]+$" title="漢字またはひらがなで入力してください"maxlength="10" required>
                            </div>
                        </div>
                    </div>

                    <!-- フリガナ（セイ・メイ） -->
                    <div class="mb-3">
                        <label class="form-label">フリガナ <span class="badge bg-danger">必須</span></label>
                        <div class="row g-2">
                            <div class="col">
                                <input type="text" class="form-control" name="last_name_kana" placeholder="セイ" value="<?php echo htmlspecialchars($last_name_kana, ENT_QUOTES, 'UTF-8'); ?>" pattern="^[ァ-ヶー]+$" title="全角カタカナで入力してください"maxlength="15" required>
                            </div>
                            <div class="col">
                                <input type="text" class="form-control" name="first_name_kana" placeholder="メイ" value="<?php echo htmlspecialchars($first_name_kana, ENT_QUOTES, 'UTF-8'); ?>" pattern="^[ァ-ヶー]+$" title="全角カタカナで入力してください"maxlength="15" required>
                            </div>
                        </div>
                    </div>

                        <hr class="my-4">
                        
                        <!-- パスワード変更セクション -->
                        <div class="mb-3">
                            <h6 class="fw-bold">🔒 パスワードの変更</h6>
                            <p class="text-muted small">パスワードを変更する場合は、現在のパスワードと新しいパスワードを入力してください。</p>
                        </div>

                        <!-- 現在のパスワード入力欄 -->
                        <div class="mb-3">
                            <label class="form-label">現在のパスワード <span class="badge bg-danger">必須</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="current_password" name="current_password" placeholder="現在のパスワードを入力">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="current_password">🫣</button>
                            </div>
                        </div>

                        <!-- 新しいパスワード入力欄（任意） -->
                        <div class="mb-3">
                            <label class="form-label">新しいパスワード <span class="badge bg-secondary">任意</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password" name="new_password" placeholder="新しいパスワードを入力">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="new_password">🫣</button>
                            </div>
                        </div>

                        <!-- 新しいパスワード確認用（任意） -->
                        <div class="mb-3">
                            <label class="form-label">新しいパスワード（確認用） <span class="badge bg-secondary">任意</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password_confirm" name="new_password_confirm" placeholder="新しいパスワードを再入力">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="new_password_confirm">🫣</button>
                            </div>
                            <div class="form-text text-muted">※パスワードは8文字以上の半角英数字を含む文字列で設定してください。</div>
                        </div>

                    <?php else: ?>
                        <!-- ====================================================== -->
                        <!-- 未ログイン時：新規会員登録フォーム -->
                        <!-- ====================================================== -->
                        <!-- メールアドレス（ログインID兼用） -->
                        <div class="mb-3">
                            <label class="form-label">メールアドレス <span class="badge bg-danger">必須</span></label>
                            <input type="email" class="form-control" name="mail" placeholder="例) example@email.com" value="<?php echo htmlspecialchars($mail_input, ENT_QUOTES, 'UTF-8'); ?>"maxlength="100" required>
                            <div class="form-text text-muted">※ログインIDとして使用します</div>
                        </div>

                        <!-- パスワード -->
                        <div class="mb-3">
                            <label class="form-label">パスワード <span class="badge bg-danger">必須</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="register_password" name="password" placeholder="8文字以上の半角英数字を入力" required>                                       
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="register_password">🫣</button>
                            </div>
                            <div class="form-text text-muted">※半角英数字を含むこと</div>
                        </div>

                        <!-- パスワード（確認用） -->
                        <div class="mb-3">
                            <label class="form-label">パスワード（確認） <span class="badge bg-danger">必須</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="register_password_confirm" name="password_confirm" placeholder="もう一度パスワードを入力" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="register_password_confirm">🫣</button>
                            </div>
                        </div>

                        <!-- お名前（姓・名） -->
                        <div class="mb-3">
                            <label class="form-label">お名前 <span class="badge bg-danger">必須</span></label>
                            <div class="row g-2">
                                <div class="col">
                                    <input type="text" class="form-control" name="last_name" placeholder="姓" value="<?php echo htmlspecialchars($last_name, ENT_QUOTES, 'UTF-8'); ?>" maxlength="10" required>
                                </div>
                                <div class="col">
                                    <input type="text" class="form-control" name="first_name" placeholder="名" value="<?php echo htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8'); ?>" maxlength="10" required>
                                </div>
                            </div>
                        </div>

                        <!-- フリガナ（セイ・メイ） -->
                        <div class="mb-3">
                            <label class="form-label">フリガナ <span class="badge bg-danger">必須</span></label>
                            <div class="row g-2">
                                <div class="col">
                                    <input type="text" class="form-control" name="last_name_kana" placeholder="セイ" value="<?php echo htmlspecialchars($last_name_kana, ENT_QUOTES, 'UTF-8'); ?>" maxlength="15" required>
                                </div>
                                <div class="col">
                                    <input type="text" class="form-control" name="first_name_kana" placeholder="メイ" value="<?php echo htmlspecialchars($first_name_kana, ENT_QUOTES, 'UTF-8'); ?>" maxlength="15" required>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- モダールフッター -->
                <div class="modal-footer flex-column">
                    <!-- 送信ボタン（ログイン状態に応じて文言を変更） -->
                    <div class="w-100 mb-2">
                        <button type="submit" class="btn btn-primary w-100"><?php echo isset($_SESSION['customer']) ? '変更内容を保存する' : '登録する（無料）'; ?></button>
                    </div>
                    
                    <!-- 未ログイン時：ログインモダールへの切り替えリンク / ログイン時：キャンセルボタン -->
                    <?php if (!isset($_SESSION['customer'])): ?>
                        <div class="text-center w-100">
                            <small class="text-muted">すでにアカウントをお持ちの方はこちら <a href="#" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#loginModal" class="text-decoration-underline">ログイン</a></small>
                        </div>
                    <?php else: ?>
                        <div class="w-100 text-end">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">キャンセル</button>
                        </div>
                    <?php endif; ?>
                </div> 
            </form> 
        </div> 
    </div> 
</div>

<!-- ==========================================================
 * 7-3. パスワードをお忘れの方用モーダル
 * ========================================================== -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <!-- modal-dialog-centered により画面中央に表示 -->
    <div class="modal-dialog custom-modal modal-dialog-centered">
        <div class="modal-content">
            <!-- パスワード再設定フォーム（POST送信） -->
            <form action="" method="POST">
                <!-- サーバー側でパスワード再設定処理と判定するためのアクション識別値 -->
                <input type="hidden" name="action" value="forgot_password">
                
                <!-- モダールヘッダー（枠線なし） -->
                <div class="modal-header border-bottom-0">
                    <!-- 閉じるボタン（×） -->
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
                </div>
                
                <!-- モダール本体（中央寄せレイアウト） -->
                <div class="modal-body text-center px-4 pb-4 pt-0">
                    <!-- タイトル & アイコンエリア -->
                    <div class="mb-3">
                        <div class="fs-1 text-success mb-2">🔒</div>
                        <h4 class="fw-bold mb-2">パスワードをお忘れの方</h4>
                        <p class="text-muted small">ご登録のメールアドレスを入力してください。</p>
                    </div>

                    <!-- エラーメッセージ表示エリア（PHP処理） -->
                    <?php if (!empty($error) && $action_mode === 'forgot_password'): ?>
                        <div class="alert alert-danger text-start" role="alert">
                            <!-- XSS対策のため HTML エスケープ処理を実施 -->
                            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>

                    <!-- メールアドレス入力欄（左寄せ） -->
                    <div class="mb-3 text-start">
                        <label class="form-label">メールアドレス <span class="badge bg-success">必須</span></label>
                        <input type="email" class="form-control" name="forgot_mail" placeholder="example@email.com" required>
                    </div>

                    <!-- 送信ボタン（ブロック要素で全幅表示） -->
                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" class="btn btn-success py-2 fw-bold" style="background-color: #0f3d2e;">送信する</button>
                    </div>

                    <!-- ログイン画面への戻りボタン（現在のモダールを閉じ、#loginModalを開く） -->
                    <div class="d-grid">
                        <button type="button" class="btn btn-outline-secondary py-2" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#loginModal">
                            &lt; ログイン画面に戻る
                        </button>
                    </div>
                </div> 
            </form> 
        </div> 
    </div> 
</div> 

<!-- ==========================================================
 * 7-4. パスワード再登録用モーダル
 * ========================================================== -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <!-- modal-dialog-centered により画面中央に配置 -->
    <div class="modal-dialog custom-modal modal-dialog-centered">
        <div class="modal-content">
            <!-- パスワード更新フォーム（POST送信） -->
            <form action="" method="POST">
                <!-- 処理を識別するキー -->
                <input type="hidden" name="action" value="reset_password">
                <!-- 対象ユーザーのメールアドレス（Null合体演算子でエラーを回避しつつ安全にエスケープ） -->
                <input type="hidden" name="reset_mail" value="<?php echo htmlspecialchars($_SESSION['reset_mail'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <!-- トークン検証済みであることを示すフラグ -->
                <input type="hidden" name="token_verified" value="1">
                
                <!-- モダールヘッダー（下線なし） -->
                <div class="modal-header border-bottom-0">
                    <!-- 閉じるボタン（×） -->
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
                </div>
                
                <!-- モダール本体 -->
                <div class="modal-body px-4 pb-4 pt-0">
                    <!-- ヘッダータイトル ＆ 説明文 -->
                    <div class="text-center mb-4">
                        <div class="fs-1 text-success mb-2">🔒</div>
                        <h4 class="fw-bold mb-2">新しいパスワードの設定</h4>
                        <p class="text-muted small">
                            新しいパスワードを入力してください。<br>
                            パスワードは8文字以上の半角英数字を含む文字列で設定してください。
                        </p>
                    </div>

                    <!-- エラーメッセージ表示エリア（PHP処理） -->
                    <?php if (!empty($error) && $action_mode === 'reset_password_modal'): ?>
                        <div class="alert alert-danger" role="alert">
                            <!-- XSS対策のため HTML エスケープ処理を実施 -->
                            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>

                    <!-- 新しいパスワード入力欄 -->
                    <div class="mb-3">
                        <label class="form-label">新しいパスワード <span class="badge bg-success">必須</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="reset_new_password" name="new_password" placeholder="新しいパスワードを入力" required>
                            <!-- パスワード切り替えボタン -->
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="reset_new_password">🫣</button>
                        </div>
                        <div class="form-text text-muted">※ 8文字以上の半角英数字を含む文字列</div>
                    </div>

                    <!-- 新しいパスワード（確認用）入力欄 -->
                    <div class="mb-4">
                        <label class="form-label">新しいパスワード（確認用） <span class="badge bg-success">必須</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="reset_new_password_confirm" name="new_password_confirm" placeholder="新しいパスワードを再入力" required>
                            <!-- パスワード切り替えボタン -->
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="reset_new_password_confirm">🫣</button>
                        </div>
                    </div>

                    <!-- パスワード更新実行ボタン -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success py-2 fw-bold" style="background-color: #0f3d2e;">パスワードを更新する</button>
                    </div>
                </div> 
            </form>
        </div> 
    </div> 
</div>

<!-- ==========================================================
 * 7-5. 確認コード入力専用モーダル
 * ========================================================== -->
<div class="modal fade" id="inputTokenModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <!-- modal-dialog-centered により画面中央に配置 -->
    <div class="modal-dialog custom-modal modal-dialog-centered">
        <div class="modal-content">
            <!-- トークン検証フォーム（POST送信） -->
            <form action="" method="POST">
                <!-- 処理識別用パラメータ -->
                <input type="hidden" name="action" value="verify_token">
                <!-- 対象ユーザーのメールアドレス（Null合体演算子で未定義エラーを回避しエスケープ処理） -->
                <input type="hidden" name="reset_mail" value="<?php echo htmlspecialchars($_SESSION['reset_mail'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                
                <!-- モダールヘッダー（下線なし） -->
                <div class="modal-header border-bottom-0">
                    <!-- 閉じるボタン（×） -->
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
                </div>
                
                <!-- モダール本体 -->
                <div class="modal-body px-4 pb-4 pt-0">
                    <!-- タイトル & アイコンエリア -->
                    <div class="text-center mb-4">
                        <div class="fs-1 text-success mb-2">🔑</div>
                        <h4 class="fw-bold mb-2">確認コードの入力</h4>
                        <p class="text-muted small">
                            メールに記載された確認コードを入力してください。
                        </p>
                    </div>

                    <!-- エラーメッセージ表示エリア（PHP処理） -->
                    <?php if (!empty($error) && $action_mode === 'input_token_modal'): ?>
                        <div class="alert alert-danger" role="alert">
                            <!-- XSS対策のため HTML エスケープ処理を実施 -->
                            <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>

                    <!-- 確認コード（トークン）入力欄 -->
                    <div class="mb-4">
                        <label class="form-label">確認コード <span class="badge bg-success">必須</span></label>
                        <input type="text" class="form-control" name="reset_token" placeholder="確認コードを入力" required>
                        <div class="form-text text-muted">※ Mailpit等に届いたコードを入力してください</div>
                    </div>

                    <!-- 送信（認証）ボタン -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success py-2 fw-bold" style="background-color: #0f3d2e;">コードを認証する</button>
                    </div>
                </div> 
            </form>
        </div> 
    </div> 
</div>

<!-- ==========================================================
 * 7-6. ログアウト完了通知モーダル
 * ========================================================== -->
<div class="modal fade" id="logoutMessageModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <!-- modal-dialog-centered により画面中央に配置 -->
    <div class="modal-dialog custom-modal modal-dialog-centered">
        <div class="modal-content">
            <!-- モダールヘッダー -->
            <div class="modal-header">
                <!-- 閉じるボタン（×） -->
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
            </div>
            
            <!-- モダール本体（ロゴおよびメッセージの表示エリア） -->
            <div class="modal-body text-center py-4">
                <!-- サイトロゴ -->
                <img src="img/logo_small.png" alt="サイトロゴ" class="img-fluid" style="max-height: 200px;">
                <!-- ログアウト完了メッセージ -->
                <p class="fs-5 mb-0">ログアウトしました。</p>
            </div>
            
            <!-- モダールフッター -->
            <div class="modal-footer">
                <!-- モダールを閉じるOKボタン -->
                <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">OK</button>
            </div> 
        </div> 
    </div> 
</div>

<!-- ==========================================================
 * 7-7. 成功メッセージ通知モーダル
 * ========================================================== -->
<div class="modal fade" id="successMessageModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <!-- modal-dialog-centered により画面中央に配置 -->
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- モダールヘッダー -->
            <div class="modal-header">
                <h5 class="modal-title">お知らせ</h5>
                <!-- 閉じるボタン（×） -->
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
            </div>
            
            <!-- モダール本体（中央寄せレイアウト） -->
            <div class="modal-body text-center py-4">
                <!-- PHP側でセットされた成功メッセージを安全に表示（XSS対策） -->
                <p class="fs-5 mb-0"><?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            
            <!-- モダールフッター -->
            <div class="modal-footer">
                <!-- アクションモード（処理種別）に応じたOKボタン挙動の分岐 -->
                <?php if ($action_mode === 'forgot_sent'): ?>
                    <!-- パスワード再設定メール送信後：単にモダールを閉じる -->
                    <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">OK</button>
                <?php else: ?>
                    <!-- その他の処理完了時：クエリパラメータを除去した現在URLへリロード（POSTの二重送信防止・画面更新） -->
                    <button type="button" class="btn btn-primary btn-sm" onclick="location.href='<?php echo strtok($_SERVER['REQUEST_URI'], '?'); ?>';">OK</button>
                <?php endif; ?>
            </div> 
        </div> 
    </div> 
</div>

<!-- ==========================================================
 * 8. スクリプト・イベント制御エリア
 * ========================================================== -->
<script>
// ======================================================
// 1. パスワード入力欄のマスク解除（文字表示/非表示切替）機能
// ======================================================
// ページ内のすべてのパスワード切り替えボタン（.toggle-password）を取得してイベントを設定
document.querySelectorAll('.toggle-password').forEach(button => {
    button.addEventListener('click', function () {
        // data-target 属性から操作対象の input 要素 ID を取得
        const targetId = this.getAttribute('data-target');
        const passwordInput = document.getElementById(targetId);
        
        // 対象の入力欄が存在する場合のみ表示タイプとアイコンを切替
        if (passwordInput) {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            // 伏字(password)時は 🫣 、テキスト表示(text)時は 🧐 に変更
            this.textContent = type === 'password' ? '🫣' : '🧐';
        }
    });
});

// ======================================================
// DOM構造の読み込み完了後に実行する安全な初期化ブロック
// ======================================================
document.addEventListener('DOMContentLoaded', () => {

    // ------------------------------------------------------
    // 2. モーダル間のシームレスな切り替え制御機能
    // ------------------------------------------------------
// 「パスワードをお忘れの方はこちら」のトリガーリンク要素を取得
const openForgotLink = document.getElementById('openForgotPassword');

// 画面上に該当リンクが存在する場合のみ処理を実行（Nullエラー防止）
if (openForgotLink) {
    openForgotLink.addEventListener('click', function(e) {
        // リンククリック時のデフォルト動作（ページトップへのスクロール等）を防止
        e.preventDefault();
        
        // ログインモーダルのHTML要素を取得
        const loginModalEl = document.getElementById('loginModal');
        // 既存のBootstrapモーダルインスタンスを取得
        const loginModal = bootstrap.Modal.getInstance(loginModalEl);
        
        // モーダルインスタンスが存在する場合、閉じるアニメーションを開始
        if (loginModal) {
            loginModal.hide();
        }

            // ログインモーダルが完全消滅した後にパスワード再設定モーダルを表示（1回限り実行）
            loginModalEl.addEventListener('hidden.bs.modal', function () {
                // パスワード再設定モーダルのHTML要素を取得
                const forgotModalEl = document.getElementById('forgotPasswordModal');
                // モーダルインスタンスを取得（存在しない場合は自動生成）
                const forgotModal = bootstrap.Modal.getOrCreateInstance(forgotModalEl);
                // パスワード再設定モーダルを表示
                forgotModal.show();
            }, { once: true });
        });
    }

    // ------------------------------------------------------
    // 3. モーダル全閉じ時の画面フリーズ防止クリーンアップ処理
    // ------------------------------------------------------
    document.querySelectorAll('.modal').forEach(modalEl => {
        modalEl.addEventListener('hidden.bs.modal', function () {
            // 画面上に「表示中（.show）」のモーダルが1つも残っていないか確認
            if (document.querySelectorAll('.modal.show').length === 0) {
                // 消えずに残った背景の暗幕（.modal-backdrop）をクリア
                document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
                // body要素のモーダル用クラス・スクロール制限CSSを初期化
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }
        });
    });

}); 
</script>
<!-- ==========================================================
 * PHPの処理結果（$action_mode, $error, $success_message）に応じて
 * ページロード時に特定のモーダルをJavaScriptで自動オープンさせる制御
 * ========================================================== -->

<?php if (!empty($error) && $action_mode === 'login'): ?>
    <!-- ログインエラー時：ログインモーダルを自動再表示 -->
    <script>window.addEventListener('DOMContentLoaded', () => { new bootstrap.Modal(document.getElementById('loginModal')).show(); });</script>
<?php endif; ?>

<?php if (!empty($error) && $action_mode === 'register'): ?>
    <!-- 会員登録・更新エラー時：会員情報登録・編集モーダルを自動再表示 -->
    <script>window.addEventListener('DOMContentLoaded', () => { new bootstrap.Modal(document.getElementById('customerModal')).show(); });</script>
<?php endif; ?>

<?php if (!empty($error) && $action_mode === 'forgot_password'): ?>
    <!-- パスワード再設定申請エラー時：メールアドレス入力モーダルを自動再表示 -->
    <script>window.addEventListener('DOMContentLoaded', () => { new bootstrap.Modal(document.getElementById('forgotPasswordModal')).show(); });</script>
<?php endif; ?>

<?php if (!empty($error) && $action_mode === 'input_token_modal'): ?>
    <!-- 確認コード（トークン）検証エラー時：コード入力モーダルを自動再表示 -->
    <script>window.addEventListener('DOMContentLoaded', () => { new bootstrap.Modal(document.getElementById('inputTokenModal')).show(); });</script>
<?php endif; ?>

<?php if (!empty($error) && $action_mode === 'reset_password_modal'): ?>
    <!-- パスワード更新エラー時：新パスワード入力モーダルを自動再表示 -->
    <script>window.addEventListener('DOMContentLoaded', () => { new bootstrap.Modal(document.getElementById('resetPasswordModal')).show(); });</script>
<?php endif; ?>

<?php if (!empty($success_message)): ?>
    <!-- 処理成功時：成功メッセージ用モーダルを表示し、状況に応じて連続（チェーン）表示を制御 -->
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            // 成功メッセージモーダルの生成と表示
            const successModalEl = document.getElementById('successMessageModal');
            const successModal = new bootstrap.Modal(successModalEl);
            successModal.show();

            <?php if ($action_mode === 'forgot_sent'): ?>
            // パスワード再設定メール送信成功時：成功モーダルが完全消滅（閉じる）した後にトークン入力モーダルを自動表示
            // ※ { once: true } により、1回限りのイベント実行（次回以降の重複発火を防止）
            successModalEl.addEventListener('hidden.bs.modal', function () {
                const tokenModal = new bootstrap.Modal(document.getElementById('inputTokenModal'));
                tokenModal.show();
            }, { once: true });
            <?php endif; ?>
        });
    </script>
<?php endif; ?>

<?php if ($action_mode === 'reset_password_modal' && empty($error)): ?>
<!-- トークン認証成功時：そのまま新パスワード設定モーダルを開く -->
<script>
    // DOMの読み込み完了後にモーダルを自動起動
    window.addEventListener('DOMContentLoaded', () => {
        new bootstrap.Modal(document.getElementById('resetPasswordModal')).show();
    });
</script>
<?php endif; ?>

<?php if (isset($_GET['logged_out']) && $_GET['logged_out'] === '1' && empty($error)): ?>
    <script>
    window.addEventListener('DOMContentLoaded', () => {
        // 1. ログアウト完了モーダルを表示[cite: 1]
        const logoutModal = new bootstrap.Modal(document.getElementById('logoutMessageModal'));
        logoutModal.show();

        // 2. モーダル表示後にURLから "?logged_out=1" を消去（アドレスバーのみ更新）
        const url = new URL(window.location.href);
        url.searchParams.delete('logged_out');
        window.history.replaceState(null, '', url.pathname + url.search);
    });
    </script>
<?php endif; ?>