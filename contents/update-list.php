<?php
require_once __DIR__ .'/../inc/books.php';
require_once __DIR__ .'/../inc/user-input.php';
require_once __DIR__ .'/../inc/token-check.php';

// var_dump($_POST);
try {
        $books = new Books($_POST);
        echo "変更しました";
    } catch (PDOException $e) {
        echo "エラー：" .$e->getMessage() ."<br>";
        exit;
    }