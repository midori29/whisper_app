<?php

// ユーザ変更処理API   対象ユーザの情報を更新する

// 必要なファイルを読み込み
require 'mysqlConnect.php';
require 'errorMsgs.php';

// Inputパラメータの取得
$userId = isset($_POST['userId']) ? $_POST['userId'] : null;          // ユーザID
$userName = isset($_POST['userName']) ? $_POST['userName'] : null;    // ユーザ名
$password = isset($_POST['password']) ? $_POST['password'] : null;    // パスワード
$profile = isset($_POST['profile']) ? $_POST['profile'] : null;       // プロフィール

// Outputパラメータの設定
$response = array(
    "result" => "",     // 認証結果
    "errCode" => "",    // エラーコード
    "errMsg" => ""      // エラーメッセージ
);

// 1．Inputパラメータの必須チェックを行う
if (empty($userId)) {
    $response['errCode'] = "006";    // ユーザIDが指定されていない場合のエラーコード
    $response['errMsg'] = $errorMessages[$response['errCode']];  // エラーメッセージの取得
    $response['result'] = "error";
    outputResponse($response);  // エラーレスポンスを返す
    exit;
}

// 2．Inputパラメータの更新内容存在チェックを行う
if (empty($userName) && empty($password) && empty($profile)) {
    $response['errCode'] = "002";    // 更新内容が無い場合のエラーコード
    $response['errMsg'] = $errorMessages[$response['errCode']];  // エラーメッセージの取得
    $response['result'] = "error";
    outputResponse($response);  // エラーレスポンスを返す
    exit;
}

try {
    // 3．DB接続処理を呼び出し、データベースの接続を行う
    $pdo->beginTransaction();     // トランザクション開始

    // 4．ユーザデータを更新するSQL文を実行する
    $sql = "UPDATE user SET ";
    $params = array();    // パラメータ配列
    // ユーザ名の更新
    if (!empty($userName)) {
        $sql .= "userName = :userName, ";
        $params[':userName'] = $userName;
    }
    // パスワードの更新(ハッシュ化)
    if (!empty($password)) {
        $sql .= "password = :password, ";
        $params[':password'] = password_hash($password, PASSWORD_BCRYPT);
    }
    // プロフィールの更新
    if (!empty($profile)) {
        $sql .= "profile = :profile, ";
        $params[':profile'] = $profile;
    }
    // 最後のカンマを削除し、WHERE句を追加
    $sql = rtrim($sql, ", ");
    $sql .= " WHERE userId = :userId";
    $params[':userId'] = $userId;

    // SQL文を準備して実行
    $stmt = $pdo->prepare($sql);
    if (!$stmt->execute($params)) {
        // 4-1．データベースのロールバック命令を実行する
        $pdo->rollBack();

        // 4-2．対象エラーメッセージをセットしてエラー終了させる
        $response['errCode'] = "001";    // SQLエラー
        $response['errMsg'] = $errorMessages[$response['errCode']];  // エラーメッセージの取得
        $response['result'] = "error";
        outputResponse($response);  // エラーレスポンスを返す
        exit;
    }

    // 5．データベースのコミット命令を実行する
    $pdo->commit();

    // 6．返却値の連想配列に成功パラメータをセットする
    $response['result'] = "success";
} catch (Exception $e) {
    // 7．エラーメッセージの設定
    $response['errCode'] = "001";
    $response['errMsg'] = $e->getMessage();
    $response['result'] = "error";
}

// 8．SQL情報をクローズさせる
$stmt = null;

// 9．DB切断処理を呼び出し、データベースの接続を解除する
require 'mysqlClose.php';
$pdo = null;   // PDOオブジェクトを閉じる

// 返却値の連想配列をJSONにエンコードしてoutputパラメータを出力する
header('Content-Type: application/json');
echo json_encode($response);

// レスポンス出力関数
function outputResponse($response) {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>
