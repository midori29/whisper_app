<?php

// ユーザ作成処理API  whisperで使用するユーザの登録を行う

// 必様なファイル読み込み
require 'mysqlConnect.php';
require 'errorMsgs.php';
require 'mysqlClose.php';

// レスポンス用のデータの枠組みを用意 ※中身はAPIの仕様により異なります。
$response = [
    "result"  => "error", // 実行結果を格納する(success or error) ※成功時にsuccessに書き換える
    "errCode" => null,    // エラーコードがある場合、格納する
    "errMsg"  => null    // エラーメッセージがある場合、格納する
];

// リクエストの解析（リクエストに関する情報は「$_SERVER」に格納）
if ($_SERVER['REQUEST_METHOD'] === 'POST') { // HTTPメソッドがPOST形式で送られてきたか確認。
		
    $postData = json_decode(file_get_contents('php://input'), true);
}

// リクエストされてきたパラメーターの情報を取得
 $user_Id  = $postData["user_Id"];
 $userName = $postData["userName"];
 $password = $postData["password"];

// パラメータチェック(未入力チェックや桁数チェック等) 
$host = "localhost";      // 接続するホスト名を指定　           
$user = "root";       // 接続するユーザー名を指定         
$password = "admin";   // 接続するユーザーのパスワードを指定
$database = "whisper"; // 接続するデーターベース名を指定

// APIで行いたい処理を記述(データベースに接続してデータを取得する等) 
$dsn = "mysql:host={$host};dbname={$database};charset=utf8mb4"; // 接続情報作成 
$options = [
	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,       // クエリ実行時のエラーや接続エラーが発生した場合、例外がスローされるよう指定する。
	PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // 取得した結果を連想配列として取得するフェッチモードの指定する。
	PDO::ATTR_EMULATE_PREPARES => false,               // プリペアドステートメントをエミュレートしないように設定。これによりSQLインジェクション攻撃からの保護が強化される。
];
try {
	$pdo = new PDO($dsn, $user, $password, $options);  // PDOオブジェクト作成
} catch (PDOException $e) {
	throw new PDOException($e->getMessage(), (int)$e->getCode());
}

if(empty($_POST["user_Id"])){
	$errMsg[] = 'ユーザーID : 006';
} 

if(empty($_POST["userName"])){
	$errMsg[] = 'ユーザー名 : 011';
} 

if(empty($_POST["password"])){
	$errMsg[] = 'パスワード : 007';
} 
 
// トランザクションの開始
$pdo->beginTransaction();
// プリペアードステートメントを実行($stmtにSQL文を実行)
// クエリ(SQL)の実行
$sql = "INSERT INTO user (USERID, USERNAME, PASSWORD) VALUES (:user_Id, :userName, :password)";
$stmt = $pdo->prepare($sql);


// プレースホルダにbind_paramを使って値をバインド(設定)
// 第一引数には、値のバインド先の文字列(:xxx)を指定します。第二引数にはバインドする値(変数)を指定する。第三引数にデータの肩を指定する。
// SQL文のプレースホルダとbindParam()の第一引数を一致させる。
$stmt->bindParam(":user_Id", $user_Id, PDO::PARAM_STR);
$stmt->bindParam(":userName", $userName, PDO::PARAM_STR);
$stmt->bindParam(":password", $password, PDO::PARAM_STR);

try{
	if ($stmt->execute() !== false) { // SQL文を実行し、結果がfalseでないかチェックする
		echo "行の追加に成功しました。";
		$pdo->commit(); // 成功したらコミット
	} else {
		echo "行の追加に失敗しました。Error: " . $pdo->errorInfo()[2];
		$pdo->rollBack(); // 失敗したらロールバック
	}
} catch (PDOException $e) {
    echo "エラー: " . $e->getMessage();
    $pdo->rollBack(); // エラーが発生したらロールバック
}


try {
	while ($row = $stmt->fetch()) {
		$data["userId"] = $row["USERID"];
		$data["userName"] = $row["USERNAME"];
		$data["password"] = $row["PASSWORD"];
		$response["list"][] = $data;
	}
$response["result"] = "success";
}catch (PDOException $e) {
	throw new PDOException($e->getMessage(), (int)$e->getCode());

}

//接続を閉じる
// $pdoがnullでないことを確認してから接続を解除
if ($pdo !== null) {
    $pdo = null; // PDO接続を切断
}

// レスポンスの送信(API⇒APIを呼び出したシステムへ)
header('Content-Type: application/json'); // JSON形式でレスポンスを送信するよう指定
echo json_encode($response, JSON_UNESCAPED_UNICODE); // $responseのデータをJSON形式に加工して出力

?>