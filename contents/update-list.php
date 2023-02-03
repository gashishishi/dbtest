<?php
require_once __DIR__ .'/../inc/books.php';
require_once __DIR__ .'/../inc/Checker.php';
require_once __DIR__ .'/../inc/token-check.php';

try {
    $books = new Books();
    $books->setChangeProperty($_POST);
    $books->update();
    echo "変更しました<br>";
    echo "<a href='index.php'> リストへ戻る <a>";
} catch (PDOException $e) {
    echo "エラー：" .$e->getMessage() ."<br>";
    exit;
}