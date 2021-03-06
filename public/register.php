<?php
// ログイン名とパスワードが期待したものでない場合はフォームに戻す
if( empty($_POST['login_name']) || empty($_POST['password'])
    || strlen($_POST['login_name']) < 3 || strlen($_POST['login_name']) > 20
    || strlen($_POST['password']) < 6 || strlen($_POST['password']) > 100
) { 
  header("HTTP/1.1 302 Found");
  header("Location: ./register_form.php?error=1");
  return;

}

	// ファイルのアップロード処理
	$filename = null;
	// ファイルの存在確認
	if ($_FILES['upload_image']['size'] > 0) {
	    // 画像かどうかのチェック
	    if (exif_imagetype($_FILES['upload_image']['tmp_name'])) {
	
	        // アップロードされたファイルの元々のファイル名から拡張子を取得
	        $ext = pathinfo($_FILES['upload_image']['name'], PATHINFO_EXTENSION);
	
	        // ランダムな値でファイル名を生成
	        $filename = uniqid() . "." . $ext;
	        $filepath = "/src/2019techc/public/static/upload_image/" . $filename;
	
	        // ファイルを保存
	        move_uploaded_file($_FILES['upload_image']['tmp_name'], $filepath);
	    }
	}

// 接続 ref. https://www.php.net/manual/ja/pdo.connections.php
$dbh = new PDO('mysql:host=database-1.cl54zqktzjxt.us-east-1.rds.amazonaws.com;dbname=dash_DB', 'admin', 'adminpass');
$select_sth = $dbh->prepare('SELECT COUNT(id) FROM users WHERE login_name = :login_name');
$select_sth->execute(['login_name' => $_POST['login_name']]);
$rows = $select_sth->fetchAll();
if ($rows[0][0] !== "0") {
  // 同じログインIDのユーザーが既にある場合はフォームに戻す
  header("HTTP/1.1 302 Found");
  header("Location: ./register_form.php?error=2");
  return;
}
// パスワードのハッシュ化
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
// INSERTする
$insert_sth = $dbh->prepare("INSERT INTO users (login_name, password) VALUES (:login_name, :password)");
$insert_sth->execute(array(
    ':login_name' => $_POST['login_name'],
    ':password' => $password,
));
// 投稿が完了したので閲覧画面に飛ばす
header("HTTP/1.1 303 See Other");
header("Location: ./register_finish.php");
?>
