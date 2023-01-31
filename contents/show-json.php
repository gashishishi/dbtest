<?php
require_once __DIR__ . '/../inc/books.php';

try{
    $books = new Books;
    $books->showJson($_POST['id']);
} catch(PDOException $e){
    echo "エラー：" .$e->getMessage() ."<br>";
    exit;
}