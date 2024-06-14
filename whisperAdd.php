<?php

// レスポンス用のデータの枠組みを用意
$response = [
    "result"  => "error", // 実行結果を格納する(success or error) ※成功時にsuccessに書き換える
    "errCode" => null,    // エラーコードがある場合、格納する
    "errMsg"  => null    // エラーメッセージがある場合、格納する
];

// リクエストの解析（リクエストに関する情報は「$_SERVER」に格納されています。）
if ($_SERVER['REQUEST_METHOD'] === 'POST') { // HTTPメソッドがPOST形式で送られてきたか確認。
    $postData = json_decode(file_get_contents('php://input'), true);
}

// リクエストされてきたパラメーターの情報を取得
 $user_Id = $postData["user_Id"];
 $content = $postData["content"];


// パラメータチェック(未入力チェックや桁数チェック等)
$host = "localhost";      // 接続するホスト名を指定します。       
$user = "root";       // 接続するユーザー名を指定します。          
$password = "admin";   // 接続するユーザーのパスワードを指定します。
$database = "whisper"; // 接続するデーターベース名を指定します。

// APIで行いたい処理を記述(データベースに接続してデータを取得する等) 
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

if(empty($_POST["user_Id"])){
	$errMsg[] = 'ユーザーID:が指定されていません';
} 

if(empty($_POST["content"])){
	$errMsg[] = 'ささやき内容:ユーザ名が指定されていません';
} 
 
// 2. Db接続処理を呼び出し、データベースの接続を行う
// トランザクションの開始
$pdo->beginTransaction();
//プリペアードステートメントを実行($stmtにSQL文を実行)
//クエリ(SQL)の実行
$sql = "INSERT INTO whisper (USERID, CONTENT ) VALUES (:user_Id, :content)";
$stmt = $pdo->prepare($sql);


//プレースホルダにbind_paramを使って値をバインド(設定)
//第一引数には、値のバインド先の文字列(:xxx)を指定します。第二引数にはバインドする値(変数)を指定します。第三引数にデータの肩を指定します
// SQL文のプレースホルダとbindParam()の第一引数を一致させる
$stmt->bindParam(":user_Id", $user_Id, PDO::PARAM_STR);
$stmt->bindParam(":content", $content, PDO::PARAM_STR);

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
		$data["content"] = $row["CONTENT"];
		$response["list"][] = $data;
	}
$response["result"] = "success";
}catch (PDOException $e) {
	throw new PDOException($e->getMessage(), (int)$e->getCode());

}

// 8．DB切断処理を呼び出し、データベースの接続を解除する
//接続を閉じる
// $pdoがnullでないことを確認してから接続を解除
if ($pdo !== null) {
    $pdo = null; // PDO接続を切断
}

// 9. 返却値の連想配列をJSONにエンコードしてoutputパラメータを出力する
// レスポンスの送信(API⇒APIを呼び出したシステムへ)
header('Content-Type: application/json'); // JSON形式でレスポンスを送信するよう指定
echo json_encode($response, JSON_UNESCAPED_UNICODE); // $responseのデータをJSON形式に加工して出力

?>