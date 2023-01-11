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
