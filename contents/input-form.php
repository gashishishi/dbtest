<?php
require_once __DIR__ .'/../inc/login-check.php';
require_once __DIR__ .'/../inc/books.php';
require_once __DIR__ .'/../inc/user.php';
include __DIR__ .'/../inc/header.php';

$token = User::createToken();

// DBへの追加のフラグ。falseなら既存データの編集
$addList = true;

// $_GET['id']が無ければDBへのデータ追加
// $_GET['id']があればデータの更新
if (!empty($_GET['id'])){
    $addList = false;
    
    // DB接続のためBooksクラスをインスタンス化
    $books = new Books();
    
    // inputタグのvalueに設定する用
    // 本の情報の設定と取得
    $books->setProperty($_GET);
    $booksProperty = $books->getProperty();
}

?>
    <form action='<?= $addList ? 'add' : 'update' ?>-list.php' method='post'>
        <p>
            <label for="title"> タイトル:</label>
            <input type="text" name="title" value="<?= $booksProperty['title'] ?? "" ?>">
        </p>
        <p>
            <label for="isbn">ISBN:</label>
            <input type="number" name="isbn" max="9999999999999" maxlength="13" value="<?= $booksProperty['isbn'] ?? "" ?>">
        </p>
        <p>
            <label for="price"> 定価:</label>
            <input type="number" name="price" max="999999" maxlength="6" value="<?= $booksProperty['price'] ?? "" ?>">
        </p>
        <p>
            <label for="published"> 出版日:</label>
            <input type="date" name="publish" value="<?= $booksProperty['publish'] ?? "" ?>">
        </p>
        <p>
            <label for="author"> 著者:</label>
            <input type="text" name="author" maxlength="80" value="<?= $booksProperty['author'] ?? "" ?>">
        </p>
        <p class="button">
            <input type="hidden" name="id" value="<?= $booksProperty['id'] ?? "" ?>">
            <input type="hidden" name="token" value="<?= $token ?>">
            <input type="submit" value=" 送信する">
        </p>
    </form>

<?php include __DIR__ .'/../inc/footer.php';?>