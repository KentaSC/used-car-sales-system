-- Schema-only export for portfolio review. No INSERT data or user records.
-- Based on original phpMyAdmin export; table definitions and indexes preserved.

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
--


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
--


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
--


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
--


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
--


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
