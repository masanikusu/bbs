<?php

//データベースの接続
$dsn = 'mysql:dbname=bbs_data;host=localhost:8889;charset=utf8';
$user = 'root';
$password = 'root';

//変数の初期化
$csv_data = null;
$sql = null;
$pdo = null;
$option = null;
$message_array = array();
$limit = null;
$stmt = null;

session_start();

//件数取得
if(!empty($_GET['limit'])) {

  if($_GET['limit'] === "10") {
    $limit = 10;
  } elseif($_GET['limit'] === "30") {
    $limit = 30;
  }
}

if(!empty($_SESSION['admin_login']) && $_SESSION['admin_login'] === true) {
  //ファイル出力処理
  
  //データベース接続
  try {
    $option = array(
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::MYSQL_ATTR_MULTI_STATEMENTS => false
    );
    $pdo = new PDO($dsn, $user, $password, $option);

    //メッセージのデータを取得
    if(!empty($limit)) {
      //SQL作成
      $stmt = $pdo->prepare("SELECT * FROM message ORDER BY post_date ASC LIMIT:limit");
      //値をセットする
      $stmt->bindParam(':limit', $_GET['limit'], PDO::PARAM_INT);
    } else {
      $stmt = $pdo->prepare("SELECT * FROM message ORDER BY post_date ASC");
    }

    //SQLクエリの実行
    $stmt->execute();
    $message_array = $stmt->fetchAll();

    //データベースの接続を閉じる
    $stmt = null;
    $pdo = null;

  } catch(PDOException $e) {
    //管理者ページへリダイレクト
    header("Location: ./admin.php");
    exit;
  }
  
  //出力設定
  header("Content-Type: text/csv");
  header("Content-Disposition: attachment; filename=ひと言メッセージデータ.csv");
  header("Content-Transfer-Encoding: binary");

  //CSVデータを作成
  if(!empty($message_array)) {
    //ラベル作成
    $csv_data .= '"ID","表示名","投稿日時"'."\n";

    foreach($message_array as $value) {
      //データを一行ずつCSVファイルに書き込む
      $csv_data .= '"'. $value['id']. '","'.$value['view_name'].'","'.$value['message'].'","'.$value['post_date']."\"\n";
    }
  }
  //ファイル出力
  echo $csv_data;
} else {
  //ログインページへリダイレクト
  header("Location: ./admin.php");
  exit();
}

return;