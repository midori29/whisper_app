<?php

// ユーザとパスワードが一致しているかチェック

// 必要なファイルをインクルード
require 'mysqlConnect.php';
require 'errorMsgs.php';
require 'mysqlClose.php';

// 入力パラメータの取得
$userId = isset($_POST['userId']) ? $_POST['userId'] : null;
$password = isset($_POST['password']) ? $_POST['password'] : null;

// 出力パラメータの設定
$response = array(
    "result" => "",    // 認証結果
    "errCode" => "",   // エラーコード
    "errMsg"          // エラーメッセージ
);

// 1. 入力パラメータの必須チェック
if (empty($userId) || empty($password)) {
    $errCode = "006"; // ユーザIDが指定されていない場合のエラーコード
    $errCode = "007"; // パスワード指定されいない場合のエラーコード
    $errMsg = $errorMessages[$errCode]; // エラーメッセージの取得
    // エラーレスポンスを返す
    returnErrorResponse($errCode, $errMsg); 
}

try {
    // 2. Db接続処理を呼び出し、データベースに接続
    $pdo->beginTransaction();

    // 3. SQL文の作成と実行（例としてパスワードが一致するユーザのカウントを取得）
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE userId = ? AND password = ?");
    $stmt->execute([$userId, $password]);

    // 結果の取得
    $row = $stmt->fetch();

    // ユーザ認証の結果を判定
    if ($row['count'] == 1) {
        // 認証成功時の処理
        $response = array(
            "result" => "成功:success",
            // 他の返却パラメータがあればここにセットする
        );
    } else {
        // 認証失敗時の処理
        $errCode = "003"; // ユーザIDまたはパスワードが違う場合のエラーコード
        $errMsg = $errorMessages[$errCode]; // エラーメッセージの取得
        // エラーレスポンスを返す
        returnErrorResponse($errCode, $errMsg);
    }
} catch (PDOException $e) {
    // データベース処理の例外処理
    $errCode = "001"; // データベース処理が異常終了した場合のエラーコード
    $errMsg = $errorMessages[$errCode]; // エラーメッセージの取得
    // エラーレスポンスを返す
    returnErrorResponse($errCode, $errMsg);
}

// 7. DB切断処理を呼び出し、データベースの接続の解除
$pdo = null;

// 8. 返却値の連想配列をJSON形式にエンコードしてoutputパラメータを出力
header('Content-Type: application/json');
echo json_encode($response);

// 処理を終了
exit();

// エラーレスポンスを返す関数
function returnErrorResponse($errCode, $errMsg) {
    global $errorMessages;
    // エラーレスポンスの作成
    $errorResponse = array(
        "result" => "失敗:error",
        "errCode" => $errCode,
        "errMsg" => $errMsg
    );
    header('Content-Type: application/json');
    echo json_encode($errorResponse);
    // 処理を終了
    exit();
}

?>
