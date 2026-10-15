<?php
/*
 * 車両価格から支払総額を計算する共通関数。
 * 複数ページで同じ計算式を使うため、このファイルへまとめている。
 */
function calculateTotalPrice(float $price): float
{
    // 車両価格の5%を諸費用として加算する
    return $price * 1.05;
}