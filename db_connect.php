<?php
/*
 * DB接続専用ファイル
 * 複数のページで同じ接続処理を繰り返さず、
 * require_once で読み込んで $pdo を使えるようにする。
 */
require_once __DIR__ . '/db_config.php';
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