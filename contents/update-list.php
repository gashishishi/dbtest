<?php
require_once __DIR__ .'/../inc/books.php';
require_once __DIR__ .'/../inc/user-input.php';
require_once __DIR__ .'/../inc/token-check.php';

// var_dump($_POST);
try {
    $books = new Books($_POST);
    $books->update();
    echo "変更しました<br>";
    echo "<a href='index.php'> リストへ戻る <a>";
} catch (PDOException $e) {
    echo "エラー：" .$e->getMessage() ."<br>";
    exit;
}