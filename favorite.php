<?php
// ==================================================
// セッションの開始
// ==================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==================================================
// データベース接続用の共通設定
// ==================================================

require_once __DIR__ . '/db_config.php';

// ==================================================
// DB接続
// ==================================================
try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

} catch (PDOException $e) {
    exit('データベース接続エラーが発生しました。');
}


// ==================================================
// ① お気に入りへの追加・復活処理
// ==================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_id'])) {

    // ログイン確認
    if (!isset($_SESSION['customer'])) {
        exit('お気に入りに追加するにはログインしてください。');
    }

    // 商品IDを取得
    $goods_id = filter_input(
        INPUT_POST,
        'add_id',
        FILTER_VALIDATE_INT
    );

    if ($goods_id) {

        try {

            // ==================================================
            // すでにお気に入りテーブルにレコードがあるか確認
            // ==================================================
            $checkSql = "
                SELECT COUNT(*)
                FROM favorite
                WHERE user_id = :user_id
                AND goods_id = :goods_id
            ";

            $checkStmt = $pdo->prepare($checkSql);

            $checkStmt->execute([
                ':user_id' => $_SESSION['customer']['id'],
                ':goods_id' => $goods_id
            ]);

            $exists = $checkStmt->fetchColumn();


            // ==================================================
            // すでにレコードが存在する場合
            // deleteflg = 0 に戻す
            // ==================================================
            if ($exists > 0) {

                $updateSql = "
                    UPDATE favorite
                    SET deleteflg = 0
                    WHERE user_id = :user_id
                    AND goods_id = :goods_id
                ";

                $updateStmt = $pdo->prepare($updateSql);

                $updateStmt->execute([
                    ':user_id' => $_SESSION['customer']['id'],
                    ':goods_id' => $goods_id
                ]);


            // ==================================================
            // レコードが存在しない場合
            // 新しく登録する
            // ==================================================
            } else {

                $insertSql = "
                    INSERT INTO favorite
                    (
                        user_id,
                        goods_id,
                        created_at,
                        deleteflg
                    )
                    VALUES
                    (
                        :user_id,
                        :goods_id,
                        NOW(),
                        0
                    )
                ";

                $insertStmt = $pdo->prepare($insertSql);

                $insertStmt->execute([
                    ':user_id' => $_SESSION['customer']['id'],
                    ':goods_id' => $goods_id
                ]);
            }


            // お気に入り一覧へ移動
            header('Location: favorite.php');
            exit;


        } catch (PDOException $e) {

            exit(
                'データベースエラーが発生しました：'
                . $e->getMessage()
            );
        }
    }
}


// ==================================================
// ② お気に入りから外す処理
// ==================================================
// DELETEは使用せず、deleteflgを1に変更する
// ==================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {

    // ログイン確認
    if (!isset($_SESSION['customer'])) {
        exit('ログインしてください。');
    }

    // 商品IDを取得
    $delete_id = filter_input(
        INPUT_POST,
        'delete_id',
        FILTER_VALIDATE_INT
    );

    if ($delete_id) {

        try {

            // ==================================================
            // DELETEではなくUPDATEを使用
            // deleteflg = 1 にする
            // ==================================================
            $updateSql = "
                UPDATE favorite
                SET deleteflg = 1
                WHERE user_id = :user_id
                AND goods_id = :goods_id
            ";

            $updateStmt = $pdo->prepare($updateSql);

            $updateStmt->execute([
                ':user_id' => $_SESSION['customer']['id'],
                ':goods_id' => $delete_id
            ]);


            // お気に入り一覧へ戻る
            header('Location: favorite.php');
            exit;


        } catch (PDOException $e) {

            exit(
                'データベースエラーが発生しました：'
                . $e->getMessage()
            );
        }
    }
}

?>
<!DOCTYPE html>
<html lang="ja">

<head>

    <!-- FontAwesome -->
    <link
        rel="stylesheet"
        href="https://cloudflare.com"
    >

    <!-- 共通CSS -->
    <link rel="stylesheet" href="css/main.css">

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>お気に入り一覧</title>


    <style>

        /* ==================================================
           お気に入り一覧全体
        ================================================== */

        .favorite-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }


        /* ==================================================
           タイトル
        ================================================== */

        .favorite-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;

            display: flex;
            align-items: center;
            gap: 10px;
        }


        /* ==================================================
           説明
        ================================================== */

        .favorite-desc {
            color: #666;
            font-size: 14px;
            margin-bottom: 30px;
        }


        /* ==================================================
           削除ボタンのフォーム
        ================================================== */

        .delete-form {
            position: absolute;
            top: 10px;
            right: 15px;
            z-index: 10;
        }


        /* ==================================================
           ×ボタン
        ================================================== */

        .delete-btn {
            background: none;
            color: #ccc;
            border: none;

            width: 24px;
            height: 24px;

            cursor: pointer;

            font-size: 20px;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 0;

            transition: color 0.2s;
        }


        /* ==================================================
           ×ボタン ホバー
        ================================================== */

        .delete-btn:hover {
            color: #333;
        }


        /* ==================================================
           タイトルアイコン
        ================================================== */

        .favorite-title i {
            color: #005B50;
            background-color: #ffffff;
            border-radius: 50%;
        }

    </style>

</head>


<body>


    <!-- ==================================================
         ヘッダー
    ================================================== -->

    <?php require 'header.php'; ?>


    <!-- ==================================================
         お気に入り一覧
    ================================================== -->

    <div class="favorite-container">


        <!-- ==================================================
             タイトル
        ================================================== -->

        <div class="favorite-title">

            <i class="fa-regular fa-heart"></i>

            お気に入り一覧

        </div>


        <!-- ==================================================
             説明
        ================================================== -->

        <p class="favorite-desc">

            お気に入りに登録した車両の一覧です。
            気になる車両を比較・検討できます。

        </p>


        <!-- ==================================================
             車両一覧
        ================================================== -->

        <div class="car-list">

            <?php

            // ==================================================
            // ログインしている場合
            // ==================================================

            if (isset($_SESSION['customer'])) {

                try {

                    // ==================================================
                    // お気に入り車両を取得
                    // deleteflg = 0 の車両だけ表示
                    // ==================================================

                    $sql = $pdo->prepare(
                        'SELECT g.*
                         FROM favorite f
                         INNER JOIN goods g
                             ON f.goods_id = g.goods_id
                         WHERE f.user_id = ?
                         AND f.deleteflg = 0'
                    );


                    // SQL実行

                    $sql->execute([
                        $_SESSION['customer']['id']
                    ]);


                    // 結果取得

                    $favorite_products = $sql->fetchAll();


                    // ==================================================
                    // お気に入りが0件の場合
                    // ==================================================

                    if (count($favorite_products) === 0) {

                        echo '
                            <p style="
                                grid-column: 1 / -1;
                                text-align: center;
                                color: #666;
                                padding: 40px 0;
                            ">
                                お気に入りに登録された車両はありません。
                            </p>
                        ';


                    } else {


                        // ==================================================
                        // お気に入り車両を1台ずつ表示
                        // ==================================================

                        foreach ($favorite_products as $row) {

                            $id = $row['goods_id'];

                            ?>


                            <!-- ==================================================
                                 車両カード
                            ================================================== -->

                            <figure style="position: relative;">


                                <!-- ==================================================
                                     車両詳細ページへのリンク
                                ================================================== -->

                                <a
                                    href="product_detail.php?id=<?= (int)$id ?>"
                                    style="
                                        text-decoration: none;
                                        color: inherit;
                                        display: block;
                                    "
                                >


                                    <!-- ==================================================
                                         車両画像
                                    ================================================== -->

                                    <img
                                        src="<?= htmlspecialchars(
                                            $row['carphoto_main'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                        alt="<?= htmlspecialchars(
                                            $row['car_name'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                        onerror="
                                            this.onerror=null;
                                            this.src='images/no-image.jpg';
                                        "
                                    >


                                    <!-- ==================================================
                                         車両情報
                                    ================================================== -->

                                    <figcaption>


                                        <!-- 車名 -->

                                        <div
                                            class="car-name"
                                            style="margin-bottom: 8px;"
                                        >

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


                                        <!-- ==================================================
                                             車両スペック
                                        ================================================== -->

                                        <div
                                            class="specs-grid"
                                            style="
                                                display: flex;
                                                gap: 10px;
                                                font-size: 12px;
                                                color: #666;
                                                margin-bottom: 8px;
                                                font-weight: normal;
                                            "
                                        >

                                            <span>
                                                <?= htmlspecialchars(
                                                    $row['model_year'] ?? '',
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>年式
                                            </span>


                                            <span>
                                                走行距離
                                                <?= number_format(
                                                    $row['milage'] ?? 0
                                                ) ?>km
                                            </span>


                                            <span>
                                                車検
                                                <?= htmlspecialchars(
                                                    $row['inspection_date'] ?? '',
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            </span>

                                        </div>


                                        <!-- ==================================================
                                             価格
                                        ================================================== -->

                                        <div
                                            class="price-box"
                                            style="
                                                font-size: 18px;
                                                font-weight: bold;
                                                color: #d32f2f;
                                            "
                                        >

                                            <?= number_format(
                                                $row['price'] ?? 0
                                            ) ?>円

                                            <span
                                                style="
                                                    font-size: 12px;
                                                    color: #222;
                                                    font-weight: normal;
                                                    margin-left: 4px;
                                                "
                                            >
                                                税込
                                            </span>

                                        </div>


                                    </figcaption>


                                </a>


                                <!-- ==================================================
                                     お気に入りから外す
                                     DELETEではなくUPDATE
                                ================================================== -->

                                <form
                                    action="favorite.php"
                                    method="POST"
                                    class="delete-form"
                                >

                                    <input
                                        type="hidden"
                                        name="delete_id"
                                        value="<?= (int)$id ?>"
                                    >


                                    <button
                                        type="submit"
                                        class="delete-btn"
                                        title="お気に入りから削除"
                                        onclick="
                                            return confirm(
                                                '本当にお気に入りから削除しますか？'
                                            );
                                        "
                                    >

                                        <i class="fa-solid fa-xmark"></i>

                                    </button>

                                </form>


                            </figure>


                            <?php

                        }

                    }


                } catch (PDOException $e) {

                    echo '
                        <p style="
                            grid-column: 1 / -1;
                            text-align: center;
                            color: red;
                        ">
                            データの取得に失敗しました。
                        </p>
                    ';

                }


            } else {


                // ==================================================
                // ログインしていない場合
                // ==================================================

                echo '
                    <p style="
                        grid-column: 1 / -1;
                        text-align: center;
                        color: #666;
                        padding: 40px 0;
                    ">
                        お気に入りを表示するには、ログインしてください。
                    </p>
                ';

            }

            ?>

        </div>

    </div>


    <!-- ==================================================
         フッター
    ================================================== -->

    <?php require 'footer.php'; ?>


</body>

</html>