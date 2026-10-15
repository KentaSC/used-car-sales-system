-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- ホスト: 127.0.0.1
-- 生成日時: 2026-10-02 01:41:38
-- サーバのバージョン： 10.4.32-MariaDB
-- PHP のバージョン: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- データベース: `test`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `contacts`
--

INSERT INTO `contacts` (`id`, `name`, `email`, `message`, `created_at`) VALUES
(1, '平川寛大', 'kankankand@gmail.com', '面接会場から失礼します。模擬プロ進捗どうですか?模擬プロが気になって、なんだか面接に集中できません。集中できなさすぎて、面接官を間違ってお母さんと呼んじゃいました。だれしも小学校の時に1度はやったことある、あれです。てへぺろ。', '2026-09-25 11:49:38'),
(2, '平川寛大', 'kankankand@gmail.com', '面接会場から失礼します。模擬プロ進捗どうですか?模擬プロが気になって、なんだか面接に集中できません。集中できなさすぎて、面接官を間違ってお母さんと呼んじゃいました。だれしも小学校の時に1度はやったことある、あれです。てへぺろ。', '2026-09-25 11:49:43'),
(3, '那須建太', 'nasu@gmail.com', '冗談を言わないと言ったにもかかわらず、やってしまいました。すみません', '2026-09-25 11:52:41'),
(4, '山田太郎', 'ssss@gmail.com', 'テストです', '2026-09-25 12:53:00'),
(5, '小村寿太郎', 'ssss@gmail.com', 'こんにちは小村寿太郎で～すww\r\nこの度、チーム卍に加入することになりました☆彡\r\nよろで～す！\r\nあ、ちなみに年齢は今年で171歳になりました～\r\nアゲアゲ～～', '2026-09-29 11:49:50'),
(6, 'ｱｲｳｴｵ', 'm39240276@gmail.com', 'qqw', '2026-09-29 14:46:10'),
(7, 'アイウエオ     サブアカ      アイウエオ', 'm392406@gmail.com', 'sfdghfghgjj', '2026-09-29 14:51:46'),
(8, 'あいうえお', 'nnnn@gmail.com', 'テスト', '2026-09-30 10:41:34');

-- --------------------------------------------------------

--
-- テーブルの構造 `favorite`
--

CREATE TABLE `favorite` (
  `goods_id` int(11) DEFAULT NULL,
  `user_id` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL COMMENT 'お気に入り登録時',
  `deleteflg` int(11) DEFAULT NULL COMMENT '論理削除',
  `deleted_at` datetime NOT NULL COMMENT 'お気に入り解除時',
  `favorite_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `favorite`
--

INSERT INTO `favorite` (`goods_id`, `user_id`, `created_at`, `deleteflg`, `deleted_at`, `favorite_id`) VALUES
(3, 'U005', '2026-09-25 15:27:19', 0, '0000-00-00 00:00:00', 32),
(12, 'U005', '2026-09-25 15:29:11', 0, '0000-00-00 00:00:00', 33),
(12, 'U004', '2026-09-29 10:39:58', 1, '0000-00-00 00:00:00', 35),
(2, 'U003', '2026-09-29 11:46:01', 1, '0000-00-00 00:00:00', 38),
(12, 'U003', '2026-09-29 11:46:05', 1, '0000-00-00 00:00:00', 39),
(3, 'U004', '2026-09-29 13:10:17', 1, '0000-00-00 00:00:00', 42),
(10, 'U004', '2026-09-29 14:05:46', 1, '0000-00-00 00:00:00', 43),
(1, 'U005', '2026-09-29 14:36:25', 0, '0000-00-00 00:00:00', 45),
(11, 'U004', '2026-09-30 09:35:48', 1, '0000-00-00 00:00:00', 46),
(5, 'U003', '2026-09-30 09:39:41', 1, '0000-00-00 00:00:00', 47),
(6, 'U004', '2026-09-30 10:20:29', 1, '0000-00-00 00:00:00', 48),
(7, 'U004', '2026-09-30 10:31:09', 1, '0000-00-00 00:00:00', 49),
(12, 'U006', '2026-09-30 11:29:29', 1, '0000-00-00 00:00:00', 50),
(11, 'U006', '2026-09-30 11:42:36', 1, '0000-00-00 00:00:00', 51),
(11, 'U003', '2026-09-30 14:30:52', 0, '0000-00-00 00:00:00', 52),
(4, 'U006', '2026-10-01 09:41:40', 0, '0000-00-00 00:00:00', 53);

-- --------------------------------------------------------

--
-- テーブルの構造 `goods`
--

CREATE TABLE `goods` (
  `carphoto_main` varchar(100) NOT NULL,
  `carphoto_rear` varchar(100) DEFAULT NULL,
  `carphoto_panel` varchar(100) DEFAULT NULL,
  `carphoto_back` varchar(100) NOT NULL,
  `carphoto_navi` varchar(100) NOT NULL,
  `goods_id` int(11) NOT NULL,
  `maker` varchar(11) NOT NULL,
  `car_grade` varchar(11) DEFAULT NULL,
  `car_model` varchar(11) DEFAULT NULL,
  `price` int(11) NOT NULL,
  `car-information` text NOT NULL,
  `car_navi` int(1) NOT NULL DEFAULT 0,
  `etc` int(1) NOT NULL DEFAULT 0,
  `backcamera` int(1) NOT NULL DEFAULT 0,
  `dashcam` int(1) NOT NULL DEFAULT 0,
  `smartkey` int(1) NOT NULL DEFAULT 0,
  `cruise_control` int(1) NOT NULL DEFAULT 0,
  `omnidirectional_camera` int(1) NOT NULL DEFAULT 0,
  `led_headlights` int(1) NOT NULL DEFAULT 0,
  `siding_door` int(1) NOT NULL DEFAULT 0,
  `no_smoking` int(1) NOT NULL DEFAULT 0,
  `date_time` datetime NOT NULL,
  `car_name` varchar(100) DEFAULT NULL,
  `model_year` year(4) DEFAULT NULL,
  `milage` int(11) DEFAULT NULL,
  `inspection_date` date DEFAULT NULL,
  `engine_cc` int(11) NOT NULL,
  `transmission` varchar(20) NOT NULL,
  `drivertrain` varchar(20) NOT NULL,
  `fuel_type` varchar(20) NOT NULL,
  `color` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `goods`
--

INSERT INTO `goods` (`carphoto_main`, `carphoto_rear`, `carphoto_panel`, `carphoto_back`, `carphoto_navi`, `goods_id`, `maker`, `car_grade`, `car_model`, `price`, `car-information`, `car_navi`, `etc`, `backcamera`, `dashcam`, `smartkey`, `cruise_control`, `omnidirectional_camera`, `led_headlights`, `siding_door`, `no_smoking`, `date_time`, `car_name`, `model_year`, `milage`, `inspection_date`, `engine_cc`, `transmission`, `drivertrain`, `fuel_type`, `color`) VALUES
('img/aqua.png', 'img/toyota_aqua_rear.png', 'img/toyota_aqua_front.png', 'img/toyota_aqua_back.png', 'img/toyota_aqua_navi.png', 1, 'トヨタ', 'S', 'コンパクトカー', 1150000, '人気のアクア再入荷です。ご家族にも、お一人用にも適したサイズです。', 0, 1, 1, 0, 1, 0, 0, 0, 0, 0, '2026-08-24 07:14:54', 'アクア', '2017', 51000, '2025-08-12', 1500, 'CVT', '2WD', 'ガソリン', '白'),
('img/fit.png', 'img/honda_fit_rear.png', 'img/honda_fit_front.png', 'img/honda_fit_back.png', 'img/honda_fit_navi.png', 2, 'ホンダフィット', '13G', 'コンパクトカー', 880000, 'みんなにフィット。どなたのニーズにもフィットする1台です。さぁ、あなたは何をフィットさせますか?', 0, 0, 1, 1, 1, 0, 1, 0, 0, 0, '2026-08-25 05:51:24', 'フィット', '2016', 43000, '2025-10-01', 1500, 'CVT', '2WD', 'ガソリン', 'シルバー'),
('img/tant.png', 'img/daihatsu_tant_rear.png', 'img/daihatsu_tant_front.png', 'img/daihatsu_tant_back.png', 'img/daihatsu_tant_navi.png', 3, 'ダイハツ', 'X SA ', '軽自動車', 1080000, '広々とした室内空間と使い勝手の良さが魅力のダイハツ タント Xです。スライドドアを採用しているため、狭い駐車場でも乗り降りしやすく、お買い物やお子様の送り迎えなど日常使いにもおすすめです。コンパクトなボディで運転しやすく、初めて軽自動車をお探しの方にもぴったりの一台です。気になる点や車両状態についてのご質問がございましたら、お気軽にお問い合わせください。', 1, 1, 1, 0, 1, 0, 0, 1, 0, 0, '2026-08-25 05:55:28', 'タント', '2019', 38000, '2026-01-01', 2000, 'CVT', '2WD', 'ガソリン', '水色'),
('img/wagonr.png', 'img/suzuki_wagonr_rear.png', 'img/suzuki_wagonr_front.png', 'img/suzuki_wagonr_back.png', 'img/suzuki_wagonr_navi.png', 4, 'スズキ', 'R FX', '軽自動車', 750000, 'コンパクトで扱いやすく、毎日の移動にぴったりなスズキ ワゴンRです。\r\n見た目以上に室内はゆったりとしており、買い物や通勤など普段使いにも便利な一台です。小回りが利くため、狭い道や駐車場でも運転しやすく、軽自動車をお探しの方にもおすすめです。\r\n', 0, 1, 0, 0, 1, 1, 0, 0, 1, 1, '2026-08-25 06:02:49', 'ワゴンR', '2018', 46000, '2026-01-01', 1500, 'CVT', '2WD', 'ガソリン', '白'),
('img/yaris.png', 'img/toyota_yaris_rear.png', 'img/toyota_yaris_front.png', 'img/toyota_yaris_back.png', 'img/toyota_yaris_navi.png', 5, 'トヨタ', 'G', 'コンパクトカー', 1680000, 'スタイリッシュなデザインと運転のしやすさが魅力のトヨタ ヤリスです。\r\nコンパクトなボディで小回りが利き、街中や狭い道でも扱いやすい一台です。日常のお買い物から通勤、休日のドライブまで幅広いシーン', 1, 1, 1, 0, 1, 0, 0, 0, 0, 1, '2026-08-25 06:06:30', 'ヤリス', '2020', 32000, '2025-11-05', 1500, 'CVT', '2WD', 'ガソリン', 'ゴールド'),
('img/roox.png', 'img/nissan_roox_back.png', 'img/nissan_roox_front.png', 'nissan_juke_back.png', 'nissan_roox_navi.png', 6, '日産', 'X', '軽自動車', 1250000, 'やさしいベージュカラーが印象的な日産ルークスです。\r\n広々とした室内空間とスライドドアで乗り降りしやすく、お買い物や通勤、ご家族でのお出かけにも使いやすい一台です。コンパクトなボディで小回りも利くため、街中での運転にもおすすめです。\r\n', 1, 1, 0, 1, 0, 0, 1, 0, 1, 0, '2026-08-25 06:09:54', 'ルークス', '2020', 29000, '2026-04-02', 1500, 'CVT', '2WD', 'ガソリン', 'フローズンバニラパール'),
('img/swift.png', 'img/suzuki_swift_rear.png', 'img/suzuki_swift_front.png', 'img/suzuki_swift_back.png', 'img/suzuki_swift_navi.png', 7, 'スズキ', 'RS', 'コンパクトカー', 1120000, 'コンパクトなボディと軽快な走りが魅力のスズキ スイフトです。小回りが利いて街中でも扱いやすく、通勤やお買い物はもちろん、休日のドライブにも活躍する一台です。すっきりとしたデザインで、運転のしやすさと実用性をバランスよく備えています。\r\n\r\n\r\n', 1, 0, 0, 0, 1, 0, 1, 1, 0, 0, '2026-08-25 06:12:56', 'スイフト', '2018', 41000, '2025-12-01', 1500, 'CVT', '2WD', 'ガソリン', '白'),
('img/juke.png', 'img/nissan_juke_rear.png', 'img/nissan_juke_front.png', 'img/nissan_juke_back.png', 'img/nissan_juke_navi.png', 8, '日産', '15RX', 'SUV', 1380000, '個性的でスポーティなデザインが目を引く日産 ジュークです。コンパクトなボディで街中でも取り回しやすく、SUVらしい存在感も楽しめる一台です。運転しやすいサイズ感ながら、力強いスタイリングで、日常使いから休日のドライブまで幅広く活躍します。', 1, 0, 1, 0, 1, 0, 0, 0, 1, 1, '2026-08-25 06:15:53', 'ジューク', '2017', 53000, '2025-11-01', 1500, 'CVT', '2WD', 'ガソリン', '黒'),
('img/cx5.png', 'img/mazda_cx5_rear.png', 'img/mazda_cx5_front.png', 'img/mazda_cx5_back.png', 'img/mazda_cx5_navi.png', 9, 'マツダ', 'XD L Packag', 'SUV', 3250000, '上質で洗練されたデザインが魅力のマツダ CX-5です。ゆとりのある室内空間と安定感のある走りを備え、街乗りから長距離ドライブまで快適に楽しめる一台です。SUVらしい力強さがありながら、落ち着きのあるスタイリングで日常にも馴染みやすいモデルです。', 1, 0, 1, 0, 1, 1, 0, 1, 0, 1, '2026-08-25 06:18:56', 'CX-5', '2021', 28000, '2026-04-01', 1600, 'CVT', '2WD', 'ガソリン', 'ネイビー'),
('img/is.png', 'img/lexus_is_rear.png', 'img/lexus_is_front.png', 'img/lexus_is_back.png', 'img/lexus_is_navi.png', 10, 'レクサス', '300h F SPOR', 'セダン', 3780000, 'スポーティさと上質感を兼ね備えたデザインが魅力のレクサス ISです。低重心でスタイリッシュなボディに、落ち着きのある室内空間を備えた一台です。街中でのドライブはもちろん、長距離でも快適に走れる、セダンならではの安定感と高級感を楽しめます。', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, '2026-08-25 06:22:01', 'IS', '2019', 27000, '2026-05-01', 1500, 'CVT', '2WD', 'ガソリン', '白'),
('img/86.png', 'img/toyota_86_rear.png', 'img/toyota_86_front.png', 'img/toyota_86_back.png', 'img/toyota_86_navi.png', 11, 'トヨタ', 'GT Limited', 'セダン', 2980000, 'スポーティさと上質感を兼ね備えたデザインが魅力のレクサス ISです。低重心でスタイリッシュなボディに、落ち着きのある室内空間を備えた一台です。街中でのドライブはもちろん、長距離でも快適に走れる、セダンならではの安定感と高級感を楽しめます。', 1, 0, 1, 0, 1, 0, 1, 0, 1, 0, '2026-08-25 06:24:54', '86', '2017', 31000, '2025-12-02', 1500, 'CVT', '2WD', 'ガソリン', '白'),
('img/landcruiser.png', 'img/toyota_landcruiser_rear.png', 'img/toyota_landcruiser_front.png', 'img/toyota_landcruiser_back.png', 'img/toyota_landcruiser_navi.png', 12, 'トヨタ', 'TX Lパッケージ', 'SUV', 3950000, '圧倒的な存在感と力強いスタイリングが魅力のトヨタ ランドクルーザーです。ゆとりある室内空間と高い走破性を備え、街乗りからアウトドア、長距離ドライブまで幅広く活躍します。SUVらしい力強さと快適性を兼ね備え、さまざまなシーンで頼もしさを感じられる一台です。', 1, 0, 0, 1, 1, 1, 1, 0, 0, 0, '2026-08-25 06:27:36', 'ランドクルーザプラド', '2022', 19000, '2027-01-01', 1500, 'CVT', '2WD', 'ガソリン', '黒');

-- --------------------------------------------------------

--
-- テーブルの構造 `history`
--

CREATE TABLE `history` (
  `user_id` varchar(15) NOT NULL COMMENT 'お気に入り登録時',
  `deleteflg` int(1) NOT NULL COMMENT '削除',
  `createdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'レコード作成時',
  `productid` int(15) NOT NULL COMMENT 'どの車に申し込んだか',
  `applicationdate` datetime NOT NULL COMMENT '申し込んだ日時',
  `negotiationdate` datetime NOT NULL COMMENT 'ユーザーが選択した商談日時',
  `consultation_method` varchar(30) NOT NULL,
  `purchaseid` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(20) NOT NULL COMMENT '「受付済」「商談中」など',
  `request` int(3) NOT NULL,
  `paymentmethod` int(20) NOT NULL,
  `contact` int(11) NOT NULL,
  `updatedate` timestamp NULL DEFAULT NULL COMMENT '更新時にupdateされる'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `history`
--

INSERT INTO `history` (`user_id`, `deleteflg`, `createdate`, `productid`, `applicationdate`, `negotiationdate`, `consultation_method`, `purchaseid`, `status`, `request`, `paymentmethod`, `contact`, `updatedate`) VALUES
('U004', 0, '2026-09-29 03:19:53', 10, '2026-09-29 12:19:53', '0000-00-00 00:00:00', 'store_visit', 1, '受付済', 0, 0, 0, NULL),
('U004', 0, '2026-09-29 03:21:34', 10, '2026-09-29 12:21:34', '0000-00-00 00:00:00', 'store_visit', 2, '受付済', 0, 0, 0, NULL),
('U004', 0, '2026-09-29 03:29:51', 10, '2026-09-29 12:29:51', '0000-00-00 00:00:00', 'store_visit', 3, '受付済', 0, 0, 0, NULL),
('U004', 0, '2026-09-29 03:31:11', 10, '2026-09-29 12:31:11', '0000-00-00 00:00:00', 'store_visit', 4, '受付済', 0, 0, 0, NULL),
('U004', 0, '2026-09-29 03:34:07', 10, '2026-09-29 12:34:07', '0000-00-00 00:00:00', 'store_visit', 5, '受付済', 0, 0, 0, NULL),
('U004', 0, '2026-09-29 03:34:41', 10, '2026-09-29 12:34:41', '0000-00-00 00:00:00', 'store_visit', 6, '受付済', 0, 0, 0, NULL),
('U004', 0, '2026-09-29 03:35:41', 10, '2026-09-29 12:35:41', '0000-00-00 00:00:00', 'store_visit', 7, '受付済', 0, 0, 0, NULL),
('U003', 0, '2026-09-29 03:45:05', 2, '2026-09-29 12:45:05', '0000-00-00 00:00:00', 'store_visit', 8, '受付済', 0, 0, 0, NULL),
('U003', 0, '2026-09-29 03:48:57', 2, '2026-09-29 12:48:57', '0000-00-00 00:00:00', 'store_visit', 9, '受付済', 0, 0, 0, NULL),
('U004', 0, '2026-09-29 03:57:58', 10, '2026-09-29 12:57:58', '0000-00-00 00:00:00', 'store_visit', 10, '受付済', 0, 0, 0, NULL),
('U004', 0, '2026-09-29 03:59:35', 12, '2026-09-29 12:59:35', '0000-00-00 00:00:00', 'store_visit', 11, '受付済', 0, 0, 0, NULL),
('U004', 0, '2026-09-29 04:05:53', 5, '2026-09-29 13:05:53', '0000-00-00 00:00:00', 'store_visit', 12, '受付済', 0, 0, 0, NULL),
('U004', 0, '2026-09-29 04:13:31', 11, '2026-09-29 13:13:31', '0000-00-00 00:00:00', 'online', 13, '受付済', 0, 0, 0, NULL),
('U004', 0, '2026-09-29 04:14:19', 11, '2026-09-29 13:14:19', '0000-00-00 00:00:00', 'remote_purchase', 14, '受付済', 0, 0, 0, NULL),
('U004', 0, '2026-09-29 04:33:41', 11, '2026-09-29 13:33:41', '0000-00-00 00:00:00', 'remote_purchase', 15, '受付済', 0, 0, 0, NULL),
('U004', 0, '2026-09-29 04:41:43', 11, '2026-09-29 13:41:43', '0000-00-00 00:00:00', 'remote_purchase', 16, '受付済', 0, 0, 0, NULL),
('U004', 0, '2026-09-29 04:42:19', 5, '2026-09-29 13:42:19', '0000-00-00 00:00:00', 'store_visit', 17, '受付済', 0, 0, 0, NULL),
('U004', 0, '2026-09-29 04:42:50', 5, '2026-09-29 13:42:50', '0000-00-00 00:00:00', 'store_visit', 18, '受付済', 0, 0, 0, NULL),
('U004', 0, '2026-09-29 04:44:48', 5, '2026-09-29 13:44:48', '0000-00-00 00:00:00', 'store_visit', 19, '受付済', 0, 0, 0, NULL),
('U004', 0, '2026-09-29 04:51:17', 5, '2026-09-29 13:51:17', '0000-00-00 00:00:00', 'store_visit', 20, '受付済', 0, 0, 0, NULL),
('U004', 0, '2026-09-29 04:51:56', 5, '2026-09-29 13:51:56', '0000-00-00 00:00:00', 'store_visit', 21, '受付済', 0, 0, 0, NULL),
('U004', 0, '2026-09-29 04:56:02', 5, '2026-09-29 13:56:02', '0000-00-00 00:00:00', 'online', 22, '受付済', 0, 0, 0, NULL),
('U004', 0, '2026-09-29 05:02:04', 1, '2026-09-29 14:02:04', '0000-00-00 00:00:00', 'store_visit', 23, '受付済', 0, 0, 0, NULL),
('U004', 0, '2026-09-29 05:32:00', 3, '2026-09-29 14:32:00', '0000-00-00 00:00:00', 'online', 24, '受付済', 0, 0, 0, NULL),
('U004', 0, '2026-09-29 05:33:09', 3, '2026-09-29 14:33:09', '0000-00-00 00:00:00', 'online', 25, '受付済', 0, 0, 0, NULL),
('U004', 0, '2026-09-29 05:34:09', 3, '2026-09-29 14:34:09', '0000-00-00 00:00:00', 'online', 26, '受付済', 0, 0, 0, NULL),
('U005', 0, '2026-09-30 01:48:44', 3, '2026-09-30 10:48:44', '0000-00-00 00:00:00', 'store_visit', 27, '受付済', 0, 0, 0, NULL),
('U005', 0, '2026-09-30 02:40:16', 12, '2026-09-30 11:40:16', '0000-00-00 00:00:00', 'store_visit', 28, '受付済', 0, 0, 0, NULL),
('U006', 0, '2026-09-30 02:40:32', 10, '2026-09-30 11:40:32', '0000-00-00 00:00:00', 'store_visit', 29, '受付済', 0, 0, 0, NULL);

-- --------------------------------------------------------

--
-- テーブルの構造 `users`
--

CREATE TABLE `users` (
  `userid` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `mail` varchar(100) NOT NULL,
  `last_name_kana` varchar(15) DEFAULT NULL,
  `first_name_kana` varchar(15) DEFAULT NULL,
  `entrydate` date DEFAULT NULL,
  `systemauthorityno` int(1) DEFAULT NULL COMMENT '0→ユーザー 1→管理者 2→スタッフ',
  `deleteflg` int(1) DEFAULT NULL COMMENT 'データが有効か 0→有効 1→無効',
  `last_name` varchar(10) DEFAULT NULL,
  `first_name` varchar(10) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- テーブルのデータのダンプ `users`
--

INSERT INTO `users` (`userid`, `password`, `mail`, `last_name_kana`, `first_name_kana`, `entrydate`, `systemauthorityno`, `deleteflg`, `last_name`, `first_name`, `reset_token`, `reset_expires`) VALUES
('U001', 'test1234', 'kumadamasashi@example.com', 'クマダ', 'マサシ', '2026-08-24', NULL, NULL, '熊田', 'まさし', NULL, NULL),
('U002', 'shirokarinto', 'shirokari@gmail.com', 'クルマ', 'タロウ', '2026-08-25', NULL, NULL, '車', '太郎', NULL, NULL),
('U003', '$2y$10$WAaNhyxyRIFoN6zS5hr0jepW2rT6xUXBrZG99q92b3vRGjbhHaivq', 'wwww@gmail.com', 'アイン', 'シュタイン', '2026-09-25', NULL, 0, '愛院', '主多院', NULL, NULL),
('U004', '$2y$10$QI2S/f4P2e5o0R2qBAxNF.FS8ArYxFUjW4j.GOAmk8LauHlep2Y1C', 'test@gmail.com', 'ア', 'ア', '2026-09-25', NULL, 0, 'さ', 'さ', '1a5d7a2bbe1cc77adfcf8f339a2d2eee074f366b8b7df4a2d264070004975886', '2026-09-30 04:19:26'),
('U005', '$2y$10$fwxQCZmllQJsEOJ5AS5v.OeSOWaQbwKXOjpPkdPWuSsMU06wPCS0y', 'nnnn@gmail.com', 'ア', 'ア', '2026-09-25', NULL, 0, 'あ', 'あ', NULL, NULL),
('U006', '$2y$10$rSIGgxjf4v37Q4zmmxmjYeld2R24QbgIbRcy6qDgvqURqpwfsFlqa', 'test01@gmail.com', 'ポムポム', 'プリン', '2026-09-30', NULL, 0, 'ぽむぽむ', 'ぷりん', NULL, NULL);

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `favorite`
--
ALTER TABLE `favorite`
  ADD PRIMARY KEY (`favorite_id`);

--
-- テーブルのインデックス `goods`
--
ALTER TABLE `goods`
  ADD PRIMARY KEY (`goods_id`);

--
-- テーブルのインデックス `history`
--
ALTER TABLE `history`
  ADD PRIMARY KEY (`purchaseid`),
  ADD KEY `idx_history_user_id` (`user_id`);

--
-- テーブルのインデックス `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userid`),
  ADD UNIQUE KEY `mail` (`mail`);

--
-- ダンプしたテーブルの AUTO_INCREMENT
--

--
-- テーブルの AUTO_INCREMENT `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- テーブルの AUTO_INCREMENT `favorite`
--
ALTER TABLE `favorite`
  MODIFY `favorite_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- テーブルの AUTO_INCREMENT `history`
--
ALTER TABLE `history`
  MODIFY `purchaseid` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
