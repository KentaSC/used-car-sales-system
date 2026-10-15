<?php
/*
 * セッションを使うページの先頭で読み込む共通ファイル
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}