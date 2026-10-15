<?php 
// ログイン処理・セッション管理および各種モーダル定義ファイルを読み込み
require_once 'Modallogin.php'; 
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live motors</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons (ナビゲーション・ボタンのアイコン表示用) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- カスタムCSS -->
    <link rel="stylesheet" href="css/style.css">
    <!-- 統合レスポンシブCSSの読み込み -->
    <link rel="stylesheet" href="css/style-responsive.css">
    
    <style>
        /* ヘッダー全体背景（薄いミントグリーン） */
        .site-header {
            background-color: #eaf2ed;
            padding: 12px 24px;
            font-family: sans-serif;
            width: 100%;
        }

        /* ヘッダーインナー（コンテンツ幅の統一と左右レイアウト） */
        .header-inner {
            width: 100%;
            max-width: 1160px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between; /* ロゴとアクションボタンを左右両端に配置 */
            align-items: center;
        }

        /* ロゴ表示エリア */
        .logo-area {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #0f3d2e;
        }

        /* ロゴ画像（サイズおよび右側テキストとの間隔） */
        .logo-img {
            height: 40px;          /* ロゴ画像の高さを40pxに統一 */
            margin-right: 12px;    /* 右側のテキストとの間に余白を設定 */
        }

        /* ロゴテキスト全体のコンテナ（ブランド名とサブタイトルを縦並びに配置） */
        .logo-text {
            display: flex;
            flex-direction: column; /* 要素を縦方向（上下）に配置 */
        }

        /* ブランド名（Live motors）のスタイリング */
        .brand-name {
            font-size: 1.5rem;     /* 文字サイズを大きめに設定 */
            font-weight: bold;      /* 太字で強調 */
            line-height: 1.1;       /* 行高を極力詰めて下部サブタイトルとの一体感を出す */
        }

        /* サブタイトル（キャッチコピー）のスタイリング */
        .sub-title {
            font-size: 0.75rem;    /* ブランド名より小さめの文字サイズ */
            color: #198754;        /* テーマに合わせたダークグリーンカラー */
            font-weight: 500;       /* 中厚手（ミディアム）の太さで視認性を確保 */
        }

        /* 右側アクションエリア */
        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px; /* 各ボタン間の余白 */
        }

        /* 白背景のカード風ボタン（基本スタイル） */
        .header-btn {
            display: inline-flex;      /* 内部のアイコンとテキストをインラインFlexで横並び配置 */
            align-items: center;       /* 垂直方向（上下）の中央に配置 */
            gap: 6px;                  /* アイコンとテキストの間隔を6pxに設定 */
            background-color: #ffffff; /* ボタンの背景色（白） */
            border: 1px solid #e0e0e0; /* 境界線（薄いグレー） */
            border-radius: 8px;        /* ボタンの角を8px丸める */
            padding: 8px 16px;         /* ボタン内側の余白（上下8px、左右16px） */
            color: #111111;            /* 文字色（濃いグレー） */
            font-size: 0.9rem;         /* 文字サイズ */
            font-weight: bold;         /* 文字を太字に指定 */
            text-decoration: none;     /* aタグ使用時の下線を削除 */
            cursor: pointer;           /* マウスカーソルを指マークに変更 */
            box-shadow: 0 1px 3px rgba(0,0,0,0.05); /* 控えめなドロップシャドウで立体感を付与 */
            transition: background-color 0.2s, box-shadow 0.2s; /* ホバー時のアニメーション時間を0.2秒に設定 */
        }

        /* マウスホバー時のボタン状態 */
        .header-btn:hover {
            background-color: #f8f9fa; /* ホバー時に背景を明るいグレーに変化 */
            color: #000000;            /* ホバー時の文字色 */
            box-shadow: 0 2px 5px rgba(0,0,0,0.1); /* 影を強くして浮かび上がる効果を強調 */
        }

        /* ボタン内部のアイコン（<i>タグ） */
        .header-btn i {
            font-size: 1.1rem;         /* アイコンサイズをテキストより少し大きめに調整 */
            color: #111111;            /* アイコンの色をテキストカラーと同色に指定 */
        }

        /* 縦区切り線 */
        .divider {
            width: 1px;
            height: 28px;
            background-color: #c5d6cc;
            margin: 0 8px;
        }

        /* ユーザーあいさつ表示エリア */
        .user-greeting {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #111111;
            font-size: 0.9rem;
        }

        .user-greeting i {
            font-size: 1.2rem;
        }
    </style>
</head>

<!-- 共通サイトヘッダー -->
<header class="site-header">
    <div class="header-inner">
        <!-- サイトロゴ・ブランディングエリア（トップ画面へ遷移） -->
        <a href="index.php" class="logo-area">
            <img src="img/logo.png" alt="Live motors" class="logo-img">
            <div class="logo-text">
            </div>
        </a>

        <!-- ナビゲーション・アクションボタンエリア -->
        <div class="header-actions">
            <!-- お気に入りページリンク -->
            <a href="favorite.php" class="header-btn">
                <i class="bi bi-heart"></i> お気に入り
            </a>

            <!-- マイページリンク -->
            <?php if (isset($_SESSION['customer'])): ?>
                <!-- ログイン時：通常通りマイページへ遷移 -->
                 <a href="mypage.php" class="header-btn">
                    <i class="bi bi-person"></i>マイページ
                 </a>
            <?php else: ?>
                <!-- 未ログイン時：新規会員登録モーダルを起動 -->
                 <button type="button" class="header-btn" data-bs-toggle="modal" data-bs-target="#customerModal">
                    <i class="bi bi-person"></i>マイページ
                 </button>
            <?php endif; ?>

            <!-- ログイン状態に応じたボタン分岐 -->
            <?php if (isset($_SESSION['customer'])): ?>
                <!-- ログイン時：ログアウト処理用ボタン -->
                <a href="?logout=1" class="header-btn">
                    <i class="bi bi-box-arrow-right"></i> ログアウト
                </a>
            <?php else: ?>
                <!-- 未ログイン時：ログインモーダル起動ボタン -->
                <button type="button" class="header-btn" data-bs-toggle="modal" data-bs-target="#loginModal">
                    <i class="bi bi-box-arrow-in-right"></i> ログイン
                </button>
            <?php endif; ?>

            <!-- 縦区切り線 -->
            <div class="divider"></div>

            <!-- ユーザーあいさつエリア（セッションから名前を取得して表示／XSS対策済み） -->
            <div class="user-greeting">
                <!-- Bootstrap Icons のユーザーアイコン -->
                <i class="bi bi-person"></i>
                <!-- 三項演算子でログイン状態に応じた名前を動的に出力 -->
                <span>こんにちは <strong><?php echo isset($_SESSION['customer']) ? htmlspecialchars($_SESSION['customer']['last_name'], ENT_QUOTES, 'UTF-8') : 'ゲスト'; ?></strong>さん</span>
            </div>
        </div>
    </div>
</header>

<!-- Bootstrap 5 JavaScript（モーダル等の動的制御用） -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

