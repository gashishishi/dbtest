<?php
session_start();
require_once
require_once __DIR__ .'/../inc/user.php';
include __DIR__ .'/../inc/header.php';

if (!empty($_SESSION['login'])){
    if ($_SESSION['login'] === true) {
        echo "ログイン済みです<br>";
        echo "<a href='index.php'>リストに戻る</a>";
    }
} else if (empty($_POST['username']) || empty($_POST['password'])){
        echo "ユーザー名、パスワードを入力してください。";
} else {
    try{
        $user = new User($_POST);
    } catch (PDOException $e){
        echo "エラー!" .$user->e($e->getMessage());
    }
}
?>
<!doctype html>
<html lang="ja">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <title>Hello, world!</title>
  </head>
  <body>
<form action='add-user.php' method='post'>
    <p>
        <label for=''>ユーザー名:</label>
        <input type='text' id="username" name='username'>
    </p>
    <p>
        <label for=''>パスワード:</label>
        <input type='text' name='password'>
    </p>

    <p class='button'>
      <input type='hidden' name='token' value='<?php echo $token ?>'>
      <input type='submit' value=' 送信する'>
    </p>
</form>

<script src="https://code.jquery.com/jquery-3.6.3.js"></script>

<script>
		$(function() {
			$("#username").change(function() {
				$.ajax({
						url: "select-user.php",         //行き先のURL
						type: "POST",                //HTTP送信メソッド
						data: $("#username").val(),  // 送る値
						dataType: "html",            // 受け取るデータの型
						timeout: 2000,              // 応答時間のタイムアウト
					})
					.done(function( res ) {
							console.log(res ); // 成功した場合,phpから受け取った値がコンソールにでる
                            $('#username').after(res);
					})
					.fail(function(jqXHR, textStatus, errorThrown) {
						console.log(jqXHR.status); // 失敗した場合 例：404
						console.log(textStatus); //例：error
						console.log(errorThrown); //例：NOT FOUND
					})
					// .always(function() {
					// 	console.log("complete"); // complete
					// });
			});
		});
	</script>
<?php include __DIR__ . '/../inc/footer.php'; ?>