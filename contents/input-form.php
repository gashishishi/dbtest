<?php
require_once __DIR__ .'/../inc/login-check.php';
require_once __DIR__ .'/../inc/books.php';
require_once __DIR__ .'/../inc/user.php';
include __DIR__ .'/../inc/header.php';

$token = User::createToken();
$edit = false;
// DBの編集フラグ。trueで編集
// $_GET['id']があればDBの更新
if (!empty($_GET['id'])){
    $edit = true;

    // 更新の場合はvalueに値を設定するためbooksクラスをインスタンス化する。
    try {
        $books = new Books();

        // inputタグのvalueを設定する。
        $books->setId($_GET['id']);
        $books->setEditProperty();
        $booksProperty = $books->getProperty();

    } catch(PDOException $e) {
        echo $e;
        exit();
    }
}
?>
    <form action='<?= $edit ? 'update' : 'add' ?>-list.php' method='post'>
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