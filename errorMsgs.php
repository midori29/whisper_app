<?php

// API内部で使用するエラーメッセージ返却処理

// 1. エラーコードと対応するエラーメッセージを連想配列で定義
$errorMessages = array(
    "001" => "データベース処理が異常終了しました ",
    "002" => "変更内容がありません",
    "003" => "ユーザIDまたはパスワードが違います",
    "004" => "対象データが見つかりませんでした",
    "005" => "ささやき内容がありません。",
    "006" => "ユーザIDが指定されていません。",
    "007" => "パスワードが指定されていません。",
    "008" => "ささやき管理番号が指定されていません。",
    "009" => "検索区分が指定されていません。",
    "010" => "検索文字列が指定されていません。",
    "011" => "ユーザ名が指定されていません。",
    "012" => "フォロユーザIDが指定されていません。",
    "013" => "フォローフラグが指定されていません。",
    "014" => "イイねフラグが指定されていません。",
    "015" => "ログインユーザIDが指定されていません。",
    "016" => "検索区分が不正です。"
);

//エラーコードの呼び出し方
// エラーコードを指定してエラーメッセージを取得
//$errCode = "000";
//$errMsg = $errorMessages[$errCode];

// エラー情報を連想配列に格納
$errorResponse = array(
    "result" => "失敗:error",
    "errCode" => $errCode,
    "errMsg" => $errMsg
);

// 2. 返却地の連想配列をJSON形式にエンコードしてoutputパラメータを出力
header('Content-Type: application/json');
echo json_encode($errorResponse);

// 3. PHPの終了命令を実行し処理を中断
exit();

?>