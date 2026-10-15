<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>car_footer</title>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" href="style.css">
    <style>
        .footer {
            background-color: #f7f8f7;
            color: #222;
            padding: 45px 5% 10px;
            font-family: sans-serif;
        }

        /* フッター全体 */
        .footer-inner {
            max-width: 1100px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            min-height: 330px;
        }

        .footer-info {
            width: 360px;
            text-align: left;
        }

        /* ロゴ */
        .footer-logo {
            width: 220px;
            height: auto;
            display: block;
            margin-bottom: 25px;
        }

        /* 説明文 */
        .footer-description {
            font-size: 14px;
            line-height: 2;
            margin: 0 0 25px;
        }


        /*住所・営業時間*/

        .footer-detail p {
            display: flex;
            align-items: flex-start;

            font-size: 14px;
            line-height: 1.8;

            margin: 0 0 15px;
        }

        /* アイコン */
        .footer-detail i {
            color: #164d42;
            font-size: 20px;
            width: 32px;
            margin-top: 3px;
            flex-shrink: 0;
        }

        .mail-link {
            display: inline-block;
            color: #164d42;
            text-decoration: none;
        }

        .mail-link:hover {
            opacity: 0.7;
        }

        
        /* コピーライト */
        .copyright {
            text-align: center;

            color: #24483f;
            font-size: 14px;

            margin-top: 20px;
        }

        /* =================================
        以下チャットボット
        ================================= */

        .chatbot {
            position: fixed;
            right: 30px;
            bottom: 25px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }


        /* =================================
        開くボタン
        ================================= */
        /* チャットを開くボタン（初期状態）の基本スタイル */
        .chat-open {
            width: 250px;
            height: 50px;

            border: none;
            border-radius: 10px;

            background-color: #155446;
            color: #fff;

            font-size: 15px;
            font-weight: bold;

            cursor: pointer;

            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);

            transition: 0.2s;
        }

        /* マウスカーソルがボタン上に載った時（ホバー状態）のアニメーション */
        .chat-open:hover {
            background-color: #0e4035;
            transform: translateY(-2px);
        }

        /* ボタン内のアイコン（<i class="...">）の装飾設定 */
        .chat-open i {
            margin-right: 8px;
            font-size: 17px;
        }


        /* =================================
        チャット画面
        ================================= */
        /* チャットウィンドウ（対話画面全体）の本体スタイル */
        .chat-window {
    display: none; 
    width: 360px;
    height: 520px;
    max-height: calc(100vh - 100px); /* ★追加：画面の高さからはみ出ないように制限 */
    background-color: #fff;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 30px rgba(0, 0, 0, 0.25);
    margin-bottom: 12px;
    flex-shrink: 0;
    
    /* ★追加：内部のヘッダー・メッセージ・ボタンを縦にきれいに収める設定 */
    flex-direction: column;
}

/* 画面開閉時に flex が維持されるように（JavaScript等で display: block になった時の考慮） */
.chat-window[style*="display: block"] {
    display: flex !important;
}

/* メッセージ表示エリア */
.chat-messages {
    flex: 1;                   /* ★修正：固定高さではなく、余った高さを柔軟に使う */
    min-height: 120px;         /* ★追加：最小の高さを確保 */
    padding: 18px;
    overflow-y: auto;
    background-color: #f5f7f6;
}


        /* =================================
        ヘッダー
        ================================= */
        /* チャットウィンドウ上部のヘッダー全体 */
        .chat-header {
            height: 65px;

            padding: 0 18px;

            background-color: #155446;
            color: #fff;

            display: flex; /* Flexboxを有効化して子要素を横並びにする */
            align-items: center; /* 子要素を上下中央（垂直方向）に配置 */
            justify-content: space-between; /* タイトル側と閉じるボタンを左右両端に振り分けて配置 */
        }

        /* ヘッダー内のメインタイトル（ボット名やサービス名など） */
        .chat-header strong {
            display: block;
            font-size: 17px;
        }

        /* ヘッダー内のサブタイトルや補足テキスト（「通常〇分以内に返信」など） */
        .chat-header span {
            display: block;

            margin-top: 3px;

            font-size: 11px;

            opacity: 0.8;
        }

        /* ヘッダー右上の「閉じる（×）」ボタン */
        .chat-close {
            border: none;

            background: transparent;

            color: #fff;

            font-size: 28px;

            cursor: pointer;

            line-height: 1;
        }


        /* =================================
        メッセージ
        ================================= */
        /* メッセージ履歴を表示する親コンテナ（スクロールエリア） */
        .chat-messages {
            height: 220px;

            padding: 18px;

            overflow-y: auto;

            background-color: #f5f7f6;
        }

        /* ボット側の返信メッセージ（吹き出し：左寄せ） */
        .bot-message {
            width: fit-content;
            max-width: 85%;

            padding: 10px 13px;

            margin-bottom: 10px;

            background-color: #fff;

            border-radius: 5px 14px 14px 14px;

            font-size: 13px;
            line-height: 1.7;

            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        /* ユーザー側の送信メッセージ（吹き出し：右寄せ） */
        .user-message {
            width: fit-content;
            max-width: 85%;

            padding: 10px 13px;

            margin: 0 0 10px auto;

            background-color: #155446;
            color: #fff;

            border-radius: 14px 5px 14px 14px;

            font-size: 13px;
            line-height: 1.7;
        }


        /* =================================
        質問ボタン
        ================================= */
        /* 選択肢ボタンを囲む親コンテナ（エリア全体）の設定 */
        .chat-buttons {
            padding: 12px;
            background-color: #fff;
            border-top: 1px solid #eee;
        }

        /* エリア内の各ボタン要素の設定 */
        .chat-buttons button {
            display: block;

            width: 100%;

            padding: 8px;

            margin-bottom: 6px;

            border: 1px solid #155446;
            border-radius: 6px;

            background-color: #fff;
            color: #155446;

            font-size: 12px;

            cursor: pointer;    /* エリア内の各ボタン要素の設定 */

            transition: 0.2s;
        }

        /* 一番最後のボタンのみ下余白を消去（親要素の下部余白との重複を防ぐ） */
        .chat-buttons button:last-child {
            margin-bottom: 0;
        }

        /* マウスカーソルがボタンの上に載った時のデザイン（ホバー状態） */
        .chat-buttons button:hover {
            background-color: #155446;
            color: #fff;
        }


        /* =================================
        スマホ
        ================================= */

        /* 画面幅が 600px 以下のデバイス（スマートフォン等）に適用するスタイル */
        @media screen and (max-width: 600px) {

            /* チャットボット全体の位置調整 */
            .chatbot {
                right: 15px;
                bottom: 15px;
                left: 15px;
            }

            /* チャットを開くボタンの幅調整 */
            .chat-open {
                width: 100%;
            }

            /* チャットウィンドウ（会話画面）のサイズ調整 */
            .chat-window {
                width: 100%;
                height: 500px;
            }

        }

    </style>
</head>

<body>

<footer class="footer">

    <div class="footer-inner">

        <!-- 左側 -->
        <div class="footer-info">
            <!-- ロゴ表示 -->
            <img src="img/logo_clear2.png"alt="Live motors"class="footer-logo">

            <p class="footer-description">
                良質な中古車を、わかりやすい価格で。<br>
                街の小さなお店だからこそできる、<br>
                丁寧なご提案をお約束します。
            </p>

            <div class="footer-detail">
                <p><i class="fa-solid fa-location-dot"></i>
                    <span>
                        〒880-0051<br>
                        宮崎県宮崎市江平西1丁目2－27
                    </span>
                </p>

                <p><i class="fa-regular fa-clock"></i>
                    <span>
                        営業時間：10：00～19：00<br>
                        定休日：水曜日
                    </span>
                </p>

                <p><a href="contact.php" class="mail-link">
                            <i class="fa-regular fa-envelope"></i>
                            <span>お問い合わせ</span>
                        </a>
                </p>
            </div>

        </div>

        <!-- =========================
        以下チャットボット
        ========================= -->

        <div class="chatbot">

            <!-- チャット画面 -->
            <div class="chat-window" id="chatWindow">

                <div class="chat-header">
                    <div>
                        <strong>Live motors</strong>
                        <span>お問い合わせチャット</span>
                    </div>

                    <button class="chat-close" onclick="closeChat()">×</button>
                </div>


                <!-- 会話部分 -->
                <div class="chat-messages" id="chatMessages">

                    <div class="bot-message">
                        こんにちは！😊<br>
                        Live motorsです。<br>
                        お車探しのお手伝いをいたします。
                    </div>

                    <div class="bot-message">
                        下のボタンからご質問をお選びください。
                    </div>

                </div>


                <!-- 質問ボタン -->
                <div class="chat-buttons">

                    <button onclick="sendQuestion('営業時間')">
                        🕐 営業時間について
                    </button>

                    <button onclick="sendQuestion('住所')">
                        📍 お店の場所について
                    </button>

                    <button onclick="sendQuestion('在庫')">
                        🚗 在庫車について
                    </button>

                    <button onclick="sendQuestion('車検')">
                        🔧 車検・整備について
                    </button>

                    <button onclick="sendQuestion('問い合わせ')">
                        ✉ お問い合わせについて
                    </button>

                </div>

            </div>


            <!-- チャットを開くボタン -->
            <button class="chat-open" onclick="openChat()">
                <i class="fa-solid fa-robot"></i>
                <span class="chat-open-text">チャットで質問する</span>
            </button>

        </div>
            </div>

            <div class="copyright">
                &copy; 2026 Live motors. All rights reserved.
            </div>

        </footer>

        <script>

        /* =========================
        チャットを開く
        ========================= */
        
        /**
         * チャット画面を表示する関数
         */
        function openChat() {

            // IDが "chatWindow" のHTML要素を取得し、CSSの display プロパティを "block" に変更して表示化
            document.getElementById("chatWindow").style.display = "block";

        }


        /* =========================
        チャットを閉じる
        ========================= */

        /**
         * チャット画面を非表示にする関数
         */
        function closeChat() {

            // IDが "chatWindow" のHTML要素を取得し、CSSの display プロパティを "none" に変更して非表示化
            document.getElementById("chatWindow").style.display = "none";

        }


        /* =========================
        質問に回答
        ========================= */

        //ユーザーの質問を受け取り、チャット画面に表示・返信処理を行う関数
        function sendQuestion(question) {
            // HTML上からIDが "chatMessages" の要素（メッセージ表示用のコンテナ）を取得
            const messages = document.getElementById("chatMessages");


        /* お客さんのメッセージ表示処理 */

        // 1. ユーザー側の送信メッセージ（吹き出し）用の div 要素を生成
        const userMessage = document.createElement("div");

            // CSSで吹き出しの見た目（右寄せ、背景色など）を適用するためのクラス名を指定
            userMessage.className = "user-message";

            // ユーザーが入力したテキスト（question）を要素のテキストとして設定
            // ※textContent を使うことで XSS（悪意あるコードの実行）を防止
            userMessage.textContent = question;

            // 親要素（messages）の末尾に、作成したメッセージ要素を追加して画面に表示
            messages.appendChild(userMessage);


            /* 回答 */
            /* 2. 質問内容に応じた返信文の準備 */
            let answer = "";


            if (question === "営業時間") {

                answer =
                "営業時間は10：00～19：00です。<br>" +
                "定休日は水曜日となっております。";

            }


            else if (question === "住所") {

                answer =
                "〒880-0051<br>" +
                "宮崎県宮崎市江平西1丁目2－27<br><br>" +
                "ご来店の際はお気をつけてお越しください。";

            }


            else if (question === "在庫") {

                answer =
                "在庫車についてのお問い合わせありがとうございます！<br><br>" +
                "現在の在庫状況については、在庫ページをご確認いただくか、店舗までお問い合わせください。";

            }


            else if (question === "車検") {

                answer =
                "車検・整備についてもお気軽にご相談ください。<br><br>" +
                "詳しい内容や料金については、店舗までお問い合わせください。";

            }


            else if (question === "問い合わせ") {

                answer =
                "お問い合わせありがとうございます！<br><br>" +
                "メールでのお問い合わせは、下記ボタンからお願いいたします。<br><br>" +
                "<a href='contact.php'>お問い合わせページへ</a>";

            }


            /* botの回答を表示 */

/* 3. 少しの間（400ミリ秒）を置いてボットの返信を表示 */
setTimeout(function() {

    // ボット用のメッセージ要素（div）を作成
    const botMessage = document.createElement("div");

    // CSSスタイリング用のクラスを付与
    botMessage.className = "bot-message";

    // 返信テキスト（answer）を要素の内部HTMLとしてセット
    botMessage.innerHTML = answer;
    
    // 親要素（messages）の末尾にボットのメッセージを追加
    messages.appendChild(botMessage);

    // 【ボット返信時】メッセージ追加後に領域の最下部へ自動スクロール
    messages.scrollTop = messages.scrollHeight;

}, 400);


// 【ユーザー送信時】ユーザーのメッセージ追加直後にも最下部へ自動スクロール
messages.scrollTop = messages.scrollHeight;

        }

</script>

</body>
</html>

