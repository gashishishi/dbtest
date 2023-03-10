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
?>
<div class="container">
    <?php $books->showList(); ?>
</div>
<?php include __DIR__ .'/../inc/footer.php';?>