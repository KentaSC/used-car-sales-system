<!-- ヘッダー読み込み -->
<?php require 'header.php'; ?>

<?php
// ==================================================
// データベース接続設定
// ==================================================

require_once __DIR__ . '/db_config.php';

try {

    // PDOを使ってMySQLへ接続する
    //
    // charset=utf8mb4
    // → 日本語や絵文字などを扱える文字コード
    //
    // PDO::ATTR_ERRMODE
    // → DBエラーが発生した場合、例外を発生させる
    //
    // PDO::ATTR_DEFAULT_FETCH_MODE
    // → SELECT結果を連想配列で取得する
    //
    // PDO::ATTR_EMULATE_PREPARES
    // → falseにすることで、本物のプリペアドステートメントを使用する
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

    // DB接続に失敗した場合は処理を終了する
    //
    // 本番環境ではDBエラーの詳細を画面に出さないのが重要
    exit('データベースに接続できませんでした。');

}

// ==================================================
// URLから商品IDを取得
// ==================================================
//
// 例：URLがproduct.php?id=1 だった場合、$productId = 1;になる
// product.php?id=1
// ↓
// $productId = 1 を格納
//

$productId = filter_input(
    INPUT_GET,           //URLのIDの？以降の部分を取得
    'id',                //URLのIDの数字を取得
    FILTER_VALIDATE_INT  //取得した値が整数として正しいか確認する
);

// IDが正しい整数として取得できなかった場合
if (!$productId) {

    exit('商品IDが指定されていません。');

}

// ==================================================
// 商品情報をDBから取得
// ==================================================

// goodsテーブルから検索
// URLで指定されたgoods_idの商品を1件取得する
$sql = "
    SELECT * FROM goods
    WHERE goods_id = :id
    LIMIT 1
";

// SQLをプリペアドステートメントとして準備する
$stmt = $pdo->prepare($sql);

// :idに$productIdを入れてSQLを実行する
$stmt->execute([
    ':id' => $productId
]);

// DBから取得した1件の商品情報を配列として取得
//
// 例：
// [
//     'goods_id' => 1,
//     'maker' => 'トヨタ',
//     'car_name' => 'アクア',
//     ...
// ]
//
$product = $stmt->fetch();


// 商品が存在しなかった場合
if (!$product) {

    exit('商品が見つかりませんでした。');

}

// ==================================================
// HTMLエスケープ用の関数
// ==================================================
//
// DBの値をHTMLに表示するときに使用する。
// < > " ' & などを安全な文字列へ変換する。
//
// 例えばDBに
// <script>alert('XSS')</script>
// が入っていたとしても、そのままHTMLとして実行されないようにする。
//

function h($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}


// ==================================================
// 商品画像を取得
// ==================================================

// 3つの画像のカラム名を指定

$images = [];

$imageColumns = [
    'carphoto_main',
    'carphoto_back',
    'carphoto_panel',
    'carphoto_rear',
    'carphoto_navi'

];

// foreachで順番に画像のURLを追加する

foreach($imageColumns as $column){

    if (
        isset($product[$column]) &&
        !empty($product[$column])
    ) {

        // 画像URLを配列へ追加
        $images[] = $product[$column];

    }
}

// ==================================================
// 装備・特徴の設定
// ==================================================
//
// DBのカラム名と画面に表示する名前・アイコンを対応させる
// 配列に装備特徴を追加する
//
// DB側：car_navi = 1
// ↓
// 画面：ナビ + ナビアイコン
//

$features = [

    'car_navi' => [
        'name' => 'ナビ',
        'icon' => '<i class="fa-solid fa-route"></i>'
    ],

    'backcamera' => [
        'name' => 'バックカメラ',
        'icon' => '<i class="fa-solid fa-car-rear"></i>'
    ],

    'etc' => [
        'name' => 'ETC',
        'icon' => '<i class="fa-solid fa-road"></i>'
    ],

    'dashcam' => [
        'name' => 'ドライブレコーダー',
        'icon' => '<i class="fa-solid fa-video"></i>'
    ],

    'smartkey' => [
        'name' => 'スマートキー',
        'icon' => '<i class="fa-solid fa-key"></i>'
    ],

    'cruise_control' => [
        'name' => 'クルーズコントロール',
        'icon' => '<i class="fa-solid fa-gauge-high"></i>'
    ],

    'omnidirectional_camera' => [
        'name' => '全方位カメラ',
        'icon' => '<i class="fa-solid fa-camera-rotate"></i>'
    ],

    'led_headlights' => [
        'name' => 'LEDヘッドライト',
        'icon' => '<i class="fa-regular fa-lightbulb"></i>'
    ],

    'sliding_door' => [
        'name' => '両側電動スライドドア',
        'icon' => '<i class="fa-solid fa-car-side"></i>'
    ],

    'no_smoking' => [
        'name' => '禁煙車',
        'icon' => '<i class="fa-solid fa-ban-smoking"></i>'
    ]

];

?>

<!-- 以下HTML -->

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <!-- スマートフォンでも画面幅に合わせる -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品詳細</title>

    <!-- Font Awesome用 -->
    <link rel="stylesheet"href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        background: #ffffff;
        color: #222;
        font-family:
            -apple-system,
            BlinkMacSystemFont,
            "Noto Sans JP",
            "Yu Gothic",
            YuGothic,
            Meiryo,
            sans-serif;
    }

    main {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    /*
    ==================================================
    商品タイトル
    ==================================================
    */

    main > section:first-child {
        margin-bottom: 16px;
    }

    main > section:first-child h2 {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0 0 6px;
        font-size: 28px;
        font-weight: 700;
        line-height: 1.3;
    }

    main > section:first-child h2 span {
        display: inline-block;
        padding: 4px 9px;
        background: #004831;
        color: #fff;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }

    main > section:first-child p {
        margin: 0;
        color: #333;
        font-size: 14px;
        line-height: 1.6;
    }

    /*
    ==================================================
    商品メインエリア
    ==================================================

    左：画像
    右：商品情報

    PCでは2列、
    スマホでは1列になる。
    */

    main > section:nth-of-type(2) {
        display: grid;
        grid-template-columns:
            minmax(0, 1.35fr)
            minmax(360px, 1fr);
        gap: 24px;
        align-items: start;
    }

    /*
    ==================================================
    左側：画像
    ==================================================
    */

    main > section:nth-of-type(2) > div:first-child {
        min-width: 0;
    }

    /*
    ==================================================
    メイン画像 + 左右ボタン
    ==================================================
    */

    .main-image-wrapper {
        position: relative;
        width: 100%;
        aspect-ratio: 4 / 3;
        overflow: hidden;
        border-radius: 10px;
        background: #f2f2f2;
    }

    /*
    大きい画像
    */

    .main-image-wrapper > img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /*
    ==================================================
    左右のボタン
    ==================================================
    */

    .image-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        border-radius: 50%;
        background: rgba(0, 0, 0, 0.55);
        color: #fff;
        font-size: 18px;
        cursor: pointer;
        z-index: 5;
        transition: 0.2s;
    }

    /*
    マウスを乗せたとき
    */

    .image-nav:hover {
        background: rgba(0, 0, 0, 0.8);
    }

    /*
    左ボタン
    */

    .image-nav.prev {
        left: 12px;
    }

    /*
    右ボタン
    */

    .image-nav.next {
        right: 12px;
    }

    /*
    ==================================================
    サムネイル
    ==================================================
    */

    main > section:nth-of-type(2)
    > div:first-child
    > div:nth-child(2) {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 8px;
        margin-top: 12px;
    }

    .thumbnail {
        height: 80px;
        cursor: pointer;
        border: 2px solid transparent;
        border-radius: 6px;
        overflow: hidden;
        transition: 0.2s;
    }

    .thumbnail:hover {
        opacity: 0.75;
    }

    /*
    現在表示している画像
    */

    .thumbnail.active {
        border-color: #004831;
    }

    /*
    マウスを乗せたとき少し透明にする
    */

    main > section:nth-of-type(2)
    > div:first-child
    > div:nth-child(2)
    > div:hover {
        opacity: 0.75;
    }

    /*
    サムネイル画像
    */

    main > section:nth-of-type(2)
    > div:first-child
    > div:nth-child(2)
    img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /*
    ==================================================
    右側：商品情報
    ==================================================
    */

    main > section:nth-of-type(2)
    > div:nth-child(2) {
        min-width: 0;
    }

    /*
    ==================================================
    価格エリア
    ==================================================
    */

    main > section:nth-of-type(2)
    > div:nth-child(2)
    > section:first-child {
        display: grid;
        grid-template-columns: 1fr 1fr;
        margin-bottom: 16px;
        border: 1px solid #ddd;
        border-radius: 10px;
        background: #fff;
        overflow: hidden;
    }

    /*
    車両価格・支払総額の各エリア
    */

    main > section:nth-of-type(2)
    > div:nth-child(2)
    > section:first-child
    > div {
        padding: 16px 18px;
    }

    /*
    2つ目の価格エリアの左側に境界線を表示
    */

    main > section:nth-of-type(2)
    > div:nth-child(2)
    > section:first-child
    > div + div {
        border-left: 1px solid #ddd;
    }

    /*
    価格タイトル
    */

    main > section:nth-of-type(2)
    > div:nth-child(2)
    > section:first-child h4 {
        margin: 0 0 5px;
        font-size: 13px;
        font-weight: 600;
        color: #333;
    }

    /*
    金額
    */

    main > section:nth-of-type(2)
    > div:nth-child(2)
    > section:first-child strong {
        display: flex;
        align-items: baseline;
        gap: 3px;
        font-size: 32px;
        line-height: 1;
        color: #111;
    }

    /*
    車両価格だけ赤色
    */

    main > section:nth-of-type(2)
    > div:nth-child(2)
    > section:first-child
    > div:first-child
    strong {
        color: #e60012;
    }

    /*
    「円」の文字
    */

    main > section:nth-of-type(2)
    > div:nth-child(2)
    > section:first-child
    strong span {

        font-size: 14px;
        font-weight: 700;
    }

    /*
    ==================================================
    車両情報テーブル
    ==================================================
    */

    main > section:nth-of-type(2)
    > div:nth-child(2)
    > section:nth-child(2) {

        margin-bottom: 16px;
    }

    main > section:nth-of-type(2) table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
        font-size: 13px;
    }

    /*
    テーブルのセル
    */

    main > section:nth-of-type(2)
    > div:nth-child(2)
    > section:nth-child(2)
    th,
    main > section:nth-of-type(2)
    > div:nth-child(2)
    > section:nth-child(2)
    td {
        padding: 10px 12px;
        border-bottom: 1px solid #e5e5e5;
    }

    /*
    最後の行だけ下線を消す
    */

    main > section:nth-of-type(2)
    table tr:last-child th,
    main > section:nth-of-type(2)
    table tr:last-child td {
        border-bottom: none;
    }

    /*
    左側の項目名
    */

    main > section:nth-of-type(2)
    table th {
        width: 18%;
        background: #f7f7f7;
        font-weight: 600;
        text-align: left;
        white-space: nowrap;
    }

    /*
    データ部分
    */

    main > section:nth-of-type(2)
    table td {
        width: 32%;
        background: #fff;
        color: #333;
    }

    /*
    ==================================================
    装備・特徴
    ==================================================
    */

    main > section:nth-of-type(2)
    > div:nth-child(2)
    > section:nth-child(3) {
        margin-bottom: 16px;
    }

    main > section:nth-of-type(2)
    > div:nth-child(2)
    > section:nth-child(3)
    h4 {
        margin: 0 0 10px;
        font-size: 15px;
        font-weight: 700;
    }

    /*
    装備を5列で表示
    */

    main > section:nth-of-type(2)
    > div:nth-child(2)
    > section:nth-child(3)
    > div {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 6px;
    }

    /*
    装備1個
    */

    main > section:nth-of-type(2)
    > div:nth-child(2)
    > section:nth-child(3)
    > div > div {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 65px;
        padding: 8px 3px;
        border: 1px solid #e2e2e2;
        border-radius: 6px;
        background: #fff;
        text-align: center;
    }

    /*
    装備アイコン
    */

    main > section:nth-of-type(2)
    > div:nth-child(2)
    > section:nth-child(3)
    > div > div
    > span:first-child {
        margin-bottom: 5px;
        color: #333;
        font-size: 17px;
    }

    /*
    装備名
    */

    main > section:nth-of-type(2)
    > div:nth-child(2)
    > section:nth-child(3)
    > div > div
    > span:last-child {
        font-size: 10px;
        line-height: 1.2;
    }


    /*
    ==================================================
    車両説明
    ==================================================
    */

    main > section:nth-of-type(2)
    > div:nth-child(2)
    > section:nth-child(4)
    h4 {
        margin: 0 0 8px;
        font-size: 15px;
    }


    main > section:nth-of-type(2)
    > div:nth-child(2)
    > section:nth-child(4)
    > div {
        padding: 14px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background: #fafafa;
        font-size: 13px;
        line-height: 1.7;
    }


    /*
    ==================================================
    下部ボタン
    ==================================================
    */

    main > section:nth-of-type(3) {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-top: 28px;
    }


    /*
    ボタン共通
    */

    main > section:nth-of-type(3) a,
    main > section:nth-of-type(3) form button {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        min-height: 52px;
        border-radius: 7px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        transition: 0.2s;
        width: 100%;
        cursor: pointer;
        box-sizing: border-box; /* 線の太さを高さに含める */
    }


    /*
    お気に入りボタン（左側）
    */

    main > section:nth-of-type(3) form button {
        border: 1px solid #004831;
        background: #fff;
        color: #004831;
    }


    /*
    お気に入りボタン：マウスを乗せたとき
    */

    main > section:nth-of-type(3) form button:hover {
        background: #f0f7f4;
    }


    /*
    問い合わせボタン（右側）
    */

    main > section:nth-of-type(3) a {
        border: 1px solid #004831;
        background: #004831;
        color: #fff;
    }


    /*
    問い合わせボタン：マウスを乗せたとき
    */

    main > section:nth-of-type(3) a:hover {
        background: #003a27;
    }


    /*
    ボタンのアイコン
    */

    main > section:nth-of-type(3) i {
        font-size: 18px;
    }

    /*
    お気に入りボタン：マウスを乗せたとき
    */

    main > section:nth-of-type(3)
    a:first-child:hover {
        background: #f0f7f4;
    }


    /*
    問い合わせボタン
    */

    main > section:nth-of-type(3)
    a:last-child {
        border: 1px solid #004831;
        background: #004831;
        color: #fff;
    }


    /*
    問い合わせボタン：マウスを乗せたとき
    */

    main > section:nth-of-type(3)
    a:last-child:hover {
        background: #003a27;
    }


    /*
    ボタンのアイコン
    */

    main > section:nth-of-type(3)
    a i {
        font-size: 18px;
    }


    /*
    ==================================================
    スマートフォン対応
    ==================================================
    */

    @media (max-width: 800px) {
        main {
            padding: 15px;
        }

        /*
        PCでは2列だった商品エリアを
        スマホでは1列にする
        */

        main > section:nth-of-type(2) {
            grid-template-columns: 1fr;
            gap: 20px;
        }


        /*
        タイトルを小さくする
        */

        main > section:first-child h2 {
            font-size: 22px;
        }


        /*
        価格を小さくする
        */

        main > section:nth-of-type(2)
        > div:nth-child(2)
        > section:first-child
        strong {
            font-size: 25px;
        }

    }


    /*
    ==================================================
    小さいスマートフォン対応
    ==================================================
    */

    @media (max-width: 500px) {
        main {
            padding: 10px;
        }


        /*
        タイトルをさらに小さくする
        */

        main > section:first-child h2 {
            font-size: 19px;
        }


        /*
        掲載中ラベル
        */

        main > section:first-child h2 span {
            font-size: 9px;
        }


        /*
        テーブル文字を小さくする
        */

        main > section:nth-of-type(2) table {
            font-size: 11px;
        }


        /*
        テーブルの余白を小さくする
        */

        main > section:nth-of-type(2)
        table th,
        main > section:nth-of-type(2)
        table td {
            padding: 8px 6px;
        }


        /*
        装備を3列にする
        */

        main > section:nth-of-type(2)
        > div:nth-child(2)
        > section:nth-child(3)
        > div {
            grid-template-columns:
                repeat(3, 1fr);
        }


        /*
        ボタンを縦並びにする
        */

        main > section:nth-of-type(3) {
            grid-template-columns: 1fr;
            gap: 10px;
            margin-top: 20px;
        }

    }

    </style>

</head>

<body>

<!-- ==================================================
     商品詳細ページ本体
================================================== -->

<main>

    <!-- ==================================================
         商品タイトル
    ================================================== -->

    <section>
        <h2>
            <?php

            // メーカー・車名・グレードを連結して表示
            //
            // h()を通しているので、
            // DBにHTMLやJavaScriptが入っていても
            // そのまま実行されない。
            //
            echo h(
                $product['maker'] . ' ' .
                $product['car_name'] . ' ' .
                $product['car_grade']
            );

            ?>
        </h2>

    </section>

    <!-- ==================================================
         商品メインエリア
    ================================================== -->

    <section>

        <!-- ==================================================
             左側：画像
        ================================================== -->
        <div>
            <?php if (!empty($images)): ?>
                <!--
                画像が存在する場合
                -->

                <!-- メイン画像 -->
            <div class="main-image-wrapper">

                <!-- 大きい画像 -->
                <img
                    id="mainImage"
                    src="<?= h($images[0]) ?>"
                    alt="<?= h($product['maker'] . ' ' . $product['car_name']) ?>"
                >

                <!-- 左ボタン -->
                <button
                    type="button"
                    id="prevButton"
                    class="image-nav prev"
                    aria-label="前の画像"
                >
                    <i class="fa-solid fa-chevron-left"></i>
                </button>

                <!-- 右ボタン -->
                <button
                    type="button"
                    id="nextButton"
                    class="image-nav next"
                    aria-label="次の画像"
                >
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>

                <!-- サムネイル -->
                <div id="thumbnailList">

    <?php foreach ($images as $index => $image): ?>

        <div class="thumbnail">
            <img
                src="<?= h($image) ?>"
                alt="<?= h($product['maker'] . ' ' . $product['car_name']) ?> <?= $index + 1 ?>"
            >
        </div>

    <?php endforeach; ?>

</div>
            <?php else: ?>

                <!--
                画像が1枚も存在しない場合
                -->

                <img
                    src="<?= h($product['carphoto_main']) ?>"
                    alt=""
                    style="width:100%;"
                >

            <?php endif; ?>

        </div>

        <!-- ==================================================
             右側：商品情報
        ================================================== -->

        <div>

            <!-- ==================================================
                 価格
            ================================================== -->

            <section>

                <!-- 車両価格 -->

                <div>
                    <h4>
                        車両価格
                    </h4>

                    <strong>

                        <?php

                        // DBのpriceを数値として扱い、
                        // 3桁ごとにカンマを付ける
                        //
                        // 例：
                        // 2500000
                        // ↓
                        // 2,500,000
                        //

                        echo number_format(
                            (float)$product['price']
                        );

                        ?>

                        <span>
                            円
                        </span>

                    </strong>

                </div>

                <!-- 支払総額 -->
                <div>

                    <h4>
                        支払総額
                    </h4>

                    <strong>

                        <?php

                        // price × 1.05=支払総額
                       
                        echo number_format(
                            (float)$product['price'] * 1.05
                        );

                        ?>

                        <span>
                            円
                        </span>

                    </strong>

                </div>

            </section>

            <!-- ==================================================
                 車両情報
            ================================================== -->

            <section>

                <table>

                    <tbody>
                        <!-- 一行目 -->
                        <tr>

                            <th>
                                年式
                            </th>

                            <td>
                                <?= h($product['model_year']) ?>年
                            </td>


                            <th>
                                ミッション
                            </th>

                            <td>
                                <?= h($product['transmission']) ?>
                            </td>

                        </tr>
                        <!-- 二行目 -->
                        <tr>

                            <th>
                                走行距離
                            </th>

                            <td>
                                <?= number_format((float)$product['milage']) ?>km
                            </td>

                            <th>
                                駆動方式
                            </th>

                            <td>
                                <?= h($product['drivertrain']) ?>
                            </td>

                        </tr>
                        <!-- 三行目 -->
                        <tr>

                            <th>
                                車検日
                            </th>

                            <td>
                                <?= date('Y年m月d日', strtotime($product['inspection_date'])) ?>
                            </td>

                            <th>
                                燃料
                            </th>

                            <td>
                                <?= h($product['fuel_type']) ?>
                            </td>

                        </tr>
                        <!-- 四行目 -->
                         <tr>

                            <th>
                                排気量
                            </th>

                            <td>
                                <?= number_format((float)$product['engine_cc']) ?>cc
                            </td>

                            <th>
                                カラー
                            </th>

                            <td>
                                <?= h($product['color']) ?>
                            </td>

                        </tr>
                    </tbody>

                </table>

            </section>

            <!-- ==================================================
                 装備・特徴
            ================================================== -->

            <section>

                <h4>
                    装備・特徴
                </h4>

                <div>
                    <!-- foreachで項目を一つずつ順番に表示 -->
                    <?php foreach ($features as $column => $feature): ?>

                        <?php

                        // DBの装備フラグが1の場合だけ表示する
                        //
                        // 例：
                        // car_navi = 1
                        // ↓
                        // ナビを表示
                        //
                        // car_navi = 0
                        // ↓
                        // 何も表示しない
                        //

                        if (
                            !isset($product[$column]) ||
                            (int)$product[$column] !== 1
                        ) {
                            continue;
                        }

                        ?>

                        <div>

                            <!-- Font Awesomeアイコン -->
                            <?= $feature['icon'] ?>

                            <span>
                                <?= h($feature['name']) ?>
                            </span>

                        </div>

                    <?php endforeach; ?>

                </div>

            </section>

            <!-- ==================================================
                 車両説明
            ================================================== -->

            <?php if (
                isset($product['car-information']) &&
                !empty($product['car-information'])
            ): ?>

                <section>

                    <h4>
                        車両の説明
                    </h4>

                    <div>

                        <?php

                        // 車両説明を安全にHTMLへ出力
                        //
                        // nl2br()
                        // → 改行を<br>に変換
                        //

                        echo nl2br(
                            h($product['car-information'])
                        );

                        ?>

                    </div>

                </section>

            <?php endif; ?>

        </div>

    </section>


    <!-- ==================================================
         下部ボタン
    ================================================== -->

    <section>

        <!-- ==================================================
            お気に入りボタン
        ================================================== -->
        <form action="favorite.php" method="POST" style="display: block; width: 100%;">
            <input type="hidden" name="add_id" value="<?= (int)$product['goods_id'] ?>">
            <button type="submit" style="display: flex; align-items: center; justify-content: center; gap: 10px; height: 68px; min-height: 68px; padding: 0; border-radius: 7px; text-decoration: none; font-size: 14px; font-weight: 600; transition: 0.2s; border: 1px solid #004831; background: #fff; color: #004831; width: 100%; cursor: pointer; box-sizing: border-box;">
                <i class="fa-regular fa-heart"></i>
                <span>お気に入りに追加</span>
            </button>
        </form>

        <!-- ==================================================
            問い合わせボタン
        ================================================== -->
        <a href="request_estimate.php?goods_id=<?= (int)$product['goods_id'] ?>"
        style="display: flex; align-items: center; justify-content: center; gap: 10px; height: 68px; min-height: 68px; padding: 0; border-radius: 7px; text-decoration: none; font-size: 14px; font-weight: 600; transition: 0.2s; border: 1px solid #004831; background: #004831; color: #fff; width: 100%; box-sizing: border-box;">
            <i class="fa-regular fa-envelope"></i>
            <span>この車について問い合わせる</span>
        </a>
    </section>
</main>
<!-- フッター読み込み -->
<?php require 'footer.php'; ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
    /*
    ==================================================
    要素を取得
    ==================================================
    */

    const mainImage =
        document.getElementById('mainImage');

    const prevButton =
        document.getElementById('prevButton');

    const nextButton =
        document.getElementById('nextButton');

    const thumbnails =
        document.querySelectorAll('.thumbnail');


    /*
    ==================================================
    PHPから画像一覧をJavaScriptへ渡す
    ==================================================
    */

    const images = <?= json_encode(
        $images,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    ) ?>;


    /*
    ==================================================
    現在表示している画像番号
    ==================================================
    */

    let currentIndex = 0;


    /*
    ==================================================
    画像を変更する関数
    ==================================================
    */

    function showImage(index) {

        /*
        画像が存在しない場合は何もしない
        */

        if (images.length === 0) {
            return;
        }


        /*
        配列の範囲を調整
        */

        if (index < 0) {

            index = images.length - 1;

        }

        if (index >= images.length) {

            index = 0;

        }


        /*
        現在の画像番号を保存
        */

        currentIndex = index;


        /*
        大きい画像を変更
        */

        mainImage.src = images[currentIndex];


        /*
        サムネイルの選択状態を変更
        */

        thumbnails.forEach(function (thumbnail, i) {

            thumbnail.classList.remove('active');

            if (i === currentIndex) {

                thumbnail.classList.add('active');

            }

        });

    }


    /*
    ==================================================
    左ボタン
    ==================================================
    */

    prevButton.addEventListener('click', function () {

        showImage(currentIndex - 1);

    });


    /*
    ==================================================
    右ボタン
    ==================================================
    */

    nextButton.addEventListener('click', function () {

        showImage(currentIndex + 1);

    });


    /*
    ==================================================
    サムネイルクリック
    ==================================================
    */

    thumbnails.forEach(function (thumbnail, index) {

        thumbnail.addEventListener('click', function () {

            showImage(index);

        });

    });


    /*
    ==================================================
    最初の画像を選択状態にする
    ==================================================
    */

    showImage(0);

});

</script>

</body>
</html>