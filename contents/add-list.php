<?php
require_once __DIR__ .'/../inc/books.php';
require_once __DIR__ .'/../inc/token-check.php';

// var_dump($_POST);

try {
    $books = new Books($_POST);
    $books->add();
    echo "追加しました<br>";
    echo "<a href='index.php'> リストへ戻る <a>";
} catch (PDOException $e) {
    echo "エラー：" .$e->getMessage() ."<br>";
    exit;
}

// p210の日付のバリデーション
// $a = '2022-12-32';
// var_dump(strtotime($a));
//              ↑のstrtotime()でやる