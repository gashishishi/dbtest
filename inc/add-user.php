<?php
session_start();
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

    <form action="add-user.php">
        追加するユーザー
    <p>
        <label for="username">ユーザー名:</label>
        <input type="text" name="username">
    </p>
    <p>
        <label for="password">パスワード:</label>
        <input type="password" name="password">
    </p>
    <input type="submit" value="送信する">
    </form>
    <script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
	<script>
		$(function() {
			$("#button1").change(function() {
				$.ajax({
						url: "testform.php",        // 送り先のURL
						type: "POST",
						data: $("#username").val(), // 送る値
						dataType: "html",           // 受け取るデータの型
						timeout: 2000,              //応答時間のタイムアウト
					})
					.done(function( res ) {
						console.log( res ); // 成功した場合、phpから受け取った値がコンソールに出る。確認用
					})

                    //通信失敗の場合。何が原因かわかりやすいので出す。
					.fail(function(jqXHR, textStatus, errorThrown) {
						console.log(jqXHR.status); //例：404
						console.log(textStatus); //例：error
						console.log(errorThrown); //例：NOT FOUND
					})
					// .always(function() {
					// 	console.log("complete"); // complete
					// });この辺全部いらない
			});
		});
	</script>
<?php include __DIR__ .'/../inc/footer.php';?>
