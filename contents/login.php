<?php
if(!isset($_SESSION)){
    session_start();
}
require_once __DIR__ .'/../inc/user.php';
// if (!empty($_SESSION['login']))の下でuserクラスをインスタンス化すると、
// createSessionId()でエラーになる。セッション開始前にheader.phpを読み込むのが原因?
try{
    $user = new User($_POST);
} catch (PDOException $e){
    echo "エラー!" .UserInput::e($e->getMessage());
}
include __DIR__ .'/../inc/header.php';
if (!empty($_SESSION['login'])){
    if ($_SESSION['login'] === true) {
        echo "ログイン済みです<br>";
        echo "<a href='index.php'>リストに戻る</a>";
    }
}
?>

    <form method="post" action="login.php">
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

<?php include __DIR__ .'/../inc/footer.php';?>
