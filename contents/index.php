<?php
if(!isset($_SESSION)){
    session_start();
}
require_once __DIR__ .'/../inc/books.php';
include __DIR__ .'/../inc/header.php';

try {
    $books = new Books;
} catch(PDOException $e) {
    echo "エラー：" .$e->getMessage() ."<br>";
    exit;
}
if(empty($_GET['id'])){
    $items = $books->list();
} else {
    $items = $books->search($_GET);

}
?>
<div>
    <h2>本を検索できます</h2>
    <form action="index.php">
        <label>タイトル</label><input type="text" name="title" value="<?= empty($_GET['id']) ? '': Checker::e($item['title']) ?>">
        <label>ISBN</label><input type="text" name="isbn" value="<?= empty($_GET['id']) ? '': Checker::e($item['isbn']) ?>">
        <label>値段</label><input type="text" name="price" value="<?= empty($_GET['id']) ? '': Checker::e($item['price']) ?>">
        <label>出版日</label><input type="text" name="publish" value="<?= empty($_GET['id']) ? '': Checker::e($item['publish']) ?>">
        <label>著者</label><input type="text" name="author" value="<?= empty($_GET['id']) ? '': Checker::e($item['author']) ?>">
        <input type="submit" value="検索">
    </form>
</div>

<table>
    <tr><th>更新</th><th>書籍名</th><th>ISBN</th><th>価格</th><th>出版日</th><th>著者名</th></tr>

    <?php foreach($items as $item): ?>
        <tr>
        <td><a href="input-form.php?id=<?= Checker::e($item['id']) ?>">更新</a></td>
        <td><?= Checker::e($item['title']) ?></td>
        <td><?= Checker::e($item['isbn']) ?></td>
        <td><?= Checker::e($item['price']) ?></td>
        <td><?= Checker::e($item['publish']) ?></td>
        <td><?= Checker::e($item['author']) ?></td>
        </tr>
    <?php endforeach ; ?>
</table>
 
<?php $books->pager(); ?>
    
<?php include __DIR__ .'/../inc/footer.php';?>