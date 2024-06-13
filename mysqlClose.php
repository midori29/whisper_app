<?php

// API内部で使用するDB切断処理

// 1. データベースの切断処理を行う
// $pdoがnullでないことを確認してから接続を解除
if ($pdo !== null) {
    $pdo = null; // PDO接続を切断
}

?>
