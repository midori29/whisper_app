<?php

// API内部で使用するDB接続処理

// 他のファイルを読み込む
require 'errorMsgs.php';

// APIで行いたい処理を記述(データベースに接続してデータを取得する等) 
$host = "localhost";      // 接続するホスト名を指定します。    例：localhost、DBサーバーアドレス(xxxxxx.db.xxxxx.ne.jp みたいな感じ) 
$user = "dbuser";       // 接続するユーザー名を指定します。          
$password = "ecc";   // 接続するユーザーのパスワードを指定します。
$database = "whisper"; // 接続するデーターベース名を指定します。

// PDOオブジェクトの作成
$dsn = "mysql:host={$host};dbname={$database};charset=utf8mb4"; // 接続情報作成 ※dsn = データソース名(Data Source Name)
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,       // クエリ実行時のエラーや接続エラーが発生した場合、例外がスローされるよう指定
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // 取得した結果を連想配列として取得するフェッチモードの指定
    PDO::ATTR_EMULATE_PREPARES => false,               // プリペアドステートメントをエミュレートしないように設定。これによりSQLインジェクション攻撃からの保護が強化されます。
];
try {
    $pdo = new PDO($dsn, $user, $password, $options);  // PDOオブジェクト作成
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}

?>