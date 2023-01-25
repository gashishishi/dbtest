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
$books->createPager();
?>
<div>
    <h2>本を検索できます</h2>
    <form action="index.php">
        <label>タイトル</label><input type="text" name="title">
        <label>ISBN</label><input type="text" name="isbn">
        <label>値段</label><input type="text" name="price">
        <label>出版日</label><input type="text" name="publish">
        <label>著者</label><input type="text" name="author">
        <input type="submit" value="検索">
    </form>
</div>

<?php !empty($_GET) ? $books->showSearch($_GET) : $books->showList(); ?>
    
<?php include __DIR__ .'/../inc/footer.php';?>